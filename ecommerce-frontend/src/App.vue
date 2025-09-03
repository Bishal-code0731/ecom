<template>
  <div id="app">
    <AppNavbar />

    <!-- Main page content -->
    <div class="container mt-4">
      <router-view />
    </div>
  </div>
</template>

<script>
import AppNavbar from './components/AppNavbar.vue'

export default {
  name: 'App',
  components: {
    AppNavbar
  }
}
</script>
