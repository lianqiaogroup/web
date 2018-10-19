<template>
    <div>
        <div class="header-panel">
            <div class='control'>
                <div class="operations" style="display: flex;">
                    <el-upload
                        :action="importFileUrl"
                        name="zipFile"
                        :on-Error="uploadError"
                        :on-Success="uploadSuccess"
                        :before-Upload="beforeAvatarUpload"
                        :limit="1"
                        :show-file-list="false">
                        <!-- <el-button @click="importFlag = true;exportFlag = false;">导入产品信息</el-button> -->
                    </el-upload>
                    <el-button type="primary" @click="isShow = true;importFlag = false;exportFlag = true;">导出产品信息</el-button>
                </div>
            </div>
        </div>
		<div class="container">
            <div v-show="exportFlag">
                <h3 style="text-align:center;">需要导出的站点信息预览</h3>
                <el-table 
                :data="list" 
                border 
                tooltip-effect="dark"
                v-loading='table_loading' 
                element-loading-text="拼命加载中" 
                height="450"
                style="width: 100%"
                >
                    <el-table-column prop='number' label='序号' width="200px" align="center">
                        <template scope="scope">
                            {{scope.$index+1}}
                        </template>
                    </el-table-column>
                    <el-table-column prop='product_id' label='产品站点id' width="400px" align="center"></el-table-column>
                    <el-table-column prop='weburl' label='产品站点域名'  align="center"></el-table-column>
                </el-table>
                <div class="startExport" v-show="list.length != 0">
                    <el-button type="primary"  @click="exportExcel()">开始导出</el-button>
                </div>
            </div>
            <div v-show="importFlag">
                <h3 style="text-align:center;">需要导入的站点信息预览</h3>
                <el-table
                 :data="importList" 
                 border 
                 tooltip-effect="dark"
                 v-loading='table_loading'
                 element-loading-text="拼命加载中" 
                 height="450"
                 @selection-change="handleSelectionChange"
                 ref="multipleTable"
                 style="width: 100%"
                 >
                    <el-table-column type="selection" width="55"></el-table-column>
                    <el-table-column prop='number' label='序号' width="70px" align="center">
                        <template scope="scope">
                            {{scope.$index+1}}
                        </template>
                    </el-table-column>
                    <el-table-column prop='product_id' label='原产品站点id'  align="center"></el-table-column>
                    <el-table-column prop='erp_number' label='erpNumber'  align="center"></el-table-column>
                    <el-table-column prop='weburl' label='产品站点域名'  align="center"></el-table-column>
                    <el-table-column label='目标部门'  align="center" width="200px">
                        <template scope="scope">
                            <el-select  v-model="scope.row.oa_id_department" @change="departmentChange(scope.row.oa_id_department,scope.$index)"  placeholder="目标部门">
                                <el-option
                                v-for="item in departmentList"
                                :key="item.id_department"
                                :label="item.department"
                                :value="item.id_department">
                                </el-option>
                            </el-select>
                        </template>
                    </el-table-column>
                    <el-table-column label='目标优化师'  align="center" width="200px">
                        <template scope="scope">
                            <el-select  v-model="scope.row.aa" @change="tutChange(scope.row.oa_id_department,scope.row.erp_number,scope.row.aa,scope.$index)"  placeholder="请选择">
                                <el-option
                                v-for="item in scope.row.list"
                                :key="item.uid"
                                :label="item.name_cn"
                                :value="item.username">
                                </el-option>
                            </el-select>
                        </template>
                    </el-table-column>
                    <el-table-column label='目标地区'  align="center" width="200px">
                        <template scope="scope">
                            <el-select  v-model="scope.row.id_zone" @change="regionChange(scope.row.id_zone,scope.$index)"  placeholder="请选择">
                                <el-option
                                v-for="item in scope.row.productZoneList"
                                :key="item.id_zone"
                                :label="item.title"
                                :value="item.id_zone">
                                </el-option>
                            </el-select>
                        </template>
                    </el-table-column>
                     <el-table-column label='目标域名'  align="center" width="200px">
                        <template scope="scope">
                            <el-select  v-model="scope.row.domain"  placeholder="请选择">
                                <el-option
                                 v-for="item in scope.row.list1"
                                :key="item.domain"
                                :label="item.domain"
                                :value="item.domain">
                                </el-option>
                            </el-select>
                        </template>
                    </el-table-column>
                     <el-table-column label='目标二级域名'  align="center" width="200px">
                         <template scope="scope">
                             <el-input v-model="scope.row.identity_tag" @blur="inputLimit(scope.row.identity_tag,scope.$index)"  placeholder="只能包含字母或数字"></el-input>
                         </template>
                     </el-table-column>    
                     <el-table-column label='状态'  align="center" width="100px">
                         <template scope="scope">
                             <span :class="[{'okStatus':scope.row.status == 'success'},{'failStatus':scope.row.status == 'failed'}]">{{scope.row.status}}</span>
                         </template>
                     </el-table-column>
                     <el-table-column label='详情'  align="center" width="100px">
                         <template scope="scope">
                             {{scope.row.info}}
                         </template>
                     </el-table-column>     
                </el-table>
                <div class="startExport" v-show="postData.length>0">
                    <el-button type="primary"  @click="save()">开始保存</el-button>
                </div>
                <!-- <div class="startExport" v-show="list.length != 0">
                    <el-button type="primary"  @click="exportExcel()">开始导出</el-button>
                </div> -->
            </div>
		</div>
        <el-dialog title="输入产品id，一行一个（每次不能超过20个站点）" :visible.sync="isShow" width="30%" center>
            <el-input type="textarea" :rows="10" v-model="textarea"></el-input>
            <span slot="footer" class="dialog-footer">
                <el-button @click='isShow = false'>取消</el-button>
                <el-button type="primary" @click="confirmSearch()">确认</el-button>
            </span>
        </el-dialog>
        <form action="/tooltmp.php?act=exportconfirm" method="post" id="formData" style="display:none" target="_blank">
            <input type="text" name="list" v-model="erpIdString">
        </form>
    </div>
