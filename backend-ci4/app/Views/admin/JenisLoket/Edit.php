<?= $this->extend('admin/Layout') ?>
<?= $this->section('content') ?>

<div class="container-fluid px-4">
    <h4 class="mt-4 mb-4">Edit Jenis Loket</h4>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="post" action="<?= base_url('admin/jenisLoket/update/' . $jenis['kode_jenis']) ?>">

                <div class="mb-3">
                    <label class="form-label">Kode Jenis (Tidak Bisa Diubah)</label>
                    <input type="text" class="form-control" value="<?= $jenis['kode_jenis'] ?>" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Jenis</label>
                    <input type="text" name="nama_jenis" class="form-control" 
                           value="<?= $jenis['nama_jenis'] ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Keterangan</label>
                    <textarea name="keterangan" class="form-control"><?= $jenis['keterangan'] ?></textarea>
                </div>

                <button class="btn btn-primary">Update</button>
                <a href="<?= base_url('admin/jenisLoket') ?>" class="btn btn-secondary">Kembali</a>

            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
