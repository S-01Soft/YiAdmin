define(['vue', 'axios', 'antd', 'YiComponents'], function(Vue, Axios, Antd, YiComponents) {
    var instance = Axios.create({
        headers: {
            'content-type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    });

    var timer;
    var hide;
    instance.interceptors.request.use(function (config) {
        if (config.loading) {
            timer = setTimeout(function() {
                hide = $vm.$message.loading(config.loadText || $lang('loading, please wait'), 0)
            }, config.timeout && config.timeout || 300);
        }
        config.headers['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');
        return config;
    }, function (e) {
        typeof hide == 'function' && hide();
        return Promise.reject(e);
    });

    instance.interceptors.response.use(function (response) {
        if (timer) clearTimeout(timer);
        typeof hide == 'function' && hide();
        if (response.status != 200) {
            $vm.$message.error(response.statusText);
            return Promise.reject(response.statusText);
        }
        if (response.headers['csrf-token']) $('meta[name="csrf-token"]').attr('content', response.headers['csrf-token']);
        if (response.data.code != 1) {
            switch (parseInt(response.data.code)) {
                case 9999: {
                    break;
                }
                case 9001: {
                    $('meta[name="csrf-token"]').attr('content', response.data.data.token);
                    $vm.$message.warning(response.data.message);
                    break;
                }
                default: {
                    $vm.$message.error(response.data.message);
                    break;
                }
            }
            return Promise.reject(response.data);
        }
        return response.data.data;
    }, function (error) {
        if (timer) clearTimeout(timer);
        typeof hide == 'function' && hide();
        $vm.$message.error(error.message);
        return Promise.reject(error);
    });

    Vue.use(Antd);
    Vue.use(YiComponents);
    Vue.prototype.$http = instance;
    Vue.prototype.$lang = Yi.lang.parse;

    return Vue;
})

