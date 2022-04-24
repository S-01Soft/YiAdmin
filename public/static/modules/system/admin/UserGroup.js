define([], function() {
    var Action = {        
        index: function() {
            Yi.vue.mixin(Mixins.table);
            var columns = [
                { title: 'ID', dataIndex: 'id', key: 'id', customRender: Yi.render.html, }, 
                { title: $lang('Name'), dataIndex: 'name', key: 'name', customRender: Yi.render.html, }, 
                { title: $lang('Created At'), dataIndex: 'created_at', key: 'created_at', customRender: Yi.render.date, }, 
                { title: $lang('Updated At'), dataIndex: 'updatetime', key: 'updatetime', customRender: Yi.render.date, }, 
                { title: $lang('Status'), dataIndex: 'status', key: 'status', customRender: Yi.render.switch, }, 
                { title: $lang('Parent'), dataIndex: 'pidC_name', key: 'pid_c.name', customRender: Yi.render.html},
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
                        this.$http.get(get_url('tree_list?type=1'), {params: params}).then(function(data) {
                            that.loading = false;
                            data = Yi.event.listen(EventPrefix + 'Init', data);
                            
                            that.data = data;
                            
                        }).catch(function() {
                            that.loading = false;
                        });
                    },
                }
            };
            return Yi.event.listen(EventPrefix + 'Option', option);
        },
        add: function() {
            Yi.vue.mixin(Mixins.form);
            var option = {
                template: '#app',
                data: function() {
                    var data = {
                        form: {},
                        rules: {
                            pid: [
                                {"required":true,"message":$lang(':attribute require', {attribute: $lang('Parent')}),"trigger":"blur"},
                            ], 
                            name: [
                                {"required":true,"message":$lang(':attribute require', {attribute: $lang('Name')}),"trigger":"blur"},
                            ], 
                            status: [
                                {"required":true,"message":$lang(':attribute require', {attribute: $lang('Status')}),"trigger":"blur"},
                            ], 
                        },
                        btnLoading: false,
                        tabs: {"base":$lang('Base')},
                        activeTab: 'base'
                    }
                    return Yi.event.listen(EventPrefix + 'Data', data);
                },
                mounted: function() {
                    this.reset();
                },
                methods: {
                    reset: function() {
                        var form = {
                            pid: 0,
                            name: '',
                            rules: '',
                            status: 1,
                        };
                        this.form = Yi.event.listen(EventPrefix + 'ResetForm', form);
                    },
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
                            pid: 0,
                            name: '',
                            rules: '',
                            status: 1,
                        },
                        rules: {
                            pid: [
                                {"required":true,"message":$lang(':attribute require', {attribute: $lang('Parent')}),"trigger":"blur"},
                            ], 
                            name: [
                                {"required":true,"message":$lang(':attribute require', {attribute: $lang('Name')}),"trigger":"blur"},
                            ], 
                            status: [
                                {"required":true,"message":$lang(':attribute require', {attribute: $lang('Status')}),"trigger":"blur"},
                            ], 
                        },
                        btnLoading: false,
                        id: '',
                        tabs: {"base":$lang('Base')},
                        activeTab: 'base'
                    }
                    return Yi.event.listen(EventPrefix + 'Data', data);
                },
                mounted: function() {
                    this.id = Yi.getQuery('id');
                    this.init();
                },
                methods: {
                    onSubmit: function() {
                        var that = this;
                        this.$refs.ruleForm.validate(function(valid) {
                            if (valid) that.submit();
                            else return false;
                        });
                    },
                    submit: function() {
                        var that = this;
                        this.btnLoading = true;
                        this.$http.post(get_url('edit'), {id: this.id, form: this.form}, {loading: true}).then(function(data) {
                            Yi.closeSelf(data);
                            that.btnLoading = false;
                        }).catch(function() {
                            that.btnLoading = false;
                        });
                    },
                    init: function() {
                        var that = this;
                        this.$http.get(get_url('edit'), {params: {id: this.id}}).then(function(data) {
                            that.form = Yi.event.listen(EventPrefix + 'Init', data);
                        });
                    },
                }
            };
            return Yi.event.listen(EventPrefix + 'Option', option);
        },
        select: function() {
            Yi.vue.mixin(Mixins.table);
            Yi.vue.mixin(Mixins.select);            
            var columns = [
                { title: 'ID', dataIndex: 'id', key: 'id', customRender: Yi.render.html, }, 
                { title: $lang('Name'), dataIndex: 'name', key: 'name', customRender: Yi.render.html, }, 
                { title: $lang('Created At'), dataIndex: 'created_at', key: 'created_at', customRender: Yi.render.date, }, 
                { title: $lang('Updated At'), dataIndex: 'updatetime', key: 'updatetime', customRender: Yi.render.date, }, 
                { title: $lang('Status'), dataIndex: 'status', key: 'status', customRender: Yi.render.switch, }, 
                { title: $lang('Parent'), dataIndex: 'pidC_name', key: 'pid_c.name', customRender: Yi.render.html},
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