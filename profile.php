<?php

require __DIR__ . '/config/bootstrap.php';

$user = require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    require_post();

    $name = trim($_POST['name'] ?? '');

    $picture = $user['profile_pic'];

    if (strlen($name) < 2) {

        $error = 'Enter a valid name.';

    } else {

        if (!empty($_FILES['profile_pic']['name'])) {

            $file = $_FILES['profile_pic'];

            $mime = (
                new finfo(FILEINFO_MIME_TYPE)
            )->file($file['tmp_name']);

            $allowed = [

                'image/jpeg' => 'jpg',

                'image/png' => 'png',

                'image/webp' => 'webp'

            ];

            if (
                $file['error'] !== UPLOAD_ERR_OK ||
                !isset($allowed[$mime]) ||
                $file['size'] > 3 * 1024 * 1024
            ) {

                $error = 'Upload a JPG, PNG or WebP image up to 3 MB.';

            } else {

                $directory = __DIR__ . '/storage/profiles';

                if (!is_dir($directory)) {

                    mkdir($directory, 0750, true);

                }

                $filename = bin2hex(random_bytes(16))
                    . '.' . $allowed[$mime];

                if (
                    move_uploaded_file(
                        $file['tmp_name'],
                        $directory . '/' . $filename
                    )
                ) {

                    $picture = 'storage/profiles/' . $filename;

                } else {

                    $error = 'Could not upload your profile picture.';

                }

            }

        }

        if (empty($error)) {

            $stmt = $pdo->prepare(

                "UPDATE users

                 SET name = ?, profile_pic = ?

                 WHERE id = ?"

            );

            $stmt->execute([

                $name,

                $picture,

                $user['id']

            ]);

            flash(
                'success',
                'Your profile has been updated.'
            );

            redirect('profile.php');

        }

    }

}

$user = current_user();

page_header('My Profile');

?>

<h1>MY PROFILE</h1>

<?php if (!empty($error)): ?>

    <div class="notice error">

        <?= e($error) ?>

    </div>

<?php endif; ?>


<?php if ($user['profile_pic']): ?>

    <img
        class="avatar"
        src="profile-image.php"
        alt="Profile picture"
    >

<?php endif; ?>


<form
    class="panel"
    method="POST"
    enctype="multipart/form-data"
>

    <?= csrf_field() ?>

    <label>Full Name</label>

    <input
        type="text"
        name="name"
        value="<?= e($user['name']) ?>"
        required
    >

    <label>Profile Picture</label>

    <input
        type="file"
        name="profile_pic"
        accept="image/jpeg,image/png,image/webp"
    >

    <p>
        Email: <?= e($user['email'] ?? 'Not provided') ?>
    </p>

    <p>
        Phone: <?= e($user['phone'] ?? 'Not provided') ?>
    </p>

    <button class="btn">
        Save Changes
    </button>

</form>


<?php page_footer(); ?>
