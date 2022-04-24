<template>
    <a-switch v-model="open" v-bind="{...$props, ...$attrs}" @change="handleChange"></a-switch>
</template>
<script>
export default {
    name: "yi-switch",
    data: function () {
        return {
            open: true
        };
    },
    props: {
        value: {
            default: 0
        },
        checkedValue: {
            default: 1
        },
        unCheckedValue: {
            default: 0
        }
    },
    watch: {
        value: function (val) {
            this.open = val == this.checkedValue;
        }
    },
    mounted: function () {
        this.open = this.value == this.checkedValue;
    },
    methods: {
        handleChange(v, e) {
            var val = v ? this.checkedValue : this.unCheckedValue;
            this.$emit('input', val);
            this.$emit('change', val, e);
        }
    }
};
</script>