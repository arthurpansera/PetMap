document.addEventListener('DOMContentLoaded', function() {
    const btnBanir = document.getElementById('btnBanirUsuario');
    const formBanir = document.getElementById('banirForm');

    if (btnBanir && formBanir) {
        btnBanir.addEventListener('click', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Tem certeza?',
                text: "Deseja realmente banir este usuário?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sim, banir!',
                cancelButtonText: 'Cancelar',
                heightAuto: false
            }).then((result) => {
                if (result.isConfirmed) {
                    formBanir.submit();
                }
            });
        });
    }

    const btnDesbanir = document.getElementById('btnDesbanirUsuario');
    const formDesbanir = document.getElementById('desbanirForm');

    if (btnDesbanir && formDesbanir) {
        btnDesbanir.addEventListener('click', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Tem certeza?',
                text: "Deseja realmente desbanir este usuário?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#007bff',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sim, desbanir!',
                cancelButtonText: 'Cancelar',
                heightAuto: false
            }).then((result) => {
                if (result.isConfirmed) {
                    formDesbanir.submit();
                }
            });
        });
    }
});

function showSection(sectionId) {
    const sections = document.querySelectorAll("section.content");
    sections.forEach(section => {
        section.style.display = "none";
    });

    const target = document.getElementById(sectionId);
    if (target) target.style.display = "block";
}

document.addEventListener('DOMContentLoaded', function () {
    const deleteButtons = document.querySelectorAll('.sweet-remove-btn');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function () {
            Swal.fire({
                title: 'Tem certeza?',
                text: 'Você deseja remover esta publicação?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#aaa',
                confirmButtonText: 'Sim, remover',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    button.closest('form').submit();
                }
            });
        });
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const deleteCommentButtons = document.querySelectorAll('.sweet-remove-comment-btn');

    deleteCommentButtons.forEach(button => {
        button.addEventListener('click', function () {
            Swal.fire({
                title: 'Tem certeza?',
                text: 'Você deseja remover este comentário?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#aaa',
                confirmButtonText: 'Sim, remover',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    button.closest('form').submit();
                }
            });
        });
    });
});