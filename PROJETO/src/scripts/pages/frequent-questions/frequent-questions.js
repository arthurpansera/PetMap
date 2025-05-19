const acc = document.querySelectorAll(".accordion");
    
acc.forEach(button => {
    button.addEventListener("click", function () {
        this.classList.toggle("active");
        const panel = this.nextElementSibling;
        panel.classList.toggle("open");
    });
});