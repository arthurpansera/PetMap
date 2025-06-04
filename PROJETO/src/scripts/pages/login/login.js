document.addEventListener("DOMContentLoaded", function() {
    const loginContent = document.getElementById("login-content");
    const registerContent = document.getElementById("register-content");

    const showRegisterBtns = document.querySelectorAll(".show-register");
    const showLoginBtns = document.querySelectorAll(".show-login");

    function toggleSections(show, hide) {
        hide.classList.remove("show");
        hide.classList.add("hidden");

        show.classList.remove("hidden");
        show.classList.add("show");
    }

    showRegisterBtns.forEach(btn => {
        btn.addEventListener("click", function(event) {
            event.preventDefault();
            toggleSections(registerContent, loginContent);
        });
    });

    showLoginBtns.forEach(btn => {
        btn.addEventListener("click", function(event) {
            event.preventDefault();
            toggleSections(loginContent, registerContent);
        });
    });
});