<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<h2 class="mb-4">Manajemen User</h2>

<?php if(session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>

<a href="<?= base_url('admin/users/create') ?>" class="btn btn-primary mb-3">Tambah User</a>

<table class="table table-striped table-bordered">
      <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Role</th>
            <th width="150px">Aksi</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($users as $u): ?>
        <tr>
            <td><?= esc($u['id']) ?></td>
            <td><?= esc($u['username']) ?></td>
            <td><?= esc($u['role']) ?></td>
            <td>
                <a href="<?= base_url('admin/users/edit/'.$u['id']) ?>" class="btn btn-warning btn-sm">Edit</a>
                <a href="<?= base_url('admin/users/delete/'.$u['id']) ?>"
                   class="btn btn-danger btn-sm"
                   onclick="return confirm('Yakin ingin menghapus user ini?')">Hapus</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?= $this->endSection() ?>
