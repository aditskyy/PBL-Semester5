<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<h3>Edit User</h3>

<?php if(session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger">
        <?php foreach(session()->getFlashdata('errors') as $err): ?>
            <div><?= esc($err) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form action="<?= base_url('admin/users/update/'.$user['id']) ?>" method="post">
    <div class="mb-3">
        <label>Username</label>
        <input type="text" name="username" class="form-control"
               value="<?= old('username', $user['username']) ?>" required>
    </div>

    <div class="mb-3">
        <label>Password (kosongkan jika tidak ingin mengganti)</label>
        <input type="password" name="password" class="form-control">
        <small class="text-muted">Isi hanya jika ingin mengganti password (min 4 karakter)</small>
    </div>

    <div class="mb-3">
        <label>Role</label>
        <select name="role" class="form-control" required>
            <option value="admin" <?= (old('role', $user['role']) == 'admin') ? 'selected' : '' ?>>Admin</option>
            <option value="operator" <?= (old('role', $user['role']) == 'operator') ? 'selected' : '' ?>>Operator</option>
            <option value="guest" <?= (old('role', $user['role']) == 'guest') ? 'selected' : '' ?>>Guest</option>
        </select>
    </div>

    <button class="btn btn-primary">Update</button>
    <a href="<?= base_url('admin/users') ?>" class="btn btn-secondary">Kembali</a>
</form>

<?= $this->endSection() ?>
