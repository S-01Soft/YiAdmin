

<template id="yi-kv-str">
  <div class="yi-kv-str">
    <yi-kv v-model="val"></yi-kv>
  </div>
</template>
<script>
export default {
  name: "yi-kv-str",
  props: {
    value: {
      default: "",
    },
  },
  data: function () {
    return {
      val: {},
    };
  },
  watch: {
    value: function (v) {
      this.val = this.parse(v);
    },
    val: {
      deep: true,
      handler: function (v) {
        this.$emit("input", JSON.stringify(v));
      },
    },
  },
  mounted: function () {
    this.val = this.parse(this.value);
  },
  methods: {
    parse: function (val) {
      return Yi.isEmpty(val) ? {} : JSON.parse(val);
    },
  },
};
</script>