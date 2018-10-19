<template>
    <section v-loading='loading' element-loading-text="拼命加载中">
        <iframe id="iframe" name="iframe" ref="iframe" :src="url" frameborder="0" style="width:100%;height:667px;"></iframe>
    </section>
</template>


<script type="text/javascript">
    
export default {
    data() {
        return {
            loading: false
        }
    }
    , props: ['domain', 'identity_tag', 'reflesh']
    , computed: {
        url: function(){
            return `http://${this.domain}/${this.identity_tag}`;
        }
    }
    , methods: {
        onUpdate(){
            let self = this;
            if( this.loading == true ) return false;
            self.loading = true;
            setTimeout(()=>{
                let timestamp = new Date().getTime();
                let url = `http://${self.domain}/${self.identity_tag}?preview=1&timestamp=${timestamp}`;
                self.$refs.iframe.setAttribute('src', url);
            }, 2000);
            setTimeout(()=>{
                self.loading = false;
            }, 3000);
        }
    }
}

</script>