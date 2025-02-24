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
