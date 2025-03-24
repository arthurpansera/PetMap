const form = document.getElementById('form')
const inputs = document.querySelectorAll('.required')
const spans = document.querySelectorAll('.span-required')

function btnRegisterOnClick(event) {
    let hasError = false;

    if (inputs[0].value === "") {
        errorAlert('Preenchimento obrigatório: Nome', 0);
        hasError = true;
    } else if (!inputWithoutNumbers(inputs[0].value)) {
        inputWithoutNumbersValidate(0);
        hasError = true;
    } else if (inputs[1].value === "") {
        errorAlert('Preenchimento obrigatório: E-mail', 1);
        hasError = true;
    } else if (!isEmail(inputs[1].value)) {
        emailValidate();
        hasError = true;
    } else if (inputs[2].value === ""){
        hasError = true;
    } else if (!isCNPJ(inputs[2].value)){
        cnpjValidate()
        hasError = true;
    } else if (inputs[3].value === "") {
        errorAlert('Preenchimento obrigatório: Telefone');
        hasError = true;
    } else if (!isTelephone(inputs[3].value)) {
        telephoneValidate();
        hasError = true;
    } else if (inputs[4].value === "") {
        errorAlert('Preenchimento obrigatório: Senha');
        hasError = true;
    } else if (!validPassword(inputs[4].value)) {
        passwordValidate();
        hasError = true;
    } else if (inputs[5].value === "") {
        errorAlert('Preenchimento obrigatório: Confirme sua senha');
        hasError = true;
    } else if (inputs[5].value !== inputs[4].value) {
        confirmPasswordValidate();
        hasError = true;
    } else if (inputs[6].value === "") {
        errorAlert('Preenchimento obrigatório: CEP');
        hasError = true;
    } else if (!isCEP(inputs[6].value)) {
        cepValidate();
        hasError = true;
    } else if (inputs[7].value === "") {
        errorAlert('Preenchimento obrigatório: Rua');
        hasError = true;
    } else if (!isRoad(inputs[7].value)) {
        roadValidate();
        hasError = true;
    } else if (inputs[8].value === "") {
        errorAlert('Preenchimento obrigatório: Número');
        hasError = true;
    } else if (!isNum(parseInt(inputs[8].value))) {
        numValidate();
        hasError = true;
    } else if (inputs[9].value === "") {
        errorAlert('Preenchimento obrigatório: Bairro');
        hasError = true;
    } else if (!inputWithoutNumbers(inputs[9].value)) {
        inputWithoutNumbersValidate(9);
        hasError = true;
    } else if (inputs[10].value === "") {
        errorAlert('Preenchimento obrigatório: Cidade');
        hasError = true;
    } else if (!inputWithoutNumbers(inputs[10].value)) {
        inputWithoutNumbersValidate(10);
        hasError = true;
    } else if (inputs[11].value === "") {
        errorAlert('Preenchimento obrigatório: Estado');
        hasError = true;
    } else if (!inputWithoutNumbers(inputs[11].value)) {
        inputWithoutNumbersValidate(11);
        hasError = true;
    } else if (inputs[12].value === "") {
        errorAlert('Preenchimento obrigatório: País');
        hasError = true;
    } else if (!inputWithoutNumbers(inputs[12].value)) {
        inputWithoutNumbersValidate(12);
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
    inputs[index].style.border = '2px solid #e63636'
    spans[index].style.display = 'block'
    inputs[index].focus()
}
// Function remove the red border
function removeError(index) {
    inputs[index].style.border = ''
    spans[index].style.display = 'none'
}
// Function creates error alert for input that is not filled in
function errorAlert(message, index) {
    Swal.fire({
        title: 'Erro!',
        text: message,
        icon: 'error',
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#399aa8',
        timer: 7000,
        timerProgressBar: true
    }).then((result) => {
        if (result.isConfirmed) {
            setTimeout(() => {
                inputs[index].focus();
            }, 300); 
        }
    });
}

// ----- FUNCTIONS TO VALIDATE THE INPUTS ----- //
function inputWithoutNumbersValidate(index) {
    if (inputs[index].value === "") {
        removeError(index)
    } else if (!inputWithoutNumbers(inputs[index].value)) {
        setError(index)
    } else {
        removeError(index)
    }
}
function emailValidate() {
    if (inputs[1].value === "") {
        removeError(1)
    } else if (!isEmail(inputs[1].value)) {
        setError(1)
    } else {
        removeError(1)
    }
}
function cnpjValidate() {
    if (inputs[2].value === "") {
        removeError(2)
    } else if (!isCNPJ(inputs[2].value)) {
        setError(2)
    } else{
        removeError(2)
    }
}
function telephoneValidate() {
    if (inputs[3].value === "") {
        removeError(3)
    } else if (!isTelephone(inputs[3].value)) {
        setError(3)
    } else{
        removeError(3)
    }
}
function passwordValidate() {
    if (inputs[4].value === "") {
        removeError(4)
    } else if (!validPassword(inputs[4].value)) {
        setError(4)
    } else{
        removeError(4)
    }
}
function confirmPasswordValidate() {
    if (inputs[5].value === "") {
        removeError(5)
    } else if ((inputs[4].value !== inputs[5].value)) {
        setError(5)
    } else{
        removeError(5)
    }
}
function cepValidate() {
    if (inputs[6].value === "") {
        removeError(6)
    } else if (!isCEP(inputs[6].value)) {
        setError(6)
    } else{
        removeError(6)
    }
}
function roadValidate() {
    if (inputs[7].value === "") {
        removeError(7)
    } else if (!isRoad(inputs[7].value)) {
        setError(7)
    } else{
        removeError(7)
    }
}
function numValidate() {
    if (inputs[8].value === "") {
        removeError(8)
    } else if (!isNum(inputs[8].value)) {
        setError(8)
    } else{
        removeError(8)
    }
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
// Function to check if is a valid telephone
function isTelephone(telephone) {
    const re = /^(\+55\s?)?(55\s?)?\d{2}\s?9?\d{4}-?\d{4}$/
    return re.test(telephone)
}
// Function to check if is a valid password
function validPassword(password) {
    const re =  /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&\.])[A-Za-z\d@$!%*?&\.]{8,}$/
    return re.test(password)
}
// Function to check if is a valid CNPJ
function isCNPJ(cnpj) {
    const re = /^(\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}|\d{14})$/
    return re.test(cnpj)
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