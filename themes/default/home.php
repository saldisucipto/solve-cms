<?php

use App\Core\View;

View::extend('layouts/defaults');
?>

<?php View::section('content'); ?>
<h1 class="text-2xl font-bold mb-4">Dashboard</h1>

<div class="bg-white p-4 rounded shadow">
    Selamat datang
</div>
<?php View::endSection(); ?>