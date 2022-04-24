<template>
    <div class="inline">
        <a-input-group compact>
            <a-input
                style="width: 100px; text-align: center"
                :placeholder="minPlaceholder"
                v-model="v1"
                @keyup.enter="handleMinChange"
                @blur="handleBlur"
            />
            <a-input
                style="
                    width: 30px;
                    border-left: 0;
                    pointer-events: none;
                    backgroundcolor: #fff;
                "
                placeholder="~"
                disabled
            />
            <a-input
                style="width: 100px; text-align: center; border-left: 0"
                :placeholder="maxPlaceholder"
                v-model="v2"
                @keyup.enter="handleMaxChange"
                @blur="handleBlur"
            />
        </a-input-group>
    </div>
</template>
<script>
export default {
    name: "yi-inputs",
    data() {
        return {
            v1: '',
            v2: ''
        }
    },
    props: {
        value: {
            default: ['', '']
        },
        minPlaceholder: '',
        maxPlaceholder: ''
    },
    watch: {
        value: {
            deep: true, handler(v) {
                this.inputs = v;
            }
        },
        v1() {
            this.$emit('input', [this.v1, this.v2])
        },
        v2() {
            this.$emit('input', [this.v1, this.v2])
        }
    },
    mounted() {
        this.v1 = this.value[0];
        this.v2 = this.value[1];
    },
    methods: {
        handleMinChange() {
            this.$emit('change', [this.v1, this.v2])
        },
        handleMaxChange() {
            this.$emit('change', [this.v1, this.v2])
        },
        handleBlur() {
            this.$emit('blur', [this.v1, this.v2])
        }
    }
};
</script>
<style scoped>
.inline {
    display: inline-block;
}
</style>