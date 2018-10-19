import qs from 'qs';

let _isArray = function(a){
    return ({}).toString.call(a) === "[object Array]";
};

class ServicePromise {
    constructor(promise, object){
        this.promise = promise;
        this.object  = object;
    }
    then(...args){
        return new ServicePromise(this.promise.then(...args), this.object);
    }
    catch(...args){
        return new ServicePromise(this.promise.catch(...args), this.object);
    }
    after(arr){
        let a = this;
        for(let item of arr){
            if(!_isArray(item)) item = [item];
            a = a.then(item[0], item[1]);
        }
        return new ServicePromise(a, this.object);
    }
    toSP(object){
        return new ServicePromise(this.promise, this.object);
    }
    static extend(obj){
        return Object.assign(ServicePromise.prototype, obj);
    }
    submitMessage(succ, fail){
        let vnode = this.object;
        return this.then(function(res){
            if(res.body.ret == 1){
               vnode.$message({
                    message: succ || res.body.msg,
                    type: 'success'
               });
            }else{
                vnode.$message({
                    message: fail || res.body.msg,
                    type: 'error'
               });
            }
            return res;
        });
    }
};
let {Promise} = window;
Promise.prototype.toSP = function(vnode){ return new ServicePromise(this, vnode); }

class ServiceBuilder{
    constructor(service){
        this.service = service;
    }   

    after(f){
        this.service._afterList.push(f);
    }

    get(name, url, paramFunc, opt){
        this.service.slots[name] = Object.assign({
              type: 'get'
            , url
            , paramFunc: paramFunc || function(){}
        }, opt);
    }
    routeTo(name, url, paramFunc, opt){
        this.service.slots[name] = Object.assign({
              type: 'routeTo'
            , url
            , paramFunc: paramFunc || function(){}
        }, opt);
    }
    navigate(name, url, paramFunc, opt){
        this.service.slots[name] = Object.assign({
              type: 'navigate'
            , url
            , paramFunc: paramFunc || function(){}
        }, opt);
    }
    post(name, url, paramFunc, opt){
        this.service.slots[name] = Object.assign({
              type: 'post'
            , url
            , paramFunc: paramFunc || function(){}
        }, opt);
    }
    post(name, url, paramFunc, opt){
        this.service.slots[name] = Object.assign({
              type: 'post'
            , url
            , paramFunc: paramFunc || function(){}
        }, opt);
    }
    postraw(name, url, paramFunc, opt){
      this.service.slots[name] = Object.assign({
              type: 'postraw'
            , url
            , paramFunc: paramFunc || function(){}
        }, opt);
    }
    jsonp(name, url, callbackName, paramFunc, opt){
        this.service.slots[name] = Object.assign({
              type: 'jsonp'
            , url
            , callback: callbackName
            , paramFunc: paramFunc || function(){}
        }, opt);
    }
    pure(name, func, opt){
        this.service.slots[name] = Object.assign({
              type: 'pure'
            , func
        }, opt);
    }
}
class Service{
    constructor(){
        this.cachePolicy = null;
        this.slots       = {};
        this.mapping = new Map();
        this._afterList  = [];
    }
    getBuilder(){
        return new ServiceBuilder(this);
    }
    install(Vue, options){
        let self = this;
        if(options.cachePolicy) this.cachePolicy = options.cachePolicy;

        // Vue.use(Service, {experiment: true})
        if(options.experiment){
            Vue.mixin({
                mounted(){
                    if(this.get){
                        //自动get，当做顶级组件
                        this.$service("indexData").then(res => {
                            this.is_admin = res.body.admin.is_admin;
                            this.admin    = res.body.admin;
                        });
                        this.get && this.get();
                    }
                }
            })
        }
        if(options.debug){
            Vue.mixin({
                mounted(){
                    if(this.get){
                        let createElement = (html, parent = document.body) => parent.appendChild(new DOMParser().parseFromString(html, 'text/html').body.firstChild);
                        this._style = createElement("<body><div style='display: block; position: relative; border: 2px solid blue; border-radius: 4px;left: 250px; width: 100%; height: 600px;'><textarea></textarea></div></body>");
                        this._styleEl = createElement("<body><style></style></body>")
                        this._styles = [createElement("<body><link href='https://unpkg.com/codemirror/lib/codemirror.css' rel='stylesheet' type='text/css'></link></body>")]


                        this._styles.forEach(document.body.appendChild.bind(document.body))
                        this._req    = document.createElement("script");
                        this._req.src = 'https://unpkg.com/requirejs/require.js';
                        this._req.onload = function(){
                            window.require.config({
                                paths:{
                                    'codemirror/lib': 'https://unpkg.com/codemirror/lib/',
                                    'codemirror/mode': 'https://unpkg.com/codemirror/mode/',
                                }
                            })
                            window.require(['codemirror/lib/codemirror', 'codemirror/mode/css/css'], (cm) => {
                                window.CodeMirror = cm;
                                this._editor = cm.fromTextArea(this._style.firstChild, {mode: 'css', lineNumbers: true});
                                this._editor.on('change', (e) => this._styleEl.innerText = e.getValue().replace(/<BR>/g, ""));
                            });
                        }.bind(this);
                        document.body.appendChild(this._req);
                    }

                },
                beforeDestroy(){
                    if(this.get){
                        
                        this._req && document.body.removeChild(this._req);
                        this._styleEl && document.body.removeChild(this._styleEl);
                        this._style && document.body.removeChild(this._style);
                        this._styles.forEach(document.body.removeChild.bind(document.body))
                    }
                }
            })
        }

        /*
        Vue.mixin({
            mounted(){
                //self.mapping.set(this.$el, this);
            },
            beforeDestroy(){
                if(self.mapping.get(this.$el) == this){
                    self.mapping.delete(this.$el);
                }
            }
        });
        */
        Vue.element = Vue.prototype.$element = function(node){
            while(node){
                if(!self.mapping.get(node)){
                    node = node.parentNode;
                }else{
                    return self.mapping.get(node);
                }
            }
            return undefined;
        };

        Vue.service = Vue.prototype.$service = function(name, ...args){
            if(arguments.length === 0){
                return self;
            }
            let handler = self.slots[name];
            if(!handler) throw `unknown service: ${name}`;
            let type = handler.type;
            if(!self[type]) throw `unknown type ${name}`
            let argparam, key;
            if(handler.paramFunc){
                argparam = handler.paramFunc.call(this, ...args);
                key = args;
            }else{
                key = argparam = args;
            }
            if(handler.type !== 'pure' && handler.nocache && typeof handler.nocache.test === 'function'){
               self.cachePolicy && self.cachePolicy.invalidate(handler.nocache);
            }
            if(handler.type !== 'pure' && self.cachePolicy && !(handler && handler.nocache)){
                let after = handler && handler.after || (x => x);
                return Promise.resolve(self.cachePolicy.get(name + "." + JSON.stringify(key), self[type].bind(self, handler, this, argparam)).toSP(this)).then(after);
            }else{
                let after = handler && handler.after || (x => x);
                if(handler.type == 'pure'){
                    return self[type].bind(self, handler, this, argparam)()
                }else{
                    return self[type].bind(self, handler, this, argparam)().then(after);
                }
                
            }
        }
        Vue.serviceAsync = Vue.prototype.$serviceAsync = Vue.service; // func returning promise is already async-ed
    }

