
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard - EventHub</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="auth-box">
    <h1>Welcome to EventHub!</h1>

    <h2>
        Hello,
        <?php echo htmlspecialchars($_SESSION['user_name']); ?>!
    </h2>

    <p>You have successfully logged in.</p>

    <h3>Your Dashboard</h3>

    <p>Explore and book your favourite events.</p>

    <a href="index.php">Go to Home</a>

    <br><br>

    <a href="logout.php" class="logout-btn">Logout</a>
</div>

</body>
</html>
