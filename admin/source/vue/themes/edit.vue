<template>
    <div id="page-themes-edit">
        <div class="header-panel">
            <div class="control">
                <div class="operations">
                    <el-button @click="handleSave" icon="plus" type="primary">保存模版</el-button>
                </div>
            </div>
        </div>

        <div class="container">
            <el-form :label-position="labelPosition" label-width="80px" :model="formValue">
                <el-row :gutter="20" class="first_row">
                    <el-col :span="8">
                        <el-form-item label="模版代号">
                            <el-input v-model="formValue.theme"></el-input>
                        </el-form-item>
                    </el-col>
                    <el-col :span="8">
                        <el-form-item label="模版名称">
                            <el-input v-model="formValue.title"></el-input>
                        </el-form-item>
                    </el-col>
                    <el-col :span="8">
                        <el-form-item label="部门权限">
                            <el-input v-model="formValue.belong_id_department"></el-input><span>多个部门id，请使用英文的“,”间隔</span>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-row :gutter="20">
                    <el-col :span="24">
                        <el-form-item label="适用地区">
                            <el-select v-model="formValue.country" multiple placeholder="请选择地区">
                                <el-option
                                  v-for="item in options.region"
                                  :key="item.code"
                                  :label="item.title"
                                  :value="item.code">
                                </el-option>
                            </el-select>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-row :gutter="20">
                    <el-col :span="24">
                        <el-form-item label="适用语种">
                            <el-select v-model="formValue.language" multiple placeholder="请选择语种">
                                <el-option
                                  v-for="item in options.language"
                                  :key="item.code"
                                  :label="item.label"
                                  :value="item.code">
                                </el-option>
                            </el-select>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-row :gutter="20">
                    <el-col :span="24">
                        <el-form-item label="模版风格">
                            <el-select v-model="formValue.style" multiple placeholder="请选择模版风格"></el-select>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-row :gutter="20">
                    <el-col :span="24">
                        <el-form-item label="图片链接">
                            <el-input v-model="formValue.thumb"></el-input>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-row :gutter="20">
                    <el-col :span="24">
                        <el-form-item label="预览链接">
                            <el-input v-model="formValue.referto_links"></el-input>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-row :gutter="20">
                    <el-col>
                        <el-form-item label="模版简介">
                            <el-input type="textarea" :rows="4" v-model="formValue.desc"></el-input>
                        </el-form-item>
                    </el-col>
                </el-row>
            </el-form>
        </div>
    </div>
</template>
<style scoped>
    .first_row .el-form-item{margin-bottom: 0;}
</style>
<script>
    Vue.http.options.headers = {
        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
    };
    export default {
        data() {
            return {
                labelPosition: 'top',
                formValue: {
                    tid: null
                    , theme: ''
                    , title: ''
                    , country: []
                    , language: []
                    , belong_id_department: ""
                    , style: ''
                    , desc: ''
                    , thumb: ''
                    , referto_links: ''
                }
                , options: {
                    region: []
                    , language: []
                }
            }
        }
        , mounted() {
            var self = this;

            // 获取语种
            this.$http.get('/template/config/theme_language').then(res=>{
                var jsonString = res.bodyText.replace(/\s/g, '');
                var data = JSON.parse(jsonString);
                for(let code in data){
                    this.options.language.push({
                        'code': code
                        , 'label': data[code]
                    });
                }
            });
            // 获取数据
            this.$http.post('theme.php?act=edit&id='+ this.$route.params.id||0).then(res => {
                // 地区
                this.options.region = res.body.id_zones;
                // 设置数据
                if (this.$route.params.id) {
                    this.formValue = {
                        tid: res.body.tid
                        , title: res.body.title || ""
                        , theme: res.body.theme
                        , belong_id_department: res.body.belong_id_department
                        , country: res.body.zone.split(',')
                        , language: res.body.lang.split(',')
                        , style: res.body.style ? res.body.style.split(',') : []
                        , desc: res.body.desc
                        , thumb: res.body.thumb
                        , referto_links: res.body.referto_links
                    }
                }
            });
        }
        , methods: {
            handleSave() {

                var formdata = this.formValue;
                    formdata.zone = this.formValue.country.join(',');
                    formdata.lang = this.formValue.language.join(',');
                    if(this.formValue.style.length>0){
                        formdata.style = this.formValue.style.join(',');
                    }
                    
                this.$http.post('theme.php?act=save', formdata, { emulateJSON: true }).then(res => {
                    if (res.body.ret == '1') {
                        this.$message({ message: '添加成功', type: 'success' });
                        this.$router.push({ path: '/themes' });
                    } else {
                        this.$message.error(res.body.msg);
                    }
                });
            }
        }
    }

</script>