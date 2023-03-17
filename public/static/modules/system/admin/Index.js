define([], function () {
    window.refreshMenu = function() {
        return $vm.refreshMenu();
    }
    var quickMenuList = Yi.event.listen(EventPrefix + 'MenuList', [
        { url: '/system/admin/module/option?name=system', title: 'System Config', icon: 'fas fa-cogs', area: '80% 90%', new: true, weigh: 10000 },
        { url: '/system/admin/auth_group/index', title: 'Admin Roles', icon: 'fas fa-users', area: '80% 90%', new: false, weigh: 10000 },
        { url: '/system/admin/admin/index', title: 'Admins', icon: 'fa fa-user-cog', area: '80% 90%', new: false, weigh: 10000 },
        { url: '/system/admin/user_group/index', title: 'User Roles', icon: 'fa fa-user-friends', area: '80% 90%', new: false, weigh: 10000 },
        { url: '/system/admin/user/index', title: 'Users', icon: 'fa fa-user-edit', area: '80% 90%', new: false, weigh: 10000 },
        { url: '/system/admin/auth_rule/index', title: 'Menu', icon: 'fa fa-align-left', area: '80% 90%', new: false, weigh: 10000 },
    ]);
    var Action = {
        index: function () {
            var self;
            var option = {
                template: '#app',
                data: function () {
                    return {
                        iframes: {},
                        activeUrls: [],
                        activeIframe: '',
                        contentHeight: '220px',
                        asideMenuHeight: '100vh',
                        collapsed: false,
                        leftWidth: 200,
                        openKeys: [],
                        hoverUrl: null,
                        dashbordUrl: dashbord,
                        menuList: []
                    }
                },
                mounted: function () {
                    this.init();
                    self = this;
                    this.getMenuList();
                    this.calcSytles();
                    window.addEventListener('resize', function () {
                        self.calcSytles();
                    });
                    $(document).on('click', '.nav-link', function() {
                        var e = this;
                        var url = e.attributes['data-url'].value;
                        if (url) {
                            var title = e.attributes['data-title'].value;
                            self.addIframe(url, title);
                        }
                    })
                },
                methods: {
                    calcSytles: function () {
                        if (window.innerWidth <= 576) this.leftWidth = 0;
                        else this.leftWidth = 200;
                        var h = window.innerHeight;
                        this.contentHeight = h - 64;
                        this.asideMenuHeight = (h - 64 + 'px');
                    },
                    handleMenuClick: function (e) {
                        var url = e.item.$el.attributes['data-url'].value;
                        if (url) {
                            var title = e.item.$el.attributes['data-title'].value;
                            this.addIframe(url, title);
                        }
                    },
                    addIframe: function (url, title) {
                        window.history.pushState('', '', '#' + url);
                        var self = this;
                        this.activeIframe = url;
                        if (self.activeUrls.indexOf(url) == -1) self.activeUrls.push(url);
                        if (!self.iframes[url]) {
                            if (!title) {
                                this.getMenuTitle(url, function(tit) {
                                    if (tit) self.setIframe(url, tit);
                                })
                            } else this.setIframe(url, title);
                        }
                    },
                    setIframe: function(url, title) {
                        this.$set(this.iframes, url, title);
                        window.localStorage.setItem('menu', JSON.stringify(this.iframes));
                    },
                    getMenuTitle: function(url, cb) {
                        if (this.iframes[url]) cb && cb(this.iframes[url]);
                        setTimeout(function() {
                            cb && cb($('.ant-menu-item-selected').text());
                        }, 20);
                    },
                    init: function () {
                        var self = this;
                        var hash = location.hash.split('#');
                        var menu = window.localStorage.getItem('menu');
                        this.iframes = menu ? JSON.parse(menu) : {};
                        hash = hash.length > 1 ? hash[1] : null;
                        if (hash) {
                            this.addIframe(hash);
                            setTimeout(function() {
                                $('.ant-menu-submenu-selected').each(function () {
                                    self.openKeys.push($(this).data('key'));
                                });
                            }, 2);
                        } else {
                            this.addIframe(dashbord, 'Dashbord');
                        }
                    },
                    handleClickTopMenuItem: function(url) {
                        this.addIframe(url);
                    },
                    handleTopMenuMouseEnter: function(url) {
                        this.hoverUrl = url;
                    },
                    handleRemoveTopMenuItem: function(url, title) {
                        delete this.iframes[url];
                        var index = this.activeUrls.indexOf(url);
                        this.activeUrls.splice(index, 1);
                        if (this.activeUrls.length == 0) this.addIframe(dashbord, 'Dashbord');
                        if (url == this.activeIframe) {
                            this.activeIframe = this.activeUrls.length ? (index - 1 < 0 ? this.activeUrls[0] : this.activeUrls[index - 1]) : null;
                            window.history.pushState('', '', '#' + this.activeIframe);
                        }
                        window.localStorage.setItem('menu', JSON.stringify(this.iframes));
                    },
                    handleClearTopMenu: function() {
                        var o = {};
                        o[dashbord] = 'Dashbord';
                        this.iframes = o;
                        this.activeIframe = dashbord;
                        if (this.activeUrls.indexOf(dashbord) == -1) window.history.pushState('', '', '#' + this.activeIframe);
                        this.activeUrls = [dashbord];
                        window.history.pushState('', '', '#' + this.activeIframe);
                        window.localStorage.setItem('menu', JSON.stringify(this.iframes));
                    },
                    handleLogout: function() {
                        this.$http.post('/system/admin/index/logout').then(function(data) {
                            location.reload();
                        });
                    },
                    handleMenuCollapse: function() {
                        if (this.leftWidth) this.leftWidth = 0;
                        else this.leftWidth = 200;
                    },
                    changeLang: function(v) {
                        this.$http.post('/system/admin/index/changeLang?lang=' + v).then(function(data) {
                            location.reload();
                        });
                    },
                    handleRestartSystem: function() {
                        var self = this;
                        var check = function() {
                            setTimeout(function() {
                                self.getSystemStatus(function(data) {
                                    if (data == 'RUNNING') {
                                        self.$message.success($lang('System started'));
                                    }
                                    else check();
                                });
                            }, 500);
                        }
                        self.$message.info($lang('System restarting'));
                        this.$http.post('/system/admin/index/restart', {}, { loading: true }).then(function(data) {
                            check();
                        });
                    },
                    getSystemStatus: function(cb) {
                        this.$http.post('/system/admin/index/status').then(function(data) {
                            typeof cb == 'function' && cb(data);
                        })
                    },
                    refreshMenu: function() {
                        this.getMenuList();
                    },
                    getMenuList: function() {
                        this.$http.post("/system/admin/index/index").then(function(data) {
                            self.menuList = data;
                        });
                    },
                }
            };
            return option;
        },
        dashbord: function() {
            var self;
            var config = {
                template: '#app',
                data: function() {
                    var date = new Date();
                    var Y = date.getFullYear();
                    var M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1);
                    var D = date.getDate();
                    var now = new Date(Y + '-' + M + '-' + D).valueOf() + new Date().getTimezoneOffset() * 60 * 1000;
                    return {
                        menu_list: [],
                        form: {
                            start: (now - 6 * 24 * 60 * 60 * 1000) / 1000,
                            end: (now + 24 * 60 * 60 * 1000 - 1000) / 1000
                        },
                        data: {},
                        dataInit: false,
                        ecahrt: null,
                        echartInit: false,
                        currentTop10Type: 'indexPageTop10',
                        top10Query: {page: 1},
                        top10Options: [
                            {label: $lang('Frontend Pages'), value: 'indexPageTop10',},
                            {label: $lang('Frontend Requests'), value: 'indexUrlTop10'},
                            {label: 'IP', value: 'indexIpTop10'}
                        ],
                        quickMenu: {
                            id: 0,
                            visible: false,
                            editable: false,
                            type: 'add',
                            index: 0,
                            key: 'system_quick_menu_list',
                            list: quickMenuList,
                            form: {
                                url: '',
                                icon: '',
                                title: '',
                                weigh: 10000,
                                area: "80% 90%",
                                new: true
                            }
                        },
                    }
                },
                computed: {
                    quick_menu_list: function() {
                        var result = [];
                        for (var i = 0; i < this.menu_list.length; i ++) {
                            var item = this.menu_list[i];
                            if (item.visible == undefined || item.visible == true) result.push(item);
                        }
                        return __.orderBy(result, ['weigh'], ['desc'])
                    }
                },
                watch: {
                    dataInit: function(v) {
                        if (v && this.echartInit) this.render();
                    },
                    echartInit: function(v) {
                        if (v && this.dataInit) this.render();
                    }
                },
                mounted: function() {
                    self = this;
                    window.v = this;
                    this.getMenuList();
                    this.getStatisticsInfo(function(data) {
                        self.dataInit = true;
                        self.data = data;
                    });
                    require(['echarts'], function(echarts) {
                        var Echart = echarts.init(document.getElementById('chart-demo'));
                        self.echartInit = true;
                        self.echart = Echart;
                    });
                    window.addEventListener('resize', function () {
                        self.echart.resize();
                    });
                },
                methods: {
                    handleOpen: function(item, index) {
                        if (this.quickMenu.editable) {
                            this.quickMenu.type = 'edit';
                            this.quickMenu.visible = true;
                            this.quickMenu.index = index;
                            this.quickMenu.form = item;
                            return;
                        }
                        if (item.new) {
                            Yi.open({
                                title: $lang(item.title),
                                content: item.url,
                                area: item.area.trim().replace(/s+/, ' ').split(' ')
                            })
                        }  else {
                            top.$vm.addIframe(item.url, item.title);
                        }
                    },
                    render: function() {
                        var data = this.data;
                        var option = {
                            legend: {
                                data: ['pv', 'ip']
                            },
                            xAxis: [
                                {
                                    type: 'category',
                                    data: data.pv.title
                                }
                            ],
                            yAxis: {
                                type: 'value'
                            },
                            series: [
                                {
                                    name: 'pv',
                                    type: 'bar',
                                    label: {
                                        show: true
                                    },
                                    data: data.pv.value
                                },
                                {
                                    name: 'ip',
                                    type: 'bar',
                                    label: {
                                        show: true
                                    },
                                    data: data.ip.value
                                }
                            ]
                        };
                        self.echart.setOption(option);
                    },
                    getStatisticsInfo: function(cb) {
                        this.$http.post('/system/admin/index/dashbord', {form: this.form}).then(function(data) {
                            typeof cb == 'function' && cb(data);
                        });
                    },
                    handleRefresh: function() {
                        this.getStatisticsInfo(function(data) {
                            self.data = data;
                            self.render();
                        });
                    },
                    getMenuList: function() {
                        var self = this;
                        this.$http.get('/system/admin/setting/index', { params: { where: { key: this.quickMenu.key } } }).then(function(data) {
                            if (data.data.length) {
                                self.quickMenu.id = data.data[0].id;
                                var list = JSON.parse(data.data[0].data);
                                self.menu_list = list;
                            } else {
                                self.menu_list = __.clone(self.quickMenu.list);
                            }
                        });
                    },
                    handleSetQuickMenu: function() {
                        if (this.quickMenu.type == 'add')
                        this.menu_list.push(this.quickMenu.form);
                        else this.$set(this.menu_list, this.quickMenu.index, this.quickMenu.form);
                        this.saveMenuSetting();
                    },
                    saveMenuSetting: function() {
                        var self = this;
                        var form = {
                            key: this.quickMenu.key, data: this.menu_list
                        };
                        this.$http.post('/system/admin/setting/add', { form: form }).then(function () {
                            self.quickMenu.visible = false;
                            self.resetMenuForm();
                            self.$message.success($lang("Operate Successful"));
                        });
                    },
                    handleRemoveMenu: function(item, index, e) {
                        this.menu_list.splice(index, 1);
                        this.saveMenuSetting();
                        e.stopPropagation();
                        e.preventDefault();
                        return false;
                    },
                    handleMenuChange: function(value) {
                        this.quickMenu.form.url = value;
                    },
                    handleClearMenuSetting: function() {
                        var self = this;
                        this.$http.post('/system/admin/setting/delete', { ids: this.quickMenu.id }).then(function() {
                            self.menu_list = __.clone(self.quickMenu.list);
                            self.quickMenu.editable = false;
                        });
                    },
                    resetMenuForm: function() {
                        this.quickMenu.form = {
                            url: '',
                            icon: '',
                            title: '',
                            new: true,
                            area: "80% 90%",
                            weigh: 10000
                        }
                    }
                }
            };
            return config;
        },
        login: function () {
            var self;
            var config = {
                template: '#app',
                data: function () {
                    return {
                        form: {
                            username: '',
                            password: '',
                            type: defaultType,
                            code: ''
                        },
                        rules: {
                            username: [
                                {"required":true,"message":$lang(':attribute require', {attribute: $lang('Username')}),"trigger":"blur"},
                            ], 
                            password: [
                                {"required":true,"message":$lang(':attribute require', {attribute: $lang('Password')}),"trigger":"blur"},
                            ], 
                        },
                        verify_visible: false,
                        message: '',
                        btn_loading: false
                    }
                },
                mounted: function() {
                    self = this;
                    Yi.event.listen('system_admin/login_mounted');
                },
                methods: {
                    submit: function() {
                        this.btn_loading = true;
                        this.$http.post('login', { form: this.form }).then(function (data) {
                            self.btn_loading = false;
                            if (data.code === 1) {
                                location.href = decodeURIComponent(Yi.getQuery('referer', location.search)) + location.hash;
                            } else if (data.code === 2) {
                                self.verify_visible = true;
                                self.message = data.message;
                            }
                        }).catch(function() {
                            self.btn_loading = false;
                        });
                    },
                    onSubmit: function () {
                        this.$refs.ruleForm.validate(function(valid) {
                            if (valid) self.submit();
                            else return false;
                        });
                    },
                    resetForm() {
                        this.$refs.ruleForm.resetFields();  
                    },
                }
            };
            return config;
        },
        error: function() {
            var config = {
                template: '#app',
                methods: {
                    handleGoBack: function() {
                        window.history.back();
                    }
                }
            };
            return config;
        },
        upgrade: function() {
            var self;
            var config = {
                template: '#app',
                data: function() {
                    return {
                        loading: false,
                        logs: []
                    }
                },
                mounted: function() {
                    self = this;
                    this.init();
                },
                methods: {
                    init: function() {
                        this.loading = true;
                        this.$http.get('').then(function(data) {
                            self.loading = false;
                            self.logs = data;
                        });
                    },
                    submit: function(version) {
                        this.$confirm({
                            title: $lang('Tips'),
                            content: $lang('Please make a backup before upgrading'),
                            onOk: function() {
                                var lock = false;
                                var data = [];
                                for (var i = 0; i < self.logs.length; i ++) {
                                    var item = self.logs[i];
                                    if (item.version == version) lock = true;
                                    if (lock) data.push(item.version)
                                }
                                self.$message.info($lang('System upgrading'))
                                self.$http.post('/system/admin/index/upgrade', {versions: data}).then(function(data) {
                                    self.system_reload();
                                });
                            }
                        })
                    },
                    system_reload: function() {
                        this.$message.info($lang('System restarting'))
                        this.$http.post('/system/admin/index/restart').then(function() {
                            self.$message.info($lang('System started'));
                            location.reload();
                        })
                    },
                }
            };
            return config
        },
        diff: function() {
            var self;
            var config = {
                template: '#app',
                data: function() {
                    return {
                        logs: []
                    }
                },
                mounted: function() {
                    self = this;
                },
                methods: {
                }
            };
            return config
        }
    };
    return Action;
})