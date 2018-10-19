<template>
    <div id="page-index">
        <div class="center">
          <charts ref='chart'></charts>
          <charts-pie ref='chartpie'></charts-pie>
        </div>
        <div class="container">
            <div class="sub-title">今日数据</div>
            <el-row type="flex" class="row-bg sub-data-list">
                <el-col :span="5">
                    <i class="material-icons" style="color: #00F;">playlist_add_check</i>
                    <div class="sub-data-num">{{ stat.newproduct }}</div>
                    <div class="sub-data-title">新增产品</div>
                </el-col>
                <el-col :span="5">
                    <i class="material-icons" style="color: rgb(255, 90, 95);">trending_up</i>
                    <div class="sub-data-num">{{ stat.newOrder }}</div>
                    <div class="sub-data-title">新增订单</div>
                </el-col>
                <el-col :span="5">
                    <i class="material-icons" style="color: rgb(102, 177, 25);">portable_wifi_off</i>
                    <div class="sub-data-num">{{ stat.fail }}</div>
                    <div class="sub-data-title">ERP通信失败</div>
                </el-col>
                <el-col :span="5">
                    <i class="material-icons" style="color: rgb(204, 204, 204);">extension</i>
                    <div class="sub-data-num">{{ stat.allProduct }}</div>
                    <div class="sub-data-title">所有产品</div>
                </el-col>
                <el-col :span="5">
                    <i class="material-icons" style="color: rgb(204, 204, 204);">receipt</i>
                    <div class="sub-data-num">{{ stat.allOrder }}</div>
                    <div class="sub-data-title">所有订单</div>
                </el-col>
            </el-row>
            <div style="margin-top:20px;">
                <a href="https://www.wjx.top/jq/19908138.aspx" target="_blank">
                    <el-button type="primary">FB反馈</el-button>
                </a>
            </div>
            <el-row type="flex" class="row-bg-2">
                <el-col :span="16">
                  <div class="grid-content bg-purple">
                    <div class="sub-title" style="color:red;" v-for='item in updateLog'> 版本更新: {{item.date}}
                       <ul>
                         <li>建站后台</li>
                         <li v-for='(li, index) in item.admin'><a href="javascript:;">{{index+1}}、{{li}}</a></li>
                         <li>单品站: </li>
                         <li v-for='(li, index) in item.shop'><a href="javascript:;">{{index+1}}、{{li}}</a></li>
                       </ul>
                    </div>
                  </div>
                </el-col>
                <el-col :span="8">
                  <div class="grid-content bg-purple">
                    <div class="sub-title">建站问题: 
                      <ul>
                        <li>
                          <a target="_blank" href="http://bbs.stosz.com/forum.php?mod=viewthread&tid=91&extra=page%3D1">怎么添加多产品套餐?</a>
                        </li>
                        <li>
                          <a target="_blank" href="http://bbs.stosz.com/forum.php?mod=viewthread&tid=92&extra=page%3D1">怎么添加赠品(附件)?</a>
                        </li>
                        <li>
                          <a target="_blank" href="http://bbs.stosz.com/forum.php?mod=viewthread&tid=89&extra=page%3D1">点击“选择产品时”找不到选择的产品?</a>
                        </li>
                        <li>
                          <a target="_blank" href="http://bbs.stosz.com/forum.php?mod=viewthread&tid=88&extra=page%3D1">如何在建站后台添加以及修改属性?</a>
                          </li>
                        <li>
                          <a target="_blank" href="http://bbs.stosz.com/forum.php?mod=viewthread&tid=87&extra=page%3D1">如何区分属性组id，属性id和产品erpid?</a>
                          </li>
                          <li>
                            <a target="_blank" href="http://bbs.stosz.com/forum.php?mod=viewthread&tid=92&extra=page%3D1">附件怎么也可以选择属性?</a>
                          </li>
                          <li>
                            <a target="_blank" href="http://bbs.stosz.com/forum.php?mod=viewthread&tid=86&extra=page%3D1">缩略图如何替换和删除?</a>
                            </li>
                          <li>
                            <a target="_blank" href="http://bbs.stosz.com/forum.php?mod=viewthread&tid=90&fromuid=30">已经在ERP添加了域名，但在建站后台显示找不到该域名信息?</a>
                            </li>
                      </ul>
                    </div>
                  </div>
                </el-col> 
            </el-row>
        </div>
    </div>
</template>
 
<script>
import charts from './charts.vue'
import chartsPie from './chartsPie.vue'  //导入首页饼图子组件

Object.entries = Object.entries || function(obj){
    return Object.keys(obj).map(function(x){
        return [x, obj[x]]
    });
}
export default {   
  data () {
    return {
        stat: {
            newproduct: 0
            , newOrder: 0
            , fail: 0
            , allProduct: 0
            , allOrder: 0
        },
        updateLog: require('json-loader!yaml-loader!../../../version-log.yml'),
    }
  }, 
  components: { charts, chartsPie }, 
  mounted() {
     window.addEventListener('resize', (e) => {
        Vue.rootHub.$emit('updateStat');
        Vue.rootHub.$emit('updateChart');
     }, false);
     
      this.get();
  }, methods: {
    get(){
        let res = {};
            res.body = {"style64": {"view": "27", "order": "0" }, "style32_2": {"view": "10", "order": "0" }, "style85": {"view": "9", "order": "0" }, "style87": {"view": "6", "order": "0" }, "style27_2": {"view": "6", "order": "0" }, "style73": {"view": "5", "order": "0" }, "style22": {"view": "5", "order": "0" }, "style7": {"view": "3", "order": "0" }, "style79": {"view": "3", "order": "0" }, "style50": {"view": "2", "order": "0" }, "style6_2": {"view": "2", "order": "0" }, "style5_2": {"view": "2", "order": "0" }, "style39": {"view": "1", "order": "0" }, "style77": {"view": "1", "order": "0" }, "style82": {"view": "1", "order": "0" }, "style33": {"view": "1", "order": "0" }, "style69": {"view": "1", "order": "0" }, "style57": {"view": "1", "order": "0" }};

        let arr = [];
        for( let row in res.body ){
            arr.push({
                'name': row.replace('style', '')
                , 'view': res.body[row].view
            });
        }
        arr = arr.sort(function(a,b){
            return parseInt(b.view) - parseInt(a.view);
        });
        arr = arr.slice(0,40);
        arr = arr.sort(function(a,b){
            return a.name > b.name;
        });
        this.$refs.chart.setParams(arr.map(row=>{return row.name;}), arr.map(row=>{return row.view;}));
  
    }
  }
}
</script>

  