<?php
require_once __DIR__ . '/app/partials/header.php';
require_once __DIR__ . '/app/config/database.php';

$id = (int)($_GET['id'] ?? 0);
$room = null;

if ($id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
        $stmt->execute([$id]);
        $room = $stmt->fetch();
    } catch (PDOException $e) {
        log_error("Room detail error: " . $e->getMessage());
    }
}

if (!$room): ?>
    <h1>Room Not Found</h1>
    <p class="empty-state">The room you're looking for doesn't exist.</p>
    <p style="text-align:center"><a href="/index.php" class="btn">Back to Rooms</a></p>
<?php else: ?>
<h1>
    <?= escape($room['name']) ?>
    <?php if ($room['type'] === 'vip'): ?>
        <span class="badge badge-vip" style="vertical-align:middle">VIP</span>
    <?php endif; ?>
</h1>
<div class="room-detail">
    <div class="room-detail-img">
        <?php $img = !empty($room['image']) ? image_url($room['image']) : 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800&q=80'; ?>
        <img src="<?= $img ?>" alt="<?= escape($room['name']) ?>">
    </div>
    <div class="room-detail-info">
        <p class="room-type"><?= escape(ucfirst($room['type'])) ?></p>
        <p class="room-desc"><?= escape($room['description']) ?></p>
        <?php if ($room['type'] === 'vip'): ?>
            <p class="room-price" style="color:var(--sand);font-style:italic"><em>VIP guests enjoy priority check-in, dedicated concierge, and exclusive amenities.</em></p>
        <?php endif; ?>
        <p><strong>Capacity:</strong> Up to <?= (int)$room['capacity'] ?> guests</p>
        <p class="room-price" style="font-size:1.5em">$<?= number_format($room['price'], 2) ?> / night</p>
        <?php if ($room['is_available']): ?>
            <?php if (!empty($_SESSION['user_id'])): ?>
                <a href="/reserve.php?room_id=<?= (int)$room['id'] ?>" class="btn">Book This Room</a>
            <?php else: ?>
                <a href="/app/auth/login.php" class="btn">Login to Book</a>
            <?php endif; ?>
        <?php else: ?>
            <p><span class="badge badge-inactive">Not Available</span></p>
        <?php endif; ?>
        <p style="margin-top:20px"><a href="/index.php">&larr; Back to Rooms</a></p>
    </div>
</div>
<?php endif; ?>
<?php require_once __DIR__ . '/app/partials/footer.php'; ?>
