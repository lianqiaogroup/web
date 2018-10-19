Vue.http.options.headers = {
  'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
};
new Vue({
    el: '#app'
    , data: {
        message: "Hello Vue"
        , uname: ""
        , pwd: ""
        , act: "login"
        , isLogin: false
        , loginLabel: "登录"
        , version: '',
    }
    , methods: {
        onLogin: function(){
            var self = this;
            var email = this.uname;
            var password = this.pwd;
            var act = this.act;
            if( email=="" || password=="" ){ return false; }
            this.isLogin = true;
            this.loginLabel = '登录中';
            var zipFormData = new FormData();
                zipFormData.append('act', act);
                zipFormData.append('email', email);
                zipFormData.append('password', password);
            this.$http.post("/index.php?act=login", zipFormData).then(function(res){
                if( res.body.code == '1' ){
                    window.location.href = '/index.php';
                }else{
                    alert('密码错误');
                    self.isLogin = false;
                    self.loginLabel = '登录';
                }
            });
        }
    }
    , created: function(){
        var self = this;
        this.version =  window.ConfigObject.version;
        document.title = '建站系统v' + window.ConfigObject.version;
        window.ConfigObject.$watch("version", _ => {
            this.version = window.ConfigObject.version;
          })
          this.version = window.ConfigObject.version;
        window.addEventListener('keydown', function(key){
            if( key.keyCode == 13 ){
                self.onLogin();
            }
        });
        var f = function(e){
            for(let div of [].slice.call(document.querySelectorAll(".form-input"))){
                div.classList.remove("focus");
            }
            let obj = e.target;
            if(obj.tagName.toLowerCase() == 'div' && obj.classList.contains('form-input')){
                obj.querySelector("input").focus();
            }
            let input = document.querySelector("input:focus");
            if(input){
                input.parentNode.parentNode.classList.add("focus");
            }
            
        }
        document.addEventListener('keydown', function(e){
            f.call(this, e);
        }, true);
        document.addEventListener('click', function(e){
            f.call(this, e);
        }, true);
        window.addEventListener("load", _ => {
            var el = document.querySelectorAll(".inner");
            el.forEach(el => el.addEventListener('mouseover', function(e){
                el.classList.add('hover');
            }, false));
            el.forEach(el => el.addEventListener('mouseout', function(e){
                el.classList.remove('hover');
            }, false));
            el.forEach(el => el.addEventListener('click', function(e){
                el.closest("div.form-input").click();
            }, false));
            el.forEach(el => el.addEventListener('touchstart', function(e){
                el.closest("div.form-input").click();
            }, false));
        });
    }
});   


document.addEventListener('DOMContentLoaded', function(){
    window.ConfigObject.$watch("version", function(){
        document.title = '建站系统v' + window.ConfigObject.version;
    })
    document.title = '建站系统v' + window.ConfigObject.version;
}, false);