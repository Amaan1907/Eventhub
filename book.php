
<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$event_id = (int)($_GET['id'] ?? $_POST['event_id'] ?? 0);
$message = "";

if ($event_id <= 0) {
    die("Invalid event ID.");
}

// Get event details
$stmt = mysqli_prepare($conn, "SELECT * FROM events WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $event_id);
mysqli_stmt_execute($stmt);
$event = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$event) {
    die("Event not found.");
}

// Process booking
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $tickets = filter_var(
        $_POST['tickets'] ?? 0,
        FILTER_VALIDATE_INT
    );

    if ($tickets === false || $tickets < 1) {
        $message = "Please enter a valid number of tickets.";
    } else {

        mysqli_begin_transaction($conn);

        try {

            // Lock event row and check seats
            $stmt = mysqli_prepare(
                $conn,
                "SELECT price, available_seats
                 FROM events WHERE id = ? FOR UPDATE"
            );

            mysqli_stmt_bind_param($stmt, "i", $event_id);
            mysqli_stmt_execute($stmt);

            $current = mysqli_fetch_assoc(
                mysqli_stmt_get_result($stmt)
            );

            mysqli_stmt_close($stmt);

            if (!$current) {
                throw new Exception("Event not found.");
            }

            if ($tickets > $current['available_seats']) {
                throw new Exception("Not enough seats available.");
            }

            $total = $current['price'] * $tickets;
            $user_id = (int)$_SESSION['user_id'];

            // Save booking
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO bookings
                (user_id, event_id, tickets, total_amount)
                VALUES (?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "iiid",
                $user_id,
                $event_id,
                $tickets,
                $total
            );

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Booking could not be saved.");
            }

            mysqli_stmt_close($stmt);

            // Reduce available seats
            $stmt = mysqli_prepare(
                $conn,
                "UPDATE events
                 SET available_seats = available_seats - ?
                 WHERE id = ?"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "ii",
                $tickets,
                $event_id
            );

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Unable to update seats.");
            }

            mysqli_stmt_close($stmt);

            mysqli_commit($conn);

            // Redirect to your actual filename
            header("Location: my.php?success=1");
            exit();

        } catch (Throwable $e) {

            mysqli_rollback($conn);
            $message = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book Event - EventHub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="auth-box">

    <h2>Book Your Event</h2>

    <h3>
        <?php echo htmlspecialchars($event['title']); ?>
    </h3>

    <p>Date:
        <?php echo htmlspecialchars($event['event_date']); ?>
    </p>

    <p>Venue:
        <?php echo htmlspecialchars($event['venue']); ?>
    </p>

    <p>Price per ticket:
        ₹<?php echo htmlspecialchars($event['price']); ?>
    </p>

    <p>Available seats:
        <?php echo (int)$event['available_seats']; ?>
    </p>

    <p class="message">
        <?php echo htmlspecialchars($message); ?>
    </p>

    <?php if ((int)$event['available_seats'] > 0): ?>

    <form method="POST" action="book.php?id=<?php echo $event_id; ?>">

        <input type="hidden"
               name="event_id"
               value="<?php echo $event_id; ?>">

        <label>Number of Tickets</label>

        <input
            type="number"
            name="tickets"
            min="1"
            max="<?php echo (int)$event['available_seats']; ?>"
            value="1"
            required
        >

        <button type="submit">Confirm Booking</button>

    </form>

    <?php else: ?>

        <p>Sorry! No seats available.</p>

    <?php endif; ?>

    <a href="index.php">Back to Home</a>

</div>

</body>
</html>
