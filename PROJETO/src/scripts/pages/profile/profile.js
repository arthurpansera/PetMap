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
const input = document.getElementById('foto_perfil');
const label = document.getElementById('label_foto');

input.addEventListener('change', function () {
    if (this.files && this.files.length > 0) {
    label.textContent = '📁 ' + this.files[0].name;
    } else {
    label.textContent = '📁 Escolher imagem';
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