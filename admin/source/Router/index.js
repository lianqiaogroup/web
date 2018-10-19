// 外部插件
let { Vue, VueRouter } = window;

Vue.use(VueRouter);

// 首页
import index from '@/vue/index/index.vue';

// 产品模块
import products from '@/vue/product/list.vue';
const adlink = resolve => require(['@/vue/product/BI-adlink.vue'], resolve);

// 主页管理模块
const sites = resolve => require(['@/vue/sites/sites.vue'], resolve);
const sites_show = resolve => require(['@/vue/sites/show.vue'], resolve);
const sites_del = resolve => require(['@/vue/sites/del.vue'], resolve);
const sites_setting = resolve => require(['@/vue/sites/edit/settings.vue'], resolve);

const article_new = resolve => require(['@/vue/article/new.vue'], resolve);
// 评论模块
const comments = resolve => require(['@/vue/comment/list.vue'], resolve);
const comments_new = resolve => require(['@/vue/comment/new.vue'], resolve);
// 订单模块
const orders = resolve => require(['@/vue/order/list.vue'], resolve);
const order_details = resolve => require(['@/vue/order/details.vue'], resolve);
// 地区管理模块
const country = resolve => require(['@/vue/country/list.vue'], resolve);
const country_areacode = resolve => require(['@/vue/country/area_code.vue'], resolve);
// 货币模块
const currency = resolve => require(['@/vue/currency/list.vue'], resolve);
// 支付模块
const pay = resolve => require(['@/vue/pay/list.vue'], resolve);
const pay_edit = resolve => require(['@/vue/pay/new.vue'], resolve);
// 短信模块
const sms_isp_states = resolve => require(['@/vue/sms/isp_states.vue'], resolve);
const sms_isp_edit = resolve => require(['@/vue/sms/isp_edit.vue'], resolve);
const sms_states_list = resolve => require(['@/vue/sms/states_list.vue'], resolve);
const sms_states_edit = resolve => require(['@/vue/sms/states_edit.vue'], resolve);
// 主题管理
const themes = resolve => require(['@/vue/themes/list.vue'], resolve);
const themes_edit = resolve => require(['@/vue/themes/edit.vue'], resolve);
// 主页模板管理
const shop_themes = resolve => require(['@/vue/shopthemes/list.vue'], resolve);
const shop_themes_edit = resolve => require(['@/vue/shopthemes/edit.vue'], resolve);
// DIY模版
const themeDiy = resolve => require(['@/vue/themeDiy/index.vue'], resolve);
//操作日志
const olog = resolve => require(['@/vue/operationlog/list.vue'], resolve);
const userlog = resolve => require(['@/vue/userlog/list.vue'], resolve);
//cloak关联管理
const cloak = resolve => require(['@/vue/cloak/list.vue'], resolve);

//报表统计模块
const reportStatistics = resolve => require(['@/vue/reportStatistics/list.vue'],resolve);

//跨系统复制站点
const copySite = resolve => require(['@/vue/copySite/list.vue'], resolve);

// 分析模块
const analytics = resolve => require(['@/vue/analytics/index.vue'], resolve);

//软文站管理模块
const advert = resolve => require(['@/vue/advert/index.vue'],resolve);
const advert_new = resolve => require(['@/vue/advert/new.vue'],resolve);

// fmp 模块
const fmp_list = resolve => require(['@/vue/fmp/List.vue'], resolve);
const fmp_add = resolve => require(['@/vue/fmp/Add.vue'], resolve);

// 用户管理模块
const user = resolve => require(['@/vue/user/list.vue'], resolve);
const user_edit = resolve => require(['@/vue/user/edit.vue'], resolve);

//素材库
const material = resolve => require(['@/vue/material/list.vue'],resolve);
const material_updata = resolve => require(['@/vue/material/updata.vue'],resolve);
const tag = resolve => require(['@/vue/material/tag.vue'],resolve);




const routes = [
    { path: '/', component: index }
    , { path: '/products', component: products }
    , { path: '/orders', component: orders }
    , { path: '/orders/:id', component: order_details }
    , { path: '/country', component: country }
    , { path: '/country/area_code', component: country_areacode }
    , { path: '/currency', component: currency }
    , { path: '/comments', component: comments }
    , { path: '/comments/new', component: comments_new }
    , { path: '/comments/new/:id', component: comments_new }
    , { path: '/advert', component: advert }
    , { path: '/advert/new', component: advert_new }
    , { path: '/advert/new/:id', component: advert_new }
    , { path: '/sites', component: sites }
    , { path: '/sites/edit', component: sites_setting }
    , { path: '/sites/edit/:domain', component: sites_setting }
    , { path: '/sites/show/:domain', component: sites_show }
    , { path: '/sites/del', component: sites_del }
    , { path: '/article/new', component: article_new }
    , { path: '/article/new/:domain', component: article_new }
    , { path: '/article/edit/:id', component: article_new }
    , { path: '/pay', component: pay }
    , { path: '/pay/edit', component: pay_edit }
    , { path: '/pay/edit/:id', component: pay_edit }
    , { path: '/sms/isp_states', component: sms_isp_states }
    , { path: '/sms/isp_edit', component: sms_isp_edit }
    , { path: '/sms/isp_edit/:id', component: sms_isp_edit }
    , { path: '/sms/states_list', component: sms_states_list }
    , { path: '/sms/states_list/:id', component: sms_states_list }
    , { path: '/sms/states_edit', component: sms_states_edit }
    , { path: '/sms/states_edit/:id', component: sms_states_edit }
    , { path: '/themes', component: themes }
    , { path: '/themes/new', component: themes_edit }
    , { path: '/themes/edit/:id', component: themes_edit }
    , { path: '/shopThemes', component: shop_themes }
    , { path: '/shopThemes/new', component: shop_themes_edit }
    , { path: '/shopThemes/edit/:id', component: shop_themes_edit }
    , { path: '/themeDiy/:id', component: themeDiy }
    , { path: '/user', component: user }
    , { path: '/user/:id', component: user_edit }
    , { path: '/operationlog', component: olog }
    , { path: '/userlog', component: userlog }
    , { path: '/cloak', component: cloak }
    , { path: '/product/adlink/:id', component: adlink }
    , { path: '/reportStatistics', component:reportStatistics }
    , { path: '/copySite', component: copySite }
    , { path: '/analytics', component: analytics }
    , { path: '/fmp/list', component: fmp_list }
    , { path: '/fmp/add', component: fmp_add }
    , { path: '/material', component: material }
    , { path: '/material/updata', component: material_updata }
    , { path: '/tag', component: tag }
];


export default new VueRouter({ routes })