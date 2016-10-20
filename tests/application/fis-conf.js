// 按需编译，只编译用到的资源
fis.set('project.files', ['*.blade.php', 'map.json']);
fis.set('project.ignore', ['public/**', 'config/**', 'route/**', 'src/**', 'storage/**','node_modules/**', 'output/**', '.git/**', 'fis-conf.js']);

// 采用 commonjs 规范支持模块化组建开发
fis.hook('commonjs', {
  packages: [
    {
      name: 'modules',
      location: '/resource/static/modules'
    }
  ],
  extList: ['.js', '.jsx', '.es', '.ts', '.tsx'],
  paths: {
   "jquery": "/resource/static/modules/jquery/jquery-3.1.1.min.js",
    $: "/resource/static/modules/jquery/jquery-3.1.1.min.js"
  }
});


//所有静态文件都要用指纹
fis.match('/resource/static/**.{js,css,png,jpg,gif}', {
  useHash: true
});

// 让所有的 js 都用模块化的方式开发。
fis.match('/resource/static/modules/**.js', {
  isMod: true
});
// 默认认为所有的资源都是静态资源
fis.match('/resource/static/**', {
  release: '/$0',
  url: '$0'
});

fis.match('/resource/views/**.blade.php', {
  // 启用 blade 语法识别插件
  //
  // 1. 转换所有的 @require(path) 路径
  // 2. 识别 @script()@endscript 让内容进行 js 标准化。
  // 3. 识别 @style()@endstyle 让内容进行 css 标准化。
  // 4. 添加钩子，方便运行时加载当前模板依赖。
  preprocessor: fis.plugin('extlang', {
    type: 'blade'
  }),

  release: '/$&',
  url: '$&',

  // 将资源信息写入 map.json 里面，方便运行时查找依赖。
  useMap: true
});

// 配置 map.json 产出路径。
fis.match('/resource/map/map.json', {
  release: '/resource/map/map.json',
});

// 给组件下面的 js 设置同名依赖
fis.match('/resource/static/components/**.js', {
  useSameNameRequire: true
})

// 支持前端模板，支持 js 内嵌后，直接翻译成可执行的 function
fis.match('/resource/static/*.tmpl', {
  parser: fis.plugin('utc'),
  rExt: '.js',
  release: false
});

// 在 prod 环境下，开启各种压缩和打包。
fis.media('prod')
  .match('/resource/static/*.js', {
    optimizer: fis.plugin('uglify-js')
  })
  .match('/resource/static/*.css', {
    optimizer: fis.plugin('clean-css')
  })
  .match('/resource/static/*.png', {
    optimizer: fis.plugin('png-compressor')
  })
  .match('::package', {
    packager: fis.plugin('map', {
      '/resource/static/pkg/third.js': [
        '/node_modules/**.js'
      ],
      '/resource/static/pkg/app.js':[
        '/resource/static/modules/**.js',
      ]
    })
  })
//     .match('::packager', {
//   packager: fis.plugin('deps-pack', {
//
//     'pkg/hello.js': [
//
//       // 将 main.js 加入队列
//       '/static/hello/src/main.js',
//
//       // main.js 的所有同步依赖加入队列
//       '/static/hello/src/main.js:deps',
//
//       // 将 main.js 所以异步依赖加入队列
//       '/static/hello/src/main.js:asyncs',
//
//       // 移除 comp.js 所有同步依赖
//       '!/static/hello/src/comp.js:deps'
//     ],
//
//     // 也可以从将 js 依赖中 css 命中。
//     'pkg/hello.css': [
//       // main.js 的所有同步依赖加入队列
//       '/static/hello/src/main.js:deps',
//     ]
//
//   })
// });