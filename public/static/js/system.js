function get_static_version()
{
    return Config.statics && Config.statics.open && Config.statics.version || (Config.debug ? Date.now() : '1.0.0');
}
require.config({
    baseUrl: '/static/',
    paths: {
        jquery: Yi.getStatic('jquery') || 'library/jquery/dist/jquery.min',
        vue: Yi.getStatic('vue') || 'library/vue/dist/vue.min',
        axios: Yi.getStatic('axios') || 'library/axios/dist/axios.min',
        lodash: Yi.getStatic('lodash') || 'library/lodash/dist/lodash.min',
        layer: Yi.getStatic('layer') || 'vendor/layer/layer',
        moment: Yi.getStatic('moment') || 'library/moment/min/moment.min',
        echarts: Yi.getStatic('echarts') || 'vendor/echarts-5.0.1/dist/echarts.min',
        antd: Yi.getStatic('antd') || 'ant/antd.min',
        locales: Yi.getStatic('antd-locales') || 'ant/antd.locales.min',
        clipboard: Yi.getStatic('clipboard') || 'vendor/clipboard.min',
        vm: 'js/vue',
        formatter: 'js/formatter.js?v=' + Config.version + '&r=' + get_static_version(),
        YiComponents: 'vendor/yi-components/dist/yi-components.js?v=' + Config.version + '&r=' + get_static_version(),
        lang: '../_lang?callback=define&m=' + Config.module + '&c=' + Config.controller + '&r=' + Config.langVersion,
        action: path + '.js?v=' + (Config.module == 'system' ? Config.version : Config.moduleVersion) + '&r=' + get_static_version()
    },
    packages: [
        {
            name: 'moment',
            location: 'library/moment',
            main: 'moment'
        }
    ],
    shim: {
        antd: {
            deps: ['vue', 'moment', 'locales'],
            exports: 'antd'
        },
        layer: {
            deps: ['jquery'],
            exports: 'layer'
        },
        YiComponents: {
            deps: ['vue', 'antd', 'lodash', 'moment'],
        }
    },
    map: {
        '*': {
            css: 'library/require-css/css.min'
        }
    },
    waitSeconds: 60,
});

window.$lang = Yi.lang.parse;
window.get_url = function(action) {
    return base_url + action;
}
window.Mixins = {
    table: {
        data: function() {
            return {
                layout: 'lg'
            }
        },
        computed: {
            c_columns: function() {
                var result = [];
                for (var i = 0; i < this.columns.length; i ++) {
                    var item = this.columns[i];
                    if (typeof item.visible == 'function' && item.visible(item)) {
                        result.push(item)
                    }
                    else if (item.visible === true || item.visible == undefined) {
                        result.push(item);
                    }
                }
                return result;
            },
            ids: function() {
                var result = [];
                for (var i = 0; i < this.selectedRows.length; i ++) {
                    result.push(this.selectedRows[i].id);
                }
                return result;
            }
        },
        watch: {
            'query.page_size': function(v) {
                this.init();
            },
            'query.page': function(v) {
                this.selectedRowKeys = [];
                this.selectedRows = [];
                this.init()
            }
        },
        methods: {
            handleGetWhere: function(where) {
                this.query.where = where;
                this.query.page = 1;
            },
            handleExports: function(setting) {
                var query = __.clone(this.query);
                var params = Yi.event.listen(EventPrefix + 'ExportsParams', query);
                params.fields = setting.fields;
                params.limit = setting.limit;
                params.title = setting.title;
                var form = document.createElement("form");
                form.style.display = "none";
                form.action = Yi.event.listen(EventPrefix + 'ExportUrl', get_url('exports'));
                form.method = "post";
                document.body.appendChild(form);
                for (var key in params) {
                    var input = document.createElement("input");
                    input.type = "hidden";
                    input.name = key;
                    input.value = typeof params[key] == 'object' ? JSON.stringify(params[key]) : params[key];
                    form.appendChild(input);
                }
                form.submit();
                form.remove();
            },
            del: function(ids, callback, key) {
                var self = this;
                var key = key || 'id';
                var where = {};
                where[key] = ['in', ids];
                where = Yi.event.listen(EventPrefix + 'DeleteWhere', where);
                var url = Yi.event.listen(EventPrefix + 'DeleteUrl', get_url('delete'));
                this.$http.post(url, {where: where}).then(function(res) {
                    self.selectedRowKeys = [];
                    callback && callback(res);
                });
            },
            onSelectChange: function(selectedRowKeys, selectedRows) {
                this.selectedRowKeys = selectedRowKeys;
                this.selectedRows = selectedRows;
            },
            handleAdd: function() {
                var self = this;
                var url = Yi.event.listen(EventPrefix + 'AddUrl', get_url('add'))
                Yi.open({
                    title: $lang('Add'),
                    content: url
                }, function(data) {
                    if (data) self.init();
                })
            },
            handleEdit: function(row) {
                var self = this;
                var url = get_url('edit') + '?id=' + row.id
                url = Yi.event.listen(EventPrefix + 'EditUrl', url);
                Yi.open({
                    title: $lang('Edit'),
                    content: url
                }, function(data) {
                    if (data) self.init();
                });
            },
            handleDeleteBatch: function() {
                var self = this;
                self.del(this.ids, function() {
                    self.selectedRowKeys = [];
                    self.selectedRows = [];
                    self.init();
                });
            },
            handleDelete: function(row) {
                var self = this;
                self.del([row.id], function() {
                    self.init();
                });
            },
            handleImportOk: function() {
                this.init();
            },
            toggle: function(row, params) {
                var pk = $vm.pk || 'id';
                var url = get_url('toggle') + '?' + pk + '=' + row[pk];
                url = Yi.event.listen(EventPrefix + 'ToggleUrl', url);
                params = Yi.event.listen(EventPrefix + 'ToggleParams', params);
                return this.$http.post(url, params);
            }
        }
    },
    form: {
        methods: {
            init: function() {
                var self = this;
                var url = Yi.event.listen(EventPrefix + 'InitUrl', get_url(Config.action));
                var form = Yi.event.listen(EventPrefix + 'InitForm', { params: { id: this.id } });
                this.$http.get(url, form).then(function(data) {
                    self.form = Yi.event.listen(EventPrefix + 'Init', data);
                });
            },
            onSubmit: function() {
                var self = this;
                var url = Yi.event.listen(EventPrefix + 'SubmitUrl', get_url(Config.action));
                var form = Yi.event.listen(EventPrefix + 'SubmitForm',{ form: self.form });
                this.$refs.ruleForm.validate(function(valid) {
                    if (valid) self.submit(url, form, function(data) {
                        Yi.closeSelf(data);
                    });
                    else return false;
                });
            },
            submit: function(url, form, success, error) {
                var self = this;                   
                this.btnLoading = true;
                this.$http.post(url, form, {loading: true}).then(function(data) {
                    self.btnLoading = false;
                    typeof success == 'function' && success(data)
                }).catch(function(e) {
                    self.btnLoading = false;
                    typeof error == 'function' && error(e);
                });
            },
            onCancel: function() {
                Yi.closeSelf();
            }
        }
    },
    select: {
        methods: {
            handleSelect: function() {
                Yi.closeSelf({
                    multiple: true,
                    data: {
                        ids: this.ids,
                        rows: this.selectedRows
                    }
                });
            },
            handleSelectOne: function(row) {
                var data = this.multiple ? {
                    multiple: true,
                    data: {
                        ids: [row.id],
                        rows: [row]
                    }
                } : {
                    multiple: false,
                    data: row
                }
                Yi.closeSelf(data);
            }
        }
    }
};

