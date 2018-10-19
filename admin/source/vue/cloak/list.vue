<template>
    <div id="page-comment-list">
        <div class="header-panel">
            <div class="control control-new">
                <div class="operations" style="display:flex;"></div>
                <div class="search">
                    <div  v-on:keyup.enter="searchAll">
                    <div >
                        <el-form style="float:left;" class="demo-form-inline" :inline="true"> 
                            <el-form-item>
                                <el-input v-model="sensitive" placeholder="请输入敏感站点域名" ></el-input>
                            </el-form-item>
                        </el-form>
                        <el-select  filterable class='searchuser' v-model="is_close" slot="prepend" placeholder="全部关联" icon="search">
                            <el-option v-for="item in options" :label='item.label' :key='item.value' :value='item.value'></el-option>
                        </el-select>
                        <el-select  filterable class='searchuser' v-model="uid" slot="prepend" placeholder="建站优化师" icon="search">
                            <el-option v-for='item in ad_members' :label='item.name' :key='item.ad_member_id' :value='item.ad_member_id'></el-option>
                        </el-select>
                         <el-button type="primary" @click="searchAll()">搜索</el-button>
                         <el-button style="float:right;" type="primary"   @click="twoOpen(0)">关联绑定</el-button>
                    </div>
                            <el-dialog
                                :title="relate" 
                                :visible.sync="dialogVisible"
                                :close-on-click-modal="false"
                                width="40%">
                                <el-form :model="site">
                                    <el-form-item class="demo-form-inline">
                                        敏感站点：<el-input :disabled="disabled" v-model="site.sensitive" style="width:85%;border-radius:5px;"></el-input>
                                    </el-form-item>
                                    <el-form-item>
                                        安全站点：<el-input v-model="site.safety" style="width:85%;border-radius:5px;"></el-input>
                                    </el-form-item>
                                     <el-form-item>
                                        <div>说明：</div>
                                        <div>1、请输入一级域名相同的站点进行关联。</div>
                                        <div>2、一个敏感站点只能关联一次。</div>
                                    </el-form-item>
                                </el-form>
                                <span slot="footer" class="dialog-footer">
                                    <el-button @click="dialogVisible = false">取 消</el-button>
                                    <el-button type="primary" @click="relative">关 联</el-button>
                                </span>
                            </el-dialog>
                </div>
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
                <el-table-column prop="" label="" width="34">
                </el-table-column> 
			    <el-table-column prop="comment_id" label="序号"  width="100">
                    <template scope="scope">
                        {{scope.$index+1}}
                    </template>
                </el-table-column>
                <el-table-column prop="username" label="优化师"></el-table-column>
                <el-table-column prop="sensitive" label="敏感站点" width="280">
                    <template scope="scope">
                        <div>{{scope.row.sensitive}}
                            <a class="scope-op"  @click="handleEdit(scope.row.sensitive_id)"  href="javascript:void(0)">编辑</a>
                        </div>
                    </template>
                </el-table-column>
                 <el-table-column prop="safety" label="安全站点" width="280">
                    <template scope="scope">
                        <div>{{scope.row.safety}}
                            <a class="scope-op" @click="handleEdit(scope.row.safety_id)"  href="javascript:void(0)">编辑</a>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="create_at" label="添加时间" >
                    <template scope="scope">
                        <div>{{ scope.row.create_at }}</div>
                    </template>
                </el-table-column>
                 <el-table-column prop="update_at" label="更改时间" >
                    <template scope="scope">
                        <div>{{ scope.row.update_at }}</div>
                    </template>
                </el-table-column>
                 <el-table-column prop="region" label="地区" >
                    <template scope="scope">
                        <div>{{ scope.row.region }}</div>
                    </template>
                </el-table-column>
                <el-table-column prop="is_close" label="操作" width="130">
                    <template scope="scope">
                        <div v-if=" scope.row.is_close == 1 ">
                            <a class="scope-op" href="javascript:void(0)" @click='twoOpen(1,scope.row.sensitive,scope.row.safety,scope.row.id)'>重新关联</a>
                            <a class="scope-op1" href='javascript:void(0)' @click='close(scope.row.id)'>关闭</a>
                        </div>
                        <div v-if=" scope.row.is_close == 2 ">
                            <a class="scope-op2"  href='javascript:void(0)' @click='refresh(scope.row.id)'>恢复</a>
                        </div>
                    </template>
                </el-table-column>
			</el-table>
            <el-pagination
                small
                layout="prev, pager, next"
                :page-size="1"
                :current-page.sync="page"
                @current-change="handleCurrentChange"
                :total="page_count"
                >
            </el-pagination>
		</div>
	</div>
