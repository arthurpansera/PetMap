document.addEventListener('DOMContentLoaded', function () {
    const orderToggle = document.getElementById('orderToggle');
    const orderMenu = document.getElementById('orderMenu');

    orderToggle.addEventListener('click', function (event) {
        event.stopPropagation();
        orderMenu.classList.toggle('active');
    });

    document.addEventListener('click', function (event) {
        if (!orderMenu.contains(event.target) && event.target !== orderToggle) {
            orderMenu.classList.remove('active');
        }
    });
});