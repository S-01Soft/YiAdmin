define([], function() {
    var Action = {        
        index: function() {
            Yi.vue.mixin(Mixins.table);
            var self;
            var columns = [
                { title: 'id', dataIndex: 'id', key: 'id', customRender: Yi.render.html, }, 
                { title: $lang('Title'), dataIndex: 'title_txt', key: 'title_txt', customRender: function(val, row, index, column) {
                    var el = (row.spacer ? row.spacer + '&nbsp;&nbsp;' : '') + row.title_txt;
                    return Yi.render.html(val, row, index, column, el);
                }, }, 
                { title: $lang('Weigh'), dataIndex: 'weigh', key: 'weigh', customRender: Yi.render.html, }, 
                { title: $lang('App'), dataIndex: 'app', key: 'app', customRender: Yi.render.html, }, 
                { title: $lang('Created At'), dataIndex: 'created_at', key: 'created_at', customRender: Yi.render.date, }, 
                { title: $lang('Status'), dataIndex: 'status', key: 'status', customRender: Yi.render.switch, }, 
                { title: $lang('Parent'), dataIndex: 'pidC_title', key: 'pid_c.title', customRender: Yi.render.html},
                { title: $lang('Operate'),key: 'action$',scopedSlots: { customRender: 'action' },},
            ];
            var option = {
                template: '#app',
                data: function() {
                    var data = {
                        columns: Yi.event.listen(EventPrefix + 'Columns', columns),
                        data: [],
                        pagination: {
                            total: 0, page_size_options: ['10', '20', '30', '40', '50']
                        },
                        selectedRowKeys: [],
                        selectedRows: [],
                        loading: false,
                        query: {
                            page: 1, page_size: 10, order: "weigh DESC, id DESC"
                        },
                    }
                    return Yi.event.listen(EventPrefix + 'Data', data);
                },
                mounted: function() {
                    self = this;
                    this.init();
                },
                methods: {
                    init: function() {                        
                        self.loading = true;
                        var params = Yi.event.listen(EventPrefix + 'InitParams', this.query);
                        this.$http.get(get_url('tree_list?type=1'), {params: params}).then(function(data) {
                            self.loading = false;
                            data = Yi.event.listen(EventPrefix + 'Init', data);
                            self.data = data;
                            
                        }).catch(function() {
                            self.loading = false;
                        });
                    },
                }
            };
            return Yi.event.listen(EventPrefix + 'Option', option);
        },
        select: function() {
            Yi.vue.mixin(Mixins.table);
            Yi.vue.mixin(Mixins.select);
            var self;           
            var columns = [
                { title: 'id', dataIndex: 'id', key: 'id', customRender: Yi.render.html, }, 
                { title: $lang('Title'), dataIndex: 'title_txt', key: 'title_txt', customRender: Yi.render.html, }, 
                { title: $lang('Weigh'), dataIndex: 'weigh', key: 'weigh', customRender: Yi.render.html, }, 
                { title: $lang('App'), dataIndex: 'app', key: 'app', customRender: Yi.render.html, }, 
                { title: $lang('App Type'), dataIndex: 'app_type', key: 'app_type', customRender: Yi.render.html, }, 
                { title: $lang('Created At'), dataIndex: 'created_at', key: 'created_at', customRender: Yi.render.date, }, 
                { title: $lang('Status'), dataIndex: 'status', key: 'status', customRender: Yi.render.switch, }, 
                { title: $lang('Parent'), dataIndex: 'pidC_title', key: 'pid_c.title', customRender: Yi.render.html},
                { title: $lang('Operate'),key: 'action$',scopedSlots: { customRender: 'action' },},
            ];
            var option = {
                template: '#app',
                data: function() {
                    var data = {
                        columns: Yi.event.listen(EventPrefix + 'Columns', columns),
                        data: [],
                        pagination: {
                            total: 0, page_size_options: ['10', '20', '30', '40', '50']
                        },
                        selectedRowKeys: [],
                        selectedRows: [],
                        loading: false,
                        query: {
                            page: 1, page_size: 10, order: "id DESC"
                        },
                        multiple: false
                    }
                    return Yi.event.listen(EventPrefix + 'Data', data);
                },
                mounted: function() {
                    self = this;
                    this.init();
                    this.multiple = Yi.getQuery('multiple');
                },
                methods: {
                    init: function() {                        
                        self.loading = true;
                        var params = Yi.event.listen(EventPrefix + 'InitParams', this.query);
                        this.$http.get(get_url('select'), {params: params}).then(function(data) {
                            self.loading = false;
                            data = Yi.event.listen(EventPrefix + 'Init', data);
                            self.data = data.data;
                            self.pagination.total = data.total;
                        }).catch(function() {
                            self.loading = false;
                        });
                    },
                }
            };
            return Yi.event.listen(EventPrefix + 'Option', option);
        },
    };

    return Action;
});