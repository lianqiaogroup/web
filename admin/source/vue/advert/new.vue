<template>
    <div id="page-comment-new1">
        <div class="header-panel">
            <div class="control">
                <div class="operations">
                    <el-button icon="plus" @click="save()" type="primary">保存</el-button>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="conten" v-loading.body="loading" element-loading-text="数据加载中">
                <el-tabs v-model="activeName" >
                    <el-tab-pane label="基本信息" name="first">
                       
                            <el-form label-position="top" label-width="80px">
                                <div class="flexbox half" v-show="id != ''">
                                    <el-form-item >
                                        <label>站点ID：</label><span>B{{id}}</span>
                                    </el-form-item>
                                </div>
                                <div class="flexbox half">
                                    <el-form-item >
                                        <label>站点名称：</label><el-input v-model="site_name"></el-input>
                                    </el-form-item>
                                </div>
                                <div class="flexbox half">    
                                    <el-form-item>
                                        <label>一级域名：</label><el-select v-model="domain" >
                                            <el-option
                                            v-for="item in firstList"
                                            :key="'www.'+item.domain"
                                            :label="'www.'+item.domain"
                                            :value="'www.'+item.domain">
                                            </el-option>
                                        </el-select>
                                    </el-form-item>
                                </div>
                                <div class="flexbox half">
                                    <el-form-item >
                                        <label>二级目录：blog/</label><el-input v-model="identify_tag"></el-input>
                                    </el-form-item>
                                </div>
                                <div class="flexbox half">    
                                    <el-form-item>
                                        <label>语言：</label><el-select v-model="language" >
                                            <el-option
                                            v-for="item in languages"
                                            :key="item.value"
                                            :label="item.label"
                                            :value="item.value">
                                            </el-option>
                                        </el-select>
                                    </el-form-item>
                                </div>
                                <div class="flexbox half">
                                    <el-form-item >
                                        <label>标题：</label><el-input v-model="site_title"></el-input>
                                    </el-form-item>
                                </div>
                                <div class="flexbox half">
                                    <el-form-item >
                                        <label>副标题：</label><el-input v-model="site_subtitle"></el-input>
                                    </el-form-item>
                                </div>
                                <div class="flexbox half">
                                    <el-form-item>
                                        <label >作者名：</label>
                                        <el-input v-model="site_author" ></el-input>
                                    </el-form-item>
                                </div>
                                <div class="flexbox">
                                    <el-form-item >
                                        <label>作者头像：</label>
                                        <el-upload
                                            name="upfile"
                                            class="avatar-uploader"
                                            action="qiniu_um.php?type=st_site"
                                            :show-file-list="false"
                                            :on-success="handleAvatarSuccess"
                                            :before-upload="beforeAvatarUpload">
                                            <img v-if="author_thumb" :src="author_thumb" class="avatar">
                                            <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                                        </el-upload>
                                    </el-form-item>    
                                </div>
                                <div class="flexbox half">
                                    <el-form-item>
                                        <label>购买按钮名称：</label><el-input v-model="product_name"></el-input>
                                    </el-form-item>
                                </div>
                                <div class="flexbox">
                                    <el-form-item >
                                        <label>文章主图：</label>
                                        <el-upload
                                            name="upfile"
                                            class="avatar-uploader"
                                            action="qiniu_um.php?type=st_site"
                                            :show-file-list="false"
                                            :on-success="handleAvatarSuccess1"
                                            :before-upload="beforeAvatarUpload">
                                            <img v-if="site_master_graph" :src="site_master_graph" class="avatar">
                                            <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                                        </el-upload>
                                    </el-form-item>    
                                </div>
                                <div class="flexbox">
                                    <el-form-item >
                                        <label>产品图片：</label>
                                        <el-upload
                                            name="upfile"
                                            class="avatar-uploader"
                                            action="qiniu_um.php?type=st_site"
                                            :show-file-list="false"
                                            :on-success="handleAvatarSuccess2"
                                            :before-upload="beforeAvatarUpload">
                                            <img v-if="product_img" :src="product_img" class="avatar">
                                            <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                                        </el-upload>
                                    </el-form-item>    
                                </div>
                                <div class="flexbox">
                                    <el-form-item >
                                        <label>网站底图：</label>
                                        <el-upload
                                            name="upfile"
                                            class="avatar-uploader"
                                            action="qiniu_um.php?type=st_site"
                                            :show-file-list="false"
                                            :on-success="handleAvatarSuccess3"
                                            :before-upload="beforeAvatarUpload">
                                            <img v-if="site_bottom_img" :src="site_bottom_img" class="avatar">
                                            <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                                        </el-upload>
                                    </el-form-item>    
                                </div>
                                <div class="flexbox half">
                                    <el-form-item>
                                        <label>底部文案：</label><el-input v-model="site_bottom_txt"></el-input>
                                    </el-form-item>
                                </div>
                                <div class="flexbox half">
                                    <el-form-item >
                                        <label style="">是否开启FB分享：</label><el-checkbox style="width:20px;" v-model="fb"></el-checkbox>
                                    </el-form-item>
                                </div>
                            </el-form>
                        
                    </el-tab-pane>
                    <el-tab-pane label="文章内容" name="second">
                        <div class='flexbox'>
                            <el-form ref="form" >
                                <el-form-item >
                                <textarea id="editor" type="textarea">

                                </textarea>
                                </el-form-item>
                            
                            </el-form>
                        </div>
                    </el-tab-pane>
                    <el-tab-pane label="模板设置" name="third">
                        <!-- <div class="conten" v-loading.body="loading" element-loading-text="数据加载中"> -->
                            <el-form label-position="top" label-width="80px">
                                <div class="flexbox half">    
                                    <el-form-item>
                                        <label>选择模板：</label><el-select v-model="theme" style="width:200px;">
                                            <el-option
                                            v-for="item in themes"
                                            :key="item.theme"
                                            :label="item.title"
                                            :value="item.theme">
                                            </el-option>
                                        </el-select>
                                    </el-form-item>
                                </div>
                                <div class="flexbox half">
                                    <el-form-item >
                                        <label style="">显示“购买按钮”：</label><el-checkbox style="width:20px;" v-model="showBuy"></el-checkbox>
                                    </el-form-item>
                                </div>
                            </el-form>
                        <!-- </div> -->
                    </el-tab-pane>
                    <el-tab-pane label="评论" name="fourth">
                        <el-form label-position="top" label-width="80px">
                                <div class="flexbox half">
                                    <el-form-item>
                                        <label>评论数：</label> <el-input-number v-model="comment_count"  :min="1" :max="20000000000" label="描述文字"></el-input-number>
                                    </el-form-item>
                                </div>
                                <div class="flexbox half">
                                    <el-form-item >
                                        <label>超链接关键词：</label><el-input v-model="comment_key_word"></el-input>
                                    </el-form-item>
                                </div>
                                <div class="flexbox half">
                                    <el-form-item >
                                        <label>超链接：</label><el-input v-model="comment_link"></el-input>
                                    </el-form-item>
                                </div>
                                <div class="flexbox half" style="padding-left:150px;">
                                    <el-form-item >
                                        <a href="resource/st_site_template.xlsx" target="_BLANK">
                                            <el-button  type="primary">下载模板</el-button>
                                        <!-- <el-button  type="primary">
                                            导入模板 
                                            <input  @change="tirggerFile($event)" id="orderFile" type="file"  name="file" />

                                        </el-button> -->
                                        </a>
                                        <el-button   type="primary" class="forUploader">
                                            导入评论
                                            <input type="file" id="uploader" ref="uploader" @change="handleUpload">
                                        </el-button>
                                        <span>{{ps}}</span>
                                    </el-form-item>
                                </div>
                            </el-form>
                    </el-tab-pane>
                </el-tabs>
            </div>    
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
            activeName: "first",
            site_name: "",
            domain: "",
            identify_tag: "",
            language: "",
            site_title: "",
            site_subtitle: "",
            site_author: "",
            author_thumb: "",
            product_name: "",
            site_master_graph: "",
            product_img: "",
            site_bottom_img: "",
            site_bottom_txt: "",
            site_content: "",
            site_template: "",
            add_time: "",
            articlePic: "",
            productPic: "",
            fb_status: "",
            theme: "style-AD-01",
            themes: [{ theme: "style-AD-01", title: "style-AD-01" }],
            comment_count: "",
            comment_key_word: "",
            comment_link: "",
            fb: false,
            showBuy:true,
            is_buy_show:1,
            loading: false,
            firstList: [],
            languages: [
                { label: "繁体中文", value: "tw" },
                { label: "简体中文", value: "cn" },
                { label: "英文", value: "en" },
                { label: "泰文", value: "tha" }
            ],
            imageUrl: "",
            files: "",
            ps: "未导入",
            id: ""
        };
    },
    mounted() {
        //初始化获取一级域名
        this.loading = true;
        if (this.$store.state.loginid) {
            this.site();
        } else {
            setTimeout(() => {
                this.site();
            }, 500);
        };

        if (!UE) {
            return false;
        }
        if (UE.getEditor) {
            this.editor = UE.getEditor("editor");
            this.init();
        } else {
            setTimeout(() => {
                this.editor = UE.getEditor("editor");
                this.init();
            }, 500);
        }
    },
    destroyed() {
        //取消
        this.editor.destroy();
    },
    methods: {
        init() {
            //初始化编辑页
            if (this.$route.params.id) {
                this.$http
                    .get(`/stsite.php?act=getEditStsite&stsite_id=${this.$route.params.id}`)
                    .then(function(result) {
                        this.content = result.body.content;
                        var list = result.body;
                        this.site_name = list.site_name;
                        this.identify_tag = list.identify_tag;
                        this.language = list.language;
                        this.site_title = list.site_title;
                        this.site_subtitle = list.site_subtitle;
                        this.site_author = list.site_author;
                        this.author_thumb = list.author_thumb;
                        this.product_name = list.product_name;
                        this.site_master_graph = list.site_master_graph;
                        this.product_img = list.product_img;
                        this.site_bottom_img = list.site_bottom_img;
                        this.site_bottom_txt = list.site_bottom_txt;
                        this.fb_status = list.fb_status;
                        this.site_content = list.site_content;
                        // this.theme = list.theme;
                        this.comment_count = list.comment_count;
                        this.comment_key_word = list.comment_key_word;
                        this.comment_link = list.comment_link;
                        this.id = list.id;
                        this.domain = list.domain;
                        this.editor.ready(
                            function() {
                                this.editor.setContent(this.site_content);
                            }.bind(this)
                        );
                        if (list.fb_status == 1) {
                            this.fb = true;
                        } else {
                            this.fb = false;
                        };
                        list.is_buy_show == 1 ? this.showBuy = true : this.showBuy = false;
                    });
            };
        },
        save() {
            var ue = UE.getEditor("editor");
            var formdata = new FormData();
            formdata.append("act", "save");
            var com = ue.getContent();
            if (com.length > 0) {
                var arr = com.split(" ");
                var reg = /^src.*source$/;
                for (var i = 0; i < arr.length; i++) {
                    if (reg.test(arr[i])) {
                        var src = arr[i].match(/src="(\S*)"/)[1];
                        var poster =
                            ' poster="' + src + '?vframe/png/offset/5" ';
                        arr[i] = poster + arr[i];
                    }
                }
                var str = arr.join(" ");
                str = str.replace(/auto/g, "none");
                this.site_content = str;
            }else{
                this.site_content = '';
            }
            var reg=/^([hH][tT]{2}[pP]:\/\/|[hH][tT]{2}[pP][sS]:\/\/)(([A-Za-z0-9-~]+)\.)+([A-Za-z0-9-~\/])+$/;
            if (this.site_name.trim() == "") {
                this.activeName = "first";
                this.$message.error("站点名称不能为空！");
                return false;
            } else if (this.domain == "") {
                this.activeName = "first";
                this.$message.error("一级域名不能为空！");
                return false;
            } else if (this.identify_tag.trim() == "") {
                this.activeName = "first";
                this.$message.error("二级目录不能为空！");
                return false;
            } else if (this.language == "") {
                this.activeName = "first";
                this.$message.error("请选择语言！");
                return false;
            } else if (this.site_title.trim() == "") {
                this.activeName = "first";
                this.$message.error("标题不能为空！");
                return false;
            } else if (this.site_subtitle.trim() == "") {
                this.activeName = "first";
                this.$message.error("副标题不能为空！");
                return false;
            } else if (this.site_author.trim() == "") {
                this.activeName = "first";
                this.$message.error("作者名不能为空！");
                return false;
            } else if (this.author_thumb == "") {
                this.activeName = "first";
                this.$message.error("作者头像不能为空！");
                return false;
            } else if (this.product_name.trim() == "") {
                this.activeName = "first";
                this.$message.error("作品名称不能为空！");
                return false;
            } else if (this.site_master_graph == "") {
                this.activeName = "first";
                this.$message.error("文章主图不能为空！");
                return false;
            } else if (this.product_img == "") {
                this.activeName = "first";
                this.$message.error("产品图片不能为空！");
                return false;
            } else if (this.site_bottom_img == "") {
                this.activeName = "first";
                this.$message.error("产品地图不能为空！");
                return false;
            } else if (this.site_bottom_txt.trim() == "") {
                this.activeName = "first";
                this.$message.error("底部文案不能为空！");
                return false;
            } else if (this.site_content.trim() == "") {
                this.activeName = "second";
                this.$message.error("文章内容不能为空！");
                return false;
            } else if (this.comment_link == "") {
                this.activeName = "fourth";
                this.$message.error("超链接不能为空！");
                return false;
            }else if(!reg.test(this.comment_link.trim())){
                this.activeName = "fourth";
                this.$message.error("超链接必须以http://或者https://开头的网址！");
                return false;
            } else if (this.files == "" && !this.$route.params.id) {
                this.activeName = "fourth";
                this.$message.error("未导入模板！");
                return false;
            }
            if (this.$route.params.id) {
                var url = "/stsite.php?act=postEditStsite";
                var msg = "已成功编辑站点"
                formdata.append("id", this.id);
            } else {
                var url = "/stsite.php?act=postAddStsite";
                var msg = "已成功新增站点"
            };

            this.fb == true ? (this.fb_status = 1) : (this.fb_status = 0);
            this.showBuy == true ? (this.is_buy_show = 1) : (this.is_buy_show = 0);
            formdata.append("site_name", this.site_name);
            formdata.append("domain", this.domain);
            formdata.append("identify_tag",this.identify_tag);
            formdata.append("language", this.language);
            formdata.append("site_title", this.site_title);
            formdata.append("site_subtitle", this.site_subtitle);
            formdata.append("site_author", this.site_author);
            formdata.append("author_thumb", this.author_thumb);
            formdata.append("product_name", this.product_name);
            formdata.append("site_master_graph", this.site_master_graph);
            formdata.append("product_img", this.product_img);
            formdata.append("site_bottom_img", this.site_bottom_img);
            formdata.append("site_bottom_txt", this.site_bottom_txt);
            formdata.append("fb_status", this.fb_status);
            formdata.append("is_buy_show", this.is_buy_show);
            formdata.append("site_content", this.site_content);
            formdata.append("theme", this.theme);
            formdata.append("comment_count", this.comment_count);
            formdata.append("comment_key_word", this.comment_key_word);
            formdata.append("comment_link", this.comment_link);
            formdata.append("file", this.files);
            this.$http.post(url, formdata).then(function(result) {
                if (result.body.ret == 500) {
                    this.$message.error(result.body.info);
                    return false;
                } else if (result.body.ret == 200) {
                    this.$message({
                        message: msg,
                        type: "success"
                    });
                    this.$router.push({ path: "/advert" });
                }
            });
        },
        handleUpload() {
            var self = this;
            var $file = this.$refs.uploader;
            if ($file.value == "") {
                return false;
            }
            var name = $file.files[0].name;
            var fileName = name
                .substring(name.lastIndexOf(".") + 1)
                .toLowerCase();
            if (fileName != "xls" && fileName != "xlsx") {
                alert("请选择execl格式文件上传！");
                $file.value = "";
                return;
            }
            this.files = $file.files[0];
            this.ps = "已导入";
            this.$message({
                message: "已经成功导入文件！",
                type: "success"
            });
        },
        site() {
            this.$http
                .get(`product_new.php?act=getSeoDomain&id_department=${this.$store.state.id_department}&loginid=${this.$store.state.loginid}`)
                .then(function(res) {
                    if (res.body.ret.length > 0) {
                        this.loading = false;
                        this.firstList = res.body.ret;
                    }else{
                        this.loading = false;
                        this.$message.error("无法获取一级域名列表！");
                    }
                });
        },
        handleAvatarSuccess(res, file) {
            this.author_thumb = res.url;
        },
        handleAvatarSuccess1(res, file) {
            this.site_master_graph = res.url;
        },
        handleAvatarSuccess2(res, file) {
            this.product_img = res.url;
        },
        handleAvatarSuccess3(res, file) {
            this.site_bottom_img = res.url;
        },
        beforeAvatarUpload(file) {
            if(file.type === "image/jpeg" || file.type === "image/png"){
                var isJPG = true;
            }
            // const isJPG = file.type === "image/jpeg";
            const isLt2M = file.size / 1024 / 1024 < 2;

            if (!isJPG) {
                this.$message.error("上传图片只能是 jpg和png格式!");
                return false;
            }
            if (!isLt2M) {
                this.$message.error("上传图片大小不能超过 2MB!");
                return false;
            }
            return isJPG && isLt2M;
        }
    }
};
</script>

