var path = require('path');

// 压缩插件
var FastUglifyJsPlugin = require('fast-uglifyjs-plugin');

// 模块分析
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;

// 生成html
const HtmlWebpackPlugin = htmlWebpackPlugin = require('html-webpack-plugin');

// 七牛上传插件
const QiniuPlugin = require('qiniu-webpack-plugin');

module.exports = {
    resolve: {
        extensions: ['.js', '.vue'],
        alias: {
            '@': path.resolve(__dirname, './source')
        }
    },
    entry: {
        // login: './source/vue/login.js',
        main: './source/vue/main.js',
    }
    , output: {
        path: path.join(__dirname, './build/js/')
        , filename: '[name].[chunkhash].js'
        , chunkFilename: '[name].[chunkhash:8].js'
        , publicPath: "http://shopadmin.stosz.com/build/js/"
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                use: [
                    {
                        loader: 'babel-loader',
                        query: {
                            presets: ['es2015']
                        }
                    },
                ],
                exclude: /node_modules/,
            }, {
                test: /\.vue$/,
                use: ['vue-loader'],
            }
            , {
                test: /\.yml$/,
                use: 'yaml-loader',
            }
        ]
    },
    externals: {
        "vue": "window.Vue"
    },
    plugins: [
        new HtmlWebpackPlugin({
            filename: 'index.html',
            template: './source/template/index.html',
            inject: true,
            chunks: ['main'],
            // 路径源修改
            static: 'http://shopadmin.stosz.com/build',
            timestamp: new Date().getTime(),
            minify: {
                //删除html的注释
                removeComments: true,
                //删除空格
                collapseWhitespace: true
            }
        }),
        new QiniuPlugin({
            ACCESS_KEY: '0znPKSrr3SZ4EGBme8kNqecuw_r6ClHPfY',
            SECRET_KEY: 'SoOrKXr8X2kuBTuJ8TkfUyGrbCrEIYkOP4Euq0LY',
            bucket: 'bucket-cn',
            path: 'build/js/'
        })
    ]
}

