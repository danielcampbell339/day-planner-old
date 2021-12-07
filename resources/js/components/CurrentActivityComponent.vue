<template>
  <div class="row">
    <div class="col">
      <div class="card mt-3">
        <div class="card-header">Current Activity</div>
        <div class="card-body">
          <h2>{{ currentActivity.activity.name }}</h2>
        </div>
      </div>
    </div>
    <div class="col">
      <div class="card mt-3">
        <div class="card-header">Next Activity</div>
        <div class="card-body">
          <h2>{{ nextActivity.activity.name }}</h2>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "CurrentActivityComponent",
  props: {
    currentActivity: Object,
    nextActivity: Object,
  },
};
</script>
