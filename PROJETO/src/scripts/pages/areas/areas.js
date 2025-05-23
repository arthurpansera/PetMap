const estadoSelect = document.getElementById('estado');
const cidadeSelect = document.getElementById('cidade');

function populateEstados() {
    while (estadoSelect.options.length > 1) {
        estadoSelect.remove(1);
    }
    Object.keys(locations).forEach(estado => {
        const option = document.createElement('option');
        option.value = estado;
        option.textContent = estado;
        estadoSelect.appendChild(option);
    });
}

function populateCidades(estado) {
    cidadeSelect.innerHTML = '<option value="">Selecione a Cidade</option>';
    cidadeSelect.disabled = true;

    if (estado && locations[estado]) {
        locations[estado].forEach(cidade => {
            const option = document.createElement('option');
            option.value = cidade;
            option.textContent = cidade;
            cidadeSelect.appendChild(option);
        });
        cidadeSelect.disabled = false;
    }
}

estadoSelect.addEventListener('change', () => {
    populateCidades(estadoSelect.value);
});

populateEstados();
populateCidades();

function filterTable() {
    const estadoSelecionado = estadoSelect.value;
    const cidadeSelecionada = cidadeSelect.value;
    const tbody = document.getElementById('table-body');
    const linhas = tbody.getElementsByTagName('tr');

    let temLinhaVisivel = false;

    for (let i = 0; i < linhas.length; i++) {
        const tdEstado = linhas[i].cells[0].textContent.trim();
        const tdCidade = linhas[i].cells[1].textContent.trim();

        const estadoOk = !estadoSelecionado || tdEstado === estadoSelecionado;
        const cidadeOk = !cidadeSelecionada || tdCidade === cidadeSelecionada;

        if (estadoOk && cidadeOk) {
            linhas[i].style.display = "";
            temLinhaVisivel = true;
        } else {
            linhas[i].style.display = "none";
        }
    }

    const noDataMessage = document.getElementById('no-data-message');
    noDataMessage.style.display = temLinhaVisivel ? 'none' : 'block';
}

estadoSelect.addEventListener('change', () => {
    cidadeSelect.value = "";
    populateCidades(estadoSelect.value);
    filterTable();
});

cidadeSelect.addEventListener('change', filterTable);

filterTable();

