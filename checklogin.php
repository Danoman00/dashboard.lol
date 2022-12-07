<?php
// Debug
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
session_start();
// Get username from POST variable
$username = $_POST['username'];

// Get password from POST variable
$password = $_POST['password'];

// Get database info from json file in db_config.json
$db_config = json_decode(file_get_contents('key/db_config.json'), true);

// Connect to database
$host = $db_config['host'];
$user = $db_config['user'];
$pass = $db_config['pass'];
$db = $db_config['db'];
$port = $db_config['port'];

$conn = mysqli_connect ($host, $user, $pass, $db, $port);

if ($conn) {
    // Get username details from database
    $sql = "SELECT * FROM users WHERE user_name = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $hashed_password = $row['password'];

        // Check if password is correct
        if (password_verify($password, $hashed_password)) {
            // Log user in
            $stmt->close();
            $_SESSION['username'] = $username;
            $_SESSION['password'] = $hashed_password;
            $_SESSION['toast'] = "Welcome back, $username!";
            header("Location: dashboard.php");
        }
        else {
            $stmt->close();
            $_SESSION['toast'] = "Incorrect password";
            header("Location: login.php");
        }
    }
    else {
        $stmt->close();
        $_SESSION['toast'] = "Username does not exist";
        header("Location: login.php");
    }
}
else {
    die ("Connection failed: " . mysqli_connect_error());
    $_SESSION['toast'] = "Connection failed";
    header('Location: login.php');
}

?>