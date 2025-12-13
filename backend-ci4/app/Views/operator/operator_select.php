<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pilih Loket Operator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="card shadow p-4 mx-auto" style="max-width: 420px;">
        <h4 class="text-center mb-4">Pilih Loket Operator</h4>

        <form action="<?= site_url('operator/setOperatorSession') ?>" method="post">
            <!-- Pilih Jenis Layanan -->
            <div class="mb-3">
                <label for="kode_jenis" class="form-label fw-semibold">Pilih Jenis Layanan</label>
                <select id="kode_jenis" name="kode_jenis" class="form-select" required>
                    <option value="">-- Pilih Jenis --</option>
                    <?php if (!empty($jenis)): ?>
                        <?php foreach ($jenis as $j): ?>
                            <option value="<?= esc($j['kode_jenis']) ?>">
                                <?= esc($j['nama_jenis']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="">(Data tidak tersedia)</option>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Pilih Loket -->
            <div class="mb-3">
                <label for="kode_loket" class="form-label fw-semibold">Pilih Loket</label>
                <select id="kode_loket" name="kode_loket" class="form-select" required>
                    <option value="">-- Pilih Loket --</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary w-100">Masuk Dashboard</button>
        </form>
    </div>
</div>

<script>
document.getElementById('kode_jenis').addEventListener('change', async function() {
    const kodeJenis = this.value;
    const loketSelect = document.getElementById('kode_loket');

    // Reset isi dropdown loket
    loketSelect.innerHTML = '<option value="">Memuat...</option>';

    if (!kodeJenis) {
        loketSelect.innerHTML = '<option value="">-- Pilih Loket --</option>';
        return;
    }

    try {
        const res = await fetch(`<?= site_url('operator/getLoketByJenis/') ?>${kodeJenis}`);
        if (!res.ok) throw new Error('Gagal memuat data loket');
        const data = await res.json();

        loketSelect.innerHTML = '<option value="">-- Pilih Loket --</option>';
        if (Array.isArray(data) && data.length > 0) {
            data.forEach(l => {
              loketSelect.innerHTML += `<option value="${l.kode_loket}">${l.nama_loket} (${l.kode_loket})</option>`;
            });
        } else {
            loketSelect.innerHTML = '<option value="">(Tidak ada loket untuk jenis ini)</option>';
        }
    } catch (err) {
        loketSelect.innerHTML = '<option value="">Gagal memuat loket</option>';
        console.error(err);
    }
});
</script>

</body>
</html>
