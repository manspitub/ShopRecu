<?php
require_once('../components/header.php');
require_once('../components/functions.php');

// Obtener la acción solicitada (view, delete, edit, add) y el ID del producto
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';
$tabla = 'products'; // nombre de la tabla de la base de datos
$columnaId = 'id'; // columna que es la primary key de la tabla
$success_message = isset($_GET['success_message']) ? $_GET['success_message'] : '';
$error_message = isset($_GET['error_message']) ? $_GET['error_message'] : '';
$role = isset($_SESSION['userLogged']['role']) ? $_SESSION['userLogged']['role'] : '';



// Verificar si la acción es una de las permitidas
if (!in_array($action, ['view', 'delete', 'edit', 'add'])) {
    $error_message = "Acción no permitida.";
    // Redirigir a la página de error si la acción no es válida
    echo "<script>window.location.href = '../logs/error.php?error_message=" . urlencode($error_message) . "';</script>";
    exit; // Asegurarse de que no se ejecute más código
}

// Comprobar si se ha proporcionado un ID para las acciones que lo requieren
if (($action === 'view' || $action === 'delete' || $action === 'edit') && empty($id)) {
    $error_message = "ID del producto no proporcionado para la acción";
    // Redirigir a la página de error si no hay ID
    echo "<script>window.location.href = '../logs/error.php?error_message=" . urlencode($error_message) . "';</script>";
    exit;
}


unauthorized($action, $role, 'ADMIN');

// Si la acción es 'add', procesa la inserción
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $error_message = ''; // Inicializamos mensaje de error

    $datos = [
        'name' => trim($_POST['name'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'price' => $_POST['price'] ?? '',
        'stock' => $_POST['stock'] ?? '',
        'category_id' => $_POST['category_id'] ?? '',
    ];

    // Validar que los campos no estén vacíos ni contengan solo espacios
    if (empty($datos['name']) || empty($datos['description'])) {
        $error_message = "Los campos de nombre y descripción son obligatorios y no pueden estar vacíos.";
    }

    // Validar que el precio sea un número válido y no negativo
    elseif (!is_numeric($datos['price']) || $datos['price'] < 0) {
        $error_message = "El precio debe ser un número válido y no puede ser negativo.";
    }

    // Validar que el stock sea un número entero válido y no negativo
    elseif (!filter_var($datos['stock'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]])) {
        $error_message = "El stock debe ser un número entero válido y no puede ser negativo.";
    }

    // Validar que la categoría sea un número entero válido (opcional si las categorías son seleccionables)
    elseif (!filter_var($datos['category_id'], FILTER_VALIDATE_INT)) {
        $error_message = "La categoría seleccionada no es válida.";
    }

    // Validar que no exista un producto con el mismo nombre
    elseif (validateProductName($tabla, $datos['name'])) {
        $error_message = "El producto '" . htmlspecialchars($datos['name']) . "' ya existe.";
    }

    // Si no hay errores, insertar el producto
    if (empty($error_message)) {
        $result = insertProduct($tabla, $datos);
        
        if ($result) {
            $success_message = "Producto '" . htmlspecialchars($datos['name']) . "' añadido correctamente.";
            //echo "<script>window.location.href = '../products.php?success_message=" . urlencode($success_message) . "';</script>";
            //exit;
        } else {
            $error_message = "Error al añadir el producto.";
        }
    }

    
}



// Obtener el producto correspondiente al ID si la acción lo requiere
if ($action === 'view' || $action === 'delete' || $action === 'edit') {
    $producto = getById($tabla, $id, $columnaId);

    // Verificar si el producto existe
    if (!$producto) {
        $error_message = "Producto no encontrado.";
        // Redirigir a la página de error si el producto no se encuentra
        echo "<script>window.location.href = '../logs/error.php?error_message=" . urlencode($error_message) . "';</script>";
        exit;
    }
}

