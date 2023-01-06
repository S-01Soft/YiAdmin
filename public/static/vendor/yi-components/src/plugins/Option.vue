

<template>
  <div class="yi-option">
    <a-tabs v-if="tabs.length" type="card" v-model="activeTab">
      <a-tab-pane
        v-for="tab in tabs"
        :key="tab.key"
        :tab="$lang(tab.title)"
      ></a-tab-pane>
    </a-tabs>
    <a-form-model
      class="form"
      ref="ruleForm"
      :model="form"
      :label-col="{ span: 4 }"
      :wrapper-col="{ span: 17 }"
    >
      <a-empty
        v-if="Object.keys(option).length == 0"
        style="margin-top: 50px"
      ></a-empty>
      <div v-else>
        <div
          v-for="(row, key) in option"
          :key="key"
          v-show="activeTab == key"
          style="width: 100%"
        >
          <div style="float: right; margin-top: -18px; color: #ababab">
            {{ key }}
          </div>
          <div style="position: relative;" v-for="(item, index) in row" :key="index" @mousemove="currentItem = item" @mouseleave="currentItem = {}">
            <a-form-item :label="$lang(item.title)" v-if="item.type != 'hidden'">
              <template slot="extra">
                <div
                  v-html="
                    (item.tip || '') +
                    ' [ ' +
                    item.name +
                    '（' +
                    item.alias +
                    '）]'
                  "
                ></div>
              </template>
              <a-input
                v-if="item.type == 'string'"
                v-model="form[item.name]"
              ></a-input>
              <a-input-number
                v-if="item.type == 'number'"
                v-model="form[item.name]"
                :step="item.options.step"
              ></a-input-number>
              <a-input-password
                v-if="item.type == 'password'"
                v-model="form[item.name]"
              ></a-input-password>
              <a-select v-if="item.type == 'select'" v-model="form[item.name]">
                <a-select-option
                  v-for="(v, i) in item.options"
                  :value="i"
                  :key="i"
                  >{{ v }}</a-select-option
                >
              </a-select>
              <a-select
                v-if="item.type == 'selects'"
                mode="multiple"
                v-model="form[item.name]"
              >
                <a-select-option
                  v-for="(v, i) in item.options"
                  :value="i"
                  :key="i"
                  >{{ v }}</a-select-option
                >
              </a-select>

              <yi-select
                v-if="item.type == 'remoteSelect'"
                v-model="form[item.name]"
                :paginate="item.options.paginate || 0"
                :url="item.options.url"
                :label-field="item.options.labelField"
                :value-field="item.options.valueField"
                :allow-clear="item.options.allowClear ? true : false"
                :set-default="item.options.setDefault || false"
              >
              </yi-select>

              <yi-select
                v-if="item.type == 'remoteSelects'"
                mode="multiple"
                :paginate="item.options.paginate || 0"
                v-model="form[item.name]"
                :url="item.options.url"
                :label-field="item.options.labelField"
                :value-field="item.options.valueField"
              >
              </yi-select>

              <yi-kv v-if="item.type == 'kv'" v-model="form[item.name]"></yi-kv>

              <a-radio-group
                v-if="item.type == 'radio'"
                v-model="form[item.name]"
                style="margin-bottom: 5px"
              >
                <a-radio v-for="(v, k) in item.options" :value="k" :key="k">{{
                  v
                }}</a-radio>
              </a-radio-group>
              <a-checkbox-group
                v-if="item.type == 'checkbox'"
                v-model="form[item.name]"
              >
                <a-checkbox
                  v-for="(v, k) in item.options"
                  :value="k"
                  :key="k"
                  >{{ $lang(v) }}</a-checkbox
                >
              </a-checkbox-group>
              <yi-image
                v-if="item.type == 'image'"
                v-model="form[item.name]"
              ></yi-image>
              <yi-image
                v-if="item.type == 'images'"
                v-model="form[item.name]"
                :multiple="true"
              ></yi-image>
              <yi-attachment
                v-if="item.type == 'file'"
                v-model="form[item.name]"
                :accept="item.options.accept"
                :image-preview="false"
                :loading="true"
              ></yi-attachment>
              <yi-editor
                v-if="item.type == 'editor'"
                v-model="form[item.name]"
                :id="'i-' + item.name"
              >
              </yi-editor>

              <a-switch
                v-if="item.type == 'switch'"
                v-model="form[item.name]"
              ></a-switch>
              <a-textarea
                v-if="item.type == 'text'"
                v-model="form[item.name]"
              ></a-textarea>
              <yi-list
                v-if="item.type == 'list'"
                v-model="form[item.name]"
              ></yi-list>
              <yi-color-picker
                v-if="item.type == 'color'"
                v-model="form[item.name]"
              ></yi-color-picker>
              <yi-editormd
                v-if="item.type == 'markdown'"
                v-model="form[item.name]"
                :data-id="'i-' + item.name"
                :s="activeTab + '-' + key"
                :show="activeTab == key"
                :option="item.options"
              ></yi-editormd>
              <yi-coder
                v-if="item.type == 'code'"
                v-model="form[item.name]"
                :s="activeTab + '-' + key"
                :show="activeTab == key"
                :option="item.options"
              ></yi-coder>
            </a-form-item>
            <div v-show="showRemove && currentItem.name == item.name" class="form-item-operate">
                <a-icon class="pointer" style="color: red;" type="delete" @click="handleRemoveOption"></a-icon>
            </div>
          </div>
        </div>
      </div>
    </a-form-model>

    <a-modal
      v-model="visible"
      centered
      :title="$lang('Add Config')"
      @ok="handleAddConfigItem"
    >
      <a-form-model
        :model="itemForm"
        :label-col="{ span: 5 }"
        :wrapper-col="{ span: 18 }"
      >
        <a-form-model-item :label="$lang('Config Type')">
          <a-select v-model="itemForm.type">
            <a-select-option
              v-for="(item, index) in types"
              :value="item.key"
              :key="index"
              >{{ item.value }}
            </a-select-option>
          </a-select>
        </a-form-model-item>
        <a-form-model-item :label="$lang('Config Group')">
          <a-input-group compact>
            <a-input v-model="itemForm.group_key" style="width: 60%"></a-input>
            <a-select @change="handleGroupChange" style="width: 40%">
              <a-select-option
                v-for="(tab, i) in tabs"
                :value="tab.key"
                :key="i"
                >{{ tab.title }}
              </a-select-option>
            </a-select>
          </a-input-group>
        </a-form-model-item>
        <a-form-model-item :label="$lang('Group Title')">
          <a-input v-model="itemForm.group"></a-input>
        </a-form-model-item>
        <a-form-model-item
          :label="$lang('Config')"
          v-if="
            types[typeIndex] &&
            (types[typeIndex].options ||
              ['select', 'selects', 'radio', 'checkbox'].indexOf(
                itemForm.type
              ) != -1)
          "
        >
          <div
            v-if="
              ['remoteSelect', 'remoteSelects'].indexOf(itemForm.type) != -1
            "
          >
            <a-input-group
              compact
              style="margin: 2px 0"
              v-for="(v, k) in types[typeIndex].options"
              :key="k"
            >
              <a-input
                :placeholder="$lang('Key')"
                v-model="k"
                readonly
                style="width: 40%"
              ></a-input>
              <a-input
                :placeholder="$lang('Value')"
                v-model="itemForm.options[k]"
                style="width: 40%"
              ></a-input>
            </a-input-group>
          </div>
          <div
            v-if="
              ['select', 'selects', 'radio', 'checkbox', 'number', 'file', 'code', 'editor'].indexOf(
                itemForm.type
              ) != -1
            "
          >
            <yi-kv v-model="itemForm.options"></yi-kv>
          </div>
        </a-form-model-item>
        <a-form-model-item :label="$lang('Var Title')">
          <a-input v-model="itemForm.title"></a-input>
        </a-form-model-item>
        <a-form-model-item :label="$lang('Var Name')">
          <a-input v-model="itemForm.alias" @change="handleNameChange"></a-input>
        </a-form-model-item>
        <a-form-model-item :label="$lang('Var Full Name')">
          <a-input v-model="itemForm.name"></a-input>
        </a-form-model-item>
        <a-form-model-item :label="$lang('Placeholder Text')">
          <a-input v-model="itemForm.placeholder"></a-input>
        </a-form-model-item>
        <a-form-model-item :label="$lang('Tips Text')">
          <a-textarea v-model="itemForm.tip"></a-textarea>
        </a-form-model-item>
      </a-form-model>
    </a-modal>
  </div>
