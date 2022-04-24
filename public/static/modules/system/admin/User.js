define([], function() {
    var Action = {
        index: function() {
            Yi.vue.mixin(Mixins.table);
            var columns = [
                { title: 'ID', dataIndex: 'id', key: 'id', customRender: Yi.render.html, }, 
                { title: $lang('Username'), dataIndex: 'username', key: 'username', customRender: Yi.render.html, }, 
                { title: $lang('Nickname'), dataIndex: 'nickname', key: 'nickname', customRender: Yi.render.html, }, 
                { title: $lang('Avatar'), dataIndex: 'avatar_url', key: 'avatar_url', customRender: Yi.render.image, }, 
                { title: $lang('Mobile'), dataIndex: 'mobile', key: 'mobile', customRender: Yi.render.html, }, 
                { title: $lang('Email'), dataIndex: 'email', key: 'email', customRender: Yi.render.html, }, 
                { title: $lang('Money'), dataIndex: 'money', key: 'money', customRender: Yi.render.html, }, 
                { title: $lang('Score'), dataIndex: 'score', key: 'score', customRender: Yi.render.html, }, 
                { title: $lang('Login Failure'), dataIndex: 'loginfailure', key: 'loginfailure', customRender: Yi.render.html, }, 
                { title: $lang('Login Time'), dataIndex: 'logintime', key: 'logintime', customRender: Yi.render.date, }, 
                { title: $lang('Login IP'), dataIndex: 'loginip', key: 'loginip', customRender: Yi.render.html, }, 
                { title: $lang('Created At'), dataIndex: 'created_at', key: 'created_at', customRender: Yi.render.date, }, 
                { title: $lang('Status'), dataIndex: 'status', key: 'status', customRender: Yi.render.switch, }, 
                
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
                            username: [
                                {"required":true,"message":$lang(':attribute require', {attribute: $lang('Username')}),"trigger":"blur"},
                            ], 
                            nickname: [
                                {"required":true,"message":$lang(':attribute require', {attribute: $lang('Nickname')}),"trigger":"blur"},
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
                            username: '',
                            nickname: '',
                            password: '',
                            avatar: '',
                            email: '',
                            mobile: '',
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
                            username: '',
                            nickname: '',
                            password: '',
                            avatar: '',
                            mobile: '',
                            email: '',
                            status: 1,
                        },
                        rules: {
                            username: [
                                {"required":true,"message":$lang(':attribute require', {attribute: $lang('Username')}),"trigger":"blur"},
                            ], 
                            nickname: [
                                {"required":true,"message":$lang(':attribute require', {attribute: $lang('Nickname')}),"trigger":"blur"},
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
            };
            return Yi.event.listen(EventPrefix + 'Option', option);
        },
        select: function() {
            Yi.vue.mixin(Mixins.table);
            Yi.vue.mixin(Mixins.select);            
            var columns = [
                { title: 'ID', dataIndex: 'id', key: 'id', customRender: Yi.render.html, }, 
                { title: $lang('Username'), dataIndex: 'username', key: 'username', customRender: Yi.render.html, }, 
                { title: $lang('Nickname'), dataIndex: 'nickname', key: 'nickname', customRender: Yi.render.html, }, 
                { title: $lang('Avatar'), dataIndex: 'avatar', key: 'avatar', customRender: Yi.render.image, }, 
                { title: $lang('Mobile'), dataIndex: 'mobile', key: 'mobile', customRender: Yi.render.html, }, 
                { title: $lang('Email'), dataIndex: 'email', key: 'email', customRender: Yi.render.html, }, 
                { title: $lang('Money'), dataIndex: 'money', key: 'money', customRender: Yi.render.html, }, 
                { title: $lang('Score'), dataIndex: 'score', key: 'score', customRender: Yi.render.html, }, 
                { title: $lang('Login Failure'), dataIndex: 'loginfailure', key: 'loginfailure', customRender: Yi.render.html, }, 
                { title: $lang('Login Time'), dataIndex: 'logintime', key: 'logintime', customRender: Yi.render.date, }, 
                { title: $lang('Login IP'), dataIndex: 'loginip', key: 'loginip', customRender: Yi.render.html, }, 
                { title: $lang('Created At'), dataIndex: 'created_at', key: 'created_at', customRender: Yi.render.date, }, 
                { title: $lang('Status'), dataIndex: 'status', key: 'status', customRender: Yi.render.switch, }, 
                
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