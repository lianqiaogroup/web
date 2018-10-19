<template>
    <section class="analytics-index">
        <div class="header-panel">
            <div class="control">
                <el-select
                    v-if="false"
                    filterable
                    class='searchuser'
                    v-model="uid"
                    slot="prepend"
                    placeholder="建站优化师"
                    icon="search"
                    style="">
                        <el-option
                            v-for='item in ad_members'
                            :label='item.name'
                            :key='item.ad_member_id'
                            :value='item.ad_member_id'>
                        </el-option>
                </el-select>
                <el-form :inline="true" :model="filter" label-width="140px" class="demo-form-inline demo-ruleForm" style="float:left;">
                    <el-form-item>
                        <div class='op-flexbox'>
                            <div class='op-box'>
                                <el-dropdown class='select-category' @command='handleSearchCategory'>
                                    <span class="el-dropdown" trigger='click'>
                                        <span ref='category'>站点域名</span>
                                        <i class="el-icon-caret-bottom el-icon--right"></i>
                                    </span>
                                    <el-dropdown-menu slot="dropdown">
                                        <el-dropdown-item command='优化师|search_member'>优化师</el-dropdown-item>
                                        <el-dropdown-item command='站点域名|search_domain'>站点域名</el-dropdown-item>
                                    </el-dropdown-menu>
                                </el-dropdown>
                                <el-select v-model="uid" filterable clearable icon="search" v-if="searchCmd == 'search_member'">
                                    <el-option label="全部" key="-1" value="-1"></el-option>
                                    <el-option
                                        v-for='item in ad_members'
                                        :label='item.name'
                                        :key='item.ad_member_id'
                                        :value='item.ad_member_id'>
                                    </el-option>
                                </el-select>
                                <el-input
                                    placeholder="复制粘贴 站点链接"
                                    v-model="filter.url"
                                    ref='textBox'
                                    v-if="searchCmd == 'search_domain'">
                                </el-input>
                           </div>
                        </div>
                    </el-form-item>
<!--                     <el-form-item>
                        <el-select v-model="uid" placeholder="请选择建站优化师" icon="search">
                            <el-option v-if="!administrator || rootUser == 0" label="全部" key="" value=""></el-option>
                            <el-option
                                v-for='item in ad_members'
                                :label='item.name'
                                :key='item.ad_member_id'
                                :value='item.ad_member_id'>
                            </el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item>
                        <el-input v-model="filter.url" placeholder="输入产品地址"></el-input>
                    </el-form-item> -->
                    <el-form-item label="客户下单时间" class="daterange">
                        <el-date-picker
                            v-model="filter.daterange"
                            type="datetimerange"
                            placeholder="选择日期范围，最大7天"
                            :picker-options="pickerOptions">
                        </el-date-picker>
                        <p style="margin:0px;">最小查询单位为小时，请输入整点进行查询</p>
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="handleSearch">查询</el-button>
                    </el-form-item>
                </el-form>
                <div style="clear: both;"></div>
                <el-row>
                    <p>国外服务器的数据同步回国内服务器，存在1小时的延迟，所以查询到的数据可能与ERP的不一致, 非实时数据。
                        这是正常现象，请耐心等待。</p>
                    <p>只显示有出单的站点信息。按照客户下单时间统计的订单数。可查询的最大时间间隔为7天。</p>
                </el-row>
            </div>
        </div>
        <div class="container">
            <div class="totalOrder">总订单：{{totalOrder}}</div>
            <el-table
                ref="multipleTable"
                :data="list"
                border
                tooltip-effect="dark"
                v-loading='table_loading'
                element-loading-text="拼命加载中">
                <el-table-column prop="" label="" width="50"></el-table-column>
                <el-table-column prop="user_name" label="优化师"></el-table-column>
                <el-table-column prop="product_id" label="站点ID"></el-table-column>
                <el-table-column prop="" label="产品域名">
                    <template scope="scope">
                        {{ scope.row.domain }}/{{ scope.row.identity_tag }}
                    </template>
                </el-table-column>
                <el-table-column prop="is_del" label="站点状态"></el-table-column>
                <el-table-column prop="count" label="订单量"></el-table-column> 
            </el-table>
            <el-pagination
                v-if="list.length>0"
                layout="total, prev, pager, next, jumper"
                :current-page.sync="page"
                :total="page_count"
                :page-size='15'
                @current-change="handleCurrentChange">
            </el-pagination>
        </div>
    </section>
</template>
<style scoped>
    .select-category .el-dropdown{
        width: 88px;
        font-size: 14px;
        color: #666;
        padding: 4px;
        box-sizing: border-box;
    }
    .daterange .el-input{
        width: 330px !important;
    }
    .totalOrder{
        padding-left: 50px;
        font-size: 14px;
        margin-top: 10px;
        margin-bottom: 10px;
    }
