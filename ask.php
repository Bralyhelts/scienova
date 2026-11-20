<?php

require __DIR__ . '/config/bootstrap.php';

$user = require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    require_post();

    $subject = trim($_POST['subject'] ?? '');

    $question = trim($_POST['question'] ?? '');

    if (
        $subject !== '' &&
        strlen($question) > 5
    ) {

        $stmt = $pdo->prepare(

            "INSERT INTO questions
            (user_id, subject, question)

            VALUES (?, ?, ?)"

        );

        $stmt->execute([

            $user['id'],

            $subject,

            $question

        ]);

        flash(
            'success',
            'Your question has been sent.'
        );

    } else {

        flash(
            'error',
            'Please complete both fields.'
        );

    }

    redirect('ask.php');

}


$stmt = $pdo->prepare(

    "SELECT *

     FROM questions

     WHERE user_id = ?

     ORDER BY id DESC"

);

$stmt->execute([$user['id']]);

page_header('Ask a Question');

?>

<h1>CONTACT THE ELITESHELF TEAM</h1>

<form class="panel" method="POST">

    <?= csrf_field() ?>

    <label>Subject</label>

    <input
        type="text"
        name="subject"
        required
    >

    <label>Your Question</label>

    <textarea
        name="question"
        required
    ></textarea>

    <button class="btn">
        Send Question
    </button>

</form>


<h2>My Questions</h2>

<?php foreach ($stmt as $question): ?>

    <article class="panel">

        <h3>
            <?= e($question['subject']) ?>
        </h3>

        <p>
            <?= nl2br(e($question['question'])) ?>
        </p>

        <span class="tag">

            <?= e($question['status']) ?>

        </span>

        <?php if ($question['reply']): ?>

            <p>

                <strong>Administrator Reply:</strong>

                <?= nl2br(e($question['reply'])) ?>

            </p>

        <?php endif; ?>

    </article>

<?php endforeach; ?>


<?php page_footer(); ?>
