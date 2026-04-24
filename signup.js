// ── Helpers ──────────────────────────────────────────────────
const $ = id => document.getElementById(id);
const field = id => document.getElementById('fg-' + id);

function setValid(id) {
    const f = field(id);
    f.classList.remove('error');
    f.classList.add('valid');
    const err = f.querySelector('.field-error');
    if (err) err.textContent = '';
}

function setError(id, msg) {
    const f = field(id);
    f.classList.remove('valid');
    f.classList.add('error');
    const err = f.querySelector('.field-error');
    if (err) err.textContent = msg;
}

function clearState(id) {
    const f = field(id);
    if (f) { f.classList.remove('valid', 'error'); }
}

function showAlert(type, msg) {
    const el = $(type === 'error' ? 'errorAlert' : 'successAlert');
    const other = $(type === 'error' ? 'successAlert' : 'errorAlert');
    other.classList.remove('show');
    el.innerHTML = (type === 'error' ? '⚠️ ' : '✅ ') + msg;
    el.classList.add('show');
    setTimeout(() => el.classList.remove('show'), 6000);
}

// ── Password strength ─────────────────────────────────────────
function checkStrength(pw) {
    let score = 0;
    if (pw.length >= 8)  score++;
    if (/[A-Z]/.test(pw)) score++;
    if (/[0-9]/.test(pw)) score++;
    if (/[^A-Za-z0-9]/.test(pw)) score++;
    return score;
}

const strengthFill  = $('strengthFill');
const strengthLabel = $('strengthLabel');
const strengthColors = ['', '#f44336', '#ff9800', '#2196f3', '#1DB954'];
const strengthTexts  = ['', 'Weak', 'Fair', 'Good', 'Strong'];

$('password').addEventListener('input', function () {
    const score = this.value ? checkStrength(this.value) : 0;
    strengthFill.style.width  = score ? (score * 25) + '%' : '0%';
    strengthFill.style.background = strengthColors[score] || '';
    strengthLabel.textContent = score ? strengthTexts[score] : '';
    strengthLabel.style.color = strengthColors[score] || '';
});

// ── Toggle password visibility ────────────────────────────────
$('togglePw').addEventListener('click', function () {
    const inp = $('password');
    const showing = inp.type === 'text';
    inp.type = showing ? 'password' : 'text';
    $('eyeIcon').innerHTML = showing
        ? '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>'
        : '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
});

// ── Live validation ───────────────────────────────────────────
$('firstname').addEventListener('blur', function () {
    this.value.trim().length >= 2 ? setValid('firstname') : setError('firstname', 'Enter at least 2 characters');
});
$('lastname').addEventListener('blur', function () {
    this.value.trim().length >= 2 ? setValid('lastname') : setError('lastname', 'Enter at least 2 characters');
});
$('email').addEventListener('blur', function () {
    /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value) ? setValid('email') : setError('email', 'Enter a valid email address');
});
$('password').addEventListener('blur', function () {
    this.value.length >= 6 ? setValid('password') : setError('password', 'Password must be at least 6 characters');
});
$('confirm').addEventListener('blur', function () {
    this.value === $('password').value && this.value !== ''
        ? setValid('confirm')
        : setError('confirm', 'Passwords do not match');
});

// ── Form submit ───────────────────────────────────────────────
$('signupForm').addEventListener('submit', function (e) {
    e.preventDefault();  // let PHP handle after validation

    let valid = true;

    const fn = $('firstname').value.trim();
    const ln = $('lastname').value.trim();
    const em = $('email').value.trim();
    const pw = $('password').value;
    const cf = $('confirm').value;

    if (fn.length < 2)                              { setError('firstname', 'Enter at least 2 characters'); valid = false; }
    else setValid('firstname');
    if (ln.length < 2)                              { setError('lastname',  'Enter at least 2 characters'); valid = false; }
    else setValid('lastname');
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(em))    { setError('email',     'Enter a valid email address');  valid = false; }
    else setValid('email');
    if (pw.length < 6)                              { setError('password',  'Password must be at least 6 characters'); valid = false; }
    else setValid('password');
    if (pw !== cf || cf === '')                     { setError('confirm',   'Passwords do not match');        valid = false; }
    else setValid('confirm');
    if (!$('terms').checked) {
        $('terms-error').textContent = 'You must accept the terms to continue';
        $('terms-error').style.display = 'block';
        valid = false;
    } else {
        $('terms-error').textContent = '';
        $('terms-error').style.display = 'none';
    }

    if (!valid) { showAlert('error', 'Please fix the errors above before continuing.'); return; }

    // Show loading state
    const btn = $('submitBtn');
    btn.classList.add('loading');
    btn.disabled = true;

    // Submit the form for real now
    this.submit();
});

// ── Show URL params (success / error from PHP) ────────────────
window.addEventListener('DOMContentLoaded', () => {
    const p = new URLSearchParams(window.location.search);
    if (p.get('error') === 'exists')  showAlert('error',   'This email is already registered. Try logging in.');
    if (p.get('error') === 'db')      showAlert('error',   'Database error. Please try again later.');
    if (p.get('success') === '1')     showAlert('success', 'Account created! You can now log in.');
});
