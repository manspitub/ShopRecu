<?php require_once("functions.php");
// Genera la URL a partir de la ruta del directorio actual, subiendo un nivel
$url = str_replace($_SERVER['DOCUMENT_ROOT'], "", dirname(__DIR__));
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Jaca</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="shortcut icon" href="<?php echo $url; ?>/assets/img/shopIcon.png" type="image/x-icon">
</head>

<body>
    <header>
        <nav class="navbar navbar-expand-lg" aria-label="Main navigation">
            <div class="container">
                <!-- Logo de la tienda -->
                <a class="navbar-brand" href="<?php echo $url; ?>">
                    <img src="<?php echo $url; ?>/assets/img/shopIcon.png" alt="Logo de Shop Jaca" height="40">
                </a>

                <!-- Botón de menú móvil -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Menú de navegación -->
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <!-- Enlace directo a Inicio -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo '//' . $_SERVER['HTTP_HOST'] . $url; ?>">Inicio</a>
                        </li>
                        <!-- Dropdown de Categorías -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" id="navbarDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                Tienda
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo '//' . $_SERVER['HTTP_HOST'] . $url; ?>">Inicio</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo $url; ?>/products.php">Productos</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo $url; ?>/categories.php">Categorías</a>
                                </li>
                            </ul>
                        </li>

                        <?php if (!isset($_SESSION['userLogged'])): ?>
                            <!-- Usuario no autenticado -->
                            <li class="nav-item">
                                <a class="nav-link btn btn-secondary" href="<?php echo $url; ?>/user/login.php">Iniciar sesión</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link btn btn-secondary" href="<?php echo $url; ?>/user/register.php">Registrarse</a>
                            </li>
                        <?php else: ?>
                            <!-- Usuario autenticado -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <?php echo htmlspecialchars($_SESSION['userLogged']['username']); ?>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="userDropdown">
                                    <li>
                                        <a class="dropdown-item" href="<?php echo $url; ?>/user/profile.php?action=view">Perfil</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?php echo htmlspecialchars($url . '/index.php?logout=true&success_message=' . urlencode('Sesión cerrada exitosamente')); ?>">
                                            Cerrar sesión
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?php echo $url; ?>/myOrders.php">Ver mis compras</a>
                                    </li>
                                </ul>
                            </li>
                            <!-- Ícono de carrito de compras -->
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo $url; ?>/cart.php">
                                    <i class="bi bi-cart4 fs-3"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

</body>

</html>