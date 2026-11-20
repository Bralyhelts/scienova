<?php

require __DIR__ . '/config/bootstrap.php';

$user = require_login();

require_post();

$amount = (int)($_POST['amount'] ?? 0);

$reference = trim(
    $_POST['transaction_ref'] ?? ''
);

$phone = trim(
    $_POST['payer_phone'] ?? ''
);


if (
    $amount < 500 ||
    $amount > 10000000 ||
    $reference === '' ||
    $phone === ''
) {

    flash(
        'error',
        'Enter valid payment information.'
    );

    redirect('wallet.php');

}


try {

    $stmt = $pdo->prepare(

        "INSERT INTO payment_requests
        (
            user_id,
            amount,
            transaction_ref,
            payer_phone
        )

        VALUES (?, ?, ?, ?)"

    );

    $stmt->execute([

        $user['id'],

        $amount,

        $reference,

        $phone

    ]);


    notify(

        (int)$user['id'],

        'Payment Submitted',

        'Your payment is awaiting administrator verification.'

    );


    audit(
        (int)$user['id'],
        'payment_submitted',
        $reference
    );


    flash(
        'success',
        'Payment submitted. Your wallet will be credited after verification.'
    );


} catch (PDOException $e) {

    flash(
        'error',
        'This transaction reference may already have been submitted.'
    );

}


redirect('wallet.php');
