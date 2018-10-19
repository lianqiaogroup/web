<template>
<div class="usergroup">
        <div class="header-panel">
            <div class="control">
                <div class="operations">
                        <div @click='$service("usergroup#add")'><el-button icon="plus" type="primary">新增用户组</el-button></div>
                </div>
                <div class="hero-title" >
                        用户组
                </div>
                
            </div>
        </div>
        <div class="container">
            <el-table :data='data.goodsList'
                border
			    tooltip-effect="dark"
                v-loading='table_loading'
                element-loading-text="拼命加载中">
                <el-table-column label='id'  prop='user_group_id' width='112'></el-table-column>
                <el-table-column label='用户组名称' prop='title' width='220'></el-table-column>
                <el-table-column label='备注' prop='remark' width='220'></el-table-column>
                <el-table-column label='添加时间' width='110'><template scope='scope'>
                        {{scope.row.create_at | formatTime}}
                    </template></el-table-column>
                <el-table-column label='操作' width="120"> 
                    <template scope="scope">
                         <div  v-if='administrator'>
                             <el-button size="small" @click='$service("usergroup#edit", scope.row.user_group_id)'>编辑</el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
              <el-pagination
            small
            layout="prev, pager, next, jumper, slot"
            :page-size="1"
            @current-change="handleCurrentChange"
            :total="data.pageCount"
            class='with-button'
            >
             <el-button class='el-pagination__append'>跳转</el-button>
            </el-pagination>
        </div>
</div>
</template>
<script>
    export default {
        data(){
            return {
                data: {
                    goodsList: []
                    , filtered: []
                }
                , page: 1
                , admin: ""
                , table_loading: false
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
        , mounted(){
            this.get();
        }
        , methods:{
            get(){
                this.table_loading = true;
                this.$service('usergroup#list', this.page).then(res => {
                    this.data = res.body;
                    Vue.nextTick(_ => this.table_loading = false);
                });
            }
            , handleCurrentChange(val){
                this.page = val;
                this.get();
            }            
        }
        , filters:{
            formatTime: val => Vue.service('dateTimeFormatShort', val)
        }
    }
</script>
