<template>
    <div id="page-country-list">
        <div class="header-panel">
            <div class="control">
                <div class="operations">
                    <!-- <el-button @click="handleNew">一级地区 <i class='material-icons'>redo</i></el-button> -->
                    <!-- <el-button @click="showSecRegionDialog">下级地区 <i class='material-icons'>redo</i></el-button> -->
                    <router-link to="/country/area_code">
                        <el-button icon="plus" type="primary">区号配置</el-button>
                    </router-link>
                    <el-button @click="handleSync">地区ERP同步 <i class='material-icons'>redo</i></el-button>
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
                <el-table-column prop="id_zone" label="" width="34">
                    <template scope="scope"><div></div></template>
                </el-table-column>
                <el-table-column type="expand">
                    <template scope="scope">
                        <el-form label-position="left" inline class="demo-table-expand">
                            <el-form-item label="二级区域">
                                <br>
                                <div v-for="son in scope.row.son">
                                    <p>{{ son.title }}</p>
                                </div>
                            </el-form-item>
                        </el-form>
                    </template>
                </el-table-column>
                <el-table-column prop="id_zone" label="ID" width="100"></el-table-column>
                <el-table-column prop="country" label="所属国家"></el-table-column>
                <el-table-column prop="title" label="区域"></el-table-column>
                <el-table-column prop="code" label="地区编码"></el-table-column>
                <el-table-column prop="currency" label="货币符号"></el-table-column>
                <el-table-column label="操作">
                    <template scope="scope">
                        <el-button @click.native='showDialogEdit(scope.row.id_zone,scope.row.title)'>编辑</el-button>
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
        <!-- 新增/编辑 一级区域 -->
        <el-dialog :visible.sync='dialogTopRegion' title='一级区域' v-loading='editModuleLoading'>
            <el-form id='form' name='form'>
                <el-row :gutter="20">
                    <el-col :span="12">
                        <el-form-item label='所属国家'><br>
                            <el-select v-model="editModule.id_country">
                                <el-option
                                    v-for="item in options.id_country"
                                    :key="item.id_country"
                                    :label="item.title"
                                    :value="item.id_country">
                                </el-option>
                            </el-select>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item label='货币'><br>
                            <el-select v-model="editModule.currency_id">
                                <el-option
                                    v-for="item in options.currency_id"
                                    :key="item.currency_id"
                                    :label="item.currency_title"
                                    :value="item.currency_id">
                                </el-option>
                            </el-select>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-form-item label='区域（中文）'><br>
                    <el-input v-model='editModule.title'></el-input>
                </el-form-item>
                <el-form-item label='区域（当地）'><br>
                    <el-input v-model='editModule.lang'></el-input>
                </el-form-item>
                <el-form-item label='地区编码'><br>
                    <el-input v-model='editModule.code'></el-input>
                </el-form-item>
                <el-form-item label='cloak地区编码'><br>
                    <el-input v-model='editModule.two_code'></el-input>
                </el-form-item>
                <div style="color:red;"  v-show="is_sms_bind_isp == 0">该地区还未开通短信验证码，请先开通</div>
                <el-form-item label='强制开通短信验证码'><br>
                    <el-checkbox v-model='forceOpen' v-if="is_sms_bind_isp == 0" disabled></el-checkbox>
                    <el-checkbox v-model='forceOpen' v-if="is_sms_bind_isp == 1" ></el-checkbox><label style="margin-left:10px;">是</label>
                </el-form-item>

                <!-- <el-form-item label='布谷鸟售后邮箱'><br>
                    <el-radio v-model="editModule.diy_email" label="0">域名对应邮箱</el-radio>
                    <el-radio v-model="editModule.diy_email" label="1">统一售后邮箱</el-radio>
                </el-form-item>
                <el-form-item label='自定义邮箱：' v-if="editModule.diy_email == 1">
                    <el-input v-model='editModule.email' type="email"></el-input>
                </el-form-item>

                <el-form-item label='渡渡鸟售后邮箱'><br>
                    <el-radio v-model="editModule.diy_email_2" label="0">域名对应邮箱</el-radio>
                    <el-radio v-model="editModule.diy_email_2" label="1">统一售后邮箱</el-radio>
                </el-form-item>
                <el-form-item label='自定义邮箱：' v-if="editModule.diy_email_2 == 1">
                    <el-input v-model='editModule.dodo_email' type="email"></el-input>
                </el-form-item>

                <el-form-item label='百灵鸟售后邮箱'><br>
                    <el-radio v-model="editModule.diy_email_3" label="0">域名对应邮箱</el-radio>
                    <el-radio v-model="editModule.diy_email_3" label="1">统一售后邮箱</el-radio>
                </el-form-item>
                <el-form-item label='自定义邮箱：' v-if="editModule.diy_email_3 == 1">
                    <el-input v-model='editModule.lark_email' type="email"></el-input>
                </el-form-item> -->

                <el-button type="primary" @click="handleSave()">保存</el-button>
            </el-form>
        </el-dialog>
        <!-- 新增二级区域 -->
        <el-dialog :visible.sync='dialogSecRegion' title='二级区域'>
            <el-form id='form' name='form'>
                <el-form-item label='所属区域'><br>
                    <el-select v-model="sonModule.id_zone">
                        <el-option
                            v-for="item in options.sonOptions"
                            :key="item.id_zone"
                            :label="item.title"
                            :value="item.id_zone">
                        </el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label='名称'><br>
                    <el-input v-model='sonModule.title'></el-input>
                </el-form-item>
                <el-form-item label='地区编码'><br>
                    <el-input v-model='sonModule.code'></el-input>
                </el-form-item>
                <el-button type="primary" @click="handleSaveSon()">保存</el-button>
            </el-form>
        </el-dialog>

        <!-- 地區同步報錯提示 -->
        <el-dialog title="地区同步报错提示" :visible.sync="dialogFailList">
            <el-table :data="failList">
                <el-table-column property="id_zone" label="id_zone" width="150"></el-table-column>
                <el-table-column property="msg" label="msg"></el-table-column>
                <el-table-column property="type" label="type" width="200"></el-table-column>
            </el-table>
        </el-dialog>
        <!-- 站点邮箱替换不成功提示列表 -->
        <el-dialog :visible.sync='errList' class="departmentalTransferConfirm" center :title="errorListMessage">
            <div style="max-height:400px;overflow-y:scroll;margin-bottom:20px;">
                <ul>
                    <li v-for="(item,index) in errorList" :key="index">{{item}}</li>
                </ul>
            </div>
            <el-button type="primary" @click="errList = false" >知道了</el-button>
        </el-dialog>
        <el-dialog
            :visible.sync="dialogVisible"
            width="10%">
            <span slot="title">{{title}}</span>
            <span>{{region}}地区，是否{{cancel}}强制开通短信验证码？</span>
            <span slot="footer" class="dialog-footer">
                <el-button @click="dialogVisible = false">取 消</el-button>
                <el-button type="primary" @click="saveNext()">确 定</el-button>
            </span>
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
        errList:false  //站点邮箱替换不成功列表是否显示
        , multipleSelection: []
        , keyword: ""
        , withDel: false
        , page: 1
        , table_loading: false
        , list: []
        , page_count: 0
        , dialogTopRegion: false
        , dialogSecRegion: false
        ,dialogVisible:false
        ,statusChange:''
        ,is_sms_bind_isp:1
        ,forceOpen:true
        ,title:'提示'
        ,cancel:''
        ,region:''
        , editModule: {
            id: ''
            , id_country: '1'
            , title: ''
            , lang: ''
            , code: ''
            , currency_id: '1'
            // , diy_email: '0'
            // , diy_email_2: '0'
            // , diy_email_3: '0'
            // , email: ''
            // , dodo_email: ''
            // , lark_email: ''
            , two_code: ''
            ,is_force_open_sms:''
        }
        , editModuleLoading: false
        , options: {
            id_country: []
            , currency_id: []
            , sonOptions: []
        }
        , sonModule: {
            id: ''
            , id_zone: '1'
            , title: ''
            , code: ''
        }
        , dialogFailList:false
        , failList:[]
        , errorList:[]   // 站点邮箱替换不成功列表
        , errorListMessage: '' // 站点邮箱替换不成功报错语句
      }
    }
    , mounted() {
        this.get();
    }
    , methods: {
        get() {
            this.table_loading = true;
            this.$http.get('/country.php?json=1&p='+this.page).then(function(res){
                this.table_loading = false;
                this.list = res.body.goodsList;
                this.page_count = res.body.pageCount;
            });
        }
        , handleCurrentChange(val){
            this.page = val;
            this.get();
        }
        , showDialogEdit(val,region){
            this.region = region;
            this.editModule = {
                id: ''
                , id_country: '1'
                , title: ''
                , lang: ''
                , code: ''
                , currency_id: '1'
                // , diy_email: '0'
                // , diy_email_2: '0'
                // , diy_email_3: '0'
                // , email: ''
                // , dodo_email: ''
                // , lark_email: ''
                , two_code: ''
                ,is_force_open_sms:''
            }
            this.$http.get('/country.php?act=edit&json=1&id='+val).then(function(res){
                this.dialogTopRegion = true;
                for(let key in this.editModule){
                    this.editModule[key] = res.body[key] || this.editModule[key];
                }
                // this.editModule.diy_email = res.body.email!="" ? '1' : '0';
                // this.editModule.diy_email_2 = res.body.dodo_email!="" ? '1' : '0';
                // this.editModule.diy_email_3 = res.body.lark_email!="" ? '1' : '0';
                this.editModule.id = res.body.id_zone;
                this.options.id_country = res.body.country;
                this.options.currency_id = res.body.currency;
                this.statusChange = this.editModule.is_force_open_sms;
                this.is_sms_bind_isp = res.body.is_sms_bind_isp || 0;
                if(this.editModule.is_force_open_sms == 'enable'){
                    this.forceOpen = true;
                }else if(this.editModule.is_force_open_sms == 'disable'){
                    this.forceOpen = false;
                }
            });
        }
        , handleSave(){ 
            var self = this;
            if( this.editModule.title == '' ){
                self.$message.error('请填写“区域”');
                return false;
            }
            if( this.editModule.lang == '' ){
                self.$message.error('请填写“区域（当地）”');
                return false;
            }
            if( this.editModule.code == '' ){
                self.$message.error('请填写“地区编码”');
                return false;
            }
            // if( this.editModule.diy_email == '1' ){
            //     if( this.editModule.email == '' ){
            //         self.$message.error('请填写“自定义邮箱”');
            //         return false;
            //     }
            //     var tof = /^[a-z0-9]+([._\\-]*[a-z0-9])*@([a-z0-9]+[-a-z0-9]*[a-z0-9]+.){1,63}[a-z0-9]+$/.test(this.editModule.email);
            //     if( tof == false ){
            //         self.$message.error('请填写正确的邮箱地址');
            //         return false;
            //     }
            // }
            // if( this.editModule.diy_email=='0' ){
            //     this.editModule.email = '';
            // }
            // if( this.editModule.diy_email_2=='0' ){
            //     this.editModule.dodo_email = '';
            // }
            // if( this.editModule.diy_email_3=='0' ){
            //     this.editModule.lark_email = '';
            // }
            this.forceOpen == true ? this.editModule.is_force_open_sms = 'enable':this.editModule.is_force_open_sms = 'disable';
            if(this.forceOpen == true && this.statusChange != this.editModule.is_force_open_sms){
                this.dialogVisible = true;
                this.title = '强制开通短信验证码';
                this.cancel='';
            }else if(this.forceOpen == false && this.statusChange != this.editModule.is_force_open_sms){
                this.dialogVisible = true;
                this.title = '取消强制开通短信验证码';
                this.cancel = '取消';
            }else{
                this.saveNext();
            }
            
        }
        ,saveNext(){
            var self = this;
            this.dialogVisible = false;
            this.editModuleLoading = true;
            this.forceOpen == true ?this.editModule.is_force_open_sms = 'enable' : this.editModule.is_force_open_sms = 'disable';
            this.$http.post('/country.php?act=save', self.editModule, {'emulateJSON': true}).then(res=>{
                this.editModuleLoading = false;
                if( res.body.ret == '1' ){
                    if(res.body.errorList.length != 0) {
                        this.errorList = res.body.errorList;
                        this.errorListMessage = res.body.msg;
                        setTimeout(()=>{ this.errList = true; },200)
                    }else{
                        self.$message({ message: "成功", type: 'success' });
                    }
                    self.dialogTopRegion = false;
                    self.get();
                }else{
                    self.$message.error(res.body.msg);
                }
            });
        }
        , handleNew(){
            var self = this;
            this.$http.get('/country.php?act=edit&json=1').then(function(res){
                this.dialogTopRegion = true;
                self.editModule = {
                    id: ''
                    , id_country: '1'
                    , title: ''
                    , lang: ''
                    , code: ''
                    , currency_id: '1'
                }
                this.options.id_country = res.body.country;
                this.options.currency_id = res.body.currency;
                self.dialogTopRegion = true;
            });
        }
        , showSecRegionDialog(val) {
            var self = this;
            this.$http.get('/country.php?act=add_son&json=1').then(function(res){
                this.dialogSecRegion = true;
                self.sonModule = {
                    id_zone: '1'
                    , title: ''
                    , code: ''
                }
                this.options.sonOptions = res.body.zone;
            });
        }
        , handleSaveSon(){
            var self = this;
            this.$http.post('/country.php?act=add_son_save&json=1', self.sonModule, {'emulateJSON': true}).then(res=>{
                if( res.body.ret == '1' ){
                    self.$message({ message: "成功", type: 'success' });
                    self.dialogSecRegion = false;
                    self.get();
                }else{
                    self.$message.error('失败');
                }
            });
        }
        , handleSync(){
            var self = this;
            this.table_loading = true;
            this.$http.get('/country.php?act=syncZone').then(res=>{
                if (res.body.ret == "1") {
                    self.$message({ message: res.body.msg, type: 'success' });
                    self.get();
                    self.table_loading = false;

                    if(res.body.fail_list !=""){
                        self.failList = res.body.fail_list;
                        self.dialogFailList = true;
                    }
                }else{
                    self.$message(res.body.msg);
                    self.table_loading = false;
                }
            });
        }
    }
  }
</script>

