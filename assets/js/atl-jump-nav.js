(function () {
    var nav = document.querySelector('.atl-jump-nav');
    var sections = document.querySelectorAll('.atl-letter-section');
    var navLinks = document.querySelectorAll('.atl-jump-link:not(.disabled)');

    if (!navLinks.length || !sections.length) return;

    var isScrolling = false;
    var scrollFallbackTimeout;
    var observer = null;
    var hasScrollEnd = 'onscrollend' in window;

    var linksByTarget = {};
    navLinks.forEach(function (link) {
        linksByTarget[link.getAttribute('href')] = link;
    });

    function setActive(link) {
        navLinks.forEach(function (l) {
            var isActive = l === link;
            l.classList.toggle('active', isActive);
            if (isActive) {
                l.setAttribute('aria-current', 'true');
            } else {
                l.removeAttribute('aria-current');
            }
        });
    }

    function finishScrolling() {
        clearTimeout(scrollFallbackTimeout);
        isScrolling = false;
    }

    // Smooth scroll for jump links
    navLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            var target = document.getElementById(this.getAttribute('href').substring(1));
            if (!target) return;

            var reduceMotion = window.matchMedia &&
                window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            isScrolling = true;
            target.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
            setActive(this);

            // Keep the URL shareable and move keyboard focus to the section
            if (window.history && history.replaceState) {
                history.replaceState(null, '', this.getAttribute('href'));
            }
            var heading = target.querySelector('.atl-letter-heading');
            if (heading) heading.focus({ preventScroll: true });

            // scrollend is exact where supported; the timer covers other browsers
            // and the case where no scroll happens at all
            clearTimeout(scrollFallbackTimeout);
            scrollFallbackTimeout = setTimeout(finishScrolling, hasScrollEnd ? 1500 : 1000);
        });
    });

    if (hasScrollEnd) {
        window.addEventListener('scrollend', finishScrolling);
    }

    // Sticky offset is resolved by the browser from CSS, so it follows the admin bar
    function getOffset() {
        var stickyTop = nav ? parseFloat(getComputedStyle(nav).top) || 0 : 0;
        var navHeight = nav ? nav.offsetHeight : 0;
        return stickyTop + navHeight + 10;
    }

    // A section is "current" while it crosses a 1px line at the offset, which
    // replaces per-scroll getBoundingClientRect work on every section.
    function observeSections() {
        if (observer) observer.disconnect();

        var offset = Math.round(getOffset());
        var bottomMargin = Math.max(window.innerHeight - offset - 1, 0);

        observer = new IntersectionObserver(function (entries) {
            if (isScrolling) return;
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                var link = linksByTarget['#' + entry.target.id];
                if (link) setActive(link);
            });
        }, {
            rootMargin: '-' + offset + 'px 0px -' + bottomMargin + 'px 0px',
            threshold: 0
        });

        sections.forEach(function (section) {
            observer.observe(section);
        });
    }

    if (!('IntersectionObserver' in window)) return;

    observeSections();

    var resizeTimeout;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(observeSections, 150);
    });
})();