// Verificar si se ha enviado un formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Acción de editar un producto existente
    if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $error_message = ''; // Inicializamos mensaje de error
    
        $datos = [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'price' => $_POST['price'] ?? '',
            'stock' => $_POST['stock'] ?? '',
            'category_id' => $_POST['category_id'] ?? '',
        ];
    
        // Validar que los campos no estén vacíos ni contengan solo espacios
        if (empty($datos['name']) || empty($datos['description'])) {
            $error_message = "Los campos de nombre y descripción son obligatorios y no pueden estar vacíos.";
        }
    
        // Validar que el precio sea un número válido y mayor que 1
        elseif (!is_numeric($datos['price']) || $datos['price'] < 1) {
            $error_message = "El precio debe ser un número válido y mayor que 1.";
        }
    
        // Validar que el stock sea un número entero válido y mayor que 1
        elseif (!filter_var($datos['stock'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
            $error_message = "El stock debe ser un número entero válido y mayor que 1.";
        }
    
        // Validar que la categoría sea un número entero válido
        elseif (!filter_var($datos['category_id'], FILTER_VALIDATE_INT)) {
            $error_message = "La categoría seleccionada no es válida.";
        }
    
        // Validar que no exista otro producto con el mismo nombre (excepto si es el mismo producto)
        elseif (validateProductName($tabla, $datos['name'], $id)) {
            $error_message = "El producto '" . htmlspecialchars($datos['name']) . "' ya existe.";
        }
    
        // Si no hay errores, intentar actualizar el producto
        if (empty($error_message)) {
            if (updateProduct($tabla, $datos, $id, $columnaId)) {
                $success_message = "Producto actualizado correctamente.";
                //echo "<script>window.location.href = 'formProduct.php?action=view&id=$id&success_message=" . urlencode($success_message) . "';</script>";
                //exit;
            } else {
                $error_message = "Error al actualizar el producto.";
            }
        }
    }
    elseif ($action === 'delete') {
        if (isset($_POST['confirm_step'])) {
            if ($_POST['confirm_step'] == '2') {
                // Intentar eliminar el producto de la base de datos
        $success_message = "El producto " . htmlspecialchars($producto['name']) . "' ha sido eliminado correctamente.";
        if (deleteProduct($tabla, $id, $columnaId)) {
            // Redirigir a la lista de productos con un mensaje de éxito
            //echo "<script>window.location.href = '../products.php?success_message=" . urlencode($mensaje) . "';</script>";
        } else {
            $error_message = "Error al eliminar el producto.";
            // Redirigir a la página de error si falla la eliminación
            //echo "<script>window.location.href = '../logs/error.php?error_message=" . urlencode($error_message) . "';</script>";
        }
            }
        }
    }
}
// Obtener todas las categorías
$categories = getAllCategories();
$category_name = ($action === 'view' || $action === 'delete') ? getCategoryName($producto['category_id']) : '';
$producto['category_id'] = $producto['category_id'] ?? ''; // Si no existe, lo deja vacío


?>



    <!-- Mostrar el mensaje de error si está presente -->
    <?php if ($error_message != ''): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>
</div>
<div class="container py-5">
    <!-- Mostrar el mensaje de éxito si está presente -->
    <?php if ($success_message != ''): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>



<main class="flex-grow-1">
    <div class="container py-5">
        <?php if ($action === 'view' || $action === 'delete'): ?>
            <h2><?php echo ($action === 'view') ? 'Detalles del Producto' : 'Eliminar Producto'; ?></h2>
            <form method="post" action="formProduct.php?action=<?php echo $action; ?>&id=<?php echo $id; ?>">
                
                <!-- Mostrar detalles del producto (solo lectura) -->
                <div class="mb-3">
                    <label class="form-label">Id</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($producto['id']); ?>" readonly>
                </div>
                <div class="mb-3">
                    <label for="name" class="form-label">Nombre</label>
                    <input type="text" id="name" class="form-control" value="<?php echo htmlspecialchars($producto['name']); ?>" readonly>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Descripción</label>
                    <textarea id="description" class="form-control" readonly><?php echo htmlspecialchars($producto['description']); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="price" class="form-label">Precio</label>
                    <input type="text" id="price" class="form-control" value="<?php echo htmlspecialchars($producto['price']); ?>" readonly>
                </div>
                <div class="mb-3">
                    <label for="stock" class="form-label">Stock</label>
                    <input type="text" id="stock" class="form-control" value="<?php echo htmlspecialchars($producto['stock']); ?>" readonly>
                </div>
                <div class="mb-3">
                    <label for="category_id" class="form-label">Categoría</label>
                    <input type="text" id="category_id" class="form-control" value="<?php echo htmlspecialchars($category_name); ?>" readonly>
                </div>
                <?php if ($action === 'view'): ?>
    <a href="../products.php" class="btn btn-secondary">Volver</a>
