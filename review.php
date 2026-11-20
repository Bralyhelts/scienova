<?php

require __DIR__ . '/config/bootstrap.php';

$user = require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    require_post();

    $rating = (int)($_POST['rating'] ?? 0);

    $comment = trim($_POST['comment'] ?? '');

    if (
        $rating < 1 ||
        $rating > 5 ||
        strlen($comment) < 3
    ) {

        flash(
            'error',
            'Please provide a rating and comment.'
        );

    } else {

        $stmt = $pdo->prepare(

            "INSERT INTO reviews
            (user_id, rating, comment)

            VALUES (?, ?, ?)"

        );

        $stmt->execute([

            $user['id'],

            $rating,

            $comment

        ]);

        flash(
            'success',
            'Thank you for reviewing ELITESHELF.'
        );

    }

    redirect('review.php');

}


$stmt = $pdo->query(

    "SELECT
        r.*,
        u.name

     FROM reviews r

     JOIN users u
        ON u.id = r.user_id

     WHERE r.status = 'visible'

     ORDER BY r.id DESC

     LIMIT 30"

);

page_header('Reviews');

?>

<h1>RATE AND REVIEW ELITESHELF</h1>

<form class="panel" method="POST">

    <?= csrf_field() ?>

    <label>Your Rating</label>

    <select name="rating">

        <option value="5">5 - Excellent</option>

        <option value="4">4 - Very Good</option>

        <option value="3">3 - Good</option>

        <option value="2">2 - Fair</option>

        <option value="1">1 - Poor</option>

    </select>


    <label>Your Comment</label>

    <textarea
        name="comment"
        required
    ></textarea>


    <button class="btn">
        Submit Review
    </button>

</form>


<h2>What Users Say</h2>

<?php foreach ($stmt as $review): ?>

    <article class="panel">

        <strong>
            <?= e($review['name']) ?>
        </strong>

        <p>
            <?= str_repeat('★', (int)$review['rating']) ?>
        </p>

        <p>
            <?= nl2br(e($review['comment'])) ?>
        </p>

    </article>

<?php endforeach; ?>


<?php page_footer(); ?>
