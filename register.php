<?php

require __DIR__ . '/config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $name = trim($_POST['name'] ?? '');

    $contact = trim($_POST['contact'] ?? '');

    $type = $_POST['user_type'] ?? 'student';

    $class = $_POST['class_level'] ?? 'S5';

    $password = $_POST['password'] ?? '';

    if (
        strlen($name) < 2 ||
        strlen($password) < 10
    ) {

        $error = 'Enter valid information. Password must be at least 10 characters.';

    } elseif (
        !in_array($type, ['parent','student','teacher'], true) ||
        !in_array($class, ['S5','S6'], true)
    ) {

        $error = 'Invalid account category or class.';

    } elseif (
        !filter_var($contact, FILTER_VALIDATE_EMAIL) &&
        !preg_match('/^[+0-9][0-9+ ]{7,18}$/', $contact)
    ) {

        $error = 'Enter a valid email or telephone number.';

    } else {

        $email = filter_var(
            $contact,
            FILTER_VALIDATE_EMAIL
        ) ? $contact : null;

        $phone = $email ? null : $contact;

        try {

            $stmt = $pdo->prepare(

                "INSERT INTO users
                (
                    name,
                    email,
                    phone,
                    user_type,
                    class_level,
                    password_hash
                )

                VALUES (?, ?, ?, ?, ?, ?)"

            );

            $stmt->execute([

                $name,

                $email,

                $phone,

                $type,

                $class,

                password_hash(
                    $password,
                    PASSWORD_DEFAULT
                )

            ]);

            $_SESSION['user_id'] = $pdo->lastInsertId();

            session_regenerate_id(true);

            audit(
                (int)$_SESSION['user_id'],
                'register'
            );

            flash(
                'success',
                'Your ELITESHELF account has been created.'
            );

            redirect('dashboard.php');

        } catch (PDOException $e) {

            $error = 'This email or telephone number is already registered.';

        }

    }

}

page_header('Create Account');

?>

<h1>Create Your ELITESHELF Account</h1>

<?php if (!empty($error)): ?>

    <div class="notice error">
        <?= e($error) ?>
    </div>

<?php endif; ?>


<form method="POST" class="panel">

    <?= csrf_field() ?>

    <label>Full Name</label>

    <input
        type="text"
        name="name"
        required
    >


    <label>Email or Telephone Number</label>

    <input
        type="text"
        name="contact"
        required
    >


    <label>Who Are You?</label>

    <select name="user_type">

        <option value="student">Student</option>

        <option value="teacher">Teacher</option>

        <option value="parent">Parent</option>

    </select>


    <label>Class</label>

    <select name="class_level">

        <option value="S5">Senior Five</option>

        <option value="S6">Senior Six</option>

    </select>


    <label>Create Password</label>

    <input
        type="password"
        name="password"
        minlength="10"
        required
    >

    <button class="btn" type="submit">
        Create Account
    </button>

    <p>
        Already have an account?

        <a href="login.php">Login here</a>
    </p>

</form>


<?php page_footer(); ?>
