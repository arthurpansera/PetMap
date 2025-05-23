const estadoSelect = document.getElementById('estado');
const cidadeSelect = document.getElementById('cidade');
const bairroSelect = document.getElementById('bairro');

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

    bairroSelect.innerHTML = '<option value="">Selecione o Bairro</option>';
    bairroSelect.disabled = true;

    if (estado && locations[estado]) {
        Object.keys(locations[estado]).forEach(cidade => {
            const option = document.createElement('option');
            option.value = cidade;
            option.textContent = cidade;
            cidadeSelect.appendChild(option);
        });
        cidadeSelect.disabled = false;
    }
}

function populateBairros(estado, cidade) {
    bairroSelect.innerHTML = '<option value="">Selecione o Bairro</option>';
    bairroSelect.disabled = true;

    if (estado && cidade && locations[estado] && locations[estado][cidade]) {
        locations[estado][cidade].forEach(bairro => {
            const option = document.createElement('option');
            option.value = bairro;
            option.textContent = bairro;
            bairroSelect.appendChild(option);
        });
        bairroSelect.disabled = false;
    }
}

function filterTable() {
    const estadoSelecionado = estadoSelect.value;
    const cidadeSelecionada = cidadeSelect.value;
    const bairroSelecionado = bairroSelect.value;
    const tbody = document.getElementById('table-body');
    const linhas = tbody.getElementsByTagName('tr');

    let temLinhaVisivel = false;

    for (let i = 0; i < linhas.length; i++) {
        const tdEstado = linhas[i].cells[0].textContent.trim();
        const tdCidade = linhas[i].cells[1].textContent.trim();
        const tdBairro = linhas[i].cells[2] ? linhas[i].cells[2].textContent.trim() : '';

        const estadoOk = !estadoSelecionado || tdEstado === estadoSelecionado;
        const cidadeOk = !cidadeSelecionada || tdCidade === cidadeSelecionada;
        const bairroOk = !bairroSelecionado || tdBairro === bairroSelecionado;

        if (estadoOk && cidadeOk && bairroOk) {
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
    bairroSelect.value = "";
    populateCidades(estadoSelect.value);
    filterTable();
});

cidadeSelect.addEventListener('change', () => {
    bairroSelect.value = "";
    populateBairros(estadoSelect.value, cidadeSelect.value);
    filterTable();
});

bairroSelect.addEventListener('change', filterTable);

populateEstados();
populateCidades();
filterTable();