    invalidate(pattern){
        this.cachePolicy && this.cachePolicy.invalidate(pattern);
    }

    get(handler, vnode, args){
        return new ServicePromise(vnode.$http.get(handler.url, {params: args}), vnode);
    }

    
    post(handler, vnode, args){
        return new ServicePromise(vnode.$http.post(handler.url, qs.stringify(args, { arrayFormat: 'brackets' })), vnode);
    }
    postraw(handler, vnode, args){
        return new ServicePromise(vnode.$http.post(handler.url, qs.stringify(args, { arrayFormat: 'brackets' }), {responseType: 'text'}), vnode);
    }

    postjson(handler, vnode, args){
        return new ServicePromise(vnode.$http.post(handler.url, args), vnode);
    }
    jsonp(handler, vnode, args){
        return new ServicePromise(vnode.$http.jsonp(handler.url, {params: args, jsonp: handler.callback}), vnode);
    }
    pure(handler, vnode, args){
        return handler.func.call(vnode, ...args);
    }
    routeTo(handler, vnode, args){
        console.log(handler, args);
        return new Promise((ac, re) => {
            vnode.$router.push({
                path: handler.url.replace(/:([A-Za-z0-9_]+)/g, function(_, key){return args[key]}),
                params: args,
            })
        }).toSP(vnode); 
    }
    navigate(handler, vnode, args){
        var query = [];
        for(let key of Object.keys(args)){
            query.push(encodeURIComponent(key) + '=' + encodeURIComponent(args[key]));
        }
        let url = handler.url;
        if(query.length){
            url = url + "?" + query.join("&");
        } 
        if(handler.blank){
            /*
            let a = document.createElement("a");
            a.href = url;
            a.target = "_blank";
            a.click();
            */
            return new Promise((ac, re) => {
                let win = window.open(url, "_blank");
                let lis =  _ => {
                    clearInterval(listen);
                    win.removeEventListener('beforeunload', lis);
                    setTimeout(_ => ac(url), 0);
                };
                let listen = setInterval(function(){
                    if(win.closed){
                        lis();
                    }
                }, 100);
               
                win.addEventListener('beforeunload', lis);
            });
        }else{
            window.location = url;
        }
        return Promise.resolve(url).toSP();
    }
}


