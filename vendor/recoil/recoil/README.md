# Recoil

[![Build Status](http://img.shields.io/travis/recoilphp/recoil/master.svg?style=flat-square)](https://travis-ci.org/recoilphp/recoil)
[![Code Coverage](https://img.shields.io/codecov/c/github/recoilphp/recoil/master.svg?style=flat-square)](https://codecov.io/github/recoilphp/recoil)
[![Code Quality](https://img.shields.io/scrutinizer/g/recoilphp/recoil/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/recoilphp/recoil/)
[![Latest Version](http://img.shields.io/packagist/v/recoil/recoil.svg?style=flat-square&label=semver)](https://semver.org)

    composer require recoil/recoil

Recoil requires PHP 7. For a version that works with PHP 5, please see the [0.4.0 release](https://github.com/recoilphp/recoil/releases/tag/0.4.0).

## Overview

Recoil aims to ease development of asynchronous applications by presenting asynchronous control flow in a familiar
"imperative" syntax.

**What does that mean?** Let's jump right in with an example that resolves multiple domain names **concurrently**.

```php
use Recoil\React\ReactKernel;
use Recoil\Recoil;

function resolveDomainName(string $name, React\Dns\Resolver\Resolver $resolver)
{
    try {
        $ip = yield $resolver->resolve($name);
        echo 'Resolved "' . $name . '" to ' . $ip . PHP_EOL;
    } catch (Exception $e) {
        echo 'Failed to resolve "' . $name . '" - ' . $e->getMessage() . PHP_EOL;
    }
}

ReactKernel::start(function () {
    // Create a React DNS resolver ...
    $resolver = (new React\Dns\Resolver\Factory)->create(
        '8.8.8.8',
        yield Recoil::eventLoop()
    );

    // Concurrently resolve three domain names ...
    yield [
        resolveDomainName('recoil.io', $resolver),
        resolveDomainName('php.net', $resolver),
        resolveDomainName('probably-wont-resolve', $resolver),
    ];
});
```

This code resolves three domain names to their IP address and prints the results to the terminal. You can try the
example yourself by running the following command in the root of the repository:

```
./examples/dns
```

Run it a few times. You'll notice that the output is not always in the same order. This is because the requests are made
concurrently and the results are shown as soon as they are received from the DNS server.

Note that there is **no callback-passing**, and that regular PHP **exceptions are used for reporting errors**. This is
what we mean by "familiar imperative syntax".

**Clear as mud?** Read on :)

## Concepts

### Coroutines

_Coroutines_ are essentially functions that can be suspended and resumed while maintaining their state. This is useful
in asynchronous applications, as the coroutine can suspend while waiting for some task to complete or information
to arrive, and the CPU is free to perform other tasks.

PHP generators provide the language-level support for functions that can suspend and resume, and Recoil provides the
glue that lets us use these features to perform asynchronous operations.

A Recoil application is started by executing an "entry-point" generator, a little like the `main()` function in the C
programming language. The Recoil kernel inspects the values yielded by the generator and identifies an operation to
perform. For example, yielding a `float` with value `30` causes the coroutine to suspend execution for 30 seconds.

The DNS example above shows a rather more advanced usage, including concurrent execution and integration with
asynchronous code that is not part of Recoil. The resulting code, however, is quite normal looking, except for the
`yield` statements!

Within Recoil, the term _coroutine_ specifically refers to a PHP generator that is being executed by the Recoil kernel.
It's no mistake that generators can be used in this way. [Nikita Popov](https://github.com/nikic) (who is responsible
for the original generator implementation in PHP) published an [excellent article](http://nikic.github.io/2012/12/22/Cooperative-multitasking-using-coroutines-in-PHP.html)
explaining generator-based coroutines. The article even includes an example implementation of a coroutine scheduler,
though it takes a somewhat different approach.

### Strands

A _Strand_ is Recoil's equivalent to your operating system's threads. Each strand has its own call-stack and may be
suspended, resumed, joined and terminated without affecting other strands. The elements on the call-stack are not
regular functions, but are instead coroutines.

Unlike threads, execution of a strand can only suspend or resume when a coroutine specifically requests to do so, hence
the term _cooperative multitasking_.

Strands are very light-weight and are sometimes known as [green threads](http://en.wikipedia.org/wiki/Green_threads), or
(perhaps less correctly) as [fibers](https://en.wikipedia.org/wiki/Fiber_(computer_science)).

Recoil's concept of the strand is defined by the [Strand](src/Kernel/Strand.php) interface.

### Awaitables

An _Awaitable_ is any value that Recoil recognises when yielded by a coroutine. For example, yielding another generator
pushes that generator onto the current strand's call-stack and invokes it, thus making it a coroutine.

The are four kinds of awaitables:

1. PHP generators - coroutines to be executed on the current strand
1. API calls - special operations used to manage strands
1. Custom awaitables - implementations of [`Awaitable`](src/Kernel/Awaitable.php), [`AwaitableProvider`](src/Kernel/AwaitableProvider.php)
or [`CoroutineProvider`](src/Kernel/CoroutineProvider.php)
1. Adaptable values - values that can be adapted into a awaitable, such as the use of `float` to suspend execption for a given number of seconds

### The Kernel and Kernel API

The _kernel_ is responsible for creating and scheduling strands, much like the operating system kernel does for threads.

There is currently a single kernel implementation bundled with Recoil, which is based on [React](https://github.com/reactphp/react),
a well established reactor event-loop implementation. This allows applications to execute coroutine based code alongside
"conventional" React code by sharing an event-loop instance.

In the future Recoil will ship with its own event-loop implementation and the React-specific code will be available as a
set of "bindings".

The kernel and strands are manipulated using the _kernel API_, a set of operations defined in [Api](src/Kernel/Api.php)
and accessible using the [Recoil facade](src/Recoil.php).

## Examples

The following examples illustrate the basic usage of coroutines and the kernel API. Additional examples are available in
the [examples folder](examples/).

References to `Recoil` and `ReactKernel` refer to the [Recoil facade](src/Recoil.php), and the [React kernel implementation](src/React/ReactKernel.php),
respectively.

### Basic execution

The following example shows the simplest way to execute a generator as a coroutine.

```php
ReactKernel::start(
    function () {
        echo 'Hello, world!' . PHP_EOL;
        yield;
    }
);
```

`ReactKernel::start()` is a convenience method that instantiates the React-based kernel and executes the given coroutine
in a new strand. Yielding `null` (via `yield` with no explicit value) allows PHP to parse the function as a generator,
and allows the kernel to process other strands, though there are none in this example.

### Calling one coroutine from another

A coroutine can be invoked by simply yielding it, as described in the section on coroutines above. You can also use the
`yield from` syntax, which may perform better but only works with generators, whereas `yield` works with any awaitable
value.

```php
function hello()
{
    echo 'Hello, ';
    yield;
}

function world()
{
    echo 'world!' . PHP_EOL;
    yield;
}

ReactKernel::start(function () {
    yield hello();
    yield world();
});
```

### Returning a value from a coroutine

To return a value from a coroutine, simply use the `return` keyword as you would in a normal function.

```php
function multiply($a, $b)
{
    yield; // force PHP to parse this function as a generator
    return $a * $b;
    echo 'This code is never reached.';
}

ReactKernel::start(function () {
    $result = yield multiply(2, 3);
    echo '2 * 3 is ' . $result . PHP_EOL;
});
```

### Throwing and catching exceptions

One of the major syntactic advantages of coroutines over callbacks is that errors can be reported using familiar
exception handling techniques. The `throw` keyword can be used in in a coroutine just as it can in a regular function.

```php
function multiply($a, $b)
{
    if (!is_numeric($a) || !is_numeric($b)) {
        throw new InvalidArgumentException();
    }

    yield; // force PHP to parse this function as a generator
    return $a * $b;
}

ReactKernel::start(function() {
    try {
        yield multiply(1, 'foo');
    } catch (InvalidArgumentException $e) {
        echo 'Invalid argument!';
    }
});
```