</style>
<!--  -->
<script>
Vue.http.options.emulateJSON = true;
window.analyticsState = {
    // 已选开始时间
    startTime: '',
    // 已选择结束时间
    endTime: '',
}
export default {
    data() {
        return {
            api: {
                list: '' 
            },
            page: 1,
            page_count: 1,
            // 过滤器的所有参数
            filter: {
                // 产品链接
                url: '',
                // 日期区间
                daterange: '',
                // 已选开始时间
                startTime: window.analyticsState.startTime,
                // 已选择结束时间
                endTime: window.analyticsState.endTime,
            },
            // 日期控件参数
            pickerOptions: {
                disabledDate(time) {
                    /*if (time.getTime() > Date.now() - 8.64e7) {
                        return true;
                    } else {*/
                        if (window.analyticsState.startTime!=''){
                            return time.getTime() > window.analyticsState.startTime + 3600 * 1000 * 24 * 7;
                        }
                    // }
                },
                onPick(time) {
                    window.analyticsState.startTime = time.minDate.getTime();
                    if (time.maxDate) {
                        let a = time.minDate.getTime();
                        let b = time.maxDate.getTime() || 0;
                    }
                }
            },
            table_loading: false,
            // 优化师列表
            ad_members: [],
            // 优化师选中值
            uid: "-1",
            // 查询数据列表
            list: [],
            searchCmd:"search_domain",
            search_value:"",
            totalOrder:0
        }
    },
    mounted() {
        this.getGroupMember();
        var defadate = new Date();
        var Y = defadate.getFullYear();
        var M = defadate.getMonth();
        var D = defadate.getDate();
        this.filter.daterange = [new Date(Y,M,D), new Date()];
    },
    methods: {
        // 搜索
        handleSearch() {
            this.page = 1;
            this.get();
        },
        get() {
            this.filter.url = this.filter.url.replace('http://', '');
            this.filter.url = this.filter.url.replace(/\s/g, '');
            
            if(this.searchCmd == "search_domain"){
                this.search_value = this.filter.url;
                if (this.search_value == "") {
                    this.$message.error('请填写站点域名');
                    return false;
                }
            }else if(this.searchCmd == "search_member"){
                this.search_value = this.uid;
                if(this.search_value == ""){
                    this.$message.error('请选择优化师');
                    return false;
                }
            }
            if ((this.filter.url != "") && (/www\.[a-zA-Z0-9\-]+\./.test(this.filter.url)==false)) {
                this.$message.error('输入的站点域名有误，请重新输入');
                return false;
            }

            // 获取日期区间
            let startDate = this.format(this.filter.daterange ? this.filter.daterange[0] : this.fmtDate(Date.now())+' 00:00:00' );
            let endDate = this.format(this.filter.daterange ? this.filter.daterange[1] : this.fmtDate(Date.now())+' 23:59:59' );

            // 避免重复ajax
            if (this.table_loading==true) {
                return false;
            } else {
                this.table_loading = true;
            }

            let params = [
                `act=orderQuantityQuery`,
                `type=${this.searchCmd}`,
                `search_value=${this.search_value}`,
                `start_time=${startDate}`,
                `end_time=${endDate}`,
                `p=${this.page}`,
            ]
            this.totalOrder = 0;
            this.$http.get(`/order_quantity.php?${params.join('&')}`).then(res => {
                this.table_loading = false;
                if (res.body.ret==0) {
                    this.$message.error(res.body.message||'未知错误');
                    this.list = [];
                    this.page_count = 0;
                } else {
                    this.list = res.body.goodsList;
                    this.page_count = res.body.count || 0;
                    this.totalOrder = res.body.totalOrder;
                }
            }, (error) => {
                this.table_loading = false;
                this.$message.error(error.status+' '+error.statusText);
                this.list = [];
                this.page_count = 0;
            });
        },
        // 获取优化师列表
        getGroupMember() {
            this.$http.get('order_quantity.php?act=getDepartmentTargetUser').then(res=>{
                if (res.body.ret) {
                    this.ad_members = res.body.ret;
                }
            });
        },
        format(time) {
            //shijianchuo是整数，否则要parseInt转换
            var time = new Date(time);
            var y = time.getFullYear();
            var m = time.getMonth()+1;
            var d = time.getDate();
            var h = time.getHours();
            var mm = time.getMinutes();
            var s = time.getSeconds();
            return y+'-'+this.add0(m)+'-'+this.add0(d)+' '+this.add0(h)+':'+this.add0(mm)+':'+this.add0(s);
        },
        add0(m) {
            return m<10?'0'+m:m;
        },
        fmtDate(obj) {
            var date =  new Date(obj);
            var y = 1900+date.getYear();
            var m = "0"+(date.getMonth()+1);
            var d = "0"+date.getDate();
            return y+"-"+m.substring(m.length-2,m.length)+"-"+d.substring(d.length-2,d.length);
        },
        handleCurrentChange(val) {
            this.page = val;
            this.get();
        },
        handleSearchCategory(cmd) {
            this.keyword = "";
            let [text, cmdkey] = cmd.split("|");
            this.searchKey = text;
            this.searchCmd = cmdkey;
            this.$refs.category.innerText = text;
            this.search_value = "";
        },
    }
}
</script>
<style scoped>
    .control {
        height: auto !important;
    }
</style>
