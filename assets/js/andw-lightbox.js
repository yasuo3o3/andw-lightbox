(function (window, document) {
    'use strict';

    var settings = window.andwLightboxSettings || {};
    var instances = [];
    var observerEnabled = !!settings.observer;
    var observer = null;
    var refreshTimer = null;

    function ready(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    function destroyInstances() {
        instances.forEach(function (instance) {
            if (instance && typeof instance.destroy === 'function') {
                instance.destroy();
            }
        });
        instances = [];
    }

    function resolveSlideEffect(effect) {
        var allowed = ['slide', 'fade', 'zoom', 'none'];

        if (allowed.indexOf(effect) !== -1) {
            return effect;
        }

        var fallback = settings.defaultAnimation;
        if (allowed.indexOf(fallback) === -1) {
            fallback = 'slide';
        }

        return fallback;
    }

    function selectorFor(effect) {
        switch (effect) {
            case 'fade':
                return '.glightbox[data-andw-animation="fade"]';
            case 'zoom':
                return '.glightbox[data-andw-animation="zoom"]';
            case 'slide':
                return '.glightbox[data-andw-animation="slide"]';
            case 'none':
                return '.glightbox[data-andw-animation="none"]';
            default:
                return '.glightbox:not([data-andw-animation])';
        }
    }

    function getOptions(effect, selector) {
        var resolvedEffect = resolveSlideEffect(effect);
        var opts = {
            selector: selector,
            touchNavigation: true,
            loop: false,
            slideEffect: resolvedEffect
        };

        if (resolvedEffect === 'none') {
            opts.openEffect = 'none';
            opts.closeEffect = 'none';
        }

        return opts;
    }

    function buildInstances() {
        if (typeof window.GLightbox !== 'function') {
            if (window.console && typeof window.console.warn === 'function') {
                window.console.warn('andW Lightbox: GLightbox is not available.');
            }
            return;
        }

        var effects = ['default', 'fade', 'zoom', 'slide', 'none'];
        effects.forEach(function (effect) {
            var selector = selectorFor(effect);
            if (!document.querySelector(selector)) {
                return;
            }

            var instance = window.GLightbox(getOptions(effect, selector));
            if (instance) {
                instances.push(instance);
            }
        });
    }

    function init() {
        destroyInstances();
        if (!document.querySelector('.glightbox')) {
            return;
        }
        buildInstances();
    }

    function scheduleRefresh() {
        if (!observerEnabled) {
            return;
        }
        if (refreshTimer) {
            window.clearTimeout(refreshTimer);
        }
        refreshTimer = window.setTimeout(init, 200);
    }

    function observe() {
        if (!observerEnabled || !('MutationObserver' in window)) {
            return;
        }

        if (observer) {
            observer.disconnect();
        }

        observer = new MutationObserver(function (records) {
            for (var i = 0; i < records.length; i++) {
                if (records[i].addedNodes && records[i].addedNodes.length) {
                    scheduleRefresh();
                    break;
                }
            }
        });

        observer.observe(document.body, { childList: true, subtree: true });
    }

    ready(function () {
        init();
        observe();
    });

})(window, document);
