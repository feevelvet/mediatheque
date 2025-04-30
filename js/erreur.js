window.addEventListener('DOMContentLoaded', () => {
    const emailInput = document.querySelector('#email');
    const passwordInput = document.querySelector('#motdepasse');
  
    if (emailInput.classList.contains('input-error')) {
      emailInput.classList.remove('input-error');
      void emailInput.offsetWidth; 
      emailInput.classList.add('input-error');
    }
  
    if (passwordInput.classList.contains('input-error')) {
      passwordInput.classList.remove('input-error');
      void passwordInput.offsetWidth;
      passwordInput.classList.add('input-error');
    }
  });
  