<?php endif; ?>
                    
                <?php if ($action === 'delete'): ?>
    <form method="post">
        

        <?php if (empty($_POST['confirm_step'])): ?>
            <!-- Primera confirmación -->
            <input type="hidden" name="confirm_step" value="1">
            <button type="submit" class="btn btn-warning">¿Estás seguro de eliminar este Producto?</button>
        <?php elseif ($_POST['confirm_step'] == 1): ?>
            <!-- Segunda confirmación -->
            <input type="hidden" name="confirm_step" value="2">
            <button type="submit" class="btn btn-danger">Confirmar Eliminación</button>
        <?php endif; ?>
        <a href="../products.php" class="btn btn-secondary">Volver</a>
    </form>
<?php endif; ?>


        <?php elseif ($action === 'edit'): ?>
            <h2>Editar Producto</h2>
            <form method="post" action="formProduct.php?action=edit&id=<?php echo $id; ?>">
            <div class="mb-3">
                    <label class="form-label">Id</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($producto['id']); ?>" readonly>
                </div>
                <div class="mb-3">
                    <label for="name" class="form-label">Nombre</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($producto['name']); ?>" required pattern="/^[a-zA-Z0-9áéíóúÁÉÍÓÚüÜñÑ\s.,!?()]+$/">
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Descripción</label>
                    <textarea id="description" name="description" class="form-control" required pattern="/^[a-zA-Z0-9áéíóúÁÉÍÓÚüÜñÑ\s.,!?()]+$/"><?php echo htmlspecialchars($producto['description']); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="price" class="form-label">Precio</label>
                    <input type="text" id="price" name="price" class="form-control" value="<?php echo htmlspecialchars($producto['price']); ?>" required min="1">
                </div>
                <div class="mb-3">
                    <label for="stock" class="form-label">Stock</label>
                    <input type="text" id="stock" name="stock" class="form-control" value="<?php echo htmlspecialchars($producto['stock']); ?>" required min="1">
                </div>
                <div class="mb-3">
                    <label for="category_id" class="form-label">Categoría</label>
                    <select id="category_id" name="category_id" class="form-control" required>
                        <option value="">Selecciona una categoría</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" 
                                <?php echo (isset($producto['category_id']) && $producto['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                <a href="../products.php" class="btn btn-secondary">Cancelar</a>
            </form>
        <?php elseif ($action === 'add'): ?>
            <h2>Añadir Nuevo Producto</h2>
            <form method="post" action="formProduct.php?action=add">
                <div class="mb-3">
                    <label for="name" class="form-label">Nombre</label>
                    <input type="text" id="name" name="name" class="form-control" required pattern="/^[a-zA-Z0-9áéíóúÁÉÍÓÚüÜñÑ\s.,!?()]+$/">
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Descripción</label>
                    <textarea id="description" name="description" class="form-control" required pattern="/^[a-zA-Z0-9áéíóúÁÉÍÓÚüÜñÑ\s.,!?()]+$/"></textarea>
                </div>
                <div class="mb-3">
                    <label for="price" class="form-label">Precio</label>
                    <input type="text" id="price" name="price" class="form-control" required min="1">
                </div>
                <div class="mb-3">
                    <label for="stock" class="form-label">Stock</label>
                    <input type="text" id="stock" name="stock" class="form-control" required min="1">
                </div>
                <div class="mb-3">
                    <label for="category_id" class="form-label">Categoría</label>
                    <select id="category_id" name="category_id" class="form-control" required>
                        <option value="">Selecciona una categoría</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" 
                                <?php echo (isset($producto['category_id']) && $producto['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Añadir Producto</button>
                <a href="../products.php" class="btn btn-secondary">Cancelar</a>
            </form>
        <?php endif; ?>
    </div>
</main>

<?php
require_once('../components/footer.php');
?>

