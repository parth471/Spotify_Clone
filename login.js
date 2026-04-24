// Toggle password visibility only — form submits naturally to login.php
const togglePassword = document.getElementById("togglePassword");
const password = document.getElementById("password");

togglePassword.addEventListener("click", () => {
    const type = password.getAttribute("type") === "password" ? "text" : "password";
    password.setAttribute("type", type);
    togglePassword.textContent = type === "password" ? "👁" : "🙈";
});
