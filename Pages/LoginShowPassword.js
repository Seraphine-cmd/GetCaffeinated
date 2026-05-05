    const togglePw = document.getElementById('togglePw');
    const pwInput  = document.getElementById('password');
    const eyeOpen  = document.getElementById('eyeOpen');
    const eyeClosed = document.getElementById('eyeClosed');

    togglePw.addEventListener('click', () => {
      const isText = pwInput.type === 'text';
      pwInput.type = isText ? 'password' : 'text';
      eyeOpen.style.display  = isText ? 'block' : 'none';
      eyeClosed.style.display = isText ? 'none'  : 'block';
    });