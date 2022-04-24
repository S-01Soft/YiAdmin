define([], function() {
    var Action = {        
        index: function() {
            Yi.vue.mixin(Mixins.table);
            var columns = [
                { title: 'ID', dataIndex: 'id', key: 'id', customRender: Yi.render.html, }, 
                { title: $lang('Path'), dataIndex: 'url', key: 'url', customRender: Yi.render.image, }, 
                { title: $lang('File Size'), dataIndex: 'filesize', key: 'filesize', customRender: Yi.render.html, }, 
                { title: $lang('Mime Type'), dataIndex: 'mimetype', key: 'mimetype', customRender: Yi.render.html, }, 
                { title: $lang('Created At'), dataIndex: 'created_at', key: 'created_at', customRender: Yi.render.date, }, 
                { title: $lang('Storage'), dataIndex: 'storage', key: 'storage', customRender: Yi.render.html, }, 
                { title: $lang('Public/Private'), dataIndex: 'type', key: 'type', customRender: Yi.render.html, }, 
                { title: $lang('Scene'), dataIndex: 'scene', key: 'scene', customRender: Yi.render.html, }, 
                { title: $lang('Group'), dataIndex: 'group', key: 'group', customRender: Yi.render.html, }, 
                { title: $lang('Admin'), dataIndex: 'admin_nickname', key: 'admin.nickname', customRender: Yi.render.html},
                { title: $lang('User'), dataIndex: 'user_nickname', key: 'user.nickname', customRender: Yi.render.html},
                { title: $lang('Operate'),key: 'action$',scopedSlots: { customRender: 'action' },},
            ];
            var option = {
                template: '#app',
                data: function() {
                    var data = {
                        columns: Yi.event.listen(EventPrefix + 'Columns', columns),
                        data: [],
                        pagination: {
                            total: 0, page_size_options: ['18', '36', '54', '72', '90']
                        },
                        selectedRowKeys: [],
                        selectedRows: [],
                        loading: false,
                        query: {
                            page: 1, page_size: 18, order: "id DESC"
                        },
                    }
                    return Yi.event.listen(EventPrefix + 'Data', data);
                },
                mounted: function() {
                    this.init();
                },
                methods: {
                    init: function() {
                        var that = this;
                        that.loading = true;
                        var params = Yi.event.listen(EventPrefix + 'InitParams', this.query);
                        this.$http.get(get_url('index'), {params: params}).then(function(data) {
                            that.loading = false;
                            data = Yi.event.listen(EventPrefix + 'Init', data);
                            
                            that.data = data.data;
                            that.pagination.total = data.total;
                            
                        }).catch(function() {
                            that.loading = false;
                        });
                    }
                }
            };
            return Yi.event.listen(EventPrefix + 'Option', option);
        },
        select: function() {
            Yi.vue.mixin(Mixins.table);
            Yi.vue.mixin(Mixins.select);
            var columns = [
                { title: 'ID', dataIndex: 'id', key: 'id', customRender: Yi.render.html, }, 
                { title: $lang('Path'), dataIndex: 'url', key: 'url', customRender: Yi.render.image, }, 
                { title: $lang('File Size'), dataIndex: 'filesize', key: 'filesize', customRender: Yi.render.html, }, 
                { title: $lang('Mime Type'), dataIndex: 'mimetype', key: 'mimetype', customRender: Yi.render.html, }, 
                { title: $lang('Created At'), dataIndex: 'created_at', key: 'created_at', customRender: Yi.render.date, }, 
                { title: $lang('Storage'), dataIndex: 'storage', key: 'storage', customRender: Yi.render.html, }, 
                { title: $lang('Public/Private'), dataIndex: 'type', key: 'type', customRender: Yi.render.html, }, 
                { title: $lang('Scene'), dataIndex: 'scene', key: 'scene', customRender: Yi.render.html, }, 
                { title: $lang('Group'), dataIndex: 'group', key: 'group', customRender: Yi.render.html, }, 
                { title: $lang('Admin'), dataIndex: 'admin_nickname', key: 'admin.nickname', customRender: Yi.render.html},
                { title: $lang('User'), dataIndex: 'user_nickname', key: 'user.nickname', customRender: Yi.render.html},
                { title: $lang('Operate'),key: 'action$',scopedSlots: { customRender: 'action' },},
            ];
            var option = {
                template: '#app',
                data: function() {
                    var data = {
                        columns: Yi.event.listen(EventPrefix + 'Columns', columns),
                        data: [],
                        pagination: {
                            total: 0, page_size_options: ['18', '36', '54', '72', '90']
                        },
                        selectedRowKeys: [],
                        selectedRows: [],
                        loading: false,
                        query: {
                            page: 1, page_size: 18, order: "id DESC"
                        },
                        multiple: false
                    }
                    return Yi.event.listen(EventPrefix + 'Data', data);
                },
                mounted: function() {
                    this.init();
                    this.multiple = Yi.getQuery('multiple');
                },
                methods: {
                    init: function() {
                        var that = this;
                        that.loading = true;
                        var params = Yi.event.listen(EventPrefix + 'InitParams', this.query);
                        this.$http.get(get_url('select'), {params: params}).then(function(data) {
                            that.loading = false;
                            data = Yi.event.listen(EventPrefix + 'Init', data);
                            that.data = data.data;
                            that.pagination.total = data.total;
                        }).catch(function() {
                            that.loading = false;
                        });
                    },
                }
            };
            return Yi.event.listen(EventPrefix + 'Option', option);
        },
    };

    return Action;
});