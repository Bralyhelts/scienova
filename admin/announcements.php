<?php

require __DIR__ . '/../config/bootstrap.php';

$admin = require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    require_post();

    $title = trim($_POST['title'] ?? '');

    $body = trim($_POST['body'] ?? '');

    if ($title && $body) {

        $stmt = $pdo->prepare(

            "INSERT INTO announcements
            (title, body, created_by)

            VALUES (?, ?, ?)"

        );

        $stmt->execute([

            $title,

            $body,

            $admin['id']

        ]);


        $users = $pdo->query(

            "SELECT id

             FROM users

             WHERE status = 'active'

             AND role = 'user'"

        )->fetchAll(PDO::FETCH_COLUMN);


        foreach ($users as $userId) {

            notify(
                (int)$userId,
                $title,
                $body
            );

        }


        flash(
            'success',
            'Announcement sent successfully.'
        );

        redirect('announcements.php');

    }

}


$announcements = $pdo->query(

    "SELECT *

     FROM announcements

     ORDER BY id DESC"

);

page_header('Announcements');

?>

<h1>SEND ANNOUNCEMENTS</h1>

<form class="panel" method="POST">

    <?= csrf_field() ?>

    <label>Announcement Title</label>

    <input
        type="text"
        name="title"
        required
    >

    <label>Announcement Message</label>

    <textarea
        name="body"
        required
    ></textarea>

    <button class="btn">

        Publish Announcement

    </button>

</form>


<h2>Previous Announcements</h2>

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