</template>
<script>
export default {
    data() {
        return {
            importFileUrl: "tooltmp.php?act=importPack",
            isShow: false, //搜索输入界面是否显示
            table_loading: false,
            textarea: "", //输入的产品 id
            list: [], //查询到的产品 id数据
            ids: "", //用于导出和批量修改状态的数据
            erp_number: "", //全部查询的产品_number
            erpIdString: "",
            exportFlag: true,
            importFlag: false,
            importList: [],
            multipleSelection: [],
            value: "",
            departmentList: [],
            postData: []
        };
    },
    methods: {
        //导出产品信息
        confirmSearch() {
            this.importFlag = false;
            this.exportFlag = true;
            var erpIdArr = this.textarea.split("\n");
            for (var i = 0; i < erpIdArr.length; i++) {
                if (!/^[0-9]+$/.test(erpIdArr[i])) {
                    break;
                };
            };
            if (i != erpIdArr.length) {
                this.$message.error("产品id 格式不规范");
                return false;
            };
            if (erpIdArr.length > 20) {
                this.$message.error("最多不能输入超过20个站点！");
                return false;
            };
            var erpIdString = erpIdArr.join();
            var _api = `/tooltmp.php?act=exportProducts`;
            this.table_loading = true;
            this.$http.post(_api, `list=${erpIdString}`).then(res => {
                this.table_loading = false;
                if (res.body.status != 200) {
                    this.$message.error("接口数据错误");
                    return false;
                };
                this.isShow = false;
                this.list = res.body.info;
                this.erpIdString = erpIdString;
            });
        },
        handleSelectionChange(val) {
            // this.selected = val.map(x => x.tid);
            this.multipleSelection = val;
            this.postData = val;
        },
        inputLimit(value, row) {
            if (/[\W]/g.test(value)) {
                alert("只能包含英文和数字！");
                this.importList[row].identity_tag = value.replace(/[\W]/g, "");
                return false;
            }
        },
        //选择部门
        departmentChange(id, row) {
            this.importList[row].aa = "";
            var _api = `/tooltmp.php?act=getMembersByDpID&department_id=`;
            this.table_loading = true;
            this.$http.get(_api + id).then(res => {
                this.table_loading = false;
                for (var i = 0; i < res.body.ret.length; i++) {
                    if (res.body.ret[i].username == "") {
                        res.body.ret[i].username = i + "test";
                    };
                };
                this.importList[row].list = res.body.ret;
            });
        },
        //选择优化师
        tutChange(department_id, erp_number, username, row) {
            this.importList[row].domain = "";
            this.importList[row].id_zone = "";
            this.table_loading = true;
            var _api = `/tooltmp.php?act=getDomainAndAreaByloginID&`;
            this.$http
                .get(
                    _api +
                        `id_department=` +
                        department_id +
                        "&loginid=" +
                        username +
                        "&erp_number=" +
                        erp_number
                )
                .then(res => {
                    this.table_loading = false;
                    for (var i = 0; i < res.body.domainList.length; i++) {
                        res.body.domainList[i].domain =
                            "www." + res.body.domainList[i].domain;
                    }
                    this.importList[row].list1 = res.body.domainList;
                    this.importList[row].productZoneList =
                        res.body.productZoneList;
                });
            let obj = {};
            if (this.importList[row].list.length > 0) {
                obj = this.importList[row].list.find(item => {
                    return item.username === username;
                });
            }
            if (obj) {
                this.importList[row].ad_member_id = obj.uid;
                this.importList[row].ad_member = obj.name_cn;
            }
        },
        //选择目标地区
        regionChange(value, row) {
            let obj = {};
            if (this.importList[row].productZoneList.length > 0) {
                obj = this.importList[row].productZoneList.find(item => {
                    return item.id_zone === value;
                });
            }
            if (obj) {
                this.importList[row].id_zone_name = obj.title;
            }
        },
        save() {
            //深拷贝
            var cloneObj = function(obj) {
                var str,
                    newobj = obj.constructor === Array ? [] : {};
                if (typeof obj !== "object") {
                    return;
                } else if (window.JSON) {
                    (str = JSON.stringify(obj)), (newobj = JSON.parse(str));
                } else {
                    for (var i in obj) {
                        newobj[i] =
                            typeof obj[i] === "object"
                                ? cloneObj(obj[i])
                                : obj[i];
                    }
                }
                return newobj;
            };
            var data = cloneObj(this.postData);
            data.forEach(function(item) {
                delete item.erp_number;
                delete item.list;
                delete item.list1;
                delete item.productZoneList;
                delete item.weburl;
                delete item.info;
                delete item.status;
                delete item.aa;
                delete item.status;
                delete item.info;
            });
            for (var i = 0; i < data.length; i++) {
                if (data[i].oa_id_department == "") {
                    alert("目标部门不能为空");
                    return false;
                }
                if (data[i].ad_member_id == "") {
                    alert("目标优化师不能为空");
                    return false;
                }
                if (data[i].id_zone == "") {
                    alert("目标部门地区不能为空");
                    return false;
                }
                if (data[i].domain == "") {
                    alert("目标域名不能为空");
                    return false;
                }
                if (data[i].identity_tag == "") {
                    alert("目标二级域名不能为空");
                    return false;
                }
            }
            var url = "/tooltmp.php?act=importproduct";
            this.table_loading = true;
            this.$http
                .post(url, data, {
                    headers: { "Content-Type": "application/json" }
                })
                .then(res => {
                    this.table_loading = false;
                    for (var i = 0; i < res.body.list.length; i++) {
                        for (var j = 0; j < this.importList.length; j++) {
                            if (
                                res.body.list[i].product_id ==
                                this.importList[j].product_id
                            ) {
                                if (res.body.list[i].info) {
                                    this.importList[j].info =
                                        res.body.list[i].info;
                                }
                                this.importList[j].status =
                                    res.body.list[i].status;
                            }
                        }
                    }
                    if (res.body.ret == 200) {
                        this.$message({
                            message: "产品已经成功导入！",
                            type: "success"
                        });
                    } else if (res.body.ret == 202) {
                        this.$message.error(
                            "导入的产品信息有误，请确认后重新保存！"
                        );
                    };
                });
        },
        //导出查询结果
        exportExcel() {
            document.querySelector("#formData").submit();
        },
        uploadSuccess(response, file, fileList) {
            this.importFlag = true;
            this.exportFlag = false;
            this.table_loading = true;
            //上传成功的回调函数
            if (response.code == 1) {
                for (var i = 0; i < response.data.length; i++) {
                    response.data[i].oa_id_department = "";
                    response.data[i].identity_tag = "";
                    response.data[i].ad_member_id = "";
                    response.data[i].id_zone = "";
                    response.data[i].domain = "";
                    response.data[i].ad_member = "";
                    response.data[i].aa = "";
                    response.data[i].id_zone = "";
                    response.data[i].id_zone_name = "";
                    response.data[i].list = [];
                    response.data[i].list1 = [];
                    response.data[i].productZoneList = [];
                    response.data[i].status = "-";
                    response.data[i].info = "-";
                }
                this.departmentList = response.departmentList;
                this.importList = response.data;
                this.table_loading = false;
            }else{
                this.table_loading = false;
                this.$message.error("导入的文件有误，请确认后重新导入！");
            }
        },
        // 上传错误
        uploadError(response, file, fileList) {
            alert("上传失败，请重试！");
            return false;
        },
        // 上传前对文件的大小的判断
        beforeAvatarUpload(file) {
            const extension = file.name.split(".")[1] === "zip";
            const isLt2M = file.size / 1024 / 1024 < 64;
            if (!extension) {
                this.$message.error("导入文件只能是zip格式!");
                return false;
            }
            if (!isLt2M) {
                this.$message.error("导入文件大小不能超过 64MB!");
                return false;
            }
            return extension && isLt2M;
        }
    }
};
</script>

<style scoped>
.startExport {
    margin-top: 20px;
}
.startExport button {
    float: right;
}
#mainlayer .container {
    padding: 30px 10px;
}
#app div.el-select-dropdown {
    margin-left: 0;
}
.okStatus {
    color: green;
}
.failStatus {
    color: red;
}
</style>

