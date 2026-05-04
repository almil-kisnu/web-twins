(function () {
    'use strict';

    if (typeof gsap === 'undefined') {
        console.warn('[TWINS] GSAP tidak ditemukan. Animasi dilewati.');
        return;
    }

    const isMobile = () => window.innerWidth < 768;

    function parseTransform(str) {
        str = str || '';
        const sm = str.match(/scale\(([\d.]+)\)/);
        const rm = str.match(/rotate\(([-\d.]+)deg\)/);
        return {
            scale: sm ? parseFloat(sm[1]) : 1,
            rotation: rm ? parseFloat(rm[1]) : 0,
        };
    }

    function initLenis() {
        return; // Disabled for performance troubleshooting
        if (typeof Lenis === 'undefined' || isMobile()) return;

        const lenis = new Lenis({
            duration: 1.1,
            easing: t => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
            smoothWheel: true,
            wheelMultiplier: 1.1,
            touchMultiplier: 1.5,
        });

        lenis.on('scroll', ScrollTrigger.update);
        gsap.ticker.add(time => lenis.raf(time * 1000));
        gsap.ticker.lagSmoothing(0);
        window._lenis = lenis;
    }

    function initScrollReveal() {

        const grids = document.querySelectorAll('[data-stagger-grid]');
        grids.forEach(grid => {
            const items = grid.querySelectorAll('[data-stagger-item]');
            if (!items.length) return;
            gsap.set(items, { opacity: 0, y: 100, scale: 0.85, rotationX: -15 });
            ScrollTrigger.create({
                trigger: grid, start: 'top 85%',
                onEnter: () => gsap.to(items, {
                    opacity: 1, y: 0, scale: 1, rotationX: 0,
                    duration: 1.2, stagger: 0.12, ease: 'expo.out', clearProps: 'all',
                }),
                once: true,
            });
        });

        const promoCards = document.querySelectorAll('.promo-card');
        if (promoCards.length) {
            gsap.set(promoCards, { opacity: 0, y: 80, rotation: 5, scale: 0.9 });
            ScrollTrigger.create({
                trigger: '.promo-slider-container', start: 'top 85%',
                onEnter: () => gsap.to(promoCards, {
                    opacity: 1, y: 0, rotation: 0, scale: 1, duration: 1.1, stagger: 0.15, ease: 'back.out(1.5)', clearProps: 'all',
                }),
                once: true,
            });
        }

        const boxLeft = document.querySelector('[data-reveal-left]');
        const boxRight = document.querySelector('[data-reveal-right]');
        if (boxLeft && boxRight) {
            gsap.set(boxLeft, { opacity: 0, x: -100, rotationY: 15 });
            gsap.set(boxRight, { opacity: 0, x: 100, rotationY: -15 });
            ScrollTrigger.create({
                trigger: '.highlight-container', start: 'top 80%',
                onEnter: () => {
                    gsap.to(boxLeft, { opacity: 1, x: 0, rotationY: 0, duration: 1.4, ease: 'power4.out', clearProps: 'all' });
                    gsap.to(boxRight, { opacity: 1, x: 0, rotationY: 0, duration: 1.4, delay: 0.15, ease: 'power4.out', clearProps: 'all' });
                },
                once: true,
            });
        }

        const featLeft = document.querySelectorAll('.feature-list.left-side .feature-item');
        const featRight = document.querySelectorAll('.feature-list.right-side .feature-item');
        const featImg = document.querySelector('.product-image-container');

        if (featLeft.length) {
            gsap.set(featLeft, { opacity: 0, x: -80, scale: 0.9 });
            ScrollTrigger.create({
                trigger: '.grid-container', start: 'top 78%',
                onEnter: () => gsap.to(featLeft, {
                    opacity: 1, x: 0, scale: 1, duration: 1.2, stagger: 0.2, ease: 'back.out(1.4)', clearProps: 'all',
                }),
                once: true,
            });
        }
        if (featRight.length) {
            gsap.set(featRight, { opacity: 0, x: 80, scale: 0.9 });
            ScrollTrigger.create({
                trigger: '.grid-container', start: 'top 78%',
                onEnter: () => gsap.to(featRight, {
                    opacity: 1, x: 0, scale: 1, duration: 1.2, stagger: 0.2, ease: 'back.out(1.4)', clearProps: 'all',
                }),
                once: true,
            });
        }
        if (featImg) {
            gsap.set(featImg, { opacity: 0, scale: 0.7, rotation: -8 });
            ScrollTrigger.create({
                trigger: '.grid-container', start: 'top 78%',
                onEnter: () => gsap.to(featImg, {
                    opacity: 1, scale: 1, rotation: 0, duration: 1.5, ease: 'expo.out', clearProps: 'all',
                }),
                once: true,
            });
        }

        const testiHeader = document.querySelectorAll('[data-reveal-up]');
        testiHeader.forEach(el => {
            gsap.set(el, { opacity: 0, y: 50, scale: 0.9 });
            ScrollTrigger.create({
                trigger: el, start: 'top 88%',
                onEnter: () => gsap.to(el, {
                    opacity: 1, y: 0, scale: 1, duration: 1.2, ease: 'back.out(1.5)', clearProps: 'all',
                }),
                once: true,
            });
        });

        // Footer Animations
        const footerCols = document.querySelectorAll('.footer-col');
        if (footerCols.length) {
            gsap.set(footerCols, { opacity: 0, y: 60 });
            ScrollTrigger.create({
                trigger: '.main-footer', start: 'top 90%',
                onEnter: () => gsap.to(footerCols, {
                    opacity: 1, y: 0, duration: 1.2, stagger: 0.2, ease: 'power3.out', clearProps: 'all'
                }),
                once: true
            });
        }
        
        const footerBottom = document.querySelector('.footer-bottom');
        if(footerBottom) {
             gsap.set(footerBottom, { opacity: 0 });
             ScrollTrigger.create({
                trigger: '.main-footer', start: 'top 90%',
                onEnter: () => gsap.to(footerBottom, {
                    opacity: 1, duration: 1.2, delay: 0.6, ease: 'power3.out', clearProps: 'all'
                }),
                once: true
            });
        }

        // Section Fade & Zoom
        const exploreSections = document.querySelectorAll('.explore-section, .highlight-section, .product-features-section, .promo-section, .testimonials-marquee-section');
        exploreSections.forEach(sec => {
            gsap.set(sec, { opacity: 0, y: 40 });
            ScrollTrigger.create({
                trigger: sec,
                start: "top 85%",
                onEnter: () => gsap.to(sec, { opacity: 1, y: 0, duration: 1.2, ease: "power3.out", clearProps: 'all' }),
                once: true
            });
        });
    }

    function initTextSplit() {
        const targets = document.querySelectorAll('[data-split-text]');

        targets.forEach(el => {
            if (el.querySelector('.sw')) return;

            const nodes = Array.from(el.childNodes);
            el.innerHTML = '';

            nodes.forEach(node => {
                if (node.nodeType === Node.TEXT_NODE) {
                    node.textContent.trim().split(/\s+/).forEach((word, wi, arr) => {
                        const clip = document.createElement('span');
                        const inner = document.createElement('span');
                        clip.className = 'sw';
                        inner.className = 'swi';
                        inner.textContent = word;
                        clip.appendChild(inner);
                        el.appendChild(clip);
                        if (wi < arr.length - 1) el.appendChild(document.createTextNode(' '));
                    });
                } else {
                    const spanEl = node.cloneNode ? node.cloneNode(true) : node;
                    el.appendChild(spanEl);
                }
            });

            const inners = el.querySelectorAll('.swi');
            gsap.set(inners, { y: '110%', opacity: 0 });

            ScrollTrigger.create({
                trigger: el, start: 'top 88%',
                onEnter: () => gsap.to(inners, {
                    y: '0%', opacity: 1,
                    duration: 0.8, stagger: 0.07, ease: 'power3.out', clearProps: 'all',
                }),
                once: true,
            });
        });
    }

    function initMagnetic() {
        if (isMobile()) return;

        const btns = document.querySelectorAll('.btn-fill, .btn-action, .btn-outline, .btn-highlights-sm, .btn-fill.main-cta');

        btns.forEach(btn => {
            const strength = parseFloat(btn.dataset.magnet) || 0.32;

            btn.addEventListener('mousemove', e => {
                const r = btn.getBoundingClientRect();
                const dx = e.clientX - (r.left + r.width / 2);
                const dy = e.clientY - (r.top + r.height / 2);
                gsap.to(btn, { x: dx * strength, y: dy * strength, duration: 0.35, ease: 'power2.out' });
            });

            btn.addEventListener('mouseleave', () => {
                gsap.to(btn, { x: 0, y: 0, duration: 0.7, ease: 'elastic.out(1, 0.45)' });
            });
        });
    }

    function initParallax() {
        if (isMobile()) return;

        const imgs = document.querySelectorAll('[data-parallax]');
        imgs.forEach(img => {
            const wrap = img.closest('[data-parallax-wrap]');
            const speed = parseFloat(img.dataset.parallaxSpeed) || 0.22;

            if (wrap) wrap.style.overflow = 'hidden';
            gsap.set(img, { scale: 1.15 });

            gsap.to(img, {
                yPercent: -15 * speed * 5,
                ease: 'none',
                scrollTrigger: {
                    trigger: wrap || img,
                    start: 'top bottom',
                    end: 'bottom top',
                    scrub: 0.8,
                },
            });
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (typeof ScrollTrigger !== 'undefined') {
            gsap.registerPlugin(ScrollTrigger);

            const style = document.createElement('style');
            style.textContent = `
                .sw  { display:inline-block; overflow:hidden; vertical-align:bottom; }
                .swi { display:inline-block; will-change:transform; }
                [data-parallax-wrap] { overflow:hidden; }
                [data-parallax] { will-change: transform; }
            `;
            document.head.appendChild(style);

            initLenis();
            initMagnetic();

            // Tunggu animasi layar pembuka selesai dan layout full terekspansi 
            // agar perhitungan start point ScrollTrigger akurat dan tidak meleset
            const checkSplash = setInterval(() => {
                if (!document.body.classList.contains('hide-overflow')) {
                    clearInterval(checkSplash);
                    
                    // Jalankan semua instance Trigger setelah layout siap
                    initScrollReveal();
                    initTextSplit();
                    initParallax();
                    
                    // Force refresh agar Trigger sinkron
                    ScrollTrigger.refresh();
                }
            }, 100);
        }
    });

})();