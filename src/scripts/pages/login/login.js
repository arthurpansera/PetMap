document.addEventListener("DOMContentLoaded", function() {
    const loginContent = document.getElementById("login-content");
    const registerContent = document.getElementById("register-content");

    const showRegisterBtn = document.getElementById("show-register");
    const showLoginBtn = document.getElementById("show-login");

    function toggleSections(show, hide) {
        hide.classList.remove("show");
        hide.classList.add("hidden");
    
        show.classList.remove("hidden");
        show.classList.add("show");
    }

    showRegisterBtn.addEventListener("click", function(event) {
        event.preventDefault();
        toggleSections(registerContent, loginContent);
    });

    showLoginBtn.addEventListener("click", function(event) {
        event.preventDefault();
        toggleSections(loginContent, registerContent);
    });
});