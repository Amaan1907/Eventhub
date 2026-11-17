
<?php
session_start();
include 'db.php';

$message = "";

if (isset($_GET['registered'])) {
    $message = "Registration successful! Please login.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, name, email, password, role FROM users WHERE email = ?"
    );

    if ($stmt) {

        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        if ($user && password_verify($password, $user['password'])) {

            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            mysqli_stmt_close($stmt);

            if ($user['role'] === 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: dashboard.php");
            }

            exit();

        } else {
            $message = "Invalid email or password!";
        }

        mysqli_stmt_close($stmt);

    } else {
        $message = "Database query failed!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - EventHub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="auth-box">

    <h2>Welcome Back!</h2>
    <p>Login to your EventHub account.</p>

    <p class="message">
        <?php echo htmlspecialchars($message); ?>
    </p>

    <form method="POST" action="login.php">

        <input
            type="email"
            name="email"
            placeholder="Email Address"
            required
        >

        <input
            type="password"
            name="password"
            placeholder="Password"
            required
        >

        <button type="submit">Login</button>

    </form>

    <p>
        Don't have an account?
        <a href="register.php">Register</a>
    </p>

    <a href="index.php">Back to Home</a>

</div>

</body>
</html>
