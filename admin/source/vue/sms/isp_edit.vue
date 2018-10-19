<template>
<div class="isp_edit">
  <div class="header-panel">
      <div class="control">
        <router-link to="/sms/isp_states">
          <el-button>短信提供商列表</el-button>
        </router-link>
        <router-link to="/sms/states_list">
          <el-button>所有关联列表</el-button>
        </router-link>
      </div>
  </div>
  <div class="form">
    <el-form :model="ruleForm" :rules="rules" ref="ruleForm" label-width="100px" class="demo-ruleForm">
    <el-form-item label="名称">
      <el-input v-model="ruleForm.ispname"></el-input>
    </el-form-item>
    <el-form-item label="类名">
      <el-input v-model="ruleForm.classname"></el-input>
    </el-form-item>
    <el-form-item label="状态">
      <el-radio-group v-model="ruleForm.status">
        <el-radio class="radio" v-model="ruleForm.status" label="1">开启</el-radio>
        <el-radio class="radio" v-model="ruleForm.status" label="0">关闭</el-radio>
      </el-radio-group>
    </el-form-item>
    <el-form-item>
      <el-button type="primary" @click="submitForm('ruleForm')">保存</el-button>
      <el-button @click="backlist()">返回</el-button>
    </el-form-item>
  </el-form>
  </div>
</div>
</template>
<script>
  Vue.http.options.emulateJSON = true;
   export default {
      data() {
        return {
          ruleForm: {
            ispname:'',
            classname:'',
            status:''
          },
          rules: {
            ispname: [
              { required: true, message: '请输入名称', trigger: 'blur' }
            ],
            classname: [
              { required: true, message: '请选类名', trigger: 'change' }
            ],
            status: [
              { required: true, message: '请选择状态', trigger: 'change' }
            ]
          }
        }
      },
      mounted(){
        if(this.$route.params.id){
          this.getisp_list();
        }else{
          this.ruleForm = {
            ispname:'',
            classname:'',
            status:''
          }
        }
        
      },
      methods:{
        getisp_list(){
          this.$http.get('/sms.php?id='+ this.$route.params.id +'&act=isp_edit').then(function(result){
            var body_list = JSON.parse(result.bodyText);
            console.log(body_list)
            this.ruleForm = {
              ispname:body_list.ispname,
              classname:body_list.classname,
              status:body_list.status
            }
            
          })
          
        },
        submitForm(formName){
          //this.$router.push({path:'/sms/isp_edit/'+ param});
          this.$refs[formName].validate((valid) => {
            if (valid) {
              var formdata = this.ruleForm;
              this.$http.post('/sms.php?act=save_isp',formdata).then(function(result){
                console.log(result);
              })
            } else {
              console.log('error submit!!');
              return false;
            }
          });
        },
        backlist(){
          this.$router.push({path:'/sms/isp_states'});
        }
      }
  }
</script>