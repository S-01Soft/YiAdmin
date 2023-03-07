
<style scoped>
.yi-exports-input {
  height: 32px;
}

.yi-exports-input,
.yi-exports-input:focus {
  border: 1px solid #ddd;
}
</style>
<template>
    <div class="yi-exports" style="display: inline-block;">
        <a-button-group>
            <a-button type="danger" @click="handleSubmit">{{$lang('Export')}}</a-button>
            <a-button type="danger" v-if="showSetting" @click="visible = !visible">
                <a-icon type="setting"></a-icon>
            </a-button>
        </a-button-group>
        <a-modal :title="$lang('Export Setting')" v-model="visible" :dialog-style="{top: '25px'}" width="600px" @ok="handleSubmit">
            <a-form>
                <a-form-item :label="$lang('Fields Setting')">
                    <div style="max-height: 250px;overflow-y: scroll;">
                        <a-table class="table" :columns="columns" :data-source="fields"
                            :row-selection="{ selectedRowKeys: selectedRowKeys, onChange: onSelectChange }"
                            :pagination="false">
                            <template slot="y-title" slot-scope="title, row">
                                <div>
                                    <input class="yi-exports-input" type="text" @change="handleChange"
                                        v-model="row.title" />
                                </div>
                            </template>
                            <template slot="key" slot-scope="key, row">
                                <div>
                                    <input class="yi-exports-input" type="text" v-model="row.key" />
                                </div>
                            </template>
                            <template slot="action" slot-scope="row">
                                <div>
                                    <a-select v-model="row.format">
                                        <a-select-option value="default">{{$lang('Default')}}</a-select-option>
                                        <a-select-option value="date">{{$lang('Date')}}</a-select-option>
                                        <a-select-option value="datetime">{{$lang('DateTime')}}</a-select-option>
                                        <a-select-option value="time">{{$lang('Time')}}</a-select-option>
                                        <slot name="formatter"></slot>
                                    </a-select>
                                </div>
                            </template>
                        </a-table>
                    </div>
                </a-form-item>
                <a-form-item :label="$lang('Export Max Count')" :label-col="{span: 6}" :wrapper-col="{span: 16}">
                    <a-input v-model="limitCount"></a-input>
                </a-form-item>
                <a-form-item :label="$lang('Export File Name')" :label-col="{span: 6}" :wrapper-col="{span: 16}">
                    <a-input v-model="title"></a-input>
                </a-form-item>
            </a-form>
            <template slot="footer">
                <a-button type="primary" @click="init">{{$lang('Reset')}}</a-button>
                <a-button type="primary" @click="handleSaveSetting" :loading="setting_loading">{{$lang('Save Config')}}</a-button>
                <a-button type="danger" @click="handleSubmit">{{$lang('Export')}}</a-button>
            </template>
        </a-modal>
    </div>
</template>
<script>
export default {
  name: "yi-exports",
  data: function () {
    return {
      limitCount: this.limit,
      title: "未命名",
      visible: false,
      fields: [],
      columns: [
        {
          title: "名称",
          dataIndex: "title",
          key: "title",
          scopedSlots: { customRender: "y-title" },
        },
        {
          title: "变量",
          dataIndex: "key",
          key: "key",
          scopedSlots: { customRender: "key" },
        },
        {
          title: "导出格式",
          key: "action$",
          scopedSlots: { customRender: "action" },
        },
      ],
      selectedRowKeys: [],
      selectedRows: [],
      setting_loading: false,
    };
  },
  props: {
    limit: {
      default: 1000,
      type: Number,
    },
    defaultColumns: {
      default: [],
      type: Object,
    },
    id: {
      default: "",
      type: String,
    },
    showSetting: {
      default: true
    }
  },
  mounted: function () {
    var self = this;
    this.$http
      .get("/system/admin/setting/index", {
        params: { where: { key: 'Export' + this.id } },
      })
      .then(function (data) {
        if (data.data.length == 0) self.init();
        else {
          var setting = JSON.parse(data.data[0].data);
          self.fields = setting.fields;
          self.selectedRowKeys = setting.selectedRowKeys;
          self.limitCount = setting.limit;
          self.title = setting.title;
          for (var i = 0; i < self.fields.length; i++) {
            var item = self.fields[i];
            if (self.selectedRowKeys.indexOf(item.key) != -1)
              self.selectedRows.push(item);
          }
        }
      });
  },
  methods: {
    init: function () {
      var result = [];
      for (var i = 0; i < this.defaultColumns.length; i++) {
        var item = this.defaultColumns[i];
        if (item.dataIndex) {
          var data = {
            title: item.title,
            key: item.key,
            format: "default",
          };
          result.push(data);
          this.selectedRowKeys.push(item.key);
          this.selectedRows.push(data);
        }
      }
      this.fields = result;
    },

    handleSubmit: function () {
      this.$emit("submit", {
        fields: this.selectedRows,
        limit: this.limitCount,
        title: this.title,
      });
    },
    onSelectChange: function (selectedRowKeys, selectedRows) {
      this.selectedRowKeys = selectedRowKeys;
      this.selectedRows = selectedRows;
    },
    handleSaveSetting: function () {
      var self = this;
      var form = {
        key: 'Export' + this.id,
        data: {
          fields: this.fields,
          selectedRowKeys: this.selectedRowKeys,
          limit: this.limitCount,
          title: this.title,
        },
      };
      this.setting_loading = true;
      this.$http
        .post("/system/admin/setting/add", { form: form })
        .then(function () {
          self.setting_loading = false;
          self.$message.success("配置保存成功");
        });
    },
    handleChange: function () {},
  },
};
</script>
