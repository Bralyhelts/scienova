<?php

require __DIR__ . '/config/bootstrap.php';

$user = require_login();

$stmt = $pdo->prepare(

    "SELECT
        d.*,
        p.title,
        p.subject

     FROM downloads d

     JOIN papers p
        ON p.id = d.paper_id

     WHERE d.user_id = ?

     ORDER BY d.id DESC"

);

$stmt->execute([$user['id']]);

page_header('Download History');

?>

<h1>MY DOWNLOAD HISTORY</h1>

<table>

    <tr>

        <th>Paper</th>

        <th>Subject</th>

        <th>Amount Paid</th>

        <th>Date</th>

    </tr>


    <?php foreach ($stmt as $record): ?>

        <tr>

            <td>
                <?= e($record['title']) ?>
            </td>

            <td>
                <?= e($record['subject']) ?>
            </td>

            <td>
                <?= money($record['amount_paid']) ?>
            </td>

            <td>
                <?= e($record['created_at']) ?>
            </td>

        </tr>

    <?php endforeach; ?>

</table>


<?php page_footer(); ?>
