<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'Solve CMS' ?></title>
</head>

<body>

    <?php require __DIR__ . '/../partials/header.php'; ?>

    <main>
        <?= $content ?>
    </main>

    <?php require __DIR__ . '/../partials/footer.php'; ?>

</body>

</html>