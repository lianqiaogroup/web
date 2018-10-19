<template>
    <div>
        <div class="header-panel">
            <div class='control'>
                <div class="operations" style="display: flex;">
                    <el-button @click="exportExcel()">导出查询结果</el-button>
                    <!-- <el-button @click="batchChangestate()">批量修改为待下架状态</el-button> -->
                    <el-button type="primary" @click="isShow = true">批量搜索爆款站点</el-button>
                </div>
            </div>
        </div>
		<div class="container">
            <el-table :data="list" border tooltip-effect="dark" v-loading='table_loading' element-loading-text="拼命加载中" height="450">
                <el-table-column prop='number' label='序号' width="100px" align="center"></el-table-column>
                <el-table-column prop='erp_number' label='erp_id' width="100px" align="center"></el-table-column>
                <el-table-column prop='cnt' label='订单总数' width="100px" align="center"></el-table-column>
                <el-table-column prop='url' label='站点链接' align="center"></el-table-column>
                <el-table-column label='站点状态'  width="150px" align="center">
                    <template scope="scope">
                        <span>{{scope.row.is_del == 0 ? '上架' : scope.row.is_del == 1 ? '下架' : scope.row.is_del == 10 ? '待下架' : scope.row.is_del == -1 ? '不存在该站点' : 'err'}}</span>
                    </template>
                </el-table-column>
                <el-table-column label='操作' width="200px" align="center">
                    <template  scope="scope">
                        <el-button size="small" round v-show="scope.row.is_del == 1" @click="editStatus(scope.row.product_id)">修改为待下架</el-button>
                    </template>
                </el-table-column>
            </el-table>
            <!-- <div class="err-list"  style="margin-top:50px">
                <span  style="font-weight:700">以下ERP id 未找到：</span>
                <p style="color:red" v-text="noerp"></p>
            </div> -->
		</div>
        <el-dialog title="输入ERP id，一行一个" :visible.sync="isShow" width="30%" center>
            <el-input type="textarea" :rows="10" v-model="textarea"></el-input>
            <span slot="footer" class="dialog-footer">
                <el-button @click='isShow = false'>取消</el-button>
                <el-button type="primary" @click="confirmSearch()">确认</el-button>
            </span>
        </el-dialog>
        <form action="/hotReport.php?act=exportexcel" method="post" id="formData" style="display:none" target="_blank">
            <input type="text" name="list" v-model="erp_number">
        </form>
    </div>
</template>
<script>
  export default {
    data() {
      return {
        isShow:false, //搜索输入界面是否显示
        table_loading: false,
        textarea:'',    //输入的erp id
        list: [],   //查询到的erp id数据
        // noerp:'',   //不存在的erp_id
        ids: '',    //用于导出和批量修改状态的数据
        erp_number:'' //全部查询的erp_number
      }
    },
    methods:{
        // 单个修改状态为待下架
        editStatus(product_id){
            // console.log(product_id);
            this.$http.post('/hotReport.php?act=modify',`product_id=${product_id}`).then(res=>{
                // res.body.status = 1;
                if(res.body.status == 0) {
                    this.$message.error(res.body.msg)
                }else if(res.body.status == 1){
                    // console.log('成功');
                    this.list.forEach(item=>{
                        if(item.product_id == product_id) {
                            item.is_del = 10;
                        }
                    })
                }else {
                    this.$message.error('接口出错')
                }
                // console.log(this.list);
                
            })
        },
        //批量修改状态
        // batchChangestate(){
        //     this.$http.post('/hotReport.php?act=modify',`ids=${this.ids}`).then(res=>{
        //         // res.body.status = 1;
        //         if(res.body.status == 0) {
        //             this.$message.error('状态修改失败')
        //         }else if(res.body.status == 1){
        //             // console.log('成功');
        //             this.list.forEach(item=>{
        //                 if(item.is_del == 1) {
        //                     item.is_del = 10;
        //                 }
        //             })
        //         }else {
        //             this.$message.error('接口出错')
        //         }
        //         // console.log(this.list);
        //     })
        // },
        //搜索
        confirmSearch(){
            var erpIdArr = this.textarea.split("\n");
            // console.log(erpIdArr);
            for(var i = 0;i < erpIdArr.length;i++){
                if(!(/^[0-9]+$/.test(erpIdArr[i]))) {
                    break;
                }
            }
            if(i!=erpIdArr.length){
                this.$message.error('ERP ID 格式不规范');
                return false;
            }
            var erpIdString = erpIdArr.join();
            // console.log(erpIdString);
            var _api = `/hotReport.php?act=query`;
            this.$http.post(_api,`list=${erpIdString}`).then(res=>{
                // res.body = {"status":1,"data":[{"product_id":"1616","url":"www.livestou.com\/acac","title":"\u6d4b\u8bd520180124","erp_number":"106372","id_del":"0"},{"product_id":"1553","url":"www.livestou.com\/85000","title":"\u8273\u538b\u7fa4\u82b3\u5c0f\u7f8a\u76ae15CM\u8fc7\u819d\u5973\u9774","erp_number":"106371","id_del":"1"},{"product_id":"1621","url":"www.livestou.com\/22indtaocan","title":"\u6d4b\u8bd5\u5370\u5ea6\u90ae\u7f1639\u6a21\u7248","erp_number":"106379","id_del":"10"},{"product_id":"1619","url":"www.livestou.com\/3965","title":"\u6d4b\u8bd5\u5370\u5ea6\u6a21\u724822\u90ae\u7f16","erp_number":"106378","id_del":"1"}],"noerp":"1,2,0","ids":"1616,1553,1621,1619,"}
                if(res.body.status != 1){
                    this.$message.error('接口数据错误');
                    return false;
                }
                this.isShow = false;
                let tempNumber = 0;
                res.body.data.forEach(item => {
                    tempNumber ++;
                    item.number = tempNumber;
                });
                this.list = res.body.data;
                // console.log(this.list);
                // this.noerp = res.body.noerp;
                this.ids = res.body.ids;
                this.erp_number = res.body.erp_number;

            })

        },

        //导出查询结果
        exportExcel(){
            document.querySelector('#formData').submit();
        }
    }
  }
</script>
