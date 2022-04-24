<template>
  <yi-upload
    :accept="accept"
    style="display: inline-block"
    :action="action"
    @response="handleImport"
    :show-group="false"
  >
    <a-button type="danger">{{$lang('Import')}}</a-button>
  </yi-upload>
</template>
<script>
export default {
  name: "yi-import",
  props: {
    action: {
      type: String,
    },
    accept: {
      default: ".xlsx",
    },
  },
  methods: {
    handleImport: function (res) {
      if (res.code == 1) this.$emit("import-ok", res);
      else {
        //this.$message.error(res.message);
        this.$emit("import-fail", res);
      }
    },
  },
};
</script>