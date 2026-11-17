
<?php
session_start();
include 'db.php';

$events = mysqli_query($conn,
    "SELECT * FROM events ORDER BY event_date ASC"
);
?>

<!DOCTYPE html>
<html>
<head>
<title>EventHub - Event Management</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="style.css">
</head>

<body>

<header>
    <div class="logo">Event<span>Hub</span></div>

    <nav>
        <a href="index.php">Home</a>
        <a href="#events">Events</a>
        <a href="#about">About</a>

        <?php if (isset($_SESSION['user_role']) &&
                  $_SESSION['user_role'] === 'admin'): ?>
            <a href="admin.php">Admin Dashboard</a>
        <?php endif; ?>
    </nav>

    <div class="nav-buttons">
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="dashboard.php">Dashboard</a>
            <a href="my.php">My Bookings</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </div>
</header>

<section class="hero">
    <h1>Make Your Events Unforgettable</h1>
    <p>Discover, organize and book amazing events.</p>
    <a href="#events" class="btn">Explore Events</a>
</section>

<section class="events" id="events">
<h2>Upcoming Events</h2>
<p>Explore our latest events.</p>

<div class="event-container">

<?php if ($events && mysqli_num_rows($events) > 0): ?>

<?php while ($event = mysqli_fetch_assoc($events)): ?>

<div class="event-card">

<div class="event-image">🎉</div>

<h3><?php echo htmlspecialchars($event['title']); ?></h3>

<p><?php echo htmlspecialchars($event['description']); ?></p>

<p><b>Date:</b>
<?php echo htmlspecialchars($event['event_date']); ?></p>

<p><b>Time:</b>
<?php echo htmlspecialchars($event['event_time'] ?? ''); ?></p>

<p><b>Venue:</b>
<?php echo htmlspecialchars($event['venue']); ?></p>

<p><b>Price:</b>
₹<?php echo htmlspecialchars($event['price']); ?></p>

<p><b>Available Seats:</b>
<?php echo (int)$event['available_seats']; ?></p>

<?php if (isset($_SESSION['user_id'])): ?>
    <a href="book.php?id=<?php echo (int)$event['id']; ?>">Book Now</a>
<?php else: ?>
    <a href="login.php">Login to Book</a>
<?php endif; ?>

</div>

<?php endwhile; ?>

<?php else: ?>

<p>No events available currently.</p>

<?php endif; ?>

</div>
</section>

<section class="about" id="about">
<h2>About EventHub</h2>
<p>EventHub helps users discover and book events easily.</p>
</section>

<footer>
<p>© 2026 EventHub | Event Management System</p>
</footer>

</body>
</html>
