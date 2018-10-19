<template>
   <div class='sites'>
       
   <!-- <div class="header-panel">
        <div class="control">
            <div class="operations">
                    <el-button icon="plus" type="primary" @click="toNewArticle">新增文章</el-button>
                    <el-button icon="plus" type="primary" @click="handleAddIndexFocus">新增焦点图</el-button>
                </div>
            <div class='op-box'>
            <el-input
                placeholder="输入关键词"
                icon="search"
                v-model="keyword"
                id="keydown"
                :on-icon-click="handleSearch">
            </el-input>
                
                </div>
                  <el-row class="op" >
                <el-col>输入要搜索的主页域名</el-col>    
            </el-row>
          </div>
    </div> -->
    <div class='header-panel-addition'></div>
    <div class='content inversed'>
        <div class='flexbox'>
        <div class='widget'>
            <div class='widget-heading'>{{ keyword }}</div>
            <div class="hero-link" >
                    <router-link to="/sites">
                        <i class="el-icon-arrow-left"></i>返回列表
                    </router-link>
                </div>
            <el-table :data="mainlist">
                <el-table-column label="语言" prop="lang" width="120" align="center"></el-table-column>
                <el-table-column label="主页" prop="domain"></el-table-column>
                <el-table-column label="标题" prop="title"></el-table-column>
                <el-table-column label="邮箱" prop="mail"></el-table-column>
                <el-table-column label="SEO标题" prop="seo_title"></el-table-column>
                <el-table-column label="SEO描述" prop="seo_description"></el-table-column>
                <el-table-column label="操作" width="240">
                    <template scope="scope">
                        <div v-if=" scope.row.is_del == 0 ">
                            <el-button size="small" @click='handleEdit(scope.row, "mainlist")'>编辑</el-button>
                            <el-button size="small" type="danger" @click='handleDelete(scope.row, "mainlist")'>删除</el-button>
                        </div>
                        <div v-if=" scope.row.is_del == 1 ">
                            <el-button size="small" type="success" @click="handleDelete(scope.row, 'mainlist')">恢复</el-button>
                        </div>
                        <div v-if=" scope.row.is_del == null ">
                            <el-button size="small" @click='handleEdit(scope.row, "mainlist")'>编辑</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        </div>
        
            <div class='flexbox'>
                <div class='widget'>
                    <div class='widget-control'><el-button size="medium" type="primary" icon="plus" @click='handleAddIndexFocus'>添加</el-button></div>
                    <div class='widget-heading'>轮播图</div>
                    <el-table :data="focus" >
                        <el-table-column label="序列" prop="sort" width="60" align="center"></el-table-column>
                        <el-table-column label="ID" prop="id" width="60" align="center"></el-table-column>
                        <el-table-column label="标题" prop="title" width="300" align="left"></el-table-column>
                        <el-table-column label="缩略图" >
                        <template scope="scope">
                            <img :src="scope.row.thumb" alt="" width="60">
                        </template>
                        </el-table-column>
                        
                        <el-table-column label="操作" width="240">
                        <template scope="scope">
                            <div v-if=" scope.row.is_del == 0 ">
                                <el-button size="small" @click='handleEdit(scope.row, "index_focus")'>编辑</el-button>
                                <el-button size="small" type="danger" @click='handleDelete(scope.row, "index_focus")'>删除</el-button>
                            </div>
                            <div v-if=" scope.row.is_del == 1 ">
                                <el-button size="small" type="success" @click="handleDelete(scope.row, 'index_focus')">恢复</el-button>
                            </div>
                        </template>
                        </el-table-column>
                    </el-table>
                    <el-pagination
                    small
                    layout="prev, pager, next"
                    :page-size="1"
                    @current-change="handleFocusChange"
                    :total="focus_page_count"
                    >
                </el-pagination>
                </div>
                </div>
                <div class='flexbox'>
                <div class='widget'>
                    <div class='widget-control'><el-button size="medium" type="primary" icon="plus" @click='handleAddProduct()'>添加</el-button></div>
                    <div class='widget-heading'>首页产品</div>
                    <el-table :data="site_products" >
                        <el-table-column label="排序" width="60" align="center" prop='sort'></el-table-column>
                        <el-table-column label="ID" prop="product_id" width="60" align="center"></el-table-column>
                        <el-table-column label="缩略图" width="64">
                        <template scope="scope">
                            <img :src="scope.row.thumb" alt="" width="48">
                        </template>
                        </el-table-column>
                        <el-table-column label="标题" prop="title"  align="left"></el-table-column>
                        
                        <el-table-column label="操作" width="240">
                        <template scope="scope">
                            <div v-if=" scope.row.is_del == 0 ">
                                <el-button size="small" @click='handleEdit(scope, "site_product")'>编辑</el-button>
                                <el-button size="small" type="danger" @click='handleDelete(scope, "site_product")'>删除</el-button>
                            </div>
                            <div v-if=" scope.row.is_del == 1 ">
                                <el-button size="small" type="success" @click="handleDelete(scope, 'site_product')">恢复</el-button>
                            </div>
                        </template>
                        </el-table-column>
                    </el-table>
                      <el-pagination
                    small
                    layout="prev, pager, next"
                    :page-size="1"
                    @current-change="handlePageChange('site', 'getProduct')(arguments[0])" 
                    :total="site.count"
                    >
                </el-pagination>
                </div>
              </div>
                <div class='flexbox'>
                
                <div class='widget'>
                    <div class='widget-control'><el-button size="medium" type="primary" icon="plus" @click='handleAddCategory'>添加</el-button></div>
                    <div class='widget-heading'>产品分类</div>
                    <el-table :data="category" >
                        <el-table-column label="排序" prop='sort' width="60" align="center"></el-table-column>
                        <el-table-column label="ID" prop="id" width="60" align="center"></el-table-column>
                        <el-table-column label="模块名" align="left" width="240" >
                            <template scope='scope'>
                                <span v-if='scope.row.parent_title' style='padding-left: 20px; ' v-html='"┗━"'></span>
                                <span v-text='scope.row.title'></span>
                            </template>

                        </el-table-column>
                        <el-table-column label="备注" prop="title_zh" align="center"></el-table-column>
                        <el-table-column label="是否展示" prop="is_del" width="80">
                            <template scope='scope'>
                                <span v-text='scope.row.is_del == "1" ? "×" : "√"'></span>
                            </template>
                        </el-table-column>
                        <el-table-column label="操作" width="280">
                        <template scope="scope">
                            
                                <el-popover
  ref="popover1"
  placement="top-start"
  width="200"
  trigger="hover"
  effect="dark"
  content="无法为二级菜单添加下级菜单">
