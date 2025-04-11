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