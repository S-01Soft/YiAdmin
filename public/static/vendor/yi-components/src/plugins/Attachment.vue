
<template>
    <div
        class="yi-attachment"
        :style="{ display: type == 'card' ? 'inline-block' : '' }"
    >
        <div v-if="type == 'card'" @click="handleOpen">
            <slot>
                <div v-for="(v, i) in fileList" :key="i">
                    <div
                        :style="{ width: imageWidth, height: imageHeight }"
                        style="display: inline-block"
                        class="pointer"
                    >
                        <img style="width: 100%;" :src="thumbImage ? thumbImage : v" />
                    </div>
                    <div
                        :title="$lang('Remove')"
                        style="
                            color: red;
                            display: inline-block;
                            margin-left: 5px;
                        "
                        class="pointer iconfont iconshanchu3"
                        v-if="v"
                        @click.stop="handleDelete(v, i)"
                    ></div>
                </div>
                <div
                    style="
                        width: 50px;
                        height: 50px;
                        line-height: 50px;
                        border: 1px dotted #888;
                        border-radius: 2px;
                        text-align: center;
                        margin: auto;
                        color: #d3bebe;
                    "
                    class="pointer"
                    v-if="fileList.length == 0"
                >
                    <a-icon type="plus"></a-icon>
                </div>
            </slot>
        </div>
        <div v-else>
            <div class="input-group">
                <a-input
                    v-model="files"
                    class="form-control"
                    :placeholder="placeholder"
                ></a-input>
                <div class="input-group-append">
                    <a-button @click="handleOpen">{{
                        $lang("Choose")
                    }}</a-button>
                    <yi-upload
                        :show-group="false"
                        style="line-height: 100%"
                        v-model="files"
                        v-bind="{ ...$props, ...$attrs }"
                        v-on="$listeners"
                    >
                        <a-button type="danger">{{ $lang("Upload") }}</a-button>
                    </yi-upload>
                </div>
            </div>

            <div class="yi-upload-image-list" v-if="imagePreview">
                <div
                    class="yi-upload-image-item"
                    v-for="(item, index) in fileList"
                    :key="index"
                >
                    <div class="img" style="height: 120px">
                        <img :src="imageShow(item)" alt="" />
                    </div>
                    <div
                        @click="handleDelete(item, index)"
                        class="text-center pointer"
                        style="
                            height: 20px;
                            line-height: 20px;
                            border-radius: 4px;
                            background-color: #df4646;
                            color: #fff;
                            font-size: 13px;
                        "
                    >
                        {{ $lang("Delete") }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    name: "yi-attachment",
    data: function () {
        return {
            files: "",
        };
    },
    props: {
        value: {
            default: "",
        },
        type: {
            default: "default",
        },
        thumbImage: {
            default: ''
        },
        multiple: {
            default: false,
        },
        placeholder: {
            default: "",
        },
        showDelete: {
            default: false,
        },
        imageWidth: {
            default: "50px",
        },
        imageHeight: {
            default: "50px",
        },
        imagePreview: {
            default: true
        },
        imageShow: {
            type: Function,
            default: function(v) {
                return v;
            }
        },
        accept: {
            default: '*'
        }
    },
    computed: {
        fileList: function () {
            return this.files == "" ? [] : this.files.split(",");
        },
    },
    watch: {
        files: function (v) {
            this.$emit("input", v);
        },
        value: function (v) {
            this.files = v;
        },
    },
    mounted: function () {
        this.files = this.value;
    },
    methods: {
        handleDelete: function (item, index) {
            var list = [];
            for (var i = 0; i < this.fileList.length; i++) {
                if (index !== i) list.push(this.fileList[i]);
            }
            this.files = list.join(",");
        },
        handleOpen: function () {
            var self = this;
            Yi.open(
                {
                    title: $lang("Choose Image"),
                    content:
                        "/system/admin/attachment/select?multiple=" +
                        this.multiple + '&accept=' + this.accept,
                },
                function (data) {
                    if (self.multiple) {
                        let urls = [];
                        for (var i = 0; i < data.data.rows.length; i++) {
                            urls.push(data.data.rows[i].url);
                        }
                        if (!!self.files.trim())
                            self.files = self.files
                                .split(",")
                                .concat(urls)
                                .join(",");
                        else self.files = urls.join(",");
                    } else self.files = data.data.url;
                }
            );
        },
    },
};
</script>
<style scoped>
.yi-attachment .yi-upload-image-list .yi-upload-image-item {
    display: table-cell;
    text-align: center;
    width: 120px;
    height: 150px;
    padding: 2px;
    margin: 2px;
    border: 1px solid #ddd;
    border-radius: 5px;
    vertical-align: middle;
}

.yi-attachment .img {
    height: 120px;
}
.yi-attachment .img img {
    max-height: 120px;
}
</style>
