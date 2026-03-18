/**
 * ============================================================
 * CHEF THARU — LUXURY PARALLAX RESTAURANT WEBSITE
 * js/script.js
 *
 * Contents:
 * 1.  DOM Ready
 * 2.  Page Loader
 * 3.  Custom Cursor
 * 4.  Header Scroll Behaviour (glass + shrink)
 * 5.  Mobile Navigation (hamburger drawer)
 * 6.  Hero Parallax (content floats up on scroll)
 * 7.  Section Parallax Backgrounds (about + reservation)
 * 8.  Reveal Animations (IntersectionObserver)
 * 9.  Stat Counter Animation
 * 10. Reservation Form Submission
 * 11. Floating Spice Particles
 * 12. Active Nav Link Highlight
 * ============================================================
 */

/* ============================================================
   1. DOM READY
   Wait for DOM to be fully parsed before running any JS
============================================================ */
document.addEventListener("DOMContentLoaded", () => {

    // Run all initialisers
    initLoader();
    initCursor();
    initHeader();
    initMobileNav();
    initParallax();
    initReveal();
    initReservationForm();
    initSpices();
    initActiveNav();

});


/* ============================================================
   2. PAGE LOADER
   Fades out the fullscreen loader overlay after assets load,
   then triggers the hero entrance animations.
============================================================ */
function initLoader() {
    const loader    = document.getElementById("loader");
    const loaderFill = document.getElementById("loaderFill");
    if (!loader) return;

    // When ALL assets (images, video) are ready → hide loader
    window.addEventListener("load", () => {
        setTimeout(() => {
            loader.classList.add("hidden");
            // Kick off stat counters after hero has appeared
            setTimeout(startStatCounters, 2800);
        }, 400); // slight extra delay for polish
    });

    // Fallback: hide loader after 3.5s even if load event fires late
    setTimeout(() => loader.classList.add("hidden"), 3500);
}


/* ============================================================
   3. CUSTOM CURSOR
   Two-part cursor: instant small dot + lagging ring.
   Ring smoothly follows with requestAnimationFrame.
============================================================ */
function initCursor() {
    const dot  = document.getElementById("cursorDot");
    const ring = document.getElementById("cursorRing");
    if (!dot || !ring) return;

    // Hide default on desktop only (CSS also does cursor:none on body)
    if (window.innerWidth <= 900) {
        dot.style.display  = "none";
        ring.style.display = "none";
        return;
    }

    let mouseX = 0, mouseY = 0;     // actual mouse position
    let ringX  = 0, ringY  = 0;     // lagged ring position

    // Track real mouse position
    document.addEventListener("mousemove", (e) => {
        mouseX = e.clientX;
        mouseY = e.clientY;

        // Dot follows instantly
        dot.style.left = mouseX + "px";
        dot.style.top  = mouseY + "px";
    });

    // Ring lags behind with lerp (linear interpolation)
    const LERP = 0.1; // 0 = no movement, 1 = instant

    (function animateRing() {
        ringX += (mouseX - ringX) * LERP;
        ringY += (mouseY - ringY) * LERP;
        ring.style.left = ringX + "px";
        ring.style.top  = ringY + "px";
        requestAnimationFrame(animateRing);
    })();

    // Enlarge ring when hovering interactive elements
    document.querySelectorAll("a, button, input, select, textarea").forEach(el => {
        el.addEventListener("mouseenter", () => {
            ring.style.width        = "52px";
            ring.style.height       = "52px";
            ring.style.borderColor  = "var(--gold)";
        });
        el.addEventListener("mouseleave", () => {
            ring.style.width        = "36px";
            ring.style.height       = "36px";
            ring.style.borderColor  = "rgba(201,166,84,.55)";
        });
    });
}


/* ============================================================
   4. HEADER SCROLL BEHAVIOUR
   Adds .scrolled class once user scrolls past the hero fold.
   CSS applies glass morphism background on that class.
============================================================ */
function initHeader() {
    const header = document.getElementById("siteHeader");
    if (!header) return;

    let lastScrollY = 0;

    window.addEventListener("scroll", () => {
        const scrollY = window.scrollY;

        // Apply glass background after scrolling 80px
        if (scrollY > 80) {
            header.classList.add("scrolled");
        } else {
            header.classList.remove("scrolled");
        }

        lastScrollY = scrollY;
    }, { passive: true });
}


