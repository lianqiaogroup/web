<template>
    <div class="sel-top">
        
        <el-row class="sel-titlesec">
            <div class="sel-title">选择模板</div>
            <el-col :span="3">
                <el-select v-model="sel.style" placeholder="风格类型">
                    <el-option :value="''" :key="'__all__'" :label="'风格类型'">
                        
                    </el-option>
                    <el-option v-for="item in sel.options.style" :value="item" :label="item" :key="item"></el-option>
                </el-select>
            </el-col>
            <el-col :span="3">
                <el-select v-model="sel.scene" placeholder="使用场景">
                    <el-option  :value="''" :key="'__all__'" :label="'使用场景'">
                        
                    </el-option>
                    
                    <el-option v-for="item in sel.options.scene" :value="item" :label="item" :key="item"></el-option>
                </el-select>
            </el-col>
            <el-col :span="3">
                <el-select v-model="sel.attrs" placeholder="模板属性">
                    <el-option v-for="item in sel.options.feature" :value="item" :key="item" :label="item.label">
                        
                    </el-option>
                </el-select>
            </el-col>
            
        </el-row>
        
        
        
        <el-row class="sel-sections">
            <el-col class="sel-left">
                <el-row type="flex">
                    <template v-for="item in sel.themeset.areamodel" >
                    <el-col class="sel-theme" 
                            :key='item.theme' 
                            :data-key='item.theme' 
                            v-if='filterTheme(item, sel)'
                    >
                        <el-row class="img-box">
                            <img :src="item.img">
                        </el-row>
                        <el-row class="sel-name-line">
                            <div class='sel-tag' v-if="item.tag">{{item.tag}}</div>
                            <div class='sel-name'>{{item.title}}</div>
                            <a class='sel-preview' :href="item.referto_links" target='_blank'>预览</a>
                        </el-row>
                        <el-row class="sel-feature-row">
                            <div class="sel-feature">{{item.type_goods.join("+")}}</div>
                        </el-row>
                    </el-col>
                  </template>
                </el-row>
                
            </el-col>
            <el-col class="sel-right">
               <el-row class="sel-stat" type="flex">
                   <div class="sel-stat-single align-left">
                       <div class='title'>模板使用量</div>
                       <div>{{selected_theme.usage || 0}}</div>
                   </div>
                   <div class="sel-stat-single align-center">
                       <div class='title'>模板转化率</div>
                       <div>{{(selected_theme.ratio || 0.00).toFixed(2)}}%</div>
                   </div>
                   <div class="sel-stat-single align-right">
                       <div class='title'>订单成交量</div>
                       <div>{{selected_theme.order || 0}}</div>
                   </div>                   
                </el-row>
                <el-row class="sel-desc-single" v-for="item in selected_theme.info">
                    <el-row class="title">
                        {{item.title}}
                    </el-row>
                    <el-row class="desc">
                        {{item.text}}
                    </el-row>
                </el-row>
                
                <el-row class="sel-button">
                  <el-button type="primary" @click='handleDone()'>
                    使用此模板<i class="material-icons">done</i>
                  </el-button>
                </el-row>
            </el-col> 
        </el-row>
        
       
        
    </div> 
</template>

<script>
     
let _DEF = str => str.split("/").map(_ => _.trim());
const DEFS = {
    "area":      _DEF("日本 / 美国 / 俄罗斯 / 新加坡 / 阿拉伯 / 马来西亚 / 泰国 / 印度尼西亚 / 阿拉伯 / 台湾 / 香港 / 澳门"),
    "scene":     _DEF("数码科技 / 高端珠宝 / 服鞋帽包 / 日用食品 / 儿童用品 / 运动户外 / 乐器 / 美妆护肤香水 / 石器配饰 / 礼品装饰"),
    "feature":   _DEF("轮播图 / 套餐选择 / 倒计时 / 订单查询 / 评论晒图 / 其他用户浏览购买提示"),
    "style":     _DEF("简洁 / 活力/ 大促 / 淡雅"),
    "layout":    _DEF("垂直式页面 / 固定浮层按钮页面"),
};

    
export default {
    data(){
        return {
         sel: {
             themeset:{
                 style: [],
                 scene: [],
                 
             },
                 attrs_options:[
                     {"value": "", "label": "模板属性"},
                     {"value": "货到付款", "label": "货到付款"},
                     {"value": "易极付", "label": "易极付"},
                     {"value": "paypal", "label": "paypal"},
                 ],
             area: "",
             arealang: "",
             typegoods: "",
             attrs: "",
             style: "",
             scene: "",
             pay_type: "",
             
         },
         style: {
            
         },
         theme: "",
         selected_theme: {info: []},
        };
    },
    
    mounted(){
        
        /*this.$http.get("/admin/template/js/jsons/theme").then(function(res){
           this.sel.themeset = JSON.parse(res.data);
           this.sel.area     = this.sel.themeset.area[0];
           this.sel.arealang = this.sel.themeset.arealang[0];
        });*/
        // Todo: 获得模板列表
           this.sel.themeset  = require('../mock/theme.json');
           this.sel.options   = DEFS;
           this.sel.style     = this.sel.themeset.area[0];
           this.sel.scene     = this.sel.themeset.arealang[0];
        
        var self = this;
        
        document.body.addEventListener('click', function(e){
            var target = e.target;
            if(target.tagName === 'A') return;
            while(target != null){
                if(target.classList.contains("sel-theme")){
                    document.querySelectorAll(".sel-theme").forEach( a => a.classList.remove("selected") );
                    target.classList.add("selected");
                    self.handleClick(target.dataset.key);
                    return;
                }
                target = target.parentElement;
            }
        }, true);
    },
    methods:{
        getThemeArgs(theme){
            return {name: theme, usage:1, ratio:Math.random() * 100, order: 2, info: [
                {"title": "适用范围", "text": "这几天心里颇不宁静。今晚在院子里坐着乘凉，忽然想起日日走过的荷塘，在这满月的光里，总该另有一番样子吧。"},
                {"title": "适用范围", "text": "沿着荷塘，是一条曲折的小煤屑路。这是一条幽僻的路；白天也少人走，夜晚更加寂寞。"},                                
                {"title": "适用范围", "text": "月光如流水一般，静静地泻在这一片叶子和花上。薄薄的青雾浮起在荷塘里。叶子和花仿佛在牛乳中洗过一样；又像笼着轻纱的梦。"},
        ]};
        },
        handleDone(){
            Vue.rootHub.$emit("theme.selected", this.selected_theme.name);
            this.clear();
        },
        handleClick(theme){
            this.selected_theme = this.getThemeArgs(theme);
        },
        filterTheme(item, sel){
            //TODO: 改为对应的下标匹配
            return (sel.style == '' || item.regions.indexOf(sel.style) > -1) && (sel.scene == '' || item.lang.indexOf(sel.scene) > -1) && (sel.attrs == '' || item.type_goods.indexOf(sel.attrs) > -1)
        },
        clear(){
            document.querySelectorAll(".sel-theme").forEach( a => a.classList.remove("selected") );
            this.selected_theme = {info: []};
            document.querySelector(".sel-left").scrollTop = 0;
        }
    }
    
}
    
    

</script>

