<?php $success_message = isset($_GET['success_message']) ? $_GET['success_message'] : '';
if (isset($_GET['logout']) && $_GET['logout'] === 'true' && !empty($_SESSION['userLogged'])) {
    session_destroy();
    echo "<script>location.reload()</script>";
    return true;
}

?>

<div class="background-section">
    <div class="container">
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success text-center">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        <h1 class="text-center mb-4">Shop Jaca</h1>
        <p class="text-center mb-5">
            Bienvenidos a Shop Jacarandá, tu tienda de confianza para encontrar los mejores productos.
            Aquí descubrirás una amplia selección de artículos de calidad, ofertas exclusivas y todo lo que necesitas para tus compras.
        </p>
    </div>
</div>