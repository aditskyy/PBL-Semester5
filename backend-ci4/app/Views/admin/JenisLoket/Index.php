<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<h2 class="mb-4">Manajemen Jenis Loket</h2>

<a href="/admin/jenisLoket/create" class="btn btn-primary mb-3">Tambah Jenis Loket</a>

<?php if(session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Kode Jenis</th>
                    <th>Nama Jenis</th>
                    <th>Keterangan</th>
                    <th width="150px">Aksi</th>
                </tr>
            </thead>
            <tbody>

                <?php if (!empty($jenisLoket)): ?>
                    <?php foreach ($jenisLoket as $row): ?>
                        <tr>
                            <td><?= esc($row['kode_jenis']) ?></td>
                            <td><?= esc($row['nama_jenis']) ?></td>
                            <td><?= esc($row['keterangan']) ?></td>
                            <td>
                                <a href="<?= base_url('admin/jenisLoket/edit/' . $row['kode_jenis']) ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="<?= base_url('admin/jenisLoket/delete/' . $row['kode_jenis']) ?>"
                                   onclick="return confirm('Yakin hapus data ini?')" 
                                   class="btn btn-danger btn-sm">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">Belum ada data.</td>
                    </tr>
                <?php endif; ?>

            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
