const menuToggle = document.getElementById('menuToggle');
const leftMenu = document.getElementById('leftMenu');
const menuOverlay = document.getElementById('menuOverlay');

menuToggle.addEventListener('click', () => {
  const isOpen = leftMenu.classList.toggle('open');
  menuToggle.classList.toggle('open', isOpen);
  menuOverlay.classList.toggle('active', isOpen);
  menuToggle.setAttribute('aria-expanded', isOpen);
});

menuOverlay.addEventListener('click', () => {
  leftMenu.classList.remove('open');
  menuToggle.classList.remove('open');
  menuOverlay.classList.remove('active');
  menuToggle.setAttribute('aria-expanded', false);
});
