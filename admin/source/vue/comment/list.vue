<template>
    <div id="page-comment-list">
        <div class="header-panel">
            <div class="control">
                  <div class="operations">
                    <a href="comment.php?act=downfile" target="_blank"><el-button >下载模版 <i class='material-icons'>redo</i></el-button></a>
                    <el-button class="forUploader">
                        导入模版<i class='material-icons'>redo</i>
                        <input type="file" id="uploader" ref="uploader" @change="handleUpload">
                    </el-button>
                    <router-link to="/comments/new">
                        <el-button icon="plus" type="primary">新增评论</el-button>
                    </router-link>
                </div>
                 <div class='op-flexbox' v-on:keyup.enter="handleSearch">
                    <div class='op-box'>
                    <el-dropdown class='select-category' @command='handleSearchCategory'>
                        <span class="el-dropdown-link" trigger='click'>
                            <span ref='category'>产品名</span><i class="el-icon-caret-bottom el-icon--right"></i>
                        </span>
                        <el-dropdown-menu slot="dropdown">
                            <el-dropdown-item command='产品名|keyword'>产品名</el-dropdown-item>
                            <el-dropdown-item command='产品ID|product_id'>产品ID</el-dropdown-item>
                            
                        </el-dropdown-menu>
                    </el-dropdown>
                    
                <el-input
                    :placeholder="'输入' + searchKey"
                    icon="search"
                    v-model="keyword"
                    ref='textBox'
                    :on-icon-click="handleSearch">
                </el-input>
               </div>
                
            </div>
            
        </div>
	  </div>
		<div class="container">
			<el-table
			    ref="multipleTable"
			    :data="list"
			    border
			    tooltip-effect="dark"
                v-loading='table_loading'
                element-loading-text="拼命加载中"
                :row-class-name="tableRowClassName"
                >
                <el-table-column prop="comment_id" label="" width="34">
                    <template scope="scope"><div></div></template>
                </el-table-column> 
			    <el-table-column prop="comment_id" label="ID" width="100"></el-table-column>
                <el-table-column prop="title" label="产品信息"></el-table-column>
                <el-table-column prop="product_id" label="产品ID" width="100"></el-table-column>
                <el-table-column prop="name" label="用户名称" width="100">
                    <template scope="scope">
                        <div v-if="scope.row.is_anonymous==1">匿名</div>
                        <div v-if="scope.row.is_anonymous==0">{{ scope.row.name }}</div>
                    </template>
                </el-table-column>
                <el-table-column prop="content" label="评论内容"></el-table-column>
                <el-table-column prop="add_time" label="添加时间" width="200"></el-table-column>
                <el-table-column prop="" label="操作" width="120">
                    <template scope="scope">
                        <div v-if=" scope.row.is_del == 0 ">
                            <a class="scope-op" href="javascript:void(0)" @click='handleEdit(scope.row)'>编辑</a>
                            <a class="scope-op" href='javascript:void(0)' @click='handleDelete(scope.row)'>删除</a>
                        </div>
                        <div v-if=" scope.row.is_del == 1 ">
                            <a class="scope-op danger" href='javascript:void(0)' @click='handleRestore(scope.row)'>恢复</a>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="comment_id" label="" width="32">
                    <template scope="scope"><div></div></template>
                </el-table-column>
			</el-table>
            <el-pagination
                small
                layout="prev, pager, next"
                :page-size="1"
                :current-page.sync="page"
                @current-change="handleCurrentChange"
                :total="page_count"
                >
            </el-pagination>
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
        multipleSelection: []
        , keyword: ""
        , withDel: false
        , table_loading: false
        , list: []
        , page: 1
        , page_count: 0
        , searchCmd: "keyword"
        , searchKey: "产品名"
      }
    }
    , mounted() {
        // if( localStorage && localStorage.comments ){
        //     var response = JSON.parse(localStorage.comments);
        //     this.set(response);
        // }else{
            this.get();
        // }
    }
    , methods: {
    	get() {
            this.table_loading = true;
            // 获取本地
            // if( localStorage && localStorage.comments ){
            //     this.table_loading = false;
            //     var response = JSON.parse(localStorage.comments);
            //     if( parseInt(response.page) == this.page ){
            //         this.set(response);
            //         return false;
            //     }
            // }
    		this.$http.get('/comment.php?p='+this.page).then(function(res){
                this.table_loading = false;
                this.set(res.body);
                if( window.localStorage ){
                    localStorage.comments = JSON.stringify(res.body);
                }
            });
        }
        , set(response) {
            this.list = response.goodsList;
            this.page_count = response.pageCount;
            this.page = parseInt(response.page);
        }
        , handleCurrentChange(val){
            this.page = val;
            this.get();
        }
        , handleEdit(param){
            this.$router.push({path:'/comments/new/'+param.comment_id});
        }
        , handleDelete(param){
            var self = this;
            param.is_del = 1;
            this.$http.get('/comment.php?act=del&comment_id='+param.comment_id+'&is_del='+param.is_del).then( res => {
                if( res.body.code == "1" ){
                    self.$message.error('删除成功');
                    this.list.map(function(item, index){
                        if( item.comment_id == param.comment_id ){
                            item.is_del = param.is_del;
                        }
                    });
                    if( localStorage ){
                        var localData = JSON.parse(localStorage.comments);
                            localData.goodsList = this.list;
                            localStorage.comments = JSON.stringify(localData);
                    }else{
                        this.get();
                    }
                }
            });
        }
        , handleRestore(param){
            var self = this;
            param.is_del = 0;
            this.$http.get('/comment.php?act=del&comment_id='+param.comment_id+'&is_del='+param.is_del).then( res => {
                if( res.body.code == "1" ){
                    self.$message({ message: '恢复成功', type: 'success'});
                    this.list.map(function(item, index){
                        if( item.comment_id == param.comment_id ){
                            item.is_del = param.is_del;
                        }
                    });
                    if( localStorage ){
                        var localData = JSON.parse(localStorage.comments);
                            localData.goodsList = this.list;
                            localStorage.comments = JSON.stringify(localData);
                    }else{
                        this.get();
                    }
                }
            });
        }
        , handleUpload(){
            var self = this;
            // 上传文件
            var $file = this.$refs.uploader;
            if( $file.value == "" ){ return false; }
            var name = $file.value;
            var formData = new FormData();
                formData.append('file', $file.files[0]);
                formData.append('name',name);
                formData.append('act', 'excel-import');
            this.$http.post('comment.php', formData).then( res => {
                if( res.body.ret == 200 ){
                    self.$message({ message: res.body.msg, type: 'success' });
                    $file.value = '';
                    self.get();
                }else{
                    self.$message.error(res.body.msg);
                }
            });
        }
        , tableRowClassName(row, index){
            // 删除颜色
            return row.is_del == 1 ? "del-row" : "";
        }
        , handleSearch(){
            var formdata = new FormData();
                formdata.append('act', 'search');
                formdata.append(this.searchCmd, this.keyword);
            this.$http.post('comment.php', formdata).then( res => {
                this.set(res.body);
            });
        },handleSearchCategory(cmd){
            let [text, cmdkey] = cmd.split("|");
            this.searchCmd = cmdkey;
            this.searchKey = text;
            this.$refs.category.innerText = text;
        }
    }
  }
</script>

