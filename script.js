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
