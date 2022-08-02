<template>
  <yi-upload
    :accept="accept"
    style="display: inline-block"
    :action="action"
    @response="handleImport"
    :show-group="false"
  >
    <a-button type="danger">{{$lang(title)}}</a-button>
  </yi-upload>
</template>
<script>
export default {
  name: "yi-import",
  props: {
    action: {
      default: '',
      type: String
    },
    url: {
      default: '/system/admin/attachment/upload?type=private&record=0',
      type: String,
    },
    accept: {
      default: ".xlsx",
    },
    title: {
      default: 'Import'
    }
  },
  methods: {
    handleImport: function (res) {
      if (res.code == 1) {
        this.$emit('import-ok', res);
      } else {
        this.$emit('import-fail', res);
      }
      // if (res.code == 1) {
      //   const load = this.$message.loading(this.$lang('Loading'), 0);
      //   this.$http.post(this.action, { url: res.data }).then(data => {
      //     load();
      //     this.$emit('import-ok', data);
      //   }).catch(e => {
      //     load();
      //     this.$emit('import-fail', e);
      //   })
      // }
      // else {
      //   this.$emit("import-fail", res);
      // }
    },
  },
};
</script>