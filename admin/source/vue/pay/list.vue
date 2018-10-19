<template>
    <div id="page-comment-list">
        <div class="header-panel">
            <div class="control">
                <div class='op-flexbox' v-on:keyup.enter="">
                    <div class='op-box'>
                         <el-popover ref='popmenu' placement="bottom-start" trigger="click">
                            <div class='item' @click='selectType("ocean", "钱海支付")' >
                                <el-radio class="radio" v-model="code" label="ocean">钱海支付</el-radio>
                            </div>
                            <div class='item' @click='selectType("asiaBill","AsiaBill")' >
                                <el-radio class="radio" v-model="code" label="asiaBill">AsiaBill</el-radio>
                            </div>
                        </el-popover>
                        <div class='select-category'>
                            <span class="el-dropdown-link" v-popover:popmenu>
                                <span ref='category' v-text='cmd'></span><i class="el-icon-caret-bottom el-icon--right"></i>
                            </span>
                        </div>
                        <el-input placeholder="输入域名" icon="search" v-model="domain" :on-icon-click="handleSearch">
                        </el-input>
                    </div>
                </div>
                <div class="operations">
                    <div @click='handleNew'>
                        <el-button icon="plus" type="primary">新增</el-button>
                    </div>
                </div>
            </div>

        </div>

        <div class="container">
            
            <el-table :data="list" border tooltip-effect="dark" v-loading='table_loading' element-loading-text="拼命加载中">
                <el-table-column prop="payment_id" label="ID" width="100"></el-table-column>
                <el-table-column prop="domain" label="域名" width="200"></el-table-column>
                <el-table-column prop="title" label="服务商" width="100"></el-table-column>
                <el-table-column prop="data" label="数据">
                    <template scope='scope'>
                            <el-tag v-for = '(item, key) in scope.row.data' style='margin-right: 5px;' type='primary'>
                                {{configs ? configs[scope.row.code][key] : key}}: {{item}}
                            </el-tag>
                        
                    </template>
                </el-table-column>
                <el-table-column prop="" label="操作" width="120">
                    <template scope="scope">
                            <a class="scope-op" href="javascript:void(0)" @click='handleEdit(scope.row)'>编辑</a>
                    </template>
                </el-table-column>
            </el-table>
            <el-pagination small layout="prev, pager, next" :page-size="1" :current-page.sync="pagination.page" @current-change="handleCurrentChange"
                :total="pagination.count">
            </el-pagination>
            
        </div>

        <el-dialog :visible.sync='editing'>
            <el-form>
                <el-form-item label="服务商">
                    <el-select v-model='editee.code' @change='changeCode(editee.code)'>
                        <el-option key='ocean' value='ocean' label='钱海支付'></el-option>
                        <el-option key='asiaBill' value='asiaBill' label='AsiaBill'></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label='域名'>
                    <el-input v-model='editee.domain'></el-input>
                </el-form-item>
                <el-form-item :label="field_config[0].title"  v-if="field_config[0]">
                    <el-input v-model="editee[field_config[0].name]"></el-input>
                </el-form-item>
                <el-form-item :label="field_config[1].title"  v-if="field_config[1]">
                    <el-input v-model="editee[field_config[1].name]"></el-input>
                </el-form-item>
                <el-form-item :label="field_config[2].title"  v-if="field_config[2]">
                    <el-input v-model="editee[field_config[2].name]"></el-input>
                </el-form-item>
                <el-form-item :label="field_config[3].title" v-if="field_config[3]">
                    <el-input v-model="editee[field_config[3].name]"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="handleSave">保存</el-button>
                    <el-button @click='() => editing=false'>取消</el-button>
                </el-form-item>
            </el-form>
        </el-dialog>
    </div>
</template>

<script>
    Vue.http.options.headers = {
        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
    };
    export default {
        data() {
            return {
                domain: "",
                code: "ocean",
                cmd: "钱海支付",
                list: [],
                pagination: {
                    page: 1,
                    count: 0,
                },
                table_loading: false,
                editing: false,
                field_config: [

                ],
                editee: {
                    code: "ocean",
                    id:         "",
                    account:    "",
                    terminal:   "",
                    secureCode: "",
                    publicKey:  "",
                    domain:     "",

                },
                configs:{
                    ocean: {},
                    asiaBill: {},
                }
            }
        }
        , mounted() {
            this.get();
        }
        , methods: {
            get() {
                Promise.all([
                this.$service('pay#get', 'ocean').then(res => {
                    this.configs.ocean = {};
                    res.body.forEach(x => this.configs.ocean[x.name] = x.title);
                }),
                this.$service('pay#get', 'asiaBill').then(res => {
                    this.configs.asiaBill = {};
                    res.body.forEach(x => this.configs.asiaBill[x.name] = x.title);
                })
                ]).then(res => {
                   this.$service('pay#list').then(res => this.list = res.body);
                });
                this.$service('pay#get', this.code).then(res => {
                    this.field_config = res.body;
                    this.field_config.forEach(x => {
                       this.editee[name] = "";
                    });
                });
            },
            changeCode(code){
                this.$service('pay#get', code).then(res => {
                    this.field_config = res.body;
                    this.field_config.forEach(x => {
                       this.editee[name] = "";
                    });
                });
            },
            selectType(name, text){
                this.code = name;
                this.cmd  = text;
                setTimeout(_ => document.dispatchEvent(new MouseEvent("click", {clientX: 0, clientY: 0})), 200);
                this.get();
            }, handleNew(){
                this.editee.domain = this.domain;
                this.editee.code   = this.code;
                this.editee.id = 0;
                this.editee.account = "";
                this.editee.terminal = "";
                this.editee.secureCode = "";
                this.editee.publicKey = "";
                this.editing = true;
            }, handleEdit(row){
                
                Object.assign(this.editee, row.data);
                this.editee.domain = row.domain;
                this.editee.id     = row.payment_id;
                this.editee.code   = row.code;
                this.changeCode(this.editee.code);
                this.editing = true;
            }, handleDelete(){

            }, handleRestore(){

            }, handleSearch(){

            }, handleUpload(){

            }, handleCurrentChange(){

            }, handleSave(){
                this.$service('pay#save', this.editee, this.field_config, this.editee.code).submitMessage().then(res => {
                    this.editing = false;
                    this.get();
                });
            }
        }
    }

</script>