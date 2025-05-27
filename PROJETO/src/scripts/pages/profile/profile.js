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

document.addEventListener('DOMContentLoaded', function () {
    if (localStorage.getItem('postDeleted') === 'true') {
    Swal.fire({
        icon: 'success',
        title: 'Publicação excluída com sucesso!',
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#7A00CC',
        allowOutsideClick: true,
        timer: 5000,
        timerProgressBar: true,
    });
    localStorage.removeItem('postDeleted');
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const checkboxEditar = document.getElementById('nao_sei_endereco_edit');
    if (checkboxEditar) {
    checkboxEditar.addEventListener('change', desabilitarCamposEnderecoEditar);
    }
});

function desabilitarCamposEnderecoEditar() {
    const checkbox = document.getElementById('nao_sei_endereco_edit');
    const campos = document.querySelectorAll('.campo-endereco-edit');

    campos.forEach(campo => {
    campo.disabled = checkbox.checked;
    if (checkbox.checked) campo.value = '';
    });
}

function openEditPostModal(button) {
    const postId = button.dataset.id;
    const titulo = button.dataset.titulo;
    const conteudo = button.dataset.conteudo;
    const tipo = button.dataset.tipo;
    const rua = button.dataset.endereco_rua;
    const bairro = button.dataset.endereco_bairro;
    const cidade = button.dataset.endereco_cidade;
    const estado = button.dataset.endereco_estado;
    const naoSeiEndereco = button.dataset.naoSeiEndereco === '1';
    const imagens = JSON.parse(button.dataset.images);

    document.getElementById('edit_post_id').value = postId;
    document.getElementById('edit_titulo').value = titulo;
    document.getElementById('edit_conteudo').value = conteudo;
    document.getElementById('edit_tipo_publicacao').value = tipo;
    document.getElementById('edit_endereco_rua').value = rua;
    document.getElementById('edit_endereco_bairro').value = bairro;
    document.getElementById('edit_endereco_cidade').value = cidade;
    document.getElementById('edit_endereco_estado').value = estado;

    const checkboxEndereco = document.getElementById('nao_sei_endereco_edit');
    checkboxEndereco.checked = naoSeiEndereco;

    desabilitarCamposEnderecoEditar();

    const gallery = document.getElementById('edit-image-gallery');
    gallery.innerHTML = '';

    imagens.forEach(imageName => {
    const container = document.createElement('div');

    const img = document.createElement('img');
    img.src = `../images/uploads/posts/${imageName}`;
    img.alt = 'Imagem da publicação';

    const checkbox = document.createElement('input');
    checkbox.type = "checkbox";
    checkbox.name = "delete_images[]";
    checkbox.value = imageName;
    checkbox.id = `delete_${imageName}`;

    const label = document.createElement('label');
    label.htmlFor = `delete_${imageName}`;
    label.classList.add("delete-icon");

    container.appendChild(img);
    container.appendChild(checkbox);
    container.appendChild(label);

    gallery.appendChild(container);
    });

    const inputEdit = document.getElementById('foto_publicacao_edit');
    inputEdit.dataset.existing = imagens.length;

    document.getElementById('postEditModal').style.display = 'block';
}

function closeEditPostModal() {
    document.getElementById('postEditModal').style.display = 'none';
}

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

const inputEdit = document.getElementById('foto_publicacao_edit');
const labelEdit = document.getElementById('label_foto_post_edit');

inputEdit.addEventListener('change', () => {
    const existing = parseInt(inputEdit.dataset.existing || "0");
    const selected = inputEdit.files.length;
    const maxImages = 8;

    const total = existing + selected;

    if (total > maxImages) {
        Swal.fire({
            icon: 'warning',
            title: 'Limite de imagens ultrapassado',
            text: `Você já tem ${existing}. Só pode adicionar mais ${maxImages - existing}.`
        });
        inputEdit.value = '';
        labelEdit.textContent = '📁 Escolher imagem:';
        return;
    }
    
    const fileNames = Array.from(inputEdit.files).map(file => file.name);
    labelEdit.textContent = '📁 ' + fileNames.join(', ');
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

function confirmDeletePost(button) {
    Swal.fire({
        title: 'Excluir publicação?',
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
                localStorage.setItem('postDeleted', 'true');

                const form = button.closest('form');
                form.submit();
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

document.querySelectorAll(".image-wrapper.more-images-posts").forEach(wrapper => {
    wrapper.addEventListener("click", () => {
        const dataImages = wrapper.getAttribute("data-images");
        if (!dataImages) return;

        imagesArray = JSON.parse(dataImages);
        currentIndex = 0;

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

function desabilitarCamposEndereco() {
  const checkbox = document.getElementById('nao_sei_endereco');
  const campos = document.querySelectorAll('.campo-endereco');

  campos.forEach(campo => {
    if (checkbox.checked) {
      campo.value = '';
      campo.disabled = true;
    } else {
      campo.disabled = false;
    }
  });
}

function showSection(sectionId) {
    const sections = document.querySelectorAll("section.content");
    sections.forEach(section => {
        section.style.display = "none";
    });

    const target = document.getElementById(sectionId);
    if (target) target.style.display = "block";
}