import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

// ✅ Start Alpine ASAP (important)
Alpine.start();

/**
 * Small helper: run after DOM is ready
 */
function onReady(fn) {
  if (document.readyState !== 'loading') fn();
  else document.addEventListener('DOMContentLoaded', fn);
}

onReady(() => {
  /**
   * HEADER SCROLL (siteHeader)
   * Adds .header-solid when user scrolls a bit
   */
  const header = document.getElementById('siteHeader');
  if (header) {
    let ticking = false;

    const updateHeader = () => {
      header.classList.toggle('header-solid', (window.scrollY || 0) > 8);
      ticking = false;
    };

    const onScroll = () => {
      if (ticking) return;
      ticking = true;
      requestAnimationFrame(updateHeader);
    };

    updateHeader();
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  /**
   * OPTIONAL: Navbar transparent mode
   */
  const navbar = document.getElementById('siteNavbar');
  const isTransparent = navbar?.classList.contains('navbar-transparent');

  if (navbar && isTransparent) {
    const handleScroll = () => {
      const y = window.scrollY || 0;
      navbar.classList.toggle('navbar-scrolled', y > 10);
    };
    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll();
  }

  /**
   * REVEAL ANIMATION (hero-reveal)
   */
  const revealEls = document.querySelectorAll('.hero-reveal');
  if (revealEls.length) {
    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((e) => {
          if (e.isIntersecting) e.target.classList.add('is-visible');
        });
      },
      { threshold: 0.2 }
    );

    revealEls.forEach((el) => io.observe(el));
  }

  /**
   * Scroll reveal (drop from top) — data-reveal
   */
  const items = document.querySelectorAll('[data-reveal]');
  if (items.length) {
    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            io.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.15, rootMargin: '0px 0px -10% 0px' }
    );

    items.forEach((el) => io.observe(el));
  }
});