<?php
require_once(__DIR__ . '/components/header.php');
require_once(__DIR__ . '/components/functions.php');

// Obtener todos los productos de la base de datos
$productos = getAll('products'); // nombre de la tabla
if (!$productos) {
    $productos = [];
}
$success_message = isset($_GET['success_message']) ? $_GET['success_message'] : '';

?>

<div class="container py-5">
    <?php if ($success_message): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>
</div>

<main class="flex-grow-1">
    <div class="container py-5">
        <!-- Botón de añadir producto -->
        <a href="../forms/formProduct.php?action=add" class="btn add-btn mb-3">
            Añadir Producto
        </a>

        <?php if (count($productos) > 0): ?>
            <!-- Tabla de productos -->
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col">Nombre</th>
                            <th scope="col">Precio</th>
                            <th scope="col">Categoría</th>
                            <th scope="col">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $producto): ?>
                            <?php
                            // Obtener el nombre de la categoría
                            $category_name = getCategoryName($producto['category_id']);
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($producto['name']); ?></td>
                                <td><?= htmlspecialchars(number_format($producto['price'], 2)); ?> €</td>
                                <td><?= htmlspecialchars($category_name); ?></td>
                                <td>
                                    <a href="../forms/formProduct.php?action=view&id=<?= urlencode($producto['id']); ?>" class="btn btn-primary btn-sm">Ver</a>
                                    <?php if (isset($_SESSION['userLogged']['role']) && $_SESSION['userLogged']['role'] === 'ADMIN'): ?>
                                        <a href="../forms/formProduct.php?action=edit&id=<?= urlencode($producto['id']); ?>" class="btn btn-warning btn-sm">Editar</a>
                                        <a href="../forms/formProduct.php?action=delete&id=<?= urlencode($producto['id']); ?>" class="btn btn-danger btn-sm">Eliminar</a>
                                    <?php endif; ?>


                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">No hay productos registrados.</div>
        <?php endif; ?>
    </div>
</main>

<?php
require_once(__DIR__ . '/components/footer.php');
?>