</el-popover>

                            
                            <div v-if=" scope.row.is_del == 0 ">
                                
                                <el-button v-if='scope.row.parent_id != 0' size="small" class='is-disabled' icon='plus' v-popover:popover1>子类</el-button>
                                
                                
                                <el-button v-if='scope.row.parent_id == 0' size="small" @click='handleAddSubCategory(scope.row)' icon='plus'>子类</el-button>
                                
                                <el-button size="small" @click='handleEdit(scope, "category")' >编辑</el-button>
                                <el-button size="small" type="danger" @click='handleDelete(scope, "category")' >删除</el-button>
                                <el-button size="small" @click='handlePreview(scope, "category")' >预览</el-button>
                            </div>
                            <div v-if=" scope.row.is_del == 1 ">
                                <el-button size="small" type="success" @click="handleDelete(scope, 'category')">恢复</el-button>
                            </div>
                            
                        </template>
                        </el-table-column>
                    </el-table>
                </div>
                </div>
                <div class='flexbox'>
                <div class='widget'>
                <div class="widget-control">
                    <el-button icon="plus" type="primary" @click="toNewArticle">新增文章</el-button>
                </div>
            <div class='widget-heading'>文章</div>
            <el-table  :data="article" >
                <el-table-column label="序列" prop="sort" width="60" align="center"></el-table-column>
                <el-table-column label="ID" prop="article_id" width="60" align="center"></el-table-column>
                <el-table-column label="文章标题" prop="title"></el-table-column>
                <el-table-column label="操作" width="240">
                  <template scope="scope">
                        <div v-if=" scope.row.is_del == 0 ">
                            <el-button size="small" @click='handleEdit(scope.row, "article")'>编辑</el-button>
                            <el-button size="small" type="danger" @click='handleDelete(scope.row, "article")'>删除</el-button>
                        </div>
                        <div v-if=" scope.row.is_del == 1 ">
                            <el-button size="small" type="success" @click="handleDelete(scope.row, 'article')">恢复</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
                <el-pagination
                    small
                    layout="prev, pager, next"
                    :page-size="1"
                    @current-change="page => handlePageChange('articles', 'getArticle')(page)"
                    :total="articles.count"
                    >
                </el-pagination>
           </div>
           </div>
             
        
    </div> 
        <el-dialog title="选择产品" class="product_select" :visible.sync="selectProductVisible">
                <el-table :data="allProduct">
                    <el-table-column property="product_id" label="产品ID" width="80"></el-table-column>
                    <el-table-column property="thumb" label="缩略图" width="100">
                        <template scope="scope">
                            <img :src='scope.row.thumb' alt="" width="60">
                        </template>
                    </el-table-column>
                    <el-table-column property="title" label="名称" width="200"></el-table-column>
                    <el-table-column property="erp_number" label="erp_id" width="120"></el-table-column>
                    <el-table-column property="product_id" label="操作">
                        <template scope="scope">
                            <el-button @click="handleProductSelected(scope.row)">选择</el-button>
                        </template>
                    </el-table-column>
                </el-table>
                <el-pagination
                    small
                    layout="prev, pager, next"
                    :page-size="1"
                    @current-change="handleCurrentChange"
                    :total="product_page_count"
                    >
                </el-pagination>
        </el-dialog>
        <el-dialog title="预览产品" class="product_select" :visible.sync="previewProductVisible">
                <el-table :data="category_product" v-loading:sync='category_products.loading'>
                    <el-table-column property="product_id" label="产品ID" width="80"></el-table-column>
                    <el-table-column property="thumb" label="缩略图" width="100">
                        <template scope="scope">
                            <img :src='scope.row.thumb' alt="" width="60">
                        </template>
                    </el-table-column>
                    <el-table-column property="title" label="名称"></el-table-column>
                </el-table>
                <el-pagination
                    small
                    layout="prev, pager, next"
                    :page-size="1"
                    @current-change="handlePageChange('category_products', 'getCategoryProduct')" 
                    :total="category_products.count"
                    >
                </el-pagination>
        </el-dialog>
        <el-dialog :title="category_title" class='category_edit' :visible.sync="editCategoryVisible">
            <el-form>
                <el-form-item label="菜单名">
                    <el-input v-model="category_.title"></el-input>
                </el-form-item>
                <el-form-item label="上级菜单"><br>
                    <div>
                    <el-select v-model="category_.parent_id" no-data-text='选择上级菜单'>
                        <el-option :key='0' value='0' label='<顶级>'></el-option>
                        <el-option v-for='item in this.category.filter(x => x.parent_id == 0)'  :key='item.id' :value='item.id' :label='item.title' v-if='category_.id != item.id'></el-option>
                    </el-select>
                    </div>
                </el-form-item>
                <el-form-item label="备注">
                    <el-input v-model="category_.title_zh"></el-input>
                </el-form-item>
              
                <el-form-item label="排序(数值越大显示越靠前)"><br>
                <div>
                    <el-input-number v-model="category_.sort"></el-input-number>
                </div></el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="handleCategoryEditFinished(category_)">保存</el-button>
                    <el-button @click='handleCategoryEditCanceled'>取消</el-button>
                </el-form-item>
            </el-form>
        </el-dialog>
        <el-dialog :title="editproduct_.newmode ? '新增产品' : '产品编辑'" class='product_edit' :visible.sync="editProductVisible">
            <el-form>
                <el-form-item label="产品">
                    <div @click='handleSelectNewProduct'>
                        <el-input ref='dialog-new-title' v-model="editproduct_.title" placeholder='选择产品' readonly>
                        </el-input>
                    </div>
                </el-form-item>
                <el-form-item label="排序(数值越大显示越靠前)"><br>
                    <el-input-number v-model="editproduct_.sort"></el-input-number>
                </el-form-item>
                <el-form-item label="缩略图"><br>
                <img :src='editproduct_.thumb' style='max-width: 160px;'>
                    <el-upload
                         action="qiniu_um.php?type=site_product"
                         list-type="picture-card"
                         :file-list='editProductImageList'
                         name='upfile'
                          :before-upload='beforeAvatarUpload'
                         :on-change='handleUploadChange'
                         :on-remove='handleUploadChange'>
                        <i class="el-icon-plus"></i>
                        
                        
                </el-upload>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="handleEditProductFinished(editproduct_)">保存</el-button>
                </el-form-item>
            </el-form>
        </el-dialog>
        <el-dialog title="轮播图设置" class='product_edit' :visible.sync="selectIndexFocusVisible">
            <el-form>
                <el-form-item label="产品">
                    <div @click='handleSelectNewProduct'>
                        <el-input  v-model="editproduct_.title" placeholder='选择产品' readonly>
                        </el-input>
                    </div>
                </el-form-item>
                <el-form-item label="域名">
                    
                        <el-input v-model="editproduct_.domain" placeholder='选择产品' readonly>
                        </el-input>
                    
                </el-form-item>
                <el-form-item label="缩略图"><br>
                    <el-upload
                         action="qiniu_um.php?type=focus"
                         list-type="picture-card"
                         :file-list='editProductImageList'
                         name='upfile'
                         :on-change='handleUploadChange'
                         :on-remove='handleUploadChange'
                          >
                        <i class="el-icon-plus"></i>
                </el-upload>
                
                <el-form-item label="描述"><br>
                    <el-input type='textarea' v-model="editproduct_.desc"></el-input>
                </el-form-item></el-form-item>
                <el-form-item label="排序(数值越大显示越靠前)"><br>
                    <el-input-number v-model="editproduct_.sort"></el-input-number>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="handleEditProductFinished(editproduct_)">保存</el-button>
                </el-form-item>
            </el-form>
        </el-dialog>
     </div>

       
