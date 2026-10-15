/* ---------------------------------------------------------------------------
   Cardyshub — supplementary behaviour
   ---------------------------------------------------------------------------
   Loaded directly rather than through Vite, because this machine has no Node
   toolchain to rebuild `resources/js/app.js` with. Alpine is already running by
   the time this executes, so anything declarative still belongs in Blade with
   x-data; this file is for the few things that need a real scroll listener.

   The header behaviour that used to live in app.js was moved here so there is
   only one implementation — see the note in that file.
   --------------------------------------------------------------------------- */
(function () {
    'use strict';

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ---------- Sticky header, condensed once past the fold ----------
       Always visible: no hide-on-scroll. It shrinks instead, so it keeps its
       reachability without eating the viewport on a phone. */
    var header = document.querySelector('[data-site-header]');

    if (header) {
        // Anything app.js may have left behind from the old hide-on-scroll.
        header.style.transform = '';

        var CONDENSE_AT = 80;

        // Deliberately synchronous rather than rAF-throttled: this only reads
        // window.scrollY and toggles two classes, so it forces no layout, and
        // running in the event itself guarantees it lands after the old
        // hide-on-scroll listener still present in the built app.js bundle
        // (listeners fire in registration order, and this file loads second).
        var applyHeaderState = function () {
            var y = window.scrollY;
            header.classList.toggle('shadow-card', y > 8);
            header.classList.toggle('is-condensed', y > CONDENSE_AT);

            // Undo the translateY(-100%) that the stale bundle still applies.
            // Remove this line once app.js is rebuilt from the updated source.
            if (header.style.transform) {
                header.style.transform = '';
            }
        };

        window.addEventListener('scroll', applyHeaderState, { passive: true });
        applyHeaderState();
    }

    /* ---------- Smooth scroll for in-page anchors ----------
       CSS `scroll-behavior` covers most of this, but doing it here lets us keep
       the URL hash out of the history stack for decorative jumps and honour the
       reduced-motion preference consistently across browsers. */
    document.addEventListener('click', function (event) {
        var link = event.target.closest('a[href^="#"]');

        if (!link) {
            return;
        }

        var id = link.getAttribute('href');

        if (id === '#' || id.length < 2) {
            return;
        }

        var target = document.getElementById(id.slice(1));

        if (!target) {
            return;
        }

        event.preventDefault();
        target.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });

        // Keep the anchor addressable without pushing a new history entry.
        if (window.history.replaceState) {
            window.history.replaceState(null, '', id);
        }

        // Move focus for keyboard and screen reader users.
        if (!target.hasAttribute('tabindex')) {
            target.setAttribute('tabindex', '-1');
        }

        target.focus({ preventScroll: true });
    });

    /* ---------- WhatsApp widget: pulse once things have settled ---------- */
    var wa = document.querySelector('.wa-widget');

    if (wa && !reduceMotion) {
        window.setTimeout(function () {
            wa.classList.add('is-ready');
        }, 2500);
    }
})();

/* ---------------------------------------------------------------------------
   Cardyshub — cart, wishlist and navigation feedback
   ---------------------------------------------------------------------------
   Three things live here:

     1. Cart and wishlist forms posted with fetch instead of a full navigation.
        The forms stay real forms pointing at the real routes — this only
        intercepts the submit event — so with JavaScript off, or before this
        file has run, everything still works via the redirect responses.

     2. Toasts for those replies, matching resources/views/components/toast.blade.php.
        That component still handles flashed session messages on full loads.

     3. A navigation preloader: a hairline progress bar shown the moment a click
        or a submit starts a real page load.

   Deliberately a second IIFE rather than part of the block above: registering
   after the anchor handler means `event.defaultPrevented` is already correct by
   the time the preloader decides whether a click will navigate.
   --------------------------------------------------------------------------- */
