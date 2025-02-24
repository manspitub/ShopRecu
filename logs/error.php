<?php
$error_message = isset($_GET['error_message']) ? htmlspecialchars($_GET['error_message']) : "Ha ocurrido un error inesperado.";
require_once('../components/header.php');
?>
<main class="flex-grow-1 mb-3">

    <div class="error-container text-center">

        <h1>Error</h1>
        <div class="d-flex justify-content-center align-items-center">
            <img src="../assets/img/error.png" alt="Error" class="img-fluid">
        </div>

        <p><?= $error_message; ?></p>
        <a href="../index.php" class="btn edit-btn">Volver a inicio</a>
    </div>
</main>
<?php
require_once('../components/footer.php');
?>