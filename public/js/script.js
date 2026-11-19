// ── MOBILE MENU ──
function toggleMobile() {
  const menu = document.getElementById('mobileMenu');
  const overlay = document.getElementById('mobileOverlay');
  const btn = document.getElementById('hamburgerBtn');
  const isOpen = menu.classList.toggle('open');
  overlay.classList.toggle('open', isOpen);
  btn.classList.toggle('open', isOpen);
  btn.setAttribute('aria-expanded', isOpen);
  btn.setAttribute('aria-label', isOpen ? 'Fermer le menu' : 'Ouvrir le menu');
  menu.setAttribute('aria-hidden', !isOpen);
  document.body.style.overflow = isOpen ? 'hidden' : '';
}

function closeMobile() {
  const menu = document.getElementById('mobileMenu');
  if (!menu.classList.contains('open')) return;
  toggleMobile();
}

// ── SCROLL REVEAL ──
function initReveal() {
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.classList.add('visible');
        observer.unobserve(e.target);
      }
    });
  }, { threshold: 0.12 });

  document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
}

// ── NAV SCROLL ──
function initNavScroll() {
  const nav = document.querySelector('nav');
  if (!nav) return;
  const onScroll = () => nav.classList.toggle('scrolled', window.scrollY > 20);
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
}

// ── ACTIVE NAV LINK ──
function initActiveNav() {
  const path = window.location.pathname;
  const hash = window.location.hash;
  let active = 'home';

  if (path.includes('a-propos')) active = 'about';
  else if (path.includes('contact')) active = 'contact';
  else if (path.includes('mentions-legales')) active = 'legal';
  else if (hash === '#comment_marche') active = 'how';
  else if (hash === '#features') active = 'features';

  document.querySelectorAll('[data-nav]').forEach(link => {
    link.classList.toggle('active', link.dataset.nav === active);
  });
}

// ── SMOOTH SCROLL (offset nav) ──
function initSmoothScroll() {
  document.querySelectorAll('a[href*="#"]').forEach(anchor => {
    anchor.addEventListener('click', (e) => {
      const href = anchor.getAttribute('href');
      if (!href || href === '#') return;

      let url;
      try {
        url = new URL(href, window.location.origin);
      } catch {
        return;
      }

      if (url.pathname !== window.location.pathname) return;

      const target = document.querySelector(url.hash);
      if (!target) return;

      e.preventDefault();
      closeMobile();
      const offset = document.querySelector('nav')?.offsetHeight ?? 72;
      const top = target.getBoundingClientRect().top + window.scrollY - offset - 16;
      window.scrollTo({ top, behavior: 'smooth' });
      history.pushState(null, '', url.hash);
    });
  });
}

// ── LEGAL PAGE — animation au scroll ──
function initLegalCards() {
  const cards = document.querySelectorAll('.section-card.is-animating');
  if (!cards.length) return;

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry, i) => {
      if (entry.isIntersecting) {
        setTimeout(() => entry.target.classList.add('visible'), i * 80);
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.08 });

  cards.forEach(c => observer.observe(c));
}

// ── COPIER (page invitation) ──
function initCopy() {
  document.querySelectorAll('[data-copy]').forEach(btn => {
    btn.addEventListener('click', async () => {
      const original = btn.textContent;
      try {
        await navigator.clipboard.writeText(btn.dataset.copy);
        btn.textContent = 'Code copié';
      } catch {
        btn.textContent = 'Copie impossible';
      }
      setTimeout(() => { btn.textContent = original; }, 1800);
    });
  });
}

// ── INIT ──
document.addEventListener('DOMContentLoaded', () => {
  initReveal();
  initNavScroll();
  initActiveNav();
  initSmoothScroll();
  initLegalCards();
  initCopy();
});

document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') closeMobile();
});
