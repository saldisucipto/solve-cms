<?php

use App\Helpers\FlashSession;
use App\Core\Csrf;
?>

<h2>Admin Login</h2>
<?php if ($msg = FlashSession::get('flash_error')): ?>
    <p style="color:red"><?= htmlspecialchars($msg) ?></p>
    <?php FlashSession::get('flash_error'); ?>
<?php endif; ?>

<?php if ($msg = FlashSession::get('csrf_error')): ?>
    <p style="color:red"><?= htmlspecialchars($msg) ?></p>
    <?php FlashSession::get('csrf_error'); ?>
<?php endif; ?>
<form method="POST" action="/login">
    <input type="text" name="_token" hidden value=" <?= Csrf::token() ?>">
    <input type="email" name="email" placeholder="Email" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>
    <button type="submit">Login</button>
</form>