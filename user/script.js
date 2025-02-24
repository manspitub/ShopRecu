document.getElementById("password").addEventListener("keyup", function () {
  const password = this.value;
  const passwordHelper = document.getElementById("passwordHelper");
  const registerButton = document.getElementById("registerButton");

  // Limpiar cualquier mensaje previo antes de mostrar los nuevos errores
  passwordHelper.innerHTML = "";

  // Inicializamos un array vacío para almacenar los mensajes de error
  let errorMessages = [];

  // Verificamos cada requisito y agregamos el mensaje correspondiente al array de errores
  if (!/(?=.*[a-zA-Z])/.test(password)) {
    errorMessages.push("La contraseña debe incluir al menos una letra.");
  }
  if (!/(?=.*\d)/.test(password)) {
    errorMessages.push("La contraseña debe incluir al menos un número.");
  }
  if (!/(?=.*[!*?¿])/.test(password)) {
    errorMessages.push(
      "La contraseña debe incluir al menos un carácter especial (*?¿!)."
    );
  }
  if (password.length < 6) {
    errorMessages.push("La contraseña debe tener al menos 6 caracteres.");
  }

  // Si no hay errores, el mensaje es verde y se indica que la contraseña cumple los requisitos
  if (errorMessages.length === 0) {
    passwordHelper.classList.remove("text-danger");
    passwordHelper.classList.add("text-success");
    passwordHelper.textContent =
      "La contraseña cumple con todos los requisitos de seguridad.";
  } else {
    // Crear un elemento <ul> para listar los errores
    const ulDiv = document.createElement("ul");
    passwordHelper.classList.remove("text-success");
    passwordHelper.classList.add("text-danger");
    // Añadimos cada mensaje de error a la lista <ul>
    errorMessages.forEach((e) => {
      const liError = document.createElement("li");
      liError.textContent = e;
      ulDiv.appendChild(liError);
    });

    // Añadimos la lista <ul> al contenedor de mensajes
    passwordHelper.appendChild(ulDiv);
  }

  // Comprobamos si las contraseñas son válidas
  enableRegisterButton();
});

document
  .getElementById("confirm_password")
  .addEventListener("keyup", function () {
    const password = document.getElementById("password").value;
    const confirmPassword = document.getElementById("confirm_password").value;
    const passwordError = document.getElementById("passwordError");
    const registerButton = document.getElementById("registerButton");

    if (password !== confirmPassword) {
      passwordError.style.display = "block"; // Mostrar el mensaje de error si las contraseñas no coinciden
    } else {
      passwordError.style.display = "none"; // Ocultar el mensaje de error si coinciden
    }

    // Comprobamos si las contraseñas son válidas
    enableRegisterButton();
  });

// Función para habilitar/deshabilitar el botón de registro
function enableRegisterButton() {
  const username = document.getElementById("username").value;
  const email = document.getElementById("email").value;
  const fullName = document.getElementById("full_name").value;
  const phoneNumber = document.getElementById("phone_number").value;
  const address = document.getElementById("address").value;
  const password = document.getElementById("password").value;
  const confirmPassword = document.getElementById("confirm_password").value;

  const passwordHelper = document.getElementById("passwordHelper");
  const passwordError = document.getElementById("passwordError");
  const registerButton = document.getElementById("registerButton");

  // Validación de los campos (los campos no deben estar vacíos)
  const fieldsValid = username && email && fullName && phoneNumber && address;

  // Verificación de la validez de la contraseña y que coincidan las contraseñas
  const passwordValid = passwordHelper.classList.contains("text-success");
  const passwordsMatch = password === confirmPassword;


  console.log(fieldsValid);
  // Si todos los campos son válidos, las contraseñas son válidas y coinciden, habilitamos el botón
  if (fieldsValid && passwordValid && passwordsMatch) {
    registerButton.disabled = false; // Habilitar el botón
  } else {
    registerButton.disabled = true; // Deshabilitar el botón
  }
}

// Llamamos a la función de habilitar/deshabilitar el botón en cada evento clave
document
  .getElementById("username")
  .addEventListener("keyup", enableRegisterButton);
document
  .getElementById("email")
  .addEventListener("keyup", enableRegisterButton);
document
  .getElementById("full_name")
  .addEventListener("keyup", enableRegisterButton);
document
  .getElementById("phone_number")
  .addEventListener("keyup", enableRegisterButton);
document
  .getElementById("address")
  .addEventListener("keyup", enableRegisterButton);
document
  .getElementById("password")
  .addEventListener("keyup", enableRegisterButton);
document
  .getElementById("confirm_password")
  .addEventListener("keyup", enableRegisterButton);

// Inicializamos el estado del botón (deshabilitado por defecto)
document.addEventListener("DOMContentLoaded", function () {
  document.getElementById("registerButton").disabled = true;
});
