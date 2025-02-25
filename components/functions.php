<?php session_start();

require_once(__DIR__ . '/../config/Database.php');

//Funcion general que manda a una pantalla de error
function redirectError($message)
{
    $url = str_replace($_SERVER['DOCUMENT_ROOT'], "", dirname(__DIR__));

    echo "<script>window.location.href = ' $url/logs/error.php?error_message=" . urlencode($message) . "';</script>";
    return false;
}

// Función para validar los campos antes de insertar o actualizar
function validateFields($table, $data)
{
    // Definir campos requeridos según la tabla
    $required_fields = getRequiredFields($table);

    if (!$required_fields) {
        redirectError("Tabla no reconocida.");
        return false;
    }

    // Verificar si faltan campos obligatorios
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || $data[$field] === "") {
            redirectError("Falta el valor de \"$field\".");
            return false;
        }
    }

    // Validaciones específicas por tabla
    return validateSpecificFields($table, $data);
}

function validateSpecificFields($table, $data)
{
    switch ($table) {
        case 'users':
            return validateUserFields($data);


        default:
            return true;
    }
}

function validateUserFields($data)
{
    // Validación para username (no puede tener espacios y no más de 25 caracteres)
    if (strpos($data['username'], ' ') !== false) {
        redirectError("El nombre de usuario no puede contener espacios en blanco");
    }
    if (strlen($data['username']) > 25) {
        redirectError("El campo username no puede tener más de 25 caracteres.");
    }

    // Validación para email (debe ser una dirección de correo válida)
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        redirectError("El campo email debe ser una dirección de correo válida.");
    }

    // Validación para password (no más de 255 caracteres)
    if (strlen($data['password']) > 255) {
        redirectError("El campo contraseña no puede tener más de 255 caracteres.");
    }

    // Validación para full_name (máximo 100 caracteres)
    if (strlen($data['full_name']) > 100) {
        redirectError("El campo nombre completo no puede tener más de 100 caracteres.");
    }

    // Validación para phone_number (máximo 15 caracteres)
    if (strlen($data['phone_number']) > 15) {
        redirectError("El campo telefono no puede tener más de 15 caracteres.");
    }

    // Validación para address (máximo 255 caracteres)
    if (strlen($data['address']) > 255) {
        redirectError("El campo 'address' no puede tener más de 255 caracteres.");
    }

    // Validación para role (debe ser 'admin' o 'user')
    $validRoles = ['admin', 'user'];
    if (!in_array(strtolower($data['role']), $validRoles)) {
        redirectError("El campo 'role' debe ser uno de los siguientes valores: 'admin' o 'user'.");
    }

    return true; // No hay errores
}


// Función para obtener los campos requeridos según la tabla
function getRequiredFields($table)
{
    $fields = [
        'users' => ['username', 'email', 'password', 'full_name', 'phone_number', 'address', 'role'],
    ];

    return $fields[$table] ?? null;
}

// Funcion que inserta cualquier elemento en la base de datos
function insertRecord($table, $data)
{
    $db = Database::getInstance()->getConnection();

    validateFields($table, $data);

    $columns = implode(", ", array_keys($data));
    $placeholders = ":" . implode(", :", array_keys($data));
    $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
    $stmt = $db->prepare($sql);

    foreach ($data as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }

    try {
        $stmt->execute();
        return true;
    } catch (PDOException $e) {
        redirectError("Error: Ya existe un registro con esta clave primaria.");
    }
}

// Comprueba si existe un registri igual en la base de datos
function recordExists($table, $column, $value)
{
    $db = Database::getInstance()->getConnection();

    $query = "SELECT COUNT(*) FROM $table WHERE $column = :value";

    $stmt = $db->prepare($query);

    $stmt->bindParam(':value', $value, PDO::PARAM_STR);

    $stmt->execute();

    $count = $stmt->fetchColumn();

    // Si el resultado es mayor que 0, el registro existe
    return $count > 0;
}

function validatePwd($password)
{
    return strlen($password) >= 6 && preg_match('/[a-zA-Z]/', $password) && preg_match('/\d/', $password) && preg_match('/[*?¿!]/', $password);
}

