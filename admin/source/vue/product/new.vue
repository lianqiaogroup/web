<template>
    <div id="page-product-new">
        <div class="title-bar header-panel">
            <div class="control">
        <el-row class="title">
            新增产品
        </el-row>
        <router-link  to="/products" class="link">
        <el-row>
            返回列表
        </el-row>
        </router-link>
                </div>
        </div>
        <el-row class="content" :gutter="20">
            <el-col :span="16" class="left-panel">
                <el-col :span="24" class="panel">
                    <el-form class="form" id="erp">
                        <el-row class="form-row" :gutter="20">
                          <el-col :span="12">
                            <el-form-item label="ERP域名" class="form-caption">
                                <div v-on:keyup.enter='handleERPSearchDomain'>
                                    <el-input v-model="product.domain" required placeholder="不加 http://" class="form-input"></el-input>
                                </div>
                            </el-form-item>
                          </el-col>
                            
                          <el-col :span="12">
                            <el-form-item label="ERPID" class="form-caption">
                                <div v-on:keyup.enter='handleERPSearch'>
                                    <el-input v-model="product.erp_number" required class="form-input"></el-input>
                                </div>
                            </el-form-item>
                          </el-col>
                        </el-row>
                        
                        <el-row class="form-row" :gutter="20">
                          <el-col :span="24">
                            <el-form-item label="ERP产品名称" class="form-caption">
                            <el-input v-model="product.title" required placeholder=""></el-input>
                            </el-form-item>
                          </el-col>
                        </el-row>
                    </el-form>
                
                   <el-form class="form" id="seo">
                        <el-row class="form-row" :gutter="20">
                          <el-col :span="12">
                            <el-form-item label="SEO标题" class="form-caption">
                            <el-input v-model="product.seo_title" required placeholder="" class="form-input"></el-input>
                            </el-form-item>
                          </el-col>
                            
                          <el-col :span="12">
                            <el-form-item label="SEO描述" class="form-caption">
                            <el-input v-model="product.seo_description" required class="form-input"></el-input>
                            </el-form-item>
                          </el-col>
                        </el-row>
                    </el-form>
                    
                     <el-form class="form"  id="product">
                        <el-row class="form-row" :gutter="20">
                          <el-col :span="12">
                            <el-form-item label="二级目录" class="form-caption">
                            <el-input v-model="product.category2" required  placeholder="" class="form-input"></el-input>
                            </el-form-item>
                          </el-col>
                            
                          <el-col :span="12">
                            <el-form-item label="选择优化师" class="form-caption">
                                <el-select v-model="product.user_id" required class="form-input">
                                    <el-option v-for="(value, item_key) in product.users" :key="item_key" :value="item_key" :label="value"></el-option>
                                </el-select>
                            </el-form-item>
                          </el-col>
                        </el-row>
                         
                         <el-row class="form-row" :gutter="20">
                          <el-col :span="12">
                            <el-form-item label="联系邮箱" class="form-caption">
                            <el-input v-model="product.email" type="email" required placeholder="" class="form-input"></el-input>
                            </el-form-item>
                          </el-col>
                            
                          <el-col :span="12">
                            <el-form-item label="产品外文名称" class="form-caption">
                            <el-input v-model="product.fname" class="form-input"></el-input>
                            </el-form-item>
                          </el-col>
                        </el-row>
                         
                         <el-row class="form-row" :gutter="20">
                          <el-col :span="12">
                            <el-form-item label="POP800 ID" class="form-caption">
                            <el-input v-model="product.pop800_id" required placeholder="" class="form-input"></el-input>
                            </el-form-item>
                          </el-col>
                            
                          <el-col :span="12">
                            <el-form-item label="FB通用像素ID" class="form-caption">
                            <el-input v-model="product.fb_px" class="form-input"></el-input>
                            </el-form-item>
                          </el-col>
                        </el-row>
                    </el-form>
                    
                    <el-form class="form"  id="locale">
                        <el-row class="form-row" :gutter="20">
                          <el-col :span="12">
                            <el-form-item label="选择地区" class="form-caption">
                            <el-select v-model="product.zone_id" required  placeholder="" class="form-input">
                                <el-option v-for="item in product.zones" :key="item.id_zone" :value="item.id_zone" :label="item.title"></el-option>
                            </el-select>
                            </el-form-item>
                          </el-col>
                            
                          <el-col :span="12">
                            <el-form-item label="选择语言" class="form-caption">
                            <el-select v-model="product.lang" required class="form-input"></el-select>
                            </el-form-item>
                          </el-col>
                        </el-row>
                         
                         <el-row class="form-row" :gutter="20">
                          <el-col :span="12">
                            <el-form-item label="前台显示模板" class="form-caption">
                                
                            </el-form-item>
                          </el-col>
                        </el-row>
                        <el-row class="form-row" :gutter="20">
                            <el-col :span="12">
                                <el-button v-model="product.theme" required placeholder="" class="form-input" @click='handleSelectTemplate' icon="el-icon-arrow-right">选择模板</el-button>
                            </el-col>
                        </el-row>
                        <el-dialog :visible.sync="selectTemplate">
                            <selectTemplate ref='selectTemplate'></selectTemplate>
                        </el-dialog>
                        
                    </el-form>
                    
                    <el-form class="form"  id="params">
                        <el-row class="form-row" :gutter="20">
                          <el-col :span="6">
                            <el-form-item label="售价" class="form-caption">
                            <el-input v-model="product.price" required placeholder="" class="form-input"></el-input>
                            </el-form-item>
                          </el-col>
                            
                            <el-col :span="6">
                                <el-form-item label="原价" class="form-caption">
                                <el-input v-model="product.regular_price" required placeholder="" class="form-input"></el-input>
                                </el-form-item>
                            </el-col>                            
                     
                        
                          <el-col :span="6">
                            <el-form-item label="库存" class="form-caption">
                            <el-input v-model="product.stock" type="email" required placeholder="" class="form-input"></el-input>
                            </el-form-item>
                          </el-col>
                            
                            <el-col :span="6">
                                <el-form-item label="销量" class="form-caption">
                                <el-input v-model="product.sales" type="email" required placeholder="" class="form-input"></el-input>
                                </el-form-item>
                            </el-col>                            
                        </el-row> 
                        
                        <el-row class="form-row params-pay-option" :gutter="20">
                          <el-col :span="3">
                            <el-checkbox v-model='product.payment_online'>易极付</el-checkbox>
                          </el-col>
                          <el-col :span="3">
                            <el-checkbox v-model='product.payment_paypal'>Paypal</el-checkbox>
                          </el-col>
                          <el-col :span="3">
                            <el-checkbox v-model='product.payment_asiabill'>Asiabill</el-checkbox>
                          </el-col>
                          <el-col :span="3">
                            <el-checkbox v-model='product.payment_underline'>货到付款</el-checkbox>
                          </el-col>                            
                        </el-row> 
                    </el-form>
                    
                     <el-form class="form pics logo"  id="pics">
                        <el-row class="form-row " :gutter="20">
                            <el-col :span="12">
                                <el-form-item label="LOGO" class="form-caption"></el-form-item>
                            </el-col>
                            
                            <el-col :span="12">
                                <div @click='handleUploadLogo'><el-form-item label="上传图片" class="form-upload-link right"></el-form-item></div>
                            </el-col>
                            
                        </el-row>                            
                            
                        <el-row class="form-row" :gutter="20" type='flex'>
                            <el-upload action="#" list-type="picture-card" :on-preview='void(0)' :file-list="product.pics.logo">
                                 <el-button slot="trigger" size="small" type="primary"><span ref='uploadLogo'>选取文件</span></el-button>
                            </el-upload>
                        </el-row>
                     </el-form>
                    <el-form class="form pics gallery">
                        <el-row class="form-row " :gutter="20">
                            <el-col :span="12">
                                <el-form-item label="图集" class="form-caption"></el-form-item>
                            </el-col>
                            
                            <el-col :span="12">
                                <div @click='handleUploadGallery'><el-form-item label="上传图片" class="form-upload-link right"></el-form-item></div>
                            </el-col>
                            
                        </el-row>                            
                            
                        <el-row class="form-row" :gutter="20" type='flex'>
                            <el-upload action="#" list-type="picture-card" :on-preview='void(0)' :file-list="product.pics.gallery">
                                 <el-button slot="trigger" size="small" type="primary"><span ref='uploadGallery'>选取文件</span></el-button>
                            </el-upload>
                        </el-row>
                     </el-form>
                    <el-form class="form pics thumbnail">
                        <el-row class="form-row " :gutter="20">
                            <el-col :span="12">
                                <el-form-item label="缩略图" class="form-caption"></el-form-item>
                            </el-col>
                            
                            <el-col :span="12">
                                <div @click='handleUploadThumbnail'><el-form-item label="上传图片" class="form-upload-link right"></el-form-item></div>
                            </el-col>
                            
                        </el-row>                            
                            
                        <el-row class="form-row" :gutter="20" type='flex'>
                            <el-upload action="#" list-type="picture-card" :on-preview='void(0)' :file-list="product.pics.thumbnail">
                                 <el-button slot="trigger" size="small" type="primary"><span ref='uploadThumbnail'>选取文件</span></el-button>
                            </el-upload>
                        </el-row>
                     </el-form>
                     
                     <div id="attrs">
                     <el-form class="form attrs" v-for="(item, index) in product.product_attr">
                            <el-row :gutter="20">
                                <el-col class="name">
                                    <el-form-item label="属性名" class="form-caption">
                                    <el-input v-model="item.name" required placeholder="" class="form-input"></el-input>
                                    </el-form-item>
                                </el-col>    
                                <el-col class="attr">
                                    <el-form-item label="组名" class="form-caption">
                                    <el-input v-model="item.group" required placeholder="" class="form-input"></el-input>
                                    </el-form-item>
                                </el-col>    
                                <el-col class="attr">
                                    <el-form-item label="组ID" class="form-caption">
                                    <el-input v-model="item.groupid" required placeholder="" class="form-input"></el-input>
                                    </el-form-item>
                                </el-col>    
                                <el-col class="attr">
                                    <el-form-item label="ERP属性ID" class="form-caption">
                                    <el-input v-model="item.erpgroupid" required placeholder="" class="form-input"></el-input>
                                    </el-form-item>
                                </el-col>    
                                <el-col class="pic">
                                    <el-form-item label="图片" class="form-caption">
                                    <el-input v-model="item.pic" required placeholder="" class="form-input"></el-input>
                                    </el-form-item>
                                </el-col>    
                            </el-row>

                            <el-row :gutter="16">
                                <div class='delete-link' v-if="product.product_attr.length != 1" @click='handleDeleteAttr(index)'>
                                    <el-form-item label="删除" class="form-delete-link"><i class="material-icons">remove_circle_outline</i></el-form-item>
                                </div>
                                
                                <div class='add-link' v-if="index == product.product_attr.length - 1"  @click='handleAddAttr()'>
                                    <el-form-item label="添加属性" class="form-add-link"><i class="material-icons">add</i></el-form-item>
                                </div>
                            </el-row>
                         
                    </el-form>
                    <div @click='handleAddAttr()' v-if='product.product_attr.length == 0'><el-form class='attrs form last' >新增属性<i class="material-icons">add</i></el-form></div>
                      
                    </div>
                    <div id="suits">
                        <el-form class="suits form"  v-for="(item, index) in product.suits"  >
                                <el-row class="form-row" :gutter="10">
                                    <el-col :span="24">
                                        <el-form-item label="套餐名" class="form-caption">
                                            <div class="form-delete-link right" @click='handleDeleteSuit(index)'><el-form-item label="删除套餐"><i class="material-icons">remove_circle_outline</i></el-form-item></div>
                                            <el-input v-model="item.name" required placeholder="" class="form-input"></el-input>
                                        </el-form-item>
                                    </el-col>
                                </el-row>      
                                <el-row class="form-row" :gutter="20">
                                    <el-col :span="6">
                                        <el-form-item label="价格" class="form-caption">
                                            <el-input v-model="item.price" required placeholder="" class="form-input"></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="套餐图片" class="form-caption">
                                            <el-input v-model="item.pic" required placeholder="" class="form-input"></el-input>
                                        </el-form-item>
                                    </el-col>
                                </el-row>      
                                <template  v-for="(sub, idx) in item.products">
                                <el-row class="form-row" :gutter="20">
                                    <el-col :span="12">
                                        <el-form-item label="产品名" class="form-caption">
                                            <el-input v-model="sub.name" required placeholder="" class="form-input"></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="数量" class="form-caption">
                                            <el-input v-model="sub.amount" required placeholder="" class="form-input"></el-input>
                                        </el-form-item>
                                    </el-col>
                                    <el-col :span="6">
                                        <el-form-item label="产品ERP ID" class="form-caption">
                                            <el-input v-model="sub.erpid" required placeholder="" class="form-input"></el-input>
                                        </el-form-item>
                                    </el-col>
                                </el-row>

                                <el-row>
                                    <div class='delete-link' v-if="item.products.length != 1" @click='handleDeleteSuitProduct(index, idx)'>
                                      <el-form-item label="删除" class="form-delete-link"><i class="material-icons">remove_circle_outline</i></el-form-item>
                                    </div>

                                    <div class='add-link' @click='handleAddSuitProduct(index)' v-if="idx == item.products.length - 1">
                                        <el-form-item label="添加产品" class="form-add-link"><i class="material-icons">add</i></el-form-item>
                                    </div>
                                </el-row>
                            </template>
                        </el-form>
                        <div @click='handleAddSuit()'><el-form class='suits form' >新增套餐<i class="material-icons">add</i></el-form></div>
                    </div>
                    <el-form class="form" id="codes">
                        <el-row class="form-row" :gutter="20">
                            <el-form-item label="51la" class="form-caption">
                                <el-input v-model="product.la" type="textarea" required placeholder="" class="form-input"></el-input>
                            </el-form-item>
                        </el-row>
                    </el-form>
                    
                    <el-form class="form">
                        <el-row class="form-row" :gutter="20">
                            <el-form-item label="Google追踪代码" class="form-caption">
                                <el-input v-model="product.google" type="textarea" required placeholder="" class="form-input"></el-input>
                            </el-form-item>
                        </el-row>
                    </el-form>
                    
                    <el-form class="form" id="content">
                        <el-row class="form-row" :gutter="20">
                            <el-form-item label="内容" class="form-caption"></el-form-item>
                            
                               <textarea id="editor" type="textarea" required placeholder="" class="form-input"></textarea>
                            
                        </el-row>
                    </el-form>
                </el-col>
            </el-col>
            
            <el-col :span="8"  class="right-panel" ref='side'>
                <ul class="navlist">
                    <li class="is-active" data-link='erp'>ERP信息</li>
                    <li data-link='seo'>SEO信息</li>
                    <li data-link='product'>产品信息</li>
                    <li data-link='locale'>区域语言</li>
                    <li data-link='params'>显示参数</li>
                    <li data-link='pics'>LOGO/图集/缩略图</li>
                    <li data-link='attrs'>产品属性</li>
                    <li data-link='suits'>套餐</li>
                    <li data-link='codes'>代码</li>
                    <li data-link='content'>内容</li>
                </ul>
            </el-col>
        </el-row>
    </div>
    
   
    
