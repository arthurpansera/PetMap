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

function openEditPostModal(button) {
    const postId = button.getAttribute('data-id');
    const titulo = button.getAttribute('data-titulo');
    const conteudo = button.getAttribute('data-conteudo');
    const tipo = button.getAttribute('data-tipo');

    document.getElementById('edit_post_id').value = postId;
    document.getElementById('edit_titulo').value = titulo;
    document.getElementById('edit_conteudo').value = conteudo;
    document.getElementById('edit_tipo_publicacao').value = tipo;

    document.getElementById('postEditModal').style.display = 'block';
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

// aparecer o nome do arquivo quando for alterar a foto de perfil
const input = document.getElementById('foto_perfil');
const label = document.getElementById('label_foto');

input.addEventListener('change', function () {
    if (this.files && this.files.length > 0) {
    label.textContent = '📁 ' + this.files[0].name;
    } else {
    label.textContent = '📁 Escolher imagem';
    }
});

// aparecer o nome do arquivo quando for postar uma publicação
const inputPost = document.getElementById('foto_publicacao');
const labelPost = document.getElementById('label_foto_post');

inputPost.addEventListener('change', function () {
    if (this.files && this.files.length > 0) {
        // criar array com todos os nomes
        const fileNames = Array.from(this.files).map(file => file.name);
        // juntar nomes separados por vírgula
        labelPost.textContent = '📁 ' + fileNames.join(', ');
    } else {
        labelPost.textContent = '📁 Escolher imagem:';
    }
});


function confirmDelete(event) {
    event.preventDefault();

    Swal.fire({
        title: 'Excluir conta?',
        text: 'Essa ação não pode ser desfeita.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#cc4a2a',
        cancelButtonColor: '#4CAF50',
        confirmButtonText: 'Sim, excluir',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Excluindo...',
                text: 'Aguarde um momento.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            setTimeout(() => {
                event.target.form.submit();
            }, 1500);
        }
    });
}

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

    modalImage.src = "../images/uploads/posts/" + imagesArray[currentIndex];
    modal.style.display = "flex";
  });
});

prevBtn.addEventListener("click", () => {
  currentIndex = (currentIndex - 1 + imagesArray.length) % imagesArray.length;
  modalImage.src = "../images/uploads/posts/" + imagesArray[currentIndex];
});

nextBtn.addEventListener("click", () => {
  currentIndex = (currentIndex + 1) % imagesArray.length;
  modalImage.src = "../images/uploads/posts/" + imagesArray[currentIndex];
});

closeBtn.addEventListener("click", () => {
  modal.style.display = "none";
});

modal.addEventListener("click", e => {
  if (e.target === modal) {
    modal.style.display = "none";
  }
});