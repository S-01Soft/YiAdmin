define([], function() {
    
    var Action = {        
        index: function() {
            Yi.vue.mixin(Mixins.table);
            var option = {
                template: '#app',
                data: function() {
                    return {
                        columns:[
                            { title: 'id', dataIndex: 'id', key: 'id',  }, 
                            { title: $lang('Class Name'), dataIndex: 'class_name', key: 'class_name',  }, 
                            { title: $lang('Description'), dataIndex: 'class_desc', key: 'class_desc', align: 'center'  }, 
                            { title: $lang('Module Name'), dataIndex: 'app_name', key: 'app_name', align: 'center'  }, 
                            { title: $lang('Sort'), dataIndex: 'sort', key: 'sort', align: 'center', customRender: Yi.render.edit  }, 
                            { title: $lang('Event'), dataIndex: 'event', key: 'event', align: 'center'  }, 
                            { title: $lang('App'), dataIndex: 'app_title', key: 'app_title', align: 'center'  }, 
                            { title: $lang('Status'), dataIndex: 'status', key: 'status', customRender: Yi.render.switch, align: 'center' }, 
                            
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
                            page: 1, page_size: 10, order: "sort DESC,id ASC"
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
                            that.data = data.data;
                            that.pagination.total = data.total;
                        }).catch(function() {
                            that.loading = false;
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
                    handleDeleteBatch: function() {
                        var that = this;
                        that.del(this.ids, function() {
                            that.init();
                        });
                    },
                    handleDelete: function(row) {
                        var that = this;
                        that.del([row.id], function() {
                            that.init();
                        });
                    },
                }
            };
            return option;
        },
    };

    return Action;
});