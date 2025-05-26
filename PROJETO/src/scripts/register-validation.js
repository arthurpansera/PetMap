document.querySelectorAll('.required').forEach((input) => {
    input.addEventListener('input', () => {
        const type = input.dataset.type;
        const value = input.value.trim();

        if (value === "") {
            removeError(input);
            return;
        }

        switch (type) {
            case "nome":
            case "bairro":
            case "cidade":
            case "estado":
            case "país":
                !inputWithoutNumbers(value) ? setError(input) : removeError(input);
                break;

            case "e-mail":
                !isEmail(value) ? setError(input) : removeError(input);
                break;

            case "CNPJ":
                !isCNPJ(value) ? setError(input) : removeError(input);
                break;

            case "CPF":
                !isCPF(value) ? setError(input) : removeError(input);
                break;

            case "data de nascimento":
                !isBirthYear(value) ? setError(input) : removeError(input);
                break;

            case "telefone":
                !isTelephone(value) ? setError(input) : removeError(input);
                break;

            case "senha":
                !validPassword(value) ? setError(input) : removeError(input);
                break;

            case "confirmar senha":
                const senhaInput = document.querySelector('[data-type="senha"]');
                value !== senhaInput.value ? setError(input) : removeError(input);
                break;

            case "CEP":
                !isCEP(value) ? setError(input) : removeError(input);
                break;

            case "rua":
                !isRoad(value) ? setError(input) : removeError(input);
                break;

            case "número":
                !isNum(value) ? setError(input) : removeError(input);
                break;
        }
    });
});

function btnRegisterOnClick(event, formElement) {
    console.log("Clique detectado");

    // Tenta encontrar o checkbox que estiver presente no form
    const naoSeiEndereco = formElement.querySelector("#nao_sei_endereco") || formElement.querySelector("#nao_sei_endereco_edit");
    const camposEndereco = ["rua", "bairro", "cidade", "estado"];

    const inputs = formElement.querySelectorAll('.required');
    let hasError = false;

    for (let input of inputs) {
        const type = input.dataset.type;
        const isRequired = input.dataset.required === "true";
        const value = input.value.trim();

        const isEndereco = camposEndereco.includes(type);

        if (isEndereco && naoSeiEndereco && naoSeiEndereco.checked) {
            removeError(input);
            continue;
        }

        if (isRequired && value === "") {
            console.log("Campo obrigatório não preenchido:", type);
            setError(input);
            errorAlert(`Preenchimento obrigatório: ${type}`, input);
            hasError = true;
            break;
        }
    }

    if (hasError) {
        event.preventDefault();
    } else {
        formElement.submit();
        const submitBtn = formElement.querySelector('#submit');
        if (submitBtn) {
            submitBtn.disabled = true;
        }
    }
}

function setError(input) {
    input.style.border = '2px solid #e63636';
    const span = input.nextElementSibling;
    if (span && span.classList.contains('span-required')) {
        span.style.display = 'block';
    }
    input.focus();
}

function removeError(input) {
    input.style.border = '';
    const span = input.nextElementSibling;
    if (span && span.classList.contains('span-required')) {
        span.style.display = 'none';
    }
}

function errorAlert(message, input) {
    Swal.fire({
        title: 'Erro!',
        text: message,
        icon: 'error',
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#7A00CC',
        timer: 7000,
        timerProgressBar: true
    }).then(() => {
        setTimeout(() => {
            input.focus();
        }, 300);
    });
}

// ----- REGEX ----- //

// Function to check if the input contains numbers
function inputWithoutNumbers(value) {
    const re = /^[A-Za-zÀ-ÖØ-öø-ÿ\s]+$/
    return re.test(value)
}

// Function to check if is a valid email
function isEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/
    return re.test(email)
}

// Function to check if is a valid CNPJ
function isCNPJ(cnpj) {
    const re = /^(\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}|\d{14})$/
    return re.test(cnpj)
}

// Function to check if is a valid CPF
function isCPF(cpf) {
    const re = /^\d{3}\.?\d{3}\.?\d{3}-?\d{2}$/
    return re.test(cpf) 
}

function isBirthYear(date) {
    const re = /^(\d{2})\/(\d{2})\/(\d{4})$/;

    if (!re.test(date)) {
        return false;
    }

    const [day, month, year] = date.split('/').map(Number);

    if (year < 1925 || year >= 2012) {
        return false;
    }

    const daysInMonth = [31, 28 + (isLeapYear(year) ? 1 : 0), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    return day > 0 && month > 0 && month <= 12 && day <= daysInMonth[month - 1];
}

// Function to check if a year is a leap year
function isLeapYear(year) {
    return (year % 4 === 0 && year % 100 !== 0) || (year % 400 === 0);
}

// Function to check if is a valid telephone
function isTelephone(telephone) {
    const re = /^(?:\+55\s?)?(?:\(?\d{2}\)?\s?)?9\d{4}-?\d{4}$/;
    return re.test(telephone);
}

// Function to check if is a valid password
function validPassword(password) {
    const re = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[#@$!%*?&.])[A-Za-z\d#@$!%*?&.]{8,}$/;
    return re.test(password);
}

// Function to check if is a valid CEP
function isCEP(cep) {
    const re = /^\d{2}\.?\d{3}-?\d{3}$/
    return re.test(cep)
}

// Function to check if is a valid road
function isRoad(road) {
    const re = /^[A-Za-zÀ-ÖØ-öø-ÿ0-9\s]+$/;
    return re.test(road);
}

// Function to check if is positive numbers
function isNum(num) {
    return !isNaN(num) && num > 0
}