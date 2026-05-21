<?php
require_once __DIR__ . '/app/partials/header.php';
require_once __DIR__ . '/app/config/database.php';
require_login();

$pending = $_SESSION['pending_reservation'] ?? null;
if (!$pending) {
    redirect('/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['_csrf'] ?? '')) { $error = 'Invalid session. Please try again.'; }
    else {
        $payment_method = in_array($_POST['payment_method'] ?? '', ['card', 'cash', 'transfer'])
            ? $_POST['payment_method'] : 'cash';

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO reservations (user_id, room_id, check_in, check_out, total_price, status, payment_method) VALUES (?, ?, ?, ?, ?, 'pending', ?)");
            $stmt->execute([
                $_SESSION['user_id'],
                $pending['room_id'],
                $pending['check_in'],
                $pending['check_out'],
                $pending['total_price'],
                $payment_method,
            ]);
            $reservation_id = $pdo->lastInsertId();

            if (!empty($pending['services'])) {
                $ins_stmt = $pdo->prepare("INSERT INTO reservation_services (reservation_id, service_id, price) VALUES (?, ?, ?)");
                foreach ($pending['services'] as $svc) {
                    $ins_stmt->execute([$reservation_id, $svc['id'], $svc['price']]);
                }
            }

            $pdo->commit();

            $_SESSION['last_reservation_id'] = $reservation_id;
            unset($_SESSION['pending_reservation']);

            $services_total = array_sum(array_column($pending['services'], 'price'));
            $msg = 'Reservation created! Room total: $' . number_format($pending['total_price'], 2);
            if ($services_total > 0) {
                $msg .= ' | Services: $' . number_format($services_total, 2);
            }
            $_SESSION['flash'] = ['type' => 'success', 'message' => $msg];
            redirect('/booking_confirmation.php');
        } catch (PDOException $e) {
            $pdo->rollBack();
            log_error("Payment error: " . $e->getMessage());
            $error = 'Something went wrong. Please try again.';
        }
    }
}

$services_total = array_sum(array_column($pending['services'], 'price'));
$grand_total = $pending['total_price'] + $services_total;
?>
<h1>Payment Method</h1>

<?php if ($error): ?><div class="flash flash-error"><?= escape($error) ?></div><?php endif; ?>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px; max-width:900px">
    <div class="form" style="margin:0">
        <h3 style="margin-top:0">Booking Summary</h3>
        <p><strong>Room:</strong> <?= escape($pending['room_name']) ?> (<?= escape(ucfirst($pending['room_type'])) ?>)</p>
        <p><strong>Check-in:</strong> <?= escape($pending['check_in']) ?></p>
        <p><strong>Check-out:</strong> <?= escape($pending['check_out']) ?></p>
        <p><strong>Nights:</strong> <?= (int)$pending['nights'] ?></p>
        <?php if (!empty($pending['services'])): ?>
        <p><strong>Services:</strong></p>
        <ul style="margin:5px 0 10px 20px">
            <?php foreach ($pending['services'] as $svc): ?>
            <li><?= escape($svc['name']) ?> — $<?= number_format($svc['price'], 2) ?></li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
        <p><strong>Room Total:</strong> $<?= number_format($pending['total_price'], 2) ?></p>
        <?php if ($services_total > 0): ?>
        <p><strong>Services Total:</strong> $<?= number_format($services_total, 2) ?></p>
        <p><strong>Grand Total:</strong> $<?= number_format($grand_total, 2) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <form method="post" class="form" id="payment-form" style="margin:0">
            <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
            <h3 style="margin-top:0">Select Payment Method</h3>

            <label class="payment-option" style="display:flex;align-items:center;gap:10px;padding:12px;border:2px solid #ddd;border-radius:8px;margin-bottom:10px;cursor:pointer">
                <input type="radio" name="payment_method" value="card" checked>
                <span style="font-size:1.3em">&#128179;</span>
                <div><strong>Credit / Debit Card</strong><br><small>Pay securely with your card</small></div>
            </label>

            <div id="card-fields" style="padding:10px 0 10px 30px">
                <div class="form-group">
                    <label>Card Number</label>
                    <input type="text" name="card_number" id="card-number" placeholder="1234 5678 9012 3456" maxlength="19" inputmode="numeric">
                </div>
                <div class="form-group">
                    <label>Cardholder Name</label>
                    <input type="text" name="card_name" placeholder="John Doe">
                </div>
                <div style="display:flex;gap:10px">
                    <div class="form-group" style="flex:1">
                        <label>Expiry (MM/YY)</label>
                        <input type="text" name="card_expiry" placeholder="MM/YY" maxlength="5">
                    </div>
                    <div class="form-group" style="flex:1">
                        <label>CVV</label>
                        <input type="text" name="card_cvv" placeholder="123" maxlength="4" inputmode="numeric">
                    </div>
                </div>
            </div>

            <label class="payment-option" style="display:flex;align-items:center;gap:10px;padding:12px;border:2px solid #ddd;border-radius:8px;margin-bottom:10px;cursor:pointer">
                <input type="radio" name="payment_method" value="cash">
                <span style="font-size:1.3em">&#128176;</span>
                <div><strong>Cash on Arrival</strong><br><small>Pay at the hotel front desk</small></div>
            </label>

            <label class="payment-option" style="display:flex;align-items:center;gap:10px;padding:12px;border:2px solid #ddd;border-radius:8px;margin-bottom:15px;cursor:pointer">
                <input type="radio" name="payment_method" value="transfer">
                <span style="font-size:1.3em">&#127974;</span>
                <div><strong>Bank Transfer</strong><br><small>Transfer to our bank account</small></div>
            </label>

            <button type="submit" class="btn" style="width:100%">Confirm Reservation</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var cardRadio = document.querySelector('input[name="payment_method"][value="card"]');
    var cardFields = document.getElementById('card-fields');
    var cardNumber = document.getElementById('card-number');

    function toggleCardFields() {
        cardFields.style.display = cardRadio.checked ? '' : 'none';
    }

    document.querySelectorAll('input[name="payment_method"]').forEach(function(r) {
        r.addEventListener('change', toggleCardFields);
    });
    toggleCardFields();
});
</script>

<?php require_once __DIR__ . '/app/partials/footer.php'; ?>
