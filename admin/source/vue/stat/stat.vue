<template>
 
  <div class='stat' v-if='is_admin'>
    <div class='d0'>
      <div class='d1'>
        <div class='d10'>
          <div class='d100'>
            <div class='d1000'>
              &nbsp;
            </div>
            <div class='d1001 flex row'>
               <el-select size="small" v-model='left_time' no-data-text='今日' placeholder='今日' @change='getLeftData'>
                  <el-option key='0' value='0' label='今日'>今日</el-option>
                  <el-option key='7' value='7' label='最近7天'>最近7天</el-option>
                  <el-option key='30' value='30' label='最近30天'>最近30天</el-option>
                  <el-option key='90' value='90' label='最近90天'>最近90天</el-option>
                  <el-option key='1000000' value='1000000' label='全部'>全部</el-option>
              </el-select>
              <el-select size="small" v-model='left_type' no-data-text='全天' placeholder='全天' @change='getLeftData'>
                  <el-option key='0' value='0' label='全天'>全天</el-option>
                  <el-option key='1' value='1' label='00:00~08:00'>00:00~08:00</el-option>
                  <el-option key='2' value='2' label='08:00~16:00'>08:00~16:00</el-option>
                  <el-option key='3' value='3' label='16:00~24:00'>16:00~24:00</el-option>
              </el-select>
            </div>
          </div>
          <div class='d101 flex col' >
            <div class='d1010 flex row' element-loading-text="拼命加载中" v-loading="left_loading">
              <div v-text='leftData.visit'></div>
              <div v-text='leftData.order'></div>
              <div v-text='leftData.ratio'></div>
              <div v-text='leftData.average + "秒"'></div>
            </div>
            <div class='d1011 flex col'>
                <div class='d10110 flex row'>
                  <div>访客数</div>
                  <div>订单量</div>
                  <div>转化率</div>
                  <div>平均停留</div>
                </div>
                <div class='d10111 flex row' style='display: none;'>
                  <div><i class='el-icon-caret-bottom'></i><span>3200</span></div>
                  <div><i class='el-icon-caret-top'></i><span>5</span></div>
                  <div><i class='el-icon-caret-top'></i><span>0.23%</span></div>
                  <div></div>
                </div>
            </div>
          </div>
          <div class='d102' element-loading-text="拼命加载中" >
            <div class='d1020' ref='chart_left' id='chart_left' v-loading="left_loading">
            </div>
          </div>
        </div>
        <div class='d11'>
          <div class='d110'>
            <el-select size="small" placeholder='访客来源' v-model='right_command'><el-option value='tongji#zone' label='访客来源' key='访客来源'>访客来源</el-option></el-select>
            <el-select size="small" v-model='right_time' no-data-text='今日' placeholder='今日' @change='getRightData'>
              <el-option key='0' value='0' label='今日'>今日</el-option>
              <el-option key='7' value='7' label='最近7天'>最近7天</el-option>
              <el-option key='30' value='30' label='最近30天'>最近30天</el-option>
              <el-option key='90' value='90' label='最近90天'>最近90天</el-option>
              <!--<el-option key='1000000' value='1000000' label='全部'>全部</el-option>-->
            </el-select>
          </div>
          <div class="d111">
            <div class='d1110' ref='chart_right' id='chart_right'  element-loading-text="拼命加载中" v-loading="right_loading">
            </div>
            <div v-if='right_command="tongji#zone"' class='d1111 flex row'>
              <div v-for='item in device_data' v-text='item.os + ":" + item.count'>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class='d2'>
        <div class='d20'>
          <div class='d200'>
            <template v-if='!this.show_product'>
              <el-button type="primary" @click='doProduct(true, "show")'>产品数据</el-button>
              <el-button type="disabled">域名数据</el-button>
            </template>
            <template v-if='this.show_product'>
              <el-button type="disabled">产品数据</el-button>
              <el-button type="primary" @click='doProduct(false, "hide")'>域名数据</el-button>
            </template>
          </div>
          <div class='d201'>
            <div class='el-control-wrapper' v-on:keyup.enter='getDownData'><el-input size="small" :icon='$service("inputbox#icon", this.search)' placeholder='搜索域名' v-model='search' :on-icon-click='_ => this.search = ""'></el-input></div>
             <el-select size="small" v-model='down_time' no-data-text='全部' placeholder='全部' @change='getDownData'>
              <el-option key='0' value='0' label='今日'>今日</el-option>
              <el-option key='7' value='7' label='最近7天'>最近7天</el-option>
              <el-option key='30' value='30' label='最近30天'>最近30天</el-option>
              <el-option key='90' value='90' label='最近90天'>最近90天</el-option>
              <el-option key='1000000' value='1000000' label='全部'>全部</el-option>
            </el-select>
              <el-select size="small" v-model='down_type' no-data-text='全天' placeholder='全天' @change='getDownData'>
                  <el-option key='0' value='0' label='全天'>全天</el-option>
                  <el-option key='1' value='1' label='00:00~08:00'>00:00~08:00</el-option>
                  <el-option key='2' value='2' label='08:00~16:00'>08:00~16:00</el-option>
                  <el-option key='3' value='3' label='16:00~24:00'>16:00~24:00</el-option>
              </el-select>
          </div>
        </div>
        <div class='d21'  >
          <el-table
          :data='domain_data' v-if='!this.show_product' element-loading-text="拼命加载中" v-loading="down_loading">
            <el-table-column label='第一次访问时间' width='164' prop='t_min'></el-table-column>

            <el-table-column label='域名'  width='184' prop='host'></el-table-column>

            <el-table-column label='访问人数'  width='122' prop='u_count'></el-table-column>

            <el-table-column label='平均访问时间(秒)'  width='164' prop='t_average'></el-table-column>

            <el-table-column label='产品ID'  width='164' prop='product_id'></el-table-column>

        
            <el-table-column label='订单人数'  width='120' prop='o_count'></el-table-column>

            <el-table-column label='转换率'  width='188'><template scope='scope'>{{ $service('ratio', scope.row.conversion, 100) }}</template></el-table-column>

            
          </el-table>
          </div><div class='d21'  >

          
       
          <el-table
          :data='product_data' v-if='this.show_product'  element-loading-text="拼命加载中" v-loading="down_loading">
            <el-table-column label='第一次访问时间' width='164' prop='t_min'></el-table-column>

            <el-table-column label='产品ID'  width='100' prop='product_id'></el-table-column>

            <el-table-column label='产品名'  width='164' prop='title'></el-table-column>

            <el-table-column label='访问人数'  width='122' prop='u_count'></el-table-column>

            <el-table-column label="平均访问时间(秒)" width="100" prop='t_average'></el-table-column>

            <el-table-column label='订单数'  width='100' prop='o_count'></el-table-column>

            <el-table-column label='转换率'  width='164'><template scope='scope'>{{ $service('ratio', scope.row.conversion, 100) }}</template></el-table-column>

            <el-table-column label='总成交金额'  width='100' prop='o_p_sum'></el-table-column>

            <el-table-column label='成交用户数'  width='95' prop='o_u_count'></el-table-column>

            

            

          </el-table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  let OptionLeft = {
        color: ["rgb(98, 0, 234)"],
        backgroundColor: {
          color: "#F6F6F6"
        },

        title: {
            text: '',
            textStyle: {
               fontSize: 12,
               fontWeight: 'bold',
               color: '#222',
            },
            padding: [5, 0, 0, 127],
            top: 50,
            left: -70,
        },
        grid: [{
            x: '110px',
            y: '70px',
            //height: '97px',
        }],
        tooltip: {
            'trigger': 'axis',
            extraCssText: ''
        },
        xAxis: {
            data: ["00:00", "08:00", "16:00", "24:00"],
            axisTick: {
                show: false,
            },
            axisLine:{
                show: false
            },
        },
        yAxis: [{
            show: true,
            type: 'value',
            interval: 30000,
            nameGap: 0,
            nameTextStyle: {
                fontSize: 12,
                fontWeight: "bold",
                color: "#aaa"
            },
            splitLine: {
                lineStyle: {
                    color: '#EEE'
                },
                show: true,
                onZero: false
            }, 
           
            axisTick:{
                show: false
            },
            axisLine:{
               show: false,
               onZero: false
            },
            axisLabel: {
                show: true,
                textStyle:{
                    fontSize: 12,
                    fontWeight: "bold",
                    color: "#aaa"
                },
                formatter: (value, index) => index ? value : ""
            },
        }, {
            show: false,
            type: 'value',
            interval: 30000,
        }],
        series: [{ 
            name: '访客数',
            type: 'bar',
            barWidth:'10px',
            data: [],
            itemStyle:{
                normal: {
                       color: '#6200ea',
                }
            },   
        }, { 
            name: '订单量',
            type: 'bar',
            barWidth:'10px',
            yAxisIndex: 0,
            data: [],
            itemStyle:{
                normal: {
                 color: '#00a699',
                }
            }
        }],
  };
  let OptionRight = {
    tooltip : {
        trigger: 'item',
        formatter: function(params, ticket, cb){
          return `${params.name}: <br/> ${params.data.realvalue} (${params.data.percent})`
        }
    },

    calculable : true,
    series : [
        {
            name:'',
            type:'pie',
            radius : [20, 110],
            center : ['45%', '50%'],
            //roseType : 'radius',
            minAngle: 15, 
            data:[
                {value:10, name:'rose1'},
                {value:5, name:'rose2'},
                {value:15, name:'rose3'},
                {value:25, name:'rose4'},
                {value:20, name:'rose5'},
                {value:35, name:'rose6'},
                {value:30, name:'rose7'},
                {value:40, name:'rose8'}
            ]
        }
    ]
  };
  export default {
    data(){
      return {
       is_admin: false,
       left_chart: null,
       right_chart: null,

       left_loading: true,
       right_loading: true,
       down_loading: true,

       leftData: { visit: 0, average: 0, order: 0},
       left_time: '0',
       left_type: '0',

       right_command: 'tongji#zone',
       right_time   : '0',
       right_type:    '0',

       down_time: '0',
       down_type: '0',
       search: '',

       device_data: [],

       show_product: true,
       product_data: [],
       domain_data: [],
       option_right: OptionRight,
       option_left: OptionLeft,
        roundData: [
          {x: 3, y: 5, radius: 10},
          {x: 60, y: 80, radius: 10},
          {x: 90, y: 120, radius: 10},
        ],
        testData: [

        ]
      }
    }, 
    
    beforeDestroy () {
      this.node && document.body.removeChild(this.node);
    },
    mounted(){
        this.node = Object.assign(document.createElement('style'), {innerText: "body, html{ background: #F2F2F2;}"});
        document.body.appendChild(this.node);
        this.$service('indexData').then(res => {
          this.is_admin = res.body.admin.is_admin;
          if(this.is_admin){
            Vue.nextTick( _ => {
              (this.left_chart = window.echarts.init(document.getElementById('chart_left'))).setOption(this.option_left);
              (this.right_chart = window.echarts.init(document.getElementById('chart_right'))).setOption(this.option_right);
                this.get();
            });
          }
        
        
        });
      
     
    }, methods: {
      newCircle(){
        this.roundData.push({x: Math.random()*565, y: Math.random()*271, radius: 10})
      },
      get(){
        if(!this.is_admin) return;
        this.getLeftData();
        this.getRightData();
        this.getDownData();
      },

      getRightData(){
        if(!this.is_admin) return;
        this.right_loading = true;
        this.$service(this.right_command, this.right_time).then(res => {
          let cnt = res.body.map(x => +x.count).reduce((a, b) => a + b, 0);
          this.option_right.series[0].data = res.body.map(({country, count}) =>  ({value: +count, realvalue: +count, name: country, percent: this.$service('ratio', count, cnt)}));
          //this.option_right.legend.data = res.body.map(({country, count}) =>  country);
          this.right_chart.setOption(this.option_right);
          if(this.right_command == 'tongji#zone'){
            this.$service('tongji#device', this.right_time).then(res => {
              setTimeout(_ => this.right_loading = false, 0);
              let cnt = res.body.map(({count}) => +count).reduce((a, b) => a + b, 0);
              this.device_data = res.body.map(({os, count}) => ({os, count: (+count * 100 / cnt).toFixed(2) + "%"}));
            });
          }
        });
      },
      groupBy(array, fn, pre = []){
        let output = {};
        if(pre){
          pre.forEach(x => output[x] = []);
        }
        array.forEach( o => {
          let key = fn(o);
          output[key] = output[key] || [];
          output[key].push(o);
        });
        let arr = [];
        for(let key of Object.keys(output).sort()){
          arr.push([key, output[key]]);
        }
        return arr;
      },
      regroupByTime(array, timetype){
        var fn;
        let timevalue = str => Date.parse(`1970-01-01 ${str}Z`);
        let onehour   = 3600000,
            eighthour = onehour * 8,
            twohour   = onehour * 2;
        let pre;
        if(timetype != '0'){
          let start = (+timetype - 1)*eighthour;
          let end   = (+timetype)*eighthour;
          array = array.filter(x => timevalue(x) >= start && timevalue(x) <= end);
          pre = [start, start + twohour, start + twohour * 2, start + twohour * 3, end].map( (o) => ("0" + Math.floor(o / twohour)*2).slice(-2) + ":00")
        }else{
          pre = [0, eighthour, eighthour * 2, eighthour * 3].map((o) => ("0" + Math.floor(o / eighthour)*8).slice(-2) + ":00")
        }

        switch(timetype){
          case '0': fn = function(time){
            let o = Math.floor(time / eighthour);
            return ("0" + o*8).slice(-2) + ":00";
          }; break;
          default: fn = function(time){
            let o = Math.floor(time / twohour);
            return ("0" + o*2).slice(-2) + ":00";
          }; break;
        }
        return this.groupBy(array, o => fn(timevalue(o)), pre);
      },
      timeSeries(typeData, xaxis, yaxis){
        let output = this.regroupByTime(typeData.data, typeData.time_type);
        xaxis.data = output.map(x => x[0]);
        yaxis.data = output.map(x => x[1].length);
      },
      async getLeftData(){
        if(!this.is_admin) return;
        this.left_loading = true;
        this.leftData = (await this.$serviceAsync('tongji#leftBox', this.left_time, this.left_type)).body;
        this.leftData.ratio = this.$serviceAsync('ratio', this.leftData.order, this.leftData.visit);
        this.timeSeries(this.leftData.usertype,  this.option_left.xAxis, this.option_left.series[0]);
        this.timeSeries(this.leftData.ordertype, this.option_left.xAxis, this.option_left.series[1]);
        //this.option_left.legend.data = this.option_left.xAxis.data;
        this.left_chart.setOption(this.option_left);
        setTimeout(_ => this.left_loading = false, 0);
      },

      async getDownData(){
        if(!this.is_admin) return;
        this.down_loading = true;
        if(this.show_product){
            this.product_data = (await this.$serviceAsync('tongji#product', this.search, this.down_time, this.down_type)).body;
        }else{
            this.domain_data  = (await this.$serviceAsync('tongji#domain', this.search, this.down_time, this.down_type)).body;
        }
        setTimeout(_ => this.down_loading = false, 0);
      },
      handleRightChangeTime(val){
        this.right_time = val;
        this.getRightData();
      },
      doProduct(val){
        if(val != this.show_product){
          this.show_product = val;
          this.search = '';
        }
        this.getDownData();
      }
    }, 
      
    
  }
</script>