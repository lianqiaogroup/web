<template>
  <div id='sites-search' class='sites'>
    <div class="header-panel inversed">
            <div class="control">
                <div class="operations">
                       
                </div>
                <el-row class="heading" >
                    <el-col>
                          输入要搜索的主页域名  
                    </el-col>    
                </el-row>
                <div class='op-box'>
                    <el-input
                        placeholder="输入关键词"
                        icon="search"
                        v-model="keyword"
                        :on-icon-click="handleSearch">
                    </el-input>
                    </div>
                    
            </div>            
    </div>
    <div class="header-panel-addition inversed"></div>
    <div class='content inversed'>
        <div class="heading">
            <div class="clear-history">清空搜索历史</div>
            <div class="title">搜索记录</div>
        </div>
        <div class="listing">
            <ul>
                <li v-for='item in history'><span class='list-time'>{{item.time}}</span><span class='list-domain'>{{item.domain}}</span></li>
            </ul>
        </div>
    </div>
  </div>
</template>

<script>
    export default {
        data(){
            return {
                history: [{
                    time: '06-02 15:30', domain: 'www.google.com.cn'
                }, {
                    time: '06-02 15:30', domain: 'www.google.com.cn'
                }]
            }
        }
    }
</script>