<template>
  <div class="card">
    <div class="card-body">
      <div class="form-group mb-3">
        <label>Start Time</label>
        <input v-model="startTime" type="time" class="form-control" />
      </div>
      <div class="form-group mb-3">
        <label>End Time</label>
        <input v-model="endTime" type="time" class="form-control" />
      </div>
      <button class="btn btn-primary" @click="createSchedule()">
        Generate Schedule
      </button>
      &nbsp;
      <button class="btn btn-primary" @click="finishActivity()">
        Finish Activity
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: "TimeComponent",
  data() {
    return {
      startTime: null,
      endTime: null,
    };
  },
  mounted() {
    this.startTime = moment().format("HH:mm");
  },
  methods: {
    createSchedule() {
      this.$emit("createSchedule");
    },
    finishActivity() {
      this.$emit("finishActivity");
    },
  },
};
</script>