function initializeSession($data)
{
    if (!isset($_SESSION["userLogged"])) {
        $_SESSION["userLogged"] = [];
    }
    $_SESSION["userLogged"] = [
        'username' => $data['username'],
        'role' => $data['role']
    ];
}

function getUserByUsername($username)
{
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]); // Corrección del parámetro

        $result = $stmt->fetch(); // Obtener solo el primer resultado
        if (!$result) {  // Comprobar si no hay filas
            redirectError("El usuario " . $username . " no se encuentra registrado");
            return false;
        }

        return $result;
    } catch (PDOException $e) {
        redirectError("Error de base de datos: " . htmlspecialchars($e->getMessage()));
        return false;
    }
}

function logIn($data)
{
    $userFind = getUserByUsername($data['username']);
    validateFields("users", $userFind);


    $message = '';

    // En el caso de admin la contraseña no esta encriptada por lo que tengo que compararla de manera normal 
    if (password_verify($data['password'], $userFind['password']) or $userFind['password'] === $data['password']) {
        initializeSession($userFind);
        $message = 'Bienvenido ' . $userFind['username'];
        echo "<script>window.location.href = '../index.php?success_message=" . urlencode($message) . "';</script>";
    } else {
        $message = 'Usuario o contraseña incorrect@';
        echo "<script>window.location.href = './login.php?error_message=" . urlencode($message) . "';</script>";
    }
}

// Función para obtener todos los datos de una tabla
function getAll($table)
{
    $db = Database::getInstance()->getConnection();

    $stmt = $db->query("SELECT * FROM $table");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function handleUserPassword($userToEdit)
{
    $action = $_GET['action'] ?? '';  // Obtener el valor de la acción

    if ($action === 'edit') {
        updateUser($userToEdit);
    }
}

function updateUser($user)
{
    // Si es correcta la contraseña actual, se actualiza la nueva introducida
    if (checkOldPass($user['oldPass'])) {

        // $newPass = $_POST['password'] ?? '';
        // $data['password'] = password_hash($newPass, PASSWORD_DEFAULT);

        // Si alguno de los valores no esta vacío, significa que el user quiere cambiar su pass
        if (!empty($user['password']) || !empty($user['confirm_password'])) {
            if ($user['password'] === $user['confirm_password']) {
                if (validatePwd($user['password'])) {
                    $user['password'] = password_hash($user['password'], PASSWORD_DEFAULT);
                } else {
                    redirectError("La contraseña no es válida");
                }
            } else {
                redirectError("Las contraseñas no coinciden");
            }
        } else {
            // Si no se quiere cambiar contraseña se deja la que estaba
            $user['password'] = password_hash($user['oldPass'], PASSWORD_DEFAULT);
        }

        // Quitamos asociaciones para poder editar correctamente
        unset($user['oldPass']);
        unset($user['confirm_password']);
        updateRecord('users', $user, $user['username'], 'username');
    }




    // En el caso de que un usuario se este editando a si mismo, actualizamos el session
    if ($_SESSION['userLogged']['username'] === $user['username']) {
        // Actualizamos los campos 'username' y 'role' en la sesión directamente

        $_SESSION['userLogged']['username'] = $user['username'];
        $_SESSION['userLogged']['role'] = $user['role']; // El rol no se cambia
    }
    $messageAction = 'El usuario ' . $user['username'] . ' ha sido modificado correctamente :)';
    echo "<script>window.location.href = './profile.php?success_message=" . urlencode($messageAction) . "';</script>";
    exit;
}

function checkOldPass($oldPass)
{
    $userPass = getUserByUsername($_POST['username']);
    if (!password_verify($oldPass, $userPass['password']) and $oldPass !== $userPass['password']) { // Caso en el que sea admin y la pass sea text plain
        redirectError("La contraseña actual introducida no es correcta");
        return false;   
    }

    return true;
}


// Funcion para actualizar un registro por ID
function updateRecord($table, $data, $id, $columnaId)
{
    $db = Database::getInstance()->getConnection();

    // Validar campos antes de actualizar
    validateFields($table, $data);

    $setPart = [];
    foreach ($data as $key => $value) {
        $setPart[] = "$key = :$key";
    }
    $setClause = implode(", ", $setPart);
    $sql = "UPDATE $table SET $setClause WHERE $columnaId = :id";

    $stmt = $db->prepare($sql);

    foreach ($data as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }
    $stmt->bindValue(":id", $id);
    try {
        $stmt->execute();

        return true; // Inserción exitosa
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            redirectError("Error: Ya existe un registro con esta clave primaria.");
        }
        redirectError("Error al insertar el registro: " . $e->getMessage());
    }
    return $stmt->execute();
}

