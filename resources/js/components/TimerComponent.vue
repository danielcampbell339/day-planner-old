<template>
  <div>
    {{ getTime }}
    <input
      type="button"
      class="btn btn-primary"
      :value="getButtonType"
      @click="toggle()"
    />
  </div>
</template>

<script>
export default {
  name: "TimerComponent",
  props: {
    activity: Object,
  },
  data() {
    return {
      paused: true,
      current: this.activity.minutes * 60,
      time: "",
    };
  },
  mounted() {
    setInterval(
      function () {
        if (!this.paused) {
          --this.current;
        }

        if (this.current <= 0) {
          clearInterval();
        }
      }.bind(this),
      1000
    );
  },
  computed: {
    getTime() {
      // Hours, minutes and seconds
      let hrs = ~~(this.current / 3600);
      let mins = ~~((this.current % 3600) / 60);
      let secs = ~~this.current % 60;

      hrs = this.formatTimer(hrs);
      mins = this.formatTimer(mins);
      secs = this.formatTimer(secs);

      return `${hrs}:${mins}:${secs}`;
    },
    getButtonType() {
      return this.paused ? "Start" : "Pause";
    },
  },
  watch: {
    start(val) {
      this.paused = true;
      this.current = val * 60;
    },
    current() {
      if (this.current <= 0) {
        this.paused = true;
        let dir = `storage/sounds/timer.mp3`;
        const audio = new Audio(dir);
        audio.play();
      }
    },
  },
  methods: {
    formatTimer(time) {
      if (time == 0) {
        time = "00";
      }

      if (time > 0 && time < 10) {
        time = "0" + time;
      }

      return time;
    },
    toggle() {
      this.paused = !this.paused;
    },
  },
};
</script>
