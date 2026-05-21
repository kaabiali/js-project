<?php
require_once __DIR__ . '/app/partials/header.php';
require_once __DIR__ . '/app/config/database.php';
require_login();

$reservation_id = $_SESSION['last_reservation_id'] ?? 0;
if (!$reservation_id) {
    redirect('/index.php');
}

$reservation = null;
try {
    $stmt = $pdo->prepare("
        SELECT r.*, rm.name AS room_name, rm.type AS room_type
        FROM reservations r
        JOIN rooms rm ON r.room_id = rm.id
        WHERE r.id = ? AND r.user_id = ?
    ");
    $stmt->execute([$reservation_id, $_SESSION['user_id']]);
    $reservation = $stmt->fetch();
} catch (PDOException $e) {
    log_error("Booking confirmation error: " . $e->getMessage());
}

if (!$reservation) {
    redirect('/index.php');
}

$services = [];
try {
    $stmt = $pdo->prepare("
        SELECT s.name, rs.price
        FROM reservation_services rs
        JOIN services s ON rs.service_id = s.id
        WHERE rs.reservation_id = ?
    ");
    $stmt->execute([$reservation_id]);
    $services = $stmt->fetchAll();
} catch (PDOException $e) {
    log_error("Booking confirmation services error: " . $e->getMessage());
}

$days = max(1, (strtotime($reservation['check_out']) - strtotime($reservation['check_in'])) / 86400);
$services_total = 0;
foreach ($services as $s) { $services_total += $s['price']; }
?>
<h1>Booking Confirmed</h1>
<div class="form" style="max-width:600px">
    <div style="text-align:center;margin-bottom:20px">
        <span style="font-size:3em">&#10003;</span>
        <p style="color:var(--navy);font-weight:600">Your reservation has been submitted!</p>
    </div>
    <p><strong>Room:</strong> <?= escape($reservation['room_name']) ?> (<?= escape(ucfirst($reservation['room_type'])) ?>)</p>
    <p><strong>Check-in:</strong> <?= escape($reservation['check_in']) ?></p>
    <p><strong>Check-out:</strong> <?= escape($reservation['check_out']) ?></p>
    <p><strong>Nights:</strong> <?= (int)$days ?></p>
    <?php if (!empty($services)): ?>
    <p><strong>Services:</strong></p>
    <ul style="margin:5px 0 10px 20px">
        <?php foreach ($services as $s): ?>
        <li><?= escape($s['name']) ?> — $<?= number_format($s['price'], 2) ?></li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
    <p><strong>Room Total:</strong> $<?= number_format($reservation['total_price'], 2) ?></p>
    <?php if ($services_total > 0): ?>
    <p><strong>Services Total:</strong> $<?= number_format($services_total, 2) ?></p>
    <p><strong>Grand Total:</strong> $<?= number_format($reservation['total_price'] + $services_total, 2) ?></p>
    <?php endif; ?>
    <p><strong>Payment:</strong> <span class="badge badge-<?= escape($reservation['payment_method'] ?? 'cash') ?>"><?= escape(ucfirst($reservation['payment_method'] ?? 'cash')) ?></span></p>
    <p><strong>Status:</strong> <span class="badge badge-pending">Pending</span></p>
    <div style="text-align:center;margin-top:20px">
        <a href="/app/user/profile.php" class="btn">View My Reservations</a>
        <br><br>
        <a href="/index.php">Back to Home</a>
    </div>
</div>
<?php require_once __DIR__ . '/app/partials/footer.php'; ?>
