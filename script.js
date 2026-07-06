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

const menuToggle = document.getElementById('menuToggle');
const menuClose = document.getElementById('menuClose');
const mobileMenu = document.getElementById('mobileMenu');
const profileDropdownToggle = document.getElementById('profileDropdownToggle');
const profileDropdownMenu = document.getElementById('profileDropdownMenu');

function toggleMobileMenu(open) {
  if (!mobileMenu) return;
  mobileMenu.classList.toggle('open', open);
  mobileMenu.setAttribute('aria-hidden', String(!open));
}

function toggleProfileDropdown(open) {
  if (!profileDropdownMenu || !profileDropdownToggle) return;
  profileDropdownMenu.classList.toggle('open', open);
  profileDropdownToggle.setAttribute('aria-expanded', String(open));
}

if (menuToggle) {
  menuToggle.addEventListener('click', () => toggleMobileMenu(true));
}

if (menuClose) {
  menuClose.addEventListener('click', () => toggleMobileMenu(false));
}

if (profileDropdownToggle) {
  profileDropdownToggle.addEventListener('click', (event) => {
    event.stopPropagation();
    const open = !profileDropdownMenu.classList.contains('open');
    toggleProfileDropdown(open);
  });
}

if (mobileMenu) {
  mobileMenu.addEventListener('click', (event) => {
    if (event.target === mobileMenu) {
      toggleMobileMenu(false);
    }
  });
}

const themeToggleSettings = document.getElementById('themeToggleSettings');

if (themeToggleSettings) {
  themeToggleSettings.addEventListener('click', () => {
    const nextTheme = document.body.dataset.theme === 'dark' ? 'light' : 'dark';
    applyTheme(nextTheme);
  });
}

document.addEventListener('click', (event) => {
  if (event.target.classList.contains('modal')) {
    event.target.style.display = 'none';
  }

  if (profileDropdownMenu && profileDropdownMenu.classList.contains('open')) {
    const isDropdownClick = event.composedPath().includes(profileDropdownMenu) || event.composedPath().includes(profileDropdownToggle);
    if (!isDropdownClick) {
      toggleProfileDropdown(false);
    }
  }
});

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape') {
    if (mobileMenu && mobileMenu.classList.contains('open')) {
      toggleMobileMenu(false);
    }
    if (profileDropdownMenu && profileDropdownMenu.classList.contains('open')) {
      toggleProfileDropdown(false);
    }
  }
});

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

document.addEventListener('submit', (e) => {
  const form = e.target.closest('.like-form');
  if (!form) {
    return;
  }

  e.preventDefault();
  const button = form.querySelector('.like-button');
  const formData = new FormData(form);

  if (button && button.classList.contains('liked')) {
    formData.set('action', 'unlike');
  }

  fetch('api.php', {
    method: 'POST',
    credentials: 'same-origin',
    body: formData
  })
    .then(response => response.json())
    .then(data => {
      if (!data.success) {
        console.error('Like action failed', data);
        return;
      }

      const liked = !!data.isLiked;
      if (button) {
        button.classList.toggle('liked', liked);
        const svg = button.querySelector('svg');
        if (svg) {
          svg.setAttribute('fill', liked ? 'currentColor' : 'none');
        }
      }

      const postCard = form.closest('.post-card');
      if (postCard) {
        const likesText = postCard.querySelector('.post-likes');
        if (likesText) {
          const likeCount = Number(data.likeCount);
          likesText.textContent = `${Number.isFinite(likeCount) ? likeCount : Math.max(0, (parseInt(likesText.textContent, 10) || 0) + (liked ? 1 : -1))} vind-ik-leuks`;
        }
      }
    })
    .catch(error => console.error('Error:', error));
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

document.querySelectorAll('.comment-form').forEach((form) => {
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const formData = new FormData(form);
    const input = form.querySelector('input[name="comment_text"]');

    if (input && input.value.trim()) {
      fetch('api.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          input.value = '';
          // Optioneel: toon een succes bericht of update comment count
        }
      })
      .catch(error => console.error('Error:', error));
    }
  });
});

// Skip comment forms that have onsubmit handlers (like the login redirect ones)
document.querySelectorAll('.comment-form[onsubmit]').forEach((form) => {
  form.removeEventListener('submit', null);
});
