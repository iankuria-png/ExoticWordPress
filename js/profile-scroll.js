/**
 * Profile Page Scroll Behavior
 * - Sticky contact bar (appears after hero leaves viewport)
 * - Scroll spy (highlights current section in anchor nav)
 * - Smooth scroll with sticky header offset
 *
 * Dependencies: None (vanilla JS)
 */
(function () {
    'use strict';

    // Bail if no profile hero on page
    var hero = document.querySelector('.profile-hero');
    if (!hero) return;

    var stickybar = document.getElementById('profile-stickybar');

    // ---------------------------------------------------------------
    // 1. STICKY BAR: Show/hide based on hero visibility
    // ---------------------------------------------------------------
    if (stickybar) {
        var heroObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                stickybar.classList.toggle('is-visible', !entry.isIntersecting);
            });
        }, {
            threshold: 0.15
        });

        heroObserver.observe(hero);
    }

    // ---------------------------------------------------------------
    // 2. SCROLL SPY: Highlight active section in sticky nav
    // ---------------------------------------------------------------
    var navLinks = stickybar
        ? Array.from(stickybar.querySelectorAll('.profile-stickybar__nav a'))
        : [];

    if (navLinks.length > 0) {
        var sections = navLinks
            .map(function (a) {
                var href = a.getAttribute('href');
                return href ? document.querySelector(href) : null;
            })
            .filter(Boolean);

        function setActiveLink(id) {
            navLinks.forEach(function (a) {
                var isMatch = a.getAttribute('href') === '#' + id;
                a.classList.toggle('is-active', isMatch);
            });
        }

        var sectionObserver = new IntersectionObserver(function (entries) {
            // Find the most visible entry
            var visible = entries
                .filter(function (e) { return e.isIntersecting; })
                .sort(function (a, b) { return b.intersectionRatio - a.intersectionRatio; });

            if (visible.length > 0 && visible[0].target.id) {
                setActiveLink(visible[0].target.id);
            }
        }, {
            threshold: [0.1, 0.25, 0.5],
            rootMargin: '-80px 0px -40% 0px'
        });

        sections.forEach(function (section) {
            sectionObserver.observe(section);
        });
    }

    // ---------------------------------------------------------------
    // 3. SMOOTH SCROLL: Anchor clicks scroll with offset
    // ---------------------------------------------------------------
    if (stickybar) {
        stickybar.addEventListener('click', function (e) {
            var link = e.target.closest('a[href^="#"]');
            if (!link) return;

            var targetId = link.getAttribute('href');
            var target = document.querySelector(targetId);
            if (!target) return;

            e.preventDefault();

            var stickyHeight = stickybar.offsetHeight || 60;
            var targetTop = target.getBoundingClientRect().top + window.pageYOffset - stickyHeight - 16;

            window.scrollTo({
                top: targetTop,
                behavior: 'smooth'
            });
        });
    }

    // ---------------------------------------------------------------
    // 4. GALLERY IMAGE LAZY-LOAD TRIGGER
    //    Swap data-original-url into src for .mobile-ready-img
    // ---------------------------------------------------------------
    var galleryImages = document.querySelectorAll('.profile-page .mobile-ready-img[data-original-url]');

    if (galleryImages.length > 0) {
        var imgObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    var img = entry.target;
                    var originalUrl = img.getAttribute('data-original-url');
                    if (originalUrl && !img.src) {
                        // Use responsive version on mobile, original on desktop
                        if (window.innerWidth <= 768) {
                            var responsiveUrl = img.getAttribute('data-responsive-img-url');
                            img.src = responsiveUrl || originalUrl;
                        } else {
                            img.src = originalUrl;
                        }
                    }
                    imgObserver.unobserve(img);
                }
            });
        }, {
            rootMargin: '200px 0px'
        });

        galleryImages.forEach(function (img) {
            imgObserver.observe(img);
        });
    }

})();
