function openModal() {
    document.getElementById("editModal").style.display = "block";
}

function closeModal() {
    document.getElementById("editModal").style.display = "none";
}

window.onclick = function(event) {
    if (event.target == document.getElementById("editModal")) {
        closeModal();
    }
}