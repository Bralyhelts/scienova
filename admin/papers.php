<?php

require __DIR__ . '/../config/bootstrap.php';

require_admin();

if (isset($_GET['delete'])) {

    $id = (int)$_GET['delete'];

    $stmt = $pdo->prepare(
        "SELECT stored_name
         FROM papers
         WHERE id = ?"
    );

    $stmt->execute([$id]);

    $paper = $stmt->fetch();

    if ($paper) {

        $stmt = $pdo->prepare(
            "DELETE FROM papers WHERE id = ?"
        );

        $stmt->execute([$id]);

        $path = cfg('upload_dir')
            . '/'
            . $paper['stored_name'];

        if (is_file($path)) {

            unlink($path);

        }

        flash(
            'success',
            'Paper deleted successfully.'
        );

    }

    redirect('papers.php');

}


$papers = $pdo->query(

    "SELECT p.*, u.name AS uploader

     FROM papers p

     JOIN users u
        ON u.id = p.uploaded_by

     ORDER BY p.id DESC"

);

page_header('Manage Papers');

?>

<h1>MANAGE PAST PAPERS</h1>

<a class="btn" href="upload.php">
    Upload New Paper
</a>


<table>

    <tr>

        <th>Title</th>

        <th>Subject</th>

        <th>Class</th>

        <th>Stream</th>

        <th>Price</th>

        <th>Action</th>

    </tr>


    <?php foreach ($papers as $paper): ?>

        <tr>

            <td>
                <?= e($paper['title']) ?>
            </td>

            <td>
                <?= e($paper['subject']) ?>
            </td>

            <td>
                <?= e($paper['class_level']) ?>
            </td>

            <td>
                <?= e($paper['stream']) ?>
            </td>

            <td>

                <?= $paper['is_free']
                    ? 'FREE'
                    : money($paper['price']) ?>

            </td>

            <td>

                <a
                    href="?delete=<?= (int)$paper['id'] ?>"
                    onclick="return confirm('Delete this paper?')"
                >

                    Delete

                </a>

            </td>

        </tr>

    <?php endforeach; ?>

</table>


<?php page_footer(); ?>
