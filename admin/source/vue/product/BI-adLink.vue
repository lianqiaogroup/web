<template>

    <div>
        <div class="header-panel">
            <div class='control'>
                <div class='op-flexbox'>
                    <div class='op-box'>
                        <div @keyup.enter='get()'>
                                <input ref='sname' placeholder='广告系列' style='width: 160px; height: 24px; border-radius: 2px; border: 1px solid #0080FF; padding:8px 16px;'>
                                <!-- <input ref='aname' placeholder='广告组' style='width: 80px; height: 24px; border-radius: 2px; border: 1px solid #0080FF; padding:8px 16px;'> -->
                        </div>
                        <div @click='get()' style='margin-left: 10px;'>
                            <el-button type="text" size='mini'>查询</el-button>
                        </div>
                    </div>
                </div>
                <div class="operations" style="display: flex;position: absolute;top: 18px;right: 0;padding: 0;">
                    <div @click='handleAddLink' style='margin-left: 10px;'>
                        <el-button icon="plus" type="primary" @click='handleAddLink'>添加广告链接</el-button>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <el-table :data="log" border tooltip-effect="dark" v-loading='table_loading' element-loading-text="拼命加载中">
                <el-table-column prop='id' label='ID' width="150px" align="center"></el-table-column>
                <el-table-column prop='ad_channel' label='广告系列' width="300px" align="center"></el-table-column>
                <!-- <el-table-column prop='ad_group' label='广告组'></el-table-column> -->
                <el-table-column prop='ad_bilink' label='广告链接' align="center">
                    <template scope='inner'>
                        <div style='display: flex; flex-flow: row nowrap;' :data-index='inner.$index'>
                            <el-button type='text' size='mini' @click='doCopy(inner.$index, inner.row)' :data-index='inner.$index'>复制链接</el-button>
                            <span :title="inner.row.ad_bilink" style='height: 24px;' :data-index='inner.$index'>{{inner.row.ad_bilink}}</span>
                            <input style='width: 1px; height: 1px; opacity: 0; pointer-events: none;' :value='inner.row.ad_bilink' :data-index='inner.$index'>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop='' label='广告操作' width="150px" align="center">
                    <template scope='inner'>
                        <el-button type='text' @click='handleEditLink(inner.row)'>编辑</el-button>
                    </template>
                </el-table-column>
            </el-table>
        </div>

        <el-pagination small layout="prev, pager, next, jumper, slot" :page-size="1" :current-page.sync='page.p' @current-change="handleCurrentChange" :total="page.count"
            class='with-button'>
            <el-button class='el-pagination__append'>跳转</el-button>
        </el-pagination>

        <el-dialog :visible.sync='dialog.BILink' title='BI广告链接生成' size='small'>
            <el-form id='form' name='form' label-width="80px" style='margin-right: 50px;'>
                <el-form-item label='广告渠道'>
                    <el-input disabled v-model='BIdata.ad_new_channel'></el-input>
                </el-form-item>
<!--                 <el-form-item label='广告媒介'>
                    <el-input v-model='BIdata.ad_media' disabled></el-input>
                </el-form-item> -->
                <el-form-item label='广告系列'>
                    <el-input v-model='BIdata.ad_channel' required='required' disabled></el-input>
                </el-form-item>
                <!-- <el-form-item label='广告组'>
                    <el-input v-model='BIdata.ad_group' required='required'></el-input>
                </el-form-item> -->
<!--                 <el-form-item label='优化师'>
                    <el-select v-model="BIdata.ad_member" placeholder="请选择">
                        <el-option
                            v-for="item in ad_member_list"
                            :key="item.uid"
                            :label="item.name_cn"
                            :value="item.uid">
                        </el-option>
                    </el-select>
                </el-form-item> -->
