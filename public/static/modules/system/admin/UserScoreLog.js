define([], function() {
    
    var Action = {        
        index: function() {
            Yi.vue.mixin(Mixins.table);
            var self;
            var columns = [
                { title: 'id', dataIndex: 'id', key: 'id', align: 'center', customRender: Yi.render.html, }, 
                { title: $lang('User ID'), dataIndex: 'user_id', key: 'user_id', align: 'center', customRender: Yi.render.html, }, 
                { title: $lang('Score'), dataIndex: 'score', key: 'score', align: 'center', customRender: Yi.render.html, }, 
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
                            title: '添加',
                            content: get_url('add')
                        }, function(data) {
                            if (data) self.init();
                        })
                    },
                    handleEdit: function(row) {                        
                        Yi.open({
                            title: '编辑',
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
        add: function() {
            Yi.vue.mixin(Mixins.form);
            var self;
            var option = {
                template: '#app',
                data: function() {
                    var data = {
                        form: {},
                        rules: {
                            user_id: [
                                {"required":true,"message":"请输入会员ID","trigger":"blur"},
                            ], 
                            score: [
                                {"required":true,"message":"请输入变更积分","trigger":"blur"},
                            ], 
                            before: [
                                {"required":true,"message":"请输入变更前积分","trigger":"blur"},
                            ], 
                            after: [
                                {"required":true,"message":"请输入变更后积分","trigger":"blur"},
                            ], 
                        },
                        btnLoading: false,
                        tabs: {"base":"基本"},
                        activeTab: 'base'
                    }
                    return Yi.event.listen(EventPrefix + 'Data', data);
                },
                mounted: function() {
                    self = this;
                    this.reset();
                },
                methods: {
                    reset: function() {
                        var form = {
                            user_id: 0,
                            score: 0,
                            before: 0,
                            after: 0,
                            memo: '',
                        };
                        this.form = Yi.event.listen(EventPrefix + 'ResetForm', form);
                    },
                }
            };
            return Yi.event.listen(EventPrefix + 'Option', option);
        },
        edit: function() {
            var self;
            var option = {
                template: '#app',
                data: function() {
                    var data = {
                        form: {
                            user_id: 0,
                            score: 0,
                            before: 0,
                            after: 0,
                            memo: '',
                        },
                        rules: {
                            user_id: [
                                {"required":true,"message":"请输入会员ID","trigger":"blur"},
                            ], 
                            score: [
                                {"required":true,"message":"请输入变更积分","trigger":"blur"},
                            ], 
                            before: [
                                {"required":true,"message":"请输入变更前积分","trigger":"blur"},
                            ], 
                            after: [
                                {"required":true,"message":"请输入变更后积分","trigger":"blur"},
                            ], 
                        },
                        btnLoading: false,
                        id: '',
                        tabs: {"base":"基本"},
                        activeTab: 'base'
                    }
                    return Yi.event.listen(EventPrefix + 'Data', data);
                },
                mounted: function() {
                    self = this;
                    this.id = Yi.getQuery('id');
                    this.init();
                },
            };
            return Yi.event.listen(EventPrefix + 'Option', option);
        },
        select: function() {
            Yi.vue.mixin(Mixins.table);
            Yi.vue.mixin(Mixins.select); 
            var self;           
            var columns = [
                { title: 'id', dataIndex: 'id', key: 'id', align: 'center', customRender: Yi.render.html, }, 
                { title: '会员ID', dataIndex: 'user_id', key: 'user_id', align: 'center', customRender: Yi.render.html, }, 
                { title: '变更积分', dataIndex: 'score', key: 'score', align: 'center', customRender: Yi.render.html, }, 
                { title: '变更前积分', dataIndex: 'before', key: 'before', align: 'center', customRender: Yi.render.html, }, 
                { title: '变更后积分', dataIndex: 'after', key: 'after', align: 'center', customRender: Yi.render.html, }, 
                { title: '备注', dataIndex: 'memo', key: 'memo', align: 'center', customRender: Yi.render.html, }, 
                { title: '创建时间', dataIndex: 'created_at', key: 'created_at', align: 'center', customRender: Yi.render.date, }, 
                
                { title: '操作',key: 'action$',scopedSlots: { customRender: 'action' },},
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

    return Yi.event.listen(EventPrefix + 'Action', Action);
});