<script>
(function () {
    function syncCsrfToForms() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (!meta) {
            return;
        }

        var token = meta.getAttribute('content');
        document.querySelectorAll('input[name="_token"]').forEach(function (input) {
            input.value = token;
        });

        if (window.axios) {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
        }
    }

    var nativeFetch = window.fetch.bind(window);

    window.refreshCsrfToken = function () {
        return nativeFetch('/csrf-token', {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (data.token) {
                    var meta = document.querySelector('meta[name="csrf-token"]');
                    if (meta) {
                        meta.setAttribute('content', data.token);
                    }
                    syncCsrfToForms();
                }
                return data.token;
            })
            .catch(function () { return null; });
    };

    window.fetch = function (input, init) {
        var options = init || {};
        return nativeFetch(input, options).then(function (response) {
            var url = typeof input === 'string' ? input : (input && input.url ? input.url : '');
            if (response.status !== 419 || options._csrfRetried || url.indexOf('/csrf-token') !== -1) {
                return response;
            }

            return window.refreshCsrfToken().then(function () {
                var retry = Object.assign({}, options, { _csrfRetried: true });
                var meta = document.querySelector('meta[name="csrf-token"]');
                var token = meta ? meta.getAttribute('content') : '';
                if (token) {
                    var headers = new Headers(retry.headers || {});
                    headers.set('X-CSRF-TOKEN', token);
                    retry.headers = headers;
                    if (retry.body instanceof FormData) {
                        retry.body.set('_token', token);
                    }
                }
                return nativeFetch(input, retry);
            });
        });
    };

    if (window.axios) {
        axios.interceptors.response.use(function (response) {
            return response;
        }, function (error) {
            var config = error.config || {};
            var url = config.url || '';
            if (!(error.response && error.response.status === 419) || config._csrfRetried || url.indexOf('/csrf-token') !== -1) {
                return Promise.reject(error);
            }
            config._csrfRetried = true;
            return window.refreshCsrfToken().then(function (token) {
                var meta = document.querySelector('meta[name="csrf-token"]');
                config.headers = config.headers || {};
                if (meta) {
                    config.headers['X-CSRF-TOKEN'] = meta.getAttribute('content');
                }
                if (token && config.data instanceof FormData) {
                    config.data.set('_token', token);
                }
                return axios(config);
            });
        });
    }

    syncCsrfToForms();

    var keepAliveRunning = false;
    function keepAlive() {
        if (keepAliveRunning) {
            return;
        }
        keepAliveRunning = true;
        window.refreshCsrfToken().finally(function () {
            keepAliveRunning = false;
        });
    }
    setInterval(keepAlive, 4 * 60 * 1000);
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') {
            keepAlive();
        }
    });

    window.addEventListener('pageshow', function (event) {
        if (event.persisted) {
            window.location.reload();
        }
    });

    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!form || form.tagName !== 'FORM' || form.method.toUpperCase() !== 'POST') {
            return;
        }
        if (form.classList.contains('login-form')) {
            return;
        }
        if (form.id === 'logout-form' || form.id === 'admin-logout-form') {
            return;
        }
        syncCsrfToForms();
    }, true);

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js?v=4').then(function (registration) {
            registration.update();
        }).catch(function () {});
    }
})();
</script>
