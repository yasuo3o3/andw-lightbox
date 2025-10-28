(function (window, document) {
    'use strict';

    if (typeof window.GLightbox === 'function') {
        return;
    }

    function extend(target, source) {
        var obj = {};
        for (var key in target) {
            if (Object.prototype.hasOwnProperty.call(target, key)) {
                obj[key] = target[key];
            }
        }
        for (var sKey in source) {
            if (Object.prototype.hasOwnProperty.call(source, sKey)) {
                obj[sKey] = source[sKey];
            }
        }
        return obj;
    }

    function LightboxInstance(options) {
        this.settings = extend({
            selector: '.glightbox',
            slideEffect: 'slide',
            openEffect: 'zoom',
            closeEffect: 'zoom',
            loop: false,
            touchNavigation: true
        }, options || {});

        this.elements = [];
        this.collection = [];
        this.activeIndex = 0;
        this.overlay = null;
        this.boundHandler = this.handleTrigger.bind(this);
        this.keyHandler = this.handleKeydown.bind(this);
        this.refresh();
    }

    LightboxInstance.prototype.refresh = function () {
        this.detach();
        try {
            this.elements = Array.prototype.slice.call(document.querySelectorAll(this.settings.selector));
        } catch (e) {
            this.elements = [];
        }
        for (var i = 0; i < this.elements.length; i++) {
            this.elements[i].addEventListener('click', this.boundHandler);
        }
    };

    LightboxInstance.prototype.detach = function () {
        if (!this.elements || !this.elements.length) {
            return;
        }
        for (var i = 0; i < this.elements.length; i++) {
            this.elements[i].removeEventListener('click', this.boundHandler);
        }
        this.elements = [];
    };

    LightboxInstance.prototype.handleTrigger = function (event) {
        event.preventDefault();
        this.openFrom(event.currentTarget || event.target);
    };

    LightboxInstance.prototype.buildCollection = function (trigger) {
        var gallery = trigger.getAttribute('data-gallery');
        if (gallery) {
            this.collection = this.elements.filter(function (el) {
                return el.getAttribute('data-gallery') === gallery;
            });
        } else {
            this.collection = [trigger];
        }

        if (!this.collection.length) {
            this.collection = [trigger];
        }

        this.activeIndex = this.collection.indexOf(trigger);
        if (this.activeIndex < 0) {
            this.activeIndex = 0;
        }
    };

    LightboxInstance.prototype.openFrom = function (trigger) {
        this.buildCollection(trigger);
        this.buildOverlay();
        this.showSlide(this.activeIndex);
    };

    LightboxInstance.prototype.buildOverlay = function () {
        this.destroyOverlay();

        var overlay = document.createElement('div');
        overlay.className = 'andw-glightbox-backdrop andw-glightbox-effect-' + this.getEffectKey();

        var stage = document.createElement('div');
        stage.className = 'andw-glightbox-stage';

        var figure = document.createElement('figure');
        var img = document.createElement('img');
        img.className = 'andw-glightbox-image';
        img.alt = '';
        figure.appendChild(img);

        var caption = document.createElement('div');
        caption.className = 'andw-glightbox-caption';
        var captionTitle = document.createElement('h2');
        caption.appendChild(captionTitle);
        var captionBody = document.createElement('p');
        caption.appendChild(captionBody);
        figure.appendChild(caption);

        stage.appendChild(figure);

        var closeBtn = this.buildButton('andw-glightbox-close', 'X', 'Close');
        var prevBtn = this.buildButton('andw-glightbox-prev', '<', 'Previous');
        var nextBtn = this.buildButton('andw-glightbox-next', '>', 'Next');

        stage.appendChild(prevBtn);
        stage.appendChild(nextBtn);
        stage.appendChild(closeBtn);

        overlay.appendChild(stage);
        document.body.appendChild(overlay);
        window.requestAnimationFrame(function () {
            overlay.classList.add('is-active');
        });
        document.body.classList.add('andw-glightbox-open');

        overlay.addEventListener('click', this.handleBackdropClick.bind(this));
        closeBtn.addEventListener('click', this.close.bind(this));
        prevBtn.addEventListener('click', this.showPrevious.bind(this));
        nextBtn.addEventListener('click', this.showNext.bind(this));
        document.addEventListener('keydown', this.keyHandler);

        this.overlay = overlay;
        this.imageEl = img;
        this.titleEl = captionTitle;
        this.descriptionEl = captionBody;
        this.closeBtn = closeBtn;
        this.prevBtn = prevBtn;
        this.nextBtn = nextBtn;

        this.focusables = [prevBtn, nextBtn, closeBtn].filter(function (el) {
            return !!el;
        });
        closeBtn.focus({ preventScroll: true });
    };

    LightboxInstance.prototype.destroyOverlay = function () {
        if (!this.overlay) {
            return;
        }
        this.overlay.parentNode.removeChild(this.overlay);
        this.overlay = null;
    };

    LightboxInstance.prototype.getEffectKey = function () {
        var effect = this.settings.openEffect;
        if (effect === 'fade' || effect === 'zoom' || effect === 'none') {
            return effect;
        }
        return 'zoom';
    };

    LightboxInstance.prototype.showSlide = function (index) {
        if (!this.collection.length) {
            return;
        }

        if (index < 0) {
            index = this.settings.loop ? this.collection.length - 1 : 0;
        }

        if (index >= this.collection.length) {
            index = this.settings.loop ? 0 : this.collection.length - 1;
        }

        this.activeIndex = index;
        this.renderItem(this.collection[index]);
        this.updateNavState();
    };

    LightboxInstance.prototype.updateNavState = function () {
        if (!this.prevBtn || !this.nextBtn) {
            return;
        }

        if (this.collection.length <= 1) {
            this.prevBtn.disabled = true;
            this.nextBtn.disabled = true;
            return;
        }

        if (this.settings.loop) {
            this.prevBtn.disabled = false;
            this.nextBtn.disabled = false;
            return;
        }

        this.prevBtn.disabled = (0 === this.activeIndex);
        this.nextBtn.disabled = (this.activeIndex === this.collection.length - 1);
    };

    LightboxInstance.prototype.applyAnimation = function(animation) {
        if (!this.overlay) {
            return;
        }

        var effectClasses = ['andw-glightbox-effect-fade', 'andw-glightbox-effect-zoom', 'andw-glightbox-effect-slide', 'andw-glightbox-effect-none'];

        // 既存エフェクトクラスをクリア
        for (var i = 0; i < effectClasses.length; i++) {
            this.overlay.classList.remove(effectClasses[i]);
        }

        // 新しいエフェクトクラスを追加
        if (animation && animation !== 'default') {
            this.overlay.classList.add('andw-glightbox-effect-' + animation);
        }
    };

    LightboxInstance.prototype.renderItem = function (element) {
        if (!this.imageEl) {
            return;
        }

        var src = element.getAttribute('href') || element.getAttribute('data-href');
        var title = element.getAttribute('data-title') || '';
        var description = element.getAttribute('data-description') || '';
        var aria = element.getAttribute('aria-label') || '';
        var animation = element.getAttribute('data-andw-animation') || 'slide';
        var self = this;

        // アニメーション動的切り替え
        this.applyAnimation(animation);

        this.imageEl.classList.add('is-transitioning');

        var loader = new Image();
        loader.onload = function () {
            self.imageEl.src = src;
            self.imageEl.alt = aria;
            self.imageEl.classList.remove('is-transitioning');
        };
        loader.onerror = function () {
            self.imageEl.classList.remove('is-transitioning');
        };
        loader.src = src;

        if (title) {
            this.titleEl.textContent = title;
            this.titleEl.style.display = '';
        } else {
            this.titleEl.textContent = '';
            this.titleEl.style.display = 'none';
        }

        if (description) {
            this.descriptionEl.textContent = description;
            this.descriptionEl.style.display = '';
        } else {
            this.descriptionEl.textContent = '';
            this.descriptionEl.style.display = 'none';
        }
    };

    LightboxInstance.prototype.showPrevious = function () {
        this.showSlide(this.activeIndex - 1);
    };

    LightboxInstance.prototype.showNext = function () {
        this.showSlide(this.activeIndex + 1);
    };

    LightboxInstance.prototype.handleBackdropClick = function (event) {
        if (event.target === this.overlay) {
            this.close();
        }
    };

    LightboxInstance.prototype.handleKeydown = function (event) {
        if (!this.overlay) {
            return;
        }

        switch (event.key) {
            case 'Escape':
                event.preventDefault();
                this.close();
                break;
            case 'ArrowLeft':
                event.preventDefault();
                this.showPrevious();
                break;
            case 'ArrowRight':
                event.preventDefault();
                this.showNext();
                break;
            case 'Tab':
                this.maintainFocus(event);
                break;
        }
    };

    LightboxInstance.prototype.maintainFocus = function (event) {
        if (!this.focusables.length) {
            return;
        }

        var first = this.focusables[0];
        var last = this.focusables[this.focusables.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    };

    LightboxInstance.prototype.close = function () {
        if (!this.overlay) {
            return;
        }

        document.removeEventListener('keydown', this.keyHandler);
        var overlay = this.overlay;
        overlay.classList.remove('is-active');
        document.body.classList.remove('andw-glightbox-open');

        window.setTimeout(function () {
            if (overlay && overlay.parentNode) {
                overlay.parentNode.removeChild(overlay);
            }
        }, 200);

        this.overlay = null;
        this.imageEl = null;
        this.titleEl = null;
        this.descriptionEl = null;
    };

    LightboxInstance.prototype.destroy = function () {
        this.close();
        this.detach();
    };

    LightboxInstance.prototype.buildButton = function (className, symbol, label) {
        var button = document.createElement('button');
        button.type = 'button';
        button.className = className;
        button.setAttribute('aria-label', label);
        var icon = document.createElement('span');
        icon.className = 'andw-glightbox-icon';
        icon.textContent = symbol;
        button.appendChild(icon);
        return button;
    };

    window.GLightbox = function (options) {
        return new LightboxInstance(options || {});
    };

})(window, document);
