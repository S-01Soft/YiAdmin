
<template functional>
    <a-sub-menu :key="props.menuInfo.name">
        <span slot="title">
            <i v-if="props.menuInfo.icon" class="left-menu-item" :class="props.menuInfo.icon"></i><span>{{ props.menuInfo.title_txt }}</span>
        </span>
        <template v-for="item in props.menuInfo.childlist">
            <a-menu-item v-if="item.childlist.length < 2" :key="item.childlist.length == 1 ? item.childlist[0].name : item.name" class="nav-link" :data-key="item.childlist.length == 1 ? item.childlist[0].name : item.name" :data-url="item.childlist.length == 1 ? item.childlist[0].name : item.name" :data-title="item.title">
                <i v-if="item.icon" class="left-menu-item" :class="item.icon"></i><span>{{ item.title_txt }}</span>
            </a-menu-item>
            <yi-sub-menu v-else :key="item.childlist.length == 1 ? item.childlist[0].name : item.name" :menu-info="item" />
        </template>
    </a-sub-menu>
</template>
<script>
export default {
    name: "yi-sub-menu",
    isSubMenu: true,
    props: ['menuInfo'],
    
};
</script>