</template>

<script>
    
  import selectTemplate from './select-template.vue'
  const MakeForm = function(opt){
      let a = new FormData();
      for(let key of Object.keys(opt)){
          a.append(key, opt[key]);
      }
      return a;
  };
  const Models = {
     attr: {
	     name: '',
	     group: '',
	     groupid: '',
	     erpgroupid: '',
	     pics: '',
     },
     product: { 
	     name: '',
	     amount: 1,
	     erpid: '',
     },
     suit: {
         name: '',
	     price:'',
	     pic: '',
	     products: [{
		       name: '',
		       amount: 1,
	               erpid: '',
	     }],
     }
  };
  Models.gen = function(key){
     return JSON.parse(JSON.stringify(this[key]));
  };
  export default {
     data(){ return {
      selectTemplate: false,
      product: {
          zone_id: 0,
          zones: [],
          product_attr: [],
          erp_number: "",
          user_id: 0,
          users: [],
        "product_id": "",
        "title": "",
        "seo_title": "",
        "seo_description": "",
        "content": "",
        "price": "",
        "market_price": "",
        "add_time": "",
        "is_del": "",
        "tags": "",
        "domain": "",
        "currency": "",
        "currency_prefix": "",
        "currency_code": "",
        "discount": "",
        "sales": "",
        "stock": "",
        "thumb": "",
        "theme": "",
        "lang": "",
        "payment_online": "",
        "payment_underline": "",
        "payment_paypal": "",
        "payment_asiabill": "",
        "erp_number": "",
        "ad_member_id": "",
        "id_zone": "",
        "id_department": "",
        "la": "",
        "fb_px": "",
        "aid": "",
        "logo": "",
        "service_contact_id": "",
        "service_email": "",
        "identity_tag": "",
        "google_js": "",
        "yahoo_js": "",
        "yahoo_js_trigger": "",
        "tips": "",
        "sales_title": "",
        "google_analytics_js": "",
         seo:     {
             title:       "",
             description: ""
         },
         product: {},
         locale:  {},
         pics:    {
             logo: [{url:'img/64x64.png'}, {url:'img/64x64.png'}, {url:'img/64x64.png'}],
             gallery: [{url:'img/64x64.png'}, {url:'img/64x64.png'}, {url:'img/64x64.png'}],
             thumbnail: [{url:'img/64x64.png'}, {url:'img/64x64.png'}, {url:'img/64x64.png'}],
         },
         attrs:   [
           {name: "红", "group": "红", "groupid": "red", "erpgroupid": "erpred", "pic": "img/64x64.png"},
           {name: "绿", "group": "绿", "groupid": "green", "erpgroupid": "erpgreen", "pic": "img/64x64.png"},
           {name: "蓝", "group": "蓝", "groupid": "blue", "erpgroupid": "erpblue", "pic": "img/64x64.png"}
         ],
         suits:   [{
            name: "小食拼盘",
            price: "29.00 RMB", 
            pic: "img/64x64.png",
            products: [
                {"name": "藤椒肯大大鸡排", amount: 2, erpid: "1"},
                {"name": "新奥尔良烤翅",  amount:  4, erpid: "2"},
                {"name": "劲爆鸡米花",    amount: 1, erpid: "3"},
                {"name": "黄金薯条",      amount: 1, erpid: "4"},
            ]
         }],
         codes:   {},
         content: ""
       } 
      }
     },
      components: {
       selectTemplate
     },
     beforeDestroy(){
          UE.delEditor('editor');
     },
     mounted(){ 
         this.setup_side();
         UE.getEditor('editor');
         
         this.$root.eventHub.$on('debug', _ => {
            console.log(JSON.stringify(this.data));
         });
         Vue.rootHub.$on("theme.selected", name => {
             this.theme = name;
             this.selectTemplate = false;
         });
     },
     methods: {
       handleUploadLogo(){
           this.$refs.uploadLogo.click();  
       },
        handleERPSearch(){
            this.$http.post("/product.php",
                MakeForm({
                    act: 'getErpProduct',
                    number: this.product.erp_number
                })
            ).then(function(res){
                if(res.body.ret == 0 || !res.body.data){
                    this.$notify.error({
                        title: '错误',
                        message: res.body.msg,
                        duration: 0,
                    })
                }else{
                    ["title", "price", "product_attr"].forEach(name => this.product[name] = res.body.data[name]);
                }
            });
       },
       handleERPSearchDomain(){
            this.$http.post("/product.php",
                MakeForm({
                    act: 'check',
                    domain: this.product.domain
                })
            ).then(function(res){
                if(res.body.ret == 0){
                    this.$notify.error({
                        title: '错误',
                        message: res.body.msg,
                        duration: 0,
                    })
                }else{
                    this.product.zones = res.body.data.zone;
                    this.product.zone_id = this.product.zones[0] && this.product.zones[0].id_zone;
                    this.product.users = res.body.data.users;
                    let d = Object.keys(res.body.data.users);
                    this.product.user_id = d && d[0];
                }
            });
            
       },
       handleUploadGallery(){
           this.$refs.uploadGallery.click();  
       },
       handleUploadThumbnail(){
           this.$refs.uploadThumbnail.click();  
       },
       handleSelectTemplate(){
           this.selectTemplate = true;
       },
       handleAddSuitProduct(index){
           this.suits[index].products.push(Models.gen('product'));  
       },
       handleDeleteSuitProduct(index, idx){
           this.suits[index].products.splice(idx, 1);  
       },
       handleAddSuit(){
           this.suits.push(Models.gen('suit'));  
       },
       handleDeleteSuit(index){
           if(this.suits.length == 1){
               Object.assign(this.suits[0], Models.gen('suit'));
           }  else{
               this.suits.splice(index, 1);
           }
       },
       handleAddAttr(){
	       this.product.product_attr.push(Models.gen('attr'));
       },
       handleDeleteAttr(index){
	   if(this.attrs.length == 1){
	      Object.assign(this.product.product_attr[0], Models.gen("attr"));
	   }else{
	      this.product.product_attr.splice(index, 1);
	   }
       },
       new_suit(){
         window.alert("ok");  
       },
       setup_side(){
         this.$refs.side.$el.style.top = "0px";
         let link = document.querySelectorAll(".right-panel li[data-link]");
         for(let node of link){
           node.addEventListener("click", _ => {
            let elem = document.querySelector("#" + node.dataset.link);
            window.scrollTo(elem.offsetLeft + 250, elem.offsetTop + 160 );
           });
         }
         window.addEventListener("scroll", (e) => {   
            if(!this.$refs.side) return;
            /* 设置边栏位置 */
            if(document.body.scrollTop >= document.body.style.height){
                this.$refs.side.$el.style.top = (document.body.scrollTop - document.body.style.height) + "px";
            }else{
                this.$refs.side.$el.style.top = "0px";
            }
             
            /* 设置边栏高亮元素 */
            let list = document.querySelectorAll(".left-panel form[id]");
            let min = document.body.scrollHeight, elem = null;
            let allTop = document.querySelector(".content").offsetTop;
            for(let node of list){
                let top = node.offsetTop + allTop;
                if(top  >= document.body.scrollTop){
                    if(min > top){
                        min = top;
                        elem = node;
                    }
                }
            }
            if(elem){
                let link = document.querySelectorAll(".right-panel li[data-link]");
                [].slice.call(link).forEach( (f) => f.classList.remove("is-active"));
                document.querySelector(`.right-panel li[data-link='${elem.id}']`).classList.add("is-active");
            }
         });
       }
     }
  }

</script>
