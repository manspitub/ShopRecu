<?php
ob_start();
require_once('../components/header.php');


// Funciones helper para controlar el estado de los campos
$action = $_GET['action'] ?? '';
// Determine if the user is an admin
$isAdmin = isset($_SESSION['userLogged']['role']) && $_SESSION['userLogged']['role'] == 'ADMIN';


// Set the username based on conditions
$username = ($isAdmin === false && !isset($_GET['user']))
    ? (isset($_SESSION['userLogged']['username']) ? $_SESSION['userLogged']['username'] : '')
    : (isset($_GET['user']) ? $_GET['user'] : $_SESSION['userLogged']['username']);

// Determine the role

    $user = getUserByUsername($username);
    $email = $user['email'];
    $phone_number = $user['phone_number'];
    $address = $user['address'];
    $full_name = $user['full_name'];
    // Otherwise, use the role from the session
    $role = $user['role'];

$tabla = 'users';
$columnaId = 'username';
$success_message = $_GET['success_message'] ?? '';

$userParam = isset($_GET['user']) ? '&user=' . urlencode($_GET['user']) : ''; // Si se recibe user recibo el parametro si no una cadena vacia

$allUsers = [];


if ($isAdmin) {
    $allUsers = getAll('users');
}

if (empty($username) or empty($role)) {
    redirectError("No estás logueado");
    exit;
}

if (!$isAdmin and isset($_GET['user'])) {
    redirectError("No tienes permiso para esto"); // Un usuario que no sea admin no puede acceder a datos de otros users
    exit;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Valida datos y edita según si quieres editar el usuario unicamente o el usuario y contraseña

    // $password = $_POST['password'] ?? '';  // Si no se pasa la contraseña, asignar un valor vacío
    // $passwordRep = $_POST['confirm_password'] ?? '';  // Si no se pasa la confirmación de la contraseña, asignar un valor vacío
    // $usernameToEdit = isset($_GET['user']) ? $_GET['user'] : trim($_SESSION['userLogged']['username']); // Si se recibe un user por get se edita ese user sino el usuario logueado
    $userToEdit = $_POST;
    // Llamar a la función pasando las contraseñas y el nombre de usuario
    handleUserPassword($userToEdit);
}



?>

<main class="flex-grow-1 mb-3">
    <div class="container py-5">

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>


        <h2>
            <?php
            echo ($action === 'view' || empty($action)) ? "Detalles de $username" : (($action === 'edit') ? "Editar $username" : "");
            ?>
        </h2>
        <form method="POST" action="">

            <?php if ($action === 'edit' || $action === 'view' || empty($action)): ?>
                <div class="mb-3">
                    <label for="username" class="form-label">Nombre de Usuario</label>
                    <input type="text" class="form-control" id="username" name="username"
                        value="<?php echo htmlspecialchars($username ?? ''); ?>" readonly>
                </div>

                <div class="mb-3">
                    <label for="username" class="form-label">Nombre Completo</label>
                    <input type="text" class="form-control" id="full_name" name="full_name"
                        value="<?php echo htmlspecialchars($full_name ?? ''); ?>" <?php echo $action === 'edit' ? '' : 'readonly'; ?>>
                </div>

                <div class="mb-3">
                    <label for="username" class="form-label">Dirección</label>
                    <input type="text" class="form-control" id="address" name="address"
                        value="<?php echo htmlspecialchars($address ?? ''); ?>" <?php echo $action === 'edit' ? '' : 'readonly'; ?>>
                </div>


                <div class="mb-3">
                    <label for="username" class="form-label">Email</label>
                    <input type="text" class="form-control" id="email" name="email"
                        value="<?php echo htmlspecialchars($email ?? ''); ?>" <?php echo $action === 'edit' ? '' : 'readonly'; ?>>
                </div>

                <div class="mb-3">
                    <label for="username" class="form-label">Teléfono</label>
                    <input type="text" class="form-control" id="phone_number" name="phone_number"
                        value="<?php echo htmlspecialchars($phone_number ?? ''); ?>" <?php echo $action === 'edit' ? '' : 'readonly'; ?>>
                </div>


                <div class="mb-3">
                    <label for="role" class="form-label">Rol</label>

                    <?php if ($action === 'edit' && $isAdmin): ?>
                        <!-- Si un admin esta editando, ya sea a si mismo o a un usuario podra cambiar el rol -->
                        <select class="form-control" id="role" name="role">
                            <option value="USER" <?= ($role == 'USER') ? 'selected' : ''; ?>>Usuario Normal</option>
                            <option value="ADMIN" <?= ($role == 'ADMIN') ? 'selected' : ''; ?>>Administrador</option>
                        </select>
                    <?php else: ?>
                        <input type="text" class="form-control" id="role" name="role"
                            value="<?php echo htmlspecialchars($role ?? ''); ?>" readonly>
                    <?php endif; ?>
                </div>

                <?php if ($action === 'edit'): ?>

                    <div class="mb-3">
                        <h2>Cambiar Contraseña</h2>
                    </div>


                    <div id="passwordFields">
                        <div class="mb-3">
                            <label for="oldPass" class="form-label">Contraseña Actual</label>
                            <input type="password" class="form-control" id="oldPass" name="oldPass" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Nueva Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Ingresa tu contraseña">
                            <div id="passwordHelper" class="mt-2 text-muted">
                                No introduzcas nada si no quieres cambiar tu contraseña.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirmar contraseña</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Repite tu contraseña">
                            <div id="passwordError" class="text-danger mt-2" style="display: none;">
                                Las contraseñas no coinciden.
                            </div>
                        </div>

                    </div>
                <?php endif ?>




            <?php endif; ?>

            <div class="d-flex justify-content-between">
                <!-- TODO BOTON EDITAR -->
                <?php if ($action === 'edit'): ?>
                    <a href="../" class="btn view-btn">Volver</a>
                    <button type="submit" class="btn save-btn">Guardar Cambios</button>
                <?php elseif ($action === 'view' || $action === ''): ?>
                    <a href="profile.php?action=edit<?= $userParam ?>" class="btn edit-btn">Editar Usuario</a>
                    <a href="../" class="btn view-btn">Volver</a>
                <?php endif; ?>
            </div>
        </form>
        <?php if ($isAdmin and !isset($_GET['user'])): ?>
            <?php if (count($allUsers) > 0): ?>
                <!-- Tabla de usuarios -->
                <div class="table-responsive my-4">
                    <table class="table table-striped table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">Nombre de usuario</th>
                                <th scope="col">Email</th>
                                <th scope="col">Nombre Completo</th>
                                <th scope="col">Telefono</th>
                                <th scope="col">Direccion</th>
                                <th scope="col">Roles</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allUsers as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['username']); ?></td>
                                    <td><?= htmlspecialchars($user['email']); ?></td>
                                    <td><?= htmlspecialchars($user['full_name']); ?></td>
                                    <td><?= htmlspecialchars($user['phone_number']); ?></td>
                                    <td><?= htmlspecialchars($user['address']); ?></td>
                                    <td><?= htmlspecialchars($user['role']); ?></td>
                                    <td>
                                        <!-- Botones de acciones -->
                                        <div class="d-flex flex-column flex-sm-row gap-2">
                                            <a href="./profile.php?action=view&user=<?= urlencode($user['username']); ?>"
                                                class="btn view-btn btn-sm">Ver Detalle Usuario</a>

                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">No se encontraron Usuarios.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>


</main>
<script src="script.js"></script>
<?php
require_once('../components/footer.php');
?>