(function () {
    'use strict';

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

    function each(list, callback) {
        Array.prototype.forEach.call(list, callback);
    }

    function findAll(selector, root) {
        return (root || document).querySelectorAll(selector);
    }

    /* =====================================================================
       Toasts
       ===================================================================== */

    // Lucide check-circle-2 and alert-circle, inlined so a toast costs no
    // network round trip. Kept in step with resources/svg/icons/.
    var TOAST_ICON = {
        success: '<circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>',
        error: '<circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/>'
    };

    var TOAST_LIFETIME = 4000; // Matches the Blade toast component.
    var MAX_TOASTS = 3;

    function dismissToast(el) {
        if (!el || el.getAttribute('data-leaving') === '1') {
            return;
        }

        el.setAttribute('data-leaving', '1');
        el.classList.remove('is-in');
        el.classList.add('is-leaving');

        window.setTimeout(function () {
            if (el.parentNode) {
                el.parentNode.removeChild(el);
            }
        }, reduceMotion ? 0 : 300);
    }

    function toast(message, variant) {
        var stack = document.querySelector('[data-toast-stack]');

        if (!stack || !message) {
            return;
        }

        variant = variant === 'error' ? 'error' : 'success';

        var el = document.createElement('div');
        el.className = 'site-toast flex items-center gap-3 rounded-card bg-white shadow-lift border border-line px-4 py-3';
        el.innerHTML =
            '<svg class="size-5 shrink-0 ' + (variant === 'error' ? 'text-alert' : 'text-mint') + '"' +
            ' xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"' +
            ' stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
            TOAST_ICON[variant] +
            '</svg>' +
            '<p class="text-sm text-ink"></p>' +
            '<button type="button" class="site-toast-close ml-auto text-ink-faint hover:text-ink">' +
            '<span class="sr-only">Dismiss</span>' +
            '<svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"' +
            ' fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"' +
            ' aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>' +
            '</button>';

        // textContent, never innerHTML: the message carries a product name
        // straight from the database.
        el.querySelector('p').textContent = message;
        el.querySelector('.site-toast-close').addEventListener('click', function () {
            dismissToast(el);
        });

        stack.appendChild(el);

        var live = findAll('.site-toast', stack);

        for (var i = 0; i < live.length - MAX_TOASTS; i++) {
            dismissToast(live[i]);
        }

        // Force a reflow so the transition has a starting state to run from.
        // Deliberately not requestAnimationFrame: rAF does not fire while the
        // tab is hidden or throttled, which would strand the toast at opacity 0
        // until it timed out and removed itself unseen.
        void el.offsetWidth;
        el.classList.add('is-in');

        window.setTimeout(function () {
            dismissToast(el);
        }, TOAST_LIFETIME);
    }

    /* =====================================================================
       Shared request plumbing
       ===================================================================== */

    function postForm(form) {
        // FormData already carries _token and the _method spoof, so the PATCH
        // and DELETE routes go over the same POST as everything else.
        return fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            }
        }).then(function (response) {
            var type = response.headers.get('content-type') || '';

            if (response.ok && type.indexOf('application/json') !== -1) {
                return response.json();
            }

            var error = new Error('Request failed with status ' + response.status);

            // An expired session or a stale CSRF token needs the browser to
            // follow a real redirect; neither can be resolved in place.
            error.needsNavigation = response.status === 401 ||
                response.status === 403 ||
                response.status === 419;

            throw error;
        });
    }

    function handleFailure(error, form) {
        if (error && error.needsNavigation) {
            form.removeAttribute('data-busy');
            startProgress();
            form.submit(); // Native submit: skips these listeners, so no loop.

            return;
        }

        toast('Sorry, that did not go through. Please try again.', 'error');
    }

    // The duplicate-click guard. Deliberately an attribute on the form rather
    // than a disabled button: disabling the control someone just pressed moves
    // focus, and the guard has to hold even when the press came from elsewhere
    // (the sticky mobile bar calls requestSubmit on the main form).
    function isBusy(form) {
        return form.getAttribute('data-busy') === '1';
    }

    function setBusy(form, button, busy) {
        if (busy) {
            form.setAttribute('data-busy', '1');
        } else {
            form.removeAttribute('data-busy');
        }

        if (button) {
            if (busy) {
                button.setAttribute('aria-busy', 'true');
            } else {
                button.removeAttribute('aria-busy');
            }
        }
    }

    function replay(el, className, duration) {
        el.classList.remove(className);
        void el.offsetWidth; // Force reflow so the keyframes restart.
        el.classList.add(className);

        window.setTimeout(function () {
            el.classList.remove(className);
        }, duration);
    }

    function setBadge(selector, count) {
        each(findAll(selector), function (badge) {
            var previous = parseInt(badge.textContent, 10);

            badge.textContent = count;
            badge.classList.toggle('is-empty', !count);

            if (count && !reduceMotion && count !== previous) {
                replay(badge, 'is-bumped', 420);
            }
        });
    }

    /* =====================================================================
       Cart
       ===================================================================== */

    function confirmOnButton(button) {
        // Only the pill buttons get the swap; the cart page's remove cross and
        // its hidden quantity submit have nothing sensible to say.
        if (!button || !button.classList.contains('btn') || button.getAttribute('data-confirming') === '1') {
            return;
        }

        var label = button.querySelector('span');

        if (!label) {
            return;
        }

        button.setAttribute('data-confirming', '1');
        var original = label.innerHTML;

        label.innerHTML =
            '<svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"' +
            ' fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"' +
            ' aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>Added';
        button.classList.add('is-confirmed');

        window.setTimeout(function () {
            label.innerHTML = original;
            button.classList.remove('is-confirmed');
            button.removeAttribute('data-confirming');
        }, 1600);
    }

    function applyCartSummary(cart) {
        if (!cart) {
            return;
        }

        setBadge('[data-cart-count]', cart.count);

        each(findAll('[data-cart-subtotal]'), function (el) {
            el.textContent = cart.subtotal;
        });

        var free = cart.free_delivery;
        var message = document.querySelector('[data-free-delivery-message]');

        if (message && free) {
            if (free.qualified) {
                message.className = 'text-sm text-ink mb-2 font-medium';
                message.textContent = 'You’ve unlocked free UK delivery. 🎉';
            } else {
                message.className = 'text-sm text-ink mb-2';
                message.innerHTML = 'Add <strong></strong> more for free UK delivery.';
                message.querySelector('strong').textContent = free.remaining;
            }
        }

        var bar = document.querySelector('[data-free-delivery-bar]');

        if (bar && free) {
            bar.style.width = free.progress + '%';
        }

        // Cart page rows, keyed by id, so a removed line disappears and the
        // survivors pick up their recalculated totals.
        var byId = {};

        (cart.items || []).forEach(function (item) {
            byId[item.id] = item;
        });

        each(findAll('[data-cart-item]'), function (row) {
            var item = byId[row.getAttribute('data-cart-item')];

            if (!item) {
                row.classList.add('is-removing');
                window.setTimeout(function () {
                    if (row.parentNode) {
                        row.parentNode.removeChild(row);
                    }
                }, reduceMotion ? 0 : 220);

                return;
            }

            var total = row.querySelector('[data-cart-line-total]');

            if (total) {
                total.textContent = item.line_total;
            }

            var quantity = row.querySelector('input[name="quantity"]');

            // Leave the field alone while it has focus, or we fight the user
            // mid-edit.
            if (quantity && document.activeElement !== quantity) {
                quantity.value = item.quantity;
            }
        });

        // The empty-bag state is server-rendered, so the last removal has to go
        // back to the server. [data-free-delivery] exists only on the cart page
        // and only while the bag has something in it.
        if (cart.count === 0 && document.querySelector('[data-free-delivery]')) {
            window.location.reload();
        }
    }

    function onCartSubmit(event) {
        var form = event.currentTarget;

        event.preventDefault();

        if (isBusy(form)) {
            return;
        }

        var button = event.submitter || form.querySelector('[type="submit"]');
        setBusy(form, button, true);

        postForm(form)
            .then(function (payload) {
                applyCartSummary(payload.cart);
                confirmOnButton(button);
                toast(payload.message, 'success');
            })
            .catch(function (error) {
                handleFailure(error, form);
            })
            .then(function () {
                setBusy(form, button, false);
            });
    }

    /* =====================================================================
       Wishlist
       ===================================================================== */

    // On the product page the button sits inside the add-to-bag form's layout
    // but belongs to a separate form by `form=`, because HTML has no nested
    // forms. So look outside the form before looking inside it.
    function wishlistToggleFor(form) {
        if (form.id) {
            var external = document.querySelector('[data-wishlist-toggle][form="' + form.id + '"]');

            if (external) {
                return external;
            }
        }

        return form.querySelector('[data-wishlist-toggle]');
    }

    // One product can appear more than once on a page (a card under "you may
    // also like" plus the page's own heart), so every control for it is synced.
    function syncWishlistState(productId, wishlisted) {
        each(findAll('[data-wishlist-form][data-product-id="' + productId + '"]'), function (form) {
            var toggle = wishlistToggleFor(form);

            if (!toggle) {
                return;
            }

            toggle.setAttribute('aria-pressed', wishlisted ? 'true' : 'false');

            var label = toggle.querySelector('[data-wishlist-label]');

            if (label) {
                label.textContent = wishlisted ? 'Remove from wishlist' : 'Add to wishlist';
            }

            if (wishlisted && !reduceMotion) {
                replay(toggle, 'is-popped', 460);
            }
        });
    }

    function pruneWishlistGrid(productId) {
        var grid = document.querySelector('[data-wishlist-grid]');

        if (!grid) {
            return;
        }

        var card = grid.querySelector('[data-product-card="' + productId + '"]');

        if (!card) {
            return;
        }

        card.classList.add('is-removing');

        window.setTimeout(function () {
            if (card.parentNode) {
                card.parentNode.removeChild(card);
            }

            // Nothing left: the empty state is server-rendered.
            if (!grid.querySelector('[data-product-card]')) {
                window.location.reload();
            }
        }, reduceMotion ? 0 : 260);
    }

    function onWishlistSubmit(event) {
        var form = event.currentTarget;

        event.preventDefault();

        if (isBusy(form)) {
            return;
        }

        var toggle = event.submitter || wishlistToggleFor(form);
        setBusy(form, toggle, true);

        postForm(form)
            .then(function (payload) {
                syncWishlistState(payload.product_id, payload.wishlisted);
                setBadge('[data-wishlist-count]', payload.count);
                toast(payload.message, 'success');

                if (!payload.wishlisted) {
                    pruneWishlistGrid(payload.product_id);
                }
            })
            .catch(function (error) {
                handleFailure(error, form);
            })
            .then(function () {
                setBusy(form, toggle, false);
            });
    }

    /* =====================================================================
       Navigation preloader
       ===================================================================== */

    var progress = document.querySelector('[data-nav-progress]');
    var progressStatus = document.querySelector('[data-nav-progress-status]');
    var progressTimer = null;

    // Ceiling on how long the bar may run. A navigation that never commits —
    // a cancelled download, a blocked popup, a server that simply never
    // answers — must not leave the bar stuck across the top of the page.
    var PROGRESS_CEILING = 20000;

    function startProgress() {
        if (!progress || progress.classList.contains('is-active')) {
            return;
        }

        progress.classList.add('is-active');

        if (progressStatus) {
            progressStatus.textContent = 'Loading…';
        }

        window.clearTimeout(progressTimer);
        progressTimer = window.setTimeout(stopProgress, PROGRESS_CEILING);
    }

    function stopProgress() {
        if (!progress) {
            return;
        }

        window.clearTimeout(progressTimer);
        progress.classList.remove('is-active');

        if (progressStatus) {
            progressStatus.textContent = '';
        }
    }

    function willNavigate(link, event) {
        // Something already handled it: the in-page anchor scroller above, an
        // Alpine handler, anything else.
        if (event.defaultPrevented) {
            return false;
        }

        // Middle click, or a modifier: the browser opens a tab or a window and
        // this page stays exactly where it is.
        if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return false;
        }

        if (link.target && link.target !== '_self') {
            return false;
        }

        if (link.hasAttribute('download') || link.hasAttribute('data-no-preloader')) {
            return false;
        }

        var href = link.getAttribute('href');

        if (!href || href.charAt(0) === '#') {
            return false;
        }

        var url;

        try {
            url = new URL(link.href, window.location.href);
        } catch (e) {
            return false;
        }

        // mailto:, tel:, whatsapp: and friends hand off to another app.
        if (url.protocol !== 'http:' && url.protocol !== 'https:') {
            return false;
        }

        if (url.origin !== window.location.origin) {
            return false;
        }

        // Same document, different fragment — a scroll, not a load.
        if (url.hash && url.href.split('#')[0] === window.location.href.split('#')[0]) {
            return false;
        }

        return true;
    }

    document.addEventListener('click', function (event) {
        var target = event.target;

        if (!target || typeof target.closest !== 'function') {
            return;
        }

        var link = target.closest('a[href]');

        if (link && willNavigate(link, event)) {
            startProgress();
        }
    });

    function onNavigatingSubmit(event) {
        var form = event.currentTarget;

        // A cart or wishlist handler already cancelled it, so nothing is going
        // to navigate and the bar would only ever be a lie.
        if (event.defaultPrevented) {
            return;
        }

        if (form.hasAttribute('data-no-preloader') || (form.target && form.target !== '_self')) {
            return;
        }

        startProgress();
    }

    /* =====================================================================
       Wiring
       ===================================================================== */

    // Bound per form rather than delegated from document, and that is not a
    // style preference. A `submit` event does not propagate past an ancestor
    // <form>, and the shop and brand pages wrap the whole product grid in
    // #shop-filter-form so that changing a filter re-submits it. Every card's
    // cart and wishlist form is therefore nested inside another form, and a
    // document-level submit listener never hears from them — silently, on the
    // two pages where adding to the bag matters most.
    //
    // Clicks are unaffected, so the link half of the preloader stays delegated.
    function bindForms() {
        each(findAll('form'), function (form) {
            if (form.getAttribute('data-submit-bound') === '1') {
                return;
            }

            form.setAttribute('data-submit-bound', '1');

            if (form.hasAttribute('data-cart-form')) {
                form.addEventListener('submit', onCartSubmit);
            } else if (form.hasAttribute('data-wishlist-form')) {
                form.addEventListener('submit', onWishlistSubmit);
            }

            // Every form gets this: the AJAX handlers above cancel their event
            // first, so onNavigatingSubmit bows out for them.
            form.addEventListener('submit', onNavigatingSubmit);
        });
    }

    // This file is deferred, so the document is already parsed. The listener is
    // a safety net for anything Alpine teleports in after us.
    bindForms();
    document.addEventListener('DOMContentLoaded', bindForms);

    // Back and forward. pageshow covers both a fresh load and a bfcache
    // restore, where the old document — bar and all — comes back as it was.
    window.addEventListener('pageshow', stopProgress);
    window.addEventListener('popstate', stopProgress);
})();
