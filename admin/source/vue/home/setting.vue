<template>
    <div id="page-comment-list">
        <div class="header-panel">
            <div class="control">
                <el-input
                    placeholder="输入域名"
                    icon="search"
                    v-model="keyword"
                    id="keyword"
                    :on-icon-click="handleSearch">
                </el-input>
                <div class="operations">
                    
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
                <el-table-column prop="domain" label="域名" width="200"></el-table-column>
                <el-table-column prop="title" label="标题" width="100"></el-table-column>
                <el-table-column prop="seo_title" label="SEO标题"></el-table-column>
                <el-table-column prop="seo_description" label="SEO描述"></el-table-column>
                <el-table-column prop="lang" label="语言"></el-table-column>
                <el-table-column prop="comment_id" label="是否已设置主页">
                    <template scope="scope">
                        <div>是</div>
                    </template>
                </el-table-column>
                <el-table-column prop="" label="操作" width="120">
                    <template scope="scope">
                        <div v-if=" scope.row.is_del == 0 ">
                            <a class="scope-op" href="javascript:void(0)" @click='handleEdit(scope.row)'>设置</a>
                            <a class="scope-op" href='javascript:void(0)' @click='handleDelete(scope.row)'>禁用</a>
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
      }
    }
    , mounted() {
        var self = this;
        if( localStorage && localStorage.home_setting_keyword !="" ){
            this.keyword = localStorage.home_setting_keyword;
            this.handleSearch();
        }
        document.getElementById("keyword").addEventListener('keydown', function(key){
            if( key.keyCode == 13 ){
                self.handleSearch();
            }
        });
    }
    , methods: {
    	get() {
            return false;
        }
        , set(response) {
            this.list = response.domainList;
            this.page_count = 1;
            this.page = 1;
        }
        , handleCurrentChange(val){
            this.page = val;
            this.get();
        }
        , handleEdit(param){
            this.$router.push({path:'/home/setting/edit/'+param.domain});
        }
        , handleDelete(param){
            var self = this;
            param.is_del = 1;
            this.$http.get('/site.php?act=delete&domain='+param.domain+'&is_del='+param.is_del).then( res => {
                if( res.body.code == "1" ){
                    self.$message.error('删除成功');
                    this.list.map(function(item, index){
                        if( item.domain == param.domain ){
                            item.is_del = param.is_del;
                        }
                    });
                }
            });
        }
        , handleRestore(param){
            var self = this;
            param.is_del = 0;
            this.$http.get('/site.php?act=delete&domain='+param.domain+'&is_del='+param.is_del).then( res => {
                if( res.body.code == "1" ){
                    self.$message({ message: '恢复成功', type: 'success'});
                    this.list.map(function(item, index){
                        if( item.domain == param.domain ){
                            item.is_del = param.is_del;
                        }
                    });
                }
            });
        }
        , tableRowClassName(row, index){
            // 删除颜色
            return row.is_del == 1 ? "del-row" : "";
        }
        , handleSearch(){
            if( this.keyword == "" ){ return false; }
            this.$http.get('/site.php?act=search&domain='+this.keyword+"&json=1").then( res => {
                this.set(res.body);
                localStorage.home_setting_keyword = this.keyword;
            });
        }
    }
  }
</script>

