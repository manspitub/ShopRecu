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
        <?php if (isset($_SESSION['userLogged']['role']) && $_SESSION['userLogged']['role'] === 'ADMIN'): ?>
            <a href="../forms/formProduct.php?action=add" class="btn add-btn mb-3">
                Añadir Producto
            </a>
        <?php endif; ?>

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
                            <?php if ($producto['stock'] > 0): ?>

                                <tr>
                                    <td><?= htmlspecialchars($producto['name']); ?></td>
                                    <td><?= htmlspecialchars(number_format($producto['price'], 2)); ?> €</td>
                                    <td><?= htmlspecialchars($category_name); ?></td>
                                    <td>
                                        <a href="../forms/formProduct.php?action=view&id=<?= urlencode($producto['id']); ?>" class="btn btn-primary btn-sm">Ver</a>
                                        <?php if (isset($_SESSION['userLogged']['role']) && $_SESSION['userLogged']['role'] === 'ADMIN'): ?>
                                            <a href="../forms/formProduct.php?action=edit&id=<?= urlencode($producto['id']); ?>" class="btn btn-warning btn-sm">Editar</a>
                                            <a href="../forms/formProduct.php?action=delete&id=<?= urlencode($producto['id']); ?>" class="btn btn-danger btn-sm">Eliminar</a>
                                            Los usuarios con rol de usuario podrán comprar productos
                                        <?php else: ?>
                                            <!-- Botón que activa el modal -->
                                            <a href="#" class="btn add-btn btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#quantityModal"
                                                data-product-id="<?= urlencode($producto['id']); ?>"
                                                data-product-name="<?= htmlspecialchars($producto['name'] ?? 'Producto sin nombre'); ?>"
                                                data-product-stock="<?= htmlspecialchars($producto['stock'] ?? 0); ?>"
                                                data-product-category="<?= htmlspecialchars($category_name ?? 'Sin categoría'); ?>"
                                                data-product-price="<?= htmlspecialchars($producto['price'] ?? '0.00'); ?>">
                                                Comprar <i class="bi bi-cart-plus-fill"></i>
                                            </a>


                                            <!-- Modal para seleccionar la cantidad y mostrar información del producto -->
                                            <div class="modal fade" id="quantityModal" tabindex="-1" aria-labelledby="quantityModalLabel" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <!-- El formulario envía los datos a cart.php con action=add -->
                                                    <form action="<?= $url; ?>/cart.php?action=add" method="post">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="quantityModalLabel">Añadir producto al carrito</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <!-- Información del producto -->
                                                                <p><strong>Producto:</strong> <span id="modalProductName"></span></p>
                                                                <p><strong>Categoría:</strong> <span id="modalProductCategory"></span></p>
                                                                <p><strong>Precio Unitario:</strong> <span id="modalProductPriceText"></span></p>
                                                                <p><strong>Quedan:</strong> <span id="modalProductStock"></span></p>
                                                                <!-- Campo oculto para el ID del producto -->
                                                                <input type="hidden" name="product_id" id="modalProductId">
                                                                <input type="hidden" name="price" id="modalProductPrice">
                                                                <!-- Selección de cantidad -->
                                                                <div class="mb-3">
                                                                    <!-- Input para la cantidad -->
                                                                    <label for="quantity">Cantidad:</label>
                                                                    <input type="number" id="quantity" name="quantity" class="form-control" min="1" required>
                                                                    <input type="hidden" id="availableStock" value="<?php echo htmlspecialchars($producto['stock']); ?>">
                                                                    <!-- Mensaje de error -->
                                                                    <small id="stock-error" class="text-danger d-none"></small>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                <!-- Botón de agregar al carrito -->
                                                                <button id="add-to-cart-btn" class="btn btn-primary" disabled>Añadir al carrito</button>
                                                                <script>
                                                                    document.addEventListener("DOMContentLoaded", function() {
                                                                        var quantityModal = document.getElementById("quantityModal");
                                                                        var productStock = 0; // Variable global para almacenar el stock

                                                                        quantityModal.addEventListener("show.bs.modal", function(event) {
                                                                            var button = event.relatedTarget;
                                                                            var productId = button.getAttribute("data-product-id");
                                                                            var productName = button.getAttribute("data-product-name");
                                                                            var productCategory = button.getAttribute("data-product-category");
                                                                            productStock = parseInt(button.getAttribute("data-product-stock")); // Guardar el stock

                                                                            var productPrice = button.getAttribute("data-product-price");

                                                                            // Actualiza los campos del modal
                                                                            quantityModal.querySelector("#modalProductId").value = productId;
                                                                            quantityModal.querySelector("#modalProductName").textContent = productName;
                                                                            quantityModal.querySelector("#modalProductCategory").textContent = productCategory;
                                                                            quantityModal.querySelector("#modalProductStock").textContent = productStock;
                                                                            quantityModal.querySelector("#modalProductPrice").value = productPrice;
                                                                            quantityModal.querySelector("#modalProductPriceText").textContent = productPrice;
                                                                        });

                                                                        const quantityInput = document.getElementById("quantity");
                                                                        const stockError = document.getElementById("stock-error");
                                                                        const addToCartBtn = document.getElementById("add-to-cart-btn");

                                                                        quantityInput.addEventListener("keyup", function() {
                                                                            let quantity = parseInt(quantityInput.value);

                                                                            if (isNaN(quantity) || quantity <= 0) {
                                                                                stockError.textContent = "Ingrese un número válido.";
                                                                                stockError.classList.remove("d-none");
                                                                                addToCartBtn.disabled = true;
                                                                                return;
                                                                            }

                                                                            if (quantity > productStock) { // Comparación con el stock real del producto
                                                                                stockError.textContent = `Solo hay ${productStock} unidades en stock, pero intentas comprar ${quantity}.`;
                                                                                stockError.classList.remove("d-none");
                                                                                addToCartBtn.disabled = true;
                                                                            } else {
                                                                                stockError.classList.add("d-none");
                                                                                addToCartBtn.disabled = false;
                                                                            }
                                                                        });
                                                                    });
                                                                </script>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>

                                            <!-- Script para cargar los datos del producto en el modal -->
                                            <script>
                                                var quantityModal = document.getElementById('quantityModal');
                                                quantityModal.addEventListener('show.bs.modal', function(event) {
                                                    var button = event.relatedTarget;
                                                    var productId = button.getAttribute('data-product-id');
                                                    var productName = button.getAttribute('data-product-name');
                                                    var productCategory = button.getAttribute('data-product-category');
                                                    var productStock = button.getAttribute('data-product-stock');
                                                    var productPrice = button.getAttribute('data-product-price');

                                                    // Actualiza los campos del modal
                                                    quantityModal.querySelector('#modalProductId').value = productId;
                                                    quantityModal.querySelector('#modalProductName').textContent = productName;
                                                    quantityModal.querySelector('#modalProductCategory').textContent = productCategory;
                                                    quantityModal.querySelector('#modalProductStock').textContent = productStock;
                                                    quantityModal.querySelector('#modalProductPrice').value = productPrice;
                                                    quantityModal.querySelector('#modalProductPriceText').textContent = productPrice;

                                                });
                                            </script>
                                        <?php endif; ?>


                                    </td>
                                </tr>
                            <?php endif; ?>
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