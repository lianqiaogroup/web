var path = require('path');

// 模块分析
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;

// 生成html
const HtmlWebpackPlugin = htmlWebpackPlugin = require('html-webpack-plugin');


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
	},
	output: {
		path: path.join(__dirname, './develop/'),
		filename: '[name].js',
		// publicPath: "",
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
	// watch: true,
	// watchOptions: {
	// 	aggregateTimeout: 500,
	// 	poll: 1000,
	// 	ignored: /node_modules/,
	// },
	devServer: {
		contentBase: __dirname + "/develop/",
		host: "dev-front-admin.stosz.com",
		port: 3001,
		historyApiFallback: false,
		inline: true,
		proxy: {
			'/proxy/': {
				target: 'http://dev-front-admin.stosz.com/',
				changeOrigin: true,
				pathRewrite: {
					'^/proxy/': ''
				}
      		},
		}
	},
	plugins: [
		new HtmlWebpackPlugin({
			filename: 'index.html',
			template: './source/template/index.html',
			inject: true,
			chunks: ['main'],
			// 路径源修改
			// static: '/develop',
			timestamp: new Date().getTime(),
			minify: {
				//删除html的注释
				removeComments: true,
				//删除空格
				collapseWhitespace: true
			}
		}),
		// new BundleAnalyzerPlugin()
		// new webpack.HotModuleReplacementPlugin()
	]
}
