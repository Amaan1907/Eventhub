
<?php
session_start();
include 'db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if ($name == "" || $email == "" || $password == "") {
        $message = "Please fill all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Enter a valid email address.";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters.";
    } else {

        $check = mysqli_prepare($conn,
            "SELECT id FROM users WHERE email = ?"
        );
        mysqli_stmt_bind_param($check, "s", $email);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $message = "Email already registered!";
        } else {

            $hashed_password = password_hash(
                $password, PASSWORD_DEFAULT
            );

            $stmt = mysqli_prepare($conn,
                "INSERT INTO users (name, email, password)
                 VALUES (?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt, "sss",
                $name, $email, $hashed_password
            );

            if (mysqli_stmt_execute($stmt)) {
                header("Location: login.php?registered=1");
                exit();
            } else {
                $message = "Registration failed. Try again.";
            }
            mysqli_stmt_close($stmt);
        }
        mysqli_stmt_close($check);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - EventHub</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="auth-box">
    <h2>Create Account</h2>
    <p>Join EventHub today!</p>

    <p class="message">
        <?php echo htmlspecialchars($message); ?>
    </p>

    <form method="POST">
        <input type="text" name="name"
               placeholder="Full Name" required>

        <input type="email" name="email"
               placeholder="Email Address" required>

        <input type="password" name="password"
               placeholder="Password (minimum 8 characters)"
               minlength="8" required>

        <button type="submit">Register</button>
    </form>

    <p>Already have an account?
        <a href="login.php">Login</a>
    </p>

    <a href="index.php">Back to Home</a>
</div>

</body>
</html>
