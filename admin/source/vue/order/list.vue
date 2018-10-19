<template>
    <el-row id="page-order-list">
        <div class="header-panel">
            <div class="control">
                <el-row>
                    <el-form
                        :inline="true"
                        :model="goodData"
                        ref="goodData"
                        label-width="80px"
                        class="demo-form-inline demo-ruleForm"> 
                            <el-form-item>
                                <div class='op-box' @keyup.enter='handleSearch'>
                                    <el-popover ref='popmenu'
                                        placement="bottom-start"
                                        trigger="click">
                                        <div class='item' @click='cmdtext="订单号"'><el-radio class="radio" v-model="cmd" label="order_no">订单号</el-radio></div>
                                        <div class='item' @click='cmdtext="产品ID"'><el-radio class="radio"  v-model="cmd" label="product_id">产品ID</el-radio></div>  
                                    </el-popover>
                                    <div class='select-category'>
                                        <span class="el-dropdown-link" v-popover:popmenu>
                                            <span ref='category' v-text='cmdtext'></span><i class="el-icon-caret-bottom el-icon--right"></i>
                                        </span>
                                    </div>
                                    <el-input
                                        :placeholder="cmdtext"
                                        icon="search"
                                        v-model="search"
                                        v-loading.fullscreen.lock="fullscreenLoading"
                                        element-loading-text="拼命加载中"
                                        element-loading-spinner="el-icon-loading"
                                        element-loading-background="rgba(0, 0, 0, 0.8)"
                                        :on-icon-click="handleSearch">
                                    </el-input>
                                </div>
                            </el-form-item>
                            <el-form-item label="操作时间" prop="startime">
                                <el-date-picker
                                    v-model="goodData.startime"
                                    format="yyyy-MM-dd HH:mm:ss"
                                    type="datetime"
                                    placeholder="开始时间"
                                    align="right"
                                    :picker-options="pickerOptions1" @change="startime">
                                </el-date-picker>
                            </el-form-item>
                            <el-form-item>
                                <el-date-picker
                                    v-model="goodData.endtime"
                                    format="yyyy-MM-dd HH:mm:ss"
                                    type="datetime"
                                    placeholder="结束时间"
                                    align="right"
                                    :picker-options="pickerOptions1" @change="endtime">
                                </el-date-picker>
                            </el-form-item>
                            <el-form-item > 
                                <div class="operations">
                                    <el-dropdown  @command='handleSetERP' trigger='click'>
                                        <span class="el-dropdown-link" trigger='click'>
                                            <span ref='erp_status'  class='erp_status'>按ERP状态筛选</span><i class="el-icon-caret-bottom el-icon--right"></i>
                                        </span>
                                        <el-dropdown-menu slot="dropdown">
                                            <el-dropdown-item command='全部|'>全部</el-dropdown-item>
                                            <el-dropdown-item command='回调通信成功|SUCCESS'>回调通信成功</el-dropdown-item>
                                            <el-dropdown-item command='回调通信失败|FAIL'>回调通信失败</el-dropdown-item>
                                            <el-dropdown-item command='创建订单失败|FAIL_CREATE'>创建订单失败</el-dropdown-item>
                                        </el-dropdown-menu>
                                    </el-dropdown>
                                    <el-dropdown  @command='handleSetOrder' trigger='click'>
                                        <span class="el-dropdown-link" trigger='click' >
                                        <span ref='order_status' class='order_status'>按订单状态筛选</span><i class="el-icon-caret-bottom el-icon--right"></i>
                                        </span>
                                        <el-dropdown-menu slot="dropdown">
                                            <el-dropdown-item command='全部|'>全部</el-dropdown-item>
                                            <el-dropdown-item command='支付成功|SUCCESS'>支付成功</el-dropdown-item>
                                            <el-dropdown-item command='支付失败|CREATE_FAIL'>支付失败</el-dropdown-item>
                                            <el-dropdown-item command='下单未支付|NOT_PAID'>下单未支付</el-dropdown-item>
                                        </el-dropdown-menu>
                                    </el-dropdown>
                                </div>
                            </el-form-item>
                    </el-form>
                </el-row>    
            </div>
        </div>

		<div class="container main-table">
    		<el-table
    		    ref="multipleTable"
    		    :data="filteredList"
    		    border
    		    tooltip-effect="dark"
                :default-expand-all='true'
                v-loading='table_loading'
                element-loading-text="拼命加载中"
                element-loading-spinner="el-icon-loading"
                element-loading-background="rgba(0, 0, 0, 0.8)"
                >
                <el-table-column type="expand">
                      <template scope="scope">
                          <div class='scope-content'>
                        <input type='checkbox' class='is-open' :id='"btn" + scope.row.order_id + "_" + scope.$index'>
                        <div class="expand-link">
                            <label v-bind:for='"btn" + scope.row.order_id + "_" + scope.$index'>
                                <div class='expand-button '><span >展开</span><i class='material-icons'>add</i></div>
                                <div class='collapse-button'><span >收起</span><i class='material-icons'>remove</i></div>
                            </label>
                        </div>
                        <el-row label-position="left" inline class="table-expand">
                            <div class="detail">
                                <img class="product-img" :src="scope.row.thumb">
                                <div class="row">
                                    <div class="col"><div class='product-name'>{{ scope.row.title }}</div></div>
                                    <div class="bottom-row">
                                         <div class="col" style='display: table-row;' v-for='item in scope.row.orderGoods'>
                                                <div class='col product-attr'>{{ item.title }} X{{ item.num }}</div>
                                         </div>
                                    </div>
                                </div>
                            </div>
                            <div class='rb-row'>
                             <div class='col product-price'>单价: {{ scope.row.currency_code }} {{ scope.row.orderGoods && (Math.max.apply(Math, scope.row.orderGoods.map(x => ~~x.price)).toFixed(2)) }}</div>
                             <div class='col product-total'>总价: {{ scope.row.currency_code }} {{ scope.row.payment_amount }}</div>
                            </div>
                         </el-row>
                        </div>
                        <div class="spacing" v-if='scope.$index != order_data.goodsList.length - 1'>
                                    
                        </div>
                      </template>
                   </el-table-column>
                    <el-table-column label="序号" width='124'><template scope='scope'>
                        <div class="mobile field">{{scope.$index+1 | countNum}}</div>
                    </template></el-table-column>
                    <el-table-column label="ERP订单信息" width='184'
                        :filters="this.$service('table#tag', this.filteredList, 'erp_status').map(
                            ({text, value}) => ({text: $service('erp_status', value) || text, value})
                        )"
                        :filter-method="$service('table#filterTag', 'erp_status')">
                        <template scope='scope'>
                            <div class="erp_id field" @click="handviewClick(scope.row.order_id)">{{scope.row.erp_no}}</div>
                            <div class="erp_status field">{{scope.row.erp_status | erp_status}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="订单信息" width='218'><template scope='scope'>
                        <div class="order_id field">{{scope.row.order_no}}</div>
                        <div class="order_time field">{{scope.row.add_time}}</div>
                    </template></el-table-column> 
                    <el-table-column label="域名" width='128'><template scope='scope'>
                        <div class="customer field">{{JSON.parse(scope.row.post_erp_data).web_url}}</div>
                    </template></el-table-column>
                    <el-table-column label="下单人" width='86'><template scope='scope'>
                        <div class="customer field">{{scope.row.name}}</div>
                    </template></el-table-column>
                    <el-table-column label="手机" width='124'><template scope='scope'>
                        <div class="mobile field">{{scope.row.mobile}}</div>
                    </template></el-table-column>
                    <el-table-column label="mail" width='124'><template scope='scope'>
                        <div class="mail field">{{scope.row.email}}</div>
                    </template></el-table-column>
                    <el-table-column label="地区" width='244'
                        :filters="this.$service('table#tag', this.filteredList, 'address')"
                        :filter-method="$service('table#filterTag', 'address')"
                        filter-placement="bottom-end">
                        <template scope='scope'>
                            <div class="address field">{{scope.row.address}}</div>
                        </template>
                    </el-table-column>

                    <el-table-column label="订单来源" width='124'><template scope='scope'>
                        <div class="mail field">{{scope.row.http_referer}}</div>
                    </template></el-table-column>
                    
                    <el-table-column :label="add_time_label" width='124'>
                        <template scope='scope'>
                            <div class="address field">{{scope.row.add_time | filterDate }}</div>
                        </template>
                    </el-table-column>
                    <el-table-column label="支付状态"
                    :filters="this.$service('table#tag', this.filteredList, 'order_status').map(
                            ({text, value}) => ({text: $service('order_status', value) || text, value})
                      )"
                     :filter-method="$service('table#filterTag', 'order_status')">
                        <template scope='scope'>
                            <div class="payment_type field">{{scope.row.pay_type}}</div>
                            <div class="payment_status field">{{scope.row.order_status | order_status}}</div>
                        </template>
                    </el-table-column>
            </el-table>
             <el-pagination
                small
                layout="prev, pager, next, jumper, slot"
                :page-size="1"
                @current-change="handleCurrentChange"
                :total="order_data.pageCount"
                class='with-button'>
                <el-button class='el-pagination__append'>跳转</el-button>
            </el-pagination>
    	</div>
  </el-row>