// Funcion para obtener un registro por ID de la table que quieras
function getById($table, $id, $columnaId)
{
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM $table WHERE $columnaId = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


// Category

//NOMBRE DE CATEGORIA 
function getCategoryName($category_id) {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT name FROM categories WHERE id = ?");
    $stmt->execute([$category_id]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ? $row['name'] : "Sin categoría";
}


//validar si ya existe esa categoria
function validateCategories($table, $name, $id = null, $columnaId = 'id') {
    $db = Database::getInstance()->getConnection();
    
    // Preparar la consulta SQL
    $sql = "SELECT COUNT(*) FROM $table WHERE name = ?";
    
    // Si se proporciona un ID, también verificamos que no exista otra categoría con el mismo ID
    if ($id !== null) {
        $sql .= " AND $columnaId != ?";
    }

    $stmt = $db->prepare($sql);
    
    // Ejecutar la consulta
    if ($id !== null) {
        $stmt->execute([$name, $id]);
    } else {
        $stmt->execute([$name]);
    }

    // Obtener el conteo
    $count = $stmt->fetchColumn();

    // Si el conteo es mayor que 0, significa que ya existe una categoría con ese nombre (y posible ID)
    return $count > 0;
}

// Inserta una categoría en la Base de Datos
function insertCategory($table, $data)
{
    $db = Database::getInstance()->getConnection();

    // Generar un nuevo ID solo si la tabla es 'categories'
    if ($table === 'categories') {
        $newId = generateNewCategoryId($table);
        $data['id'] = $newId; // Asignar el nuevo ID al array de datos
    }

    // Construir la consulta SQL
    $columns = implode(", ", array_keys($data));
    $placeholders = ":" . implode(", :", array_keys($data));

    // SQL para insertar la categoría
    $sql = "INSERT INTO categories ($columns) VALUES ($placeholders)";
    $stmt = $db->prepare($sql);

    // Bindear los parámetros
    foreach ($data as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }

    // Ejecutar la consulta y retornar el resultado
    return $stmt->execute();
}

// GENERAR ID DE CATEGORÍA
function generateNewCategoryId($table)
{
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT MAX(id) AS max_id FROM $table");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si no hay registros, comenzar desde un ID base
    if (!$row['max_id']) {
        return '1'; // o el valor inicial que desees
    }

    // Incrementar el ID numérico
    $nuevoId = intval($row['max_id']) + 1;
    return $nuevoId;
}

// ACTUALIZAR UNA CATEGORÍA
function updateCategory($table, $data, $id, $columnaId = 'id')
{
    // Validaciones específicas para la tabla Category
    if ($table === 'categories') {
        // Validar si el nombre no está vacío
        if (isset($data['nombre']) && empty($data['nombre'])) {
            $error_message = "El campo 'nombre' no puede estar vacío.";
            echo "<script>window.location.href = '../../../logs/error.php?error_message=" . urlencode($error_message) . "';</script>";
            return false;
        }
    }

    try {
        $db = Database::getInstance()->getConnection();
        $set = "";
        foreach ($data as $columna => $valor) {
            $set .= "$columna = :$columna, ";
        }
        $set = rtrim($set, ", ");
        $stmt = $db->prepare("UPDATE $table SET $set WHERE $columnaId = :id");
        $data['id'] = $id;

        return $stmt->execute($data);
    } catch (PDOException $e) {
        echo "<script>window.location.href = '../../../logs/error.php?error_message=Error de base de datos: " . htmlspecialchars($e->getMessage()) . ".';</script>";
        return false;
    }
}

// BORRAR UNA CATEGORÍA
function deleteCategory($table, $id, $columnaId = 'id')
{
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("DELETE FROM $table WHERE $columnaId = ?");

    return $stmt->execute([$id]);
}

// PRODUCTS

//Validar si ya existe producto
function validateProductName($table, $name, $id = null, $columnaId = 'id') {
    $db = Database::getInstance()->getConnection();
    
    // Preparar la consulta SQL
    $sql = "SELECT COUNT(*) FROM $table WHERE name = ?";
    
    // Si se proporciona un ID, aseguramos que el producto con ese ID no sea el mismo
    if ($id !== null) {
        $sql .= " AND $columnaId != ?";
    }

    $stmt = $db->prepare($sql);
    
    // Ejecutar la consulta
    if ($id !== null) {
        $stmt->execute([$name, $id]);
    } else {
        $stmt->execute([$name]);
    }

    // Obtener el conteo
    $count = $stmt->fetchColumn();

    // Si el conteo es mayor que 0, significa que ya existe un producto con ese nombre
    return $count > 0;
}

//AÑADIR UN PRODUCTO
function insertProduct($table,$data)
{
    $db = Database::getInstance()->getConnection();

    // Generar un nuevo ID solo si la tabla es 'products'
    if ($table === 'products') {
        $newId = generateNewProductId($table);
        $data['id'] = $newId; // Asignar el nuevo ID al array de datos
    }

    // Construir la consulta SQL
    $columns = implode(", ", array_keys($data));
    $placeholders = ":" . implode(", :", array_keys($data));

    // SQL para insertar el producto
    $sql = "INSERT INTO products ($columns) VALUES ($placeholders)";
    $stmt = $db->prepare($sql);

    // Bindear los parámetros
    foreach ($data as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }

    // Ejecutar la consulta y retornar el resultado
    return $stmt->execute();
}

//GENERAR ID DE PRODUCTO
function generateNewProductId($table)
{
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT MAX(id) AS max_id FROM $table");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si no hay registros, comenzar desde un ID base
    if (!$row['max_id']) {
        return '1'; // o el valor inicial que desees
    }

    // Incrementar el ID numérico
    $nuevoId = intval($row['max_id']) + 1; // Incrementar el ID numérico
    return $nuevoId; // Retornar nuevo ID
}

//ACTUALIZAR UN PRODUCTO 
function updateProduct($table, $data, $id, $columnaId = 'id')
{
    // Validaciones específicas para la tabla Producto
    if ($table === 'products') {
        // Validar si el precio es un número
        if (isset($data['precio']) && !is_numeric($data['precio'])) {
            $error_message = "El valor de 'precio' debe ser un número.";
            echo "<script>window.location.href = '../../../logs/error.php?error_message=" . urlencode($error_message) . "';</script>";
            return false;
        }
        
        // Validar si el nombre no está vacío
        if (isset($data['nombre']) && empty($data['nombre'])) {
            $error_message = "El campo 'nombre' no puede estar vacío.";
            echo "<script>window.location.href = '../../../logs/error.php?error_message=" . urlencode($error_message) . "';</script>";
            return false;
        }
    }

    try {
        $db = Database::getInstance()->getConnection();
        $set = "";
        foreach ($data as $columna => $valor) {
            $set .= "$columna = :$columna, ";
        }
        $set = rtrim($set, ", ");
        $stmt = $db->prepare("UPDATE $table SET $set WHERE $columnaId = :id");
        $data['id'] = $id;

        return $stmt->execute($data);
    } catch (PDOException $e) {
        echo "<script>window.location.href = '../../../logs/error.php?error_message=Error de base de datos: " . htmlspecialchars($e->getMessage()) . ".';</script>";
        return false;
    }
}

//BORRAR PRODUCTO 
function deleteProduct($table, $id, $columnaId = 'id')
{
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("DELETE FROM $table WHERE $columnaId = ?");

    return $stmt->execute([$id]);
}

function getAllCategories() {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT id, name FROM categories");
    $categories = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $categories[] = $row;
    }

    return $categories;
}