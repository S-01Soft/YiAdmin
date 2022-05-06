define([], function() {
    var Action = {        
        index: function() {
            Yi.vue.mixin(Mixins.table);
            var self;
            var columns = [
                { title: 'ID', dataIndex: 'id', key: 'id', customRender: Yi.render.html, }, 
                { title: '管理员', dataIndex: 'user_id', key: 'user_id', customRender: Yi.render.html, }, 
                { title: '标识', dataIndex: 'key', key: 'key', customRender: Yi.render.html, }, 
                { title: '配置', dataIndex: 'data', key: 'data', customRender: Yi.render.html, }, 
                
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
                                {"required":true,"message":"请输入管理员","trigger":"blur"},
                            ], 
                            key: [
                                {"required":true,"message":"请输入标识","trigger":"blur"},
                            ], 
                            data: [
                                {"required":true,"message":"请输入配置","trigger":"blur"},
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
                            user_id: '',
                            key: '',
                            data: '',
                        };
                        this.form = Yi.event.listen(EventPrefix + 'ResetForm', form);
                    }
                }
            };
            return Yi.event.listen(EventPrefix + 'Option', option);
        },
        edit: function() {
            Yi.vue.mixin(Mixins.form);
            var option = {
                template: '#app',
                data: function() {
                    var data = {
                        form: {
                            user_id: '',
                            key: '',
                            data: '',
                        },
                        rules: {
                            user_id: [
                                {"required":true,"message":"请输入管理员","trigger":"blur"},
                            ], 
                            key: [
                                {"required":true,"message":"请输入标识","trigger":"blur"},
                            ], 
                            data: [
                                {"required":true,"message":"请输入配置","trigger":"blur"},
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
                    this.id = Yi.getQuery('id');
                    this.init();
                }
            };
            return Yi.event.listen(EventPrefix + 'Option', option);
        },
        select: function() {
            Yi.vue.mixin(Mixins.table);
            Yi.vue.mixin(Mixins.select)
            var self;           
            var columns = [
                { title: 'ID', dataIndex: 'id', key: 'id', customRender: Yi.render.html, }, 
                { title: '管理员', dataIndex: 'user_id', key: 'user_id', customRender: Yi.render.html, }, 
                { title: '标识', dataIndex: 'key', key: 'key', customRender: Yi.render.html, }, 
                { title: '配置', dataIndex: 'data', key: 'data', customRender: Yi.render.html, }, 
                
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
        }
    };

    return Yi.event.listen(EventPrefix + 'Action', Action);
});