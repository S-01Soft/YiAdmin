
<template>
  <component
    :is="type"
    v-bind="{ ...$props, ...$attrs }"
    v-on="$listeners"
  ></component>
</template>
<script>
export default {
  name: "yi-editor",
  props: {
    type: {
      default: Config.admin.editor || "yi-textarea",
      type: String,
    },
  },
};
</script>