<!--                 <el-form-item label='部门'>
                    <el-input disabled v-model='BIdata.ad_department'></el-input>
                </el-form-item> -->
                <el-form-item>
                    <el-button type="primary" @click='handleSaveBILink' :loading='generating' :disabled='generating'>生成广告链接</el-button>
                </el-form-item>
                <el-input type="textarea" :rows="3" v-model='BIdata.ad_bilink' disabled></el-input>
            </el-form>
        </el-dialog>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                is_admin: false,
                admin: {},
                log: [],
                table_loading: false,
                edit: {
                    id: '',
                },
                searchParams: {
                    product_id: 0,
                    oa_id_department: '',
                    ad_member: '',
                },
                product: {},
                page: {
                    p: 1,
                    count: 1,
                },
                generating: false,
                product_data: {},
                dialog: {
                    BILink: false
                }
                , BIdata: {
                    link: '',
                    ad_channel: '',
                    ad_media: 'CPM',
                    // ad_group: '',
                    ad_series: '',
                    ad_name: '',
                    ad_bilink: '',
                    ad_id_department: '',
                    ad_department: '',
                    ad_loginname: '',
                    ad_loginid: '',
                    ad_name: '',
                    ad_new_channel: '',
                    product_id: '',
                    total_times: '',
                    ad_member: '',
                }
                , ad_member_list: []
            }
        },
        methods: {
            get() {
                this.$service('product#list', 'product_id', this.product_id, 0, 1).then(res => {
                    if(res.body.res == "fail"){
                        console.log(123)
                        this.$message(res.body.msg);
                        history.back(-1);
                    }else{
                        this.product = res.body.goodsList[0];
                    }
                });
                this.$service("indexData").then(res => {
                    this.is_admin = res.body.admin.is_admin;
                    this.admin = res.body.admin;
                });
                this.$service('BIlink#list', this.product_id, '', this.$refs.sname.value, this.page.p).then(res => {
                    if (res.body.res == 'succ' || res.body.msg == "no data found") {
                        this.log = res.body.data.bidata.goodsList;
                        this.page.count = res.body.data.bidata.pageCount;
                    }
                });

            },
            handleCurrentChange(val) {
                this.page.p = val;
                this.get();
            },
            checkFields(){
               
                // if(!this.BIdata.ad_group){
                //     this.$message("需要填写广告组名称");
                //     return false;
                // }
                if(!this.BIdata.ad_new_channel){
                    this.$message("需要填写广告渠道");
                    return false;
                }
                return true;
            }
            , handleAddLink() {
                function padStart(str, len) {
                    str = str.toString();
                    if (str.length >= len) return str;
                    if (str.length == len - 1) return "0" + str;
                    return Array.apply(null, Array(len - str.length)).map(_ => "0").join("") + str;
                }

                var s = this.searchParams;
                this.$service('BIlink#extdata', this.product_id, this.product.oa_id_department, this.product.ad_member).then(res => {
                    var extdata = res.body.data.extdata;
                    this.BIdata = {
                        link: '',
                        ad_media: this.product_id,
                        // ad_group: '',
                        ad_series: '',
                        ad_channel: '1' + padStart(this.product_id, 10) + padStart(extdata.total_times + 1, 3),
                        ad_bilink: '',
                        ad_name: '',
                        ad_new_channel: `${this.product.domain}/${this.product.identity_tag || ''}`,
                        ad_id_department:  this.product.oa_id_department,
                        ad_department: extdata.department,
                        ad_loginname: this.admin.login_name,
                        ad_loginid: extdata.loginid,
                        total_times: '',
                        product_id: this.product_id,
                        ad_member: extdata.ad_member
                    }
                    this.ad_member_list = extdata.dep_member_lists;
                    this.edit.id = undefined;
                    this.dialog.BILink = true;
                    
                }, console.error);


            },
            handleEditLink(row){
                
                function padStart(str, len) {
                    str = str.toString();
                    if (str.length >= len) return str;
                    if (str.length == len - 1) return "0" + str;
                    return Array.apply(null, Array(len - str.length)).map(_ => "0").join("") + str;
                }

                var s = this.searchParams;
                Promise.all([this.$service('BIlink#list', this.product_id, row.id, undefined, 1),
                this.$service('BIlink#extdata', this.product_id, this.product.oa_id_department, this.product.ad_member)]).then(([res2, res]) => {
                    var extdata = res.body.data.extdata;
                    var d = res2.body.data.bidata.goodsList[0];
                    this.BIdata = {
                        link: '',
                        ad_media: d.ad_media,
                        // ad_group: d.ad_group,
                        ad_series: d.ad_series,
                        ad_channel: d.ad_channel,
                        ad_bilink: d.ad_bilink,
                        ad_name: d.ad_name,
                        ad_new_channel: d.ad_new_channel,
                        ad_id_department:  this.product.oa_id_department,
                        ad_department: extdata.department,
                        ad_loginname: this.admin.login_name,
                        ad_loginid: extdata.loginid,
                        total_times: '',
                        product_id: this.product_id,
                        ad_member: d.ad_member,
                    }
                    this.ad_member_list = extdata.dep_member_lists;
                    this.edit.id = d.id;
                    this.dialog.BILink = true;
                    
                }, console.error);
            },
            handleSaveBILink(){
                if(!this.checkFields()) return;
                this.generating = true;
                var p = this.product_data, b = this.BIdata, pid = this.product_id;
                this.BIdata.ad_bilink = `http://${this.product.domain}/${this.product.identity_tag || ''}?utm_campaign=${encodeURIComponent(b.ad_channel)}`;
                
                this.$service("BIlink#save", pid, b, this.edit.id).then(res => {
                    if(res.body.res == "succ"){
                        this.$message("保存成功");
                        this.dialog.BILink = false;
                        setTimeout(function(){
                            this.get()
                            this.generating = false;
                        }.bind(this), 1500);
                    }else{
                        var msg = res.body.msg != "" ? res.body.msg : "保存失败";
                        this.$message(msg);
                        this.generating = false;
                    }
                }, console.error);
            },
            doCopy(index, row) {
                try {
                    let el = document.querySelector("input[data-index='" + index + "']");
                    el.value = row.ad_bilink;
                    el.select();
                    document.execCommand("copy");
                    this.$message("成功复制到剪贴板");
                } catch (e) {
                    this.$message("复制失败");
                }
            }
        },
        mounted() {
            if (this.$route.params.id) {
                this.product_id = this.$route.params.id;
                this.get();
            } else {
                history.back(-1);
            }
            // this.get();
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