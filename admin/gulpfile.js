// 模块插件列表
var gulp = require('gulp');

// CSS 预编译模块
var less = require('gulp-less'); // LESS

// 压缩css模块
var minifycss = require('gulp-minify-css');

// file system 模块
var fs = require('fs');

// 目录路径模块
var path = require('path');

// 重命名模块
var rename = require('gulp-rename'); 

// 监控文件模块
var watch  = require('gulp-watch'); 

// 删除文件模块
var clean = require('gulp-clean'); 

// 替换模块
var replace = require('gulp-replace'); 

// node 命令行传参模块
var minimist = require('minimist');

// 命令行颜色块
var color = require('colors');

// 文件合并模块
var concat = require('gulp-concat');

// 本地测试数据服务器
const spawn = require('child_process').spawn;

// Javascript压缩模块
var uglify = require('gulp-uglify');

// 静态资源文件MD5版本号
var rev = require('gulp-rev'); 

// 静态资源替换
var revCollector = require('gulp-rev-collector');



// CDN源
var CDN = "http://shopadmin.stosz.com";

// 是否开发模式
var develop_mode = true; 

// 是否生产模式
let IS_PRODUCTION = false;

// 存放编译任务输出的目录
var destination = ""; 

// 站目录结构
const Project = {
    build: {
        base: "./build/"
        , js: "./build/js/"
        , css: "./build/style/"
        , html: "./build/*.html"
    }
    , develop: {
        base: "./develop/"
        , html: "./develop/*.html"
        , css: "./develop/style/*.css"
        , js: "./develop/js/*.js"
    }
    , source: {
        base: "./source/"
        , html: ["./source/template/index.html", "./source/template/login.html" ]
        , less: "./source/less/*.less"
        , plugins: "./source/plugins/**/*.*"
        , images: "./source/images/**/*.jpg"
    }
}

gulp.task("server", function(){
   spawn("node", ["static-server.js"]);
});

// 默认任务
gulp.task('default', function(){

});

// 开发任务
gulp.task('develop', function(){
    develop_mode = true;
    destination = Project.develop.base;
    gulp.run([ 'less', 'images', 'plugins', 'watch' ]);
});


// 编译 LESS
gulp.task('less', function() {
    if( develop_mode==false){
        return gulp.src( Project.source.less )
            .pipe(less())
            .pipe(replace('{{ resouce-origin }}', !develop_mode ? "/build" : "/develop"))
            .pipe(minifycss())
            .pipe(gulp.dest( Project.build.css ));
    }
    if( develop_mode==true ){
        return gulp.src( Project.source.less )
            .pipe(less())
            .pipe(replace('{{ resouce-origin }}', !develop_mode ? "/build" : "/develop"))
            .pipe(gulp.dest( destination + "style/" ));
    }
});

// 复制 images
gulp.task('images', function(){
    return gulp.src( Project.source.images )
        .pipe(gulp.dest( destination + 'images/' ));
});

// 复制 plugins
gulp.task('plugins', function(){
    return gulp.src( Project.source.plugins )
        .pipe(gulp.dest( destination + 'plugins' ));
});


gulp.task('watch', function(){
    gulp.watch([ Project.source.html ], ['replaceStaticFile']);
    gulp.watch(['./source/less/**/*.less'], ['less']);
});



// =====================================================================

/*
生成 build 任务流程
    － 清除多余文件
    － 编译html
    － 编译less
    － 编译ES6
    － 替换静态资源路径(CDN) cdn_url_replace
    － 静态资源hash替换(JS,CSS)
    － 部署7牛
*/
gulp.task('build', ['build_clean'], function(){
    // 生产模式
    IS_PRODUCTION = true;
    develop_mode = false;
    destination = Project.build.base;
    gulp.run([
        'build_clean', 'build_less'
    ]);
});

// 清空 build 文件
gulp.task('build_clean', function(){
    return gulp.src([Project.build.js, Project.build.css])
        .pipe(clean());
});

// 编译 LESS
gulp.task('build_less', function () {
    return gulp.src( Project.source.less )
        .pipe(less())
        .pipe(replace('{{ resouce-origin }}', "/build"))
        .pipe(minifycss())
        .pipe(gulp.dest( Project.build.css ));
});


