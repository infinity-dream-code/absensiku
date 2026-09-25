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

    window.refreshCsrfToken = function () {
        return fetch('/csrf-token', {
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

    syncCsrfToForms();

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
        navigator.serviceWorker.getRegistrations().then(function (registrations) {
            registrations.forEach(function (registration) {
                registration.unregister();
            });
        });
    }
})();
</script>
