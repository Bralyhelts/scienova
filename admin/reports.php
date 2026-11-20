<?php

require __DIR__ . '/../config/bootstrap.php';

require_admin();

if (($_GET['type'] ?? '') === 'payments') {

    $stmt = $pdo->query(

        "SELECT
            u.name,
            u.email,
            u.phone,
            t.type,
            t.amount,
            t.reference,
            t.description,
            t.created_at

         FROM wallet_transactions t

         JOIN users u
            ON u.id = t.user_id

         ORDER BY t.id DESC"

    );

    $filename = 'eliteshelf-payments.csv';

    $headers = [

        'Name',
        'Email',
        'Phone',
        'Type',
        'Amount UGX',
        'Reference',
        'Description',
        'Date'

    ];

} else {

    $stmt = $pdo->query(

        "SELECT
            id,
            name,
            email,
            phone,
            user_type,
            class_level,
            balance,
            status,
            created_at

         FROM users

         ORDER BY id"

    );

    $filename = 'eliteshelf-members.csv';

    $headers = [

        'ID',
        'Name',
        'Email',
        'Phone',
        'User Type',
        'Class',
        'Balance UGX',
        'Status',
        'Date Created'

    ];

}


header('Content-Type: text/csv; charset=utf-8');

header(
    'Content-Disposition: attachment; filename="' .
    $filename . '"'
);

$output = fopen('php://output', 'w');

fputcsv($output, $headers);

foreach ($stmt as $row) {

    fputcsv($output, array_values($row));

}

fclose($output);

exit;
