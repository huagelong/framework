@extends('layout')
@require('/resource/static/js/test.js')
@section('title', 'Page Title')

@section('sidebar')
    @parent

    <p>This is appended to the master sidebar.</p>
@endsection

@section('content')
    <p>{{$test}}</p>
    <?php
    $records = [1];
    ?>
    @if (count($records) === 1)
        I have one record!
    @elseif (count($records) > 1)
        I have multiple records!
    @else
        I don't have any records!
    @endif

@endsection