<style scoped>
.el-tabs__item {
    font-size: 26px;
}
label {
    display: inline-block;
    width: 120px;
    text-align: right;
}
.el-input {
    width: 265px !important;
}
#page-comment-new .el-tabs__item {
    font-size: 24px !important;
}
#app #mainlayer #page-comment-new div.el-select .el-input {
    width: 100%;
}
.avatar-uploader .el-upload {
    border: 1px dashed #d9d9d9;
    border-radius: 6px;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}
.avatar-uploader .el-upload:hover {
    border-color: #409eff;
}
.avatar-uploader-icon {
    font-size: 28px;
    color: #8c939d;
    width: 178px;
    height: 178px;
    line-height: 178px;
    text-align: center;
}
.avatar {
    width: 178px;
    height: 178px;
    display: block;
    border-radius: 10px;
}
.forUploader {
    position: relative;
    overflow: hidden;
}
#uploader {
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    right: 0;
    opacity: 0;
    cursor: pointer;
}
.avatar-uploader {
    display: inline-block;
}
.avatar-uploader-icon {
    background-color: #fbfdff;
    border: 1px dashed #c0ccda;
    border-radius: 6px;
    box-sizing: border-box;
    width: 148px;
    height: 148px;
    cursor: pointer;
    line-height: 146px;
    vertical-align: top;
}
.el-input-number {
    float: right;
}
</style>
