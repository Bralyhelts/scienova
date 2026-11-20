<?php

require __DIR__ . '/../config/bootstrap.php';

$admin = require_admin();

$tables = [

    'users',

    'papers',

    'payment_requests',

    'downloads',

    'questions'

];

$counts = [];

foreach ($tables as $table) {

    $counts[$table] = (int)$pdo->query(

        "SELECT COUNT(*) FROM `$table`"

    )->fetchColumn();

}


$pending = (int)$pdo->query(

    "SELECT COUNT(*)

     FROM payment_requests

     WHERE status = 'pending'"

)->fetchColumn();


$total = (int)$pdo->query(

    "SELECT COALESCE(SUM(amount), 0)

     FROM wallet_transactions

     WHERE type = 'credit'"

)->fetchColumn();


page_header('Administrator Dashboard');

?>

<h1>ELITESHELF ADMINISTRATION</h1>

<h2>System Overview</h2>

<div class="cards">

    <?php foreach ($counts as $name => $count): ?>

        <div class="tile">

            <small>
                <?= e(ucwords(
                    str_replace('_', ' ', $name)
                )) ?>
            </small>

            <h2><?= $count ?></h2>

        </div>

    <?php endforeach; ?>

</div>


<div class="cards">

    <div class="tile">

        <small>Pending Payments</small>

        <h2><?= $pending ?></h2>

        <a href="payments.php">
            Verify Payments
        </a>

    </div>


    <div class="tile">

        <small>Total Verified Wallet Credits</small>

        <h2><?= money($total) ?></h2>

    </div>

</div>


<h2>ADMINISTRATION MENU</h2>

<div class="admin-links">

    <a class="tile" href="upload.php">
        Upload Past Papers
    </a>

    <a class="tile" href="papers.php">
        Manage Papers
    </a>

    <a class="tile" href="payments.php">
        Verify Payments
    </a>

    <a class="tile" href="users.php">
        Manage Users
    </a>

    <a class="tile" href="announcements.php">
        Send Announcements
    </a>

    <a class="tile" href="questions.php">
        Answer Questions
    </a>

    <a class="tile" href="reports.php">
        Members Excel Report
    </a>

    <a class="tile" href="reports.php?type=payments">
        Payments Excel Report
    </a>

    <a class="tile" href="backup.php">
        Database Backup
    </a>

</div>


<?php page_footer(); ?>
