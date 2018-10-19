<template>
    <div id="page-comment-new">
        <div class="header-panel">
            <div class="control">
                <div class="operations">
                    <el-button @click="handleSave" icon="plus" type="primary">保存评论</el-button>
                </div>
            </div>
        </div>

        <div class="container">
            <el-form :label-position="labelPosition" label-width="80px" :model="formValue">
                <el-row :gutter="20">
                    <el-col :span="10">
                        <el-form-item label="选择产品">
                            <el-input v-model="formValue.title" @focus="showDialog"></el-input>
                            <input type="hidden" v-model="formValue.product_id">
                        </el-form-item>
                    </el-col>
                    <el-col :span="10">
                        <el-form-item label="用户名">
                            <el-input v-model="formValue.name" v-bind:disabled="formValue.is_anonymous==1"></el-input>
                        </el-form-item>
                    </el-col>
                    <el-col :span="4">
                        <el-form-item label="是否匿名">
                            <el-tooltip placement="top">
                                <el-switch v-model="formValue.is_anonymous" on-color="#13ce66" off-color="#ff4949" on-value="1" off-value="0">
                                </el-switch>
                            </el-tooltip>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-row>
                    <el-col :span="24">
                        <el-form-item label="星级">
                            <div class="star">
                                <span v-bind:class="{ selected: formValue.score >=1 }" @click="handleChangeScore(1)"><i class="el-icon-star-on"></i></span>
                                <span v-bind:class="{ selected: formValue.score >=2 }" @click="handleChangeScore(2)"><i class="el-icon-star-on"></i></span>
                                <span v-bind:class="{ selected: formValue.score >=3 }" @click="handleChangeScore(3)"><i class="el-icon-star-on"></i></span>
                                <span v-bind:class="{ selected: formValue.score >=4 }" @click="handleChangeScore(4)"><i class="el-icon-star-on"></i></span>
                                <span v-bind:class="{ selected: formValue.score >=5 }" @click="handleChangeScore(5)"><i class="el-icon-star-on"></i></span>
                            </div>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-row>
                    <el-col :span="24">
                        <el-form-item label="评价内容">
                            <el-input type="textarea" :rows="4" v-model="formValue.content"></el-input>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-row>
                    <el-col :span="24">
                        <el-upload action="qiniu_um.php?type=comment" name="upfile" list-type="picture-card" :multiple="true" :file-list="fileList"
                            accept="image/*" :on-success="handleSuccess" :on-remove="handleRemove">
                            <i class="el-icon-plus"></i>
                        </el-upload>
                        <el-dialog v-model="dialogVisible" size="tiny">
                            <img width="100%" :src="dialogImageUrl" alt="">
                        </el-dialog>
                    </el-col>
                </el-row>
            </el-form>


            <el-dialog title="选择产品" class="product_select" :visible.sync="dialogTableVisible">
                <el-form>
                    <el-form-item label=''>
                        <el-popover ref='popmenu' placement="bottom-start" trigger="click">
                            <div class='item' @click='cmdtext="域名"; dismiss();'>
                                <el-radio class="radio" v-model="cmd" label="domain">域名</el-radio>
                            </div>
                            <div class='item' @click='cmdtext="产品名"; dismiss();'>
                                <el-radio class="radio" v-model="cmd" label="title">产品名</el-radio>
                            </div>
                            <div class='item' @click='cmdtext="产品id"; dismiss();'>
                                <el-radio class="radio" v-model="cmd" label="product_id">产品id</el-radio>
                            </div>
                            <div class='item' @click='cmdtext="ERPid";  dismiss();'>
                                <el-radio class="radio" v-model="cmd" label="erp_id">ERPid</el-radio>
                            </div>
                        </el-popover>
                        <div class="header-panel" v-on:keyup.enter="handleSearch">
                            <div class='control' style='background: white; padding-left: 0px;'>
                                <div class='select-category'>
                                    <span class="el-dropdown-link" v-popover:popmenu>
                                        <span ref='category' v-text='cmdtext'></span>
                                        <i class="el-icon-caret-bottom el-icon--right"></i>
                                    </span>
                                </div>
                                <el-input
                                    placeholder="输入关键词"
                                    :icon="keyword ? 'close' : 'search'"
                                    v-model="keyword"
                                    v-on:keyup.enter="handleSearch"
                                    :on-icon-click="handleSearchOrClose">
                                </el-input>
                            </div>
                        </div>
                    </el-form-item>
                </el-form>
                <el-table :data="goodsList">
                    <el-table-column property="product_id" label="产品ID" min-width="80"></el-table-column>
                    <el-table-column property="thumb" label="缩略图" width="100">
                        <template scope="scope">
                            <img :src='scope.row.thumb' alt="" style='width: 64px; height:64px;'>
                        </template>
                    </el-table-column>
                    <el-table-column property="title" label="名称" min-width="200"></el-table-column>
                    <el-table-column property="erp_number" label="erp_id" min-width="60"></el-table-column>
                    <el-table-column property="product_id" label="操作" min-width="60">
                        <template scope="scope">
                            <el-button @click="handleProductSelected(scope.row)">选择</el-button>
                        </template>
                    </el-table-column>
                </el-table>
                <el-pagination small layout="prev, pager, next" :page-size="1" @current-change="handleCurrentChange" :total="page_count">
                </el-pagination>
            </el-dialog>
        </div>
    </div>
