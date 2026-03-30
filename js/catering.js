/**
 * ============================================================
 * CHEF THARU — CATERING PAGE
 * js/catering.js
 *
 * Contents:
 * 1.  Custom Cursor
 * 2.  Header Scroll (glass effect)
 * 3.  Mobile Navigation
 * 4.  Scroll Reveal (IntersectionObserver)
 * 5.  Catering Enquiry Form
 * 6.  Package Card Tilt (3D hover effect)
 * 7.  Hero Spotlight (mouse follow)
 * ============================================================
 */

document.addEventListener("DOMContentLoaded", () => {
    initCursor();
    initHeader();
    initMobileNav();
    initReveal();
    initCateringForm();
    initPackageTilt();
    initHeroSpotlight();
});


/* ============================================================
   1. CUSTOM CURSOR
   Dot follows instantly, ring lerps behind
============================================================ */
function initCursor() {
    const dot  = document.getElementById("cursorDot");
    const ring = document.getElementById("cursorRing");
    if (!dot || !ring || window.innerWidth <= 900) {
        if (dot)  dot.style.display  = "none";
        if (ring) ring.style.display = "none";
        return;
    }

    let mX = 0, mY = 0, rX = 0, rY = 0;
    const LERP = 0.1;

    document.addEventListener("mousemove", (e) => {
        mX = e.clientX; mY = e.clientY;
        dot.style.left = mX + "px";
        dot.style.top  = mY + "px";
    });

    (function loop() {
        rX += (mX - rX) * LERP;
        rY += (mY - rY) * LERP;
        ring.style.left = rX + "px";
        ring.style.top  = rY + "px";
        requestAnimationFrame(loop);
    })();

    document.querySelectorAll("a, button, input, select, textarea, label").forEach(el => {
        el.addEventListener("mouseenter", () => { ring.style.width = "52px"; ring.style.height = "52px"; });
        el.addEventListener("mouseleave", () => { ring.style.width = "36px"; ring.style.height = "36px"; });
    });
}


/* ============================================================
   2. HEADER SCROLL — adds .scrolled glass class after 80px
============================================================ */
function initHeader() {
    const header = document.getElementById("siteHeader");
    if (!header) return;
    window.addEventListener("scroll", () => {
        header.classList.toggle("scrolled", window.scrollY > 80);
    }, { passive: true });
}


/* ============================================================
   3. MOBILE NAVIGATION — hamburger slide-in drawer
============================================================ */
function initMobileNav() {
    const hamburger = document.getElementById("hamburger");
    const nav       = document.getElementById("mainNav");
    if (!hamburger || !nav) return;

    hamburger.addEventListener("click", () => {
        hamburger.classList.toggle("active");
        nav.classList.toggle("active");
        document.body.style.overflow = nav.classList.contains("active") ? "hidden" : "";
    });

    nav.querySelectorAll("a").forEach(link => {
        link.addEventListener("click", () => {
            hamburger.classList.remove("active");
            nav.classList.remove("active");
            document.body.style.overflow = "";
        });
    });
}


/* ============================================================
   4. SCROLL REVEAL
   Watches [data-reveal] elements and adds .visible on entry
============================================================ */
function initReveal() {
    const els = document.querySelectorAll("[data-reveal]");
    if (!els.length) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, i) => {
            if (entry.isIntersecting) {
                /* Stagger cards that share the same section */
                const delay = parseInt(entry.target.dataset.delay || 0) + i * 60;
                setTimeout(() => entry.target.classList.add("visible"), delay);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.10, rootMargin: "0px 0px -50px 0px" });

    els.forEach(el => observer.observe(el));
}


