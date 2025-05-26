// ------------------ MÁSCARAS ------------------
function MascaraCPF(cpfInput, event) {
  if (!mascaraInteiro(event)) {
    event.returnValue = false;
  }
  return formataCampo(cpfInput, '000.000.000-00', event);
}

function MascaraCNPJ(cnpjInput, event) {
  if (!mascaraInteiro(event)) {
    event.returnValue = false;
  }
  return formataCampo(cnpjInput, '00.000.000/0000-00', event);
}

function MascaraCep(cepInput, event) {
  if (!mascaraInteiro(event)) {
    event.returnValue = false;
  }
  return formataCampo(cepInput, '00.000-000', event);
}

function MascaraData(dataInput, event) {
  if (!mascaraInteiro(event)) {
    event.returnValue = false;
  }
  return formataCampo(dataInput, '00/00/0000', event);
}

function MascaraTelefone(telInput, event) {
  if (!mascaraInteiro(event)) {
    event.returnValue = false;
  }
  return formataCampo(telInput, '(00) 00000-0000', event);
}

function mascaraInteiro(event) {
  const key = event.keyCode || event.which;
  if (key < 48 || key > 57) {
    return false;
  }
  return true;
}

function formataCampo(campo, Mascara, evento) {
  var Digitato = evento.keyCode || evento.which;
  var exp = /\-|\.|\/|\(|\)| /g;
  var campoSoNumeros = campo.value.toString().replace(exp, "");
  var posicaoCampo = 0;
  var NovoValorCampo = "";
  var TamanhoMascara = campoSoNumeros.length;

  if (Digitato != 8) {
    for (var i = 0; i < Mascara.length; i++) {
      if (posicaoCampo >= TamanhoMascara) break;

      var cMascara = Mascara.charAt(i);
      if (["-", ".", "/", "(", ")", " "].includes(cMascara)) {
        NovoValorCampo += cMascara;
      } else {
        NovoValorCampo += campoSoNumeros.charAt(posicaoCampo);
        posicaoCampo++;
      }
    }
    campo.value = NovoValorCampo;
  }
  return true;
}

document.querySelectorAll('.required').forEach((input) => {
  input.addEventListener('input', () => {
    const type = input.dataset.type;
    const value = input.value.trim();

    if (value === "") {
      removeError(input);
      return;
    }

    switch (type.toLowerCase()) {
      case "nome":
      case "bairro":
      case "cidade":
      case "estado":
      case "país":
        !inputWithoutNumbers(value) ? setError(input) : removeError(input);
        break;

      case "e-mail":
      case "email":
        !isEmail(value) ? setError(input) : removeError(input);
        break;

      case "cnpj":
        !isCNPJ(value) ? setError(input) : removeError(input);
        break;

      case "cpf":
        !isCPF(value) ? setError(input) : removeError(input);
        break;

      case "data de nascimento":
      case "data":
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
        value !== (senhaInput ? senhaInput.value : "") ? setError(input) : removeError(input);
        break;

      case "cep":
        !isCEP(value) ? setError(input) : removeError(input);
        break;

      case "rua":
        !isRoad(value) ? setError(input) : removeError(input);
        break;

      case "número":
      case "numero":
        !isNum(value) ? setError(input) : removeError(input);
        break;

      default:
        removeError(input);
        break;
    }
  });
});

// ------------------ FUNÇÃO ------------------
// FUNÇÃO  QUE VERIFICA SE OS CAMPOS ESTÃO VAZIS E EXIBE O ALERTA CASO ESTAJAM
function btnRegisterOnClick(event, formElement) {
  console.log("Clique detectado");

  const naoSeiEndereco = formElement.querySelector("#nao_sei_endereco") || formElement.querySelector("#nao_sei_endereco_edit");
  const camposEndereco = ["rua", "bairro", "cidade", "estado"];

  const inputs = formElement.querySelectorAll('.required');
  let hasError = false;

  for (let input of inputs) {
    const type = input.dataset.type.toLowerCase();
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

// ------------------ REGEX ------------------
function hasRepeatedDigits(value) {
  return /^(\d)\1+$/.test(value);
}

function inputWithoutNumbers(value) {
  const re = /^[A-Za-zÀ-ÖØ-öø-ÿ\s]+$/;
  return re.test(value);
}

function isEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
  return re.test(email);
}

function isCNPJ(cnpj) {
  const re = /^(\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}|\d{14})$/;
  if (!re.test(cnpj)) return false;

  const onlyNumbers = cnpj.replace(/\D/g, '');
  if (hasRepeatedDigits(onlyNumbers)) return false;

  return true;
}

function isCPF(cpf) {
  const re = /^\d{3}\.?\d{3}\.?\d{3}-?\d{2}$/;
  if (!re.test(cpf)) return false;

  const onlyNumbers = cpf.replace(/\D/g, '');
  if (hasRepeatedDigits(onlyNumbers)) return false;

  return true;
}

function isBirthYear(date) {
  const re = /^(\d{2})\/(\d{2})\/(\d{4})$/;
  if (!re.test(date)) return false;

  const [day, month, year] = date.split('/').map(Number);
  if (year < 1925 || year >= 2012) return false;

  const daysInMonth = [31, 28 + (isLeapYear(year) ? 1 : 0), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
  return day > 0 && month > 0 && month <= 12 && day <= daysInMonth[month - 1];
}

function isLeapYear(year) {
  return (year % 4 === 0 && year % 100 !== 0) || (year % 400 === 0);
}

function isTelephone(tel) {
  const re = /^\(\d{2}\) \d{4,5}-\d{4}$/;
  if (!re.test(tel)) return false;

  const onlyNumbers = tel.replace(/\D/g, '');
  if (hasRepeatedDigits(onlyNumbers)) return false;

  return true;
}

function validPassword(pass) {
  return pass.length >= 6;
}

function isCEP(cep) {
  const re = /^(\d{5}-\d{3}|\d{8}|\d{2}\.\d{3}-\d{3})$/;
  if (!re.test(cep)) return false;

  const onlyNumbers = cep.replace(/\D/g, '');
  if (hasRepeatedDigits(onlyNumbers)) return false;

  return true;
}

function isRoad(rua) {
  const re = /^[A-Za-z0-9\s]+$/;
  return re.test(rua);
}

function isNum(num) {
  if (!/^\d+$/.test(num)) return false;
  if (hasRepeatedDigits(num)) return false;
  return true;
}

// ------------------ API DO CEP ------------------
function buscarEnderecoPorCEP() {
  const cep = document.getElementById('CEP').value.replace(/\D/g, '');

  if (cep.length === 8) {
    fetch(`https://viacep.com.br/ws/${cep}/json/`)
      .then(response => response.json())
      .then(data => {
        if (!data.erro) {
          document.getElementById('road').value = data.logradouro;
          document.getElementById('neighborhood').value = data.bairro;
          document.getElementById('city').value = data.localidade;
          document.getElementById('state').value = data.uf;
          document.getElementById('country').value = 'Brasil';
        } else {
          alert('CEP não encontrado.');
        }
      })
      .catch(() => alert('Erro ao buscar CEP.'));
  }
}