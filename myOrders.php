<?php

require_once(__DIR__ . '/components/header.php');
require_once(__DIR__ . '/components/functions.php');

$orders = getPurchaseHistory();
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
    <div class="container mt-5">
        <h1 class="mb-4">Historial de Compras</h1>

        <?php if (empty($orders)): ?>
            <div class="alert alert-info" role="alert">
                No has realizado ninguna compra aún.
            </div>
        <?php else: ?>
            <div class="row">
                <?php
                // Agrupar los pedidos por ID de orden (para que cada pedido tenga su propia card)
                $groupedOrders = [];
                foreach ($orders as $order) {
                    $groupedOrders[$order['order_id']][] = $order;
                }

                // Mostrar los pedidos en cards
                foreach ($groupedOrders as $orderId => $orderDetails):
                    $totalPrice = 0;
                    foreach ($orderDetails as $item) {
                        $totalPrice += $item['quantity'] * $item['price'];
                    }
                ?>
                    <div class="col-md-4 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title">Pedido ID: <?= htmlspecialchars($orderId); ?></h5>
                            </div>
                            <div class="card-body">
                                <h6 class="card-subtitle mb-3 text-muted">Productos:</h6>
                                <ul class="list-group">
                                    <?php foreach ($orderDetails as $item): ?>
                                        <li class="list-group-item">
                                            <strong><?= htmlspecialchars($item['product_name']); ?></strong>
                                            - <?= htmlspecialchars($item['quantity']); ?> x
                                            $<?= number_format($item['price'], 2); ?>
                                            = $<?= number_format($item['quantity'] * $item['price'], 2); ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="card-footer text-muted">
                                <strong>Total: $<?= number_format($totalPrice, 2); ?></strong>
                            </div>
                            <div class="card-footer">
                                <span class="badge bg-<?= $orderDetails[0]['status'] == 'completed' ? 'success' : ($orderDetails[0]['status'] == 'pending' ? 'warning' : 'danger'); ?>">
                                    <?= ucfirst(htmlspecialchars($orderDetails[0]['status'])); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>
<?php
require_once(__DIR__ . '/components/footer.php');
?>