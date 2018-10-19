<template>
	<div id="headers" class="headers">
	   <div class="logo" onclick='window.location="#/"; void(0);'>
         <div class="logo-body" >
          <div class="logo-name">
            <img src='' ref='logo' width='192px'>
          </div>
          <div class="logo-text">布谷鸟建站系统v4.39</div>
         </div>
        
       </div>
       <div class="login-info">
        <div class="login-body">
         <div class="login-text">Hi,<span>{{ username }}</span> <a href='/index.php?act=logout' class="login-out">注销</a></div>
        </div>
       </div>
	</div>
</template>

<script>
export default {
  props: ['username'],
  data () {
    return {
      version: ''
    }
  },
  methods: {
        
  },
  mounted(){
    this.$refs.logo.src = document.getElementById("appPath").value + "/images/Cuckoo.png"
  }
} 
</script>
 