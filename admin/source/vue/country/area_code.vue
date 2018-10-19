<template>
    <div id="page-country-list">
        <div class="header-panel">
            <div class="control">
                <div class="operations"></div>
                <div class="hero-title">区号配置</div>
                <div class="hero-link">
                    <router-link to="/country">
                        <i class="el-icon-arrow-left"></i>返回地区列表
                    </router-link>
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
                >
                <el-table-column prop="id_country" label="" width="34">
                    <template scope="scope"><div></div></template>
                </el-table-column>
                <el-table-column prop="id_country" label="ID" width="100"></el-table-column>
                <el-table-column prop="title" label="国家"></el-table-column>
                <el-table-column prop="ncode" label="区号"></el-table-column>
                <el-table-column label="操作">
                    <template scope="scope">
                        <el-button @click.native='showDialogEdit(scope.row)'>编辑</el-button>
                    </template>
                </el-table-column>
            </el-table>
            <el-pagination
                small
                layout="prev, pager, next, jumper"
                :page-size="1"
                @current-change="handleCurrentChange"
                :total="page_count"
                >
            </el-pagination>
        </div>
        <!--  -->
        <el-dialog :visible.sync='dialogShow' title='编辑'>
            <el-form id='form' name='form'>
                <el-form-item label='国家'><br>
                    <el-input v-model='editModule.title'></el-input>
                </el-form-item>
                <el-form-item label='区号'><br>
                    <el-input v-model='editModule.ncode'></el-input>
                </el-form-item>
                <el-button type="primary" @click="handleSave()">保存</el-button>
            </el-form>
        </el-dialog>
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
        , page: 1
        , table_loading: false
        , list: []
        , page_count: 0
        , dialogShow: false
        , editModule: {
            id_country: ''
            , title: ''
            , ncode: ''
        }
      }
    }
    , mounted() {
        this.get();
    }
    , methods: {
        get() {
            this.table_loading = true;
            this.$http.get('/country.php?act=clist&json=1&p='+this.page).then(function(res){
                this.table_loading = false;
                this.list = res.body.goodsList;
                this.page_count = res.body.pageCount;
            });
        }
        , handleCurrentChange(val){
            this.page = val;
            this.get();
        }
        , showDialogEdit(row){
            this.dialogShow = true;
            this.editModule.id_country = row.id_country;
            this.editModule.title = row.title;
            this.editModule.ncode = row.ncode;
        }
        , handleSave(){ 
            var self = this;
            this.$http.post('/country.php?act=country_save', self.editModule, {'emulateJSON': true}).then(res=>{
                if( res.body.ret == '1' ){
                    self.$message({ message: "成功", type: 'success' });
                    self.dialogShow = false;
                    self.get();
                }else{
                    self.$message.error('失败');
                }
            });
        }
    }
  }
</script>

