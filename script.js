const themeToggle = document.getElementById('themeToggle');
const storedTheme = localStorage.getItem('instant-theme');

function applyTheme(theme) {
  document.body.dataset.theme = theme;
  localStorage.setItem('instant-theme', theme);
}

if (storedTheme === 'dark' || storedTheme === 'light') {
  applyTheme(storedTheme);
} else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
  applyTheme('dark');
} else {
  applyTheme('light');
}

if (themeToggle) {
  themeToggle.addEventListener('click', () => {
    const nextTheme = document.body.dataset.theme === 'dark' ? 'light' : 'dark';
    applyTheme(nextTheme);
  });
}

document.querySelectorAll('.comment-btn').forEach((button) => {
  button.addEventListener('click', () => {
    const targetId = button.dataset.target;
    const input = document.getElementById(targetId);
    if (input) {
      input.focus();
      input.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  });
});

document.querySelectorAll('.like-form .like-button').forEach((button) => {
  button.addEventListener('click', () => {
    button.classList.add('liked');
    setTimeout(() => button.classList.remove('liked'), 600);
  });
});

document.querySelectorAll('.mobile-nav-btn').forEach((button) => {
  button.addEventListener('click', () => {
    document.querySelectorAll('.mobile-nav-btn').forEach((item) => item.classList.remove('active'));
    button.classList.add('active');
  });
});

document.querySelectorAll('.follow-btn').forEach((button) => {
  button.addEventListener('click', () => {
    if (button.textContent === 'Volgen') {
      button.textContent = 'Gevolgd';
      button.style.color = 'var(--text)';
    } else {
      button.textContent = 'Volgen';
      button.style.color = 'var(--brand)';
    }
  });
});
