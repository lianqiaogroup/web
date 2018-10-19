# 一、文档

### 前后端分离接口文档：
http://192.168.105.224/web/#/38?page_id=203

### 建站后台对外公共接口文档：

# 二、PHP端

# 三、前端

## 1. 简介
技术栈: Vue + Vue-Router + Vuex + Axios  
脚手架: ```webpack@2.2```, ```gulp```  
依赖环境：```Node@6+```  


## 2. 命令
1). 安装命令  
```npm install```

2). 开发命令  
```npm run watch```  
执行后打开 dev-front-admin.stosz.com:3001 即可

3). 打包命令  
```npm run build``` 


## 3. 目录结构
```
|-- admin  
| |-- build 打包后的文件
| |-- source 源代码
| | |-- Api 
| | | |-- Service.js
| | | |-- ...
| |-- template 老的twig模版代码
| |-- webpack.dev.config.js
| |-- webpack.productION.config.js
| |-- gulpfile.js
| |-- .babelrc
```

## 4. webpack 插件列表
```
1. BundleAnalyzerPlugin 模块分析
2. HtmlWebpackPlugin 生成静态文件
3. QiniuPlugin 打包文件自动上传到七牛的云存储
```


# 四、脚本（定时任务)


# 五、发版日志

v4.40 (2018-08-07)
```
1. xxxxxxxx
2. xxxxxxxxXX
3. xxxxxxxxXXXX
4. xxxxx
```

v4.39 (2018-08-07)
```
1. xxx
2. xxx
3. xxx
4. xx
```