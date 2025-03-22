const form = document.getElementById('form')
const campos = document.querySelectorAll('.required')
const spans = document.querySelectorAll('.span-required')

function btnRegisterOnClick(event) {
    let hasError = false;

    if (campos[0].value === "") {
        errorAlert('Preenchimento obrigatório: Nome', 0);
        hasError = true;
    } else if (!inputWithoutNumbers(campos[0].value)) {
        inputWithoutNumbersValidate(0);
        hasError = true;
    } else if (campos[1].value === "") {
        errorAlert('Preenchimento obrigatório: E-mail', 1);
        hasError = true;
    } else if (!isEmail(campos[1].value)) {
        emailValidate();
        hasError = true;
    } else if (campos[2].value === "") {
        errorAlert('Preenchimento obrigatório: Área de atuação', 2);
        hasError = true;
    } else if (!inputWithoutNumbers(campos[2].value)) {
        inputWithoutNumbersValidate(2);
        hasError = true;
    } else if (campos[3].value === "") {
        errorAlert('Preenchimento obrigatório: Data de Fundação', 3);
        hasError = true;
    } else if (!isValidDate(campos[3].value)) {
        dateValidate();
        hasError = true;
    } else if (campos[4].value === "") {
        errorAlert('Preenchimento obrigatório: Telefone', 4);
        hasError = true;
    } else if (!isTelephone(campos[4].value)) {
        telephoneValidate();
        hasError = true;
    } else if (campos[5].value === "") {
        errorAlert('Preenchimento obrigatório: Rede Social', 5);
        hasError = true;
    } else if (campos[6].value === "") {
        errorAlert('Preenchimento obrigatório: Senha', 6);
        hasError = true;
    } else if (!validPassword(campos[6].value)) {
        passwordValidate();
        hasError = true;
    } else if (campos[7].value === "") {
        errorAlert('Preenchimento obrigatório: Confirme sua senha', 7);
        hasError = true;
    } else if (campos[6].value !== campos[7].value) {
        confirmPasswordValidate();
        hasError = true;
    } else if (campos[8].value === "") {
        errorAlert('Preenchimento obrigatório: CEP', 8);
        hasError = true;
    } else if (!isCEP(campos[8].value)) {
        cepValidate();
        hasError = true;
    } else if (campos[9].value === "") {
        errorAlert('Preenchimento obrigatório: Rua', 9);
        hasError = true;
    } else if (!isRoad(campos[9].value)) {
        roadValidate();
        hasError = true;
    } else if (campos[10].value === "") {
        errorAlert('Preenchimento obrigatório: Número', 10);
        hasError = true;
    } else if (!isNum(parseInt(campos[10].value))) {
        numValidate();
        hasError = true;
    } else if (campos[11].value === "") {
        errorAlert('Preenchimento obrigatório: Bairro', 11);
        hasError = true;
    } else if (!inputWithoutNumbers(campos[11].value)) {
        inputWithoutNumbersValidate(11);
        hasError = true;
    } else if (campos[12].value === "") {
        errorAlert('Preenchimento obrigatório: Cidade', 12);
        hasError = true;
    } else if (!inputWithoutNumbers(campos[12].value)) {
        inputWithoutNumbersValidate(12);
        hasError = true;
    } else if (campos[13].value === "") {
        errorAlert('Preenchimento obrigatório: Estado', 13);
        hasError = true;
    } else if (!inputWithoutNumbers(campos[13].value)) {
        inputWithoutNumbersValidate(13);
        hasError = true;
    } else if (campos[14].value === "") {
        errorAlert('Preenchimento obrigatório: País', 14);
        hasError = true;
    } else if (!inputWithoutNumbers(campos[14].value)) {
        inputWithoutNumbersValidate(14);
        hasError = true;
    }

    if (hasError) {
        event.preventDefault();
    } else {
        form.submit();
        document.getElementById('submit').disabled = true;
    }
}

// Function creates a red border on the input where the condition is not met
function setError(index) {
    campos[index].style.border = '2px solid #e63636'
    spans[index].style.display = 'block'
    campos[index].focus()
}

// Function remove the red border
function removeError(index) {
    campos[index].style.border = ''
    spans[index].style.display = 'none'
}

