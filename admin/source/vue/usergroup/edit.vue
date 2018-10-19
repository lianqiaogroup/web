<template>
    <div class="sites">
        <div class="header-panel">
            <div class="control">
                <div class="operations">
                        <div @click='handleSave()'><el-button icon="check" type="primary">保存设置</el-button></div>
                </div>
                <div class="hero-title" v-text = 'user_group_id ? "编辑用户组" : "新增用户组"'>
                        
                </div>
                <div class="hero-link" >
                   <div @click='$router.push({path: "/usergroup"})'> <i class="el-icon-arrow-left"></i>返回列表</div>
                </div>
            </div>
        </div>
        <div class="content" style='margin-top: 50px; '>
            <el-form v-if='user_group_id'>
                <div class="flexbox quarter">
                    <el-form-item label="用户组">
                        <el-input v-model='info[0].title' placeholder='标题'></el-input>
                    </el-form-item>
                </div>
                <div class="flexbox half">
                    <el-form-item label="备注">
                        <el-input type='textarea' v-model='info[0].remark'></el-input>
                    </el-form-item>
                </div> 
            </el-form>
            <el-form v-if='!user_group_id' v-for='(item, index) in info'>
                <div class="flexbox half">
                    <el-form-item label="用户组标题">
                        <el-input v-model='item.title' placeholder='标题'>
                            <el-button size="small" slot='prepend' type="danger" v-if='index > 0' @click='handleDelete(index)'>删除本行</el-button>
                        </el-input>
                    </el-form-item>
                    <el-form-item label="备注">
                        <el-input v-model='item.remark'></el-input>
                    </el-form-item>
                </div>
            </el-form >
            <el-form v-if='!user_group_id'>
                <div style='padding-left: 50px;'><el-button type='primary' icon='plus'  @click='handleAdd()' >添加一行</el-button></div>
            </el-form>
        </div>
    </div>
</template>
<script>
    export default {
        data(){
            return {
                user_group_id: "",
                info: [{title:"", remark:""}],
            }
        }, mounted(){
            this.user_group_id = this.$route.params.user_group_id || "";
            this.get();
        }, methods: {
            get(){
                if(this.user_group_id){
                    this.$service('usergroup#list').then(res => {
                        let list = res.body.goodsList || [];
                        Object.assign(this.info[0], list.filter(x => x.user_group_id == this.user_group_id)[0]);
                    });
                }
            },
            handleSave(){
                console.log(1);
                this.$service('usergroup#save', this.info, this.user_group_id).then(res => {
                    if(res.body.ret == "0"){
                        this.$message.error({message: "保存失败[" + res.body.msg + "]"});
                    }else{
                        this.$message({message: "保存成功"});
                        this.$router.replace("/usergroup");
                    }
                });
            },
            handleDelete(index){
                this.info.splice(index, 1);
            },
            handleAdd(){
                this.info.push({
                    title: '',
                    remark: '',
                })
            }
        }
    }
</script>