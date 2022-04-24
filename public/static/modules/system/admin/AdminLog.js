define([], function() {
    
    var Action = {
        index: function() {
            var self;
            var columns = [
                { title: 'ID', dataIndex: 'id', key: 'id', customRender: Yi.render.html, }, 
                { title: $lang('AdminId'), dataIndex: 'admin_id', key: 'admin_id', customRender: Yi.render.html, }, 
                { title: $lang('Username'), dataIndex: 'username', key: 'username', customRender: Yi.render.html, }, 
                { title: $lang('Url'), dataIndex: 'url', key: 'url', customRender: Yi.render.html, width: '200px'}, 
                { title: $lang('Title'), dataIndex: 'title', key: 'title', customRender: Yi.render.html, width: '100px', }, 
                { title: $lang('Content'), dataIndex: 'content', key: 'content', customRender: Yi.render.html, width: '200px'}, 
                { title: $lang('Method'), dataIndex: 'method', key: 'method', customRender: Yi.render.html, }, 
                { title: $lang('AppType'), dataIndex: 'app_type', key: 'app_type', customRender: Yi.render.html, }, 
                { title: $lang('App'), dataIndex: 'app', key: 'app', customRender: Yi.render.html, }, 
                { title: $lang('Useragent'), dataIndex: 'useragent', key: 'useragent', customRender: Yi.render.html, width: '200px', }, 
                { title: $lang('Ip'), dataIndex: 'ip', key: 'ip', customRender: Yi.render.html, }, 
                { title: $lang('CreateTime'), dataIndex: 'created_at', key: 'created_at', customRender: Yi.render.date, }, 
                
                { title: $lang('Operate'),key: 'action$',scopedSlots: { customRender: 'action' },},
            ];
            var option = {
                template: '#app',
                data: function() {
                    var data = {
                        columns: Yi.event.listen(EventPrefix + 'Columns', columns),
                        data: [],
                        pagination: {
                            current_page: 1, total: 0
                        },
                        selectedRowKeys: [],
                        selectedRows: [],
                        loading: false,
                        query: {
                            page: 1, page_size: 10, order: "id DESC"
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
                },
                mounted: function() {
                    self = this;
                    this.init();
                },
                methods: {
                    init: function() {                        
                        self.loading = true;
                        var params = Yi.event.listen(EventPrefix + 'InitParams', this.query);
                        this.$http.get(Action.api.url('index'), {params: params}).then(function(data) {
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
                            content: Action.api.url('add')
                        }, function(data) {
                            if (data) self.init();
                        })
                    },
                    handleEdit: function(row) {                        
                        Yi.open({
                            title: $lang('Edit'),
                            content: Action.api.url('edit') + '?id=' + row.id
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
        api: {
            url: function(action) {
                return base_url + action;
            },
        }
    };

    return Yi.event.listen(EventPrefix + 'Action', Action);
});