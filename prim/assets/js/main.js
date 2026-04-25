/**
* Template Name: Delicious - v4.7.1
* Template URL: https://bootstrapmade.com/delicious-free-restaurant-bootstrap-theme/
* Author: BootstrapMade.com
* License: https://bootstrapmade.com/license/
*/




(function () {
  "use strict";

  /**
   * Easy selector helper function
   */
  const select = (el, all = false) => {
    el = el.trim()
    if (all) {
      return [...document.querySelectorAll(el)]
    } else {
      return document.querySelector(el)
    }
  }

  /**
   * Easy event listener function
   */
  const on = (type, el, listener, all = false) => {
    let selectEl = select(el, all)
    if (selectEl) {
      if (all) {
        selectEl.forEach(e => e.addEventListener(type, listener))
      } else {
        selectEl.addEventListener(type, listener)
      }
    }
  }

  /**
   * Easy on scroll event listener 
   */
  const onscroll = (el, listener) => {
    el.addEventListener('scroll', listener)
  }


  /**
   * Scrolls to an element with header offset
   */
  const scrollto = (el) => {
    let header = select('#header')
    let offset = header.offsetHeight
    let elementPos = select(el).offsetTop
    window.scrollTo({
      top: elementPos - offset,
      behavior: 'smooth'
    })
  }

  /**
   * Toggle .header-scrolled class
   */
  let selectHeader = select('#header')
  let selectTopbar = select('#topbar')
  if (selectHeader) {
    const headerScrolled = () => {
      if (window.scrollY > 100) {
        selectHeader.classList.add('header-scrolled')
        if (selectTopbar) selectTopbar.classList.add('topbar-scrolled')
      } else {
        selectHeader.classList.remove('header-scrolled')
        if (selectTopbar) selectTopbar.classList.remove('topbar-scrolled')
      }
    }
    window.addEventListener('load', headerScrolled)
    onscroll(document, headerScrolled)
  }

  /**
   * Back to top button
   */
  let backtotop = select('.back-to-top')
  if (backtotop) {
    const toggleBacktotop = () => {
      if (window.scrollY > 100) backtotop.classList.add('active')
      else backtotop.classList.remove('active')
    }
    window.addEventListener('load', toggleBacktotop)
    onscroll(document, toggleBacktotop)
  }


  /**
   * Scroll with offset on links with .scrollto
   */
  on('click', '.scrollto', function (e) {
    if (select(this.hash)) {
      e.preventDefault()

      let navbar = select('#navbar')
      if (navbar.classList.contains('navbar-mobile')) {
        navbar.classList.remove('navbar-mobile')
        let navbarToggle = select('.mobile-nav-toggle')
        navbarToggle.classList.toggle('bi-list')
        navbarToggle.classList.toggle('bi-x')
      }
      scrollto(this.hash)
    }
  }, true)

  /**
   * Scroll to hash on page load
   */
  window.addEventListener('load', () => {
    if (window.location.hash) {
      if (select(window.location.hash)) scrollto(window.location.hash)
    }
  });

  /**
   * Hero carousel indicators
   */
  let heroCarouselIndicators = select("#hero-carousel-indicators")
  let heroCarouselItems = select('#heroCarousel .carousel-item', true)

  heroCarouselItems.forEach((item, index) => {
    (index === 0) ?
      heroCarouselIndicators.innerHTML += "<li data-bs-target='#heroCarousel' data-bs-slide-to='" + index + "' class='active'></li>" :
      heroCarouselIndicators.innerHTML += "<li data-bs-target='#heroCarousel' data-bs-slide-to='" + index + "'></li>"
  });

  /**
   * Menu isotope and filter
   */
  window.addEventListener('load', () => {
    let menuContainer = select('.menu-container');
    if (menuContainer) {
      let menuIsotope = new Isotope(menuContainer, {
        itemSelector: '.menu-item',
        layoutMode: 'fitRows'
      });

      let menuFilters = select('#menu-flters li', true);

      on('click', '#menu-flters li', function (e) {
        e.preventDefault();
        menuFilters.forEach(el => el.classList.remove('filter-active'));
        this.classList.add('filter-active');
        menuIsotope.arrange({
          filter: this.getAttribute('data-filter')
        });
      }, true);
    }
  });

  /**
   * Testimonials sliders
   */
  /* ---------- Testimonials Swiper ---------- */
  new Swiper(".testimonials-slider", {
    loop: true,
    speed: 1000,
    autoHeight: true, // ✅ IMPORTANT
    autoplay: {
      delay: 12000,
      disableOnInteraction: false
    },
    slidesPerView: 1,
    spaceBetween: 20,
    pagination: {
      el: ".testimonials-pagination",
      clickable: true
    }
  });



})();