/* ============================================================
   5. MOBILE NAVIGATION
   Toggles slide-in drawer and animated hamburger → X icon.
   Closes drawer when a nav link is clicked.
============================================================ */
function initMobileNav() {
    const hamburger = document.getElementById("hamburger");
    const nav       = document.getElementById("mainNav");
    if (!hamburger || !nav) return;

    // Toggle drawer
    hamburger.addEventListener("click", () => {
        hamburger.classList.toggle("active");
        nav.classList.toggle("active");

        // Prevent body scroll while drawer is open
        document.body.style.overflow = nav.classList.contains("active") ? "hidden" : "";
    });

    // Close on link click
    nav.querySelectorAll("a").forEach(link => {
        link.addEventListener("click", () => {
            hamburger.classList.remove("active");
            nav.classList.remove("active");
            document.body.style.overflow = "";
        });
    });

    // Close when clicking outside
    document.addEventListener("click", (e) => {
        if (nav.classList.contains("active") && !nav.contains(e.target) && e.target !== hamburger) {
            hamburger.classList.remove("active");
            nav.classList.remove("active");
            document.body.style.overflow = "";
        }
    });
}


/* ============================================================
   6. HERO PARALLAX + SECTION PARALLAX BACKGROUNDS
   Creates depth effect by moving elements at different speeds
   relative to the scroll position.

   - heroContent: drifts upward as page scrolls down (mild)
   - aboutBgLayer: moves slower than scroll (background effect)
   - resBgLayer:   same, for reservation section
============================================================ */
function initParallax() {
    const heroContent = document.getElementById("heroContent");
    const aboutBg     = document.getElementById("aboutBgLayer");
    const resBg       = document.getElementById("resBgLayer");

    // Throttle with requestAnimationFrame for smooth 60fps
    let ticking = false;

    function onScroll() {
        if (!ticking) {
            requestAnimationFrame(updateParallax);
            ticking = true;
        }
    }

    function updateParallax() {
        const scrollY = window.scrollY;
        const vh      = window.innerHeight;

        /* --- Hero content: subtle upward drift --- */
        if (heroContent) {
            // Only apply within the first viewport (performance)
            if (scrollY < vh * 1.5) {
                heroContent.style.transform = `translateY(${scrollY * 0.28}px)`;
            }
        }

        /* --- About section background: slower-than-scroll --- */
        if (aboutBg) {
            const aboutSection = document.querySelector(".about-section");
            if (aboutSection) {
                const rect   = aboutSection.getBoundingClientRect();
                const relY   = scrollY - (scrollY + rect.top - vh); // distance scrolled within section
                aboutBg.style.transform = `translateY(${relY * 0.25}px)`;
            }
        }

        /* --- Reservation background: same technique --- */
        if (resBg) {
            const resSection = document.querySelector(".reservation-section");
            if (resSection) {
                const rect   = resSection.getBoundingClientRect();
                const relY   = scrollY - (scrollY + rect.top - vh);
                resBg.style.transform = `translateY(${relY * 0.22}px)`;
            }
        }

        ticking = false;
    }

    window.addEventListener("scroll", onScroll, { passive: true });
}


/* ============================================================
   7. REVEAL ANIMATIONS
   IntersectionObserver watches all [data-reveal] elements.
   When they enter the viewport, .visible class is applied
   which triggers the CSS transition (fade + slide in).
============================================================ */
function initReveal() {
    const elements = document.querySelectorAll("[data-reveal]");
    if (!elements.length) return;

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry, i) => {
                if (entry.isIntersecting) {
                    // Stagger delay for groups of items
                    const delay = entry.target.dataset.delay || i * 80;
                    setTimeout(() => {
                        entry.target.classList.add("visible");
                    }, delay);
                    observer.unobserve(entry.target); // animate once
                }
            });
        },
        {
            threshold: 0.12,   // trigger when 12% of element is visible
            rootMargin: "0px 0px -60px 0px" // start slightly before bottom edge
        }
    );

    elements.forEach(el => observer.observe(el));
}


