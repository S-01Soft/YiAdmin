define([], function() {
    var Action = {        
        index: function() {
            Yi.vue.mixin(Mixins.table);
            var columns = [
                { title: 'id', dataIndex: 'id', key: 'id',  }, 
                { title: $lang('Type'), dataIndex: 'type', key: 'type',  }, 
                { title: $lang('Pid'), dataIndex: 'pid', key: 'pid',  }, 
                { title: $lang('Name'), dataIndex: 'name', key: 'name',  }, 
                { title: $lang('Title'), dataIndex: 'title', key: 'title',  }, 
                { title: $lang('Icon'), dataIndex: 'icon', key: 'icon',  }, 
                { title: $lang('Condition'), dataIndex: 'condition', key: 'condition',  }, 
                { title: $lang('Remark'), dataIndex: 'remark', key: 'remark',  }, 
                { title: $lang('Is Menu'), dataIndex: 'ismenu', key: 'ismenu',  }, 
                { title: $lang('Created At'), dataIndex: 'created_at', key: 'created_at', customRender: Yi.render.date, }, 
                { title: $lang('Updated At'), dataIndex: 'updated_at', key: 'updated_at', customRender: Yi.render.date, }, 
                { title: $lang('Weigh'), dataIndex: 'weigh', key: 'weigh',  }, 
                { title: $lang('Status'), dataIndex: 'status', key: 'status', customRender: Yi.render.switch, }, 
                { title: $lang('App'), dataIndex: 'app', key: 'app',  }, 
                { title: $lang('Parent'), dataIndex: 'parent_rule', key: 'parent_rule',  }, 
                { title: $lang('App Type'), dataIndex: 'app_type', key: 'app_type',  }, 
                { title: $lang('Parent'), dataIndex: 'pidC_name', key: 'pid_c.name', customRender: Yi.render.html},
                { title: $lang('Operate'),key: 'action$',scopedSlots: { customRender: 'action' },},
            ];
            var option = {
                template: '#app',
                data: function() {
                    return {
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
                    }
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
                    },
                }
            };
            return option;
        },
        select: function() {
            Yi.vue.mixin(Mixins.table);
            Yi.vue.mixin(Mixins.select);
            var option = {
                template: '#app',
                data: function() {
                    return {
                        columns:[
                            { title: 'id', dataIndex: 'id', key: 'id',  }, 
                            { title: $lang('Type'), dataIndex: 'type', key: 'type',  }, 
                            { title: $lang('Pid'), dataIndex: 'pid', key: 'pid',  }, 
                            { title: $lang('Name'), dataIndex: 'name', key: 'name',  }, 
                            { title: $lang('Title'), dataIndex: 'title', key: 'title',  }, 
                            { title: $lang('Icon'), dataIndex: 'icon', key: 'icon',  }, 
                            { title: $lang('Condition'), dataIndex: 'condition', key: 'condition',  }, 
                            { title: $lang('Remark'), dataIndex: 'remark', key: 'remark',  }, 
                            { title: $lang('Is Menu'), dataIndex: 'ismenu', key: 'ismenu',  }, 
                            { title: $lang('Created At'), dataIndex: 'created_at', key: 'created_at', customRender: Yi.render.date, }, 
                            { title: $lang('Updated At'), dataIndex: 'updated_at', key: 'updated_at', customRender: Yi.render.date, }, 
                            { title: $lang('Weigh'), dataIndex: 'weigh', key: 'weigh',  }, 
                            { title: $lang('Status'), dataIndex: 'status', key: 'status', customRender: Yi.render.switch, }, 
                            { title: $lang('App'), dataIndex: 'app', key: 'app',  }, 
                            { title: $lang('Parent'), dataIndex: 'parent_rule', key: 'parent_rule',  }, 
                            { title: $lang('App Type'), dataIndex: 'app_type', key: 'app_type',  }, 
                            { title: $lang('Parent'), dataIndex: 'pidC_name', key: 'pid_c.name', customRender: Yi.render.html},
                            { title: $lang('Operate'),key: 'action$',scopedSlots: { customRender: 'action' },},
                        ],
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
            return option;
        },
    };

    return Action;
});