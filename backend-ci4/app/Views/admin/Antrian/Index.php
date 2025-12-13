<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<h3>Manajemen Antrian</h3>

<?php if(session()->getFlashdata('success')): ?>
    <div class="alert alert-success">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<div class="table-responsive mt-3">
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID Antrian</th>
                <th>Kode Jenis</th>
                <th>Kode Loket</th>
                <th>Nomor</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th width="150px">Aksi</th>
            </tr>
        </thead>

        <tbody>
            <?php if (!empty($antrian)) : ?>
                <?php foreach ($antrian as $row): ?>
                    <tr>
                        <td><?= esc($row['id_antrian']) ?></td>
                        <td><?= esc($row['kode_jenis']) ?></td>
                        <td><?= esc($row['kode_loket']) ?></td>
                        <td><strong><?= esc($row['nomor']) ?></strong></td>
                        <td><?= esc($row['tanggal']) ?></td>

                        <td>
                            <?php if ($row['status'] == 'Menunggu'): ?>
                                <span class="badge bg-warning text-dark">Menunggu</span>
                            <?php elseif ($row['status'] == 'Diproses'): ?>
                                <span class="badge bg-primary">Diproses</span>
                            <?php else: ?>
                                <span class="badge bg-success">Selesai</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <a href="<?= base_url('admin/antrian/delete/'.$row['id_antrian']) ?>" 
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Yakin ingin menghapus antrian ini?')">
                               Hapus
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">Tidak ada data antrian</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>
