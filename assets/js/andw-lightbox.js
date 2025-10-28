/**
 * andW Lightbox - WordPress GLightbox Integration
 *
 * This file integrates GLightbox library for WordPress
 * GLightbox Copyright (c) 2018 Biati Digital (https://www.biati.digital)
 * GLightbox is licensed under the MIT License
 *
 * @author Netservice
 * @license GPLv2 or later
 */
(function (window, document) {
    'use strict';

    var settings = window.andwLightboxSettings || {};
    var instances = [];
    var observerEnabled = !!settings.observer;
    var observer = null;
    var refreshTimer = null;
    var savedScrollPosition = 0;

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
            case 'default':
                return '.glightbox:not([data-andw-animation])';
            default:
                return null;
        }
    }

    function getOptions(effect, selector) {
        var resolvedEffect;

        // デフォルト効果の場合は設定値を使用
        if (effect === 'default') {
            resolvedEffect = resolveSlideEffect(settings.defaultAnimation);
        } else {
            resolvedEffect = resolveSlideEffect(effect);
        }

        var opts = {
            selector: selector,
            touchNavigation: true,
            loop: false,
            slideEffect: resolvedEffect,
            height: '80vh',
            zoomable: true,
            draggable: true,
            onOpen: function() {
                savedScrollPosition = window.pageYOffset || document.documentElement.scrollTop;
            },
            onClose: function() {
                setTimeout(function() {
                    window.scrollTo(0, savedScrollPosition);
                }, 50);
            }
        };

        // アニメーション効果を開閉効果にも適用
        if (resolvedEffect === 'none') {
            opts.openEffect = 'none';
            opts.closeEffect = 'none';
        } else if (resolvedEffect === 'fade') {
            opts.openEffect = 'fade';
            opts.closeEffect = 'fade';
        } else if (resolvedEffect === 'zoom') {
            opts.openEffect = 'zoom';
            opts.closeEffect = 'zoom';
        } else {
            // slide または その他の場合はzoomを使用（GLightboxのデフォルト）
            opts.openEffect = 'zoom';
            opts.closeEffect = 'zoom';
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
            if (!selector || !document.querySelector(selector)) {
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

    // GLightbox動的読み込み完了時の再初期化
    document.addEventListener('andwLightboxReady', function(event) {
        if (typeof window.console !== 'undefined' && typeof window.console.log === 'function') {
            window.console.log('andW Lightbox: Re-initializing after GLightbox ready from ' + event.detail.source);
        }
        init();
    });

    // グローバルに再初期化関数を公開（デバッグ用）
    window.andwLightboxReinit = function() {
        init();
        return 'andW Lightbox re-initialized';
    };

    ready(function () {
        init();
        observe();
    });

})(window, document);
