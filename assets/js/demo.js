(function () {
    'use strict';

    function addStylesheet(href) {
        if (!href) {
            return;
        }

        if ([...document.querySelectorAll('link[rel="stylesheet"]')].some((link) => link.href === href)) {
            return;
        }

        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = href;
        document.head.appendChild(link);
    }

    function addScript(src, callback) {
        if (!src) {
            callback();
            return;
        }

        if (window.maplibregl) {
            callback();
            return;
        }

        if ([...document.querySelectorAll('script')].some((script) => script.src === src)) {
            const waitForLibrary = window.setInterval(() => {
                if (window.maplibregl) {
                    window.clearInterval(waitForLibrary);
                    callback();
                }
            }, 50);
            return;
        }

        const script = document.createElement('script');
        script.src = src;
        script.async = true;
        script.onload = callback;
        script.onerror = callback;
        document.body.appendChild(script);
    }

    function initMap(container) {
        if (!container || !window.maplibregl) {
            return;
        }

        if (container.dataset.osmproxyInitialized === '1') {
            return;
        }

        container.dataset.osmproxyInitialized = '1';

        const styleUrl = container.dataset.mapStyle;
        const lat = parseFloat(container.dataset.mapLat || '51.43');
        const lng = parseFloat(container.dataset.mapLng || '6.77');
        const zoom = parseFloat(container.dataset.mapZoom || '13');

        const map = new window.maplibregl.Map({
            container,
            style: styleUrl,
            center: [lng, lat],
            zoom,
        });

        map.addControl(new window.maplibregl.NavigationControl(), 'top-right');
    }

    function boot() {
        const container = document.getElementById('osmproxy-vector-map');
        if (!container) {
            return;
        }

        addStylesheet(container.dataset.maplibreCss);
        addScript(container.dataset.maplibreJs, function () {
            initMap(container);
        });
    }

    if (typeof window.jQuery === 'function') {
        window.jQuery(document).on('rex:ready', boot);
    }

    if (document.readyState !== 'loading') {
        boot();
    }
})();
