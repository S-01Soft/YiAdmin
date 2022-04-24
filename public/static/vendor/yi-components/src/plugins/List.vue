

<style>
.yi-list .yi-list-item {
  border: 1px solid #ddd;
  border-radius: 5px;
  padding: 10px;
  margin-top: 10px;
}
</style>
<template>
  <div class="yi-list">
    <a-row :gutter="24">
      <a-col
        :xs="24"
        :sm="24"
        :md="12"
        :lg="12"
        v-for="(item, index) in list"
        :key="index"
      >
        <div class="yi-list-item">
          <div v-for="(v, k) in item" :key="k">
            <a-input :addon-before="k" v-model="item[k]" />
          </div>
          <div
            style="
              position: absolute;
              right: 3px;
              bottom: -18px;
              display: block;
              padding: 0 5px;
              z-index: 9;
            "
          >
            <span class="pointer" @click="handleAddItem(item, index)">
              <a-icon
                type="plus-circle"
                style="color: rgb(224, 37, 37)"
              ></a-icon>
            </span>
            <span class="pointer" @click="handleDelItem(item, index)">
              <a-icon
                type="minus-circle"
                style="color: rgb(224, 37, 37)"
              ></a-icon>
            </span>
          </div>
        </div>
      </a-col>
    </a-row>
    <a-button type="danger" @click="visible = !visible">{{$lang('Add Column')}}</a-button>
    <a-modal :title="$lang('Add Column')" v-model="visible" @ok="handleAddKey">
      <a-input v-model="newKey" :placeholder="$lang(['Input', 'Key'])"></a-input>
    </a-modal>
  </div>
</template>
<script>
export default {
  name: "yi-list",
  data: function () {
    return {
      list: [],
      visible: false,
      newKey: "",
    };
  },
  props: {
    value: {
      default: [],
    },
  },
  watch: {
    value: {
      deep: true,
      handler: function (v) {
        this.list = v;
      },
    },
    list: {
      deep: true,
      handler: function (v) {
        this.$emit("input", v);
      },
    },
  },
  mounted: function () {
    this.list = this.value;
  },
  methods: {
    handleAddItem: function (item, index) {
      var obj = {};
      var keys = Object.keys(item);
      for (var i = 0; i < keys.length; i++) {
        obj[keys[i]] = "";
      }
      this.list.splice(index + 1, 0, obj);
    },
    handleDelItem: function (item, index) {
      this.list.splice(index, 1);
    },
    handleAddKey: function () {
      if (!this.newKey) {
        return;
      }
      if (this.list.length) {
        for (var i = 0; i < this.list.length; i++) {
          this.$set(this.list[i], this.newKey, "");
        }
      } else {
        var obj = {};
        obj[this.newKey] = "";
        this.list = [obj];
      }
      this.newKey = "";
      this.visible = false;
    },
  },
};
</script>