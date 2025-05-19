const abandonosData = [
    { estado: "SP", cidade: "São Paulo", especie: "Cachorro", quantidade: 120 },
    { estado: "SP", cidade: "Campinas", especie: "Gato", quantidade: 85 },
    { estado: "SP", cidade: "Sorocaba", especie: "Outro", quantidade: 45 },
    { estado: "RJ", cidade: "Rio de Janeiro", especie: "Outro", quantidade: 150 },
    { estado: "RJ", cidade: "Niterói", especie: "Cachorro", quantidade: 95 },
    { estado: "RJ", cidade: "Cabo Frio", especie: "Gato", quantidade: 60 },
    { estado: "MG", cidade: "Belo Horizonte", especie: "Outro", quantidade: 75 },
    { estado: "MG", cidade: "Uberlândia", especie: "Cachorro", quantidade: 100 },
    { estado: "MG", cidade: "Juiz de Fora", especie: "Outro", quantidade: 30 },
    { estado: "PR", cidade: "Curitiba", especie: "Outro", quantidade: 120 },
    { estado: "PR", cidade: "Londrina", especie: "Gato", quantidade: 80 },
    { estado: "PR", cidade: "Maringá", especie: "Cachorro", quantidade: 110 }
];

function filterData() {
    const estado = document.getElementById('estado').value;
    const cidade = document.getElementById('cidade').value;
    const especie = document.getElementById('especie').value;

    const filteredData = abandonosData.filter(item => {
        return (
            (estado === "" || item.estado === estado) &&
            (cidade === "" || item.cidade === cidade) &&
            (especie === "" || item.especie === especie)
        );
    });

    displayData(filteredData);
}

function displayData(data) {
    const tableBody = document.getElementById('table-body');
    tableBody.innerHTML = "";
    const noDataMessage = document.getElementById('no-data-message');

    if (data.length === 0) {
        noDataMessage.style.display = "block";
    } else {
        noDataMessage.style.display = "none";
        data.forEach(item => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${item.estado}</td>
                <td>${item.cidade}</td>
                <td>${item.especie}</td>
                <td>${item.quantidade}</td>
            `;
            tableBody.appendChild(row);
        });
    }
}

document.getElementById('estado').addEventListener('change', (event) => {
    const estado = event.target.value;
    const cidadeSelect = document.getElementById('cidade');
    cidadeSelect.innerHTML = '<option value="">Selecione a Cidade</option>';

    const cidades = {
        "SP": ["São Paulo", "Campinas", "Sorocaba"],
        "RJ": ["Rio de Janeiro", "Niterói", "Cabo Frio"],
        "MG": ["Belo Horizonte", "Uberlândia", "Juiz de Fora"],
        "PR": ["Curitiba", "Londrina", "Maringá"]
    };

    if (estado) {
        cidades[estado].forEach(cidade => {
            const option = document.createElement('option');
            option.value = cidade;
            option.textContent = cidade;
            cidadeSelect.appendChild(option);
        });
    }

    filterData();
});

document.querySelectorAll('.filter-select').forEach(select => {
    select.addEventListener('change', filterData);
});

displayData(abandonosData);