</template>
<script>
export default {
  name: "yi-option",
  props: {
    value: {
      default: {},
    },
    showRemove: {
        default: false
    }
  },

  watch: {
    value: {
      deep: true,
      handler: function (val) {
        this.initData(val);
      },
    },
    "itemForm.type": function (v) {
      this.$set(
        this.itemForm,
        "options",
        this.types[this.typeIndex].options || {}
      );
      if (["kv"].indexOf(v) != -1) this.$set(this.itemForm, "value", {});
      else if (["checkbox", "selects", "list"].indexOf(v) != -1)
        this.$set(this.itemForm, "value", []);
      else if (["switch"].indexOf(v) != -1)
        this.$set(this.itemForm, "value", 1);
      else this.$set(this.itemForm, "value", "");
    },
  },
  computed: {
    typeIndex: function () {
      var index = 0;
      for (var i = 0; i < this.types.length; i++) {
        if (this.types[i].key == this.itemForm.type) return i;
      }
      return index;
    },
  },
  data: function () {
    return {
      form: {},
      activeTab: 0,
      option: {},
      tabs: [],
      visible: false,
      itemForm: {},
      types: [
        { key: "string", value: "单行文本" },
        { key: "text", value: "多行文本" },
        {
          key: "number",
          value: "数字",
          options: {
            step: "1",
          },
        },
        { key: "password", value: "密码" },
        { key: "switch", value: "开关" },
        { key: "select", value: "单选" },
        { key: "selects", value: "多选" },
        {
          key: "remoteSelect",
          value: "远程单选",
          options: {
            labelField: "",
            valueField: "",
            url: "",
            paginate: 0,
          },
        },
        {
          key: "remoteSelects",
          value: "远程多选",
          options: {
            labelField: "",
            valueField: "",
            url: "",
            paginate: 0,
          },
        },
        { key: "radio", value: "单选框" },
        { key: "checkbox", value: "多选框" },
        { key: "kv", value: "键值对" },
        { key: "list", value: "列表" },
        { key: "image", value: "单图" },
        { key: "images", value: "多图" },
        { key: "file", value: "文件" , options: {accept: '*'}},
        { key: "color", value: "颜色" },
        { key: "editor", value: "富文本编辑器" },
        { key: "code", value: "代码编辑器", options: {} },
        { key: "hidden", value: "隐藏" },
      ],
      currentItem: {}
    };
  },
  mounted: function () {
    this.resetForm();
  },
  methods: {
    initData: function (data) {
      var tabs = [];
      var keys = [];
      var result = {};
      var form = {};
      var $keys = Object.keys(data);
      for (var i = 0; i < $keys.length; i++) {
        var item = data[$keys[i]];
        if (!item.group_key) continue;
        if (item.hidden) continue;
        if (keys.indexOf(item.group_key) == -1) {
          if (!this.activeTab) this.activeTab = item.group_key;
          tabs.push({ key: item.group_key, title: item.group });
          keys.push(item.group_key);
        }
        if (result[item.group_key]) result[item.group_key].push(item);
        else result[item.group_key] = [item];
        form[item.name] = item.value;
      }
      this.form = form;
      this.option = result;
      this.tabs = tabs;
    },
    handleAddConfigItem: function () {
      this.$emit("add-form-item", this.itemForm);
    },
    show: function () {
      this.visible = true;
    },
    hide: function () {
      this.visible = false;
    },
    handleGroupChange: function (v, e) {
      this.itemForm.group_key = v.trim();
      this.itemForm.group = e.componentOptions.children[0].text.trim();
    },
    getForm: function () {
      return this.form;
    },
    resetForm: function () {
      this.itemForm = {
        group: "",
        group_key: "",
        name: "",
        alias: "",
        title: "",
        type: "string",
        options: {},
        value: "",
        placeholder: "",
        tip: "",
      };
    },
    handleRemoveOption: function() {
        this.$emit("delete-form-item", this.currentItem);
    },
    handleNameChange: function() {
      this.itemForm.name = this.itemForm.group_key + '_' + this.itemForm.alias;
    }
  },
};
</script>

<style scoped>
    .form-item-operate {
        position: absolute;
        right: 24px;
        top: 8px;
    }
</style>