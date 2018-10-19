<template>
<div class="states_edit">
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
  <div class="from_">
    <el-form :model="ruleForm" :rules="rules" ref="ruleForm" label-width="200px" class="demo-ruleForm">
        <el-form-item label="选择服务提供商">
          <el-select v-model="check_ispid">
            <el-option
              v-for="item in ispid_sel"
              :key="item.check_ispid"
              :label="item.ispname"
              :value="item.id">
            </el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="选择地区">
          <el-select v-model="check_nation">
            <el-option
              v-for="item in nation"
              :key="item.check_nation"
              :label="item.region_name"
              :value="item.region_id">
            </el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="选择语言">
          <el-select v-model="ruleForm.lang">
            <el-option label="区域一" value="shanghai"></el-option>
            <el-option label="区域二" value="beijing"></el-option>
          </el-select>
        </el-form-item>
      <el-form-item label="前台显示模板">
        <el-checkbox-group v-model="ruleForm.type">
          <el-button type="primary">选择模板</el-button>
          <el-checkbox label="不使用模板" value="1" v-model="ruleForm.use_default"></el-checkbox>
        </el-checkbox-group>
      </el-form-item>
      <el-form-item label="区号">
        <el-input v-model="ruleForm.ncode"></el-input>
      </el-form-item>
      <el-form-item label="前缀">
        <el-input v-model="ruleForm.prefix"></el-input>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="submitForm('ruleForm')">保存</el-button>
        <el-button @click="resetForm('ruleForm')">返回</el-button>
      </el-form-item>
    </el-form>
  </div>
</div>
</template>
<script>
  export default {
    data() {
      return {
        ruleForm: {
          use_default:'',
          ncode:'',
          prefix:''
        },
        ispid_sel:[],
        check_ispid:'',
        nation:[],
        check_nation:'',
        lang:[],
        check_lang:'',
        this_theme:[],
        check_theme:'',
        rules: {
          name: [
            { required: true, message: '请选择服务提供商', trigger: 'change' },
          ],
          region: [
            { required: true, message: '请选择地区', trigger: 'change' }
          ],
          type: [
            { required: true, message: '请选择选择语言', trigger: 'change' }
          ],
          resource: [
            { required: true, message: '请选择活动资源', trigger: 'change' }
          ],
          desc: [
            { required: true, message: '请填写区号', trigger: 'blur' }
          ],
          desc: [
            { required: true, message: '请填写前缀', trigger: 'blur' }
          ]
        }
      };
    },
    mounted(){
       if(this.$route.params.id){
          this.getisp_list();
        }else{
          this.ruleForm = {
            use_default:'',
            ncode:'',
            prefix:''
          }
        }
    },
    methods: {
      getisp_list(){
        this.$http.get('/sms.php?id='+ this.$route.params.id +'&act=isp_state_edit').then(function(result){
          //console.log(result.bodyText);
          var datas = JSON.parse(result.bodyText);
          console.log(datas);
          this.ruleForm = {
            use_default:'',
            ncode:datas.ncode,
            prefix:datas.prefix
          }
          this.ispid_sel = datas.ispids;
          this.check_ispid = datas.ispid;
          this.nation  = datas.region;
          this.check_nation = datas.nation;
          // this.lang = ;
          // this.check_lang = ,
          // this.this_theme = ,
          // this.check_theme = ,
        })
      },
      submitForm(formName) {
        this.$refs[formName].validate((valid) => {
          if (valid) {
            alert('submit!');
          } else {
            console.log('error submit!!');
            return false;
          }
        });
      },
      resetForm(formName) {
        this.$refs[formName].resetFields();
      }
    }
  }
</script>