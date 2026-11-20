<?php

require __DIR__ . '/config/bootstrap.php';

$user = require_login();

$where = [];

$args = [];

$stream = trim($_GET['stream'] ?? '');

$class = trim($_GET['class'] ?? '');

$subject = trim($_GET['subject'] ?? '');

if (in_array($stream, ['Arts', 'Science'], true)) {

    $where[] = "stream = ?";

    $args[] = $stream;

}

if (in_array($class, ['S5', 'S6'], true)) {

    $where[] = "class_level = ?";

    $args[] = $class;

}

if ($subject !== '') {

    $where[] = "subject = ?";

    $args[] = $subject;

}

$sql = "SELECT * FROM papers";

if ($where) {

    $sql .= " WHERE " . implode(" AND ", $where);

}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);

$stmt->execute($args);

$papers = $stmt->fetchAll();

page_header('A-Level Library');

?>

<h1>A-LEVEL PAST PAPERS LIBRARY</h1>

<form class="filters" method="GET">

    <select name="stream">

        <option value="">All Streams</option>

        <option value="Arts">Arts</option>

        <option value="Science">Science</option>

    </select>


    <select name="class">

        <option value="">All Classes</option>

        <option value="S5">S5</option>

        <option value="S6">S6</option>

    </select>


    <input
        type="text"
        name="subject"
        placeholder="Search subject"
    >

    <button class="btn">
        Search Papers
    </button>

</form>


<div class="cards">

<?php foreach ($papers as $paper): ?>

    <article class="tile">

        <span class="tag">

            <?= e($paper['stream']) ?>

            |

            <?= e($paper['class_level']) ?>

        </span>

        <h3>
            <?= e($paper['title']) ?>
        </h3>

        <p>
            Subject: <?= e($paper['subject']) ?>
        </p>

        <p>
            Year: <?= e((string)$paper['year']) ?>
        </p>

        <p>

            <?php if ($paper['is_free']): ?>

                FREE

            <?php else: ?>

                <?= money($paper['price']) ?>

            <?php endif; ?>

        </p>

        <a
            class="btn"
            href="paper.php?id=<?= (int)$paper['id'] ?>"
        >

            View Paper

        </a>

    </article>

<?php endforeach; ?>


<?php if (!$papers): ?>

    <p>No papers found.</p>

<?php endif; ?>

</div>


<?php page_footer(); ?>
