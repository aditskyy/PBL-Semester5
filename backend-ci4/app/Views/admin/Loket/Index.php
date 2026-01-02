<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<h2 class="mb-4">Manajemen Loket</h2>

<?php if(session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>

<a href="/admin/loket/create" class="btn btn-primary mb-3">Tambah Loket</a>

<table class="table table-bordered table-striped">
    <thead class="table-dark">
        <tr>
            <th>Kode</th>
            <th>Nama Loket</th>
            <th>Jenis</th>
            <th>Warna</th> <th>Icon</th>  <th width="150px">Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($loket as $l) : ?>
        <tr>
            <td><?= $l['kode_loket'] ?></td>
            <td><?= $l['nama_loket'] ?></td>
            <td><span class="badge bg-secondary"><?= $l['kode_jenis'] ?></span></td>
            <td>
                <div style="width: 20px; height: 20px; background: <?= $l['warna'] ?>; border-radius: 3px; display: inline-block; vertical-align: middle;"></div>
                <?= $l['warna'] ?>
            </td>
            <td><i class="bi bi-<?= $l['icon'] ?>"></i> <?= $l['icon'] ?></td>
            <td>
                <a href="/admin/loket/edit/<?= $l['kode_loket'] ?>" class="btn btn-warning btn-sm">Edit</a>
                <a href="/admin/loket/delete/<?= $l['kode_loket'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus?')">Delete</a>
            </td>
        </tr>
        <?php endforeach ?>
    </tbody>
</table>

<?= $this->endSection() ?>
