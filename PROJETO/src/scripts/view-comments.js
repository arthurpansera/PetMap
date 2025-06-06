function toggleCommentForm(idPost) {
    const formWrapper = document.getElementById(`comment-form-${idPost}`);
    if (!formWrapper) return;

    if (formWrapper.style.display === "none" || formWrapper.style.display === "") {
        formWrapper.style.display = "block";

        const innerContainer = document.getElementById(`comment-form-container-${idPost}`);
        if (innerContainer) {
            innerContainer.style.display = "block";
        }
    } else {
        formWrapper.style.display = "none";

        const innerContainer = document.getElementById(`comment-form-container-${idPost}`);
        if (innerContainer) {
            innerContainer.style.display = "none";
        }
    }
}

function closeCommentForm(idPost) {
    const formWrapper = document.getElementById(`comment-form-${idPost}`);
    const innerForm = document.getElementById(`comment-form-container-${idPost}`);
    const submitBtn = document.getElementById(`submit-button-${idPost}`);

    if (formWrapper) formWrapper.style.display = "none";
    if (innerForm) innerForm.style.display = "none";

    document.getElementById(`id_comentario_${idPost}`).value = "";
    document.getElementById(`textarea_comentario_${idPost}`).value = "";

    if (submitBtn) {
        submitBtn.name = "comentar";
        submitBtn.textContent = "Enviar";
    }
}

function editarComentario(idPost, idComentario, texto) {
    const formWrapper = document.getElementById(`comment-form-${idPost}`);
    const innerForm = document.getElementById(`comment-form-container-${idPost}`);
    const submitBtn = document.getElementById(`submit-button-${idPost}`);

    if (formWrapper) formWrapper.style.display = "block";
    if (innerForm) innerForm.style.display = "block";

    document.getElementById(`id_comentario_${idPost}`).value = idComentario;
    document.getElementById(`textarea_comentario_${idPost}`).value = texto;

    if (submitBtn) {
        submitBtn.name = "update_comment";
        submitBtn.textContent = "Salvar";
    }

    formWrapper.scrollIntoView({ behavior: 'smooth' });
}

function editarComentarioPerfil(idPost, idComentario, conteudo) {
    document.querySelectorAll('.comment-form').forEach(form => {
        form.style.display = 'none';
    });

    const formWrapper = document.getElementById(`edit-form-${idComentario}`);
    const textarea = document.getElementById(`textarea_comentario_perfil_${idComentario}`);
    const inputId = document.getElementById(`id_comentario_perfil_${idComentario}`);

    if (formWrapper && textarea && inputId) {
        textarea.value = conteudo;
        inputId.value = idComentario;
        formWrapper.style.display = 'block';
        textarea.focus();
    }
}

function closeCommentFormPerfil(idComentario) {
    const formWrapper = document.getElementById(`edit-form-${idComentario}`);
    if (formWrapper) {
        formWrapper.style.display = 'none';
    }
}

function confirmDelete(button) {
    const form = button.closest('form');

    Swal.fire({
        title: 'Excluir comentário?',
        text: 'Essa ação não pode ser desfeita.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#cc4a2a',
        cancelButtonColor: '#4CAF50',
        confirmButtonText: 'Sim, excluir',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const hiddenBtn = document.createElement("input");
            hiddenBtn.type = "hidden";
            hiddenBtn.name = button.name;
            hiddenBtn.value = "1";
            form.appendChild(hiddenBtn);

            form.submit();
        }
    });
}

function toggleComments(postId) {
    const wrapper = document.getElementById(`comments-wrapper-${postId}`);
    if (wrapper) {
        wrapper.style.display = (wrapper.style.display === 'none' || wrapper.style.display === '') ? 'block' : 'none';
    }
}