import Cache from './cache.js'
let DefaultService = new Service(Cache);
let service = new ServiceBuilder(DefaultService);

service.get('indexData', '/indexData.php');
service.jsonp('chartData', 'http://120.77.200.250/api.php', 'callback', _ => ({
   act: 'get'
   , type: 'all'
   , day: new Date().getDate() - 1
}));

service.get('product#list', '/product_new.php', ($keyword, $keywordValue, $is_del, $page) => ({
    [$keyword]: $keywordValue,
    is_del: $is_del ? '1' : '0',
    json: '1',
    p: $page
}), {
    after(res){
        if(res.body.res == "succ"){
            res.body.goodsList.forEach(product => {
                if(product.thumb && product.thumb.endsWith("-shop")){
                    product.thumb+="80";
                }
            });
        }
        return res;
    }, 
    nocache: true,
});

service.post('product#copy', '/product.php', ({domain, product_id,  id_zone, zone, identity_tag, lang, theme, is_comment}) => ({
    domain
    , product_id
    , id_zone
    , zone
    , act: 'copy'
    , identity_tag
    , lang
    , theme
    , is_comment
}), {nocache: /^\.product#/});
service.get('productL#getAUser', '/product.php', _ => ({act: 'getAUser'}))
service.get('productL#getAdUser', '/product.php', _ => ({act: 'getAdUser'}))
service.get('productL#getAllZone', '/product.php', _ => ({act: 'getAllZone'}))

service.get('order#list', '/order.php', (order_status, erp_status, start_time, end_time, key, val, p) => ({
    json: '1'
    , p
    , order_status
    , erp_status
    , start_time
    , end_time
    , [key]: val,
}));

service.navigate('product#new', '/product.php', _ => ({
    act: 'edit'
}), {
    nocache: /^\.product#/
    , blank: true  //在新页面打开
});

service.navigate('product#newerp', '/product_new.php', _ => ({
    act: 'edit'
}), {
    nocache: /^\.product#/
    , blank: true  //在新页面打开
});

/*
service.navigate('product#new', '#/products/new', _ => ({
}), {
    nocache: /^\.product#/
}); 
*/



// 编辑产品
service.navigate('product#edit', '/product.php', (product_id) => ({
    act: 'edit'
    , product_id
}), {
    nocache: /^\.product#/
    , blank: true  //在新页面打开
});
// 编辑产品-新（对接erp）
service.navigate('product#editerp', '/product_new.php', (product_id) => ({
    act: 'edit'
    , product_id
}), {
    nocache: /^\.product#/
    , blank: true  //在新页面打开
});

service.post('product#delete', '/product.php', (product_id) => ({
    act: 'del'
    , product_id
    , is_del: '1'
}), {nocache: /^\.product#/});

service.post('product#resume', '/product.php', (product_id) => ({
    act: 'del'
    , product_id
    , is_del: '0'
}), {nocache: /^\.product#/});

let uniqStr = function(strs){
  let obj = {};
  strs.forEach( x => obj[x] = 1);
  return Object.keys(obj);
};


service.post('product#transferDomain', '/product.php', (domain, id_department) => ({
     act: 'departmentChange'
   , id_department
   , domain: uniqStr(domain)
}), {nocache: /^\.product#/});

service.post('product#transferDepartment', '/product.php', (products, id_department) => ({
     act: 'productDepartChange'
   , id_department
   , products: uniqStr(products)
}), {nocache: /^\.product#/});


service.get(    'user#list',   '/user.php', p => ({
    json: '1',
    p,
}));



service.post(   'user#delete', '/user.php', uid => ({
    uid
    ,  act: 'delete'
    ,  is_del: 1
}), {nocache: /^\.user#/});

service.post(   'user#resume', '/user.php', uid => ({
    uid
    ,  act: 'delete'
    ,  is_del: 0
}), {nocache: /^\.user#/});

service.post(   'user#save',  '/user.php', (uid, password, user_group_id, user_name, email) => ({
        uid
        , password
        , user_group_id
        , user_name
        , email
}), {nocache: /^\.user#/});

service.navigate(  'user#add', '#/user/edit', _ => ({}), {
    nocache: /^\.user#/, 
});
service.post(  'user#save', '/user.php?act=save', ({uid, username, user_group_id, email, password}) => ({uid, username, user_group_id, email, password}), 
{
    nocache: /^\.user#/, 
});
service.routeTo('user#edit', '/user/edit/:uid', uid => ({
    uid: uid || ""
}), {
    nocache: /^\.user#/
});

service.pure('$', function(id){
    if(document.getElementById(id)) return () => document.getElementById(id);
    let $ = parent => (tag, attr, fn) => {
        if(tag){
            let node = Object.assign(document.createElement(tag), attr);
            fn && fn($(node));
            (parent || document.body).appendChild(node);
            return node;
        }else{
            return fn && fn(parent);
        }
    };
    return $;
});

service.get(    'usergroup#list',   '/userGroup.php', p => ({
    json: '1',
    p,
}));

service.navigate(  'usergroup#add', '#/usergroup/edit', _ => ({}), {
    nocache: /^\.usergroup#/, 
});

service.post(   'usergroup#save',    '/userGroup.php?act=save', (info, user_group_id)=>{
    let title  = info.map(x => x.title),
        remark = info.map(x => x.remark);
    if(user_group_id){
        return {id: user_group_id, title: title[0], remark: remark[0]};
    }else{
        return {title, remark};
    }
},  {nocache: /^\.usergroup#/});

service.routeTo(  'usergroup#edit', '/usergroup/edit/:user_group_id', user_group_id => ({user_group_id: user_group_id || ""}), {
    nocache: /^\.usergroup#/, 
}); 

/*
service.post(   'usergroup#add',    '/userGroup.php?act=save', info=>{
    let title  = info.map(x => x.title),
        remark = info.map(x => x.remark);
    return {title, remark};
},  {nocache: /^\.usergroup#/});

*/
let getDate = function(val){
    let tm = val instanceof Date ? val : Date.parse(val);
    let options = {
        month: '2-digit', day: '2-digit',
    };
    return tm.getFullYear()+ "-" + new Intl.DateTimeFormat('en-US', options).format(tm).replace("/", "-").replace(",", "");
};
service.pure('getDate', getDate);

function formatDateTime(val){
        try{
            let tm = val instanceof Date ? val : Date.parse(val);
            let options = {
                month: '2-digit', day: '2-digit',
                hour: '2-digit', minute: '2-digit', 
                hour12: false
            };
            return new Intl.DateTimeFormat('en-US', options).format(tm).replace("/", " - ").replace(",", "").replace(" ", "\n").replace(":", " : ");
        }catch(e){
            return "";
        }
}





function formatDateTimeRaw(val){
    function padStart(str, len){
        str = str.toString();
        if(str.length < len){
            if(len - str.length == 1) return "0" + str;
            return Array.apply(null, Array(len - str.length)).map(_ => "0").join("") + str;
        }
        return str;
    }
    try{
        let tm = val instanceof Date ? +val : Date.parse(val);
        if(isNaN(tm)){
            return "";
        }
        tm = new Date(tm);
        let date = [padStart(tm.getFullYear(), 4), padStart(tm.getMonth() + 1, 2), padStart(tm.getDate(), 2)].join("-");
        let time = [padStart(tm.getHours(), 2), padStart(tm.getMinutes(), 2), padStart(tm.getSeconds(), 2)].join(":");
        return [date, time].join(" ");
    }catch(e){
        console.log(e);
        return "";
    }
}
service.pure('dateTimeFormatShort', formatDateTime);
service.pure('|>date', function(val){
    try{
        let tm = val instanceof Date ? val : Date.parse(val);
        let options = {
            month: '2-digit', day: '2-digit',
        };
        return new Intl.DateTimeFormat('en-US', options).format(tm).replace("/", " - ").replace(",", "").replace(" ", "\n").replace(":", " : ");
    }catch(e){
        return "";
    }
});
service.pure('|>time', function(val){
    try {
        let tm = val instanceof Date ? val : Date.parse(val);
        let options = {
            hour: '2-digit', minute: '2-digit', 
            hour12: false
        };
        return new Intl.DateTimeFormat('en-US', options).format(tm).replace("/", " - ").replace(",", "").replace(" ", "\n").replace(":", " : ");
    }catch(e){
        return "";
    }
});

service.pure('ratio', function(a, b){
    if(Math.abs(+b) < 1e-6){
        return " -- ";
    }
    return (+a * 100 / +b).toFixed(2) + '%';
}, {nocache: true});
service.get('category#preview', '/category.php', (domain, p, id)=>({
  act: 'preview',
  category_id: id,
  p,
  domain 
}));

DefaultService.ALL = Symbol("ALL");
service.pure('table#tag', function(a, b){
    return uniqStr((a || []).map(x => new String(x[b]))).map(x => ({text: x, value: x}));
}, {nocache: true});

service.pure('table#filterTag', function(tag) {
          return function(value, row){
                return value === DefaultService.ALL || row[tag] === value;
        }
}, {nocache: true});
let tongji_get_date = function(obj){
    if(obj.v_day_num == 0){
        return Object.assign({}, obj, {v_date: getDate(new Date())});
    }else{
        return obj;
    }
}

service.get('tongji#zone', '/tongji.php', v_day_num => tongji_get_date({
    act: 'zone',
    v_day_num,
}));
service.get('tongji#device', '/tongji.php', v_day_num => tongji_get_date({
    act: 'device',
    v_day_num,
}));
service.get( 'tongji#product', '/tongji.php', (search, v_day_num, v_time_type) =>tongji_get_date({
    act: 'product',
    search,
    v_day_num,
    v_time_type,
}));
service.get( 'tongji#domain', '/tongji.php', (search, v_day_num, v_time_type) => tongji_get_date({
    act: 'host',
    search,
    v_day_num,
    v_time_type,
}));
service.get( 'tongji#leftBox', '/tongji.php', (v_day_num, v_time_type) => tongji_get_date({
    act: 'leftBox',
    v_day_num,
    v_time_type,
}));

service.pure('inputbox#icon', val => val ? 'close' : 'search', {nocache: true});
service.pure('inputbox#click', function(key){
    return function(){
        this[key] && (this[key] = '') 
    }.bind(this);
}, {nocache: true});


service.post('site_product#add', '/site_product.php?act=save', (domain, {product_id, thumb}, sort) => ({
    act: 'save',
    domain, 
    product_id, 
    sort,
    is_del: 0,
    thumb
}), {nocache: /^\.site_product#/});


service.post('site_product#save', '/site_product.php', (domain, {product_id, thumb, sort, id}) => ({
    act: 'save',
    domain, 
    product_id, 
    sort,
    is_del: 0,
    sid: id,
    thumb
}),{nocache: /^\.site_product#/});

service.get('site_product#delete', '/site_product.php', (domain, sid) => ({
    act: 'delete',
    domain, 
    is_del: 1,
    sid,
}),{nocache: /^\.site_product#/});

service.get('site_product#resume', '/site_product.php', (domain, sid) => ({
    act: 'delete',
    domain, 
    is_del: 0,
    sid,
}),{nocache: /^\.site_product#/});

service.get('site_product#list', '/site_product.php', (domain, p) => ({
    domain, 
    p
}));


service.get('category#list', '/category.php', (domain, p) => ({
   domain,
   p
}));


service.post('admin#setAdmin', '/admin_set.php?act=admin_save', (username, isAdmin) => ({
    username,
    is_admin: isAdmin ? 1 : 0,
}), {nocache: /^\.admin#/});


service.get('admin#get', '/admin_set.php', (uname, cname, dname, is_admin, p) => ({
    act: 'admin_list',
    username: uname,
    name_cn: cname,
    department: dname,
    is_admin: is_admin ? 1 : 0,
    p
}))

service.get('admin#adminlist', 'admin_set.php', () => ({
    act: 'select_admin',
}))

service.get('adminlog#get', '/admin_set.php', (start_time, end_time, p, loginid, act_loginid, name_cn) => 
({start_time: formatDateTimeRaw(start_time), end_time: formatDateTimeRaw(end_time), p, loginid, act_loginid, name_cn, act: 'admin_log'}));
/*
let categoryList = null;
service.pure('category#list', function(){
  return Promise.resolve(categoryList = categoryList || {body: [
{
"cid":1,
"title":"test",
"parent_id":0,
"sort":0,
"is_del":0
},
{
"cid":2,
"title":"hello",
"parent_id":1,
"sort":1,
"is_del":1
},
{
"cid":3,
"title":"3",
"parent_id":0,
"sort":3,
"is_del":1
},
{
"cid":4,
"title":"11",
"parent_id":3,
"sort":111,
"is_del":1
}
]})
}, {nocache: true});
*/
service.get('category#delete', '/category.php',  (domain, {id})=> ({
   domain,
   cid: id, 
   is_del: 1,
   act: 'delete'
}), {nocache: /^\.category#/});
service.get('category#resume', '/category.php', (domain, {id}) => ({
   domain,
   cid: id, 
   is_del: 0,
   act: 'delete'
}), {nocache: /^\.category#/});
service.post('category#add', '/category.php', (domain, {title, parent_id, sort, title_zh}) => ({
   title,
   parent_id: parent_id || 0,
   sort,
   domain,
   is_del: 0,
   title_zh,
   act: 'save'
}), {nocache: /^\.category#/});

service.post('category#edit', '/category.php', (domain, {id, title, parent_id, sort, title_zh}) => ({
   title,
   parent_id: parent_id || 0,
   sort,
   cid: id,
   domain,
   act: 'save',
   title_zh,
}), {nocache: /^\.category#/});

service.get('index_focus#list', '/index_focus.php', (domain, p) => ({
    domain,
    p,
    json: '1'
}));

service.get('BIlink#list', '/product_new.php', (product_id, id, sname, p) => (id ? {
    id,
    product_id,
    // ad_group: gname,
    ad_channel: sname,
    act: 'getBILink',
    p
} : {
    product_id,
    // ad_group: gname,
    ad_channel: sname,
    act: 'getBILink',
    p
}))

service.post('BIlink#save', '/product_new.php?act=saveBILink', (product_id, data, id) => (Object.assign({}, {
    product_id,
}, data, id ? {id} : {})), {nocache: /^\.BIlink#/} );

service.post('BIlink#extdata', 'product_new.php?act=getProductExtData', (product_id, oa_id_department, ad_member) => ({
    product_id,
    oa_id_department,
    ad_member,
}))

service.get('country#list', '/country.php', () => ({
  act: 'getZone',
}));

service.post('pay#save', '/payment.php?act=save', (obj, keys, code) => {
    let ret = {id: obj.payment_id || obj.id || "", domain: obj.domain || "", code};
    keys.forEach(y => ret[y.name] = obj[y.name]);
    return ret;
}, {nocache: /^\.pay#/});

service.get('pay#get', '/payment.php', code => ({
  code,
  act: 'getPay',
}));

service.get('pay#list', '/payment.php');


service.post('index_focus#save', '/index_focus.php?act=save&json=1', ({domain, title, product_id, id, sort, desc, thumb, is_del}) => ({
    domain,
    id: id || 0,
    product_id,
    is_del,
    sort,
    desc,
    thumb,
    title,
    upfile: '',
    act: 'save',
}), {nocache: /^\.index_focus#/});  


 const ORDER_STATUS = function(name){
     return {
         "SUCCESS": "支付成功",
         "FAIL":    "支付失败",
         "NOT_PAID": "下单未支付",
     }[name];
 };

 const ERP_STATUS = function(name){
     return {
         "SUCCESS": "回调通信成功",
         "FAIL":    "回调通信失败",
         "CREATE_FAIL": "创建订单失败",
         "FAIL_CREATE": "创建订单失败",
     }[name];
 };

 service.pure('erp_status', ERP_STATUS);
 service.pure('order_status', ORDER_STATUS);
 
if(window){
    window.addEventListener('load', function(){        
        window.addEventListener('beforeunload', function(){
            DefaultService.cachePolicy && DefaultService.cachePolicy.invalidate(/.*/);
        });


    });
    let XHR = window.XMLHttpRequest.prototype;
    let oldsend = XHR.send;
    XHR.send = function(){
        let oldreadystate = this.onreadystatechange;
        
        this.onreadystatechange = function(){
            if(this.readyState == 4 && this.status == 200){
                var judge = this.responseURL.split("/")[3];
                if(this.responseText.match(/<!DOCTYPE html>/) || this.responseText.match(/\{"session":0\}/)){
                    console.log(judge)
                    if (judge.indexOf('postEditStsite') > -1 || judge.indexOf('postAddStsite') > -1 || judge.indexOf('article') > -1){
                        alert("登录状态超时，请先在新的页面中登录后，在原来的界面进行操作。");
                        window.open(window.location.href);
                    }else{
                        window.location.reload();
                    }
                    
                }
                oldreadystate && oldreadystate.apply(this, arguments);
            }
        }
        return oldsend.apply(this, arguments);
    }

}


export default DefaultService;


