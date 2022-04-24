<template>
    <a-textarea v-model="content"></a-textarea>
</template>
<script>
export default {
    name: 'yi-textarea',
    data() {
        return {
            content: undefined
        }
    },
    props: {
        value: undefined
    },
    watch: {
        value(v) {
            this.content = v;
        },
        content(v) {
            this.$emit('input', v);
        }
    },
    mounted() {
        this.content = this.value
    }
}
</script>