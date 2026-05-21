<?php require_once __DIR__ . '/app/partials/header.php'; ?>
<style>
.hero-banner { width:100%; height:400px; object-fit:cover; border-radius:var(--radius-lg); margin:20px 0; }
.about-section { max-width:800px; margin:0 auto 40px; }
.about-section h2 { font-family:var(--font-heading); color:var(--navy); margin-bottom:15px; }
.about-section p { line-height:1.8; margin-bottom:15px; }
.contact-info p { margin-bottom:8px; }
.map-wrap { border-radius:var(--radius-lg); overflow:hidden; margin:20px 0; }
.map-wrap iframe { width:100%; height:350px; border:0; }
</style>

<div class="about-section">
    <img class="hero-banner" src="https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1400&q=85" alt="Azur Cove Hotel">

    <h2>About Azur Cove Hotel</h2>
    <p><em>Where the Sea Meets Serenity</em></p>
    <p>Nestled along the pristine coastline of Tunisia, Azur Cove Hotel offers a tranquil escape where the Mediterranean Sea meets unparalleled comfort. Our resort combines modern luxury with the timeless charm of coastal living, providing every guest with an unforgettable stay.</p>
    <p>From our spacious rooms with panoramic ocean views to our world-class spa and dining experiences, every detail at Azur Cove is designed to help you unwind, reconnect, and create lasting memories.</p>

    <h2>Location</h2>
    <p>Rue de la Corniche, 1057 Tunis, Tunisia</p>
    <div class="map-wrap">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d51099.32166624239!2d10.159285!3d36.8065!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x12fd337f5e9ef719%3A0x5c94c12c5efc8a82!2sTunis%2C+Tunisia!5e0!3m2!1sen!2s!4v1" allowfullscreen loading="lazy"></iframe>
    </div>

    <h2>Contact</h2>
    <div class="contact-info">
        <p><strong>Phone:</strong> +216 71 000 000</p>
        <p><strong>Email:</strong> contact@azurcove.com</p>
        <p><strong>Front Desk:</strong> Open 24/7</p>
        <p><strong>Check-in:</strong> 3:00 PM &nbsp;|&nbsp; <strong>Check-out:</strong> 12:00 PM</p>
    </div>
</div>
<?php require_once __DIR__ . '/app/partials/footer.php'; ?>
