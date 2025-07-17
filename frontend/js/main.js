// ARCHIVO PRINCIPAL: inicializa todo y utilidades
import { initSmoothScrolling, initNavbarScroll, initParallaxEffects, initScrollToTop, initLoadingEffects } from './animations.js';
import { initFormValidation } from './validations.js';
import { initContactForm } from './form.js';
import { initRecaptcha } from './recaptcha.js';

document.addEventListener('DOMContentLoaded', () => {
  initRecaptcha();
  initSmoothScrolling();
  initNavbarScroll();
  initFormValidation();
  initAnimations();
  initLoadingEffects();
  initContactForm();
  initScrollToTop();
  initParallaxEffects();
});

// Llama a las funciones de animaciones (p  ara modularidad)
function initAnimations() {
  // Animaciones al hacer scroll
  const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
  };

  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) entry.target.classList.add('animate-in');
    });
  }, observerOptions);

  document.querySelectorAll('.card, .row, .col-lg-6').forEach(el => {
    observer.observe(el);
  });
}

// Throttle para scroll
function throttle(func, limit) {
  let inThrottle;
  return function () {
    if (!inThrottle) {
      func.apply(this, arguments);
      inThrottle = true;
      setTimeout(() => inThrottle = false, limit);
    }
  };
}

// Aplicar throttle a scroll para optimizar rendimiento
window.addEventListener('scroll', throttle(() => {
  // Aquí podés poner eventos que quieras optimizar
}, 16)); // ~60fps

console.log('TechSolutions - JavaScript cargado correctamente ✅');
