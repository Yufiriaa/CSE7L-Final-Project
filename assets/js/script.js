// Auto-dismiss toast
const toast = document.getElementById('toast');
if (toast) {
  setTimeout(() => {
    toast.style.transition = 'opacity .4s';
    toast.style.opacity = '0';
    setTimeout(() => toast.remove(), 400);
  }, 2600);
}

// Close modal on overlay click
document.querySelectorAll('.modal-overlay').forEach(o => {
  o.addEventListener('click', e => {
    if (e.target === o) {
      window.location = window.location.pathname + window.location.search.replace(/&?(add(_group)?|detail=\d+)/g, '').replace(/&&/, '&').replace(/\?&/, '?');
    }
  });
});
