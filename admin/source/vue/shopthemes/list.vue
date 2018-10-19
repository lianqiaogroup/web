<template>
  <div id="page-comment-list">
      <div class="header-panel">
            <div class="control">
                <div class="operations" style="display: flex;">
                    <div style="margin-right:8px;">
                        <router-link to="/shopThemes/new">
                            <el-button icon="plus" type="primary">增加模版</el-button>
                        </router-link>
                    </div>
                </div>
                <div class='op-flexbox' v-on:keyup.enter="get">
                    <div class='op-box'>
                        <el-dropdown class='select-category' @command='handleSearchCategory'>
                            <span class="el-dropdown-link" trigger='click'>
                                <span ref='category'>地区</span>
                                <i class="el-icon-caret-bottom el-icon--right"></i>
                            </span>
                            <el-dropdown-menu slot="dropdown">
                                <el-dropdown-item command='模版代号|theme_code'>代号</el-dropdown-item>
                                <el-dropdown-item command='地区|region_code'>地区</el-dropdown-item>
                            </el-dropdown-menu>
                        </el-dropdown>
                        <el-input
                            :placeholder="'输入' + searchKey"
                            icon="search"
                            v-model="keyword"
                            ref='textBox'
                            :on-icon-click="get"
                            v-if="searchCmd!='region_code'">
                        </el-input>
                        <el-select
                            v-if="searchCmd=='region_code'"
                            v-model="selectedRegion_code"
                            icon="search"
                            @change="get"
                            placeholder="请选择地区">
                            <el-option
                                v-for="region in regions"
                                :key="region.code"
                                :label="region.title"
                                :value="region.code">
                            </el-option>
                        </el-select>
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
                :default-sort = "{prop: 'created_at', order: 'descending'}"
                @selection-change="handleSelectionChange"
                >
                <el-table-column prop="" label="" width="50"></el-table-column>
                <el-table-column type="selection" width="55"></el-table-column>
			    <el-table-column prop="sid" label="ID" width="100"></el-table-column>
                <el-table-column prop="theme" label="模版代号"></el-table-column>
                <el-table-column prop="title" label="模版名称"></el-table-column>
                <el-table-column prop="zone" label="地区"></el-table-column>
                <el-table-column prop="lang" label="语种"></el-table-column>
                <el-table-column prop="desc" label="模版简介"></el-table-column>
                <el-table-column prop="theme_count" label="使用数"></el-table-column>
                <el-table-column prop="" label="操作" width="200">
                    <template scope="scope">
                        <!-- <div v-if="scope.row.is_del == 0 "> -->
                            
                            
                            <template v-if="scope.row.is_del == 0">
                                <a class="scope-op" @click="handlePreview(scope.row.theme)">预览</a>
                                <a class="scope-op" href="javascript:;" @click="handleTakeLand(scope.row.sid)">下架</a>
                                <a class="scope-op" href="javascript:;" @click="handleEdit(scope.row.sid)">编辑</a>
                            </template>
                            <template v-if="scope.row.is_del == 1">
                                <a class="scope-op" @click="handlePreview()">预览</a>
                                <a class="scope-op" href="javascript:;" @click="handleTakeOff(scope.row.sid)"><span style="color:#ff4949;">上架</span></a>
                            </template>
                        <!-- </div> -->

                    </template>
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

        <el-dialog :visible.sync="dialogVisible"
            :show-close="false">
            <div v-if="imgUrl" style="text-align: center;">
                <img :src="imgUrl" />
            </div>
            <div v-else>
            暂未上传图片
            </div>
        </el-dialog>
  </div>
</template>

<<script>
Vue.http.options.headers = {
  'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
};
export default {
  data() {
      return { 
        keyword: "", 
        withDel: false, 
        table_loading: false, 
        list: [], 
        page: 1, 
        page_count: 0, 
        searchCmd: "region_code", 
        searchKey: "地区", 
        selected: [], 
        selectedRegion_code: '', 
        regions: [],
        dialogVisible: false,
        imgUrl:'',
        theme_count:''
      }
    },
    mounted() {
        // get country
        this.$service('country#list').then(res => this.regions = [{title:'所有',code:''}].concat(res.body) );
        // get theme datas
        this.get();
    },
    methods: {
    	get() {
            this.table_loading = true;
            if( this.searchCmd=='region_code' ){
                this.keyword = this.selectedRegion_code;
            }
    		this.$http.get(`/shop_theme.php?p=${this.page}&${this.searchCmd}=${this.keyword}`).then(function(res){
                this.table_loading = false;
                this.set(res.body);
            });
        },
        set(response) {
            this.list = response.goodsList;
            this.page_count = response.pageCount;
            this.page = parseInt(response.page);
            console.log(this.list);
        },
        //当前页码变化
        handleCurrentChange(val){
            this.page = val;
            this.get();
        },
        //预览模板
        handlePreview(id){
            if(id){
                this.imgUrl = '/source/images/homeTheme/'+id+'.png';
            }else{
                this.imgUrl = undefined;
            }
            this.dialogVisible = true;
            console.log("预览模板" + id);
        },
        // handleClose(done) {
        //     this.$confirm('确认关闭？')
        //     .then(_ => {
        //         done();
        //     })
        //     .catch(_ => {});
        // },
        //编辑模板
        handleEdit(id){
            this.$router.push({path:'/shopthemes/edit/'+id});
        },
        // 上架
        handleTakeOff(id){
            var self = this;
            var sid = id ? id : this.selected.join(",");
            if( sid == "" ){ return false; }
            this.$http.get('/shop_theme.php?act=delete&sid=' + sid + '&is_del=' + 0).then( res => {
                console.log(res);
                if( res.body.ret == "1" ){
                    self.$message({ message: '上架成功', type: 'success'});
                    self.get();
                }
            });
        },
        // 下架
        handleTakeLand(id){
            var self = this;
            var sid = id ? id : this.selected.join(",");
            if( sid == "" ){ return false; }
            this.$http.get('/shop_theme.php?act=delete&sid=' + sid + '&is_del=' + 1).then( res => {
                if( res.body.ret == "1" ){
                    self.$message({ message: '下架成功', type: 'success'});
                    self.get();
                }
            });
        },
        // 删除颜色
        tableRowClassName(row, index){
            return row.is_del == 1 ? "del-row" : "";
        },
        handleSearchCategory(cmd){
            let [text, cmdkey] = cmd.split("|");
            this.searchKey = text;
            this.searchCmd = cmdkey;
            this.$refs.category.innerText = text;
        },
        handleSelectionChange(val) {
            this.selected = val.map(x => x.tid);
        }
    }
}
</script>
