
<template id="yi-kv">
  <div class="yi-kv">
    <a-input-group
      compact
      v-for="(key, index) in Object.keys(option)"
      :key="index"
      style="margin: 2px 0"
    >
      <a-input
        :placeholder="$lang('Key')"
        v-model="key"
        @focus="handleKeyFocus"
        @change="handleKeyChange"
        @blur="handleKeyBlur"
        :data-key="key"
        style="width: 40%"
      ></a-input>
      <a-input
        :placeholder="$lang('Value')"
        v-model="option[key]"
        style="width: 40%"
      ></a-input>
      <a-button type="danger" @click="delItem(key, index)">
        <a-icon type="delete"></a-icon>
      </a-button>
    </a-input-group>
    <a-button type="danger" style="margin-top: 5px" @click="addItem">
      <a-icon type="plus"></a-icon>
    </a-button>
  </div>
</template>
<script>
export default {
  name: "yi-kv",
  data: function () {
    return {
      option: {},
      active_new_key: "",
      active_old_key: "",
    };
  },
  props: {
    value: {
      default: {},
    },
  },
  watch: {
    option: {
      deep: true,
      handler: function (data) {
        this.$emit("input", data);
      },
    },
    value: {
      deep: true,
      handler: function (v) {
        this.option = v;
      },
    },
  },
  mounted: function () {
      this.option = this.value;
  },
  methods: {
    addItem: function () {
      var index = Object.keys(this.option).length + 1;
      this.$set(this.option, "key" + index, "");
    },
    delItem: function (key) {
      this.$delete(this.option, key);
    },
    handleKeyChange: function (e) {
      this.active_new_key = e.target.value;
      this.active_old_key = e.target.getAttribute("data-key");
    },
    handleKeyBlur: function () {
      var result = {};
      var keys = Object.keys(this.option);
      for (var i = 0; i < keys.length; i++) {
        if (this.active_old_key == keys[i])
          result[this.active_new_key] = this.option[this.active_old_key];
        else result[keys[i]] = this.option[keys[i]];
      }
      this.$set(this, "option", result);
    },
    handleKeyFocus: function (e) {
      e.target.select();
    },
  },
};
</script>
