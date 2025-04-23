function openModal() {
    document.getElementById("editModal").style.display = "block";
}

function closeModal() {
    document.getElementById("editModal").style.display = "none";
}

function openPostModal() {
    document.getElementById("postModal").style.display = "block";
}

function closePostModal() {
    document.getElementById("postModal").style.display = "none";
}

function openEditPostModal() {
    document.getElementById("postEditModal").style.display = "block";
}

function closeEditPostModal() {
    document.getElementById("postEditModal").style.display = "none";
}

window.addEventListener("click", function(event) {
    if (event.target === document.getElementById("editModal")) {
        closeModal();
    }
    if (event.target === document.getElementById("postModal")) {
        closePostModal();
    }
    if (event.target === document.getElementById("postEditModal")) {
        closeEditPostModal();
    }
})