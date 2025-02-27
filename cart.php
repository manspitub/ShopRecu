<?php

require_once(__DIR__ . '/components/header.php');
require_once(__DIR__ . '/components/functions.php');

$action = $_GET['action'] ?? '';
$role = isset($_SESSION['userLogged']['role']) ? $_SESSION['userLogged']['role'] : '';
$table = 'order';

unauthorized($action, $role, 'USER');

// Función para añadir un producto al carrito en sesión
function addProductToCart($productId, $quantity, $totalPrice, $status)
{
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Si el producto ya está en el carrito, suma la cantidad
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity'] += $quantity;
        $_SESSION['cart'][$productId]['total_price'] += $totalPrice;
    } else {
        $_SESSION['cart'][$productId] = [
            'productId'   => $productId,
            'productName' => getProductNameById($productId)['name'],
            'quantity'    => $quantity,
            'total_price' => $totalPrice,
            'status'      => $status
        ];
    }
}

// Procesar la acción de añadir producto solo si se envía por POST con la acción 'add'
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'add') {
    // Recoger los datos del formulario
    $productId = $_POST['product_id'] ?? null;
    $quantity  = $_POST['quantity'] ?? 1;
    $price     = $_POST['price'] ?? 0;

    // Validar los datos recibidos
    if ($productId && is_numeric($quantity) && is_numeric($price)) {
        $quantity = (int)$quantity;
        $price = (float)$price;
        $totalPrice = $quantity * $price;
        $status = 'pending';
        // Llamar a la función para agregar el producto al carrito en sesión
        addProductToCart($productId, $quantity, $totalPrice, $status);
    } else {
        // Redirigir a la página de error mostrando mensaje de error
        redirectError('Hubo un error al validar los datos del producto a añadir');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'clear_cart') {
    unset($_SESSION['cart']); // Borra todos los productos del carrito
}


// Manejar la eliminación de un producto del carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove_product') {
    $productIdToRemove = $_POST['product_id'] ?? null;

    if ($productIdToRemove && isset($_SESSION['cart'][$productIdToRemove])) {
        // Eliminar el producto del carrito
        unset($_SESSION['cart'][$productIdToRemove]);
        echo "<script>window.location.href = 'cart.php';</script>"; // Redirigir de nuevo al carrito
    }
}


$cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
?>
<main class="flex-grow-1">
    <div class="container mt-5">
        <h1 class="mb-4">Carrito de Compras</h1>

        <?php if (empty($cartItems)): ?>
            <div class="alert alert-info" role="alert">
                No hay productos en el carrito.
            </div>
        <?php else: ?>
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Precio Total</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $totalPrice = 0;
                    foreach ($cartItems as $productId => $product):
                        $totalPrice += $product['total_price'];
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($product['productName']); ?></td>
                            <td><?= htmlspecialchars($product['quantity']); ?></td>
                            <td>
                                <?php
                                $unitPrice = $product['quantity'] > 0
                                    ? $product['total_price'] / $product['quantity']
                                    : 0;
                                echo '$' . number_format($unitPrice, 2);
                                ?>
                            </td>
                            <td><?= '$' . number_format($product['total_price'], 2); ?></td>
                            <td>
                                <form action="cart.php" method="POST" style="display:inline-block;">
                                    <input type="hidden" name="action" value="remove_product">
                                    <input type="hidden" name="product_id" value="<?= $productId; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <!-- Mostrar el precio total -->
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                        <td><strong>$<?= number_format($totalPrice, 2); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
            <div class="d-flex justify-content-between">
                <a href="../products.php" class="btn btn-secondary">Seguir Comprando</a>
                <!-- Botón para abrir el modal de confirmación de compra -->
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#checkoutModal">
                    Proceder al Pago
                </button>
                <form action="cart.php" method="POST">
                    <input type="hidden" name="action" value="clear_cart">
                    <button type="submit" class="btn btn-danger">Vaciar Carrito</button>
                </form>
            </div>

            <!-- Modal de Confirmación de Compra -->
            <div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="checkoutModalLabel">Confirmar Compra</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <h6>Resumen de tu compra:</h6>
                            <ul id="cartSummary" class="list-group mb-3">
                                <!-- Aquí se insertarán los productos con JavaScript -->
                            </ul>
                            <p><strong>Total a pagar:</strong> <span id="totalPrice"></span> €</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <form action="checkout.php" method="POST">
                                <button type="submit" class="btn btn-success">Confirmar y Pagar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    let cartData = <?php echo isset($_SESSION['cart']) ? json_encode($_SESSION['cart']) : '{}'; ?>;
                    let cartSummary = document.getElementById("cartSummary");
                    let totalPriceElement = document.getElementById("totalPrice");

                    if (!cartData || Object.keys(cartData).length === 0) {
                        cartSummary.innerHTML = "<li class='list-group-item text-center'>Tu carrito está vacío.</li>";
                        totalPriceElement.innerText = "0.00 €";
                        return;
                    }

                    let total = 0;
                    cartSummary.innerHTML = "";

                    Object.values(cartData).forEach(product => {
                        let productName = product.productName || "Producto desconocido";
                        let quantity = product.quantity;

                        let item = document.createElement("li");
                        item.className = "list-group-item";
                        item.innerHTML = `<strong>${productName}</strong> - ${quantity} x ${(product.total_price / quantity).toFixed(2)}€ = <strong>${product.total_price.toFixed(2)}€</strong>`;
                        cartSummary.appendChild(item);

                        total += product.total_price;
                    });

                    totalPriceElement.innerText = total.toFixed(2);
                });
            </script>






        <?php endif; ?>
    </div>
</main>
<?php
require_once(__DIR__ . '/components/footer.php');
