<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<h2 class="mb-4">Manajemen Profile</h2>

<?php if(session()->getFlashdata('success')): ?>
    <div class="alert alert-success">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<table class="table table-striped table-bordered">
      <thead class="table-dark"></thead>
    <tr>
        <th>Nama Instansi</th>
        <td><?= esc($profile['nama_instansi']) ?></td>
    </tr>
    <tr>
        <th>Alamat</th>
        <td><?= esc($profile['alamat']) ?></td>
    </tr>
    <tr>
        <th>Telepon</th>
        <td><?= esc($profile['telp']) ?></td>
    </tr>
    <tr>
        <th>Logo</th>
        <td>
            <img src="<?= base_url('uploads/logo/'.$profile['gambar_logo']) ?>" height="80">
        </td>
    </tr>
</table>

<a href="<?= base_url('admin/profile/edit/'.$profile['id']) ?>" class="btn btn-warning">
    Edit Profile
</a>

<?= $this->endSection() ?>
