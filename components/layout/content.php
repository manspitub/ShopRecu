<?php $success_message = isset($_GET['success_message']) ? $_GET['success_message'] : '';
if (isset($_GET['logout']) && $_GET['logout'] === 'true' && !empty($_SESSION['userLogged'])) {
    session_destroy();
    echo "<script>location.reload()</script>";
    return true;
}

?>

<div style="background-image: url('<?php echo $url; ?>/assets/img/shopBackground.jpeg'); background-size: cover; background-position: center; background-attachment: fixed; padding: 350px 0; color: black;">
    <div class="container">
        <?php if (!empty($success_message)) : ?>
            <div class="alert alert-success text-center">
                <?= htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        <div style="background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(5px); padding: 20px; border-radius: 8px; color: black;">
            <h1 class="text-center mb-4">Shop Jaca</h1>
            <p class="text-center mb-5">
                Bienvenidos a Shop Jacarandá, tu tienda de confianza para encontrar los mejores productos.
                Aquí descubrirás una amplia selección de artículos de calidad, ofertas exclusivas y todo lo que necesitas para tus compras.
            </p>
        </div>
    </div>
</div>