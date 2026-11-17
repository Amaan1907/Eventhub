
<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) ||
    $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$sql = "SELECT b.*, u.name, u.email, e.title
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN events e ON b.event_id = e.id
        ORDER BY b.booking_date DESC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Bookings - EventHub</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="admin-container">
    <h1>All Event Bookings</h1>

    <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <div class="admin-event">
            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
            <p>User: <?php echo htmlspecialchars($row['name']); ?></p>
            <p>Email: <?php echo htmlspecialchars($row['email']); ?></p>
            <p>Tickets: <?php echo (int)$row['tickets']; ?></p>
            <p>Total: ₹<?php echo htmlspecialchars($row['total_amount']); ?></p>
            <p>Booking ID: <?php echo (int)$row['id']; ?></p>
        </div>
    <?php endwhile; ?>

    <a href="admin.php">Back to Admin Dashboard</a>
</div>
</body>
</html>
