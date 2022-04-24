
<style>
.yi-icons .icon-item {
  border: 1px solid #ddd;
  width: 44px;
  float: left;
  margin: 3px;
  padding: 3px 8px;
  cursor: pointer;
  text-align: center;
}
</style>
<template>
  <div class="yi-icon">
    <i
      :class="iconclass"
      class="pointer"
      v-if="iconclass != ''"
      @click="visible = !visible"
      :style="{ color: color }"
    ></i>
    <span
      v-else
      class="pointer"
      @click="visible = !visible"
      style="font-size: 12px"
      >{{$lang('Choose')}}</span
    >
    <a-modal
      v-model="visible"
      :dialog-style="{ top: '20px' }"
    >
      <a-input
        v-model="search.value"
        :placeholder="$lang('Please press enter to search')"
        @keypress.enter="handleSearch"
        style="margin: 20px 0"
      ></a-input>
      <div class="yi-icons clear" style="max-height: 350px; overflow-y: scroll">
        <div
          class="icon-item"
          v-for="(item, index) in showIcons"
          :key="index"
          @click="
            iconclass = item;
            visible = false;
          "
        >
          <i style="font-size: 20px" :class="item"></i>
        </div>
      </div>
    </a-modal>
  </div>
</template>
<script>
export default {
  name: "yi-icon",
  data: function () {
    return {
      icons: [],
      iconclass: "",
      visible: false,
      showIcons: [],
      search: {
        value: "",
      },
    };
  },
  props: {
    color: "",
    value: "",
    source: { default: "/static/vendor/font.json" },
  },
  watch: {
    value: function (val) {
      this.iconclass = val;
    },
    iconclass: function (val) {
      this.$emit("input", val);
    },
  },
  mounted: function () {
    this.init();
    this.iconclass = this.value;
  },
  methods: {
    handleSearch: function () {
      var result = [];
      var load = this.$message.loading("");
      for (var i = 0; i < this.icons.length; i++) {
        var icon = this.icons[i];
        if (icon.indexOf(this.search.value) != -1) result.push(icon);
      }
      setTimeout(load, 1);
      this.showIcons = result;
    },
    show: function () {
      this.visible = true;
    },
    close: function () {
      this.visible = false;
    },
    init: function () {
      var self = this;
      $.get(this.source, function (res) {
        self.icons = self.showIcons = res;
      });
    },
    getIcons: function (prefixs) {
      var self = this;
      $.get(this.source, function (ret) {
        var exp = /\.([a-zA-Z0-9\-]*):/gi;
        window.icons = ret;
        var result;
        while ((result = exp.exec(ret)) != null) {
          var icon = result[1];
          var ignore = ["sr-only-focusable", "fa-font-awesome-logo-full"];
          if (ignore.indexOf(icon) == -1) {
            var prefix = "fa";
            if (prefixs[icon]) prefix = prefixs[icon];
            self.icons.push(prefix + " " + icon);
          }
        }
        self.showIcons = self.icons;
      });
    },
  },
};
</script>