<template>
    <div class="sites">
        <div class="header-panel">
            <div class="control">
                <div class="operations">
                        <div @click='handleSave'><el-button icon="check" type="primary">保存设置</el-button></div>
                </div>
                <div class="hero-title" v-text = 'uid ? "编辑用户" : "新增用户"'>
                        
                </div>
                <div class="hero-link" >
                     <div @click='$router.push({path: "/user"})'><i class="el-icon-arrow-left"></i>返回列表</div>
                </div>
            </div>
        </div>
        <div class="header-panel-addition inversed"></div>
        <div class="content">
            <el-form>
                <div class="flexbox half">
                    <el-form-item label="用户组">
                        <el-select v-model='user_group_id' placeholder='选择用户组' class='field'>
                            <el-option v-for='item in usergroups' :key='item.user_group_id' :value='item.user_group_id' :label='item.title'></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="用户名">
                        <el-input placeoholder='请输入用户名' v-model='username'></el-input>
                    </el-form-item>
                </div>
                <div class="flexbox quarter">
                    <el-form-item label="邮箱/email">
                        <el-input placeholder='请输入邮箱' v-model='email'></el-input>
                    </el-form-item>
                </div> 
                <div class="flexbox quarter">
                    <el-form-item label="密码(不修改密码，此项为空)">
                        <el-input type='password' v-model='input_password'></el-input>
                    </el-form-item>
                </div>
            </el-form>
        </div>
    </div>
</template>
<script>
    export default {
        data(){
            return {
                username: "",
                password: "",
                input_password: "",
                usergroups: [],
                email: "",
                uid: "",
                user_group_id: "",
            }
        }, mounted(){
            this.uid = this.$route.params.uid || "";
          console.log(this.$route.params.uid || "");
           this.get();
        }, methods: {
            get(){
                this.$service('usergroup#list').then(res => Vue.nextTick(_ => this.usergroups = res.body.goodsList)); 
                if(this.uid){
                    this.$service('user#list').then(res => {
                        let list = res.body.goodsList || [];
                        Object.assign(this, list.filter(x => x.uid == this.uid)[0]);
                    });
                }
            },
            handleSave(){
                this.$service('user#save', Object.assign({}, this, {password: this.input_password})).then(res => {
                    if(res.body.ret == "0"){
                        this.$message.error({message: "保存失败[" + res.body.msg + "]"});
                    }else{
                        this.$message({message: "保存成功"});
                        this.$router.replace("/user");
                    }
                });
            },
        }
    }
</script>