<script>
(function () {
    function syncCsrfToken(token) {
        if (!token) {
            return;
        }

        var meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) {
            meta.setAttribute('content', token);
        }

        document.querySelectorAll('input[name="_token"]').forEach(function (input) {
            input.value = token;
        });
    }

    function refreshCsrfToken() {
        return fetch('/csrf-token', {
            method: 'GET',
            credentials: 'same-origin',
            cache: 'no-store',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Cache-Control': 'no-cache'
            }
        })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                syncCsrfToken(data.token);
                return data.token;
            })
            .catch(function () { return null; });
    }

    document.addEventListener('DOMContentLoaded', function () {
        refreshCsrfToken();

        setInterval(refreshCsrfToken, 3 * 60 * 1000);

        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) {
                refreshCsrfToken();
            }
        });

        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                refreshCsrfToken();
            }
        });

        document.querySelectorAll('form.login-form').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (form.dataset.csrfReady === '1') {
                    form.dataset.csrfReady = '0';
                    return;
                }

                event.preventDefault();

                refreshCsrfToken().finally(function () {
                    form.dataset.csrfReady = '1';
                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit();
                    } else {
                        form.submit();
                    }
                });
            });
        });
    });

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.getRegistrations().then(function (registrations) {
            registrations.forEach(function (registration) {
                registration.unregister();
            });
        });
    }
})();
</script>
