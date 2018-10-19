<template>
    <component v-if='domain' is='show'></component>
    <component v-else='domain' is='search'></component>
</template>

<script>
  import show     from './show.vue'
  import search   from './search.vue'
  import settings from './edit/settings.vue'
  export default {
      data(){
          return {
              domain: "www.abc.com",
          }
      },
      components: {
         show, 
         search, 
         settings
      }
  }
</script>