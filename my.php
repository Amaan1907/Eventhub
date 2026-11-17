
<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = (int)$_SESSION['user_id'];

$stmt = mysqli_prepare($conn,
    "SELECT b.*, e.title, e.event_date, e.venue
     FROM bookings b
     JOIN events e ON b.event_id = e.id
     WHERE b.user_id = ?
     ORDER BY b.booking_date DESC"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Bookings - EventHub</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="admin-container">

    <h1>My Bookings</h1>

    <?php if (isset($_GET['success'])): ?>
        <p class="message">Booking successful!</p>
    <?php endif; ?>

    <?php if (isset($_GET['cancelled'])): ?>
        <p class="message">Booking cancelled successfully!</p>
    <?php endif; ?>

    <?php if (mysqli_num_rows($result) > 0): ?>

        <?php while ($row = mysqli_fetch_assoc($result)): ?>

            <div class="admin-event">

                <h3><?php echo htmlspecialchars($row['title']); ?></h3>

                <p>Date: <?php echo htmlspecialchars($row['event_date']); ?></p>

                <p>Venue: <?php echo htmlspecialchars($row['venue']); ?></p>

                <p>Tickets: <?php echo (int)$row['tickets']; ?></p>

                <p>Total: ₹<?php echo htmlspecialchars($row['total_amount']); ?></p>

                <p>Booking ID: <?php echo (int)$row['id']; ?></p>

                <form method="POST" action="cancel.php"
                      onsubmit="return confirm('Are you sure you want to cancel this booking?');">

                    <input type="hidden" name="booking_id"
                           value="<?php echo (int)$row['id']; ?>">

                    <button type="submit">Cancel Booking</button>

                </form>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <p>No bookings yet.</p>

    <?php endif; ?>

    <a href="index.php">Back to Home</a>

</div>

</body>
</html>
