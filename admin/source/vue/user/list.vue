<template>
<div class="users">
        <div class="header-panel">
            <div class="control">
                <div class="operations"></div>
                <div class="hero-title">管理员列表</div>
                <div class='op-flexbox1'>
                        <div class='op-box'  v-on:keyup.enter="get()" style='width: 800px;'>
                            <div @keyup.enter='get()'>
                                <input ref='uname' placeholder='用户ID' style='width: 80px; height: 24px; border-radius: 2px; border: 1px solid #0080FF; padding:8px 16px;'>
                                <input ref='cname' placeholder='用户中文名' style='width: 80px; height: 24px; border-radius: 2px; border: 1px solid #0080FF; padding:8px 16px;'>
                                <input ref='dname' placeholder='部门' style='width: 80px; height: 24px; border-radius: 2px; border: 1px solid #0080FF; padding:8px 16px;'>
                                <span style='width: 5px;'>&nbsp;</span>
                                <span>
                                <el-checkbox v-model='is_root'>只看管理员</el-checkbox>
                                <el-button type="text" size='mini' @click='get()'>查询</el-button>
                                </span>
                            </div>
                        </div>
                </div>
            </div>
        </div>
        <div class="container">
            <el-table
                :data="goodsList"
                border
			    tooltip-effect="dark"
                v-loading='table_loading'
                element-loading-text="拼命加载中">
                <el-table-column label="" prop="uid" width="34">
                    <template scope="scope"><div></div></template>
                </el-table-column>
                <el-table-column label='id'  prop='uid' width='112'></el-table-column>
                <el-table-column label='用户名称' prop='username'></el-table-column>
                <el-table-column label='姓名' prop='name_cn'></el-table-column>
                <el-table-column label='部门' prop='department'></el-table-column>
                <el-table-column label='是否管理员' prop='department'>
                    <template scope='inner'>
                        <span v-if='inner.row.is_admin == "1"'>是</span>
                        <span v-if='inner.row.is_admin == "0"'>否</span>
                    </template>
                </el-table-column>
                <el-table-column label='添加时间'>
                    <template scope='scope'>
                        {{scope.row.create_at | formatTime}}
                    </template>
                </el-table-column>
                <el-table-column label='操作'>
                    <template scope='scope'>
                        <el-button type='primary' size='mini' v-if='scope.row.is_admin == "0"' @click='setAdmin(scope.row, true)'>设为管理</el-button>
                        <el-button type='danger' size='mini' v-if='scope.row.is_admin == "1"' @click='setAdmin(scope.row, false)'>取消管理</el-button>
                    </template>
                </el-table-column>
            </el-table>
            <el-pagination
            small
            layout="prev, pager, next, jumper, slot"
            class='with-button'
            :page-size="1"
            :total="pageCount"
            :current-page.sync='page'
            @current-change="handleCurrentChange"
            >
             <el-button class='el-pagination__append'>跳转</el-button>
            </el-pagination>
        </div> 
</div>
</template>
<style scoped>
    .header-panel .control{
        height: 120px;
    }
</style>
<script>
    export default {
        data() { 
            return {
                goodsList: []
                , admin: ""
                , page: 1
                , table_loading: false
                , pageCount: 0
                , s_username: ''
                , s_namecn: ''
                , s_department: ''
                , is_root: false,
            }
        }
        , computed: {
            username(){
                return this.$store.state.username
            }
            , administrator(){
                return this.$store.state.auth.is_admin
            }
        }
        , mounted() {
            this.get();
        }
        , methods: {
            get(){
                var self = this;
                self.table_loading = true;
                console.log(this);
                this.$service("admin#get", this.$refs.uname.value, this.$refs.cname.value, this.$refs.dname.value, this.is_root, this.page).then(res => {
                    var d = res.body;
                    self.table_loading = false;
                    
                    if(d.res == "succ"){
                        self.pageCount = d.data.admin_list.total_p;
                        self.goodsList = d.data.admin_list.goodsList;
                    }
                   
                });
                
            }
            , handleCurrentChange(val){
                if(val >= 0 && val <= this.pageCount){
                    this.page = val;
                    this.get();
                }else if(val > this.pageCount){
                    this.page = this.pageCount;
                }
            }, setAdmin(row, isAdmin){
                this.$service('admin#setAdmin', row.username, isAdmin).then(res => {
                    this.get();
                })
            }
        }, 
        filters: {
            formatTime: val => Vue.service('dateTimeFormatShort', val)
        }

    }
</script>

<style scoped>
    .header-panel .control{
        height:130px;
    }
</style>

