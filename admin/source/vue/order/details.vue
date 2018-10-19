<template>
<div class="order_detail">
  <el-row :gutter="20">
    <el-col :span="8">
      <div class="grid-content">
        <h6>订单来源</h6>
        <div class="orderfrom">
          <el-row>
            <el-col :span="8" style="width:130px"><div class="grid-left">域名</div></el-col>
            <el-col :span="16"><div class="grid-right">{{web_url}}</div></el-col>
          </el-row>
          <el-row>
            <el-col :span="8" style="width:130px"><div class="grid-left">优化师</div></el-col>
            <el-col :span="16"><div class="grid-right">{{name_cn}}</div></el-col>
          </el-row>
          <el-row>
            <el-col :span="8" style="width:130px"><div class="grid-left">来源</div></el-col>
            <el-col :span="16"><div class="grid-right">{{http_referer}}</div></el-col>
          </el-row>
          <el-row>
            <el-col :span="8" style="width:130px"><div class="grid-left">来源地区</div></el-col>
            <el-col :span="16"><div class="grid-right">{{ip}}</div></el-col>
          </el-row>
          <el-row>
            <el-col :span="8" style="width:130px"><div class="grid-left">浏览器</div></el-col>
            <el-col :span="16"><div class="grid-right">{{user_agent}}</div></el-col>
          </el-row>
          <el-row>
            <el-col :span="8" style="width:130px"><div class="grid-left">终端机</div></el-col>
            <el-col :span="16"><div class="grid-right">{{device}}</div></el-col>
          </el-row>
        </div>
      </div>
      <div class="grid-content">
        <h6>收件人信息</h6>
        <div class="users">
          <el-row>
            <el-col :span="8" style="width:130px"><div class="grid-left">国家</div></el-col>
            <el-col :span="16"><div class="grid-right">{{country}}</div></el-col>
          </el-row>
          <el-row>
            <el-col :span="8" style="width:130px"><div class="grid-left">省市区详细地址</div></el-col>
            <el-col :span="16"><div class="grid-right">{{address}}</div></el-col>
          </el-row>
          <el-row>
            <el-col :span="8" style="width:130px"><div class="grid-left">收件人姓名</div></el-col>
            <el-col :span="16"><div class="grid-right">{{name}}</div></el-col>
          </el-row>
          <el-row>
            <el-col :span="8" style="width:130px"><div class="grid-left">电话</div></el-col>
            <el-col :span="16"><div class="grid-right">{{mobile}}</div></el-col>
          </el-row>
          <el-row>
            <el-col :span="8" style="width:130px"><div class="grid-left">备用电话</div></el-col>
            <el-col :span="16"><div class="grid-right">{{mob_backup}}</div></el-col>
          </el-row>
          <el-row>
            <el-col :span="8" style="width:130px"><div class="grid-left">邮箱</div></el-col>
            <el-col :span="16"><div class="grid-right">{{email}}</div></el-col>
          </el-row>
          <el-row>
            <el-col :span="8" style="width:130px"><div class="grid-left">邮编</div></el-col>
            <el-col :span="16"><div class="grid-right">{{zipcode}}</div></el-col>
          </el-row>
          <el-row>
            <el-col :span="8" style="width:130px"><div class="grid-left">留言</div></el-col>
            <el-col :span="16"><div class="grid-right">{{remark}}</div></el-col>
          </el-row>
        </div>
      </div>
    </el-col>
    <el-col :span="16">
      <div class="grid-content">
        <h6>订单信息</h6>
        <div class="orderno">
          <el-row>
            <el-col :span="5" style="width:130px"><div class="grid-left">订单号</div></el-col>
            <el-col :span="7"><div class="grid-right">{{erp_no}}</div></el-col>
            <el-col :span="5" style="width:130px"><div class="grid-left">下单时间</div></el-col>
            <el-col :span="7"><div class="grid-right">{{add_time}}</div></el-col>
          </el-row>
          <el-row>
            <el-col :span="5" style="width:130px"><div class="grid-left">订单ERP状态</div></el-col>
            <el-col :span="7"><div class="grid-right">{{msg_erp_status}}</div></el-col>
            <el-col :span="5" style="width:130px"><div class="grid-left">发货时间</div></el-col>
            <el-col :span="7"><div class="grid-right">{{date_delivery}}</div></el-col>
          </el-row>
          <el-row>
            <el-col :span="5" style="width:130px"><div class="grid-left">物流方式</div></el-col>
            <el-col :span="7"><div class="grid-right">{{shipping_name}}</div></el-col>
            <el-col :span="5" style="width:130px"><div class="grid-left">物流单号</div></el-col>
            <el-col :span="7"><div class="grid-right">{{track_number}}</div></el-col>
          </el-row>
        </div>
      </div>
      <div class="grid-content">
        <h6>商品信息</h6>
        <div class="protatle">
          <el-table
            :data="Goodsdata"
            border
            style="width: 100%">
            <el-table-column
              prop="product_id"
              label="产品id">
            </el-table-column>
            <el-table-column
              prop="erp_id"
              label="ERP_ID">
            </el-table-column>
            <el-table-column
              prop="title"
              label="产品名称">
            </el-table-column>
            <el-table-column
              prop="attr"
              label="产品属性">
              <template scope="scope">
                <span v-for="item in scope.row.attr">{{item.name}} </span>
              </template>
            </el-table-column>
            <el-table-column
              prop="promotion_price"
              label="单价">
              <template scope="scope">
                <span>{{currency_code}}</span><span>{{scope.row.promotion_price}}</span>
              </template>
            </el-table-column>
            <el-table-column
              prop="num"
              label="数量">
            </el-table-column>
            <el-table-column
              prop="total"
              label="总价">
              <template scope="scope">
                <span>{{currency_code}}</span><span>{{scope.row.total}}</span>
              </template>
            </el-table-column>
          </el-table>
        </div>
        <div class="payment_amount">
          <el-row>
            <el-col :span="8" style="width:130px"><div class="grid-left">订单总价</div></el-col>
            <el-col :span="16"><div class="grid-right"><span>{{currency_code}}</span><span>{{payment_amount}}</span></div></el-col>
          </el-row>
        </div>
      </div>
    </el-col>
  </el-row>
