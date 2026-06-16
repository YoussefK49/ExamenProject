const themeToggle = document.getElementById('themeToggle');
const storedTheme = localStorage.getItem('instant-theme');

function applyTheme(theme) {
  document.body.dataset.theme = theme;
  localStorage.setItem('instant-theme', theme);
  themeToggle.textContent = theme === 'dark' ? '☀️' : '🌙';
}

if (storedTheme === 'dark' || storedTheme === 'light') {
  applyTheme(storedTheme);
} else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
  applyTheme('dark');
} else {
  applyTheme('light');
}

themeToggle.addEventListener('click', () => {
  const nextTheme = document.body.dataset.theme === 'dark' ? 'light' : 'dark';
  applyTheme(nextTheme);
});
