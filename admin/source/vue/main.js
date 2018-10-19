// 外部插件
let { Vuex, VueRouter } = window;

// API模块
import axios from 'axios';

// Vuex
import store from '@/Store';

// 路由模块
import router from '@/Router';

// 菜单栏模块
import slidemenu from '@/vue/global/slideMenu.vue';


// 增加拦截器，兼容用vue-resource的老接口请求、后续砍掉
Vue.http.interceptors.push((request, next) => {
    if (location.hostname=='dev-front-admin.stosz.com' && location.port=='3001') {
        if (request.url.match('http')==null) {
            if (request.url.split()[0]!='/') {
                request.url = `/proxy/${request.url}`;
            } else {
                request.url = `/proxy${request.url}`;
            }
        }
    }
    next((response) => {
        return response; 
    }); 
}); 

// 老接口中心、后续砍了
import Service from './service.js';
// 缓存数据
import cachePolicy from './cache.js';
Vue.use(Service, { cachePolicy });


Vue.use({
    install: function (Vue) {
        Object.defineProperty(Vue.prototype, "window", { get: function () { return window; } }); // loose binding
    }
});

// 不明所以的菜单栏代码，待定是否能砍
// let tm = null;
// let fn = function () {
//     tm = new Date();
//     let path = window.location.hash;
//         path += '$';
//     let items = [].slice.call(document.querySelectorAll(".slideMenu .doubleClick"));
//         items.forEach(it => it.classList.remove('is-active'));


//     items.forEach(it => it.addEventListener('click', e => {
//         let a = document.createEvent('MouseEvent');
//             a.initEvent('click', false, false);
        
//         try {
//             it.firstChild.firstChild.click(a);
//         } catch(e) {

//         }
       
//     }));
//     try {
//         console.log(path)
//         items.forEach(it => {
//             if ((it.firstChild.href + '$').indexOf(path) > -1) {
//                 it.classList.add('is-active');
//                 throw 'break';
//             }
//         });
//     } catch (e) { }
// };
// window.addEventListener('hashchange', fn);
// window.addEventListener('load', fn);




// 权限接口
import AuthAPI from '@/Api/Auth';


new Vue({
    el: '#app',
    router,
    store,
    data: {},
    components: {
        slidemenu,
    },
    created() {
        // 检查登录
        AuthAPI.checkLogin().then((res) => {
            this.$store.commit("updateLogInfo", res.admin);
        });
    }
});

