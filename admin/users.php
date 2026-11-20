<?php

require __DIR__ . '/../config/bootstrap.php';

$admin = require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    require_post();

    $id = (int)$_POST['id'];

    $status = $_POST['status'] === 'suspended'
        ? 'suspended'
        : 'active';

    if ($id !== (int)$admin['id']) {

        $stmt = $pdo->prepare(

            "UPDATE users

             SET status = ?

             WHERE id = ?

             AND role <> 'admin'"

        );

        $stmt->execute([
            $status,
            $id
        ]);

    }

    flash(
        'success',
        'User status updated.'
    );

    redirect('users.php');

}


$users = $pdo->query(

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

     ORDER BY id DESC"

);

page_header('Manage Members');

?>

<h1>REGISTERED ELITESHELF MEMBERS</h1>

<table>

    <tr>

        <th>Name</th>

        <th>Contact</th>

        <th>Category</th>

        <th>Balance</th>

        <th>Status</th>

        <th>Action</th>

    </tr>


    <?php foreach ($users as $user): ?>

        <tr>

            <td>
                <?= e($user['name']) ?>
            </td>

            <td>
                <?= e($user['email'] ?? $user['phone']) ?>
            </td>

            <td>
                <?= e($user['user_type']) ?>
                /
                <?= e($user['class_level'] ?? '—') ?>
            </td>

            <td>
                <?= money($user['balance']) ?>
            </td>

            <td>
                <?= e($user['status']) ?>
            </td>

            <td>

                <?php if ($user['id'] !== $admin['id']): ?>

                    <form method="POST">

                        <?= csrf_field() ?>

                        <input
                            type="hidden"
                            name="id"
                            value="<?= (int)$user['id'] ?>"
                        >

                        <input
                            type="hidden"
                            name="status"
                            value="<?= $user['status'] === 'active'
                                ? 'suspended'
                                : 'active' ?>"
                        >

                        <button class="btn small">

                            <?= $user['status'] === 'active'
                                ? 'Suspend'
                                : 'Activate' ?>

                        </button>

                    </form>

                <?php endif; ?>

            </td>

        </tr>

    <?php endforeach; ?>

</table>


<?php page_footer(); ?>
