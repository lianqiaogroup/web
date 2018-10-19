<template>
    <div id="page-product-list">
        <div class="header-panel">
            <div class="breadcrumb">
                <ul>
                    <li class="title">您的位置：</li>
                    <li class="item"><a href="/">首页</a></li>
                    <li class="item action"><a href="/#/products">单品站管理</a></li>
                </ul>
            </div>
            <div class="control" v-show="oldControlIsShow">
                <div class=""></div>
                <div class="operations" style="display: flex;">
                  <div @click="oldControlIsShow = false" style='margin-right: 10px;'>
                      <el-button>进入新版</el-button>
                    </div>
                    <div @click='handleTransfer'>
                        <el-button icon="warning" v-if="administrator">转移部门</el-button>
                    </div>
                    <div @click='handleNewErp' style='margin-left: 10px;'>
                        <el-button icon="plus" type="primary" v-if=" username != ga_account ">新ERP新增产品</el-button>
                    </div>
                </div>
                <div class='op-flexbox' v-on:keyup.enter="handleSearch">
                    <div class='op-box'>
                        <el-popover ref='popmenu' placement="bottom-start" trigger="click">
                            <div class='item' @click='cmdtext="域名";  keyword=""; '>
                                <el-radio class="radio" v-model="cmd" label="domain">域名</el-radio>
                            </div>
                            <div class='item' @click='cmdtext="产品名"; keyword=""'>
                                <el-radio class="radio" v-model="cmd" label="title">产品名</el-radio>
                            </div>
                            <div class='item' @click='cmdtext="产品id"; keyword=""'>
                                <el-radio class="radio" v-model="cmd" label="product_id">产品id</el-radio>
                            </div>
                            <div class='item' @click='cmdtext="主题"; keyword=""'>
                                <el-radio class="radio" v-model="cmd" label="theme">主题</el-radio>
                            </div>
                            <div class='item' @click='cmdtext="ERPid"; keyword=""'>
                                <el-radio class="radio" v-model="cmd" label="erp_id">ERPid</el-radio>
                            </div>
                            <div class='item' @click='cmdtext="建站用户"; keyword=""'>
                                <el-radio class="radio" v-model="cmd" label="aid">建站用户</el-radio>
                            </div>
                            <div class='item' @click='cmdtext="优化师"; keyword=""'>
                                <el-radio class="radio" v-model="cmd" label="ad_member_id">优化师</el-radio>
                            </div>
                            <div class='item' @click='cmdtext="产品区域"; keyword=""'>
                                <el-radio class="radio" v-model="cmd" label="id_zone">产品区域</el-radio>
                            </div>
                        </el-popover>
                        <div class='select-category'>
                            <span class="el-dropdown-link" v-popover:popmenu>
                                <span ref='category' v-text='cmdtext'></span><i class="el-icon-caret-bottom el-icon--right"></i>
                            </span>
                        </div>
                        <el-input placeholder="输入关键词" :icon="keyword ? 'close' : 'search'" v-model="keyword" v-on:keyup.enter="handleSearch" :on-icon-click="handleSearchOrClose">
                            <el-select v-model="keyword" filterable class='searchuser' slot="prepend" placeholder="建站用户" v-if="this.cmd == 'aid'" icon="search" @change='handleSearchSpecial()'>
                                <el-option v-for='item in agents' :label='item.name' :key='item.aid' :value='item.aid'></el-option>
                            </el-select>
                            <el-select v-model="keyword" filterable class='searchuser' slot="prepend" placeholder="优化师" v-if="this.cmd == 'ad_member_id'" icon="search" @change='handleSearchSpecial()'>
                                <el-option v-for='item in ad_members' :label='item.name' :key='item.ad_member_id' :value='item.ad_member_id'></el-option>
                            </el-select>
                            <el-select v-model="keyword" filterable class='searchuser' slot="prepend" placeholder="投放区域" v-if="this.cmd == 'id_zone'" icon="search" @change='handleSearchSpecial()'>
                                <el-option v-for='item in allZones' :key='item.id_zone' :value='item.id_zone' :label='item.title'></el-option>
                            </el-select>
                        </el-input>
                    </div>
                </div>
                <el-row class="op">
                    <el-col>
                        <el-col :span="6"><a href='javascript:;' @click="handleExportList" class="op-link">导出域名</a></el-col>
                        <el-col :span="10" class="op-checkbox" :offset="8">
                            <el-checkbox v-model="withDel" @change='handleSearch'>已删除的产品</el-checkbox>
                        </el-col>
                    </el-col>
                </el-row>
            </div>
            <!-- 新版搜索 start -->
            <div class="control-new control" v-show="!oldControlIsShow">
                <div class="operations" style="display: flex;">
                    <div style="line-height:44px;margin-right:10px;">
                        <el-button @click="handleExportListNew" class="op-link">导出域名</el-button>
                    </div>
                    <!-- <div @click="oldControlIsShow = true" style='margin-right: 10px;'>
                      <el-button class="oldEdition">返回旧版</el-button>
                    </div> -->
                    <div @click='handleTransfer'>
                        <el-button v-if="administrator" class="transfer">转移部门</el-button>
                    </div>
                    <div @click='handleNewErp' style='margin-left: 10px;'>
                        <el-button type="primary" v-if=" username != ga_account ">新增站点</el-button>
                    </div>
                </div>
                <div class="search" @keyup.enter="enterSearch">
                  <el-form class="demo-form-inline" :inline="true"> 
                    <el-form-item>
                      <el-input placeholder="请输入域名" v-model="search.domainname"></el-input>
                    </el-form-item>
                    <el-form-item>
                      <el-button type="primary" size="medium" @click="searchAll()">&nbsp;&nbsp;&nbsp;&nbsp;查询&nbsp;&nbsp;&nbsp;&nbsp;</el-button>
                      <el-button type="info" @click="clearSearch()" class="clearSearch">&nbsp;&nbsp;&nbsp;&nbsp;重置&nbsp;&nbsp;&nbsp;&nbsp;</el-button>
                    </el-form-item>
                    <el-form-item>
                      <span  @click="advancedSearch()" class="advancedSearch">高级搜索<i class="el-icon-caret-bottom" v-show="!moreSearchIsShow"></i><i class="el-icon-caret-top" v-show="moreSearchIsShow"></i></span>
                    </el-form-item>
                  </el-form>
                  <div class="moreSearch" v-show="moreSearchIsShow">
                    <el-row>
                        <el-col :span="6"><div class="pright"><el-input placeholder="请输入产品名" v-model="search.product_name"></el-input></div></el-col>
                        <el-col :span="6"><div class="pright"><el-input placeholder="请输入产品id" v-model="search.product_id"></el-input></div></el-col>
                        <el-col :span="6"><div class="pright"><el-input placeholder="请输入主题" v-model="search.theme"></el-input></div></el-col>
                        <el-col :span="6"><div class="pright"><el-input placeholder="请输入ERP id" v-model="search.erp_id"></el-input></div></el-col>
                    </el-row>
                    <el-row>
                        <el-col :span="6">
                            <div class="pright">
                                <el-select v-model="search.user" filterable class='searchuser' placeholder="建站用户">
                                  <el-option v-for='item in agents' :label='item.name' :key='item.aid' :value='item.aid'></el-option>
                                </el-select>
                            </div>
                        </el-col>
                        <el-col :span="6">
                            <div class="pright">
                                <el-select v-model="search.youhuashi" filterable class='searchuser' placeholder="建站优化师">
                                  <el-option v-for='item in ad_members' :label='item.name' :key='item.ad_member_id' :value='item.ad_member_id'></el-option>
                                </el-select>
                            </div>
                        </el-col>
                        <el-col :span="6">
                            <div class="pright">
                                <el-select v-model="search.product_area" filterable class='searchuser' placeholder="产品区域">
                                  <el-option v-for='item in allZones' :key='item.id_zone' :value='item.id_zone' :label='item.title'></el-option>
                                </el-select>
                            </div>
                        </el-col>
                        <el-col :span="6">
                            <div class="pright">
                                <el-select v-model="search.status" filterable class='searchuser' placeholder="站点状态">
                                  <el-option v-for='item in [{title:"上架",code:0},{title:"下架",code:1}]' :key='item.code' :value='item.code' :label='item.title'></el-option>
                                </el-select>
                            </div>
                        </el-col>
                    </el-row>
                  </div>
                  <!-- <div class="searchbox">
                      <el-button type="primary" @click="searchAll()">查询</el-button>
                      <el-button type="info" @click="clearSearch()" class="clearSearch">重置</el-button>
                  </div> -->
                </div>
                <!-- <el-row class="op">
                    <el-col>
                        <el-col :span="6"><a href='javascript:;' @click="handleExportList" class="op-link">导出域名</a></el-col>
                        <el-col :span="10" class="op-checkbox" :offset="8">
                            <el-checkbox v-model="withDel" @change='handleSearch'>已删除的产品</el-checkbox>
                        </el-col>
                    </el-col>
                </el-row> -->
            </div>
            <!-- 新版搜索 end -->
        </div>

        <div class="container">
            <div class="tablebox">
                <el-table ref="multipleTable" :data="product_data.goodsList" stripe tooltip-effect="dark" v-loading='table_loading' element-loading-text="拼命加载中"
                    @selection-change="handleSelectionChange" @filter-change='handleFilterChange'>
                    <el-table-column type="selection" class='product-selection' ref='sel' width="50"></el-table-column>
                    <el-table-column label='产品号' width='80' prop='product_id'></el-table-column>
                    <el-table-column label="产品信息" width='240'>
                        <template scope="scope">
                            <el-row>
                                <div class='scope-img-col'>
                                    <a :href="'http://'+scope.row.domain+'/'+scope.row.identity_tag" class="scope-domain" target="_blank">
                                        <img :src="scope.row.thumb||'/template/assets/img/product_preview.jpg'">
                                    </a>
                                </div>
                                <div class='scope-name-col'>
                                    <a :href="'http://'+scope.row.domain+'/'+scope.row.identity_tag" class="scope-name" target="_blank">
                                        <el-row class="scope-name">{{ scope.row.title }} </el-row>
                                    </a>
                                    <el-row><span class='scope-price'>{{ scope.row.price }}</span>&nbsp;<span class="scope-unit">{{ scope.row.currency_code }}</span></el-row>
                                </div>
                            </el-row>
                        </template>
                    </el-table-column>
                    <el-table-column label="ERP ID" width='110' prop='erp_number'></el-table-column>
                    <el-table-column label="域名" width='150'>
                        <template scope="scope">
                            <a :href="'http://'+scope.row.domain+'/'+scope.row.identity_tag" class="scope-domain" target="_blank">{{ scope.row.domain }}/{{ scope.row.identity_tag }}</a>
                        </template>
                    </el-table-column>
                    <template v-if="administrator">
                        <el-table-column
                            class-name='filter-caption'
                            label='地区'
                            width='110'
                            prop='zone'
                            :filter-multiple='false'
                            :filters="this.$service('table#tag', this.product_data.goodsList, 'zone')"
                            :filter-method="$service('table#filterTag', 'zone')" filter-placement="bottom">
                        </el-table-column>
                        <el-table-column label="语种" width='80' prop="lang"></el-table-column>
                        <el-table-column
                            class-name='filter-caption'
                            label="部门"
                            width='110'
                            prop="department"
                            :filter-multiple='false'
                            :filters="this.$service('table#tag', this.product_data.goodsList, 'id_department')"
                            :filter-method="$service('table#filterTag', 'id_department')"
                            filter-placement="bottom">
                        </el-table-column>
                        <el-table-column
                            class-name='filter-caption'
                            label="验证码"
                            width="120"
                            align="center"
                            prop="is_open_sms"
                            :filter-multiple='false'
                            :filters="this.$service('table#tag', this.product_data.goodsList, 'is_open_sms')"
                            :filter-method="$service('table#filterTag', 'is_open_sms')"
                            filter-placement="bottom">
                        </el-table-column>
                    </template>
                    <el-table-column
                        class-name='filter-caption'
                        label="优化师"
                        width='110'
                        prop="ad_member"
                        :filter-multiple='false'
                        :filters="this.$service('table#tag', this.product_data.goodsList, 'ad_member')"
                        :filter-method="$service('table#filterTag', 'ad_member')"
                        filter-placement="bottom">
                    </el-table-column>
                    <el-table-column label="添加时间" width='130'>
                        <template scope="scope">
                            <el-row class="scope-time">{{ scope.row.add_time | filterDate}}</el-row>
                            <el-row class="scope-time">{{ scope.row.add_time | filterTime}}</el-row>
                        </template>
                    </el-table-column>
                    <el-table-column class-name='filter-caption' label="主题模板" width='115' :filter-multiple='false' :filters="this.$service('table#tag', this.product_data.goodsList, 'theme')"
                        :filter-method="$service('table#filterTag', 'theme')" :filtered-value='filteredValue' filter-placement="bottom"
                        prop='theme'>
                        <template scope="scope">
                            <el-row class="scope-theme">
                                <template v-if="isDiyTheme(scope.row.theme)">
                                    <a @click="goThemeDiy(scope.row)">{{ scope.row.theme }}<i class="el-icon-setting"></i></a>
                                </template> 
                                <template v-else>
                                    {{ scope.row.theme }}
                                </template>
                            </el-row>
                        </template>
                    </el-table-column>
                    <el-table-column label="操作" width="160">
                        <template scope="scope">
                            <section v-if=' username == ga_account '>
                                
                            </section>
                            <section v-else>
                                <template v-if="scope.row.is_del=='0'">
                                    <a class="scope-op" href='javascript:;' @click='handleEdit(scope.row)'>编辑</a>
                                    <a class="scope-op" href='javascript:;' @click='handleCopy(scope.row)'>复制</a>
                                    <a class="scope-op" href='javascript:;' @click='handleDelete(scope.row)'>删除</a>
                                    <a class="scope-op" href='javascript:;' style="display:block;" @click='handleBIlink(scope.row)'>生成广告链接</a>
                                </template>
                                <template v-if="scope.row.is_del=='10'||scope.row.is_del=='1'">
                                    <a class="scope-op scope-resume" href='javascript:;' @click='handleResume(scope.row)'>恢复</a>
                                    <a class="scope-op" href="javascript:;" @click="handleInfoPhp(scope.row)">预览</a>
                                </template>
                            </section>
                        </template>
                    </el-table-column>
                </el-table>
            </div>
            <div class="pagebox">
                <!-- <el-pagination small layout="prev, pager, next, jumper, slot" :page-size="1" @current-change="handleCurrentChange" :current-page.sync="current_page" :total="product_data.pageCount"
                    class='with-button'>
                    <el-button class='el-pagination__append'>跳转</el-button>
                </el-pagination> -->
                <el-pagination
                  @current-change="handleCurrentChange"
                  :current-page.sync="current_page"
                  :page-size="1"
                  layout="prev, pager, next, jumper"
                  :total="product_data.pageCount">
                </el-pagination>
            </div>
        </div>

        <el-dialog :visible.sync='product_copying' title='复制产品'>
            <el-form>
                <el-form-item label='请输入域名'><br>
                    <el-select v-model="copy.domain">
                        <el-option v-for="item in copyDomain" :key="item.domain" :value="item.domain" :label="item.domain"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label='请输入二级目录'><br>
                    <el-input v-model='copy.identity_tag'></el-input>
                </el-form-item>
                <el-form-item label='请选择产品投放地区'><br>
                    <el-select v-model='copy.id_zone' @change="copyIdZone">
                        <el-option v-for='item in canCopyZones' :key='item.id_zone' :value='item.id_zone' :label='item.title'></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label='请选择语言'><br>
                    <el-select v-model='copy.lang' @change="copyLang">
                        <el-option v-for='(item,key) in Copylanguage' :key='key' :value='key' :label='item'></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label='请选择模板'><br>
                    <el-select v-model='copy.theme'>
                        <el-option v-for='item in Copythemes' :key='item.theme' :value='item.theme' :label='item.title'></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item>
                    <el-checkbox v-if="copy.lang != theCopyLang" v-model="copyComment" disabled>复制评论</el-checkbox>
                    <el-checkbox v-else v-model="copyComment">复制评论</el-checkbox>
                </el-form-item>
                <el-button type="primary" @click="handleCopyProduct()">保存</el-button>
                <el-button @click='() => product_copying = false'>取消</el-button>
            </el-form>
        </el-dialog>

        <el-dialog :visible.sync='transfer.show' title='批量转移产品站点' class="departmentalTransfer" center :close-on-click-modal='transfer.closeOnClickModal'>
            <el-form>
                <el-form-item label='*选择需要转入的部门'>
                    ID: {{ transfer.id_department }}<br>
                    <el-autocomplete
                        v-model="transfer.department"
                        :fetch-suggestions="transferFilterDepartment"
                        placeholder="请选择部门"
                        ref="transferDepartment"
                        @select="handleTransferSelectDepartment">
                    </el-autocomplete>
                </el-form-item>
                <el-form-item label='*选择需要转入的产品站点（一行一个，每次建议不超过50个，格式示例：www.abc.com/123）'><br>
                    <el-input type="textarea" :rows="5" v-model='transfer.domain' ref="transferDomain"></el-input>
                </el-form-item>
                <el-button @click='() => transfer.show = false'>取消</el-button>
                <el-button type="primary" @click="handleTransferProduct()">确认</el-button>
            </el-form>
        </el-dialog>
        <!-- 产品站点转移二次确认提示框 start -->
        <el-dialog :visible.sync='transfer.succConfirmationShow' class="departmentalTransferConfirm" center :close-on-click-modal='transfer.closeOnClickModal'>
            <div class="list">
              <p style="color:#000;text-align:center;font-size:20px;margin:0">是否转移下列产品站点到{{transfer.department}}?</p>
              <span style="color:red;text-align:center;display:block">（尽量一批转移完毕，否则同域名下的其他未转移到新部门的产品在第一批转移后会被下架）</span>
              <el-table :data="transfer.list" border style="width: 100%">
                <el-table-column prop="ad_member" label="优化师">
                </el-table-column>
                <el-table-column prop="product_id" label="产品id">
                </el-table-column>
                <el-table-column prop="site_url" label="产品站点">
                </el-table-column>
              </el-table>

              <!-- 会被下架产品列表 -->
              <div v-if="transfer.delProductListShow">
                <p style="color:#000;text-align:center;font-size:20px;margin-top:50px">此次转移会导致以下产品下架</p>
                <el-table :data="transfer.del_product_list" border style="width: 100%">
                  <el-table-column prop="ad_member" label="优化师">
                  </el-table-column>
                  <el-table-column prop="product_id" label="产品id">
                  </el-table-column>
                  <el-table-column prop="product_url" label="产品站点">
                  </el-table-column>
                </el-table>
              </div>
              

              <!-- 会被删除套餐列表 -->
              <div v-if="transfer.delcomboListShow">
                <p style="color:#000;text-align:center;font-size:20px;margin-top:50px">此次转移会导致以下套餐被删除</p>
                <el-table :data="transfer.del_combo_list" border style="width: 100%">
                  <el-table-column prop="product_id" label="产品id">
                  </el-table-column>
                  <el-table-column prop="combo_id" label="套餐id">
                  </el-table-column>
                  <el-table-column prop="title" label="套餐名称">
                  </el-table-column>
                </el-table>
              </div>
              
            </div>
            <el-button @click='() => transfer.succConfirmationShow = false'>取消</el-button>
            <el-button type="primary" @click="finalConfirmation()">确认</el-button>
        </el-dialog>
        <el-dialog :visible.sync='transfer.failConfirmationShow' class="departmentalTransferConfirm" center title="信息有误" :close-on-click-modal='transfer.closeOnClickModal'>
            <el-table :data="transfer.list" height="535" border style="width: 100%">
              <el-table-column prop="ad_member" label="优化师" >
              </el-table-column>
              <el-table-column prop="product_id" label="产品id" >
              </el-table-column>
              <el-table-column prop="site_url" label="产品站点">
              </el-table-column>
              <el-table-column prop="msg" label="错误类型">
              </el-table-column>
            </el-table>
            <el-button type="primary" @click="confirmError()">知道了</el-button>
        </el-dialog>
        <!-- 产品站点转移二次确认提示框 end -->
        
    </div>
