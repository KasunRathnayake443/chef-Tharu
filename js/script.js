document.addEventListener("DOMContentLoaded", function () {

    /* ===============================
       RESERVATION FORM
    ===============================*/
    const form = document.getElementById("reservation-form");
    const message = document.getElementById("reservation-message");

    if (form && message) {
        form.addEventListener("submit", function(event) {
            event.preventDefault();

            message.textContent = "Thank you! We will confirm your reservation shortly.";
            message.style.color = "#c9a654";

            setTimeout(() => {
                message.textContent = "";
            }, 4000);
        });
    }

    /* ===============================
       OPTIONAL: PAUSE MENU ON HOVER (Extra Safety)
    ===============================*/
    const slider = document.getElementById("menuSlider");

    if (slider) {
        slider.addEventListener("mouseenter", () => {
            slider.style.animationPlayState = "paused";
        });

        slider.addEventListener("mouseleave", () => {
            slider.style.animationPlayState = "running";
        });
    }

});

/* =====================================
   SMART NAVBAR - Hide on scroll down, Show on scroll up
===================================== */

document.addEventListener("DOMContentLoaded", function () {
    const navbar = document.getElementById("navbar");
    let lastScroll = 0;

    window.addEventListener("scroll", () => {
        const currentScroll = window.scrollY;

        if (currentScroll > lastScroll && currentScroll > 80) {
            navbar.classList.add("hidden");
        } else {
            navbar.classList.remove("hidden");
        }

        lastScroll = currentScroll;
    });

    // Show navbar when mouse moves to top
    document.addEventListener("mousemove", (e) => {
        if (e.clientY < 80) {
            navbar.classList.remove("hidden");
        }
    });
});