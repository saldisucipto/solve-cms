<h2>Dashboard</h2>

<?php if (!empty($success)): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>


<p>Welcome, <?= $admin['name'] ?></p>

<a href="/logout">Logout</a>