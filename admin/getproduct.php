<?php

require_once 'ini.php';
$model = new admin\helper\getproduct($register);

$id = $_GET['p'];
$from = '';
$to = '';
if(isset($_GET['from']))
    $from = $_GET['from'];
if(isset($_GET['to']))
    $to = $_GET['to'];
switch($id){
    case 1:
        $c = '0';
        if(isset($_GET['c']))
            $c = strtoupper($_GET['c']);
        $l = '50';
        if(isset($_GET['l']))
            $l = $_GET['l'];
        $data = $model->gettotal($from, $to, $c,$l);
        echo "从<input type='text' name='from' id='from' placeholder='开始时间'>到<input type='text' name='to' id='to' placeholder='结束时间'><input type='button' name='search' id='search' value='搜索'>";
        echo "<table style='border: dotted;border-width: 1;'>";
        echo "<tr><th width='5%'>pid</th><th width='5%'>eid</th><th width='5%'>订单数</th><th width='18%'>网站</th><th width='17%'>建站时间</th><th width='40%'>产品名</th><th width='5%'>价格</th><th width='5%'>操作</th></tr>";
        foreach($data as $item){
            echo "<tr>";
            echo "<td>" . $item['product_id']. "</td>";
            echo "<td>" . $item['erp_number']. "</td>";
            echo "<td>" . $item['c']. "</td>";
            echo "<td><a target='_blank' href='http://" . $item['domain'] . "'>" . $item['domain']. "</a></td>";
            echo "<td>" . $item['ctime']. "</td>";
            echo "<td>" . $item['title']. "</td>";
            echo "<td>" . $item['price']. "</td>";
            echo "<td><a target='_blank' href='http://admin.stosz.com/getproduct.php?p=2&id=" . $item['product_id']. "'>详情</a></td>";
            echo "</tr>";
        }
        echo '</table>';
        break;
    case 2:
        $data = $model->getmonth($_GET['id']);
        echo $data[0]['domain'].'|'.$data[0]['title'].'|'.$data[0]['price'].'<br>';
        $dm = array();
        $cm = array();

        foreach($data as $item){
            $dm[] = '"' . $item['dayon'] . '"';
            $cm[] = '"' . $item['c'] . '"';
        }

        $dm = implode(',', $dm);
        $cm = implode(',', $cm);

        ?>
        <script type="text/javascript" src="http://www.ichartjs.com/ichart.latest.min.js"></script>

        <div id='canvasDiv'></div>
        <script>
            $(function(){
                var flow=[<?=$cm?>];

                var data = [
                    {
                        name : 'PV',
                        value:flow,
                        color:'#ec4646',
                        line_width:2
                    }
                ];

                var labels = [<?=$dm?>];

                var chart = new iChart.LineBasic2D({
                    render : 'canvasDiv',
                    data: data,
                    align:'center',
                    title : {
                        text:'日期订单量',
                        font : '微软雅黑',
                        fontsize:24,
                        color:'#b4b4b4'
                    },
                    subtitle : {
                        text:'订单量达到最大值',
                        font : '微软雅黑',
                        color:'#b4b4b4'
                    },
                    footnote : {
                        text:'table',
                        font : '微软雅黑',
                        fontsize:11,
                        fontweight:600,
                        padding:'0 28',
                        color:'#b4b4b4'
                    },
                    width : 800,
                    height : 400,
                    shadow:true,
                    shadow_color : '#202020',
                    shadow_blur : 8,
                    shadow_offsetx : 0,
                    shadow_offsety : 0,
                    background_color:'#2e2e2e',
                    tip:{
                        enable:true,
                        shadow:true,
                        listeners:{
                            //tip:提示框对象、name:数据名称、value:数据值、text:当前文本、i:数据点的索引
                            parseText:function(tip,name,value,text,i){
                                return "<span style='color:#005268;font-size:12px;'>"+labels[i]+"的订单量约:<br/>"+
                                    "</span><span style='color:#005268;font-size:20px;'>"+value+"单</span>";
                            }
                        }
                    },
                    crosshair:{
                        enable:true,
                        line_color:'#ec4646'
                    },
                    sub_option : {
                        smooth : true,
                        label:false,
                        hollow:false,
                        hollow_inside:false,
                        point_size:8
                    },
                    coordinate:{
                        width:640,
                        height:260,
                        striped_factor : 0.18,
                        grid_color:'#4e4e4e',
                        axis:{
                            color:'#252525',
                            width:[0,0,4,4]
                        },
                        scale:[{
                            position:'left',
                            start_scale:0,
                            end_scale:100,
                            scale_space:30,
                            scale_size:2,
                            scale_enable : false,
                            label : {color:'#9d987a',font : '微软雅黑',fontsize:11,fontweight:600},
                            scale_color:'#9f9f9f'
                        },{
                            position:'bottom',
                            label : {color:'#9d987a',font : '微软雅黑',fontsize:11,fontweight:600},
                            scale_enable : false,
                            labels:labels
                        }]
                    }
                });
                //利用自定义组件构造左侧说明文本
                chart.plugin(new iChart.Custom({
                    drawFn:function(){
                        //计算位置
                        var coo = chart.getCoordinate(),
                            x = coo.get('originx'),
                            y = coo.get('originy'),
                            w = coo.width,
                            h = coo.height;
                        //在左上侧的位置，渲染一个单位的文字
                        chart.target.textAlign('start')
                            .textBaseline('bottom')
                            .textFont('600 11px 微软雅黑')
                            .fillText('订单量',x-40,y-12,false,'#9d987a')
                            .textBaseline('top')
                            .fillText('(时间)',x+w+12,y+h+10,false,'#9d987a');

                    }
                }));
                //开始画图
                chart.draw();
            });



        </script>
<?php

        echo "<table border='1'>";
        echo "<tr><th>日期</th>";
        foreach($data as $item){
            echo "<td>" . $item['dayon']. "</td>";
        }
        echo "</tr>";
        echo "<tr><th>数据</th>";
        foreach($data as $item){
            echo "<td>" . $item['c']. "</td>";
        }
        echo "</tr>";
        echo '</table>';

        break;
    case 3:
        $t = 'day';
        $num = 30;
        if(isset($_GET['t'])){
            if($_GET['t'] == 'month'){
                $t = 'month';
            }
        }
        if(isset($_GET['num'])){
            $num = $_GET['num'];
        }
        $data = $model->product_num($t,$num);
        echo '产品上新数<br>';
        $dm = array();
        $cm = array();

        foreach($data as $item){
            $dm[] = '"' . $item['dayon'] . '"';
            $cm[] = '"' . $item['c'] . '"';
        }

        $dm = implode(',', $dm);
        $cm = implode(',', $cm);
?>

        <script type="text/javascript" src="http://www.ichartjs.com/ichart.latest.min.js"></script>

        <div id='canvasDiv'></div>
        <script>
            $(function(){
                var flow=[<?=$cm?>];

                var data = [
                    {
                        name : '产品数',
                        value:flow,
                        color:'#ec4646',
                        line_width:2
                    }
                ];

                var labels = [<?=$dm?>];

                var chart = new iChart.LineBasic2D({
                    render : 'canvasDiv',
                    data: data,
                    align:'center',
                    title : {
                        text:'产品数',
                        font : '微软雅黑',
                        fontsize:24,
                        color:'#b4b4b4'
                    },
                    subtitle : {
                        text:'产品数达到最大值',
                        font : '微软雅黑',
                        color:'#b4b4b4'
                    },
                    footnote : {
                        text:'table',
                        font : '微软雅黑',
                        fontsize:11,
                        fontweight:600,
                        padding:'0 28',
                        color:'#b4b4b4'
                    },
                    width : 800,
                    height : 400,
                    shadow:true,
                    shadow_color : '#202020',
                    shadow_blur : 8,
                    shadow_offsetx : 0,
                    shadow_offsety : 0,
                    background_color:'#2e2e2e',
                    tip:{
                        enable:true,
                        shadow:true,
                        listeners:{
                            //tip:提示框对象、name:数据名称、value:数据值、text:当前文本、i:数据点的索引
                            parseText:function(tip,name,value,text,i){
                                return "<span style='color:#005268;font-size:12px;'>"+labels[i]+"的产品数约:<br/>"+
                                    "</span><span style='color:#005268;font-size:20px;'>"+value+"个</span>";
                            }
                        }
                    },
                    crosshair:{
                        enable:true,
                        line_color:'#ec4646'
                    },
                    sub_option : {
                        smooth : true,
                        label:false,
                        hollow:false,
                        hollow_inside:false,
                        point_size:8
                    },
                    coordinate:{
                        width:640,
                        height:260,
                        striped_factor : 0.18,
                        grid_color:'#4e4e4e',
                        axis:{
                            color:'#252525',
                            width:[0,0,4,4]
                        },
                        scale:[{
                            position:'left',
                            start_scale:0,
                            end_scale:100,
                            scale_space:30,
                            scale_size:2,
                            scale_enable : false,
                            label : {color:'#9d987a',font : '微软雅黑',fontsize:11,fontweight:600},
                            scale_color:'#9f9f9f'
                        },{
                            position:'bottom',
                            label : {color:'#9d987a',font : '微软雅黑',fontsize:11,fontweight:600},
                            scale_enable : false,
                            labels:labels
                        }]
                    }
                });
                //利用自定义组件构造左侧说明文本
                chart.plugin(new iChart.Custom({
                    drawFn:function(){
                        //计算位置
                        var coo = chart.getCoordinate(),
                            x = coo.get('originx'),
                            y = coo.get('originy'),
                            w = coo.width,
                            h = coo.height;
                        //在左上侧的位置，渲染一个单位的文字
                        chart.target.textAlign('start')
                            .textBaseline('bottom')
                            .textFont('600 11px 微软雅黑')
                            .fillText('产品数',x-40,y-12,false,'#9d987a')
                            .textBaseline('top')
                            .fillText('(时间)',x+w+12,y+h+10,false,'#9d987a');

                    }
                }));
                //开始画图
                chart.draw();
            });



        </script>

<?php
        echo "<table border='1'>";
        echo "<tr><th>日期</th>";
        foreach($data as $item){
            echo "<td>" . $item['dayon']. "</td>";
        }
        echo "</tr>";
        echo "<tr><th>数据</th>";
        foreach($data as $item){
            echo "<td>" . $item['c']. "</td>";
        }
        echo "</tr>";
        echo '</table>';
        break;
    case 4:
        $data = $model->product_chart();
        echo "产品id,添加时间,域名,目录,当天订单数,最后下单时间<br>";
        foreach($data as $item){
            echo $item['product_id']. ",".$item['add_time']. ",".$item['domain']. ",".$item['identity_tag']. ",".$item['order_count']. ",".$item['last_date']."<br>";
        }
        break;
    case 5:
        $data = $model->product_chart2();
        echo "域名,添加时间,当天订单数,二级目录数,最后下单时间<br>";
        foreach($data as $item){
            echo $item['d']. ",".$item['addtime']. ",".$item['order_count']. ",".$item['c']. ",".$item['lasttime']."<br>";
        }
        break;

    case 6: // top 50 ads, top 20 product
        $data = $model->top50ads();
        echo "ERP编号,域名,产品名,订单数<br>";
        foreach($data as $item){
            echo "<br>----------------广告专员姓名" . $item['name'] . " 编号" . $item['uid']."------------------<br>";
            foreach($item['data'] as $da){
                $title = str_replace(",","",$da['title']);
                $title = str_replace("?","",$title);
                echo $da['erp_number'] . "," . $da['domain'] . "/" . $da['identity_tag'] . "," . $title . "," . $da['c'] . "<br>";
            }
        }

        break;

    default:

        break;
}

