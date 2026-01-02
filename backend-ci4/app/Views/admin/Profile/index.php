<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<h2 class="mb-4">Manajemen Profile</h2>

<?php if(session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>

<table class="table table-striped table-bordered">
    <tr>
        <th width="200px">Nama Instansi</th>
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
        <th>Warna Utama Sistem</th>
        <td>
            <div style="width: 50px; height: 20px; background: <?= esc($profile['color_palette']) ?>; border: 1px solid #000;"></div>
            <?= esc($profile['color_palette']) ?>
        </td>
    </tr>
</table>

<a href="<?= base_url('admin/profile/edit/'.$profile['id']) ?>" class="btn btn-warning">Edit Profile</a>

<?= $this->endSection() ?>