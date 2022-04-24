
<template>
  <div class="yi-color-picker">
    <a-input v-model="val">
      <div slot="addonAfter">
        <color-picker v-model="val"></color-picker>
      </div>
    </a-input>
  </div>
</template>
<script>
import ColorPicker from "../library/color-picker/packages/color-picker/src/color-picker";
export default {
  components: { ColorPicker: ColorPicker },
  name: "yi-color-picker",
  data: function () {
    return {
      val: "",
    };
  },
  props: {
    value: {
      default: "",
    },
  },
  watch: {
    val: function (v) {
      this.$emit("input", v);
    },
    value: function (v) {
      this.val = v;
    },
  },
  mounted: function () {
    this.val = this.value;
  },
};
</script>