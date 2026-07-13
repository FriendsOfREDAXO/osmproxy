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

    function setStatus(target, text, isError) {
        if (!target) {
            return;
        }

        target.textContent = text;
        target.classList.toggle('is-error', Boolean(isError));
    }

    function updateRasterStatus(container) {
        const status = document.querySelector('[data-osmproxy-raster-status]');
        const url = document.querySelector('[data-osmproxy-raster-url]');
        const image = container?.querySelector('.osmproxy-demo-raster-image');

        if (!status || !image) {
            return;
        }

        const applyLoadedState = function () {
            const currentUrl = image.currentSrc || image.src || '';
            if (url && currentUrl) {
                url.textContent = currentUrl;
            }

            if (image.naturalWidth > 0) {
                setStatus(status, `geladen (${image.naturalWidth}x${image.naturalHeight})`);
            } else {
                setStatus(status, 'geladen, aber ohne Bilddaten', true);
            }
        };

        if (image.complete) {
            applyLoadedState();
        } else {
            setStatus(status, 'lädt Raster-Tile …');
            image.addEventListener('load', applyLoadedState, {once: true});
            image.addEventListener('error', function () {
                setStatus(status, 'Raster-Tile konnte nicht geladen werden', true);
            }, {once: true});
        }

        window.setTimeout(function () {
            if (image.complete && image.naturalWidth > 0) {
                applyLoadedState();
            }
        }, 250);
    }

    function initMap(container) {
        if (!container || !window.maplibregl) {
            const status = document.querySelector('[data-osmproxy-vector-status]');
            setStatus(status, 'MapLibre ist nicht verfügbar', true);
            return;
        }

        if (container.dataset.osmproxyInitialized === '1') {
            return;
        }

        container.dataset.osmproxyInitialized = '1';
        setStatus(document.querySelector('[data-osmproxy-vector-status]'), 'initialisiert …');

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
        map.on('load', function () {
            const status = document.querySelector('[data-osmproxy-vector-status]');
            const styleTarget = document.querySelector('[data-osmproxy-vector-style]');
            if (styleTarget && styleUrl) {
                styleTarget.textContent = styleUrl;
            }

            setStatus(status, 'geladen');
        });
        map.on('error', function (event) {
            const status = document.querySelector('[data-osmproxy-vector-status]');
            const message = event?.error?.message || 'Vector-Map konnte nicht geladen werden';
            setStatus(status, message, true);
        });
    }

    function boot() {
        const container = document.getElementById('osmproxy-vector-map');
        if (!container) {
            return;
        }

        updateRasterStatus(document.querySelector('.osmproxy-demo-box--raster'));
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