// Javascript文件路径替换hash版本
gulp.task('rev-javascript', function(){
    // 需要版本替换的文件
    let _rev = { "login.js": "" , "main.js": ""}
    // 生成rev-javascript文件
    fs.readdirSync('./build/js/').map( filename => {
        filename.includes('main') ? _rev['main.js'] = filename : null;
        filename.includes('login') ? _rev['login.js'] = filename : null;
    });
    fs.writeFileSync(Project.build.base+"js/rev-manifest.json", JSON.stringify(_rev));
    // 替换路径
    return gulp.src([Project.build.html])
        .pipe(replace('main.js', _rev['main.js']))
        .pipe(replace('login.js', _rev['login.js']))
        .pipe(gulp.dest( Project.build.base ));
});

// 推送style文件到七牛CDN
gulp.task('qniuPush', function(){
    var filelist = fs.readdirSync('./build/style/');
    var filelistName = [];
        filelist.map(function(filename){
            if( filename.indexOf('.json') < 0 ){
                filelistName.push(filename);
            }
        });
        filelistName.map(function(filename){
            var key = "build/style/"+filename;
            var token = uptoken(qiniu.conf.bucket, key);
            uploadFile(token, key, './build/style/' + filename);
        });
});

// 插件推荐
gulp.task('qniuPush-plugins', function(){
    return false;
    // vue
    fs.readdirSync('./build/plugins/vue/').map(function(filename){
        var key = "build/plugins/vue/"+filename;
        var token = uptoken(qiniu.conf.bucket, key);
        uploadFile(token, key, './build/plugins/vue/' + filename);
    });
    // fmp config
    fs.readdirSync('./build/plugins/fmp/').map(function(filename){
        var key = "build/plugins/fmp/"+filename;
        var token = uptoken(qiniu.conf.bucket, key);
        uploadFile(token, key, './build/plugins/fmp/' + filename);
    });
    // echarts
    fs.readdirSync('./build/plugins/echarts/').map(function(filename){
        var key = "build/plugins/echarts/"+filename;
        var token = uptoken(qiniu.conf.bucket, key);
        uploadFile(token, key, './build/plugins/echarts/' + filename);
    });
    // images
    fs.readdirSync('./build/images/').map(function(filename, type){
        var key = "build/images/"+filename;
        var token = uptoken(qiniu.conf.bucket, key);
        uploadFile(token, key, './build/images/' + filename);
    });
    // element
    fs.readdirSync('./build/plugins/element-ui/').map(function(filename){
        var key = "build/plugins/element-ui/"+filename;
        var token = uptoken(qiniu.conf.bucket, key);
        uploadFile(token, key, './build/plugins/element-ui/' + filename);
    });
    // element－fonts
    fs.readdirSync('./build/plugins/element-ui/fonts/').map(function(filename){
        var key = "build/plugins/element-ui/fonts/"+filename;
        var token = uptoken(qiniu.conf.bucket, key);
        uploadFile(token, key, './build/plugins/element-ui/fonts/' + filename);
    });
    // clipboard
    fs.readdirSync('./build/plugins/clipboard/').map(function(filename){
        var key = "build/plugins/clipboard/"+filename;
        var token = uptoken(qiniu.conf.bucket, key);
        uploadFile(token, key, './build/plugins/clipboard/' + filename);
    });
    // fonts
    fs.readdirSync('./build/plugins/font/').map(function(filename){
        var key = "build/plugins/font/"+filename;
        var token = uptoken(qiniu.conf.bucket, key);
        uploadFile(token, key, './build/plugins/font/' + filename);
    });
});

var qiniu = require("qiniu");
    qiniu.conf.ACCESS_KEY = '0znPKSrr3SZ4EGBme8kNqecuw_r6ClHPfY';
    qiniu.conf.SECRET_KEY = 'SoOrKXr8X2kuBTuJ8TkfUyGrbCrEIYkOP4Euq0LY';
    qiniu.conf.bucket = 'bucket-cn';

//构建上传策略函数
function uptoken(bucket, key) {
    var putPolicy = new qiniu.rs.PutPolicy(bucket+":"+key);
    return putPolicy.token();
}

//构造上传函数
function uploadFile(uptoken, key, localFile) {
  var extra = new qiniu.io.PutExtra();
    qiniu.io.putFile(uptoken, key, localFile, extra, function(err, ret) {
      if(!err) {
        console.log('upload succuess!')
      } else {
        console.log(err);
      }
  });
}



// =========================================

gulp.task('theme_edit', ['theme_edit_less', 'theme_edit_watch'], function(){});
gulp.task('theme_edit_less', function(){
    return gulp.src( './source/less/theme_edit.less' )
        .pipe(less())
        .pipe(gulp.dest("develop/" + "style/" ))
        .pipe(gulp.dest("build/style/"));
});
gulp.task('theme_edit_watch', function(){
    gulp.watch(['./source/less/theme_edit.less'], ['theme_edit_less']);
});





