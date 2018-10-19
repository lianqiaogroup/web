<template>
    <section class="analytics-index">
        <div class="header-panel">
            <div class="control">
                <div class="search">
                    <el-form :inline="true" :model="filter" ref="filter" label-width="80px" class="demo-form-inline demo-ruleForm">
                        <el-form-item>
                            <el-input placeholder="请输入域名站点" v-model="filter.url"></el-input>
                        </el-form-item>
                        <el-form-item>
                            <el-button type="primary" @click="handleQuery">查询</el-button></el-form-item>
                        <el-form-item style="float:right;">
                            <router-link to="/advert/new">
                                <el-button type="primary">新增站点</el-button>
                            </router-link>
                            <el-button type="danger" @click="handleDel" disabled>删除站点</el-button>
                            <el-button type="normal" @click="filter.is_del=1" disabled>回收站</el-button>
                        </el-form-item>
                    </el-form>
                </div>
            </div>
            <div class="container">
                <el-table
                    ref="multipleTable"
                    :data="tableData"
                    border
                    tooltip-effect="dark"
                    v-loading='loading'
                    element-loading-text="拼命加载中">
                    <el-table-column width="50"></el-table-column>
                    <el-table-column
                        width="80"
                        type="selection"
                        class="product-selection"
                        align="center"
                        ref="sel">
                    </el-table-column>
                    <el-table-column prop="id" label="ID" width="80" align="center"></el-table-column>
                    <el-table-column prop="site_name" label="站点名称"></el-table-column>
                    <el-table-column prop="domain" label="域名">
                        <template scope="scope">
                            <a :href="`http://${ scope.row.domain}/blog/${ scope.row.identify_tag}`" target="_BLANK" style="color: #666;">{{ scope.row.domain}}/blog/{{ scope.row.identify_tag}}</a>
                        </template>
                    </el-table-column>
                    <el-table-column prop="uid_name_cn" label="优化师"></el-table-column>
                    <el-table-column prop="department" label="部门"></el-table-column>
                    <el-table-column prop="add_time" label="建立时间"></el-table-column>
                    <el-table-column prop="theme" label="模板"></el-table-column>
                    <el-table-column prop="language" label="语言"></el-table-column>
                    <el-table-column prop="act_desc" label="操作">
                        <template scope="scope">
                            <a class="scope-op" href='javascript:;' @click='handleEdit(scope.row.id)'>编辑</a>
                        </template>
                    </el-table-column>
                </el-table>
            </div>
            <div class="block">
                <el-pagination
                    v-if="page_count>0"
                    small
                    layout="prev, pager, next"
                    :page-size="1"
                    :current-page.sync="page"
                    :total="page_count"
                    @current-change="handleCurrentChange">
                </el-pagination>
                
            </div>
        </div>
    </section>
</template>
<script>
Vue.http.options.headers = {
    "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8"
};
export default {
    data() {
        return {
            // APi接口
            api: {
                list: 'stsite.php',
                del: '',
            },
            // 表格数据
            tableData: [
                {
                    'id': 0,
                    'title': '测试数据',
                    'url': 'http://www.baidu.com',
                    'user_name': 'Cullen',
                    'department': '测试部门',
                    'create_time': '建立时间',
                    'theme': '模版',
                    'lang': '语言',
                }
            ],
            // 分页
            page: 1,
            page_count: 1,
            // 过滤器
            filter: {
                // keyword
                url: '',
                // 删除状态
                is_del: 0,
            },
            loading: false,
        };
    },
    mounted() {
        this.getData();
    },
    methods: {
        getData() {
            // 禁止多次异步请求
            if (this.loading==true) return false;

            // 开启异步锁
            this.loading = true;

            this.$http.get(`${this.api.list}?p=${this.page}&domain=${this.filter.url}`).then(res => {
                // 关闭异步锁
                this.loading = false;
                // 结果处理
                if (res.body.goodsList) {
                    this.tableData = res.body.goodsList;
                    this.page_count = res.body.pageCount || 0;
                } else {
                    this.$message.error(res.body.message||'未知错误');
                    this.tableData = [];
                    this.page_count = 0;
                }
            });
        },
        handleCurrentChange(currentPage) {
            this.page = currentPage;
            // 检查搜索条件是否正确，不正确则清空内容
            if (this.checkUrl()==false) {
                this.filter.url = '';
            }
            // 查询数据
            this.getData();
        },
        // 编辑编辑
        handleEdit(param){
            this.$router.push({path:'/advert/new/'+param});
        },
        // 查询操作
        handleQuery() {
            // true=查询 or false=警告
            if (this.checkUrl()==true) {
                this.page = 1;
                this.getData();
            } else {
               this.$message.error('输入的站点格式有误，请重新输入');
            }
        },
        // 删除操作
        handleDel () {
        },
        // 检测域名是否符合规则
        checkUrl () {
            // 如果为空不做特殊处理，直接搜素
            if (this.filter.url==='') {
                this.getData();
                return true;
            }

            let _res = false;

            // replace http or https
            let _url = this.filter.url;
                _url = _url.replace('http://', '');
                _url = _url.replace('https://', '');

            // 去除最后1个字符为 "/"
            let _arr = _url.split('');
            if (_arr[_arr.length-1]==='/') {
                _arr.pop();
                _url = _arr.join('');
            }

            // 更新Vue值
            this.filter.url = _url;

            // Example: www.baidu.com/blog/aaa
            let reg1 = /^www\.[a-zA-Z0-9\-]*\.[a-zA-Z]{2,}\/blog\/[a-zA-Z0-9]+/;
            if (reg1.test(_url)) {
                _res = true;
            } else {
                // if have /blog/
                let reg2 = /^www\.[a-zA-Z0-9\-]*\.[a-zA-Z]{2,}\/blog\/{0,1}$/;
                if (reg2.test(_url)) {
                    _res = true;
                } else {
                    // check identiy_tag
                    let reg3 = /^www\.[a-zA-Z0-9\-]*\.[a-zA-Z]{2,}\/[a-zA-Z0-9]+/;
                    if (reg3.test(_url)) {
                        _res = false;
                    } else {
                        // check domain format
                        let reg4 = /^www\.[a-zA-Z0-9\-]*\.[a-zA-Z]{2,}/;
                        _res = reg4.test(_url);
                    }
                }
            }
            return _res;
        }
    }
};
</script>
