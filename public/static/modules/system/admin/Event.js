define([], function() {
    var Action = {        
        index: function() {
            Yi.vue.mixin(Mixins.table);
            var self;
            var columns = [
                { title: 'ID', dataIndex: 'id', key: 'id', customRender: Yi.render.html, }, 
                { title: $lang('Group'), dataIndex: 'group', key: 'group', customRender: Yi.render.html, }, 
                { title: $lang('Event'), dataIndex: 'event', key: 'event', customRender: Yi.render.html, }, 
                { title: $lang('Description'), dataIndex: 'event_desc', key: 'event_desc', customRender: Yi.render.html, }, 
                
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
                            page: 1, page_size: 10, order: "id ASC"
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
                        this.$http.get(get_url('index'), {params: params}).then(function(data) {
                            self.loading = false;
                            data = Yi.event.listen(EventPrefix + 'Init', data);
                            
                            self.data = data.data;
                            self.pagination.total = data.total;
                            
                        }).catch(function() {
                            self.loading = false;
                        });
                    },
                    handlePageChange: function(page) {
                        this.query.page = page;
                        this.init();
                    },
                    onSelectChange: function(selectedRowKeys, selectedRows) {
                        this.selectedRowKeys = selectedRowKeys;
                        this.selectedRows = selectedRows;
                    },
                    handleAdd: function() {                        
                        Yi.open({
                            title: $lang('Add'),
                            content: get_url('add')
                        }, function(data) {
                            if (data) self.init();
                        })
                    },
                    handleEdit: function(row) {                        
                        Yi.open({
                            title: $lang('Edit'),
                            content: get_url('edit') + '?id=' + row.id
                        }, function(data) {
                            if (data) self.init();
                        });
                    },
                    handleDeleteBatch: function() {                        
                        self.del(this.ids, function() {
                            self.init();
                        });
                    },
                    handleDelete: function(row) {
                        
                        self.del([row.id], function() {
                            self.init();
                        });
                    },
                }
            };
            return Yi.event.listen(EventPrefix + 'Option', option);
        },
    };

    return Action;
});