for (var i = 0; i < Yi._config.length; i ++) {
    require.config(Yi._config[i]);
}

Yi.require('action', 'layer', 'jquery', 'vm', 'lodash', 'moment', 'moment/locale/zh-cn', 'clipboard', 'lang');
require(Yi.modules(), function () {
    Yi.lang.load(require('lang'));
    window.antd = require('antd');
    window.Layer = require('layer');
    window.Vue = require('vm');
    window.Moment = window.moment = require('moment');
    Moment.locale(Config.lang);
    window.__ = require('lodash');
    var Action = require('action');
    var Locale = require('locales');
    window.Layout = {
        xs: 576, sm: 768, md: 992, lg: 1200, xl: 1600
    }
    Yi.load(Config.admin.requires, function() {
        Yi.init();
        if (Action[action]) {
            Yi.vue.mixin({
                data: function() {
                    return {
                        layout: 'lg'
                    }
                },
                mounted: function() {
                    $('#loading-block').hide();
                    var w = window.innerWidth
                    if (w <= Layout.xs) this.layout = 'xs';
                    else if (w <= Layout.sm) this.layout = 'sm';
                    else if (w <= Layout.md) this.layout = 'md';
                    else if (w <= Layout.lg) this.layout = 'lg';
                    else if (w <= Layout.xl) this.layout = 'xl';
                    else this.layout = 'xxl';
                    window.$vm = this;
                    this.query = __.merge(__.clone(this.query), Yi.getQuery())
                },
            })
            Yi.vue.create(Action[action]());
            window.$action = Action;
            new Vue({
                el: '#main-app',
                data: function() {
                    return {
                        locales: Locale.locales,
                        lang: {}
                    };
                },
                mounted: function() {
                    var maps = {};
                    var keys = Object.keys(Locale.locales);
                    for (var i = 0; i < keys.length; i ++) {
                        maps[Locale.locales[keys[i]].locale] = keys[i];
                    }
                    this.lang = Locale.locales[maps[Config.lang]];
                }
            });
    
            $(document).on('click', '.open-dialog', function() {
                var title = this.getAttribute('data-title') || this.getAttribute('title');
                var url = this.getAttribute('href') || this.getAttribute('data-url')
                var option = $(this).data('option');
                Yi.open(__.merge(option, {
                    title: title,
                    content: url,
                }));
                return false;
            });
        }
    })

    var Clipboard = require('clipboard');
    var clipboard = new Clipboard('.clipboard');
    clipboard.on('success', function() {
        $vm.$message.success($lang('Copy Successful'));
    });
});