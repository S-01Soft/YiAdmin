<template>
  <div class="yi-date-picker">
    <a-date-picker
      value-format="X"
      :show-time="showTime"
      :value="moment(val, 'X')"
      @change="handleChange"
      v-bind="{...$props, ...$attrs}"
      v-on="$listeners"
    ></a-date-picker>
  </div>
</template>
<script>
export default {
  name: "yi-date-picker",
  data: function () {
    return {
      val: 0,
      moment: window.moment
    };
  },
  props: {
    value: {
      default: 0,
    },
    showTime: {
      default: true
    }
  },
  watch: {
    value: function (v) {
      this.val = this.parse(v);
    },
    val: function (v) {
      this.$emit("input", v);
    },
  },
  mounted: function () {
    this.val = this.parse(this.value);
  },
  methods: {
    parse: function (v) {
      return Yi.isEmpty(v) ? parseInt(Date.now() / 1000) : v;
    },
    handleChange: function (v) {
      this.val = v;
      this.$emit("change", v);
    },
  },
};
</script>