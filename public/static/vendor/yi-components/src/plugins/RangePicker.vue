<template>
    <a-range-picker
        :value-format="'X'"
        v-model="val"
        v-bind="{...$attrs, ...$props}"
        v-on="$listeners"
    ></a-range-picker>
</template>
<script>
export default {
    name: "yi-range-picker",
    data: function () {
        return {
            val: [0, 0]
        };
    },
    props: {
        value: {
            default: [0, 0]
        }
    },
    watch: {
        value: {
            deep: true, handler: function(v) {
                this.val = v;
            }
        },
        val: {
            deep: true, handler: function(v) {
                if (v && !this.$attrs.showTime) {
                    if (v[0]) v[0] = Math.floor((new Date((new Date(v[0] * 1000)).format('yyyy-MM-dd') + ' 00:00:00')).valueOf() / 1000).toString()
                    if (v[1]) v[1] = Math.floor((new Date((new Date(v[1] * 1000)).format('yyyy-MM-dd') + ' 23:59:59')).valueOf() / 1000).toString()
                }
                this.$emit('input', v);
            }
        }
    },
    mounted: function() {
        this.val = this.value;
    },
};
</script>