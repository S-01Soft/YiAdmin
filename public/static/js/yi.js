var Yi = {
    _callbacks: [],
    _modules: [],
    _config: [],
    config: function (option) {
        Yi._config.push(option);
    },
    init: function () {
        for (var i = 0; i < this._callbacks.length; i++) {
            cb = this._callbacks[i];
            if (typeof cb == 'function') cb && cb();
        }
    },
    ready: function (callback) {
        this._callbacks.push(callback);
    },
    require: function () {
        for (var i = 0; i < arguments.length; i++) {
            var module = arguments[i];
            if (Yi._modules.indexOf(module) == -1) Yi._modules.push(module);
        }
    },
    modules: function () {
        return Yi._modules;
    },
    load: function(requires, callback) {
        if (!requires) {
            typeof callback == 'function' && callback();
            return;
        }
        var loaded = 0;
        for (var i = 0; i < requires.length; i ++) {
            require([requires[i]], function() {
                loaded += 1;
                if (loaded == requires.length) typeof callback == 'function' && callback();
            });
        }
    },
    ajax: function (option, success, error, complete) {
        var index;
        if (typeof option.loading == 'undefined' || option.loading) {
            index = Layer.load(option.loading || 0);
        }
        var defaultOption = {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (res) {
                if (res.code == 1) {
                    if (typeof success == 'function') {
                        var result = success(res.data, res);
                        if (result === false) return;
                    }
                    Toastr.success($lang('Operate Successful'));
                } else if (res.code == 9001) {
                    $('meta[name="csrf-token"]').attr('content', res.data.token);
                    Toastr.warning(res.message);
                    return;
                } else {
                    if (typeof error == 'function') {
                        var result = error(res);
                        if (result === false) return;
                    }
                    Toastr.error(res.message);
                }
            },
            error: function (e) {
                if (typeof error == 'function') {
                    var result = error(e);
                    if (result === false) return;
                }
                Toastr.error(e.statusText);
            },
            complete: function (res) {
                index && Layer.close(index);
                typeof complete == 'function' && complete(res);
            }
        };
        option = $.extend(defaultOption, option);
        $.ajax(option);
    },
    get: function (option, success, error, complete) {
        option = typeof option == 'string' ? { url: option } : option;
        option.method = 'GET';
        this.ajax(option, success, error, complete);
    },
    post: function (option, success, error, complete) {
        option = typeof option == 'string' ? { url: option } : option;
        option.method = 'POST';
        this.ajax(option, success, error, complete);
    },
    lang: {
        _data: {},
        load: function(data) {
            Yi.lang._data = data;
        },
        parse: function () {
            var args = arguments;
            if (args[0] == undefined) return ''
            var num = args.length;
            if (typeof args[0] == 'object') {
                var strArr = [];
                var params = [args[0]];
                for (var i = 1; i < args.length; i ++) {
                    params.push(args[i]);
                }
                for (var i = 0; i < args[0].length; i ++) {
                    params[0] = args[0][i];
                    strArr.push(Yi.lang.parse.apply(this, params));
                }
                return strArr.join(' ');
            }
            var result = Yi.lang._data[args[0].toLowerCase()] || args[0];
            if (result.indexOf('.') != -1) {
                var arr = result.split('.');
                arr[0] = arr[0].toLowerCase();
                result = Yi.getByDotKey(Yi.lang._data, arr.join('.')) || result;
            }
            for (var i = 1; i < num; i++) {
                var pattern = "\\{" + (i - 1) + "\\}";
                var re = new RegExp(pattern, "g");
                result = result.replace(re, args[i]);
            }
            if (typeof args[1] == 'object') {
                var keys = Object.keys(args[1]);
                for (var i = 0; i < keys.length; i++) {
                    var key = keys[i];
                    var value = args[1][key];
                    result = result.replace(new RegExp('{:' + key + '}', "g"), value);
                    result = result.replace(new RegExp(':' + key, "g"), value);
                }
            }
            var index = 1;
            result = result.replace(/%((%)|s|d)/g, function (match) {
                var value = args[index];
                switch (match) {
                    case '%d':
                        value = parseFloat(value);
                        if (isNaN(value)) value = 0;
                        break;
                }
                index++;
                return value;
            });
            return result;

        }
    },
    vue: {
        default: null,
        _options: [],
        _mixins: [],
        option: function (option, type) {
            if (type == 'default') Yi.vue.default = option; //Yi.vue.default || option;
            else Yi.vue._options.push(option);
        },
        mixin: function (option) {
            Yi.vue._mixins.push(option);
        },
        getMixins: function () {
            return Yi.vue._mixins;
        },
        create: function (option) {
            option = Yi.vue.default || option;
            for (var i = 0; i < Yi.vue._options.length; i++) {
                option = __.merge(option, Yi.vue._options[i]);
            }
            option.mixins = Yi.vue.getMixins();
            return Vue.component('app', option);
        }
    },
    event: {
        _listeners: {},
        on: function (name, cb) {
            var array = typeof name == 'string' ? name.split(',') : name;
            for (var i = 0; i < array.length; i++) {
                name = array[i];
                if (Yi.event._listeners[name]) Yi.event._listeners[name].push(cb);
                else Yi.event._listeners[name] = [cb];
            }
        },
        listen: function (name, payload) {
            if (!Yi.event._listeners[name]) return payload;
            for (var i = 0; i < Yi.event._listeners[name].length; i++) {
                var cb = Yi.event._listeners[name][i];
                payload = cb(payload);
            }
            return payload;
        }
    },
    getQuery: function (key, url) {
        url = url || window.location.href;
        // var match = url.match(/(?<=\?).+/);
        // var arr = match ? match[0].split('&') : []
        var match = url.split('?');
        var arr = match.length > 1 ? (match[1] ? match[1].split('&') : []) : []
        var result = {};
        for (var i = 0; i < arr.length; i++) {
            var v = arr[i].split('=');
            var k = v[0];
            v = v.length ? (v[1]) :null;
            try {
                result[k] = JSON.parse(decodeURIComponent(v));
            } catch (e) {
                result[k] = v;
            }
        }
        if (key) return result[key];
        return result;
    },
    querystring: function (obj) {
        var result = [];
        var keys = Object.keys(obj);
        for (var i = 0; i < keys.length; i++) {
            result.push(keys[i] + '=' + obj[keys[i]]);
        }
        return result.join('&');
    },
    strToKv: function (str, autoTransNumber, delimiter) {
        autoTransNumber = autoTransNumber || true;
        delimiter = delimiter || ',';
        var result = {};
        str = str.replace(/[,|\s]+/g, ',');
        var data = str.split(',');
        for (var i = 0; i < data.length; i++) {
            var arr = data[i].split('=');
            if (arr.length == 2) {
                var val = arr[1];
                if (autoTransNumber && val != '') {
                    var v = Number(val);
                    if (!isNaN(v)) result[arr[0]] = v;
                    else result[arr[0]] = val;
                }
                else result[arr[0]] = val;
            }
        }
        return result;
    },
    arrayColumn: function (arr, name) {
        var result = [];
        for (var i = 0; i < arr.length; i++) {
            result.push(arr[i][name]);
        }
        return result;
    },
    open: function (option, callback) {
        var _option = {
            area: ['80%', '90%'],
            type: 2,
            moveOut: true,
        };
        var option = __.merge(_option, option);
        var success = option.success;
        option.success = function (layero) {
            if (typeof success == 'function') success(layero);
            if (callback) $(layero).data('callback', callback);
        }
        return Layer.open(option);
    },
    closeSelf: function (data) {
        var index = parent.layer.getFrameIndex(window.name);
        var callback = parent.$('#layui-layer' + index).data('callback');
        parent.layer.close(index);
        if (typeof callback == 'function') {
            callback(data);
        }
    },
    date: function (timestamp) {
        timestamp = timestamp || 0;
        if (timestamp.toString().length == 10) timestamp = timestamp * 1000;
        return new Date(timestamp);
    },
    getByDotKey: function (row, key) {
        var keys = key.split('.');
        for (var i = 0; i < keys.length; i++) {
            if (row == null) return undefined;
            if (typeof row[keys[i]] == 'undefined') return undefined;
            row = row[keys[i]];
        }
        return row;
    },
    
    isEmpty: function (val) {
        if (val == null) return true;
        if (typeof val === 'boolean') return false;
        if (typeof val === 'number') return !val;
        if (val instanceof Error) return val.message === '';
        switch (Object.prototype.toString.call(val)) {
            case '[object String]':
            case '[object Array]':
                return !val.length;
            case '[object File]':
            case '[object Map]':
            case '[object Set]': {
                return !val.size;
            }
            case '[object Object]': {
                return !Object.keys(val).length;
            }
        }
        return false;
    },
    getStatic: function (name) {
        if (Config.statics.open && Config.statics.maps && Config.statics.maps[name]) return Config.statics.maps[name];
        return null;
    },
    util: {
        findRows: function (rows, key, value, return_row) {
            var index = -1;
            for (var i = 0; i < rows.length; i++) {
                if (rows[i][key] == value) {
                    index = i;
                    break;
                }
            }
            if (return_row) return index == -1 ? null : rows[index];
            return index;
        },
        findRowsObj: function (rows, row, return_row) {
            var index = -1;
            var keys = Object.keys(row);
            for (var i = 0; i < rows.length; i++) {
                var found = true;
                for (var j = 0; j < keys.length; j++) {
                    var key = keys[j];
                    if (row[key] != rows[i][key]) {
                        found = false;
                        continue;
                    }
                }
                if (found) {
                    index = i;
                    break;
                }
            }
            if (return_row) return index == -1 ? null : rows[index];
            return index;
        },
        findRowsMulti: function (rows, keys, values, return_row) {
            var syb = '___';
            var index = -1;
            for (var i = 0; i < rows.length; i++) {
                if (this.getRowValuesByFieldsStr(rows[i], keys, syb) == values.join(syb)) {
                    index = i;
                    break;
                }
            }
            if (return_row) return index == -1 ? null : rows[index];
            return index;
        },
        getRowValuesByFieldsArr: function (row, keys) {
            var result = [];
            for (var i = 0; i < keys.length; i++) {
                result.push(row[keys[i]]);
            }
            return result;
        },
        getRowValuesByFieldsStr: function (row, keys, syb) {
            syb = syb || '___';
            return this.getRowValuesByFieldsArr(row, keys).join(syb);
        },
        packageRows: function (keyFields, rows1, rows2) {
            var result = rows1;
            for (var i = 0; i < rows2.length; i++) {
                if (this.findRowsMulti(rows1, keyFields, this.getRowValuesByFieldsArr(rows2[i], keyFields)) == -1) {
                    result.push(rows2[i]);
                }
            }
            return result;
        },
        deleteRowByKey: function (rows, key, value) {
            var index = Yi.util.findRows(rows, key, value);
            if (index != -1) rows.splice(index, 1);
            return rows;
        },
        exchangeRow: function (rows, obj1, obj2) {
            var index1 = Yi.util.findRowsObj(rows, obj1);
            if (index1 == -1) return rows;
            var index2 = Yi.util.findRowsObj(rows, obj2);
            if (index2 == -1) return rows;
            var row1 = rows[index1];
            var row2 = rows[index2];
            rows[index1] = row2;
            rows[index2] = row1;
            return rows;
        },
        deleteRow: function (rows, deleteRow) {
            var index = Yi.util.findRowsObj(rows, deleteRow);
            if (index != -1) rows.splice(index, 1);
            return rows;
        },
        deleteRows: function (rows, deletes) {
            for (var i = 0; i < deletes.length; i++) {
                rows = Yi.util.deleteRow(rows, deletes[i]);
            }
            return rows;
        },
        insertBefore: function (rows, search, row) {
            var index = Yi.util.findRowsObj(rows, search);
            if (index == -1) return rows;
            rows.splice(index, 0, row);
            return rows;
        },
        insertAfter: function (rows, search, row) {
            var index = Yi.util.findRowsObj(rows, search);
            if (index == -1) return rows;
            rows.splice(index + 1, 0, row);
            return rows;
        },
        moveBefore: function (rows, search_source, search_dest) {
            var row = Yi.util.findRowsObj(rows, search_source, true);
            Yi.util.deleteRow(rows, search_source);
            return Yi.util.insertBefore(rows, search_dest, row);
        },
        moveAfter: function (rows, search_source, search_dest) {
            var row = Yi.util.findRowsObj(rows, search_source, true);
            Yi.util.deleteRow(rows, search_source);
            return Yi.util.insertAfter(rows, search_dest, row);
        },
        find: function (rows, search, cb) {
            var row = Yi.util.findRowsObj(rows, search, true);
            typeof cb == 'function' && cb(row);
        }
    },
    render: {
        val: function (value, row, index, column) {
            return value === undefined ? Yi.getByDotKey(row, column.key) : value;
        },
        html: function (value, row, index, column, el) {
            var h = $vm.$createElement;
            var v = el || Yi.getByDotKey(row, column.key);
            return h('div', {
                domProps: {
                    innerHTML: v === undefined ? '' : v
                }
            })
        },
        option: function (value, row, index, column) {
            var val = Yi.render.val(value, row, index, column);
            return column.option[val];
        },
        date: function (value, row, index, column) {
            var val = Yi.render.val(value, row, index, column);
            var option = column.option || {};
            return val ? Yi.date(val).format(option.format || 'yyyy-MM-dd hh:mm:ss') : '';
        },
        image: function (value, row, index, column) {
            var val = Yi.render.val(value, row, index, column);
            var attrs = __.merge({
                'data-clipboard-text': val,
                class: 'clipboard',
                title: $lang('Click to copy'),
                src: val,
                width: '44px',
                onerror: 'this.src="/static/images/image.png";this.onerror=null;'
            }, column.option || {});
            var h = $vm.$createElement;
            return h('img', {
                attrs: attrs
            });
        },
        images: function (value, row, index, column) {
            var val_arr = Yi.render.val(value, row, index, column).split(',');
            var children = [];
            for (var i = 0; i < val_arr.length; i++) {
                val = val_arr[i];
                children.push(Yi.render.image(val, row, index, column));
            }
            var h = $vm.$createElement;
            return h('div', {}, children);
        },
        switch: function (value, row, index, column) {
            value = Yi.render.val(value, row, index, column);
            var h = $vm.$createElement;
            var vNode = h('a-switch', {
                size: 'small',
                props: {
                    checked: value ? true : false
                },
                on: {
                    change: function (val) {
                        var params = {};
                        params[column.key] = val ? 1 : 0;
                        $vm.toggle(row, params).then(function () {
                            $vm.$message.success($lang('Operate Successful'));
                            $vm.init();
                        });
                    }
                }
            });
            return vNode;
        },
        edit: function(value, row, index, column) {
            value = Yi.render.val(value, row, index, column);
            var h = $vm.$createElement;
            var vNode = h('input', {
                size: 'small',
                style: {
                    border: 'none',
                    width: column.width || '55px'
                },
                domProps: {
                    value: value
                },
                on: {
                    change: function(e) {
                        var params = {};
                        params[column.key] = e.target.value;
                        $vm.toggle(row, params).then(function () {
                            $vm.$message.success($lang('Operate Successful'));
                            $vm.init();
                        });
                    }
                }
            })
            return vNode;
        },
        enum: function (val, row, index, column, option) {
            var v = Yi.getByDotKey(row, column.key);
            var h = $vm.$createElement;
            option = option ? option[v] : {};
            option.domProps = {
                innerHTML: column.enums[v] || ''
            };
            var vnodes = h('div', option);
            return vnodes;
        }
    },
};