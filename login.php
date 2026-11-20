<?php

require __DIR__ . '/config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $contact = trim($_POST['contact'] ?? '');

    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare(

        "SELECT *
         FROM users
         WHERE email = ? OR phone = ?
         LIMIT 1"

    );

    $stmt->execute([
        $contact,
        $contact
    ]);

    $user = $stmt->fetch();

    if (
        $user &&
        $user['status'] === 'active' &&
        password_verify(
            $password,
            $user['password_hash']
        )
    ) {

        session_regenerate_id(true);

        $_SESSION['user_id'] = (int)$user['id'];

        audit(
            (int)$user['id'],
            'login'
        );

        redirect('dashboard.php');

    } else {

        $error = 'Incorrect login details or inactive account.';

    }

}

page_header('Login');

?>

<h1>Welcome Back to ELITESHELF</h1>

<?php if (!empty($error)): ?>

    <div class="notice error">

        <?= e($error) ?>

    </div>

<?php endif; ?>


<form method="POST" class="panel">

    <?= csrf_field() ?>

    <label>Email or Telephone Number</label>

    <input
        type="text"
        name="contact"
        required
    >

    <label>Password</label>

    <input
        type="password"
        name="password"
        required
    >

    <button class="btn">

        Login

    </button>

    <p>

        Don't have an account?

        <a href="register.php">
            Create Account
        </a>

    </p>

</form>


<?php page_footer(); ?>
