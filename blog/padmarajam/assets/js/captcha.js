// captcha.js
function generateCaptcha() {
  const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
  let captcha = '';
  for (let i = 0; i < 6; i++) {
    captcha += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  document.getElementById('captchaText').innerText = captcha;
  document.getElementById('captcha_input').value = '';
  fetch('save_captcha.php?code=' + captcha);
}
window.onload = generateCaptcha;