/* ============================================================
   5. CATERING ENQUIRY FORM
   Validates, shows success, resets after 4s
============================================================ */
function initCateringForm() {
    const form    = document.getElementById("catering-enquiry-form");
    const message = document.getElementById("catering-message");
    if (!form || !message) return;

    form.addEventListener("submit", (e) => {
        e.preventDefault();

        /* Show confirmation */
        message.textContent = "✦ Thank you! Our catering team will contact you within 24 hours.";
        message.style.opacity = "1";

        form.reset();

        /* Auto-clear after 5s */
        setTimeout(() => {
            message.style.opacity = "0";
            setTimeout(() => { message.textContent = ""; }, 400);
        }, 5000);
    });
}


/* ============================================================
   6. PACKAGE CARD 3D TILT
   Each package card tilts toward the cursor on hover,
   with a moving sheen highlight. Desktop only.
============================================================ */
function initPackageTilt() {
    if (!window.matchMedia("(pointer: fine)").matches) return;

    const cards = document.querySelectorAll(".package-card");
    const MAX   = 10; /* max tilt degrees */

    cards.forEach(card => {
        /* Add sheen overlay */
        const sheen = document.createElement("div");
        sheen.style.cssText = `
            position:absolute; inset:0; border-radius:6px; pointer-events:none; z-index:10;
            opacity:0; transition:opacity .3s;
        `;
        card.appendChild(sheen);

        card.addEventListener("mouseenter", () => {
            card.style.transition = "transform 0.08s linear, box-shadow 0.4s, border-color 0.4s";
            sheen.style.opacity   = "1";
        });

        card.addEventListener("mousemove", (e) => {
            const r    = card.getBoundingClientRect();
            const xF   = (e.clientX - r.left)  / r.width  - 0.5;
            const yF   = (e.clientY - r.top)   / r.height - 0.5;
            const rotY =  xF * MAX * 2;
            const rotX = -yF * MAX * 2;
            card.style.transform = `perspective(900px) rotateX(${rotX}deg) rotateY(${rotY}deg) translateZ(12px)`;

            /* Sheen follows mouse */
            const sx = ((e.clientX - r.left) / r.width  * 100).toFixed(1);
            const sy = ((e.clientY - r.top)  / r.height * 100).toFixed(1);
            sheen.style.background = `radial-gradient(circle 160px at ${sx}% ${sy}%, rgba(255,255,255,.07), transparent 70%)`;
        });

        card.addEventListener("mouseleave", () => {
            card.style.transition = "transform 0.6s cubic-bezier(0.25,0.46,0.45,0.94), box-shadow 0.5s, border-color 0.4s";
            card.style.transform  = "";
            sheen.style.opacity   = "0";
        });
    });
}


/* ============================================================
   7. HERO SPOTLIGHT
   Warm gold glow follows cursor across the hero section
============================================================ */
function initHeroSpotlight() {
    if (!window.matchMedia("(pointer: fine)").matches) return;

    const hero = document.querySelector(".catering-hero");
    if (!hero) return;

    /* Create spotlight element */
    const spot = document.createElement("div");
    spot.style.cssText = `
        position:absolute; inset:0; z-index:3; pointer-events:none;
        opacity:0; transition:opacity .6s;
    `;
    hero.appendChild(spot);

    let mX = 0, mY = 0, sX = 0, sY = 0, active = false;
    const LERP = 0.07;

    hero.addEventListener("mouseenter",  () => { spot.style.opacity = "1"; active = true;  });
    hero.addEventListener("mouseleave",  () => { spot.style.opacity = "0"; active = false; });
    hero.addEventListener("mousemove", (e) => {
        const r = hero.getBoundingClientRect();
        mX = e.clientX - r.left;
        mY = e.clientY - r.top;
    });

    (function loop() {
        if (active) {
            sX += (mX - sX) * LERP;
            sY += (mY - sY) * LERP;
            const r  = hero.getBoundingClientRect();
            const px = (sX / r.width  * 100).toFixed(2);
            const py = (sY / r.height * 100).toFixed(2);
            spot.style.background = `radial-gradient(circle 480px at ${px}% ${py}%, rgba(201,166,84,.11) 0%, rgba(201,166,84,.04) 40%, transparent 70%)`;
        }
        requestAnimationFrame(loop);
    })();
}
