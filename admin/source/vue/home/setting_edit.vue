<template>
    <div id="page-comment-new">
        <div class="header-panel">
            <div class="control">
                <div class="operations">
                    <el-button @click="handleSave" icon="plus" type="primary">保存</el-button>
                </div>
            </div>
        </div>
    
        <div class="container">
            <el-form :label-position="labelPosition" label-width="80px" :model="formValue">
                <el-row :gutter="20">
                    <el-col :span="12">
                        <el-form-item label="域名">
                            <el-input v-model="formValue.domain"></el-input>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item label="标题">
                            <el-input v-model="formValue.title"></el-input>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-row>
                    <el-col :span="12">
                        <el-form-item label="语言">
                            <el-select v-model="formValue.lang" placeholder="请选择">
                                <el-option
                                  v-for="item in langs"
                                  :key="item.value"
                                  :label="item.label"
                                  :value="item.value">
                                </el-option>
                            </el-select>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item label="主页模版">
                            <el-select v-model="formValue.theme" placeholder="请选择">
                                <el-option
                                  v-for="item in themes"
                                  :key="item.value"
                                  :label="item.label"
                                  :value="item.value">
                                </el-option>
                            </el-select>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-row :gutter="20">
                    <el-col :span="12">
                        <el-form-item label="SEO标题">
                            <el-input v-model="formValue.seo_title"></el-input>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item label="SEO描述">
                            <el-input v-model="formValue.seo_des"></el-input>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-row>
                    <el-col :span="24">
                        <el-form-item label="Google Analytics">
                            <el-input type="textarea" :rows="4" v-model="formValue.Google_js"></el-input>
                        </el-form-item>
                    </el-col>
                </el-row>
            </el-form>
        </div>
    </div>
</template>

<script>
Vue.http.options.headers = {
  'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
};
export default {
    data() {
      return { 
        labelPosition: 'top',
        formValue: {
          comment_id: ''
          , title: '请选择'
          , product_id: ''
          , name: ''
          , score: 5
          , is_anonymous: "0"
          , content: ''
          , theme: 'style1'
        }
        , themes: [
            { 'label': "默认模版", value: 'style1' }
            , { 'label': "日本ZOZOTOWN模版", value: 'style2' }
        ]
        , dialogImageUrl: '' // 本地图
        , dialogVisible: false
        , dialogTableVisible: false
        , goodsList: []
        , uploaded: [] // cdn图
        , page_count: 1
        , page: 1
        , fileList: []
        , langs: []
      }
    }
    , mounted() {
        var self = this;
        if( this.$route.params.id ){
            this.$http.post('comment.php?act=edit&comment_id='+this.$route.params.id).then( res => {
                console.log(res.body)
                this.formValue = {
                  comment_id: this.$route.params.id
                  , title: res.body.title || ""
                  , product_id: res.body.product_id
                  , name: res.body.name
                  , score: res.body.star
                  , is_anonymous: res.body.is_anonymous
                  , content: res.body.content
                }
                self.uploaded = [];
                res.body.thumbs.map(function(item, index){
                    // self.uploaded.push({ cdn: item.thumb, uid: item.commont_thumb_id });
                    // this.
                    self.fileList.push({name: item.commont_thumb_id, url: item.thumb});
                });
            });
        }
    }
    , methods: {
        showDialog(){
            this.dialogTableVisible = true;
            this.getProduct();
        }
        , handleSave(){
            if( this.formValue.title == "" || this.formValue.product_id == '' ){
                this.$message.error('请选择产品');
                return false;
            }
            if( this.formValue.is_anonymous == "0" && this.formValue.name == "" ){
                this.$message.error('请填写姓名');
                return false;
            }
            if( this.formValue.content == "" ){
                this.$message.error('请填写内容');
                return false;
            }
            var formdata = new FormData();
                formdata.append('act', 'save');
                formdata.append('comment_id', this.formValue.comment_id);
                formdata.append('title', this.formValue.title);
                formdata.append('product_id', this.formValue.product_id);
                formdata.append('name', this.formValue.name);
                formdata.append('star', this.formValue.score);
                formdata.append('product_id', this.formValue.product_id);
                formdata.append('is_anonymous', this.formValue.is_anonymous);
                formdata.append('content', this.formValue.content);
                // this.uploaded = [];
                this.uploaded.map(function(item, index){
                    formdata.append('photos[]', item.cdn);
                });
                // formdata.append('photos[]', "http://dasdasdasdads.jpg");
            this.$http.post('comment.php', formdata).then( res => {
                if( res.body.code == '1' ){
                    this.$message({ message: '添加成功', type: 'success' });
                    this.$router.push({path:'/comments'});
                }else{
                    this.$message.error('未知错误');
                }
            });
        }
        // 改分
        , handleChangeScore(val){
            this.formValue.score = val;
        }
        // 删除图片
        , handleRemove(file, fileList) {
            console.log(file)
            var self = this;
            var newarr = [];
            this.uploaded.map(function(item, index){
                if( item.uid != file.uid && item.cdn!=file.url ){
                    newarr.push(item);
                }
            });
            this.uploaded = newarr;
        }
        , handlePictureCardPreview(file) {
            this.dialogImageUrl = file.url;
            this.dialogVisible = true;
        }
        , handleSuccess(response, file, fileList){
            console.log(response)
            this.uploaded.push({ 'uid': file.uid, 'cdn': response.url });
        }
        , getProduct(){
            this.$http.get('product.php?act=publicProduct2&p='+this.page).then( res => {
                this.goodsList = res.body.goodsList;
                this.page_count = res.body.pageCount;
                console.log(res.body.goodsList)
            });
        }
        , handleCurrentChange(val){
            this.page = val;
            this.getProduct();
        }
        , handleProductSelected(param){
            console.log(param.product_id)
            console.log(param.title);
            this.dialogTableVisible = false;
            this.formValue.product_id = param.product_id;
            this.formValue.title = param.title;
        }
    }
  }
</script>

