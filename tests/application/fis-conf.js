// 按需编译，只编译用到的资源
fis.set('project.files', ['*.blade.php', 'map.json']);
fis.set('project.ignore', ['public/**', 'config/**', 'route/**', 'src/**', 'storage/**','node_modules/**', 'output/**', '.git/**', 'fis-conf.js']);
// 采用 commonjs 规范支持模块化组建开发
fis.hook('commonjs', {
  packages: [
    // 短路径支持
    // 可以通过 require('/libs/alert') 依赖 `static/libs/alert.js`
    {
      name: 'libs',
      location: '/resource/static/libs'
    }
  ]
});

//所有静态文件都要用指纹
fis.match('/resource/static/**.{js,css,png,jpg,gif}', {
  useHash: true
});

// 让所有的 js 都用模块化的方式开发。
fis.match('/resource/static/**.js', {
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

// static/js 下面放非模块化 js
fis.match('/resource/static/js/*.js', {
  isMod: false,
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
fis
  .media('prod')

  .match('/resource/static/*.js', {
    optimizer: fis.plugin('uglify-js')
  })

  .match('/resource/static/*.css', {
    optimizer: fis.plugin('clean-css')
  })

  .match('/resource/static/*.png', {
    optimizer: fis.plugin('png-compressor')
  })

  // libs 目录下面的 js 打成一个
  .match('/resource/static/libs/**.js', {
    packTo: '/resource/static/pkg/lib.js'
  })

  // components 目录下面的打成一个。
  .match('/resource/static/components/**.js', {
    packTo: '/resource/static/pkg/components.js'
  })
