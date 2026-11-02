(function () {
    'use strict';

    function isExternalWebUrl(value) {
        if (!value || /^(mailto:|tel:|javascript:|#)/i.test(value)) {
            return false;
        }

        try {
            return new URL(value, window.location.href).origin !== window.location.origin;
        } catch (error) {
            return true;
        }
    }

    function notifyBlockedNavigation() {
        window.alert('External website navigation is disabled.');
    }

    document.addEventListener('click', function (event) {
        var anchor = event.target instanceof Element ? event.target.closest('a[href]') : null;
        if (anchor && isExternalWebUrl(anchor.getAttribute('href'))) {
            event.preventDefault();
            event.stopImmediatePropagation();
            notifyBlockedNavigation();
        }
    }, true);

    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (form instanceof HTMLFormElement && isExternalWebUrl(form.getAttribute('action'))) {
            event.preventDefault();
            event.stopImmediatePropagation();
            notifyBlockedNavigation();
        }
    }, true);

    var originalSubmit = HTMLFormElement.prototype.submit;
    HTMLFormElement.prototype.submit = function () {
        if (isExternalWebUrl(this.getAttribute('action'))) {
            notifyBlockedNavigation();
            return;
        }

        return originalSubmit.call(this);
    };

    var originalOpen = window.open;
    window.open = function (url) {
        if (isExternalWebUrl(url)) {
            notifyBlockedNavigation();
            return null;
        }

        return originalOpen.apply(window, arguments);
    };
})();
