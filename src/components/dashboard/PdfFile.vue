<script setup>
import { ref } from "vue";

defineProps({
  file_url: String,
});
</script>

<template>
  <div>
    <a :href="'http://' + file_url" target="_blank" rel="noopener noreferrer">{{
      file_url
    }}</a>
  </div>
</template>
