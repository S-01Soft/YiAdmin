
<template>
  <component
    :is="type"
    v-bind="{ ...$props, ...$attrs }"
    v-on="$listeners"
  ></component>
</template>
<script>
export default {
  name: "yi-markdown",
  props: {
    type: {
      default: Config.admin.markdown || "yi-textarea",
      type: String,
    },
  },
};
</script>