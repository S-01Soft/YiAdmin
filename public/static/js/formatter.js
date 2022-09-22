
require.config({
    paths: {
        uploader: 'vendor/webuploader/webuploader.nolog.min',
    },
    shim: {
        uploader: ['css!vendor/webuploader/webuploader']
    }
});


define(function () {
    var Event = {
        events: {},
        on: function(name, callback) {
            if (this.events[name]) this.events[name].push(callback);
            else this.events[name] = [callback];
        },
        off: function(name) {
            this.events[name] && delete this.events[name];
        },
        fire: function() {
            var name = arguments[0];
            var args = [];
            for (var i = 1; i < arguments.length; i ++) {
                args.push(arguments[i]);
            }
            if (this.events[name]) {
                var listeners = this.events[name];
                for (var i = 0; i < listeners.length; i ++) {
                    typeof listeners[i] == 'function' && listeners[i].apply(null, args);
                }
            }
        }
    };

    var Formatter = {
        event: Event,
        render: function (el) {
            var apis = this.api;
            var keys = Object.keys(apis);
            for (var i = 0; i < keys.length; i++) {
                apis[keys[i]](el);
            }
        },
        api: {
            uploader: function(form) {
                if (!$('.web-uploader', form).size()) return;
                var css = '.web-uploader-item{display: inline-block;margin-top: 3px;}';
                css += '.web-uploader-img{width: 90px;height: 90px;display:table-cell;vertical-align: middle;padding: 5px;border: 1px solid #ddd;border-radius: 6px;}';
                css += '.web-uploader-item:not(first){margin-left: 4px;}';
                css += '.web-uploader-img img {width:100%;max-height: 100%;}';
                css += '.web-uploader-btn-del{margin-top:2px;height:20px;line-height: 20px;border-radius: 4px;background-color: rgb(223, 70, 70);color: rgb(255, 255, 255);font-size: 13px;}'
                css += '.btn-upload .webuploader-pick {display: inline-block;font-weight: 400;color: #212529;text-align: center;vertical-align: middle;-webkit-user-select: none;padding: .375rem .75rem;';
                css += '-moz-user-select: none;-ms-user-select: none;user-select: none;border: 1px solid transparent;font-size: 1rem;border-radius: .25rem;transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;';
                css += 'background-color:#dc3545;border-color: #dc3545;color: #fff;padding-left:2em;padding-right:2em;border-radius: 100px;border-top-left-radius: 0;border-bottom-left-radius: 0;}';
                $(document.head).append($('<style>' + css + '</style>'))
                require(['uploader'], function(Uploader) {
                    $('.web-uploader', form).each(function() {
                        var self = this;
                        var defaults = {
                            swf: '/static/vendor/webuploader/Uploader.swf',
                            server: '/system/api/upload',
                            pick: $('.btn-upload', this).data('pick') || $('.btn-upload', this),
                            max: 1,
                            review: true
                        };
                        var option = __.merge(defaults, $('.btn-upload', this).data());
                        option.auto = false;
                        var uploader = Uploader.create(option);
                        var File = {
                            _files: {},
                            add: function(file) {
                                if (this._files[file.id]) return false;
                                var has_file = false;
                                this.each(function(id, v) {
                                    if (v.url == file.url) has_file = true;
                                });
                                if (!has_file) {this._files[file.id] = file; return true}
                                return false;
                            },
                            remove: function(id) {
                                if (this._files[id]) {
                                    //uploader.removeFile(this._files[id], true)
                                    delete this._files[id];
                                }
                            },
                            each: function(callback) {
                                var keys = Object.keys(this._files);
                                for (var i = 0; i < keys.length; i ++) {
                                    if (typeof callback == 'function') {
                                        var res = callback(keys[i], this._files[keys[i]]);
                                        if (res === false) return res;
                                    }
                                }
                                return true;
                            },
                            count: function() {
                                var c = 0;
                                this.each(function() {
                                    c ++
                                });
                                return c;
                            },
                            clear: function() {
                                this._files = {};
                            },
                            getFiles: function() {
                                return this._files;
                            }
                        };

                        var init = function() {
                            var urls = $('.web-uploader-input', self).val();
                            if (!urls) return;
                            urls = urls.split(',');
                            for (var i = 0; i < urls.length; i ++) {
                                File.add({
                                    id: i, url: urls[i]
                                });
                            }
                        }


                        // 填充url到input
                        var fixInput = function() {
                            var urls = []
                            File.each(function(id, file) {
                                urls.push(file.url);
                            })
                            $('.web-uploader-input', self).val(urls.join(','));
                        }

                        // 填充文件预览
                        var fixFileView = function() {
                            if (!option.review) return;
                            var el = '';
                            File.each(function(id, file) {
                                el += '<div class="web-uploader-item"><div class="web-uploader-img"><img onerror="this.src=\'/file_default.png\';this.onerror=null;" src="' + file.url + '" /></div><div class="web-uploader-btn-del text-center pointer" data-file_id="' + file.id + '">删除</div></div>'
                            })
                            $('.file-list', self).html(el);
                        }

                        $(self).on('click', '.web-uploader-btn-del', function() {
                            Event.fire('uploader.removeFile', this, self, uploader);
                        });
                        
                        init();
                        fixFileView();

                        Event.on('uploader.uploadSuccess', function(file) {
                            fixFileView();
                            fixInput();
                        });
                        
                        Event.on('uploader.removeFile', function(e) {
                            var file_id = $(e).data('file_id');
                            File.remove(file_id);
                            fixFileView();
                            fixInput();
                        });

                        var lodash = __;
                        uploader.on('uploadBeforeSend', function(obj, data, headers) {
                            headers = lodash.merge(headers, {
                                'X-Requested-With': 'XMLHttpRequest'
                            });
                        });

                        uploader.on('fileQueued', function(file) {
                            if (File.each(function(id, v) {
                                if (v.__hash == file.__hash) return false;
                            })) uploader.upload();
                            else uploader.removeFile(file)
                        });

                        uploader.on('uploadError', function( file , e) {
                            Toastr.error(e);
                        });

                        uploader.on('uploadSuccess', function(file, res)  {
                            if (res.code == 1) {
                                file.url = res.data;
                                if (option.max == 1) File.clear();
                                else if (option.max <= File.count()) {
                                    Toastr.error($lang('maximum number of files uploaded is %d', [option.max]))
                                    return;
                                }
                                var r = File.add(file);
                                uploader.removeFile(file, true);
                                Event.fire('uploader.uploadSuccess', file, self, uploader);
                                if (r) Toastr.success(res.message || $lang('Upload Successful'));
                            } else {
                                Toastr.error(res.message);
                            }
                        });
                        Event.fire('uploader.init', self, uploader, File);
                    });
                });
            },
            switch: function(form) {
                if (!$('.yi-switch', form).size()) return;
                $('.yi-switch', form).each(function() {
                    var self = this;
                    var id = $(this).attr('id');
                    $(this).addClass('hidden');
                    $(this).after('<i class="fa fa-toggle-on pointer fa-2x" data-id="' + id + '"></i>');
                    var el = $('i[data-id="' + id + '"]', form);
                    var change = function () {
                        var v = $(self).val() == 1 ? 1 : 0;
                        if (v) {
                            $(el).removeClass('text-gray fa-flip-horizontal')
                            $(el).addClass('text-success');
                        } else {
                            $(el).removeClass('text-success');
                            $(el).addClass('text-gray fa-flip-horizontal');
                        }
                    }
                    $(el).click(function() {
                        var v = $(self).val() == 1 ? 0 : 1;
                        $(self).val(v);
                        $(self).trigger('change');
                        setTimeout(function() {
                            change();
                        }, 100)
                    })
                    change();
                })
            },
            submit: function(form) {
                $('.submit', form).each(function(index, el) {
                    var submit = function () {
                        Yi.post({
                            url: $(el).data('action') || $(form).attr('action'),
                            data: $(form).serialize(),
                        }, function (data) {
                            $(form).trigger('success', data, el);
                            Event.fire('form.success', data, el);
                        }, function(e) {
                            $(form).trigger('error', e, el);
                            Event.fire('form.error', e, el);
                        }, function(e) {
                            $(form).trigger('finish', e, el);
                            Event.fire('form.finish', e, el);
                        });
                    }
                    $(el).on('click', function(e) {
                        $(form).trigger('beforeSubmit', el, e);
                        Event.fire('form.beforeSubmit', el, e);
                        submit();
                        e.preventDefault();
                        return false;
                    });
                });
            }
        },
        loader: {}
    };
    return Yi.event.listen('formatter', Formatter);
});