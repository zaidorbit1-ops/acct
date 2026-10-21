const menuToggle = document.querySelector("#menu-toggle");
const primaryNavigation = document.querySelector("#primary-navigation");
const servicesMenu = document.querySelector(".services-menu");
const megaMenu = servicesMenu?.querySelector(".mega-menu");
const desktopNavigation = window.matchMedia("(min-width: 1101px)");
let servicesMenuCloseTimer;

if (menuToggle && primaryNavigation) {
  menuToggle.addEventListener("click", () => {
    const isOpen = menuToggle.getAttribute("aria-expanded") === "true";
    menuToggle.setAttribute("aria-expanded", String(!isOpen));
    primaryNavigation.classList.toggle("is-open", !isOpen);
  });

  desktopNavigation.addEventListener("change", () => {
    menuToggle.setAttribute("aria-expanded", "false");
    primaryNavigation.classList.remove("is-open");
    if (servicesMenu) servicesMenu.open = false;
  });
}

if (servicesMenu) {
  servicesMenu.addEventListener("pointerenter", (event) => {
    window.clearTimeout(servicesMenuCloseTimer);
    if (desktopNavigation.matches && event.pointerType !== "touch") {
      servicesMenu.open = true;
    }
  });

  servicesMenu.addEventListener("pointerleave", () => {
    window.clearTimeout(servicesMenuCloseTimer);
    if (!desktopNavigation.matches || servicesMenu.contains(document.activeElement)) return;

    servicesMenuCloseTimer = window.setTimeout(() => {
      if (!servicesMenu.matches(":hover") && !servicesMenu.contains(document.activeElement)) {
        servicesMenu.open = false;
      }
    }, 180);
  });

  megaMenu?.addEventListener("pointerenter", () => {
    window.clearTimeout(servicesMenuCloseTimer);
    if (desktopNavigation.matches) servicesMenu.open = true;
  });

  document.addEventListener("click", (event) => {
    if (event.target instanceof Node && !servicesMenu.contains(event.target)) {
      servicesMenu.open = false;
    }
  });

  servicesMenu.addEventListener("focusout", (event) => {
    if (
      event.relatedTarget instanceof Node
      && !servicesMenu.contains(event.relatedTarget)
      && !servicesMenu.matches(":hover")
    ) {
      servicesMenu.open = false;
    }
  });
}

document.querySelectorAll("[data-finder-trigger]").forEach((trigger) => {
  const dialogId = trigger.getAttribute("aria-controls");
  const dialog = dialogId ? document.getElementById(dialogId) : null;
  if (!(dialog instanceof HTMLDialogElement)) return;

  trigger.addEventListener("click", () => {
    if (!dialog.open) dialog.showModal();
  });

  dialog.querySelector(".finder-dialog-close")?.addEventListener("click", () => {
    dialog.close();
  });

  dialog.addEventListener("click", (event) => {
    if (event.target === dialog) dialog.close();
  });
});

const isHomePage = document.body.classList.contains("page-home");
const isAboutPage = document.body.classList.contains("page-about");
const isServicePage = document.body.classList.contains("page-service");
const isIndustryPage = document.body.classList.contains("page-industries");
const isContactPage = document.body.classList.contains("page-contact");
const isCareersPage = document.body.classList.contains("page-careers");
const isRedesignPage = isIndustryPage || isContactPage || isCareersPage;
const motionPreference = window.matchMedia("(prefers-reduced-motion: reduce)");

if ((isHomePage || isAboutPage || isServicePage || isRedesignPage) && !motionPreference.matches) {
  const scrollClass = isHomePage ? "home-smooth-scroll" : isAboutPage ? "about-smooth-scroll" : isServicePage ? "service-smooth-scroll" : "redesign-smooth-scroll";
  document.documentElement.classList.add(scrollClass);

  if ("IntersectionObserver" in window) {
    const revealSelector = isHomePage
      ? "body.page-home .reference-home > section, body.page-home .reference-footer .footer-consultation"
      : isAboutPage
        ? "body.page-about .reference-about > section, body.page-about .reference-footer .footer-consultation"
        : isServicePage
          ? "body.page-service main section, body.page-service .reference-footer .footer-consultation"
          : "body.page-industries main .editorial-hero, body.page-industries main > section, body.page-contact main .editorial-hero, body.page-contact main .office-map, body.page-contact .enquiry-next-steps, body.page-careers main .editorial-hero, body.page-careers main > section, body.page-industries .reference-footer .footer-consultation, body.page-contact .reference-footer .footer-consultation, body.page-careers .reference-footer .footer-consultation";
    const cardSelector = isAboutPage
      ? "body.page-about .about-strengths article, body.page-about .about-values .values-grid > *, body.page-about .about-team-card, body.page-about .about-office-layout > *, body.page-about .about-office .office-map"
      : isServicePage
        ? "body.page-service .service-detail-list > *, body.page-service .process-grid > *, body.page-service .service-related-grid > *, body.page-service .service-questions .question-item"
        : isIndustryPage
          ? "body.page-industries .sector-detail"
          : isContactPage
                ? "body.page-contact .contact-details, body.page-contact .contact-detail-card, body.page-contact .form-panel, body.page-contact .next-step-card"
            : isCareersPage
              ? "body.page-careers .career-panel, body.page-careers .photo-careers"
              : "";
    const revealItems = document.querySelectorAll(`${revealSelector}${cardSelector ? `, ${cardSelector}` : ""}`);
    const sectionObserver = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        entry.target.classList.toggle("is-visible", entry.isIntersecting);
      });
    }, { threshold: 0.08, rootMargin: "0px 0px -32px 0px" });

    revealItems.forEach((item) => {
      const isCard = cardSelector && item.matches(cardSelector);
      item.classList.add(isCard ? "scroll-reveal-card" : "scroll-reveal");
      if (isCard) {
        const siblings = [...item.parentElement.children];
        item.style.setProperty("--reveal-delay", `${(siblings.indexOf(item) % 4) * 75}ms`);
      }
      sectionObserver.observe(item);

      const itemBounds = item.getBoundingClientRect();
      if (itemBounds.bottom > 0 && itemBounds.top < window.innerHeight - 32) {
        item.classList.add("is-visible");
      }
    });
  }
}

document.addEventListener("keydown", (event) => {
  if (event.key !== "Escape") return;

  const supportDialog = document.querySelector("dialog[open]");
  if (supportDialog instanceof HTMLDialogElement) {
    supportDialog.close();
    return;
  }

  if (servicesMenu?.open) {
    servicesMenu.open = false;
    servicesMenu.querySelector("summary")?.focus();
    return;
  }

  if (menuToggle?.getAttribute("aria-expanded") === "true" && primaryNavigation) {
    primaryNavigation.classList.remove("is-open");
    menuToggle.setAttribute("aria-expanded", "false");
    menuToggle.focus();
  }
});