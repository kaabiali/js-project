<?php
require_once __DIR__ . '/app/partials/header.php';
require_once __DIR__ . '/app/config/database.php';

$services = [];
try {
    $stmt = $pdo->query("SELECT id, name, description, price, image FROM services ORDER BY name");
    $services = $stmt->fetchAll();
} catch (PDOException $e) {
    log_error("Services page error: " . $e->getMessage());
}
?>
<h1>Our Services</h1>
<p style="margin-bottom:20px">Add these services to your reservation when booking a room. <a href="/index.php">Browse rooms</a>.</p>
<div class="room-grid">
    <?php foreach ($services as $s): ?>
    <div class="room-card">
        <div class="room-img">
            <?php $s_img = !empty($s['image']) ? image_url($s['image']) : 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=800&q=80'; ?>
            <img src="<?= $s_img ?>" alt="<?= escape($s['name']) ?>">
        </div>
        <div class="room-info">
            <h3><?= escape($s['name']) ?></h3>
            <p class="room-desc"><?= escape($s['description']) ?></p>
            <p class="room-price">$<?= number_format($s['price'], 2) ?></p>
            <p><span class="badge badge-active">Available</span></p>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($services)): ?>
    <p class="empty-state">No services available at this time.</p>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/app/partials/footer.php'; ?>
