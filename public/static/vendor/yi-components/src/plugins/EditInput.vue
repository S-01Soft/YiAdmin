
<style>
.yi-edit-input {
  position: relative;
}

.yi-edit-input .edit-span {
  border-bottom: 1px dotted rgb(34, 1, 110);
  cursor: pointer;
  margin: 0 4px;
}

.yi-edit-input .yi-edit {
  position: absolute;
  top: -20px;
}
</style>
<template>
  <div class="yi-edit-input" style="display: inline-block" @click="handleClick">
    <div v-if="showEdit" class="input-group">
      <a-input
        ref="ainput"
        @press-enter="handleSubmit"
        :size="size"
        v-model="val"
        class="form-control"
      ></a-input>
      <div class="input-group-append">
        <a-button
          :loading="loading"
          :size="size"
          type="danger"
          @click="handleSubmit"
          >确定</a-button
        >
        <a-button :size="size" @click="handleCancel">{{$lang('Cancel')}}</a-button>
      </div>
    </div>
    <span v-else class="edit-span">{{ value }}</span>
  </div>
</template>
<script>
export default {
  name: "yi-edit-input",
  data: function () {
    return {
      val: null,
      showEdit: false,
      loading: false,
    };
  },
  props: {
    value: {
      default: null,
    },
    param: {
      default: null,
    },
    size: {
      default: "default",
    },
  },
  watch: {
    value: function (v) {
      this.val = v;
    },
    val: function (v) {
      this.$emit("input", v);
    },
  },
  mounted: function () {
    var that = this;
    this.val = this.value;
    document.addEventListener("click", function () {
      that.showEdit = false;
    });
  },
  methods: {
    handleClick: function (e) {
      var self = this;
      this.showEdit = true;
      this.$nextTick(function () {
        self.$refs["ainput"].focus();
      });
      e.stopPropagation ? e.stopPropagation() : (e.cancelBubble = true);
    },
    handleCancel: function (e) {
      this.showEdit = false;
      e.stopPropagation ? e.stopPropagation() : (e.cancelBubble = true);
    },
    handleSubmit: function () {
      this.$emit("submit", this.val, this.param, this);
    },
    load: function () {
      this.loading = true;
    },
    finish: function () {
      this.loading = false;
    },
  },
};
</script>