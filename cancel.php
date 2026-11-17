
<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$booking_id = (int)($_POST['booking_id'] ?? 0);

if ($booking_id <= 0 || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: my.php");
    exit();
}

mysqli_begin_transaction($conn);

try {
    $stmt = mysqli_prepare($conn,
        "SELECT event_id, tickets FROM bookings
         WHERE id=? AND user_id=? FOR UPDATE"
    );
    mysqli_stmt_bind_param($stmt, "ii", $booking_id, $user_id);
    mysqli_stmt_execute($stmt);
    $booking = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$booking) {
        throw new Exception("Booking not found.");
    }

    $stmt = mysqli_prepare($conn,
        "UPDATE events SET available_seats = available_seats + ?
         WHERE id=?"
    );
    mysqli_stmt_bind_param(
        $stmt, "ii", $booking['tickets'], $booking['event_id']
    );
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($conn,
        "DELETE FROM bookings WHERE id=? AND user_id=?"
    );
    mysqli_stmt_bind_param($stmt, "ii", $booking_id, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    mysqli_commit($conn);
    header("Location: my.php?cancelled=1");
    exit();

} catch (Throwable $e) {
    mysqli_rollback($conn);
    die("Cancellation failed: " . htmlspecialchars($e->getMessage()));
}
?>
