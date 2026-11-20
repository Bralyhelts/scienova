<?php

require __DIR__ . '/config/bootstrap.php';

$user = require_login();

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare(
    "SELECT *
     FROM papers
     WHERE id = ?"
);

$stmt->execute([$id]);

$paper = $stmt->fetch();

if (!$paper) {

    http_response_code(404);

    exit('Paper not found.');

}

page_header('Paper Details');

?>

<div class="panel">

    <span class="tag">

        <?= e($paper['stream']) ?>

        |

        <?= e($paper['class_level']) ?>

    </span>

    <h1>
        <?= e($paper['title']) ?>
    </h1>

    <p>
        Subject: <?= e($paper['subject']) ?>
    </p>

    <p>
        Year: <?= e((string)$paper['year']) ?>
    </p>

    <p>
        <?= e($paper['description'] ?? '') ?>
    </p>

    <p>

        File size:

        <?= number_format(
            $paper['file_size'] / 1048576,
            2
        ) ?>

        MB

    </p>


    <h2>

        <?php if ($paper['is_free']): ?>

            FREE DOWNLOAD

        <?php else: ?>

            <?= money($paper['price']) ?>

        <?php endif; ?>

    </h2>


    <form method="POST" action="download.php">

        <?= csrf_field() ?>

        <input
            type="hidden"
            name="paper_id"
            value="<?= (int)$paper['id'] ?>"
        >

        <button
            class="btn"
            data-loading="Preparing secure download..."
        >

            Download Paper

        </button>

    </form>

    <p class="muted">

        Please wait five seconds while
        ELITESHELF prepares your paper.

    </p>

</div>


<?php page_footer(); ?>
