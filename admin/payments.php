<?php

require __DIR__ . '/../config/bootstrap.php';

$admin = require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    require_post();

    $id = (int)$_POST['id'];

    $decision = $_POST['decision'] ?? '';

    $note = trim($_POST['note'] ?? '');

    $pdo->beginTransaction();

    try {

        $stmt = $pdo->prepare(

            "SELECT *

             FROM payment_requests

             WHERE id = ?

             FOR UPDATE"

        );

        $stmt->execute([$id]);

        $payment = $stmt->fetch();


        if (
            !$payment ||
            $payment['status'] !== 'pending'
        ) {

            throw new RuntimeException(
                'Payment is no longer pending.'
            );

        }


        $status = $decision === 'approve'
            ? 'approved'
            : 'rejected';


        $stmt = $pdo->prepare(

            "UPDATE payment_requests

             SET status = ?,
                 admin_note = ?,
                 reviewed_at = NOW(),
                 reviewed_by = ?

             WHERE id = ?"

        );

        $stmt->execute([

            $status,

            $note,

            $admin['id'],

            $id

        ]);


        if ($status === 'approved') {

            $stmt = $pdo->prepare(

                "UPDATE users

                 SET balance = balance + ?

                 WHERE id = ?"

            );

            $stmt->execute([

                $payment['amount'],

                $payment['user_id']

            ]);


            $stmt = $pdo->prepare(

                "INSERT INTO wallet_transactions

                (
                    user_id,
                    type,
                    amount,
                    reference,
                    description
                )

                VALUES (?, ?, ?, ?, ?)"

            );

            $stmt->execute([

                $payment['user_id'],

                'credit',

                $payment['amount'],

                'TOPUP-' . $payment['id'],

                'Verified Mobile Money top-up'

            ]);

        }


        notify(

            (int)$payment['user_id'],

            'Payment ' . $status,

            $status === 'approved'
                ? 'Your wallet has been credited.'
                : 'Your payment was rejected. ' . $note

        );


        $pdo->commit();

        flash(
            'success',
            'Payment ' . $status . '.'
        );

    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {

            $pdo->rollBack();

        }

        flash('error', $e->getMessage());

    }

    redirect('payments.php');

}


$payments = $pdo->query(

    "SELECT
        r.*,
        u.name,
        u.phone,
        u.email

     FROM payment_requests r

     JOIN users u
        ON u.id = r.user_id

     ORDER BY r.id DESC"

);

page_header('Payment Verification');

?>

<h1>MOBILE MONEY PAYMENT VERIFICATION</h1>

<table>

    <tr>

        <th>User</th>

        <th>Amount</th>

        <th>Transaction</th>

        <th>Status</th>

        <th>Action</th>

    </tr>


    <?php foreach ($payments as $payment): ?>

        <tr>

            <td>

                <?= e($payment['name']) ?>

                <br>

                <?= e($payment['phone'] ?? '') ?>

            </td>


            <td>

                <?= money($payment['amount']) ?>

            </td>


            <td>

                <?= e($payment['transaction_ref']) ?>

                <br>

                <?= e($payment['payer_phone']) ?>

            </td>


            <td>

                <?= e($payment['status']) ?>

            </td>


            <td>

                <?php if ($payment['status'] === 'pending'): ?>

                    <form method="POST">

                        <?= csrf_field() ?>

                        <input
                            type="hidden"
                            name="id"
                            value="<?= (int)$payment['id'] ?>"
                        >

                        <input
                            name="note"
                            placeholder="Admin note"
                        >

                        <button
                            class="btn small"
                            name="decision"
                            value="approve"
                        >

                            Approve

                        </button>

                        <button
                            class="btn danger small"
                            name="decision"
                            value="reject"
                        >

                            Reject

                        </button>

                    </form>

                <?php else: ?>

                    <?= e($payment['admin_note'] ?? '') ?>

                <?php endif; ?>

            </td>

        </tr>

    <?php endforeach; ?>

</table>


<?php page_footer(); ?>
