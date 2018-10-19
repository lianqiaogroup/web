<template>

    <div class="users">
        <div class="header-panel">
            <div style='padding: 16px 51px; background: #F6F6F6;'>
                <el-form :inline="true" :model="searchParams" label-width="80px" class="demo-form-inline demo-ruleForm">
                    <el-row>
                        <el-col :span='4'>
                            <el-form-item label="开始时间" prop="start_time">
                                <el-date-picker v-model="searchParams.start_time" format="yyyy-MM-dd HH:mm:ss" type="datetime" placeholder="开始时间" align="left"
                                    :picker-options="pickerOptions1">
                                </el-date-picker>
                            </el-form-item>
                        </el-col>
                        <el-col :span='4'>
                            <el-form-item label="结束时间">
                                <el-date-picker v-model="searchParams.end_time" format="yyyy-MM-dd HH:mm:ss" type="datetime" placeholder="结束时间" align="left"
                                    :picker-options="pickerOptions1">
                                </el-date-picker>
                            </el-form-item>
                        </el-col>
                        <el-col :span='4'>
                            <el-form-item label="操作人姓名拼音" label-width="110px">
                                <el-input v-model="searchParams.name_cn"></el-input>
                            </el-form-item>
                        </el-col>
                        <el-col :span='4'>
                            <el-form-item label="操作人登陆ID" label-width="110px">
                                <el-input v-model="searchParams.act_loginid"></el-input>
                            </el-form-item>
                        </el-col>
                        <el-col :span='4'>
                            <el-form-item label="被操作人登陆ID" label-width="110px">
                                <el-input v-model="searchParams.loginid"></el-input>
                            </el-form-item>
                        </el-col>

                        <el-col :span='4'>
                            <el-form-item>
                                <el-button type="primary" @click="get()">查询</el-button>
                            </el-form-item>
                        </el-col>
                    </el-row>
                </el-form>
            </div>
        </div>

        <div class="container">
            <el-table :data="log" border tooltip-effect="dark" v-loading='table_loading' element-loading-text="拼命加载中">
                <el-table-column prop='act_loginid' label='操作人'>

                </el-table-column>
                <el-table-column prop='loginid' label='被操作人'>

                </el-table-column>
                <el-table-column prop='time' label='操作时间'>
                    <template scope='inner'>
                        {{inner.row.time | filterDate}}
                        <br> {{inner.row.time | filterTime}}
                    </template>
                </el-table-column>
                <el-table-column prop='time' label='详情'>
                    <template scope='inner'>
                        {{inner.row.act_desc}}
                        <br>
                    </template>
                </el-table-column>
            </el-table>
        </div>

        <el-pagination small layout="prev, pager, next, jumper, slot" :page-size="1" @current-change="handleCurrentChange" :total="page.count"
            class='with-button'>
            <el-button class='el-pagination__append'>跳转</el-button>
        </el-pagination>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                log: [],
                table_loading: false,
                searchParams: {
                    start_time: '',
                    end_time: '',
                    loginid: '',
                    name_cn: '',
                    act_loginid: '',
                },
                page: {
                    p: 1,
                    count: 1,
                },
                pickerOptions1: {
                    shortcuts: [{
                        text: '今天',
                        onClick(picker) {
                            picker.$emit('pick', new Date());
                        }
                    }, {
                        text: '昨天',
                        onClick(picker) {
                            const date = new Date();
                            date.setTime(date.getTime() - 3600 * 1000 * 24);
                            picker.$emit('pick', date);
                        }
                    }, {
                        text: '一周前',
                        onClick(picker) {
                            const date = new Date();
                            date.setTime(date.getTime() - 3600 * 1000 * 24 * 7);
                            picker.$emit('pick', date);
                        }
                    }]
                },
            }
        },
        methods: {
            get() {
                this.searchParams.p = this.page.p;
                this.$service('adminlog#get', this.searchParams.start_time, this.searchParams.end_time, this.searchParams.p,
                    this.searchParams.loginid, 
                    this.searchParams.act_loginid, 
                    this.searchParams.name_cn,
                
                ).then(res => {
                    if(res.body.ret == 1 || !res.body.msg){
                        this.log = res.body.goodsList.map(x => {
                            return Object.assign({}, x, {
                                time: new Date(x.act_time * 1000)
                            })
                        });
                        this.page.count = res.body.total_p;
                    }else{
                        console.error(res.body.msg);
                        this.$notify({title: '错误', text: res.body.msg});
                    }
                });

            },
            handleCurrentChange(val){
                this.page.p = val;
                this.get();
            }
        },
        mounted() {
            this.get();
        }
        , filters: {
            filterTime: function (val) {
                return Vue.service('|>time', val);
            },
            filterDate: function (val) {
                return Vue.service('|>date', val);
            }
        }
    }
</script>