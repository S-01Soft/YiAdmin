define([], function() {
    
    var Action = {        
        index: function() {
            Yi.vue.mixin(Mixins.table);
            var self;
            var columns = [
                { title: 'id', dataIndex: 'id', key: 'id', align: 'center', customRender: Yi.render.html, }, 
                { title: $lang('User ID'), dataIndex: 'user_id', key: 'user_id', align: 'center', customRender: Yi.render.html, }, 
                { title: $lang('Money'), dataIndex: 'money', key: 'money', align: 'center', customRender: Yi.render.html, }, 
                { title: $lang('Before'), dataIndex: 'before', key: 'before', align: 'center', customRender: Yi.render.html, }, 
                { title: $lang('After'), dataIndex: 'after', key: 'after', align: 'center', customRender: Yi.render.html, }, 
                { title: $lang('Remark'), dataIndex: 'memo', key: 'memo', align: 'center', customRender: Yi.render.html, }, 
                { title: $lang('Created At'), dataIndex: 'created_at', key: 'created_at', align: 'center', customRender: Yi.render.date, }, 
                
                { title: $lang('Operate'),key: 'action$', align: 'center',scopedSlots: { customRender: 'action' },},
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
                            page: 1, page_size: 10, order: "id DESC",
                            where: {}
                        },
                    }
                    return Yi.event.listen(EventPrefix + 'Data', data);
                },
                computed: {
                    ids: function() {
                        var result = [];
                        for (var i = 0; i < this.selectedRows.length; i ++) {
                            result.push(this.selectedRows[i].id);
                        }
                        return result;
                    },
                    c_columns: function() {
                        var result = [];
                        for (var i = 0; i < this.columns.length; i ++) {
                            var item = this.columns[i];
                            if (item.visible || item.visible == undefined) result.push(item);
                        }
                        return result;
                    }
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
                            self.selectedRowKeys = [];
                            self.selectedRows = [];
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

    return Yi.event.listen(EventPrefix + 'Action', Action);
});