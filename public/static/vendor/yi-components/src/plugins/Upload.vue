<template id="yi-upload">
    <div class="yi-upload">
        <a-upload
            v-bind="{ ...$props, ...$attrs }"
            v-on="$listeners"
            slot="addonAfter"
            :action="action"
            name="file"
            :show-upload-list="false"
            :multiple="multiple"
            :custom-request="customRequest"
            @change="handleChange"
        >
            <slot></slot>
        </a-upload>
    </div>
</template>
<script>
export default {
    name: "yi-upload",
    data: function () {
        return {
            fileList: [],
            previewVisible: false,
            previewImage: "",
            defaultFileList: [],
            val: "",
            t_files: [],
            hideLoading: null,
        };
    },
    props: {
        max: {
            default: 1,
        },
        value: {
            default: "",
        },
        custom: {
            default: false,
        },
        action: {
            default: "/system/admin/attachment/upload",
        },
        multiple: {
            default: false,
        },
        loading: {
            default: true,
        },
        loadingText: {
            default: $lang("Loading"),
        },
        showError: {
            default: true
        }
    },
    watch: {
        value: function (v) {
            this.fileList = v == "" ? [] : v.split(",");
            this.val = v;
        },
        val: function (v) {
            this.$emit("input", v);
            this.$emit("change", v);
        },
    },
    methods: {
        customRequest: function(option) {
            var self = this;
            const file = option.file;
            const perSize = 2 * 1024 * 1024;
            const data = {
                url: this.action,
                method: 'POST',
                data: {},
                loading: false,
                contentType: false,
                processData: false,
                error: function(e) {
                    self.$message.error(e.statusText);
                    option.onError(e);
                }
            };
            var error = function(msg) {
                self.$message.error(msg);
            }
            if (file.size > perSize) {
                const count = Math.ceil(file.size / perSize);
                const chunk_id = 'i.' + Date.now() + Math.random();
                var i = 0;
                const upload = function() {
                    const formData = new FormData();
                    formData.append('file', file.slice(i * perSize, (i + 1) * perSize));
                    formData.append('index', i);
                    formData.append('count', count);
                    formData.append('chunk_id', chunk_id);
                    formData.append('name', file.name);
                    formData.append('mime_type', file.type);
                    data.data = formData;
                    data.success = function(res) {
                        if (res.code != 1 && self.showError) {
                            error(res.message);
                            option.onError(res);
                        } else {
                            i ++;
                            if (res.data == 'continue') upload();
                            else option.onSuccess(res);
                        }
                    }
                    $.ajax(data);
                }
                upload();
            } else {
                const formData = new FormData();
                formData.append('file', file);
                data.data = formData;
                data.success = function(res) {
                    if (res.code != 1 && self.showError) error(res.message)
                    option.onSuccess(res);
                }
                $.ajax(data);
            }
        },
        handleChange: function (data) {
            this.$emit('upload', data.file);
            if (this.loading) {
                var status = data.file.status;
                if (status === "uploading") {
                    if (!this.hideLoading)
                        this.hideLoading = this.$message.loading(
                            this.$lang(this.loadingText),
                            0
                        );
                } else {
                    typeof this.hideLoading == "function" && this.hideLoading();
                    this.hideLoading = null;
                }
            }
            var fileList = data.fileList;
            fileList = fileList.filter(function (item) {
                return item.status !== undefined;
            });
            var files = [];
            for (var i = 0; i < fileList.length; i++) {
                var file = fileList[i];
                if (file.response) {
                    if (file.response.code == 1) {
                        file.url = file.response.data;
                    }
                    if (this.t_files.indexOf(file.uid) == -1) {
                        this.t_files.push(file.uid);
                        this.$emit("response", file.response);
                    }
                }
                fileList[i] = file;
                if (this.multiple) files.push(file.url);
                else files = [file.url]
            }
            this.fileList = files;
            this.val = files.join(",");
        },
    },
};
</script>