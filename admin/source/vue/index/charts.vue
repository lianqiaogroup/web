<template>
 <div class="charts"><div class='chart' id='charts' ref='charts'></div></div>
</template>

<script>

export default {
  data(){
   return {
    option: {
        color: ["rgb(98, 0, 234)"],
        backgroundColor: {
          color: "#F6F6F6"
        },
        title: {
            text: '模板下单百分比统计',
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
            y: '110px',
            height: '97px',
        }],
        tooltip: {
            'trigger': 'axis',
             formatter([visit, order, percent], ticket, cb){
              let ret = "<div style='padding-right: 0px;'>";
                  ret += `style${visit.axisValue} ${visit.marker} ${visit.seriesName} ${visit.value} %`;
                  ret += "</div>"
              return ret
            },
            extraCssText: 
            `border-radius: 4px; 
             background: rgba(0,0,0,0.8);
             font-size: 12px;
             font-weight: bold;
             color: white;
             height: 36px;
             line-height: 36px;
             padding-right: 10px;
             padding-left: 10px;
            `
        },
        xAxis: {
            data: [2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,22,23,24,25,26,27,28,29,30],
            axisTick: {
                show: false,
            },
            axisLine:{
                show: false
            },
            splitLine:{
             
            },
            minInterval: 0,
            axisLabel:{
                show: true,
                textStyle: {
                    fontSize: 12,
                    fontWeight: "bold",
                    color: "#aaa",
                },  
                margin: 18,
                interval: 0
            }
        },
        yAxis: [{
            show: true,
            type: 'value',
            interval: 30000,
            name: 'Style',
            nameLocation: 'start',
            nameGap: 18,
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
            name: '下单比例',
            type: 'bar',
            barWidth:'10px',
            stack: 'Style',
            data: [],
            itemStyle:{
                normal: {
                       color: '#6200ea',
                }
            },
            
        }]
    }
   } 
  }, 
  methods: {
     updateChart(){
       this.chart.setOption(this.option); 
       this.interval = this.interval || setInterval(function(){
           if(!document.querySelector(".bk") && document.querySelector(".chart")){
               let bk = document.createElement("div");
               bk.classList.add("bk");
               document.querySelector(".chart").firstChild.appendChild(bk);
            }
       }, 100);
     },
     setParams(names, view){
        names  && (this.option.xAxis.data  = names.map(_ => _.replace("style", "")));
        view   && (this.option.series[0].data = view);
        this.chart.setOption(this.option); 
     }
   },
  beforeUpdate(){
     this.updateChart();
  },
  mounted(){
    this.chart = echarts.init(document.getElementById('charts'));
    this.updateChart();
    
  }
   
}
</script>