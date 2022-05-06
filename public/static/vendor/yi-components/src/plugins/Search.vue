
<style scoped>
.yi-search {
    margin: 5px;
}

.yi-search .min-box {
    min-width: 150px;
}
.inline {
    display: inline-block;
}
</style>
<template>
    <div class="yi-search">
        <a-form layout="inline" v-if="visible">
            <div
                style="display: inline-block"
                v-for="(item, index) in c_columns"
                :key="index"
            >
                <a-form-item
                    :label="item.search.title || item.title"
                    v-if="item.search"
                >
                    <a-select
                        @change="handleWhereChange(item)"
                        class="min-box"
                        v-if="item.search.type == 'select'"
                        v-model="item.search.value"
                        :placeholder="item.search.placeholder || ''"
                    >
                        <a-select-option
                            v-for="(v, i) in item.search.options"
                            :key="i"
                            :value="v.value"
                            >{{ v.label }}
                        </a-select-option>
                    </a-select>
                    <yi-range-picker
                        @change="handleWhereChange(item)"
                        :value-format="item.search.valueFormat || 'X'"
                        v-model="item.search.value"
                        v-if="item.search.type == 'date'"
                        v-bind="{...item.search.attrs || {}, ...item.search.props || {}}"
                    ></yi-range-picker>
                    <a-input
                        v-if="item.search.type == 'input'"
                        v-model="item.search.value"
                        @change="handleWhereChange(item)"
                        @keyup.13="handleSubmit"
                        :placeholder="item.search.placeholder || ''"
                    >
                    </a-input>
                    <yi-select
                        @vchange="handleWhereChange(item)"
                        v-model="item.search.value"
                        v-if="item.search.type == 'remoteSelect'"
                        :style="item.search.option.style || { width: '120px' }"
                        :url="item.search.option.url"
                        :label-field="item.search.option.labelField"
                        :value-field="item.search.option.valueField"
                        :paginate="item.search.option.paginate || false"
                        :allow-clear="
                            item.search.option.allowClear ? true : false
                        "
                        :get-data="item.search.option.getData"
                        :set-default="item.search.option.setDefault"
                        :mode="item.search.option.mode || 'default'"
                    ></yi-select>
                    <a-radio-group
                        v-if="item.search.type == 'btnGroup'"
                        v-model="item.search.value"
                        @change="handleWhereChange(item)"
                    >
                        <a-radio-button
                            v-for="(v, i) in item.search.options"
                            :key="i"
                            :value="v.value"
                        >
                            {{ v.label }}
                        </a-radio-button>
                    </a-radio-group>
                    <a-radio-group
                        v-if="item.search.type == 'radio'"
                        v-model="item.search.value"
                        @change="handleWhereChange(item)"
                    >
                        <a-radio
                            v-for="(v, i) in item.search.options"
                            :key="i"
                            :value="v.value"
                        >
                            {{ v.label }}
                        </a-radio>
                    </a-radio-group>
                    <a-switch v-if="item.search.type == 'switch'" v-model="item.search.value" @change="handleWhereChange(item)" v-bind="{...item.search.props, ...item.search.attrs}" v-on="item.search.events"></a-switch>
                    <yi-checkbox v-if="item.search.type == 'checkbox'" v-model="item.search.value" :options="item.search.options" @change="handleWhereChange(item)" v-bind="{...item.search.props, ...item.search.attrs}" v-on="item.search.events"></yi-checkbox>
                    <yi-inputs v-if="item.search.type == 'inputs'" v-model="item.search.value" @change="handleWhereChange(item)" @keyup.13.native="handleSubmit" v-bind="{...item.search.props, ...item.search.attrs}" v-on="item.search.events"></yi-inputs>
                    <component v-if="item.search.type == 'custom'" :is="item.search.component" v-bind="{...item.search.attrs, ...item.search.props}" v-on="item.search.events" v-model="item.search.value"></component>
                </a-form-item>
            </div>
        </a-form>
    </div>
</template>
<script>
export default {
    name: "yi-search",
    data: function () {
        return {
            visible: false,
            where: {},
            c_columns: [],
        };
    },
    props: {
        defaultWhere: {
            default: {},
            type: Object,
        },
        columns: {
            default: [],
        },
    },
    watch: {
        defaultWhere: {
            deep: true,
            handler: function (v) {
                this.where = v;
            },
        },
        columns: {
            deep: true,
            handler: function () {
                this.getWhere();
            },
        },
    },
    mounted: function () {
        this.where = this.defaultWhere;
        this.init();
        this.getWhere();
    },
    methods: {
        init() {
            let list = [];
            for (let i = 0; i < this.columns.length; i++) {
                let item = this.columns[i];
                if (!item.search) continue;
                if (this.defaultWhere[item.key] !== undefined) {
                    let val =
                        typeof this.defaultWhere[item.key] == "object"
                            ? this.defaultWhere[item.key][1]
                            : this.defaultWhere[item.key];
                    item.search.value = val;
                }
                switch (item.search.type) {
                    case 'input':
                    case 'inputs':
                        item.search.auto = false;
                        break;
                    default:
                        item.search.auto = true;
                        break;
                }
                list.push(item);
            }
            this.c_columns = list;
        },
        getWhere: function (v) {
            var where = this.where || {};
            this.visible = false;
            for (var i = 0; i < this.c_columns.length; i++) {
                var item = this.c_columns[i];
                if (typeof item.search == 'function') item.search = item.search();
                if (!item.search) continue;
                this.visible = true;
                if (
                    !item.search.fullMatch && 
                    (
                        item.search.value === undefined ||
                        item.search.value === null ||
                        item.search.value.length == 0
                    )
                ) {
                    delete where[item.key];
                    continue;
                }
                var s = "=";
                if (!item.search.s) {
                    switch (item.search.type) {
                        case "date":
                            s = "BETWEEN";
                            break;
                        case "checkbox":
                            s = "IN";
                            break;
                        case 'inputs':
                            s = 'BETWEEN';
                            break;
                    }
                } else s = item.search.s;
                switch (item.search.type) {
                    case 'date':
                        if (!item.search.value || (item.search.value[0] == 0 && item.search.value[1] == 0)) {
                            delete where[item.key];
                            continue;
                        }
                        break;
                }
                where[item.key] = [s, item.search.value];
            }
            this.$emit("change", where);
            this.$emit("get-where", where);
        },
        handleSubmit: function () {
            this.$emit("handle-select");
        },
        handleWhereChange: function (item) {
            this.$emit("item-change", item);
            this.$nextTick(() => {
                if (item.search.auto) this.handleSubmit();
            })
        },
    },
};
</script>