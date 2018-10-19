<template>
    <div id="page-comment-list">
        <div class="header-panel">
        	<div class="breadcrumb">
                <ul>
                    <li class="title">您的位置：</li>
                    <li class="item"><a href="/">首页</a></li>
                    <li class="item"><a href="javascript:;">广告</a></li>
                    <li class="item action"><a href="/#/fmp/list">广告素材</a></li>
                </ul>
            </div>
            <div class="contain" id="contain" v-loading.body="loading" element-loading-text="数据提交中">
                <div class="box">
                    <div>填写以下广告素材，提交后进行投放广告，带<span class="blueStar">*</span>号为必填项，不带非必填</div>
                        <div>
                        <!-- <h2>企业账号</h2>
                        <div class="group">
                            <label class="lab">企业账号<span class="blueStar">*</span>：</label>
                            <el-select class="productSelectTwo" v-model="accountNum" filterable  placeholder="请输入">
                                <el-option
                                v-for="item in accountArr"
                                :key="item.num"
                                :label="item.num"
                                :value="item.num">
                                </el-option>
                            </el-select>
                        </div> -->
                    </div>
                    <div>
                        <!-- <h2>广告系列</h2>
                        <div class="group">
                            <label class="lab">广告系列名称<span class="blueStar">*</span>：</label><el-input v-model="adSeriName" placeholder="填写规范：姓名-产品名称"></el-input>
                        </div> -->
                         <h2>产品信息</h2>
                        <div class="group">
                            <label class="lab">建站网址<span class="blueStar">*</span>：</label><el-input v-model="web_url" placeholder="请填写"></el-input>
                        </div>
                         <div class="group">
                            <label class="lab ">价格<span class="blueStar">*</span>：</label><el-input class="priceInput" v-model="adPrice" placeholder="请填写"></el-input> 元
                        </div>
                        
                    </div>
                    <div>
                        <h2>受众范围</h2>
                        <!-- <div class="group">
                            <label class="lab">广告组名称<span class="blueStar">*</span>：</label><el-input v-model="adGroupName" placeholder="填写规范：姓名-产品名称"></el-input>
                        </div> -->
                        <div class="group">
                            <label class="lab">年龄范围：</label> 
                            <el-select v-model="minAge"  placeholder="请选择">
                                <el-option
                                v-for="item in options"
                                :key="item.value"
                                :label="item.value"
                                :value="item.value">
                                </el-option>
                            </el-select> -
                            <el-select v-model="maxAge"  placeholder="请选择">
                                <el-option
                                v-for="item in options"
                                :key="item.value"
                                :label="item.value"
                                :value="item.value">
                                </el-option>
                            </el-select>
                            <label class="lab">性别：</label> 
                            <el-radio-group v-model="sex">
                                <el-radio-button label="不限"></el-radio-button>
                                <el-radio-button label="男性"></el-radio-button>
                                <el-radio-button label="女性"></el-radio-button>
                            </el-radio-group>
                        </div>
                         <div class="group">
                            <label class="lab">受众兴趣<span class="blueStar">*</span>：</label><el-input v-model="productNum" placeholder="使用右方的工具进行查找，填写时用逗号分离"></el-input>
                        </div>
                        <!-- <div class="group">
                            <label class="lab">产品品类<span class="blueStar">*</span>：</label>
                            <el-select class="productSelect" v-model="productNum" filterable  placeholder="请输入">
                                <el-option
                                v-for="item in arrProduct"
                                :key="item.num"
                                :label="item.num"
                                :value="item.num">
                                </el-option>
                            </el-select>
                            <label class="lab ml60">价格<span class="blueStar">*</span>：</label><el-input class="priceInput" v-model="adPrice" placeholder="请填写"></el-input> 元
                        </div> -->
                    </div>
                    <div>
                        <h2>其它信息</h2>
                        <!-- <div class="group">
                            <label class="lab">广告上传方式<span class="blueStar">*</span>：</label>
                            <el-radio-group  v-model="create_type">
                                <el-radio :label="1">复用帖子</el-radio> <el-tooltip popper-class="adv" class="item" effect="dark" content="举例：你先在FB上投放一双运动鞋的广告，然后在系统上想投放那双运动鞋的广告，然后直接复用之前的帖子，输入ID，提交后，系统推送进行投放" placement="top"><el-button class="circle" size="mini" type="primary">?</el-button></el-tooltip>
                                <el-radio class="ml100" :label="2">创建广告创意</el-radio>  <el-tooltip popper-class="adv" class="item" effect="dark" content="准备广告创意：图片或视频、标题、描述等内容，将这些内容填写，提交后，系统推送进行投放" placement="top"><el-button class="circle " size="mini" type="primary">?</el-button></el-tooltip>
                            </el-radio-group>
                        </div> -->
                        <div >
                            <div class="group">
                                <label class="lab">品类-主页-账号<span class="blueStar">*</span>：</label>
                                <el-select class="larWidth" v-model="accountNum" filterable  placeholder="请输入">
                                    <el-option
                                    v-for="item in accountArr"
                                    :key="item.num"
                                    :label="item.num"
                                    :value="item.value">
                                    </el-option>
                                </el-select>
                            </div>
                            <!-- <div class="group">
                                <label class="lab">粉丝页ID<span class="blueStar">*</span>：</label><el-input v-model="page_id" placeholder="请填写"></el-input>
                            </div> -->
                            <div class="group">
                                <label class="lab">帖子ID<span class="blueStar">*</span>：</label><el-input v-model="post_id" placeholder="请填写"></el-input>
                            </div>
                            <div class="group">
                                <label class="lab">请选择广告种类<span class="blueStar">*</span>：</label>
                                <el-radio-group v-model="radio2">
                                    <el-radio :label="1">单图</el-radio>
                                    <el-radio :label="2">多图</el-radio>
                                    <el-radio :label="3">幻灯片</el-radio>
                                    <el-radio :label="4">视频</el-radio>
                                </el-radio-group>
                            </div>
                            <div class="group">
                                <label class="lab">投放方式<span class="blueStar">*</span>：</label>
                                <el-radio-group v-model="radio3">
                                    <el-radio :label="1">上新</el-radio>
                                    <el-radio :label="2">优化</el-radio>
                                </el-radio-group>
                            </div>
                        </div>
                        <!-- <div v-else="create_type == 2">
                            <div class="group">
                                <label class="lab">广告名<span class="blueStar">*</span>：</label><el-input v-model="adName" placeholder="填写规范：姓名-产品名称"></el-input>
                            </div>
                            <div class="group">
                                <label class="lab">广告描述<span class="blueStar">*</span>：</label><el-input v-model="adDescrip" placeholder="请填写"></el-input>
                            </div>
                            <div class="group">
                                <label class="lab">广告标题<span class="blueStar">*</span>：</label><el-input v-model="adTitle" placeholder="请填写"></el-input>
                            </div>
                            <div class="group">
                                <label class="lab">广告副标题<span class="blueStar">*</span>：</label><el-input v-model="adSubTitle" placeholder="请填写"></el-input>
                            </div>
                            <div class="group">
                                <label class="lab">目标页网址<span class="blueStar">*</span>：</label><el-input v-model="adSite" placeholder="请填写"></el-input>
                            </div>
                            
                            <div class="group">
                                <label class="lab">视频或图片<span class="blueStar">*</span>：</label>
                                <el-button v-show="smallloading" class="forUploader" size="mini" type="primary">点击上传
                                    <input type="file" id="uploader" ref="uploader" @change="handleUpload">
                                </el-button>
                                <img v-show="port" src="/build/images/process.gif" alt="">
                                <span v-show="port">数据传输中...</span>
                                <span v-show="this.filePath">{{fileName}}</span>
                                <span @click="deleteImg" class="blue" v-show="delet">删除</span>
                                
                            </div>
                        </div> -->
                        
                        
                    </div>
                    <div class="sub">
                        <el-button @click="sub()"  type="primary">&nbsp;&nbsp;提&nbsp;&nbsp;交&nbsp;&nbsp;</el-button>
                    </div>
                </div>
                <div class="leftbox">
                    <div>查找受众兴趣</div>
                        <el-select
                            class="intertSelect"
                            v-model="value9"
                            filterable
                            remote
                            @change="selectChange"
                            reserve-keyword
                            placeholder=" 请输入品类，输入后选择"
                            :remote-method="remoteMethod"
                            >
                            <el-option
                            v-for="item in options4"
                            :key="item.id"
                            :label="item.label"
                            :value="item.value">
                            </el-option>
                        </el-select>
                    <el-table
                        :data="interestingData"
                         stripe
                        center
                        style="width: 400px">
                        <el-table-column
                        label="品类对应的受众兴趣 　点击可复制"
                        width="398px">
                        <template scope="scope">
                            <el-button class="btn" :data-clipboard-text="scope.row.tag" @click="copy" type="text">{{scope.row.tag}}</el-button>
                        </template>
                        </el-table-column>
                    </el-table>
                </div>
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
            radio2: 1,
            radio3: 1,
            adSeriName: "",
            minAge: 13,
            maxAge: 65,
            sex: "不限",
            adGroupName: "",
            adName: "",
            adDescrip: "",
            adTitle: "",
            adSubTitle: "",
            adSite: "",
            productNum: "",
            adPrice: "",
            adPic: "",
            input: "",
            loading: false,
            list: [],
            page: 1,
            page_count: 0,
            options: [],
            imageUrl: "",
            fileList: [],
            arrProduct: "",
            accountArr: [
                // { num: 1521744841235203 },
                // { num: 1891140967849956 },
                // { num: 349407535588856 },
                // { num: 658302644509277 },
                // { num: 1624101024332917 },
                // { num: 1624101030999583 },
                // { num: 1624101037666249 },
                // { num: 1763514533738273 },
                // { num: 227746614479671 },
                // { num: 2073471799587838 },
                // { num: 2105643122980492 }
            ],
            accountNum: "",
            smallloading: true,
            post_id: "",
            page_id: "",
            create_type: 1,
            domain: "",
            uploadApi: "",
            deleteApi: "",
            port: false,
            isVideo: "",
            fileName: "",
            filePath: "",
            delet: false,
            value8: "",
            interestingData: [],
            web_url: "",
            options4: [], //下拉列表
            value9: [], //选中的值
            list: [],
            loading: false,
            states: []
        };
    },
    created() {
        for (var i = 0; i < 53; i++) {
            this.options.push({ value: 13 + i });
        };
    },
    mounted() {
        this.getUrl();
        this.getJson();
        
    },
    methods: {
        selectChange(val) {
             console.log(this.domain);
            if (val != "") {
                this.$http.post(
                        this.domain + "/api/advertising/interest",
                        { name: val ,type:2},
                        { emulateJSON: true }
                    ).then(function(res){
                        console.log(res)
                       if(res.body.code == 200){
                           
                           this.interestingData = res.body.data;
                           console.log(this.interestingData)
                       }
                    },function(err){
                        this.loading = false;
                    })
            }
        },
        remoteMethod(query) {
           
            if (query !== "") {
                this.loading = true;
                this.$http
                    .post(
                        this.domain + "/api/advertising/interest",
                        { name: query ,type:1},
                        { emulateJSON: true }
                    )
                    .then(function(res) {
                        // console.log(res)
                        if(res.body.code == 200){
                            this.list = res.body.data.map(item => {
                                return { value: item.cat_name, label: item.cat_name,id:item.cat_id };
                            });
                            // console.log(this.list)
                            setTimeout(() => {
                            this.loading = false;
                            this.options4 = this.list.filter(item => {
                                return (
                                    item.label
                                        .toLowerCase()
                                        .indexOf(query.toLowerCase()) > -1
                                );
                            });
                            console.log(this.options4)
                        }, 200);
                        }
                        
                    },function(err){
                        // alert("传输错误");
                        this.loading = false;
                    });
            } else {
                this.options4 = [];
            }
        },
        copy() {
            var clipboard = new Clipboard(".btn");
            clipboard.on("success", e => {
                this.$message({
                    showClose: true,
                    message: "复制成功",
                    type: "success"
                });
                // 释放内存
                clipboard.destroy();
            });
            clipboard.on("error", e => {
                // 不支持复制
                console.log("该浏览器不支持自动复制");
                // 释放内存
                clipboard.destroy();
            });
        },
        getUrl() {
            this.$http
                .get("/FacebookMarketing.php?act=getFmpApi")
                .then(function(res) {
                    this.domain = res.body.domain;
                    this.uploadApi = res.body.uploadImage;
                    this.deleteApi = res.body.deleteImage;
                    this.$http.post(this.domain+'/api/advertising/account',{},{'emulateJSON': true}).then(function(res){
                        if(res.body.code == 200){
                            this.accountArr = res.body.data.map(item => {
                                // return { num:'品类-账号'+ item.cat_name+','+item.page_id+','+item.account_id+','+item.pixel_id };
                                return { 
                                    num:`${item.cat_name}-账号${item.account_id}-主页${item.page_id}-像素${item.pixel_id}`,
                                    value:item.cat_name+','+item.page_id+','+item.account_id+','+item.pixel_id 
                                    };
                            });
                        }
                    })
                });
        },
        getJson() {
            this.$http
                .get(
                    "http://shopadmin.stosz.com/build/plugins/fmp/cate-pop.json"
                )
                .then(function(res) {
                    let keys = Object.keys(res.body);
                    let arr = [];
                    for (var i = 0; i < keys.length; i++) {
                        arr.push({ num: keys[i] });
                    }
                    this.arrProduct = arr;
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
            if (fileName != "jpg" && fileName != "png" && fileName != "mp4") {
                alert("只能上传jpg/png/mp4格式的文件！");
                $file.value = "";
                return;
            }
            this.adPic = $file.files[0];
            var formdata = new FormData();
            formdata.append("file", this.adPic);

            this.port = true;
            this.smallloading = false;
            document.getElementById("uploader").value = "";
            this.$http.post(this.uploadApi, formdata).then(
                function(res) {
                    // this.$http.post("https://pokerface02.stosz.com/index.php/api/advertising/upload",formdata).then(function(res) {
                    if (res.body.code == 200) {
                        this.isVideo = res.body.data.isVideo;
                        this.fileName = res.body.data.clientFilename;
                        this.filePath = res.body.data.path;
                        this.delet = true;
                    } else {
                        alert("上传失败！");
                        return false;
                    }
                    this.port = false;
                },
                function(error) {
                    this.port = false;
                    this.smallloading = true;
                    return false;
                    alert("上传失败！");
                }
            );
        },
        deleteImg() {
            this.port = true;
            this.smallloading = false;
            this.delet = false;
            console.log(this.deleteApi);
            //  this.$http.post("https://pokerface02.stosz.com/index.php/api/advertising/deupload",{file:this.filePath},{'emulateJSON': true}).then(function(res) {
            // this.$http.post(this.domain+"/api/advertising/deupload",{file:this.filePath},{'emulateJSON': true}).then(function(res){
            this.$http
                .post(
                    this.deleteApi,
                    { file: this.filePath },
                    { emulateJSON: true }
                )
                .then(
                    function(res) {
                        this.port = false;
                        this.smallloading = true;
                        if (res.body.code == 200) {
                            this.filePath = "";
                            this.smallloading = true;
                            this.fileName = "";
                        } else {
                            this.port = false;
                            this.delet = true;
                            this.smallloading = false;
                            alert(res.body.msg);
                        }
                    },
                    function(error) {
                        this.port = false;
                        this.delet = true;
                        alert("删除失败！");
                    }
                );
        },
        sub() {
            var sexNum = "";
            if (this.sex == "不限") {
                sexNum = 0;
            } else if (this.sex == "男性") {
                sexNum = 1;
            } else if (this.sex == "女性") {
                sexNum = 2;
            }

            if (this.web_url == "") {
                this.$message.error("建站网址不能为空！");
                return false;
            } else if (this.adPrice.trim() == "") {
                this.$message.error("价格不能为空！");
                return false;
            } else if (this.productNum.trim() == "") {
                this.$message.error("受众兴趣不能为空！");
                return false;
            } else if (this.accountNum == "") {
                this.$message.error("请选择企业账号！");
                return false;
            } else if (this.post_id.trim() == "") {
                this.$message.error("帖子ID不能为空！");
                return false;
            }
            if (this.maxAge < this.minAge) {
                this.$message.error(
                    "最大的年龄不能小于最小的年龄，请重新选择！"
                );
                return false;
            }
            var reg = /^\d+(\.\d+)?$/;
            if(!reg.test(this.adPrice)){
                 this.$message.error("价格只能输入数字！");
                 return false;
            }

            var formdata = new FormData();
            formdata.append("account_id", this.accountNum);
            formdata.append("targeting[age_min]", this.minAge);
            formdata.append("targeting[age_max]", this.maxAge);
            formdata.append("targeting[genders]", sexNum);
            formdata.append("targeting[interests]", this.productNum);
            formdata.append("post_id", this.post_id);
            formdata.append("web_url", this.web_url);
            formdata.append("customer_price", this.adPrice);
            formdata.append("ad[type]", this.radio2);
            formdata.append("ad[mode]", this.radio3);

            // if (this.accountNum == "") {
            //     this.$message.error("请选择企业账号！");
            //     return false;
            // } else if (this.adSeriName.trim() == "") {
            //     this.$message.error("广告系列名称不能为空！");
            //     return false;
            // } else if (this.adGroupName.trim() == "") {
            //     this.$message.error("广告组名称不能为空！");
            //     return false;
            // } else if (this.productNum == "") {
            //     this.$message.error("请选择产品品类！");
            //     return false;
            // } else if (this.adPrice == "") {
            //     this.$message.error("价格不能为空！");
            //     return false;
            // }
            // if (this.maxAge < this.minAge) {
            //     this.$message.error(
            //         "最大的年龄不能小于最小的年龄，请重新选择！"
            //     );
            //     return false;
            // }

            // var formdata = new FormData();
            // if (this.create_type == 1) {
            //     if (this.post_id.trim() == "") {
            //         this.$message.error("帖子ID不能为空！");
            //         return false;
            //     } else if (this.page_id == "") {
            //         this.$message.error("粉丝页ID不能为空！");
            //         return false;
            //     }
            //     formdata.append("post_id", this.post_id); //帖子ID
            //     formdata.append("page_id", this.page_id); //主页ID
            // } else {
            //     if (this.adName.trim() == "") {
            //         this.$message.error("广告名不能为空！");
            //         return false;
            //     } else if (this.adDescrip.trim() == "") {
            //         this.$message.error("广告描述不能为空！");
            //         return false;
            //     } else if (this.adTitle.trim() == "") {
            //         this.$message.error("广告标题不能为空！");
            //         return false;
            //     } else if (this.adSubTitle.trim() == "") {
            //         this.$message.error("广告副标题不能为空！");
            //         return false;
            //     } else if (this.adSite.trim() == "") {
            //         this.$message.error("目标页网址不能为空！");
            //         return false;
            //     } else if (this.filePath == "") {
            //         this.$message.error("请上传一个视频或者图片！");
            //         return false;
            //     }
            //     var reg = /(((^https?:(?:\/\/)?)(?:[-;:&=\+\$,\w]+@)?[A-Za-z0-9.-]+|(?:www.|[-;:&=\+\$,\w]+@)[A-Za-z0-9.-]+)((?:\/[\+~%\/.\w-_]*)?\??(?:[-\+=&;%@.\w_]*)#?(?:[\w]*))?)$/g;
            //     if (!reg.test(this.adSite)) {
            //         this.$message.error("请输入正确的目标页网址！");
            //         return false;
            //     }
            //     formdata.append("ad[name]", this.adName);
            //     formdata.append("ad[describe]", this.adDescrip);
            //     formdata.append("creative[name]", this.adTitle);
            //     formdata.append("creative[subtitle]", this.adSubTitle);
            //     formdata.append("creative[link_url]", this.adSite);
            // }

            // formdata.append("account_id", this.accountNum);
            // formdata.append("campaign_name", this.adSeriName);
            // formdata.append("targeting[age_min]", this.minAge);
            // formdata.append("targeting[age_max]", this.maxAge);
            // formdata.append("targeting[genders]", sexNum);
            // formdata.append("adset_name", this.adGroupName);
            // formdata.append("targeting[interests]", this.productNum);
            // formdata.append("customer_price", this.adPrice);
            // formdata.append("file_path", this.filePath);
            // formdata.append("file_type", this.isVideo);
            this.loading = true;
            this.$http
                .post(this.domain+"/api/advertising/create", formdata)
                .then(function(res) {
                    if (res.body.code == 200) {
                        this.$message({
                            message: "提交成功",
                            type: "success"
                        });
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        this.$message.error(res.body.msg);
                        this.loading = false;
                    }
                });
        }
    }
};
</script>


<style scoped>
.contain {
    padding: 20px;
    padding-left: 120px;
}
.blueStar {
    color: #60b9e7;
    font-size: 26px;
    vertical-align: middle;
}
.blue {
    color: #60b9e7;
}
.group {
    margin-bottom: 20px;
}
.group .lab {
    display: inline-block;
    width: 150px;
    text-align: right;
}
.group .el-select {
    width: 70px;
}
.group .productSelect {
    width: 100px;
}
.group .productSelectTwo {
    width: 250px;
}
.group .el-input {
    width: 500px;
}
.group .priceInput {
    width: 170px;
}
.sub {
    width: 650px;
    text-align: right;
}
.upload-demo {
    display: inline-block;
}
.sub {
    margin-top: 50px;
    text-align: center;
}
.ml60 {
    margin-left: 60px;
}
.circle {
    padding: 0;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border-color: #ccc;
    background: #ccc;
    margin-left: 5px;
}
.ml100 {
    margin-left: 100px;
}
.el-tooltip__popper {
    width: 200px !important;
}
.box {
    width: 700px;
    float: left;
}
.leftbox {
    width: 400px;
    float: left;
}
#contain {
    width: 1100px;
}
.el-table__header {
    width: 100%;
}
.intertSelect {
    margin: 10px 0;
}
.breadcrumb{
    min-width:1200px;
}
.group .larWidth{
    width:495px;
}
</style>

<style>
.adv {
    width: 200px;
}
</style>

