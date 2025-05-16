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

const inputPost = document.getElementById('foto_publicacao');
const labelPost = document.getElementById('label_foto_post');

inputPost.addEventListener('change', function () {
    if (this.files && this.files.length > 0) {
        labelPost.textContent = '📁 ' + this.files[0].name;
    } else {
        labelPost.textContent = '📁 Escolher imagem:';
    }
});