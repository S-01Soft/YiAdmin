define([], function() {
    var Action = {        
        index: function() {
            Yi.vue.mixin(Mixins.table);
            var self;
            var option = {
                template: '#app',
                data: function() {
                    return {
                        list: [],
                        query: {
                            type: 1,
                            page: 1,
                            where: {
                                tag: '',
                                kw: ''
                            }
                        },
                        pagination: {
                            current_page: 1, total: 0, page_size: 24
                        },
                        isSearch: false,
                        localModules: {},
                        login_visible: false,
                        login: {
                            type: 1,
                            message: ''
                        },
                        loginForm: { username: '', password: '', code: null },
                        confirmLoading: false,
                        userinfo: null,
                        btn_login_loading: false,
                        tags: [],
                        downing: false,
                        process: {
                            show: false,
                            closable: false,
                            list: []
                        }
                    }
                },
                watch: {
                    'query.type': {
                        handler: function(v) {
                            if (v == 2 && this.tags.length == 0) {
                                this.list = [];
                                this.init();
                            } else this.init();
                        }
                    }
                },
                mounted: function() {
                    self = this;
                    this.init();
                    this.getUserInfo();
                    this.getTags();
                },
                methods: {
                    init: function() {
                        this.list = [];
                        var hide = this.$message.loading('', 0);
                        this.$http.get(get_url('index'), {params: this.query}).then(function(data) {
                            hide();
                            self.list = data.data;
                            if (self.query.type == 1) {
                                self.localModules = JSON.parse(JSON.stringify(data.data));
                                self.pagination.total = 0;
                            }
                            else {
                                self.pagination.total = data.total;
                                self.pagination.page_size = data.per_page;
                            }
                        }).catch(function() {
                            hide();
                        });
                    },
                    handleLocalSearch: function(e) {
                        var v = e.target.value;
                        if (!v) {
                            this.isSearch = false;
                            return;
                        }
                        this.isSearch = true;
                        for (var k in this.list) {
                            var item = this.list[k];
                            if (
                                item.title.toUpperCase().indexOf(v.toUpperCase()) == -1  
                                && item.desc.toUpperCase().indexOf(v.toUpperCase()) == -1
                                && item.name.toUpperCase().indexOf(v.toUpperCase()) == -1
                            ) {
                                this.$set(this.list[k], 'visible', false);
                            } else {
                                this.$set(this.list[k], 'visible', true);
                            }
                        }
                    },
                    getTags: function(cb) {
                        this.$http.get(get_url('tags')).then(function(data) {
                            self.tags = data;
                            typeof cb == 'function' && cb(data);
                        }).catch(function() {});
                    },
                    handleFileUpload: function(file) {
                        if (file.status == 'uploading') this.progress().info('Module is uploading, please wait');
                        if (file.error) this.progress().error(file.error.status + ' ' + file.error.statusText);
                    },
                    handleTabClick: function(v) {
                        if (this.query.where.tag == v) this.query.where.tag = '';
                        else this.query.where.tag = v;
                        this.init();
                    },
                    handlePageChange: function(page) {
                        this.query.page = page;
                        this.init();
                    },
                    handleSetConfig: function(name, item) {
                        Yi.open({
                            title: $lang([item.title, 'Config']),
                            content: get_url('option?name=' + name)
                        })
                    },
                    handleStatusChange: function(name, item) {
                        this.setState(name, item.status ? 1 : 0, function() {
                            self.hideProcess();
                        }, function() {
                            self.init()
                        });
                    },
                    setState: function(name, status, success, error) {
                        this.progress().info(status ? 'Enabling module' : 'Disabling module');
                        this.$http.post(get_url('setState'), {name: name, status: status}).then(function(data) {
                            top.refreshMenu();
                            self.progress().info('Refreshing menu');
                            self.system_reload(success);
                        }).catch(function(e) {
                            self.progress().error(e.message);
                            typeof error == 'function' && error(name, status);
                        });
                    },
                    handleInstall: function(res) {
                        if (res.code == 1) this.install(res.data, 0);
                        else {
                            self.progress().error(res.message);
                        }
                    },
                    install: function(path, force) {
                        this.progress().info("Installing, please wait");
                        self.$http.post(get_url('install'), {path: path, force: force}).then(function(data) {
                            if (data.code == 1) {
                                self.setState(data.data, 1, function() {
                                    location.reload()
                                })
                            } else {
                                self.$confirm({
                                    title: $lang('Tips'),
                                    content: $lang('Module already exists,do you wish to continue installation?'),
                                    onOk: function() {
                                        self.install(data.path, 1);
                                    },
                                    onCancel: function() {
                                        self.hideProcess();
                                    }
                                })
                            }
                        }).catch(function(e) {
                            self.progress().error(e.message);
                            self.system_reload();
                        });
                    },
                    handleUninstall: function(name) {
                        this.progress().info('Module is uninstalling');
                        this.$http.post(get_url('uninstall'), { name: name }).then(function() {
                            self.progress().info('Module is uninstalled');
                            self.system_reload(function() {
                                location.reload();
                            })
                        }).catch(function() {
                            self.progress().error(e.message);
                            self.system_reload();
                        });
                    },
                    handleRemoteInstall: function(name, version) {
                        var self = this;
                        if (this.downing) {
                            this.$message.warning($lang("Downloading, please wait"));
                            return;
                        }
                        this.downing = true;
                        this.progress().info("Downloading, please wait");
                        this.$http.post(get_url('remoteInstall'), {name: name, version: version}).then(function(data) {
                            self.downing = false;
                            self.setState(data, 1, function() {
                                location.reload()
                            })
                        }).catch(function(e) {
                            self.downing = false;
                            self.progress().error(e.message);
                        })
                    },
                    system_reload: function(cb) {
                        this.progress().info('System restarting');
                        this.$http.post('/system/admin/index/restart').then(function() {
                            self.check(cb);
                        })
                    },
                    check: function(cb) {
                        setTimeout(function() {
                            self.getSystemStatus(function(data) {
                                if (data == 'RUNNING') {
                                    self.progress().info('System started');
                                    typeof cb == 'function' && cb(data)
                                } else {
                                    self.progress().info('系统状态：' + data, false);
                                    self.check(cb);
                                }
                            });
                        }, 500);
                    },
                    showProgress: function(message, trans, type) {
                        if (!this.process.show) this.process.show = true;
                        if (!this.process.closable) this.process.closable = false;
                        if (trans === undefined) trans = true;
                        if (type === undefined) type = 'info';
                        var message = trans ? $lang(message) : message;
                        this.process.list.push({
                            type: type, message: message
                        });
                        // this.process.list.push(trans ? $lang(message) : message)
                    },
                    hideProcess: function() {
                        this.process.show = false;
                        this.process.list = [];
                    },
                    progress: function() {
                        return {
                            info: function(message, trans) {
                                self.showProgress(message, trans, 'info')
                            },
                            error: function(message, trans) {
                                self.showProgress(message, trans, 'error')
                                self.process.closable = true;
                            }
                        }
                    },
                    handleRefresh: function() {
                        this.$http.post(get_url('refresh'), {}, { loading: true }).then(function() {
                            self.init();
                        })
                    },
                    handleShowLogin: function() {
                        self.login_visible = true;
                    },
                    getUserInfo: function() {
                        this.$http.get(get_url('getUserInfo')).then(function(data) {
                            self.userinfo = data;
                        }).catch(function() {});
                    },
                    handleLogin: function() {
                        if (this.userinfo) {
                            this.login_visible = false;
                            return;
                        }
                        this.confirmLoading = true;
                        this.$http.post(get_url('login'), { form: this.loginForm }, { loading: true }).then(function(data) {
                            self.confirmLoading = false;
                            if (data.code == 1) {
                                self.userinfo = data.user;
                                self.loginForm.username = '';
                                self.loginForm.password = '';
                                self.loginForm.code = null;
                                self.login.type = 1;
                            } else if (data.code == 2) {
                                self.$message.error(data.message);
                                self.login.type = 2;
                                self.login.message = data.message;
                                self.loginForm.code = '';
                            }
                        }).catch(function() {
                            self.confirmLoading = false;
                        });
                    },
                    handleLogout: function() {
                        this.$http.post(get_url('logout')).then(function(data) {
                            self.userinfo = null;
                        });
                    },
                    handleWeigh: function(item) {
                        var hide = this.$message.loading();
                        this.$http.post(get_url('weigh'), {name: item.name, weigh: item.sort}).then(function() {
                            self.init();
                            hide();
                        }).catch(function() {
                            hide();
                        });
                    },
                    getSystemStatus: function(cb) {
                        this.$http.post('/system/admin/index/status', {}).then(function(data) {
                            typeof cb == 'function' && cb(data);
                        })
                    }
                }
            };
            return option;
        },
        option: function() {
            var option = {
                template: '#app',
                data: function() {
                    return {
                        data: []
                    }
                },
                mounted: function() {
                    this.init();
                },
                methods: {
                    init: function() {
                        var self = this;
                        this.$http.get(get_url('option?name=' + Yi.getQuery('name'))).then(function(data) {
                            self.data = data;
                        });
                    },
                    handleShowConfigItem: function() {
                        this.$refs.refOption.show();
                    },
                    OnSubmitClose: function() {
                        this.save(function() {
                            Yi.closeSelf();
                        })
                    },
                    onSubmit: function() {
                        var self = this;
                        this.save(function() {
                            self.$message.success($lang('Operate Successful'));
                        });
                    },
                    save: function(cb) {
                        var form = this.$refs.refOption.getForm();
                        this.$http.post(get_url('option'), {form: form, name: Yi.getQuery('name')}).then(function(data) {
                            typeof cb == 'function' && cb(data);
                        });
                    },
                    handleAddFormItem: function(item) {
                        var self = this;
                        this.$http.post(get_url('add_option_item'), {form: item, name: Yi.getQuery('name')}).then(function(data) {
                            self.$message.success($lang('Operate Successful'));
                            setTimeout(function() {
                                location.reload();                                
                            }, 1000);
                        });
                    },
                    handleDeleteFormItem: function(item) {
                        var self = this;
                        this.$confirm({
                            title: $lang('Tips'),
                            content: $lang('Are you sure you want to delete the configuration item?'),
                            onOk: function() {
                                self.$http.post(get_url('delete_option_item'), {option_name: item.name, name: Yi.getQuery('name')}).then(function(data) {
                                    self.$message.success($lang('Operate Successful'));
                                    setTimeout(function() {
                                        location.reload();                                
                                    }, 1000);
                                });
                            }
                        });
                    },
                    onCancel: function() {
                        Yi.closeSelf()
                    }
                }
            };
            return option;
        },
    };
    return Action;
});