</template>

<script>
Vue.http.options.headers = {
  "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8"
};

import qs from 'qs';
export default {
  data() {
    return {
      oldControlIsShow:false, //是否显示为旧版搜索
      moreSearchIsShow:false, //高级搜索是否显示
      current_page:1,       //当前页码
      product_data: require("../mock/products.json"),
      multipleSelection: [],
      keyword: "",
      withDel: false,
      page: 1,
      table_loading: false,
      cmd: "domain",
      adminlist: ["1", "2", "3"],
      domain: [],
      product_id: [],
      filters: [],
      tags: {},
      radio: "",
      cmdtext: "域名",
      agents: [],
      ad_members: [],
      filteredValue: [],
      allZones: [],
      copy: {
        product_id: 0,
        domain: "",
        id_zone: "",
        zone:"",
        identity_tag: "",
        lang:"",
        theme:"",
        is_comment:""
      },
      zones: [], // all zones
      canCopyZones: [], // 用于复制站点的地区（根据域名可用地区过滤，base on zones）
      Copylanguage:[],//用于复制站点的语言
      Copythemes:[],//用于复制站点的模板
      copyComment:true,//复制评论
      theCopyLang:"",//所复制站点原本的语言
      copyid_department:"",
      product_copying: false,
      adminInfo:"",
      copyDomain:[],
      handleFilterChanged: null,
      ga_account: "googleID",
      changing: false,
      BIshow: false,
      BIdata: {
        link: "",
        ad_media: "CPM",
        ad_group: "",
        ad_series: "",
        ad_name: "",
        ad_bilink: "",
        ad_id_department: "",
        ad_department: "",
        ad_loginname: "",
        ad_loginid: "",
        product_id: ""
      },
      transfer: {
        closeOnClickModal:false,
        list:[],  //转移清单（二次确认框）
        del_combo_list:[], //转移会导致套餐被删除清单
        del_product_list:[], //转移会导致产品下架清单
        succConfirmationShow:false, //产品站点转移二次确认框显示/隐藏(信息无误)
        failConfirmationShow:false, //产品站点转移二次确认框显示/隐藏(信息有误)
        delProductListShow:false,   //产品下架清单 显示/隐藏
        delcomboListShow:false,     //套餐被删除清单 显示/隐藏
        isShow: false,    //错误类型这一列是否显示
        show: false,
        domain: "",    //需要转移的产品站点
        domainArr:[],  //需要转移的产品站点清单
        departments: [],
        department: "",
        _department: "",
        id_department: "",
        uid: "",
        ader: "",
        _ader: "",
        aders: []
      },
      search:{
        domainname:"", //域名
        product_name:"", //产品名
        product_id:"",  //产品id
        theme:"",       //主题
        erp_id:"",      //erp id
        user:"",        //建站用户
        youhuashi:"",   //建站优化师
        product_area:"",  //产品区域
        status:'',        //站点状态
      }
    };
  },
  computed: {
    username() {
      return this.$store.state.username;
    },
    administrator() {
      return this.$store.state.auth.is_admin;
    }
  },
  created() {
    this.$watch("cmd", function() {
      switch (this.cmd) {
        case "id_zone":
          this.$service("productL#getAllZone").then(
            res => (this.allZones = res.body.ret)
          );
          break;
        case "aid":
          this.$service("productL#getAUser").then(
            res => (this.agents = res.body.ret)
          );
          break;
        case "ad_member_id":
          this.$service("productL#getAdUser").then(
            res => (this.ad_members = res.body.ret)
          );
          break;
      }
      document.dispatchEvent(
        new MouseEvent("click", { clientX: 0, clientY: 0 })
      );
    });
  },
  mounted() {
    window.product = this;
    this.get();
    this.getSearchOptionData();//获取新版搜索下拉框数据
    let self = this;

    let fix = setInterval(function() {
      if (!self.$refs.sel) return;
      let div = document.querySelectorAll(`.${self.$refs.sel.columnId} .cell`);
      if (div.length == 0) return;
      [...div].forEach(function(obj) {
        obj.style.paddingLeft = "0px";
      });
      clearInterval(fix);
    }, 1000);

    this.handleFilterChanged = function(e) {
      let el = e.target;
      if (!el.classList.contains("el-checkbox__original")) return;
      if (el.value == "Symbol(ALL)" && el.checked) {
        let group = Vue.element(el.closest(".el-checkbox-group"));
        // console.log(group);
      }
    };
    document.addEventListener("change", this.handleFilterChanged, true);
  },
  beforeDestroy() {
    if (this.handleFilterChanged)
      document.removeEventListener("change", this.handleFilterChanged);
  },
  methods: {
    get() {
      if (this.table_loading) {
        return;
      }
      this.$service("country#list").then(res => (this.zones = res.body));
      /*
                this.$service('productL#getAllZone').then(res => this.allZones = res.body.ret);
                this.$service('productL#getAdUser').then(res => this.ad_members = res.body.ret);
                this.$service('productL#getAUser').then(res => this.agents = res.body.ret);
            */
      this.table_loading = true;
      this.$service(
        "product#list",
        this.keyword ? this.cmd : undefined,
        this.keyword || undefined,
        this.withDel,
        this.page
      ).then(res => {
        setTimeout(_ => (this.table_loading = false), 0); // vue async feature
        this.product_data = res.body;
        this.adminInfo = res.body.admin;
        this.product_data.goodsList &&
          this.product_data.goodsList.map(res => {
            res.is_open_sms = res.is_open_sms == 1 ? "有" : "无";
          });
      });
      // console.log(this.product_data);
    },
    handleEdit(val) {
      this.$service("product#editerp", val.product_id);
    },
    handleEditErp(val) {
      this.$service("product#editerp", val.product_id);
    },
    handleDelete(val) {
      const h = this.$createElement;
      var warningLabel = null;
      this.$http
        .get(
          `/product_new.php?act=getRelComboByPid&product_id=${val.product_id}&json=1`
        )
        .then(response => {
          if (response.body.combo.length == 0) {
            warningLabel = h("p", null, [
              h("span", null, "确实要删除掉 "),
              h("span", null, val.title),
              h("span", null, " 吗")
            ]);
          } else {
            // console.log("response.body.combo");
            console.log(response.body.combo);

            warningLabel = h("div", null, [
              h("h4", null, "如果删除该产品,下列产品中的套餐将失效,是否删除?"),
              h("div", { attrs: { class: "del-box" } }, [
                h("table", { attrs: { class: "del-table" } }, [
                  h("thead", null, [
                    h("th", null, "产品id"),
                    h("th", null, "产品名称"),
                    h("th", null, "套餐id"),
                    h("th", null, "套餐名称")
                  ]),
                  h("tbody", null, [
                    response.body.combo.map(row => {
                      return h("tr", null, [
                        h("td", null, `${row.product_id}`),
                        h("td", null, `${row.product_title}`),
                        h("td", null, `${row.combo_id}`),
                        h("td", null, `${row.title}`)
                      ]);
                    })
                  ])
                ])
              ])
            ]);
          }
          console.log(response);
          this.$msgbox({
            title: "确认删除",
            message: warningLabel,
            showCancelButton: true,
            confirmButtonText: "确定",
            cancelButtonText: "取消"
          }).then(action => {
            if (action === "confirm") {
              this.$service("product#delete", val.product_id).then(res => {
                if (res.body.ret == "0") {
                  this.$message.error({
                    message: res.body.msg || "删除失败"
                  });
                } else {
                  this.$message({
                    message: res.body.msg || "删除成功"
                  });
                //   this.get();
                  this.searchAll(this.page)
                }
              });
            }
          });
        });
    },
    handleResume(val) {
      const h = this.$createElement;
      this.$msgbox({
        title: "确认恢复",
        message: h("p", null, [
          h("span", null, "确实要恢复 "),
          h("span", null, val.title),
          h("span", null, " 吗")
        ]),
        showCancelButton: true,
        confirmButtonText: "确定",
        cancelButtonText: "取消"
      }).then(action => {
        if (action === "confirm") {
          this.$service("product#resume", val.product_id).then(res => {
            if (res.body.ret == "0") {
              this.$message.error({
                message: res.body.msg || "恢复失败"
              });
            } else {
              this.$message({
                message: res.body.msg || "恢复成功"
              });
              this.get();
            }
          });
        }
      });
    },
    handleNew() {
      this.$service("product#new");
    },
    handleNewErp() {
      this.$service("product#newerp");
    },
    handleSearch() {
      if (!this.table_loading) {
        this.get();
      } else {
        this.$message({ message: "正在拼命加载中，请稍候" });
      }
    },
    handleSearchSpecial() {
      if (this.keyword == "") {
        return;
      }
      this.handleSearch();
    },
    handleCurrentChange(val) {
      this.page = val;
      if(this.oldControlIsShow){
        this.get();
      }else {
        this.searchAll(val);
      }
    },
    handleSelectionChange(val) {
      this.domain = val.map(x => x.domain);
      this.product_id = val.map(x => x.product_id);
    },
    handleClose() {
      this.keyword = "";
      this.handleSearch();
    },
    handleSearchOrClose() {
      this.keyword ? this.handleClose() : this.handleSearch();
    },
    getcopyLang(){
        this.$http.get('template/config/theme_language').then(responese=>{
            this.Copylanguage = JSON.parse(responese.body);

        })
    },
    getCopythemes(id){
        var datas = {
            'id_zone':this.copy.id_zone,
            'lang':this.copy.lang,
            'id_department': '0,' + id
        }
        this.$http.post('/theme_select.php',`id_zone=${datas.id_zone}&lang=${datas.lang}&id_department=${datas.id_department}`).then(responese=>{
            if(typeof responese.body != 'string'){
                this.Copythemes = responese.body;
                var theme = this.copy.theme;
                var tid = this.Copythemes.filter(res=>{return res.theme == theme});
                if(tid.length == 0){
                    this.copy.theme = '';
                }
            }else{
                this.Copythemes = [];
                this.copy.theme = '';
            }
        })
    },
    getCopyDomain(){
        var datas = this.adminInfo;
        this.$http.get(`product_new.php?act=getSeoDomain&id_department=${datas.id_department}&loginid=${datas.login_name}`).then(responese=>{
            this.copyDomain = responese.body.ret;
            this.copyDomain && this.copyDomain.map(res => {
                res.domain = "www." + res.domain;
            });
            var thecopy = this.copy.domain;
            var theDomain = this.copyDomain.filter(f => {return f.domain == thecopy});
            if(theDomain.length == 0){
                this.copy.domain = "";
            }
        });
        // console.log(this.copyDomain)
    },
    handleCopy(row) {
      // console.log(row)
      this.product_copying = true;
      this.copyComment = true;
      this.copy.domain = row.domain;//域名
      this.copy.identity_tag = "";//二级目录
      this.copy.lang = row.lang;//语言
      this.theCopyLang = row.lang;
      this.copy.theme = row.theme;//模板
      this.copyid_department = row.oa_id_department;//部门
      this.copy.id_zone = row.id_zone;//地区名称
      this.copy.zone = row.code;//地区编码
      this.getCopyDomain();
      this.getcopyLang();
      this.getCopythemes(row.oa_id_department);
      this.$http.post('/product_new.php',`number=${row.erp_number}&act=getErpProduct&product_id=${row.product_id}&ad_member=${row.ad_member}`).then(res=>{
        if(res.body.ret == 1){
          if(res.body.data.productZoneList.length == 0) {
            this.canCopyZones = [{id_zone: 0, title: "没有可选地区"}];
              // this.copy.domain = row.domain;
              this.copy.product_id = row.product_id;
              this.copy.id_zone = 0;
              this.copy.zone = '';
          }else {
            this.canCopyZones = res.body.data.productZoneList;
            // this.copy.domain = row.domain;
            this.copy.product_id = row.product_id;
            var iscanCopyZones = this.canCopyZones.filter(res=>{return res.id_zone == row.id_zone});
            if(iscanCopyZones.length == 0){
                this.copy.id_zone = '';//地区名称
                this.copy.zone = '';//地区编码
            }
          }
        }else if(res.body.ret == 0){
          this.$message.error(res.body.msg);
        }else{
          this.$message.error('接口错误');
        }
      });
    },
    copyIdZone(){
        //复制站点的地区
        this.getCopythemes(this.copyid_department);
        var id = this.copy.id_zone;
        var zone = this.canCopyZones.filter(res=>{return res.id_zone == id});
        if(zone.length != 0){
            this.copy.zone = zone[0].code;
        }
    },
    copyLang(){
        this.getCopythemes(this.copyid_department);
        if(this.copy.lang != this.theCopyLang){
            this.copyComment = false;
        }else{
            this.copyComment = true;
        }
    },
    handleCopyProduct() {
      if(this.copy.id_zone == '') {
        this.$message.error('请选择产品地区');
      }else if(this.copy.id_zone == 0){
          this.$message.error('没有可选地区。复制失败');
      }else if(this.copy.theme == ''){
        this.$message.error('请选择模板');
      }else{
        if (this.table_loading == true) {
          return false;
        } else {
          this.table_loading = true;
        }
        if(this.copyComment == true){
            this.copy.is_comment = 1;
        }else{
            this.copy.is_comment = 0;
        }
        this.$service("product#copy", this.copy).then(res => {
          this.table_loading = false;
          if (res.body.ret == 1) {
            this.get();
            this.product_copying = false;
          } else {
            this.$message.error(res.body.msg);
          }
        });
      }
      
    },
    handleFilterChange(...args) {
      console.log(args);
    },
    handleExportList() {
      let url = "/product_ext.php?act=exportdomain";
      let is_del = this.withDel==true ? 1 : 0;
      url = url + `&${this.cmd}=${this.keyword}&is_del=${is_del}`;
      window.open(url);
    },
    handleBIlink(val) {
      this.$router.push({ path: "/product/adlink/" + val.product_id });
      return;

      this.BIshow = true;
      this.$http
        .get("/product_new.php?act=getBILink&product_id=" + val.product_id)
        .then(res => {
          if (!Array.isArray(res.data.data.bidata)) {
            for (var ad in res.data.data.bidata) {
              this.BIdata[ad] = res.data.data.bidata[ad];
            }
          } else {
            this.BIdata = {
              link: "",
              ad_media: "CPM",
              ad_group: "",
              ad_series: "",
              ad_name: "",
              ad_bilink: "",
              ad_id_department: "",
              ad_department: "",
              ad_loginname: "",
              ad_loginid: "",
              product_id: val.product_id
            };
          }
          this.BIdata.link = "http://" + val.domain + "/" + val.identity_tag;
          if (!this.BIdata.ad_loginname) {
            this.$http
              .get(
                "/product_new.php?act=getProductExtData&product_id=" +
                  val.product_id +
                  "&oa_id_department=" +
                  val.oa_id_department +
                  "&ad_member=" +
                  val.ad_member
              )
              .then(res => {
                this.BIdata.ad_department = res.body.data.extdata.department;
                this.BIdata.ad_loginname = res.body.data.extdata.loginid;
                this.BIdata.ad_id_department = val.oa_id_department;
              });
          }
        });
    },
    saveBIlink() {
      if (!this.BIdata.ad_media) {
        this.$message("请输入广告媒介");
        return false;
      }
      if (!this.BIdata.ad_group) {
        this.$message("请输入广告组");
        return false;
      }
      if (!this.BIdata.ad_series) {
        this.$message("请输入广告系列");
        return false;
      }
      if (!this.BIdata.ad_name) {
        this.$message("请输入广告名称");
        return false;
      }
      this.BIdata.ad_bilink =
        this.BIdata.link +
        "?utm_source=facebook&utm_medium=" +
        this.BIdata.ad_media +
        "&utm_campaign=" +
        this.BIdata.ad_group +
        "-" +
        this.BIdata.ad_series +
        "-" +
        this.BIdata.ad_name +
        "&utm_term=" +
        this.BIdata.ad_id_department +
        "&utm_content=" +
        this.BIdata.ad_loginname;
      var formdata = new FormData();
      formdata.append("product_id", this.BIdata.product_id);
      formdata.append("ad_loginid", this.BIdata.ad_loginid);
      formdata.append("ad_channel", "facebook");
      formdata.append("ad_media", this.BIdata.ad_media);
      formdata.append("ad_group", this.BIdata.ad_group);
      formdata.append("ad_series", this.BIdata.ad_series);
      formdata.append("ad_name", this.BIdata.ad_name);
      formdata.append("ad_id_department", this.BIdata.ad_id_department);
      formdata.append("ad_bilink", this.BIdata.ad_bilink);
      this.$http.post("/product_new.php?act=saveBILink", formdata).then(res => {
        this.$message(res.body.msg ? res.body.msg : res.body.data.msg);
      });
    },
    isDiyTheme(themeCode) {
      var entry = [
        "style5_2",
        "style32_2",
        "style22",
        "style27_2",
        "style64",
        "style79",
        "style87"
      ];
      return entry.includes(themeCode);
    },
    goThemeDiy(data) {
      if (data.theme == "style79") {
        window.open("theme_edit.php?product_id=" + data.product_id);
      } else {
        this.$router.push({ path: "/themeDiy/" + data.product_id });
      }
    },
    handleInfoPhp(data) {
      var url = `/info.php?pid=${data.product_id}&uid=${data.aid}`;
      window.open(url);
    },
    // 显示转移部门弹层
    handleTransfer() {
      this.table_loading = true;
      this.$http.get("/product_new.php?act=getAllDepartment").then(res => {
        this.table_loading = false;
        this.transfer.show = true;
        if (res.body.ret == 1) {
          this.transfer.departments = res.body.departments.map(x => {
            return {
              value: x.department,
              id_department: x.id_department
            };
          });
        } else {
          this.$message.error(res.body.msg);
        }
      });
    },
    // 转移部门 - 筛选部门
    transferFilterDepartment(queryString, cb) {
      if (queryString != this.transfer._department) {
        this.transfer.id_department = "";
        this.transfer.uid = "";
        this.transfer.ader = "";
        this.transfer._ader = "";
      }
      var result = this.transferFilter(queryString, this.transfer.departments);
      cb(result);
    },
    // 转移部门 － 选择部门
    handleTransferSelectDepartment(param) {
      this.transfer._department = param.value;
      this.transfer.id_department = param.id_department;
    },
    transferFilter(queryString, collect) {
      return collect.filter(x => {
        return x.value.includes(queryString);
      });
    },
    //提交输入的待转移部门列表
    handleTransferProduct() {
      //判断文本域中的产品站点格式是否有误
      this.transfer.domainArr = this.transfer.domain.split("\n");
      for(var i =0;i<this.transfer.domainArr.length;i++){
        if(/\//.test(this.transfer.domainArr[i])){
          if(!(/^www\.[a-zA-Z0-9]+(-?)[a-zA-Z0-9]+\.[a-zA-Z]*\/[^,%&\}#]*$/.test(this.transfer.domainArr[i]))){
            break;
          }
        }else {
          if(!(/^www\.[a-zA-Z0-9]+-?[a-zA-Z0-9]+\.[a-zA-Z]*$/.test(this.transfer.domainArr[i]))) {
            break;
          }
        }
      }
      if(i!=this.transfer.domainArr.length) {
        this.$message.error("域名为空或域名不规范");
        this.$refs.transferDomain.$el.children[0].focus();
        return false;
      }
      if (this.transfer.id_department == "") {
        this.$message.error("请选择部门");
        this.$refs.transferDepartment.$el.children[0].children[0].focus();
        return false;
      }
      
      this.transfer.show = false;
      this.table_loading = true;
      var _api = `/product_new.php?act=checkoutToNewDepartment`;
      var param = {
        oa_id_department: this.transfer.id_department,
        list: this.transfer.domainArr.join(),
        department: this.transfer.department,
      }
      this.$http.post(_api, param, { emulateJSON: true }).then(res => {
        this.table_loading = false;
        if(res.body.ret == 0){
          setTimeout(()=>{
            this.transfer.failConfirmationShow = true;
          },200)
          this.transfer.list = res.body.fail_list;
        }else if(res.body.ret == 1){
          if(res.body.succ_list.length != 0) {  //判断可转移的列表
            var timer = setTimeout(()=>{
              this.transfer.succConfirmationShow = true;
            },200)
            this.transfer.list = res.body.succ_list;
            if(res.body.del_combo_list.length !=0){ //判断会被删除的套餐列表
                let _list = res.body.del_combo_list.filter(row=>{ return row.is_del != null; });
                _list.forEach(item=>{
                    let temp = item.is_del==0?'正常':item.is_del==1?'已删除':'err';
                    if(temp == 'err'){
                        this.$message.error("接口数据错误");
                        clearTimeout(timer);
                        return false;
                    }
                    item.title = `${item.title} (${temp})`;
                });
                this.transfer.del_combo_list = _list;
                this.transfer.delcomboListShow = true;
            }
            if(res.body.del_product_list.length !=0){ //判断会导致下架的产品
                let _list = res.body.del_product_list.filter(row=>{ return row.is_del != null; });
                    _list.forEach(item=>{
                        let temp = item.is_del==0?'正常':item.is_del==1?'已删除':item.is_del==10?'待下架':'err';
                        if(temp == 'err'){
                            this.$message.error("接口数据错误");
                            clearTimeout(timer);
                            return false;
                        }
                        item.product_url = `${item.domain}/${item.identity_tag} (${temp})`;
                    });
                    this.transfer.del_product_list = _list;
                    this.transfer.delProductListShow = true;
            }
          }else {
            setTimeout(()=>{
              // this.transfer.domain = '';
              // this.transfer.department = '';
              // this.transfer.id_department = '';
              this.transfer.show = true;
            },200)
            this.$message.error("没有需要转移的站点");
          }
        }else {
          this.$message.error("接口错误");
        }  
      });
    },
    //确认转移
    finalConfirmation(){
      this.table_loading = true;
      var productIds = [];
      this.transfer.list.forEach(item=>{
        productIds.push(+item.product_id);
      })
      var _api = `/product_new.php?act=changeNewDepartment`;
      var pdatas = {
        oa_id_department:this.transfer.id_department,
        list:productIds.join(),
        department:this.transfer.department
      }
      this.$http.post(_api,pdatas,{ emulateJSON: true }).then(res=>{
        console.log(res.body);
        this.table_loading = false;
        if (res.body.ret == 1) {
          this.get();
          this.transfer.succConfirmationShow = false;
          this.transfer.domain = '';
          this.transfer.department = '';
          this.transfer.id_department = '';
          this.$message("操作成功");
        } else {
          this.$message.error("转移失败");
        }
      })
    },
    //确认知道错误
    confirmError(){
      this.transfer.failConfirmationShow = false ;
      setTimeout(()=>{
        this.handleTransfer();
      },200)
    },
    //展开高级搜索
    advancedSearch(){
      if(this.moreSearchIsShow) {
        this.moreSearchIsShow = false;
      }else {
        this.moreSearchIsShow = true;
      }
    },
    //新版搜索获取下拉框数据
    getSearchOptionData(){
          this.$service("productL#getAllZone").then(
            res => (this.allZones = res.body.ret)
          );
          this.$service("productL#getAUser").then(
            res => (this.agents = res.body.ret)
          );
          this.$service("productL#getAdUser").then(
            res => (this.ad_members = res.body.ret)
          );
    },
    //搜索
    searchAll(page){
        this.current_page = page || 1;
        let p = page || 1;
        // let formData = `domain=${this.search.domainname}&title=${this.search.product_name}&product_id=${this.search.product_id}&theme=${this.search.theme}&erp_id=${this.search.erp_id}&aid=${this.search.user}&ad_member_id=${this.search.youhuashi}&id_zone=${this.search.product_area}&is_del=${this.search.status}&json=1&p=${p}`;
        let formData = qs.stringify({domain: this.search.domainname, title: this.search.product_name,product_id:this.search.product_id,theme:this.search.theme,erp_id:this.search.erp_id,aid:this.search.user,ad_member_id:this.search.youhuashi,id_zone:this.search.product_area,is_del:this.search.status,json:1,p:p});
        console.log(formData);
        let _api = `/product_new.php?${formData}`;
        if (this.table_loading) {
          return;
        }
        this.table_loading = true;
        this.$http.get(_api).then(res => {
          setTimeout(_ => (this.table_loading = false), 0); // vue async feature
          this.product_data = res.body;
          this.product_data.goodsList &&
            this.product_data.goodsList.map(res => {
              res.is_open_sms = res.is_open_sms == 1 ? "有" : "无";
            });
        });
    },
    //新版回车键搜索
    enterSearch(){
      if (!this.table_loading) {
        this.searchAll();
      } else {
        this.$message({ message: "正在拼命加载中，请稍候" });
      }
    },
    //清空搜索项
    clearSearch(){
        this.search.domainname = "" ; //域名
        this.search.product_name = "" ; //产品名
        this.search.product_id = "" ;  //产品id
        this.search.theme = "" ;       //主题
        this.search.erp_id = "" ;      //erp id
        this.search.user = "" ;        //建站用户
        this.search.youhuashi = "" ;   //建站优化师
        this.search.product_area = "" ;  //产品区域
        this.search.status = "" ;        //站点状态
    },
    //新版搜索导出域名
    handleExportListNew() {
      let url = "/product_ext.php?act=exportdomain";
      url = url + "&" + `domain=${this.search.domainname}&title=${this.search.product_name}&product_id=${this.search.product_id}&theme=${this.search.theme}&erp_id=${this.search.erp_id}&aid=${this.search.user}&ad_member_id=${this.search.youhuashi}&id_zone=${this.search.product_area}&is_del=${this.search.status}&json=1&p=1`;;
      window.open(url);
    },

  },
  filters: {
    filterTime: function(val) {
      return Vue.service("|>time", val);
    },
    filterDate: function(val) {
      return Vue.service("|>date", val);
    }
  }
};
</script>
