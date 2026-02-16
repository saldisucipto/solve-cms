<?php

use App\Core\View;
use App\Helpers\FlashSession;

View::extend('layouts/auth');
?>

<?php View::section('content'); ?>
<div class="form-container-sm">
    <?php if ($msg = FlashSession::get('flash_error')): ?>
        <p style="color:red"><?= htmlspecialchars($msg) ?></p>
        <?php FlashSession::get('flash_error'); ?>
    <?php endif; ?>

    <?php if ($msg = FlashSession::get('csrf_error')): ?>
        <p style="color:red"><?= htmlspecialchars($msg) ?></p>
        <?php FlashSession::get('csrf_error'); ?>
    <?php endif; ?>
    <form action="#" class=" form-container">
        <div class=" text-center flex flex-col ">
            <h3 class="title-text">Login</h3>
            <p class="subtitle-text">Login Untuk Akses Sistem Kamu !</p>
        </div>

        <div class="input-with-label">
            <label class="label-input" for="">Your Email</label>
            <input placeholder="Your Email" class=" input " type="email" name="" id="">
        </div>
        <div class="input-with-label">
            <label class="label-input" for="">Password</label>
            <input placeholder="Your Email" class=" input " type="password" name="" id="">
        </div>
        <button class="button-primary">Login</button>
    </form>
</div>
<?php View::endSection(); ?>