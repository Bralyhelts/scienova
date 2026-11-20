<?php

require __DIR__ . '/config/bootstrap.php';

$user = require_login();

$stmt = $pdo->prepare(
    "SELECT COUNT(*)
     FROM downloads
     WHERE user_id = ?"
);

$stmt->execute([$user['id']]);

$downloads = $stmt->fetchColumn();

$stmt = $pdo->query(
    "SELECT *
     FROM announcements
     ORDER BY id DESC
     LIMIT 5"
);

$announcements = $stmt->fetchAll();

page_header('Dashboard');

?>

<h1>
    Welcome, <?= e($user['name']) ?>
</h1>

<?php if ($user['profile_pic']): ?>

    <img
        class="avatar"
        src="profile-image.php"
        alt="Profile picture"
    >

<?php endif; ?>


<div class="cards">

    <div class="tile">

        <small>Wallet Balance</small>

        <h2>
            <?= money($user['balance']) ?>
        </h2>

        <a href="wallet.php">
            Manage Wallet
        </a>

    </div>


    <div class="tile">

        <small>Downloaded Papers</small>

        <h2>
            <?= (int)$downloads ?>
        </h2>

        <a href="downloads.php">
            View Downloads
        </a>

    </div>


    <div class="tile">

        <small>My Class</small>

        <h2>
            <?= e($user['class_level'] ?? '—') ?>
        </h2>

        <a href="profile.php">
            Edit Profile
        </a>

    </div>

</div>


<section class="section">

    <h2>Quick Access</h2>

    <a class="btn" href="library.php">
        Browse Library
    </a>

    <a class="btn secondary" href="ai.php">
        AI Generator
    </a>

    <a class="btn secondary" href="wallet.php">
        My Wallet
    </a>

    <a class="btn secondary" href="ask.php">
        Ask a Question
    </a>

    <a class="btn secondary" href="review.php">
        Rate and Review
    </a>

</section>


<h2>Latest Announcements</h2>

<?php foreach ($announcements as $announcement): ?>

    <article class="panel">

        <h3>
            <?= e($announcement['title']) ?>
        </h3>

        <p>
            <?= nl2br(e($announcement['body'])) ?>
        </p>

    </article>

<?php endforeach; ?>


<?php page_footer(); ?>