</template>

<script>
    Vue.http.options.headers = {
        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
    };
    export default {
        data() {
            return {
                labelPosition: 'top',
                formValue: {
                    comment_id: ''
                    , title: '请选择'
                    , product_id: ''
                    , name: ''
                    , score: 5
                    , is_anonymous: "0"
                    , content: ''
                }
                , dialogImageUrl: '' // 本地图
                , dialogVisible: false
                , dialogTableVisible: false
                , goodsList: []
                , uploaded: [] // cdn图
                , page_count: 1
                , page: 1
                , fileList: []
                , keyword: ""
                , table_loading: false
                , product_data: {},
                page: 1,
                cmd: "domain",
                radio: "",
                cmdtext: '域名'
            }
        }
        , mounted() {
            var self = this;
            if (this.$route.params.id) {
                this.$http.post('comment.php?act=edit&comment_id=' + this.$route.params.id).then(res => {
                    // console.log(res.body)
                    this.formValue = {
                        comment_id: this.$route.params.id
                        , title: res.body.title || ""
                        , product_id: res.body.product_id
                        , name: res.body.name
                        , score: res.body.star
                        , is_anonymous: res.body.is_anonymous
                        , content: res.body.content
                    }
                    self.uploaded = [];
                    res.body.thumbs.map(function (item, index) {
                        self.fileList.push({ id: item.commont_thumb_id, url: item.thumb });
                    });
                });
            }
        }
        , methods: {
            showDialog() {
                this.dialogTableVisible = true;
                this.getProduct();
            }
            , handleSave() {
                if (this.formValue.title == "" || this.formValue.product_id == '') {
                    this.$message.error('请选择产品');
                    return false;
                }
                if (this.formValue.is_anonymous == "0" && this.formValue.name == "") {
                    this.$message.error('请填写姓名');
                    return false;
                }
                if (this.formValue.content == "") {
                    this.$message.error('请填写内容');
                    return false;
                }
                var formdata = new FormData();
                formdata.append('act', 'save');
                formdata.append('comment_id', this.formValue.comment_id);
                formdata.append('title', this.formValue.title);
                formdata.append('product_id', this.formValue.product_id);
                formdata.append('name', this.formValue.name);
                formdata.append('star', this.formValue.score);
                formdata.append('product_id', this.formValue.product_id);
                formdata.append('is_anonymous', this.formValue.is_anonymous);
                formdata.append('content', this.formValue.content);
                // this.uploaded = [];
                this.uploaded.map(function (item, index) {
                    formdata.append('photos[]', item.cdn);
                });
                // formdata.append('photos[]', "http://dasdasdasdads.jpg");
                this.$http.post('comment.php', formdata).then(res => {
                    if (res.body.code == '1') {
                        this.$message({ message: '添加成功', type: 'success' });
                        if (localStorage) {
                            localStorage.comments = "";
                        }
                        this.$router.push({ path: '/comments' });
                    } else {
                        this.$message.error('未知错误');
                    }
                });
            }
            // 改分
            , handleChangeScore(val) {
                this.formValue.score = val;
            }
            // 删除图片
            , handleRemove(file, fileList) {
                var self = this;
                console.log(file)
                var newarr = [];
                this.uploaded.map(function (item, index) {
                    if (item.uid != file.uid && item.cdn != file.url) {
                        newarr.push(item);
                    }
                });
                this.uploaded = newarr;
                this.$http.get('comment.php?act=delThumb&id=' + file.id).then(res => {
                    console.log(res.body)
                });
            }
            , handlePictureCardPreview(file) {
                this.dialogImageUrl = file.url;
                this.dialogVisible = true;
            }
            , handleSuccess(response, file, fileList) {
                this.uploaded.push({ 'uid': file.uid, 'cdn': response.url });
            }
            , getProduct() {
                this.$service('product#list', this.cmd, this.keyword, this.withDel, this.page)
                    .then(res => {
                        setTimeout(_ => this.table_loading = false, 0);  // vue async feature
                        this.goodsList = res.body.goodsList;
                        this.product_data = res.body;
                        this.page_count = res.body.pageCount;

                    });
            }, get() {
                this.$service('product#list', this.cmd, this.keyword, this.withDel, this.page)
                    .then(res => {
                        setTimeout(_ => this.table_loading = false, 0);  // vue async feature
                        this.goodsList = res.body.goodsList;
                        this.product_data = res.body;
                        this.page_count = res.body.pageCount;

                    });
            }
            , handleCurrentChange(val) {
                this.page = val;
                this.getProduct();
            }
            , handleProductSelected(param) {
                this.dialogTableVisible = false;
                this.formValue.product_id = param.product_id;
                this.formValue.title = param.title;
            }, handleSearch() {
                if (!this.table_loading) {
                    this.get();
                } else {
                    this.$message({ message: "正在拼命加载中，请稍候" })
                }
            }
            , handleClose() {
                this.keyword = '';
                this.handleSearch();
            }, handleSearchOrClose() {
                this.keyword ? this.handleClose() : this.handleSearch();
            }, dismiss() {
                setTimeout(_ => document.dispatchEvent(new MouseEvent("click", { clientX: 0, clientY: 0 })), 200);
            }
        }
    }

</script>