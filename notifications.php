<?php

require __DIR__ . '/config/bootstrap.php';

$user = require_login();

$update = $pdo->prepare(

    "UPDATE notifications

     SET is_read = 1

     WHERE user_id = ?"

);

$update->execute([$user['id']]);


$stmt = $pdo->prepare(

    "SELECT *

     FROM notifications

     WHERE user_id = ?

     ORDER BY id DESC

     LIMIT 100"

);

$stmt->execute([$user['id']]);

page_header('Notifications');

?>

<h1>MY NOTIFICATIONS</h1>

<?php foreach ($stmt as $notification): ?>

    <article class="panel">

        <h3>

            <?= e($notification['title']) ?>

        </h3>

        <p>

            <?= nl2br(e($notification['body'])) ?>

        </p>

        <small>

            <?= e($notification['created_at']) ?>

        </small>

    </article>

<?php endforeach; ?>


<?php page_footer(); ?>