</template>
<script>
Vue.http.options.headers = {
    "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8"
};
export default {
    data() {
        return {
            multipleSelection: [],
            keyword: "",
            withDel: false,
            table_loading: false,
            list: [],
            page: 1,
            page_count: 1,
            searchCmd: "keyword",
            searchKey: "产品名",
            dialogVisible: false,
            options: [
                { value: "0", label: "全部关联" },
                { value: "1", label: "开启" },
                { value: "2", label: "关闭" }
            ],
            value: "",
            ad_members: [],
            ad_membersvalue: "",
            site: {
                safety: "",
                sensitive: ""
            },
            disabled: false,
            id: "",
            sensitive: "",
            uid: "",
            is_close: "",
            relate:"新增关联"
        };
    },
    created() {
        //优化师列表初始化
        this.$service("productL#getAdUser").then(
            res => (this.ad_members = res.body.ret)
        );
    },
    mounted() {
        //默认列表初始化
        this.get();
    },
    methods: {
        get() {
            // this.$http.get("/cloak.php?act=select&p="+this.page+"&sensitive="+this.sensitive+"&uid="+this.uid+"&is_close="+this.is_close).then(function(res) {
            //     this.table_loading = false;
            //     this.set(res.body);
            // });
            var obj = {
                sensitive: this.sensitive,
                uid: this.uid,
                is_close: this.is_close
            };
            this.$http
                .post("cloak.php?act=select&p=" + this.page, obj, {
                    emulateJSON: true
                })
                .then(res => {
                    if (res.body.goodsList.length > 0) {
                        this.table_loading = false;
                        this.set(res.body);
                    }
                });
        },
        set(response) {
            this.list = response.goodsList;
            this.page_count = response.pageCount;
            this.page = parseInt(response.page);
        },
        //分页点击事件
        handleCurrentChange(val) {
            this.page = val;
            this.get();
        },
        twoOpen(a, b, c, d) {
            this.dialogVisible = true;
            if (a === 0) {
                this.site.safety = "";
                this.site.sensitive = "";
                this.disabled = false;
            } else {
                this.site.safety = c;
                this.site.sensitive = b;
                this.disabled = true;
                this.id = d;
                this.relate = "重新关联"
            }
        },
        //关联绑定
        relative() {
            if (this.disabled == false) {
                var connection = "add"; //新增
                var obj = {
                    sensitive: this.site.sensitive.trim(),
                    safety: this.site.safety.trim()
                };
            } else {
                //重新关联
                var connection = "update"; //更新
                var obj = { safety: this.site.safety.trim(), id: this.id };
            };
            if(this.site.safety !='' && this.site.sensitive !='' && this.site.safety.trim() === this.site.sensitive.trim()){
                alert("敏感站点与安全站点重复，请修改后重新关联");
                return;
            };
            this.$http
                .post("cloak.php?act=" + connection, obj, { emulateJSON: true })
                .then(res => {
                    if (res.body.ret == 1) {
                        this.$message({
                            message: res.body.message,
                            type: "success",
                            duration: 3000
                        });
                        this.dialogVisible = false;
                        this.get();
                    } else {
                        this.$message({
                            message: res.body.message,
                            type: "warning",
                            duration: 3000
                        });
                    }
                });
        },
        searchAll() {
            //搜索
            this.$http
                .post(
                    "cloak.php?act=select",
                    {
                        sensitive: this.sensitive,
                        uid: this.uid,
                        is_close: this.is_close
                    },
                    { emulateJSON: true }
                )
                .then(res => {
                    if (res.body.ret == 0) {
                        this.list = [];
                    } else {
                        this.set(res.body);
                    }
                });
        },
        handleEdit(id) {
            //站点编辑跳转
            this.$service("product#editerp", id);
        },
        close(id) {
            //关闭
            var self = this;
            this.$http
                .post(
                    "cloak.php?act=update",
                    { is_close: 2, id: id },
                    { emulateJSON: true }
                )
                .then(res => {
                    this.$message({
                        message: res.body.message,
                        type: "success",
                        duration: 3000
                    });
                    if(this.list.length == 1 && this.page >1){
                        this.page=this.page - 1;
                        return;
                    }else if(this.list.length == 1 && this.page == 1){
                        this.list = [];
                        // return;
                    };
                     this.get();
                    
                });
        },
        refresh(id) {
            //更新
            var self = this;
            this.$http
                .post(
                    "cloak.php?act=update",
                    { is_close: 1, id: id },
                    { emulateJSON: true }
                )
                .then(res => {
                    this.$message({
                        message: res.body.message,
                        type: "success",
                        duration: 3000
                    });
                     if(this.list.length == 1 && this.page >1){
                        this.page=this.page - 1;
                        return;
                    }else if(this.list.length == 1 && this.page == 1){
                        this.list = [];
                        // return;
                    };
                     this.get();
                });
        }
    }
};
</script>

<style scope>
.el-dialog--small {
    width: 30%;
}
.el-dialog__body {
    padding-bottom: 0px;
}
#mainlayer #page-comment-list .container .scope-op {
    color: #20a0ff;
}
div.el-select-dropdown {
    margin: 0;
}
.el-select-dropdown__item.selected {
    color: #000;
    background-color: #fff;
}
.el-select-dropdown__item.selected.hover {
    background-color: #e4e8f1;
}
#mainlayer #page-comment-list .container .scope-op1 {
    color: red;
    text-decoration: none;
}
#mainlayer #page-comment-list .container .scope-op2 {
    color: green;
    text-decoration: none;
}
.el-form-item__content{
    line-height: normal;
}
.header-panel .control .search {
    background: #f6f6f6;
    padding:0;
}
</style>


