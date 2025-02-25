<?php
require_once(__DIR__ . '/components/header.php');
require_once(__DIR__ . '/components/functions.php');

// Obtener todas las categorías de la base de datos
$categorias = getAll('categories'); // nombre de la tabla
if (!$categorias) {
    $categorias = [];
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
        <!-- Botón de añadir categoría -->
        <a href="../forms/formCategories.php?action=add" class="btn add-btn mb-3">
            Añadir Categoría
        </a>

        <?php if (count($categorias) > 0): ?>
            <!-- Tabla de categorías -->
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col">Nombre</th>
                            <th scope="col">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categorias as $categoria): ?>
                            <tr>
                                <td><?= htmlspecialchars($categoria['name']); ?></td>
                                <td>
                                    <a href="../forms/formCategories.php?action=view&id=<?= urlencode($categoria['id']); ?>" class="btn btn-primary btn-sm">Ver</a>
                                    <a href="../forms/formCategories.php?action=edit&id=<?= urlencode($categoria['id']); ?>" class="btn btn-warning btn-sm">Editar</a>
                                    <a href="../forms/formCategories.php?action=delete&id=<?= urlencode($categoria['id']); ?>" class="btn btn-danger btn-sm">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">No hay categorías registradas.</div>
        <?php endif; ?>
    </div>
</main>

<?php
require_once(__DIR__ . '/components/footer.php');
?>

