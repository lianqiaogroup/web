<template>
  <div class="chartsPie">
      <div class="pie-charts">
          <div class="pie-charts-l" style="width: 560px;height: 360px;"></div>
      </div>
      <div class="pie-charts">
          <div class="pie-charts-r" style="width: 560px;height: 360px;"></div>
      </div>
  </div>
</template>

<script>
export default {
    data(){
        return {
          
        }
    },
    mounted(){
      this.initChartLeft();
      this.initChartRight();
    },
    methods: {
        initChartLeft(){
            // console.log(echarts);
            let myChartLeft = echarts.init(document.querySelector('.pie-charts-l'));
            myChartLeft.setOption({
                title: {
                    text: '模版使用量',
                    // subtext: '纯属虚构',
                    x: 'center'
                },
                tooltip: {
                    trigger: 'item',
                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                },
                legend: {
                    orient: 'vertical',
                    left: 'left',
                    data: ['style22' , 'style27_2' , 'style32_2' , 'style57' , 'style64' , 'style69' , 'style6_2' , 'style73' , 'style79' , 'style85' ]
                },
                series: [
                    {
                        name: '访问来源',
                        type: 'pie',
                        radius: '55%',
                        center: ['50%', '60%'],
                        data: [
                            {value: 3113, name: 'style22'},
                            {value: 1943, name: 'style27_2'},
                            {value: 7479, name: 'style32_2'},
                            {value: 2648, name: 'style57'},
                            {value: 8064, name: 'style64'},
                            {value: 1784, name: 'style69'},
                            {value: 2078, name: 'style6_2'},
                            {value: 1982, name: 'style73'},
                            {value: 1814, name: 'style79'},
                            {value: 3490, name: 'style85'}
                        ],
                        itemStyle: {
                            emphasis: {
                                shadowBlur: 10,
                                shadowOffsetX: 0,
                                shadowColor: 'rgba(0, 0, 0, 0.5)'
                            }
                        }
                    }
                ]
            }, true);
        },
        initChartRight(){
            // console.log(echarts);
            let myChartRight = echarts.init(document.querySelector('.pie-charts-r'));
            myChartRight.setOption({
                title: {
                    text: '模版下单量',
                    // subtext: '纯属虚构',
                    x: 'center'
                },
                tooltip: {
                    trigger: 'item',
                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                },
                legend: {
                    orient: 'vertical',
                    left: 'left',
                    data: ['style22' , 'style32_2' , 'style39' , 'style64' , 'style79' , 'style82' , 'style85' , 'style88' , 'style90' , 'style91' ]
                },
                series: [
                    {
                        name: '访问来源',
                        type: 'pie',
                        radius: '55%',
                        center: ['50%', '60%'],
                        data: [
                            {value: 5132, name: 'style22'},
                            {value: 11919, name: 'style32_2'},
                            {value: 6709, name: 'style39'},
                            {value: 18414, name: 'style64'},
                            {value: 2704, name: 'style79'},
                            {value: 2379, name: 'style82'},
                            {value: 6150, name: 'style85'},
                            {value: 8352, name: 'style88'},
                            {value: 2874, name: 'style90'},
                            {value: 5745, name: 'style91'}        
                        ],
                        itemStyle: {
                            emphasis: {
                                shadowBlur: 10,
                                shadowOffsetX: 0,
                                shadowColor: 'rgba(0, 0, 0, 0.5)'
                            }
                        }
                    }
                ]
            }, true);
        }
    }
}
</script>

