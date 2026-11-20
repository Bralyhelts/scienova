<?php

require __DIR__ . '/config/bootstrap.php';

$user = require_login();

$stmt = $pdo->prepare(
    "SELECT *
     FROM payment_requests
     WHERE user_id = ?
     ORDER BY id DESC"
);

$stmt->execute([$user['id']]);

$requests = $stmt->fetchAll();

page_header('My Wallet');

?>

<h1>MY ELITESHELF WALLET</h1>

<div class="tile">

    <small>Available Balance</small>

    <h2><?= money($user['balance']) ?></h2>

</div>


<div class="panel">

    <h2>Deposit Money</h2>

    <p>
        Send Mobile Money to:
        <strong>0766599663</strong>
    </p>

    <p>
        Enter your transaction reference
        after completing payment.
    </p>


    <form method="POST" action="payment-submit.php">

        <?= csrf_field() ?>

        <label>Amount (UGX)</label>

        <input
            type="number"
            name="amount"
            min="500"
            max="10000000"
            step="500"
            required
        >

        <label>Transaction Reference</label>

        <input
            type="text"
            name="transaction_ref"
            required
        >

        <label>Sender Telephone Number</label>

        <input
            type="text"
            name="payer_phone"
            required
        >

        <button class="btn">

            Submit Payment

        </button>

    </form>

</div>


<h2>Payment History</h2>

<table>

    <tr>

        <th>Date</th>

        <th>Amount</th>

        <th>Reference</th>

        <th>Status</th>

    </tr>

    <?php foreach ($requests as $request): ?>

        <tr>

            <td>
                <?= e($request['created_at']) ?>
            </td>

            <td>
                <?= money($request['amount']) ?>
            </td>

            <td>
                <?= e($request['transaction_ref']) ?>
            </td>

            <td>
                <?= e($request['status']) ?>
            </td>

        </tr>

    <?php endforeach; ?>

</table>


<?php page_footer(); ?>
