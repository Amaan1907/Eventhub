
<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";
$edit = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST['action'];

    if ($action === "delete") {
        $id = (int)$_POST['id'];

        $stmt = mysqli_prepare($conn, "DELETE FROM events WHERE id=?");
        mysqli_stmt_bind_param($stmt, "i", $id);

        $message = mysqli_stmt_execute($stmt)
            ? "Event deleted successfully!"
            : "Unable to delete event.";

        mysqli_stmt_close($stmt);

    } else {

        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $date = $_POST['event_date'];
        $time = $_POST['event_time'];
        $venue = trim($_POST['venue']);
        $price = $_POST['price'];
        $seats = $_POST['available_seats'];

        if ($title === "" || $date === "" || $venue === "" ||
            !is_numeric($price) || $price < 0 ||
            filter_var($seats, FILTER_VALIDATE_INT) === false ||
            $seats < 0) {

            $message = "Enter valid details.";

        } elseif ($action === "update") {

            $id = (int)$_POST['id'];

            $stmt = mysqli_prepare($conn,
                "UPDATE events SET title=?, description=?,
                event_date=?, event_time=?, venue=?,
                price=?, available_seats=? WHERE id=?");

            mysqli_stmt_bind_param($stmt, "sssssdii",
                $title, $description, $date, $time,
                $venue, $price, $seats, $id);

            $message = mysqli_stmt_execute($stmt)
                ? "Event updated successfully!"
                : "Update failed!";

            mysqli_stmt_close($stmt);

        } else {

            $stmt = mysqli_prepare($conn,
                "INSERT INTO events
                (title, description, event_date, event_time,
                venue, price, available_seats)
                VALUES (?, ?, ?, ?, ?, ?, ?)");

            mysqli_stmt_bind_param($stmt, "sssssdi",
                $title, $description, $date, $time,
                $venue, $price, $seats);

            $message = mysqli_stmt_execute($stmt)
                ? "Event added successfully!"
                : "Event addition failed!";

            mysqli_stmt_close($stmt);
        }
    }
}

if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];

    $stmt = mysqli_prepare($conn, "SELECT * FROM events WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $edit = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
}

$events = mysqli_query($conn, "SELECT * FROM events ORDER BY event_date ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - EventHub</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="admin-container">

<h1>EventHub Admin Dashboard</h1>
<p>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>

<p class="message"><?php echo htmlspecialchars($message); ?></p>

<h2><?php echo $edit ? "Edit Event" : "Add New Event"; ?></h2>

<form method="POST" class="admin-form">

<input type="hidden" name="action"
value="<?php echo $edit ? 'update' : 'add'; ?>">

<?php if ($edit): ?>
<input type="hidden" name="id" value="<?php echo $edit['id']; ?>">
<?php endif; ?>

<label>Event Name</label>
<input type="text" name="title"
value="<?php echo htmlspecialchars($edit['title'] ?? ''); ?>" required>

<label>Description</label>
<textarea name="description"><?php
echo htmlspecialchars($edit['description'] ?? '');
?></textarea>

<label>Event Date</label>
<input type="date" name="event_date"
value="<?php echo htmlspecialchars($edit['event_date'] ?? ''); ?>" required>

<label>Event Time</label>
<input type="time" name="event_time"
value="<?php echo htmlspecialchars(substr($edit['event_time'] ?? '', 0, 5)); ?>">

<label>Venue</label>
<input type="text" name="venue"
value="<?php echo htmlspecialchars($edit['venue'] ?? ''); ?>" required>

<label>Ticket Price</label>
<input type="number" name="price" min="0" step="0.01"
value="<?php echo htmlspecialchars($edit['price'] ?? '0'); ?>" required>

<label>Available Seats</label>
<input type="number" name="available_seats" min="0"
value="<?php echo htmlspecialchars($edit['available_seats'] ?? '100'); ?>" required>

<button type="submit">
<?php echo $edit ? "Update Event" : "Add Event"; ?>
</button>

</form>

<h2>All Events</h2>

<?php while ($row = mysqli_fetch_assoc($events)): ?>

<div class="admin-event">

<h3><?php echo htmlspecialchars($row['title']); ?></h3>
<p>Date: <?php echo htmlspecialchars($row['event_date']); ?></p>
<p>Venue: <?php echo htmlspecialchars($row['venue']); ?></p>
<p>Price: ₹<?php echo htmlspecialchars($row['price']); ?></p>
<p>Seats: <?php echo (int)$row['available_seats']; ?></p>

<a href="admin.php?edit=<?php echo $row['id']; ?>">Edit</a>

<form method="POST" onsubmit="return confirm('Delete this event?')">
<input type="hidden" name="action" value="delete">
<input type="hidden" name="id" value="<?php echo $row['id']; ?>">
<button type="submit">Delete</button>
</form>

</div>

<?php endwhile; ?>

<br>
<a href="index.php">Home</a> |
<a href="logout.php">Logout</a>
<a href="all.php">View All Bookings</a> |

</div>
</body>
</html>