// Function creates error alert for input that is not filled in
function errorAlert(message, index) {
    Swal.fire({
        title: 'Erro!',
        text: message,
        icon: 'error',
        confirmButtonText: 'Entendido' ,
        confirmButtonColor:'#399aa8',
        timer: 7000,
        timerProgressBar: true
    }).then((result) => {
        if (result.isConfirmed) {
            campos[index].focus()
        }
    })
}

// ----- FUNCTIONS TO VALIDATE THE INPUTS ----- //
function inputWithoutNumbersValidate(index) {
    if (campos[index].value === "") {
        removeError(index)
    } else if (!inputWithoutNumbers(campos[index].value)) {
        setError(index)
    } else {
        removeError(index)
    }
}

function emailValidate() {
    if (campos[1].value === "") {
        removeError(1)
    } else if (!isEmail(campos[1].value)) {
        setError(1)
    } else {
        removeError(1)
    }
}

function dateValidate() {
    if (campos[3].value === "") {
        removeError(3)
    } else if (!isValidDate(campos[3].value)) {
        setError(3)
    } else{
        removeError(3)
    }
}


function telephoneValidate() {
    if (campos[4].value === "") {
        removeError(4)
    } else if (!isTelephone(campos[4].value)) {
        setError(4)
    } else{
        removeError(4)
    }
}

function passwordValidate() {
    if (campos[6].value === "") {
        removeError(6)
    } else if (!validPassword(campos[6].value)) {
        setError(6)
    } else{
        removeError(6)
    }
}

function confirmPasswordValidate() {
    if (campos[7].value === "") {
        removeError(7)
    } else if ((campos[6].value !== campos[7].value)) {
        setError(7)
    } else{
        removeError(7)
    }
}

function cepValidate() {
    if (campos[8].value === "") {
        removeError(8)
    } else if (!isCEP(campos[8].value)) {
        setError(8)
    } else{
        removeError(8)
    }
}

function roadValidate() {
    if (campos[9].value === "") {
        removeError(9)
    } else if (!isRoad(campos[9].value)) {
        setError(9)
    } else{
        removeError(9)
    }
}

function numValidate() {
    if (campos[10].value === "") {
        removeError(10)
    } else if (!isNum(campos[10].value)) {
        setError(10)
    } else{
        removeError(10)
    }
}

// Function to check if is a valid date
function isValidDate(date) {
    const re = /^(0[1-9]|[12]\d|3[01])\/?(0[1-9]|1[0-2])\/?(19|20)\d\d$/

    if (!re.test(date)) {
        return false
    }

    let day, month, year
    if (date.includes('/')) {
        [day, month, year] = date.split('/').map(Number)
    } else {
        day = Number(date.slice(0, 2))
        month = Number(date.slice(2, 4))
        year = Number(date.slice(4, 8))
    }

    if (year < 1895 || year > 2024) {
        return false
    }

    const daysInMonth = [31, 28 + (isLeapYear(year) ? 1 : 0), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31]

    return day <= daysInMonth[month - 1]
}

// Function to check if a year is a leap year
function isLeapYear(year) {
    return (year % 4 === 0 && year % 100 !== 0) || (year % 400 === 0)
}

// ----- REGEX ----- //
// Function to check if the input contains numbers
function inputWithoutNumbers(index) {
    const re = /^[A-Za-zÀ-ÖØ-öø-ÿ\s]+$/
    return re.test(index)
}

// Function to check if is a valid email
function isEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/
    return re.test(email)
}

// Function to check if is a valid password
function validPassword(password) {
    const re =  /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&\.])[A-Za-z\d@$!%*?&\.]{8,}$/
    return re.test(password)
}

// Function to check if is a valid telephone
function isTelephone(telephone) {
    const re = /^(\+55\s?)?(55\s?)?\d{2}\s?9?\d{4}-?\d{4}$/
    return re.test(telephone)
}

// Function to check if is a valid CEP
function isCEP(cep){
    const re = /^\d{2}\.?\d{3}-?\d{3}$/
    return re.test(cep)
}

// Function to check if is a valid road
function isRoad(road){
    const re = /^[A-Za-z0-9\s]+$/
    return re.test(road)
}

// Function to check if is positive numbers
function isNum(num) {
    return !isNaN(num) && num > 0
}