<template>
    <div class="yi-select">
        <a-select
            v-bind="{ ...$props, ...$attrs }"
            v-on="$listeners"
            style="width: 100%"
            v-model="val"
        >
            <div slot="dropdownRender" slot-scope="menu">
                <v-nodes :vnodes="menu"></v-nodes>
            </div>
            <a-select-option
                v-for="(item, index) in data"
                :key="index"
                :value="item[valueField]"
            >
                <div v-html="showLabel(item)"></div>
            </a-select-option>
        </a-select>
    </div>
</template>
<script>
export default {
    name: "yi-select",
    components: {
        VNodes: {
            functional: true,
            render: function (h, ctx) {
                var vnodes = [ctx.props.vnodes];
                var vm = ctx.parent;
                if (ctx.parent.last_page > 1 && vm.pagi) {
                    vnodes.push(
                        h(
                            "div",
                            {
                                class: "text-center yi-select-pagination",
                                on: {
                                    mousedown: function (e) {
                                        e.preventDefault();
                                        return false;
                                    },
                                },
                            },
                            [
                                h(
                                    "span",
                                    {
                                        class: {
                                            text: true,
                                            disabled: !vm.hasPre,
                                        },
                                        on: {
                                            click: function (e) {
                                                ctx.parent.pre();
                                            },
                                        },
                                    },
                                    "上一页"
                                ),
                                h(
                                    "span",
                                    {
                                        class: {
                                            text: true,
                                            disabled: !vm.hasNext,
                                        },
                                        on: {
                                            click: function () {
                                                ctx.parent.next();
                                            },
                                        },
                                    },
                                    "下一页"
                                ),
                            ]
                        )
                    );
                }
                return h("div", vnodes);
            },
        },
    },
    data: function () {
        return {
            val: "",
            data: [],
            hasNext: false,
            hasPre: false,
            last_page: 1,
            inited: false,
        };
    },
    props: {
        url: "",
        valueField: "",
        labelField: "",
        pageField: {
            default: "page",
        },
        pagesizeField: {
            default: "page_size",
        },
        page: {
            default: 1,
        },
        pagesize: {
            default: 10,
        },
        value: {
            default: null,
        },
        size: {
            default: "default",
        },
        paginate: undefined,
        getData: {
            type: undefined | Function,
            default: undefined,
        },
        setDefault: {
            default: true,
        },
        mode: {
            default: "default",
        },
    },
    computed: {
        pagi: function () {
            return this.paginate || this.paginate === "";
        },
    },
    watch: {
        value: function (v) {
            if (this.mode == "multiple") {
                v = v.length ? v.split(",") : [];
                for (var i = 0; i < v.length; i++) {
                    var val = Number(v[i]);
                    if (!isNaN(val)) v[i] = val;
                }
            }
            this.val = v;
        },
        val: function (v, ov) {
            if (this.mode == "multiple") {
                if (typeof v == "number") v = v.toString();
                else if (typeof v == "string") {
                } else v = v ? v.join(",") : "";
            }
            this.$emit("input", v);
            this.$emit("vchange", v, this.data);
        },
        url: function () {
            this.init();
        },
    },
    mounted: function () {
        if (this.mode == "multiple") this.val = this.parseValue(this.value);
        else this.val = this.value;
        this.init();
    },
    methods: {
        init: function () {
            return _.throttle(this._init, 1000)();
        },
        parseValue: function (v) {
            if (typeof v == "object") return v;
            v = v ? v.split(",") : [];
            for (var i = 0; i < v.length; i++) {
                var val = Number(v[i]);
                if (!isNaN(val)) v[i] = val;
            }
            return v;
        },
        _init: function () {
            var that = this;
            var form = {};
            if (this.pagi) {
                form[this.pageField] = this.page;
                form[this.pagesizeField] = this.pagesize;
            }
            if (!this.url) return;
            this.$emit("data-loading");
            let url =
                typeof this.url == "function"
                    ? this.url(this.inited)
                    : this.url;
            this.$http
                .get(url, { params: form })
                .then((res) => {
                    this.inited = true;
                    if (that.pagi) {
                        that.hasNext =
                            that.page == res.last_page ? false : true;
                        that.hasPre = that.page > 1 ? true : false;
                        that.data =
                            typeof that.getData == "function"
                                ? that.getData(res)
                                : res.data;
                        that.last_page = res.last_page;
                    } else {
                        that.data =
                            typeof that.getData == "function"
                                ? that.getData(res)
                                : res;
                    }
                    if (that.setDefault && !that.val && that.data.length > 0) {
                        that.val = that.data[0][that.valueField];
                    }
                    that.$emit("data-change", res);
                })
                .catch();
        },
        pre: function () {
            if (this.page <= 1) return;
            this.page--;
            this.init();
        },
        next: function () {
            if (this.page >= this.last_page) return;
            this.page++;
            this.init();
        },
        showLabel(item) {
            if (typeof this.labelField == 'function') return this.labelField(item);
            return item[this.labelField];
        }
    },
};
</script>

<style>
.yi-select-pagination .text {
    padding-left: 10px;
    font-size: 12px;
    cursor: pointer;
    color: #409eff;
}

.yi-select-pagination {
    padding: 10px 0;
}

.yi-select-pagination .disabled {
    color: #bfbfbf;
}
</style>