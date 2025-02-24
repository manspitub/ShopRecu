<?php
require_once('../components/functions.php');






// Verificar que username sea correcto y no esté en el array
// Verificar que la contraseñas sean iguales 
// Verificar que la contraseña siga el patrón adecuado

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Comprobamos si 'username', 'password' y 'confirm_password' están definidos
    if (isset($_POST['username'], $_POST['password'], $_POST['confirm_password'], $_POST['email'], $_POST['full_name'], $_POST['phone_number'], $_POST['address'])) {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $passwordRep = trim($_POST['confirm_password']);
        $email = trim($_POST['email']);
        $full_name = trim($_POST['full_name']);
        $phone_number = trim($_POST['phone_number']);
        $address = trim($_POST['address']);


        // Validamos que los campos no estén vacíos

        // Comprobamos que el nombre de usuario tenga entre 3 y 20 caracteres
        if (strlen($username) >= 3 && strlen($username) <= 20) {

            if (recordExists('users', 'username', $username)) {
                redirectError('El usuario ' . $username . ' ya existe');
            }


            // Validamos que la contraseña cumpla con los requisitos de longitud y seguridad
            if (validatePwd($password)) {
                // Verificamos que las contraseñas coincidan
                if ($password == $passwordRep) {
                    // Generamos el hash de la contraseña para almacenarla de manera segura
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);


                    $data = [ // Aquí usamos [] para agregar al array
                        'username' => $username,
                        'password' => $hashedPassword,
                        'role' => 'USER', // Por defecto, al registrarse tendrá rol básico
                        'email' => $email,
                        'full_name' => $full_name,
                        'phone_number' => $phone_number,
                        'address' => $address
                    ];


                    insertRecord('users', $data);

                    // Redirigir a una página de éxito o inicio de sesión
                    $message = 'El usuario ' . $username . ' ha sido añadido satisfactoriamente :)';
                    echo "<script>window.location.href = './login.php?success_message=" . urlencode($message) . "';</script>";
                    exit; // Detener la ejecución para evitar que el código siga ejecutándose
                } else {
                    redirectError('Las contraseñas no coinciden');
                }
            } else {
                redirectError('Contraseña débil: al menos 6 caracteres, una letra, un número y un carácter especial');
            }
        } else {
            redirectError('El nombre de usuario debe tener entre 3 y 20 caracteres');
        }
    } else {
        redirectError('Alguno de los campos no está definido');
    }
}

require_once('../components/header.php');
?>
<div class="text-center">
    <div class="d-flex flex-column min-vh-100 text-center">
        <div class="container my-5 flex-grow-1 d-flex justify-content-center align-items-center">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="card shadow-lg">
                        <div class="card-body">
                            <h3 class="text-center mb-4">Registrarse</h3>
                            <form id="registrationForm" action="" method="POST">
                                <!-- Nombre de usuario -->
                                <div class="mb-3">
                                    <label for="username" class="form-label">Nombre de usuario</label>
                                    <input minlength="3" maxlength="20" type="text" class="form-control" id="username"
                                        name="username" placeholder="Ingresa tu usuario" required>
                                </div>

                                <!-- Correo electrónico -->
                                <div class="mb-3">
                                    <label for="email" class="form-label">Correo electrónico</label>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="Ingresa tu correo electrónico" required>
                                </div>

                                <!-- Nombre completo -->
                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Nombre completo</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" placeholder="Ingresa tu nombre completo" required>
                                </div>

                                <!-- Número de teléfono -->
                                <div class="mb-3">
                                    <label for="phone_number" class="form-label">Número de teléfono</label>
                                    <input type="text" class="form-control" id="phone_number" name="phone_number" placeholder="Ingresa tu número de teléfono" required>
                                </div>

                                <!-- Dirección -->
                                <div class="mb-3">
                                    <label for="address" class="form-label">Dirección</label>
                                    <input type="text" class="form-control" id="address" name="address" placeholder="Ingresa tu dirección" required>
                                </div>

                                <!-- Contraseña -->
                                <div class="mb-3">
                                    <label for="password" class="form-label">Contraseña</label>
                                    <input type="password" class="form-control" id="password" name="password"
                                        placeholder="Ingresa tu contraseña" required>
                                    <div id="passwordHelper" class="mt-2"></div>
                                </div>

                                <!-- Confirmar contraseña -->
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirmar contraseña</label>
                                    <input type="password" class="form-control" id="confirm_password"
                                        name="confirm_password" placeholder="Repite tu contraseña" required>
                                    <div id="passwordError" class="text-danger mt-2" style="display: none;">
                                        Las contraseñas no coinciden.
                                    </div>
                                </div>

                                <!-- Botón de registro -->
                                <button type="submit" class="btn btn-primary w-100" id="registerButton" disabled>Registrarse</button>
                            </form>

                            <!-- Enlace para iniciar sesión -->
                            <p class="mt-3"><a href="login.php" class="text-decoration-none">¿Ya tienes una cuenta? Inicia sesión</a></p>
                        </div>
                    </div>

                    <!-- Enlace para volver al inicio mejorado -->
                    <a href="../index.php" class="btn btn-primary mt-4">Volver a inicio</a>

                </div>
            </div>
        </div>
    </div>
</div>


<script src="script.js"></script>

<?php
require_once('../components/footer.php');
?>