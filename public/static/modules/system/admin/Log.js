define([], function() {
    var Action = {        
        index: function() {
            Yi.vue.mixin(Mixins.table);
            var self;
            var columns = [
                { title: 'ID', dataIndex: 'id', key: 'id', customRender: Yi.render.html, }, 
                { title: $lang('User ID'), dataIndex: 'user_id', key: 'user_id', customRender: Yi.render.html, search: {
                    type: 'input'
                } }, 
                { title: $lang('Username'), dataIndex: 'username', key: 'username', customRender: Yi.render.html, search: {
                    type: 'input', s: 'like'
                }}, 
                { title: $lang('URL'), dataIndex: 'url', key: 'url', customRender: Yi.render.html, width: '150px', search: {
                    type: 'input', s: 'like'
                } }, 
                { title: $lang('Title'), dataIndex: 'title', key: 'title', customRender: Yi.render.html, width: '100px', visible: false}, 
                { title: $lang('Content'), dataIndex: 'content', key: 'content', customRender: Yi.render.html, width: '200px', }, 
                { title: $lang('Mehotd'), dataIndex: 'method', key: 'method', customRender: Yi.render.html, }, 
                { title: $lang('Type'), dataIndex: 'type', key: 'type', customRender: Yi.render.html, search: {
                    type: 'input'
                }}, 
                { title: $lang('App'), dataIndex: 'app', key: 'app', customRender: Yi.render.html, search: {
                    type: 'remoteSelect', value: null, auto: true, option: { paginate: false, labelField: 'title', valueField: 'name', url: '/system/admin/index/getApps', getData: function(_data) {
                        _data = [{name: null, title: $lang('All')}].concat(_data);
                        return _data;
                    }}
                }}, 
                { title: $lang('UserAgent'), dataIndex: 'useragent', key: 'useragent', width: '150px', customRender: function(val, row, index, column) {
                    var h = $vm.$createElement;
                    return h('a-tooltip', {
                        props: {
                            title: val
                        }
                    }, [h('div', {
                        style: 'width: 150px',
                        class: 'line-1'
                    }, row.user_agent_txt || val)]);
                }, width: '200px', }, 
                { title: $lang('Referer'), dataIndex: 'referer', key: 'referer', width: '150px', customRender: function(val, row, index, column) {
                    var h = $vm.$createElement;
                    return h('a-tooltip', {
                        props: {
                            title: val
                        }
                    }, [h('div', {
                        style: 'width: 150px',
                        class: 'line-1'
                    }, row.user_agent_txt || val)]);
                }, width: '200px', }, 
                { title: $lang('IP'), dataIndex: 'ip', key: 'ip', customRender: function(val, row, index, column) {
                    var el = val;
                    if (row.ip_txt) el = row.ip_txt + ' [' + val + ']';
                    return Yi.render.html(val, row, index, column, el);
                }, }, 
                { title: $lang('Created At'), dataIndex: 'created_at', key: 'created_at', customRender: Yi.render.date, search: {
                    type: 'date'
                }}, 
                
                { title: $lang('Operate'),key: 'action$',scopedSlots: { customRender: 'action' }, visible: false},
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
        }
    };

    return Yi.event.listen(EventPrefix + 'Action', Action);
});