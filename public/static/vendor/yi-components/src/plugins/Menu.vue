<template>
    <a-menu
        mode="inline"
        theme="dark"
        :inline-collapsed="collapsed"
        v-bind="{ ...$props, ...$attrs }"
        v-on="$listeners"
    >
        <template v-for="(item,index) in list">
            <a-menu-item v-if="item.childlist.length < 2" :key="item.childlist.length == 1 ? item.childlist[0].name : item.name" class="nav-link" :data-key="item.childlist.length == 1 ? item.childlist[0].name : item.name" :data-url="item.childlist.length == 1 ? item.childlist[0].name : item.name" :data-title="item.title" @click="$emit('menu-click', $event)">
                <i v-if="item.icon" class="left-menu-item" :class="item.icon"></i><span>{{ item.title_txt }}</span>
            </a-menu-item>
            <yi-sub-menu v-else :key="item.childlist.length == 1 ? item.childlist[0].name : item.name" :menu-info="item" @menu-click="$emit('menu-click', $event)"></yi-sub-menu>
        </template>
        <a-menu-item>
            <a href="https://www.01soft.top" target="_blank"><i class="fa fa-home"></i> <span>访问官网</span> </a>    
        </a-menu-item>
    </a-menu>
</template>
<script>
export default {
    name: "yi-menu",
    data() {
        return {
            collapsed: false,
            list: [],
        };
    },
    mounted() {
        this.getMenuList();
    },
    methods: {
        getMenuList() {
            this.$http.post("system/admin/index/index").then((data) => {
                this.list = data;
            });
        },
    },
};
</script>