</div>
</template>

<script>
  Vue.http.options.headers = {
    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
  };
  Vue.http.options.emulateJSON = true;
  export default {
    data() {
      return {
        Goodsdata: [],
        web_url:"",
        name_cn:"",
        http_referer:"",
        user_agent:"",
        device:"",
        country:"",
        address:"",
        name:"",
        mobile:"",
        mob_backup:"",
        email:"",
        erp_no:"",
        ip:"",
        date_delivery:"",
        add_time:"",
        msg_erp_status:"",
        shipping_name:"",
        track_number:"",
        zipcode:"",
        remark:"",
        currency_code:"",
        payment_amount:""
      }
    },
    mounted(){
      if(this.$route.params.id){
        this.postdata();
      }

    },
    methods:{
      postdata(){
        var parem = {
          'order_id':this.$route.params.id
        }
        this.$http.post('/order.php?act=info',parem).then(function(result){
          var datas = result.body;
          var data_from = result.body.data.post_erp_data;//订单来源
          var data_erp_status = result.body.erp_status;//erp状态
          this.web_url = data_from.web_url;
          this.name_cn = datas.data.user.name_cn;
          this.http_referer = data_from.http_referer;
          this.ip = datas.data.ip;
          this.user_agent = data_from.user_agent;
          this.device = data_from.web_info.device;
          this.date_delivery = data_erp_status.date_delivery;
          this.country = data_from.country;
          this.address = datas.data.address;
          this.name = datas.data.name;
          this.mobile = datas.data.mobile;
          this.mob_backup = data_from.web_info.mob_backup;
          this.email = datas.data.email;
          this.add_time = datas.data.add_time;
          this.erp_no = datas.data.erp_no;
          this.msg_erp_status = data_erp_status.msg_erp_status;
          this.shipping_name = data_erp_status.shipping_name;
          this.track_number = data_erp_status.track_number;
          this.remark = data_from.remark;
          this.zipcode = data_from.zipcode;
          this.Goodsdata = datas.goods;
          this.currency_code = data_from.currency_code;
          this.payment_amount = parseFloat(datas.data.payment_amount/100);
        })
      }
    }
  }
</script>