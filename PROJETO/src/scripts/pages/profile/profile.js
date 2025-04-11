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

function openPostModal() {
    document.getElementById("postModal").style.display = "block";
}

function closePostModal() {
    document.getElementById("postModal").style.display = "none";
}

window.onclick = function(event) {
    if (event.target === document.getElementById("postModal")) {
        closePostModal();
    }
}

function openEditPostModal() {
    document.getElementById("postEditModal").style.display = "block";
}

function closeEditPostModal() {
    document.getElementById("postEditModal").style.display = "none";
}

window.onclick = function(event) {
    if (event.target === document.getElementById("postEditModal")) {
        closeEditPostModal();
    }
}