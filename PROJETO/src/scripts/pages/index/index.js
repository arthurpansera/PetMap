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

// aparecer o nome do arquivo quando for postar uma publicação
const inputPost = document.getElementById('foto_publicacao');
const labelPost = document.getElementById('label_foto_post');

inputPost.addEventListener('change', function () {
    const maxImages = 8;

    if (this.files.length > maxImages) {
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: `Você pode selecionar no máximo ${maxImages} imagens.`
        });
        this.value = '';
        labelPost.textContent = '📁 Escolher imagem:';
        return;
    }

    if (this.files && this.files.length > 0) {
        const fileNames = Array.from(this.files).map(file => file.name);
        labelPost.textContent = '📁 ' + fileNames.join(', ');
    } else {
        labelPost.textContent = '📁 Escolher imagem:';
    }
});

document.getElementById('postForm').addEventListener('submit', function(e) {
    const maxImages = 8;
    const files = inputPost.files;

    if (files.length > maxImages) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: `Você pode enviar no máximo ${maxImages} imagens.`
        });
        return;
    }
});

const modal = document.getElementById("modal-images-posts");
const modalImage = document.getElementById("modalImage");
const closeBtn = document.querySelector(".close-images-posts");
const prevBtn = document.getElementById("prevImage");
const nextBtn = document.getElementById("nextImage");

let imagesArray = [];
let currentIndex = 0;

document.querySelectorAll(".image-wrapper").forEach(wrapper => {
    wrapper.addEventListener("click", () => {
        imagesArray = JSON.parse(wrapper.getAttribute("data-images"));

        const indexAttr = wrapper.getAttribute("data-index");
        if (imagesArray.length > 1) {
            currentIndex = 0;
        } else if (indexAttr !== null) {
            currentIndex = parseInt(indexAttr);
        } else {
            currentIndex = 0;
        }

        modalImage.src = "src/assets/images/uploads/posts/" + imagesArray[currentIndex];
        modal.style.display = "flex";
    });
});

prevBtn.addEventListener("click", () => {
    currentIndex = (currentIndex - 1 + imagesArray.length) % imagesArray.length;
    modalImage.src = "src/assets/images/uploads/posts/" + imagesArray[currentIndex];
});

nextBtn.addEventListener("click", () => {
    currentIndex = (currentIndex + 1) % imagesArray.length;
    modalImage.src = "src/assets/images/uploads/posts/" + imagesArray[currentIndex];
});

closeBtn.addEventListener("click", () => {
    modal.style.display = "none";
});

modal.addEventListener("click", e => {
    if (e.target === modal) {
        modal.style.display = "none";
    }
});
