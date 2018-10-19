import axios from 'axios';
// import store from '../store'

/*
 * 开发环境下 process.env.BASE_API='/proxy/', 会自动代理到 www.plxqq.com 项目
 */

const service = axios.create({
    baseURL: location.hostname=='dev-front-admin.stosz.com' && location.port=='3001' ? '/proxy/' : '',
    timeout: 300000 // 请求超时
});

/**
 *description   axios全局请求拦截
 *@config{auth} Boolean, 用于判断改接口是否需要身份信息验证
 *@config{mock} Boolean, 是否开启mock模式，https://www.easy-mock.com/
 */ 
service.interceptors.request.use(
    (config) => {

        // 判断接口是否开启了mock模式
        if (config.mock && config.mock==true) {
            // config.baseURL = 'https://www.easy-mock.com/mock/5afea3526ba6060f61c23226/Cube';
            config.baseURL = 'https://www.easy-mock.com/mock/5b6aaed98e2f5f1bd860e318';
        }

        // 如果开缓存的话先check localStorage
        // if (config.cache==true && config.cacheName!='' && localStorage) {
        //     if (localStorage.getItem(config.cacheName)) {
        //         console.warn(`load localStorage with ${config.cacheName}`);
        //         Promise.resolve(JSON.parse(localStorage.getItem(config.cacheName)));
        //         // console.log(config.url = '');
        //         return config;
        //     }
        // }

        // 加入token 
        // if (config.method=='post') {
        //     Object.assign(config.params, {
        //         token: store.state.token
        //     });
        // }

        // 返回更新的ajax配置文件
        return config
    },
    (error) => {
        Promise.reject(error)
    },
);

/**
 *description   axios全局接口响应拦截
 *res{code}     接口状态码，成功=200，失败=500，没权限=403
                根据状态码调用不同的全局模块
 */ 
service.interceptors.response.use(
    (res) => {

         // 如果开缓存的话存到 localStorage
        if (res.config.cache==true && res.config.cacheName!='' && localStorage) {
            localStorage.setItem(res.config.cacheName, JSON.stringify(res.data))
        }

        if (typeof(res.data)=="string" && res.data!="") {
            return Promise.reject(err)
        }
        // 获取状态码
        let _code = parseInt(res.code);

        // 根据状态码执行不同的操作
        if (_code===200) {
            return res.data;
        }
        if (_code===500) {
            return Promise.reject({})
        }
        if (_code==403) {
            return Promise.reject({})
        }
        return res.data;
    },
    (err) => {
        return Promise.reject(err)
    },
);



export default service
