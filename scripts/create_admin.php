<?php

require __DIR__ . '/../config/bootstrap.php';

$email = cfg('admin_seed_email');

$password = cfg('admin_seed_password');


if (
    !$email ||
    !$password ||
    strlen($password) < 14
) {

    exit(
        "Configure a valid administrator email and password first.\n"
    );

}


$stmt = $pdo->prepare(

    "SELECT id

     FROM users

     WHERE email = ?"

);

$stmt->execute([$email]);


if ($stmt->fetchColumn()) {

    exit(
        "An account with this email already exists.\n"
    );

}


$stmt = $pdo->prepare(

    "INSERT INTO users

    (
        name,
        email,
        user_type,
        password_hash,
        role
    )

    VALUES (?, ?, 'admin', ?, 'admin')"

);


$stmt->execute([

    'ELITESHELF Administrator',

    $email,

    password_hash(
        $password,
        PASSWORD_DEFAULT
    )

]);


echo "Administrator account created successfully.\n";

echo "Remove the seed password and delete this script now.\n";
