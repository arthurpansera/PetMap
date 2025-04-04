const form = document.getElementById('form')
const inputs = document.querySelectorAll('.required')
const spans = document.querySelectorAll('.span-required')

function btnRegisterOnClick(event) {
    let hasError = false;

    if (inputs[0].value === "") {
        errorAlert('Preenchimento obrigatório: Nome');
        hasError = true;
    } else if (!inputWithoutNumbers(inputs[0].value)) {
        inputWithoutNumbersValidate(0);
        hasError = true;
    } else if (inputs[1].value === "") {
        errorAlert('Preenchimento obrigatório: E-mail');
        hasError = true;
    } else if (!isEmail(inputs[1].value)) {
        emailValidate();
        hasError = true;
    } else if (inputs[2].value === "") {
        errorAlert('Preenchimento obrigatório: Telefone');
        hasError = true;
    } else if (!isTelephone(inputs[2].value)) {
        telephoneValidate();
        hasError = true;
    } else if (inputs[3].value === "") {
        errorAlert('Preenchimento obrigatório: Senha');
        hasError = true;
    } else if (!validPassword(inputs[3].value)) {
        passwordValidate();
        hasError = true;
    } else if (inputs[4].value === "") {
        errorAlert('Preenchimento obrigatório: Confirme sua senha');
        hasError = true;
    } else if (inputs[4].value !== inputs[3].value) {
        confirmPasswordValidate();
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
        confirmButtonColor: '#7A00CC',
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
function telephoneValidate() {
    if (inputs[2].value === "") {
        removeError(2)
    } else if (!isTelephone(inputs[2].value)) {
        setError(2)
    } else{
        removeError(2)
    }
}
function passwordValidate() {
    if (inputs[3].value === "") {
        removeError(3)
    } else if (!validPassword(inputs[3].value)) {
        setError(3)
    } else{
        removeError(3)
    }
}
function confirmPasswordValidate() {
    if (inputs[4].value === "") {
        removeError(4)
    } else if ((inputs[3].value !== inputs[4].value)) {
        setError(4)
    } else{
        removeError(4)
    }
}

// ----- REGEX ----- //

// Function to check if the input contains numbers
function inputWithoutNumbers(index) {
    const re = /^[A-Za-zÀ-ÖØ-öø-ÿ\s]+$/;
    return re.test(index)
}
// Function to check if is a valid email
function isEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
    return re.test(email)
}
// Function to check if is a valid telephone
function isTelephone(telephone) {
    const re = /^(\+55\s?)?(55\s?)?\d{2}\s?9?\d{4}-?\d{4}$/;
    return re.test(telephone)
}
// Function to check if is a valid password
function validPassword(password) {
    const re = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[#@$!%*?&.])[A-Za-z\d#@$!%*?&.]{8,}$/;
    return re.test(password);
}

