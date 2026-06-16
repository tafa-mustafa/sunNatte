

// ── PAGE ROUTING ──
function showPage(page) {
  document.querySelectorAll('.page-section').forEach(s => s.classList.remove('active'));
  document.getElementById('main-' + page).classList.add('active');
  document.querySelectorAll('.nav-links a').forEach(a => a.classList.remove('active'));
  const navEl = document.getElementById('nav-' + page);
  if (navEl) navEl.classList.add('active');
  window.scrollTo({ top: 0, behavior: 'smooth' });
  setTimeout(initReveal, 100);
  return false;
}

// ── MOBILE MENU ──
function toggleMobile() {
  document.getElementById('mobileMenu').classList.toggle('open');
}
function closeMobile() {
  document.getElementById('mobileMenu').classList.remove('open');
}

// ── SCROLL REVEAL ──
function initReveal() {
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); observer.unobserve(e.target); } });
  }, { threshold: 0.12 });
  document.querySelectorAll('.reveal').forEach(el => {
    el.classList.remove('visible');
    observer.observe(el);
  });
}

// ── COUNT-UP ──
function countUp(el, target, duration) {
  let start = 0;
  const step = target / (duration / 16);
  const timer = setInterval(() => {
    start += step;
    if (start >= target) { el.textContent = target; clearInterval(timer); return; }
    el.textContent = Math.floor(start);
  }, 16);
}

function initCounters() {
  const statObserver = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        const target = parseInt(e.target.dataset.target);
        if (target) countUp(e.target, target, 1500);
        statObserver.unobserve(e.target);
      }
    });
  }, { threshold: 0.5 });
  document.querySelectorAll('[data-target]').forEach(el => statObserver.observe(el));
}

// ── FAQ ──
function toggleFaq(btn) {
  const item = btn.parentElement;
  const wasOpen = item.classList.contains('open');
  document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('open'));
  if (!wasOpen) item.classList.add('open');
}

// ── FORM ──
function handleSubmit(btn) {
  btn.textContent = '✅ Message envoyé !';
  btn.style.background = '#059669';
  btn.disabled = true;
  setTimeout(() => {
    btn.textContent = 'Envoyer le message →';
    btn.style.background = '';
    btn.disabled = false;
  }, 3000);
}

// ── NAV SCROLL ──
function initNavScroll() {
  const nav = document.querySelector('nav');
  if (!nav) return;
  const onScroll = () => nav.classList.toggle('scrolled', window.scrollY > 20);
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
}

// ── INIT ──
document.addEventListener('DOMContentLoaded', () => {
  initReveal();
  initCounters();
  initNavScroll();
});







//Script Contact

    // Notification
    function showNotification(message, type) {
        const notif = document.createElement('div');
        notif.className = `notification ${type}`;
        notif.innerHTML = `${type === 'success' ? '✅' : '❌'} ${message}`;
        document.body.appendChild(notif);
        setTimeout(() => notif.remove(), 5000);
    }

    // Envoi du message
    function sendMessage() {
        const name = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const subject = document.getElementById('subject').value;
        const message = document.getElementById('message').value.trim();

        // Validation
        if (!name || !email || !subject || !message) {
            showNotification('Veuillez remplir tous les champs obligatoires', 'error');
            return;
        }

        if (!email.includes('@') || !email.includes('.')) {
            showNotification('Veuillez entrer une adresse email valide', 'error');
            return;
        }

        // Construction du corps de l'email
        const body = `Nom: ${name}%0A%0AEmail: ${email}%0A%0ATéléphone: ${phone || 'Non renseigné'}%0A%0ASujet: ${subject}%0A%0AMessage:%0A${message}%0A%0A---%0AEnvoyé depuis le formulaire de contact SUNUNATT`;

        // Ouverture du client mail
        window.location.href = `mailto:contact@tecnon.sn?subject=Contact%20SUNUNATT%20-%20${encodeURIComponent(subject)}&body=${body}`;

        showNotification('Votre client mail s\'ouvre. Envoyez-nous votre message !', 'success');
    }


            // MENTION LÉGALE 
     // Scroll animation
    const cards = document.querySelectorAll('.section-card');
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry, i) => {
        if (entry.isIntersecting) {
          setTimeout(() => entry.target.classList.add('visible'), i * 80);
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.08 });
    cards.forEach(c => observer.observe(c));
 
    