</template>

<script>


export default {
  data(){
    return {
       tableConfig: require('../table/site.json'),
       mainlist: []
       , focus: []
       , article: []
       , site_products: []
       , keyword: ""
       , url_config: {
          mainlist: '/site.php?act=search'
          , index_focus: 'index_focus.php'
          , article: 'article.php'
       },
       focus_page_count: 1,
       focus_page: 1,
       category: [],
       selectProductVisible: false,
       goodsList: [],
       allProduct: [],
       product_page_count: 1,
       page: 1,
       editCategoryVisible: false,
       handleProductSelected: _ => _,
       handleCategoryEditFinished: _ => _,
       handleEditProductFinished: _ => _,
       category_title: '',
       editproduct_: {},
       editProductImageList: [],
       category_: {},
       editProductVisible: false,
       selectIndexFocusVisible: false,
       category_product: [],
       category_preview: {},
       previewProductVisible: false,
       category_products: {
           page: 1,
           count: 1,
           loading: true,
       },
       articles: {
          page: 1,
          count: 1,
       },
       site:{
          page: 1,
          count: 1,
       }
    };
  }
  , watch: {
      keyword(){
          this.keyword = this.keyword.replace(/http:\/\//g, '');
      }
  }
  , mounted() {
      var self = this;
      if( this.$route.params.domain ){
          this.keyword = this.$route.params.domain;
      }
      window.addEventListener('keydown', function(key){
          if( key.keyCode == 13 ){
              self.handleSearch();
          }
      });
      if( this.keyword == '' ){ return false; }
      this.handleSearch();
  }
  , methods: {
        getProduct(){
            this.$service('site_product#list', this.keyword, this.site.page).then(res => {  
                this.site.count = res.body.pageCount || 1;
                this.site_products = res.body.goodsList; 
            });
            this.$service('product#list', "domain", this.keyword, 0, this.page).then(res => {
                this.allProduct = res.body.goodsList;
                this.product_page_count = res.body.pageCount;
            });
        }
        , getIndexFocus(){
            this.$service('index_focus#list', this.keyword, this.focus_page).then(res => {
                this.focus = res.body.goodsList.map(x => Object.assign(x, {title: x.name}))
                this.focus_page_count = res.body.pageCount;
            });
        }
        , getCategory(){
            this.$service('category#list', this.keyword).then(res => {
                this.category = this.regroupCategoryInplace(res.body.data);
            });
        }
        , getArticle(){
            this.$http.get('/article.php?json=1&domain='+this.keyword).then( res => {
                this.article        = res.body.goodsList;
                this.articles.count = res.body.pageCount;
            });
        }
        , regroupCategoryInplace(obj){
              if(!obj) return [];
              let db = obj.reduce((a, b) => { a[b.id] = {data: {title_zh: b.title_zh, id: b.id, is_del: b.is_del, parent_id: b.parent_id, title: b.title, sort: b.sort}, children: []}; return a}, {});
              db[0] = {data: {}, children: []};
              let tree = {};
              obj.forEach(c => {  
                db[c.parent_id].children.push(c.id);
                db[c.id].data.parent_title = db[c.parent_id].data.title;
              });
              Object.keys(db).forEach(key => {
                db[key].children.sort((a, b) => db[b].data.sort - db[a].data.sort);
              });
              obj.splice(0);
              let dfs = function(id, lv){ 
                if(id != 0){
                    db[id].data.lv = lv;
                    obj.push(db[id].data);
                }
                db[id].children.forEach(f => dfs(f, lv + 1));
              };
            dfs(0, 0);
            return obj;
        }
        , toNewArticle(){
            if( this.keyword == "" ){ return false; }
            this.$router.push({path:'/article/new/'+this.keyword});
        }
        , handleSearch(){
            if( this.keyword=="" ) return false;
            if( this.keyword.split('www.').length<2 ){
                this. this.$message({ message: '输入www.', type: 'error'});
                return false;
            };
            this.$http.get('site.php?act=search&json=1&domain='+this.keyword).then( res => {
                this.mainlist = res.body.domainList;
            });
            this.getIndexFocus();
            this.getArticle();
            this.getProduct();
            this.getCategory();
        }
        , async handleEdit(param, type){
            if( type == 'article' ){
                this.$service().invalidate(/^\.article#/);
                this.$router.push({path:'/article/edit/'+param.article_id});
                // window.open("/article.php?act=edit&article_id="+param.article_id);
            }
            if( type == 'index_focus' ){
            return this.handleAddIndexFocus(param);
            }
            if( type == 'mainlist' ){
                this.$router.push({path:'/sites/edit/'+this.keyword});
            }
            if( type == 'site_product'){
                await this.$service('site_product#save', this.keyword, await this.editProduct(param.row)).submitMessage();
                this.getProduct();
            }
            if( type == 'category'){
                await this.$service('category#edit', this.keyword, await this.editCategory(param.row)).submitMessage();
                this.getCategory();
            }
        }
        , async handleDelete(param, type){
            var _is_del = param.is_del == "1" ? "0" : "1";
            if(type == 'site_product'){
                var _is_del = param.row.is_del == "1" ? "0" : "1";
                if(_is_del == "1"){
                    await this.$service('site_product#delete', this.keyword, param.row.id).submitMessage();
                }else{
                    await this.$service('site_product#resume', this.keyword, param.row.id).submitMessage();
                }
                this.getProduct();
                return;
            }
            if(type == 'category'){
                var _is_del = param.row.is_del == "1" ? "0" : "1";
               if(_is_del == "1"){
                 await this.$service('category#delete', this.keyword, param.row).submitMessage();
               }else{
                 await this.$service('category#resume', this.keyword, param.row).submitMessage();
               }
              this.getCategory();
              return;
          }
          var url = this.url_config[type];
          if(type == 'article'){
              this.$service().invalidate(/^\.article#/);
          }

          var request = { 'act': "delete", 'is_del': _is_del }
              param.domain ? request.domain = param.domain : null;
              param.id ? request.mid = param.id : null;
              param.article_id ? request.article_id = param.article_id : null;
          this.$http({
              methods: "GET"
              , url: url
              , params: request
          }).then( res => {
              if( res.body.ret == "1" ){
                  param.is_del = request.is_del;
                  this.$message({ message: '操作成功', type: 'success'});
              }else{
                  this.$message({ message: res.body.msg, type: 'error'});
              }
          });
        }
        , getLocalData(){
         return false;
        }
        , async handleAddProduct(){          
            await this.$service('site_product#save', this.keyword, await this.editProduct(null, true)).submitMessage();
            this.getProduct();
        }
        , async handleAddCategory(){
            await this.$service('category#add', this.keyword, await this.editCategory()).submitMessage();
            this.getCategory();
        }
        , async handleAddSubCategory({id}){
            await this.$service('category#add', this.keyword, await this.editCategory({parent_id: id})).submitMessage();
            this.getCategory();
        }
        , handleCurrentChange(val){
            this.page = val;
            this.getProduct();
        }
        , selectProduct(){
            this.selectProductVisible = true;
            return new Promise((ac, re) => {
                this.handleProductSelected = (product) => {
                    this.selectProductVisible = false;
                    ac(product);
                }
            });
        }
        , editCategory(row){
            this.category_title = row ? (row.id ? "编辑分类" : "新增子分类") : "新增分类";
            this.category_ = Object.assign({title: '', sort: '', parent_id: '', is_del: '0', id: row && row.id}, row);
            if(this.category_.parent_id == 0){
                this.category_.parent_id = '';
            }
            this.editCategoryVisible = true;
            return new Promise((ac, re) => {
                this.handleCategoryEditFinished = (cat) => {
                    this.editCategoryVisible = false;
                    ac(cat);
                }
            });
        }
        , handleCategoryEditCanceled(){
            this.editCategoryVisible = false;
        }
        , editProduct(row, newmode, key = 'editProductVisible'){
            this[key] = true;
            this.editproduct_ = row || {title: '', thumb: '', sort: '', is_del: '0'};
            this.editproduct_.newmode = newmode;
            this.editProductImageList = row && row.thumb ? [{name: '', url: row.thumb}] : [];
            return new Promise((ac, re) => {
                this.handleEditProductFinished = (prod) => {
                    console.log(prod);
                    this.editProductVisible = false;
                    ac(prod);
                }
          });
        }
        , handleUploadChange(file, filelist){
          if(file.response){
              this.editproduct_.thumb = file.response.url; 
              this.editProductImageList = [{name: '', url: file.response.url}];
          }
          if(filelist.length >= 2){
            filelist.splice(0, filelist.length - 1);
          }else if(filelist.length == 0){
            this.editproduct_.thumb = null; 
          }else{
            if(file.response){
               this.editproduct_.thumb = file.response.url; 
               this.editProductImageList = [{name: '', url: file.response.url}];
            }else{
               this.editproduct_.thumb = null; 
            }
          }
        },
        beforeAvatarUpload(file) {
        // const isJPG = file.type === 'image/jpeg';
        const isLt2M = file.size / 1024 / 1024 < 5;

        // if (!isJPG) {
        //   this.$message.error('上传头像图片只能是 JPG 格式!');
        // }
        if (!isLt2M) {
          this.$message.error('上传头像图片大小不能超过 5MB!');
        }
        return isLt2M;
      }
        , async handleSelectNewProduct(){
          if(!this.editproduct_.newmode) return;
          let product = await this.selectProduct();
          Vue.nextTick(_ => {
            let {product_id, thumb, title} = product;
            Object.assign(this.editproduct_, {product_id, thumb, title});
            this.editProductImageList = [{name: '', url: thumb}];  
          }, 0);
        }
        , async handleAddIndexFocus(row){
            this.editProduct(Object.assign({
              id: undefined,
              thumb: '',
              product_id: 0,
              sort: 0,
              is_del: 0,
              domain: this.keyword,
              title: row && row.title,
            }, row), true, 'selectIndexFocusVisible');
            this.handleEditProductFinished = (prod) =>{
              console.log(prod)
              this.$service('index_focus#save', prod).then(res => {
                  if(res.body.ret == '1'){
                      this.selectIndexFocusVisible = false;
                      this.getIndexFocus();
                  }
                  return res;
              }).submitMessage();
            }
        }
        , handleFocusChange(val){
            this.focus_page = val;
            this.getIndexFocus();
        }
        , handlePageChange(val, fn){
            return function(page){
              console.trace('page', page);
              this[val].page = page;
              this[fn]();
            }.bind(this);
        }
        , handlePreview(param, type){
            if(type == 'category'){
              this.category_preview = param;
              this.getCategoryProducts();
              this.previewProductVisible = true;
            }
        }
        , async getCategoryProducts(){
            this.category_products.loading = true;
            let ret = await this.$service('category#preview', this.keyword, this.category_products.page, this.category_preview.row.id);
            this.category_product = ret.body.data;
            this.category_products.count = ret.body.pageCount;
            Vue.nextTick(_ => this.category_products.loading = false);
        }
    }
}
</script>