/* ============================================================
   8. STAT COUNTER ANIMATION
   Counts up numbers in .stat-number elements smoothly
   from 0 to their data-target value.
   Called after the loader hides (so numbers are visible).
============================================================ */
function startStatCounters() {
    const counters = document.querySelectorAll(".stat-number[data-target]");
    if (!counters.length) return;

    counters.forEach(counter => {
        const target   = parseInt(counter.dataset.target, 10);
        const duration = 1800; // ms
        const stepTime = 16;   // ~60fps
        const steps    = duration / stepTime;
        const increment = target / steps;
        let current  = 0;

        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                counter.textContent = target;
                clearInterval(timer);
            } else {
                counter.textContent = Math.floor(current);
            }
        }, stepTime);
    });
}


/* ============================================================
   9. RESERVATION FORM
   Prevents default submit, shows confirmation message,
   auto-clears after 4 seconds, resets the form.
============================================================ */
function initReservationForm() {
    const form    = document.getElementById("reservation-form");
    const message = document.getElementById("reservation-message");
    if (!form || !message) return;

    form.addEventListener("submit", (e) => {
        e.preventDefault();

        // Show success message
        message.textContent = "✦ Thank you! We'll confirm your reservation within 24 hours.";
        message.style.opacity = "1";

        // Reset form fields
        form.reset();

        // Auto-clear message
        setTimeout(() => {
            message.style.opacity = "0";
            setTimeout(() => { message.textContent = ""; }, 400);
        }, 4000);
    });
}


/* ============================================================
   10. FLOATING SPICE PARTICLES
   Dynamically creates Font Awesome spice/herb icons that
   float upward inside every .spice-animation container.
   Random positions, sizes, durations = organic feel.
============================================================ */
function initSpices() {
    const containers = document.querySelectorAll(".spice-animation");
    if (!containers.length) return;

    // Icon classes from Font Awesome (food/nature themed)
    const icons = [
        "fa-solid fa-pepper-hot",
        "fa-solid fa-seedling",
        "fa-solid fa-leaf",
        "fa-solid fa-mortar-pestle",
        "fa-solid fa-star",          // extra decorative sparkle
        "fa-solid fa-circle-dot"     // subtle dot for variety
    ];

    containers.forEach(container => {
        const COUNT = 18; // particles per container

        for (let i = 0; i < COUNT; i++) {
            const icon = document.createElement("i");

            // Random icon
            icon.className = icons[Math.floor(Math.random() * icons.length)];

            // Random horizontal start position
            icon.style.left = Math.random() * 100 + "%";

            // Random staggered start time (so they don't all appear at once)
            icon.style.animationDelay = Math.random() * 12 + "s";

            // Random duration for natural variation
            icon.style.animationDuration = (10 + Math.random() * 12) + "s";

            // Random size (smaller = more subtle)
            icon.style.fontSize = (12 + Math.random() * 16) + "px";

            // Slight opacity variation
            icon.style.opacity = (0.15 + Math.random() * 0.3).toFixed(2);

            container.appendChild(icon);
        }
    });
}


/* ============================================================
   11. ACTIVE NAV LINK HIGHLIGHT
   Uses IntersectionObserver to detect which section is
   currently in view and highlights the matching nav link.
============================================================ */
function initActiveNav() {
    const sections  = document.querySelectorAll("section[id]");
    const navLinks  = document.querySelectorAll(".nav-link");
    if (!sections.length || !navLinks.length) return;

    const sectionObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const id = entry.target.getAttribute("id");

                    // Remove active from all links
                    navLinks.forEach(link => link.removeAttribute("data-active"));

                    // Apply to matching link
                    const activeLink = document.querySelector(`.nav-link[href="#${id}"]`);
                    if (activeLink) activeLink.setAttribute("data-active", "true");
                }
            });
        },
        {
            threshold: 0.35, // section must be 35% visible
        }
    );

    sections.forEach(s => sectionObserver.observe(s));
}
