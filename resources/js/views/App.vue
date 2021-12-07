<template>
  <div>
    <navigation-component></navigation-component>
    <div class="container-fluid mt-3 ml-3">
      <router-view />
    </div>
  </div>
</template>

<script>
import NavigationComponent from "../components/NavigationComponent.vue";

export default {
  components: { NavigationComponent },
  name: "App",
};
</script>
