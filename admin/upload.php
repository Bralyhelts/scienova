<?php

require __DIR__ . '/../config/bootstrap.php';

$admin = require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    require_post();

    $file = $_FILES['paper'] ?? null;

    $title = trim($_POST['title'] ?? '');

    $subject = trim($_POST['subject'] ?? '');

    $stream = $_POST['stream'] ?? '';

    $class = $_POST['class_level'] ?? '';

    $year = (int)($_POST['year'] ?? 0);

    $free = isset($_POST['is_free']);


    if (
        !$file ||
        $file['error'] !== UPLOAD_ERR_OK ||
        $file['size'] > cfg('max_upload_bytes') ||
        $title === '' ||
        $subject === '' ||
        !in_array($stream, ['Arts','Science'], true) ||
        !in_array($class, ['S5','S6'], true)
    ) {

        $error = 'Check your information and file size.';

    } else {

        $mime = (
            new finfo(FILEINFO_MIME_TYPE)
        )->file($file['tmp_name']);

        if ($mime !== 'application/pdf') {

            $error = 'Only PDF documents are allowed.';

        } else {

            $directory = cfg('upload_dir');

            if (!is_dir($directory)) {

                mkdir($directory, 0750, true);

            }

            $stored = bin2hex(
                random_bytes(20)
            ) . '.pdf';


            if (
                move_uploaded_file(
                    $file['tmp_name'],
                    $directory . '/' . $stored
                )
            ) {

                $price = $free
                    ? 0
                    : paper_price((int)$file['size']);


                $stmt = $pdo->prepare(

                    "INSERT INTO papers

                    (
                        title,
                        subject,
                        stream,
                        class_level,
                        year,
                        description,
                        file_name,
                        stored_name,
                        file_size,
                        price,
                        is_free,
                        uploaded_by
                    )

                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"

                );

                $stmt->execute([

                    $title,

                    $subject,

                    $stream,

                    $class,

                    $year ?: null,

                    trim($_POST['description'] ?? ''),

                    safe_file_name($file['name']),

                    $stored,

                    $file['size'],

                    $price,

                    $free ? 1 : 0,

                    $admin['id']

                ]);


                flash(
                    'success',
                    'Paper uploaded successfully.'
                );

                redirect('papers.php');

            } else {

                $error = 'File upload failed.';

            }

        }

    }

}

page_header('Upload Papers');

?>

<h1>UPLOAD PAST PAPERS</h1>

<?php if (!empty($error)): ?>

    <div class="notice error">

        <?= e($error) ?>

    </div>

<?php endif; ?>


<form
    class="panel"
    method="POST"
    enctype="multipart/form-data"
>

    <?= csrf_field() ?>


    <label>Paper Title</label>

    <input
        type="text"
        name="title"
        required
    >


    <label>Subject</label>

    <input
        type="text"
        name="subject"
        required
    >


    <label>Stream</label>

    <select name="stream">

        <option value="Arts">Arts</option>

        <option value="Science">Science</option>

    </select>


    <label>Class</label>

    <select name="class_level">

        <option value="S5">Senior Five</option>

        <option value="S6">Senior Six</option>

    </select>


    <label>Year</label>

    <input
        type="number"
        name="year"
        min="2000"
        max="2100"
    >


    <label>Description</label>

    <textarea name="description"></textarea>


    <label>Choose PDF Paper</label>

    <input
        type="file"
        name="paper"
        accept="application/pdf"
        required
    >


    <label class="inline">

        <input
            type="checkbox"
            name="is_free"
        >

        Make this paper FREE

    </label>


    <button class="btn">

        Upload Paper

    </button>

</form>


<?php page_footer(); ?>
