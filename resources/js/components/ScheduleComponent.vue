<template>
  <table class="table table-striped table-hover">
    <thead>
      <tr>
        <th class="col" v-for="(header, index) of tableHeaders" :key="index">
          <span v-if="header.show">{{ header.name }}</span>
        </th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="(s, index) of schedule" :key="index">
        <td>
          <input
            type="checkbox"
            v-model="activitiesToFinish"
            :value="JSON.stringify(s)"
          />
        </td>
        <td>{{ s.time }}</td>
        <td>{{ s.activity.name }}</td>
        <td>
          <timer-component v-if="s.activity.minutes" :activity="s.activity" />
        </td>
        <td>
          <span v-if="s.activity.frequency">
            <input
              type="button"
              class="btn btn-primary"
              value="Complete Activity"
              @click="finishFrequencyActivity(s.activity)"
            />
          </span>
        </td>
        <td>
          <span v-if="s.activity.commands > 0">
            <input
              type="button"
              class="btn btn-primary"
              value="Run Commands"
              @click="runCommands(s.activity.commands)"
            />
          </span>
        </td>
      </tr>
    </tbody>
  </table>
</template>

<script>
import TimerComponent from "./TimerComponent.vue";

export default {
  name: "ScheduleComponent",
  components: {
    TimerComponent,
  },
  props: {
    schedule: {
      type: Array,
    },
  },
  data() {
    return {
      tableHeaders: [
        {
          name: "multi-select",
          show: false,
        },
        {
          name: "Time",
          show: true,
        },
        {
          name: "Name",
          show: true,
        },
        {
          name: "timer",
          show: false,
        },
        {
          name: "finish-freq",
          show: false,
        },
        {
          name: "run-command",
          show: false,
        },
      ],
      activitiesToFinish: [],
    };
  },
  methods: {
    runCommands(commands) {
      for (let command of commands) {
        window.open(command.name, "_blank");
      }
    },
    finishFrequencyActivity(activity) {
      this.$emit("finishFrequencyActivity", activity);
    },
  },
};
</script>