// back top---------------

/* ===============================
   DOM READY
================================ */
document.addEventListener("DOMContentLoaded", function () {

  /* ---------- GLightbox ---------- */
  if (typeof GLightbox !== "undefined") {
    GLightbox({
      selector: ".glightbox"
    });
  }

  /* ---------- Generic Swiper Init (JSON config) ---------- */
  // document.querySelectorAll(".init-swiper").forEach(swiperEl => {
  //   const configEl = swiperEl.querySelector(".swiper-config");
  //   let config = {};

  //   if (configEl) {
  //     try {
  //       config = JSON.parse(configEl.textContent);
  //     } catch (e) {
  //       console.error("Invalid swiper config JSON", e);
  //     }
  //   }

  //   new Swiper(swiperEl, {
  //     ...config,
  //     autoplay: config.autoplay
  //       ? { delay: config.autoplay.delay, disableOnInteraction: false }
  //       : false,
  //     pagination: {
  //       el: swiperEl.querySelector(".swiper-pagination"),
  //       clickable: true
  //     }
  //   });
  // });

  /* ---------- Back To Top ---------- */
  const backToTop = document.getElementById("backToTop");
  if (backToTop) {
    window.addEventListener("scroll", () => {
      backToTop.style.display = window.scrollY > 300 ? "flex" : "none";
    });

    backToTop.addEventListener("click", () => {
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
  }

  /* ---------- Courses Swiper ---------- */
  if (document.querySelector(".courses-swiper")) {
    new Swiper(".courses-swiper", {
      loop: true,
      slidesPerView: 4,
      spaceBetween: 20,
      autoplay: { delay: 3000, disableOnInteraction: false },
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev"
      },
      pagination: {
        el: ".swiper-pagination",
        clickable: true
      },
      breakpoints: {
        0: { slidesPerView: 1 },
        576: { slidesPerView: 2 },
        768: { slidesPerView: 3 },
        1024: { slidesPerView: 4 }
      }
    });
  }

  /* ---------- Navbar Hide on Scroll ---------- */
  const navbar = document.getElementById("navbar");
  let lastScrollY = window.scrollY;

  if (navbar) {
    window.addEventListener("scroll", () => {
      if (navbar.classList.contains("show")) return;

      navbar.style.transition = "transform 0.3s ease";
      navbar.style.transform =
        window.scrollY > lastScrollY ? "translateY(-110%)" : "translateY(0)";

      lastScrollY = window.scrollY;
    });
  }

  /* ---------- WhatsApp Inquiry ---------- */
  const submitBtn = document.getElementById("submitBtn");
  if (submitBtn) {
    submitBtn.addEventListener("click", () => {
      const inquiry = document.getElementById("inquirySelect")?.value;
      if (!inquiry) {
        alert("Please select your inquiry type!");
        return;
      }

      const number = "918144408771";
      const message = `Hello, I would like to enquire about: ${inquiry}`;
      window.open(
        `https://wa.me/${number}?text=${encodeURIComponent(message)}`,
        "_blank"
      );
    });
  }

  /* ---------- Email Form ---------- */
  const emailForm = document.getElementById("emailForm");
  if (emailForm) {
    emailForm.addEventListener("submit", e => {
      e.preventDefault();

      const name = document.getElementById("name").value;
      const email = document.getElementById("email").value;
      const phone = document.getElementById("phone").value;
      const subject = document.getElementById("subject").value;
      const message = document.getElementById("message").value;

      const mailto = `mailto:info@example.com?subject=${encodeURIComponent(
        subject
      )}&body=${encodeURIComponent(
        `Name: ${name}\nEmail: ${email}\nPhone: ${phone}\n\nMessage:\n${message}`
      )}`;

      window.location.href = mailto;
      emailForm.reset();

      setTimeout(() => alert("✅ Message sent successfully"), 500);
    });
  }

  /* ---------- Mobile Navbar Toggle ---------- */
  const toggler = document.querySelector(".custom-toggler");
  const navCollapse = document.getElementById("navbarSupportedContent");

  if (toggler && navCollapse) {
    toggler.addEventListener("click", () => {
      toggler.classList.toggle("active");
      navCollapse.classList.toggle("show");
    });

    /* ----- DELETE THIS PART ----- */
/* ---------- Close Mobile Menu on Nav Link Click (FIXED) ---------- */
document.querySelectorAll('#navbarSupportedContent .nav-link').forEach(link => {
  link.addEventListener('click', (e) => {
    
    // MUKKIYAMANA LINE: 
    // Click pannadhu "Dropdown Toggle" ah irundha, menu-va close pannadhe!
    if (link.classList.contains('dropdown-toggle')) {
        return; 
    }

    // Matha links (Home, About, Contact) click panna mattum close aagum
    const navCollapse = document.getElementById('navbarSupportedContent');
    const toggler = document.querySelector('.custom-toggler');

    if (navCollapse.classList.contains('show')) {
      navCollapse.classList.remove('show');
      if(toggler) {
        toggler.setAttribute('aria-expanded', 'false');
        toggler.classList.remove('active');
      }
    }
  });
});
/* ---------------------------- */
  }

  /* ---------- Gallery Swiper ---------- */
  if (document.querySelector(".gallery-swiper")) {
    new Swiper(".gallery-swiper", {
      loop: true,
      slidesPerView: 4,
      spaceBetween: 20,
      autoplay: { delay: 2500, disableOnInteraction: false },
      pagination: { el: ".swiper-pagination", clickable: true },
      breakpoints: {
        0: { slidesPerView: 1 },
        768: { slidesPerView: 2 },
        992: { slidesPerView: 4 }
      }
    });
  }

  /* ---------- Team Swiper ---------- */
  if (document.querySelector(".team-swiper")) {
    new Swiper(".team-swiper", {
      loop: true,
      spaceBetween: 25,
      pagination: { el: ".swiper-pagination", clickable: true },
      breakpoints: {
        0: { slidesPerView: 1 },
        768: { slidesPerView: 2 },
        992: { slidesPerView: 3 }
      }
    });
  }

  /* ---------- Video Swiper ---------- */
  if (document.querySelector(".video-swiper")) {
    new Swiper(".video-swiper", {
      loop: false,
      spaceBetween: 20,
      slidesPerView: 1,
      grabCursor: true,
      pagination: {
        el: ".video-swiper .swiper-pagination",
        clickable: true
      },
      breakpoints: {
        768: { slidesPerView: 2 },
        1024: { slidesPerView: 3 }
      }
    });
  }

  document.querySelectorAll(".video-card").forEach(card => {
    const overlay = card.querySelector(".video-overlay");
    const iframe = card.querySelector("iframe");

    overlay.addEventListener("click", () => {
      card.classList.add("playing");

      // enable autoplay on click
      if (!iframe.src.includes("autoplay=1")) {
        iframe.src += (iframe.src.includes("?") ? "&" : "?") + "autoplay=1";
      }
    });
  });


});



// courses page=======
/* ===============================
   DOM READY
================================ */
document.addEventListener("DOMContentLoaded", function () {

  /* ---------- Back To Top ---------- */
  const btn = document.getElementById("backToTop");
  if (btn) {
    window.addEventListener("scroll", () => {
      btn.style.display = window.scrollY > 300 ? "flex" : "none";
    });

    btn.addEventListener("click", () => {
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
  }

  /* ---------- Courses Swiper ---------- */
  if (typeof Swiper !== "undefined" && document.querySelector(".courses-swiper")) {
    new Swiper(".courses-swiper", {
      slidesPerView: 4,
      spaceBetween: 20,
      loop: true,
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev"
      },
      pagination: {
        el: ".swiper-pagination",
        clickable: true,
        type: "bullets"
      },
      breakpoints: {
        0: { slidesPerView: 1 },
        768: { slidesPerView: 2 },
        1024: { slidesPerView: 4 }
      }
    });
  }

  /* ---------- Navbar Hide on Scroll ---------- */
  const navbar = document.getElementById("navbar");
  let lastScrollY = window.scrollY;

  if (navbar) {
    window.addEventListener("scroll", () => {
      if (navbar.classList.contains("show")) return;

      navbar.style.transition = "transform 0.3s ease";
      navbar.style.transform =
        window.scrollY > lastScrollY
          ? "translateY(-110%)"
          : "translateY(0)";

      lastScrollY = window.scrollY;
    });
  }

  /* ---------- WhatsApp Inquiry Button ---------- */
  const submitBtn = document.getElementById("submitBtn");
  if (submitBtn) {
    submitBtn.addEventListener("click", () => {
      const inquiry = document.getElementById("inquirySelect")?.value;
      if (!inquiry) {
        alert("Please select your inquiry type!");
        return;
      }

      const number = "918144408771";
      const message = `Hello, I would like to enquire about: ${inquiry}`;
      window.open(
        `https://wa.me/${number}?text=${encodeURIComponent(message)}`,
        "_blank"
      );
    });
  }

  /* ---------- WhatsApp Contact Form ---------- */
  const whatsappForm = document.getElementById("whatsappForm");
  if (whatsappForm) {
    whatsappForm.addEventListener("submit", e => {
      e.preventDefault();

      const name = document.getElementById("name").value;
      const email = document.getElementById("email").value;
      const phone = document.getElementById("phone").value;
      const subject = document.getElementById("subject").value;
      const message = document.getElementById("message").value;

      const whatsappMessage = `*New Inquiry*\n\nName: ${name}\nEmail: ${email}\nPhone: ${phone}\nSubject: ${subject}\nMessage: ${message}`;
      const encodedMessage = encodeURIComponent(whatsappMessage);

      window.open(
        `https://wa.me/918144408771?text=${encodedMessage}`,
        "_blank"
      );
    });
  }

  /* ---------- Mobile Navbar Toggle ---------- */
  const toggler = document.querySelector(".custom-toggler");
  const navCollapse = document.getElementById("navbarSupportedContent");

  if (toggler && navCollapse) {
    toggler.addEventListener("click", () => {
      toggler.classList.toggle("active");
      navCollapse.classList.toggle("show");
    });

    document
      .querySelectorAll("#navbarSupportedContent .nav-link")
      .forEach(link => {
        link.addEventListener("click", () => {
          navCollapse.classList.remove("show");
          toggler.classList.remove("active");
        });
      });
  }

});
document.addEventListener("DOMContentLoaded", () => {

  const animatedItems = document.querySelectorAll("[data-animate]");

  const observer = new IntersectionObserver(
    (entries, obs) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add("show");
          obs.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.25 }
  );

  animatedItems.forEach(el => {
    el.classList.add("animate");
    observer.observe(el);
  });

});


/* ---------- Close Mobile Menu on Nav Link Click (FIXED) ---------- */
document.querySelectorAll('#navbarSupportedContent .nav-link').forEach(link => {
  link.addEventListener('click', (e) => {
    
    // MUKKIYAMANA LINE: 
    // Click pannadhu "Dropdown Toggle" ah irundha, menu-va close pannadhe!
    if (link.classList.contains('dropdown-toggle')) {
        return; 
    }

    // Matha links (Home, About, Contact) click panna mattum close aagum
    const navCollapse = document.getElementById('navbarSupportedContent');
    const toggler = document.querySelector('.custom-toggler');

    if (navCollapse.classList.contains('show')) {
      navCollapse.classList.remove('show');
      if(toggler) {
        toggler.setAttribute('aria-expanded', 'false');
        toggler.classList.remove('active');
      }
    }
  });
});
