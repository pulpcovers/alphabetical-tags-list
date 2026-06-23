(function () {
    var isScrolling = false;
    var scrollTimeout;

    // Smooth scroll for jump links
    document.querySelectorAll('.atl-jump-link:not(.disabled)').forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            var targetId = this.getAttribute('href').substring(1);
            var targetElement = document.getElementById(targetId);
            if (targetElement) {
                isScrolling = true;
                targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });

                // Update active state immediately
                document.querySelectorAll('.atl-jump-link').forEach(function (l) {
                    l.classList.remove('active');
                });
                this.classList.add('active');

                // Remove focus to prevent outline persistence
                this.blur();

                // Reset isScrolling flag after scroll completes
                setTimeout(function () {
                    isScrolling = false;
                }, 1000);
            }
        });
    });

    // Update active link based on scroll position
    function updateActiveLink() {
        if (isScrolling) return;

        var sections = document.querySelectorAll('.atl-letter-section');
        var navLinks = document.querySelectorAll('.atl-jump-link:not(.disabled)');

        var navHeight = document.querySelector('.atl-jump-nav')?.offsetHeight || 0;
        var adminBarHeight = 0;

        if (window.innerWidth > 782) {
            adminBarHeight = document.querySelector('#wpadminbar')?.offsetHeight || 0;
        }

        var offset = navHeight + adminBarHeight + 10;
        var currentSection = null;

        sections.forEach(function (section) {
            var rect = section.getBoundingClientRect();
            if (rect.top <= offset && rect.bottom > offset) {
                currentSection = section;
            }
        });

        if (!currentSection) {
            var closestSection = null;
            var closestDistance = Infinity;

            sections.forEach(function (section) {
                var rect = section.getBoundingClientRect();
                if (rect.top < offset) {
                    var distance = offset - rect.top;
                    if (distance < closestDistance) {
                        closestDistance = distance;
                        closestSection = section;
                    }
                }
            });

            currentSection = closestSection;
        }

        if (currentSection) {
            var id = currentSection.getAttribute('id');
            navLinks.forEach(function (link) {
                if (link.getAttribute('href') === '#' + id) {
                    if (!link.classList.contains('active')) {
                        navLinks.forEach(function (l) {
                            l.classList.remove('active');
                            l.blur();
                        });
                        link.classList.add('active');
                    }
                }
            });
        }
    }

    window.addEventListener('scroll', function () {
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(updateActiveLink, 50);
    }, { passive: true });

    var resizeTimeout;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(updateActiveLink, 100);
    });

    updateActiveLink();
})();
