<?php

require __DIR__ . '/../config/bootstrap.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    require_post();

    $password = $_POST['backup_password'] ?? '';

    if (strlen($password) < 12) {

        $error = 'Use at least 12 characters for the backup password.';

    } else {

        $tables = $pdo->query(
            "SHOW TABLES"
        )->fetchAll(PDO::FETCH_COLUMN);

        $sql = "-- ELITESHELF DATABASE BACKUP\n";

        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n";


        foreach ($tables as $table) {

            $create = $pdo->query(

                "SHOW CREATE TABLE `$table`"

            )->fetch(PDO::FETCH_NUM);


            $sql .= "DROP TABLE IF EXISTS `$table`;\n";

            $sql .= $create[1] . ";\n";


            $rows = $pdo->query(

                "SELECT * FROM `$table`"

            )->fetchAll(PDO::FETCH_ASSOC);


            foreach ($rows as $row) {

                $columns = array_map(

                    fn($column) => "`$column`",

                    array_keys($row)

                );


                $values = array_map(

                    fn($value) =>

                        $value === null
                            ? 'NULL'
                            : $pdo->quote((string)$value),

                    array_values($row)

                );


                $sql .= "INSERT INTO `$table` (";

                $sql .= implode(',', $columns);

                $sql .= ") VALUES (";

                $sql .= implode(',', $values);

                $sql .= ");\n";

            }

        }


        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";


        $iv = random_bytes(16);

        $encrypted = openssl_encrypt(

            $sql,

            'aes-256-cbc',

            hash('sha256', $password, true),

            OPENSSL_RAW_DATA,

            $iv

        );


        header(
            'Content-Type: application/octet-stream'
        );

        header(
            'Content-Disposition: attachment; filename="eliteshelf-backup.enc"'
        );


        echo "ELITESHELF1";

        echo base64_encode($iv);

        echo "\n";

        echo base64_encode($encrypted);

        exit;

    }

}

page_header('Database Backup');

?>

<h1>ELITESHELF DATABASE BACKUP</h1>

<div class="panel">

    <p>
        Generate an encrypted database backup.
    </p>

    <p>
        Keep your backup password separately.
        Uploaded PDF files must be backed up separately.
    </p>

    <?php if (!empty($error)): ?>

        <div class="notice error">

            <?= e($error) ?>

        </div>

    <?php endif; ?>


    <form method="POST">

        <?= csrf_field() ?>

        <label>Backup Password</label>

        <input
            type="password"
            name="backup_password"
            minlength="12"
            required
        >

        <button class="btn">

            Generate Backup

        </button>

    </form>

</div>

<?php page_footer(); ?>
