<?php

require __DIR__ . '/config/bootstrap.php';

$user = require_login();

require_post();

$paperId = (int)($_POST['paper_id'] ?? 0);

$stmt = $pdo->prepare(
    "SELECT *
     FROM papers
     WHERE id = ?"
);

$stmt->execute([$paperId]);

$paper = $stmt->fetch();

if (!$paper) {

    flash('error', 'Paper not found.');

    redirect('library.php');

}

$cost = (int)$paper['price'];

$pdo->beginTransaction();

try {

    $stmt = $pdo->prepare(
        "SELECT balance
         FROM users
         WHERE id = ?
         FOR UPDATE"
    );

    $stmt->execute([$user['id']]);

    $balance = (int)$stmt->fetchColumn();


    if (!$paper['is_free'] && $balance < $cost) {

        $pdo->rollBack();

        flash(
            'error',
            'Insufficient amount. Please top up your wallet.'
        );

        redirect('wallet.php');

    }


    if (!$paper['is_free'] && $cost > 0) {

        $stmt = $pdo->prepare(
            "UPDATE users
             SET balance = balance - ?
             WHERE id = ?"
        );

        $stmt->execute([
            $cost,
            $user['id']
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

            $user['id'],

            'debit',

            $cost,

            'PAPER-' . $paperId . '-' . bin2hex(random_bytes(4)),

            'Download: ' . $paper['title']

        ]);

    }


    $stmt = $pdo->prepare(
        "INSERT INTO downloads
        (user_id, paper_id, amount_paid)
        VALUES (?, ?, ?)"
    );

    $stmt->execute([

        $user['id'],

        $paperId,

        $cost

    ]);


    $pdo->commit();

    audit(
        (int)$user['id'],
        'paper_download',
        (string)$paperId
    );


} catch (Throwable $e) {

    if ($pdo->inTransaction()) {

        $pdo->rollBack();

    }

    http_response_code(500);

    exit('Unable to complete download.');

}


$file = cfg('upload_dir') . '/' . $paper['stored_name'];

if (!is_file($file)) {

    http_response_code(404);

    exit('Paper file is missing. Contact administrator.');

}

header('Content-Type: application/pdf');

header(
    'Content-Length: ' . filesize($file)
);

header(
    'Content-Disposition: attachment; filename="' .
    safe_file_name($paper['file_name']) . '"'
);

readfile($file);

exit;
