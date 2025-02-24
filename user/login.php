<?php
require_once('../components/functions.php');
// Verificar si el parámetro 'success_message' está presente en la URL
$success_message = isset($_GET['success_message']) ? $_GET['success_message'] : '';
$error_message = isset($_GET['error_message']) ? $_GET['error_message'] : '';
$table = 'users';




if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['username'], $_POST['password'])) {
        $data = array('username' => $_POST['username'], 'password' => $_POST['password']);
        logIn($data);
    }
}

?>
<?php
require_once('../components/header.php');
?>

<div class="text-center">
    <!-- div para bajar el footer al fondo -->
    <div class="d-flex flex-column min-vh-100 text-center">
        <!-- Contenedor del contenido principal -->
        <div class="container my-5 flex-grow-1 d-flex justify-content-center align-items-center">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="card shadow-lg">
                        <div class="card-body">
                            <h3 class="text-center mb-4">Iniciar sesión</h3>

                            <!-- Mostrar el mensaje de éxito si está presente -->
                            <?php if (!empty($success_message)): ?>
                                <div class="alert alert-success text-center">
                                    <?php echo htmlspecialchars($success_message); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($error_message)): ?>
                                <div class="alert alert-danger text-center">
                                    <?php echo htmlspecialchars($error_message); ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Nombre de usuario</label>
                                    <input type="text" class="form-control" id="username" name="username"
                                        placeholder="Ingresa tu usuario" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Contraseña</label>
                                    <input type="password" class="form-control" id="password" name="password"
                                        placeholder="Ingresa tu contraseña" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Ingresar</button>
                            </form>
                            <p class="mt-3"><a href="register.php" class="text-decoration-none">¿No estás registrado? Regístrate aquí</a></p>
                        </div>
                    </div>
                    <a href="../index.php" class="btn mt-4">Volver a inicio</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once('../components/footer.php');
?>