document.addEventListener("DOMContentLoaded", function () {
    const input = document.querySelector('.search-bar input[name="pesquisa"]');
    const suggestionBox = document.getElementById('user-suggestions');
    let timeout;

    const path = window.location.pathname;
    let profilePath = '';

    if (path.includes('index.php')) {
        profilePath = '/PetMap/PROJETO/src/assets/pages/';
    } else if (path.includes('lost-animals.php') || path.includes('rescued-animals.php')) {
        profilePath = '/PetMap/PROJETO/src/assets/pages/';
    } else {
        profilePath = '/PetMap/PROJETO/src/assets/pages/';
    }

    input.addEventListener('input', () => {
        clearTimeout(timeout);
        const term = input.value.trim();

        if (term.length < 1) {
            suggestionBox.innerHTML = '';
            suggestionBox.style.display = 'none';
            return;
        }

        timeout = setTimeout(() => {
            fetch(`${window.location.pathname}?ajax_search=1&term=${encodeURIComponent(term)}`)
                .then(res => res.json())
                .then(data => {
                    if (!data || data.length === 0) {
                        suggestionBox.innerHTML = '<div class="suggestion-item" style="padding:5px;">Nenhum usuário encontrado.</div>';
                        suggestionBox.style.display = 'block';
                        return;
                    }

                    suggestionBox.innerHTML = data.map(user =>
                        `<div class="suggestion-item" style="padding:5px; cursor:pointer;" onclick="window.location.href='${profilePath}view-profile.php?id=${user.id_usuario}'">${user.nome}</div>`
                    ).join('');
                    suggestionBox.style.display = 'block';
                })
                .catch(() => {
                    suggestionBox.innerHTML = '<div class="suggestion-item" style="padding:5px; color:red;">Erro na busca.</div>';
                    suggestionBox.style.display = 'block';
                });
        }, 300);
    });

    document.addEventListener('click', function (e) {
        if (!suggestionBox.contains(e.target) && e.target !== input) {
            suggestionBox.style.display = 'none';
        }
    });
});