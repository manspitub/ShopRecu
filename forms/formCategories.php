<?php
require_once('../components/header.php');
require_once('../components/functions.php');

// Obtener la acción solicitada (view, delete, edit, add) y el ID de la categoría
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';
$tabla = 'categories'; // nombre de la tabla de la base de datos
$columnaId = 'id'; // columna que es la primary key de la tabla
$success_message = isset($_GET['success_message']) ? $_GET['success_message'] : '';
$error_message = isset($_GET['error_message']) ? $_GET['error_message'] : '';

if (!in_array($action, ['view', 'delete', 'edit', 'add'])) {
    $error_message = "Acción no permitida.";
    echo "<script>window.location.href = '../logs/error.php?error_message=" . urlencode($error_message) . "';</script>";
    exit;
}

if (($action === 'view' || $action === 'delete' || $action === 'edit') && empty($id)) {
    $error_message = "ID de la categoría no proporcionado.";
    echo "<script>window.location.href = '../logs/error.php?error_message=" . urlencode($error_message) . "';</script>";
    exit;
}
// ADD
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'name' => trim($_POST['name']),
        'description' => trim($_POST['description']),
    ];

    // Validar que los campos no estén vacíos
    if (empty($datos['name']) || empty($datos['description']) || strlen(trim($datos['name'])) === 0 || strlen(trim($datos['description'])) === 0) {
        $error_message = "Los campos de nombre y descripción son obligatorios y no pueden estar vacíos ni contener solo espacios.";
    }

    // Validar que el nombre no contenga números o caracteres especiales
    if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s]+$/', $datos['name'])) {
        $error_message = "El nombre solo puede contener letras y espacios.";
    }

    // Validar que la descripción no contenga caracteres no permitidos
    if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s]+$/', $datos['description'])) {
        $error_message = "La descripción contiene caracteres no permitidos.";
    }

    // Validar que no exista una categoría con el mismo nombre
    $existingCategory = validateCategories($tabla, $_POST['name'], $id);
    if ($existingCategory) {
        $error_message = "La categoría '" . htmlspecialchars($datos['name']) . "' ya existe.";
    }

    // Si no hay errores, insertar la categoría
    if (empty($error_message)) {
        if (insertCategory($tabla, $datos)) {
            $success_message = "Categoría '" . htmlspecialchars($datos['name']) . "' añadida correctamente.";
        } else {
            $error_message = "Error al añadir la categoría.";
        }
    }
}



if ($action === 'view' || $action === 'delete' || $action === 'edit') {
    $categoria = getById($tabla, $id, $columnaId);
    if (!$categoria) {
        echo "<script>window.location.href = '../logs/error.php?error_message=Categoría no encontrada';</script>";
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'edit') {
        $datos = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
        ];

        // Validar que no exista una categoría con el mismo nombre
        $existingCategory = validateCategories($tabla, $_POST['name'], $id);
        if ($existingCategory) {
            $error_message = "La categoría '" . htmlspecialchars($datos['name']) . "' ya existe.";
        }

        if (empty($error_message)) {
            if (updateCategory($tabla, $datos, $id, $columnaId)) {
                $success_message = "Categoría actualizada correctamente.";
                $categoria = getById($tabla, $id, $columnaId);
            } else {
                $error_message = "Error al actualizar la categoría.";
            }
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete') {
        if (isset($_POST['confirm_step'])) {
            if ($_POST['confirm_step'] == '2') {
                // Segunda confirmación: Proceder a eliminar
                if (deleteCategory($tabla, $id, $columnaId)) {
                    $success_message = "Categoría eliminada correctamente.";
                    // Se mantiene $categoria sin limpiar para que los datos sigan visibles
                } else {
                    $error_message = "Error al eliminar la categoría.";
                }
            }
        }
    }
}

?>

<div class="container py-5">
    <!-- Mostrar el mensaje de éxito si está presente -->
    <?php if ($success_message != ''): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <!-- Mostrar el mensaje de error si está presente -->
    <?php if ($error_message != ''): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>
</div>





<main class="flex-grow-1">
    <div class="container py-5">
        <?php if ($action === 'view' || $action === 'delete'): ?>
            <h2><?php echo ($action === 'view') ? 'Detalles de la Categoría' : 'Confirmar Eliminación'; ?></h2>
            <form method="post">
            <div class="mb-3">
                    <label class="form-label">Id</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($categoria['id']); ?>" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($categoria['name']); ?>" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea class="form-control" readonly><?php echo htmlspecialchars($categoria['description']); ?></textarea>
                </div>
                <?php if ($action === 'view'): ?>
                <!-- Botón de volver solo para la acción 'view' -->
                <a href="../categories.php" class="btn btn-secondary">Volver</a>
            <?php endif; ?>
                
                <?php if ($action === 'delete'): ?>
    <form method="post">
        

        <?php if (empty($_POST['confirm_step'])): ?>
            <!-- Primera confirmación -->
            <input type="hidden" name="confirm_step" value="1">
            <button type="submit" class="btn btn-warning">¿Estás seguro de eliminar esta categoría?</button>
        <?php elseif ($_POST['confirm_step'] == 1): ?>
            <!-- Segunda confirmación -->
            <input type="hidden" name="confirm_step" value="2">
            <button type="submit" class="btn btn-danger">Confirmar Eliminación</button>
        <?php endif; ?>
        <a href="../categories.php" class="btn btn-secondary">Volver</a>
    </form>
<?php endif; ?>

        <?php elseif ($action === 'edit' || $action === 'add'): ?>
            <h2><?php echo ($action === 'edit') ? 'Editar Categoría' : 'Añadir Categoría'; ?></h2>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="name" class="form-control" value="<?php echo $action === 'edit' ? htmlspecialchars($categoria['name']) : ''; ?>" required pattern="[a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s]+" title="Solo se permiten letras y espacios">
                </div>
                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <input type="text" name="description" class="form-control" value="<?php echo $action === 'edit' ? htmlspecialchars($categoria['description']) : ''; ?>" required pattern="[a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s]+" title="Solo se permiten letras y espacios">
                </div>
                <button type="submit" class="btn btn-primary">Guardar</button>
                <a href="../categories.php" class="btn btn-secondary">Cancelar</a>
            </form>
        <?php endif; ?>
    </div>
</main>


<?php
require_once('../components/footer.php');
?>