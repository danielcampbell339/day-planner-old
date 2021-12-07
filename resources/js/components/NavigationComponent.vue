<template>
  <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="#">Day Planner</a>
    <button
      class="navbar-toggler"
      type="button"
      data-toggle="collapse"
      data-target="#navbarNavAltMarkup"
      aria-controls="navbarNavAltMarkup"
      aria-expanded="false"
      aria-label="Toggle navigation"
    />
    <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
      <div class="navbar-nav">
        <router-link class="nav-item nav-link active" to="/"
          >Schedule</router-link
        >
        <router-link class="nav-item nav-link" to="/activities"
          >Activities</router-link
        >
        <router-link class="nav-item nav-link" to="/settings"
          >Settings</router-link
        >
      </div>
    </div>
  </nav>
</template>

<script>
export default {
  name: "NavigationComponent",
};
</script>
