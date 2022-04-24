<template>
    <div style="display: inline-block">
        <a-checkbox
            v-if="showCheckAll"
            :indeterminate="indeterminate"
            :checked="checkAll"
            @change="onCheckAllChange"
        >
            {{ $lang("All") }}
        </a-checkbox>
        <a-checkbox-group
            v-model="checkedList"
            :options="options"
            @change="onChange"
        ></a-checkbox-group>
    </div>
</template>
<script>
export default {
    name: "yi-checkbox",
    data() {
        return {
            checkedList: [],
            indeterminate: true,
            checkAll: false,
        };
    },
    props: {
        value: {
            type: Array,
            default: [],
        },
        options: {
            type: Object,
            default: {},
        },
        showCheckAll: {
            default: true,
            type: Boolean,
        },
    },
    watch: {
        checkedList: {
            deep: true,
            handler(v) {
                this.$emit("input", v);
            },
        },
        value: {
            deep: true,
            handler(v) {
                this.checkedList = v;
            },
        },
    },
    mounted: function() {
        this.checkedList = this.value;
        this.check(this.checkedList)
    },
    methods: {
        onChange(checkedList) {
            this.check(checkedList)
            this.$emit("change", checkedList);
        },
        check(checkedList) {
            this.indeterminate =
                !!checkedList.length &&
                checkedList.length < this.options.length;
            this.checkAll = checkedList.length === this.options.length;
        },
        onCheckAllChange(e) {
            let checkedList = [];
            this.options.forEach((item, index) => {
                checkedList.push(item.value)
            })
            Object.assign(this, {
                checkedList: e.target.checked ? checkedList : [],
                indeterminate: false,
                checkAll: e.target.checked,
            });
            this.$emit("change", checkedList);
        },
    },
};
</script>