</template>

<script>
// import moment from "moment";
const ORDER_STATUS = function(name) {
    return {
        SUCCESS: "支付成功",
        FAIL: "支付失败",
        NOT_PAID: "下单未支付"
    }[name];
};

const ERP_STATUS = function(name) {
    return {
        SUCCESS: "回调通信成功",
        FAIL: "回调通信失败",
        CREATE_FAIL: "创建订单失败",
        FAIL_CREATE: "创建订单失败"
    }[name];
};
export default {
    data() {
        return {
            fullscreenLoading:false,
            pickerOptions1:{
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
            order_data: [],
            page: 1,
            multipleSelection: [],
            order_no: "",
            table_loading: false,
            erp_status: "",
            order_status: "",
            selectDateVisible: false,
            add_time_label: "筛选下单日期",
            clickedTime: 0,
            selDate: "",
            filteredList: [],
            goodData:{
            startime: "",
            endtime: ""
            },
            filteredDate: "",
            pickerOptions0: {},
            cmdtext: "订单号",
            cmd: "order_no",
            search: "",
            date: {
                year: new Date().getFullYear(),
                month: parseInt(new Date().getMonth() + 1),
            }
        };
    },
    mounted() {
        window.app = this;
        this.get();
        // let s = setInterval(() => {
        //     if (document.querySelector("th.add_time")) {
        //         clearInterval(s);
        //         let self = this;
        //         new Vue({
        //             template: `<el-date-picker placeholder="筛选下单日期V" size='mini' :editable='false' popper-class='selectDate' :clearable='true' v-model='date' @change='handleDateChange'></el-date-picker>`,
        //             data() {
        //                 return {
        //                     date: ""
        //                 };
        //             },
        //             methods: {
        //                 handleDateChange(val) {
        //                     self.handleDateChange(val);
        //                 }
        //             }
        //         }).$mount(document.querySelector("th.add_time").firstChild);
        //     }
        // }, 10);
    },
    methods: {
         startime(val){
        this.goodData.startime = val;
      },
      endtime(val){
        this.goodData.endtime = val;
      },
        get() {
            var startime = Number(new Date(this.goodData.startime).getTime());
            var endtime = Number(new Date(this.goodData.endtime).getTime());
            var reg = /^[0-9]*$/;
            if(startime>endtime){alert("开始时间不能大于结束时间！");return false;}
            if(this.cmd == "product_id" && this.search != "" && !reg.test(this.search)){
                alert("只能输入数字！");return false;
            };
            this.$service(
                "order#list",
                this.order_status,
                this.erp_status,
                this.goodData.startime,
                this.goodData.endtime,
                this.cmd,
                this.search,
                this.page,
            ).then(res => {
                this.order_data = res.body;
                this.filteredList = this.order_data.goodsList || [];
                this.filteredDate = "";
                this.fullscreenLoading = false;
            }).catch(error=>{
                this.fullscreenLoading = false;
            });
        },
        handleSetERP(command) {
            let [disp, val] = command.split("|");
            this.$refs.erp_status.innerText = disp;
            this.erp_status = val;
            this.get();
        },
        handleSetOrder(command) {
            let [disp, val] = command.split("|");
            this.$refs.order_status.innerText = disp;
            this.order_status = val;
            this.get();
        },
        handleEdit(val) {
            console.log(val);
        },
        handleDelete(val) {
            console.log(val);
        },
        handleSearch() {
            this.fullscreenLoading = true;
            this.get();
        },
        handleCurrentChange(val) {
            this.page = val;
            console.log(this.page)
            this.get();
        },
        handleSetFilter(val) {
            return function(command) {
                console.log(command);
            };
        },
        // handleDateChange(date) {
        //     console.log('s')
        //     let c = moment(date).format("YYYYMMDD");
        //     this.filteredList =
        //         (this.order_data.goodsList || []).filter(
        //             x => moment(x.add_time).format("YYYYMMDD") == c
        //         ) || [];
        // },
        handviewClick(id) {
            this.$router.push({ path: "/orders/" + id });
        }
    },
    filters: {
        erp_status: ERP_STATUS,
        order_status: ORDER_STATUS,
        filterDate: function(val) {
            return Vue.service("|>date", val);
        },
        countNum: (val)=> {
            return Number(val)+(window.app.page-1)*20
        }
    }
};
</script>
<style scoped>
    .header-panel .control .el-date-editor--datetime{
        border:none;
    }
    .header-panel .header-panel .control .el-date-editor--datetime input{
        border: 1px solid #bfcbd9!important;
    }
    .header-panel .header-panel .control .el-input .el-input__inner {
        border: 1px solid #bfcbd9!important;
    }
    .header-panel .op-box{
        width:100%;
    }
    .header-panel .control .select-category{
        height:40px;
    }
</style>


