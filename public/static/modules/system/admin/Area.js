define([], function() {
    var Action = {        
        index: function() {
            Yi.vue.mixin(Mixins.table);
            Yi.vue.mixin(Mixins.table);
            var columns = [
                { title: 'ID', dataIndex: 'id', key: 'id',  }, 
                { title: $lang('Parent ID'), dataIndex: 'pid', key: 'pid',  }, 
                { title: $lang('Short Name'), dataIndex: 'shortname', key: 'shortname',  }, 
                { title: $lang('Name'), dataIndex: 'name', key: 'name',  }, 
                { title: $lang('Long Name'), dataIndex: 'mergename', key: 'mergename',  }, 
                { title: $lang('Level'), dataIndex: 'level', key: 'level',  option: { 0: '0', 1: '1', 2: '2', 3: '3' },  customRender: Yi.render.option, }, 
                { title: $lang('Pinyin'), dataIndex: 'pinyin', key: 'pinyin',  }, 
                { title: $lang('Code'), dataIndex: 'code', key: 'code',  }, 
                { title: $lang('Zip'), dataIndex: 'zip', key: 'zip',  }, 
                { title: $lang('First'), dataIndex: 'first', key: 'first',  }, 
                { title: $lang('Lng'), dataIndex: 'lng', key: 'lng',  }, 
                { title: $lang('Lat'), dataIndex: 'lat', key: 'lat',  }, 
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
                            that.data = data.data;
                            that.pagination.total = data.total;
                            Yi.event.listen(EventPrefix + 'Init', data);
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
            Yi.vue.mixin(Mixins.table);
            Yi.vue.mixin(Mixins.select);
            var option = {
                template: '#app',
                data: function() {
                    return {
                        columns:[
                            { title: 'ID', dataIndex: 'id', key: 'id',  }, 
                            { title: $lang('Parent ID'), dataIndex: 'pid', key: 'pid',  }, 
                            { title: $lang('Short Name'), dataIndex: 'shortname', key: 'shortname',  }, 
                            { title: $lang('Name'), dataIndex: 'name', key: 'name',  }, 
                            { title: $lang('Long Name'), dataIndex: 'mergename', key: 'mergename',  }, 
                            { title: $lang('Level'), dataIndex: 'level', key: 'level',  option: { 0: '0', 1: '1', 2: '2', 3: '3' },  customRender: Yi.render.option, }, 
                            { title: $lang('Pinyin'), dataIndex: 'pinyin', key: 'pinyin',  }, 
                            { title: $lang('Code'), dataIndex: 'code', key: 'code',  }, 
                            { title: $lang('Zip'), dataIndex: 'zip', key: 'zip',  }, 
                            { title: $lang('First'), dataIndex: 'first', key: 'first',  }, 
                            { title: $lang('Lng'), dataIndex: 'lng', key: 'lng',  }, 
                            { title: $lang('Lat'), dataIndex: 'lat', key: 'lat',  }, 
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
                            that.data = data.data;
                            that.pagination.total = data.total;
                            typeof that.afterInit == 'function' && that.afterInit(data);
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