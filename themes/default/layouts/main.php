<?php

use App\Core\Config; ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title><?= $title ?? Config::get('app.name') ?></title>
</head>

<body>

    <?php require __DIR__ . '/../partials/header.php'; ?>

    <main>
        <?= $content ?>
    </main>

    <?php require __DIR__ . '/../partials/footer.php'; ?>

</body>

</html>