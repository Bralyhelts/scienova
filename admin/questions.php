<?php

require __DIR__ . '/../config/bootstrap.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    require_post();

    $id = (int)$_POST['id'];

    $reply = trim($_POST['reply'] ?? '');

    $stmt = $pdo->prepare(

        "UPDATE questions

         SET reply = ?,
             status = 'answered'

         WHERE id = ?"

    );

    $stmt->execute([
        $reply,
        $id
    ]);


    $stmt = $pdo->prepare(

        "SELECT user_id

         FROM questions

         WHERE id = ?"

    );

    $stmt->execute([$id]);

    $userId = $stmt->fetchColumn();


    if ($userId) {

        notify(

            (int)$userId,

            'Your question has been answered',

            $reply

        );

    }


    flash(
        'success',
        'Reply sent successfully.'
    );

    redirect('questions.php');

}


$questions = $pdo->query(

    "SELECT
        q.*,
        u.name

     FROM questions q

     JOIN users u
        ON u.id = q.user_id

     ORDER BY q.id DESC"

);

page_header('User Questions');

?>

<h1>MEMBER QUESTIONS</h1>

<?php foreach ($questions as $question): ?>

    <article class="panel">

        <h3>

            <?= e($question['subject']) ?>

        </h3>

        <p>

            Asked by:
            <?= e($question['name']) ?>

        </p>

        <p>

            <?= nl2br(e($question['question'])) ?>

        </p>


        <?php if ($question['status'] !== 'answered'): ?>

            <form method="POST">

                <?= csrf_field() ?>

                <input
                    type="hidden"
                    name="id"
                    value="<?= (int)$question['id'] ?>"
                >

                <label>Administrator Reply</label>

                <textarea
                    name="reply"
                    required
                ></textarea>

                <button class="btn">

                    Send Reply

                </button>

            </form>

        <?php else: ?>

            <p>

                <strong>Reply:</strong>

                <?= nl2br(e($question['reply'])) ?>

            </p>

        <?php endif; ?>

    </article>

<?php endforeach; ?>


<?php page_footer(); ?>
