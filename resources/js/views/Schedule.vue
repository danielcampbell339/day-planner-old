<template>
  <div>
    <time-component ref="timeComponent" @createSchedule="createSchedule()" />
    <div v-if="schedule">
      <current-activity-component
        ref="currentActivityComponent"
        :currentActivity="currentActivity"
        :nextActivity="nextActivity"
      />
      <div class="card mt-3">
        <div class="card-header">Schedule</div>
        <div class="card-body">
          <schedule-component
            ref="scheduleComponent"
            :schedule="out"
            @finishFrequencyActivity="finishFrequencyActivity"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import TimeComponent from "../components/TimeComponent.vue";
import CurrentActivityComponent from "../components/CurrentActivityComponent.vue";
import ScheduleComponent from "../components/ScheduleComponent.vue";

export default {
  name: "Schedule",
  data() {
    return {
      schedule: null,
      out: null,
      currentActivity: null,
      nextActivity: null,
      cacheKey: null,
    };
  },
  components: { TimeComponent, CurrentActivityComponent, ScheduleComponent },
  async mounted() {
    this.getScheduleFromCache();
  },
  methods: {
    async getScheduleFromCache() {
      const today = moment().format("YYYY-MM-DD");
      const url = `api/cache/${today}`;
      const response = await axios.get(url);

      if (response.status != 200) {
        return;
      }

      this.schedule = response.data.schedule;
      this.cacheKey = response.data.cache_key;
      this.out = this.condenseSchedule((x) => x.schedule_id);
      this.checkCurrentActivity();
    },
    async createSchedule() {
      this.schedule = await this.getSchedule();
      this.out = this.condenseSchedule((x) => x.schedule_id);
      this.checkCurrentActivity();
    },
    async getSchedule() {
      const startTime = this.$refs.timeComponent.$data.startTime;
      const endTime = this.$refs.timeComponent.$data.endTime;

      const url = `api/schedule/${endTime}/${startTime}`;

      const response = await axios.get(url);
      this.cacheKey = response.data.cache_key;
      return response.data.schedule;
    },
    getCurrentActivity(exclude = []) {
      let currentTime = moment().format("H:mm");

      for (let s of this.schedule) {
        if (
          s.time === currentTime &&
          s.activity &&
          !exclude.includes(s.activity.name)
        ) {
          return Object.assign({}, s);
        }
      }

      return null;
    },
    checkCurrentActivity() {
      // Get the current time
      let newActivity = this.getCurrentActivity();

      if (this.currentActivity?.activity?.name != newActivity.activity.name) {
        let s = "alert.mp3";
        if (newActivity.activity.sound) {
          s = newActivity.activity.sound;
        }
        let dir = `storage/sounds/${s}`;
        const audio = new Audio(dir);
        audio.play();
      }

      this.currentActivity = newActivity;
      this.nextActivity = this.getNextActivity();

      setTimeout(this.checkCurrentActivity, 60000);
    },
    getNextActivity() {
      let currentActivityIndex = null;
      let nextActivity = null;

      for (let i = 0; i < this.schedule.length; i++) {
        if (this.schedule[i].schedule_id === this.currentActivity.schedule_id) {
          currentActivityIndex = i;
          break;
        }
      }

      for (let i = currentActivityIndex; i < this.schedule.length; i++) {
        if (this.schedule[i].schedule_id !== this.currentActivity.schedule_id) {
          nextActivity = this.schedule[i];
          break;
        }
      }

      return nextActivity;
    },
    condenseSchedule(key, val) {
      let seen = new Set();
      return this.schedule.filter((item) => {
        let k = key(item);
        return seen.has(k) ? false : seen.add(k);
      });
    },
    async finishFrequencyActivity(activity) {
      // Update the date in the db
      let updatedSchedule = await axios.post(
        `api/frequency/${activity.frequency}`,
        {
          schedule: this.schedule,
          cache_key: this.cacheKey,
        }
      );

      this.schedule = updatedSchedule.data.schedule;
    },
  },
  watch: {
    schedule(val) {
      this.out = this.condenseSchedule((x) => x.schedule_id);
    },
  },
};
</script>
