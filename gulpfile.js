var gulp = require('gulp'),
	minifycss = require('gulp-minify-css'),
	rev = require('gulp-rev'),
	htmlmin = require('gulp-htmlmin'),
	minifyjs = require('gulp-uglify'),
	revCollector = require('gulp-rev-collector'),
	imagemin = require("gulp-imagemin");

// npm install gulp-minify-css gulp-rev gulp-htmlmin gulp-uglify gulp-rev-collector gulp-imagemin

// 压缩css
gulp.task('minifycss',function(){
	return gulp.src('./style/*.css')      
	.pipe(minifycss())
	.pipe(rev())
	.pipe(gulp.dest('./build/style/'))
	.pipe(rev.manifest())
	.pipe(gulp.dest('./rev/style/'));
});



// 压缩图片
gulp.task('testImagemin', function() {
    gulp.src('./images/*.{png,jpg,gif,ico}')
    .pipe(imagemin())
    .pipe(gulp.dest('./build/images'));
});

// 压缩js
gulp.task('minifyjs',function(){
   return gulp.src(['./js/*.js'])
       .pipe(minifyjs())
       .pipe(rev())
       .pipe(gulp.dest('./build/js/'))
       .pipe(rev.manifest())
		.pipe(gulp.dest('./rev/js/'));
});

// 压缩html页面并替换js、css路径
gulp.task('minifyhtml', function () {
    var options = {
        removeComments: false,//清除HTML注释
        collapseWhitespace: false,//压缩HTML
        collapseBooleanAttributes: true,//省略布尔属性的值 <input checked="true"/> ==> <input />
        removeEmptyAttributes: true,//删除所有空格作属性值 <input id="" /> ==> <input />
        removeScriptTypeAttributes: true,//删除<script>的type="text/javascript"
        removeStyleLinkTypeAttributes: true,//删除<style>和<link>的type="text/css"
        minifyJS: true,//压缩页面JS
        minifyCSS: true//压缩页面CSS
    };
    gulp.src(['./rev/style/rev-manifest.json','./rev/js/rev-manifest.json','./*.html'])
        .pipe(htmlmin(options))
        .pipe(revCollector())
        .pipe(gulp.dest('./build/'));
});

gulp.task('default',['minifycss','testImagemin','minifyjs','minifyhtml']);