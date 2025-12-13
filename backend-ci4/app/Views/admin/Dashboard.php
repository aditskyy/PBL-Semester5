<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<style>
    /* ---- Custom Dashboard Styling ---- */
    .stat-card {
        border-radius: 14px;
        padding: 25px;
        color: #fff;
        box-shadow: 0 8px 18px rgba(0,0,0,0.15);
        background: linear-gradient(145deg, #4e73df, #224abe);
        transition: 0.25s;
    }
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 25px rgba(0,0,0,0.25);
    }

    .stat-card.green {
        background: linear-gradient(145deg, #1cc88a, #17a673);
    }
    .stat-card.orange {
        background: linear-gradient(145deg, #f6c23e, #dda20a);
    }

    .status-badge {
        font-size: 17px;
        padding: 7px 14px;
        border-radius: 20px;
        font-weight: bold;
    }
</style>

<h2 class="fw-bold mb-4">Dashboard Admin</h2>

<!-- =============================== -->
<!--   STATISTIC SUMMARY CARDS       -->
<!-- =============================== -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card blue">
            <p class="mb-1 text-light">Total Jenis Layanan</p>
            <h2 class="fw-bold"><?= $countJenis ?></h2>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card green">
            <p class="mb-1 text-light">Total Loket</p>
            <h2 class="fw-bold"><?= $countLoket ?></h2>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card orange">
            <p class="mb-1 text-light">Total Operator</p>
            <h2 class="fw-bold"><?= $countOperator ?></h2>
        </div>
    </div>
</div>

<!-- =============================== -->
<!--      CHART SECTION             -->
<!-- =============================== -->

<div class="row g-4 mb-4">

    <div class="col-md-6">
        <div class="card shadow p-3">
            <h5 class="fw-bold mb-3">📈 Total Antrian 7 Hari Terakhir</h5>
            <canvas id="chart7Hari" height="130"></canvas>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow p-3">
            <h5 class="fw-bold mb-3">🏢 Total Antrian per Loket (Hari Ini)</h5>
            <canvas id="chartPerLoket" height="130"></canvas>
        </div>
    </div>

</div>


<!-- =============================== -->
<!--   STATUS LOKET LIST             -->
<!-- =============================== -->

<div class="card shadow">
    <div class="card-header bg-primary text-white fw-bold">
        Status Loket & Aktivitas Antrian Hari Ini
    </div>

    <ul class="list-group list-group-flush">

        <?php foreach ($loketList as $l): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center py-3">

            <div>
                <b class="fs-5"><?= $l['nama_loket'] ?></b><br>
                <small>Total Antrian: <b><?= $l['total_antrian'] ?></b></small><br>
                <small>Nomor Terakhir: <b><?= $l['last_nomor'] ?></b></small>
            </div>

            <?php if (($l['status'] ?? 'tutup') === 'buka'): ?>
                <span class="status-badge bg-success">Buka</span>
            <?php else: ?>
                <span class="status-badge bg-danger">Tutup</span>
            <?php endif; ?>

        </li>
        <?php endforeach; ?>

    </ul>
</div>


<!-- =============================== -->
<!--           CHART.JS              -->
<!-- =============================== -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Data dari Controller
    let chartDates   = <?= $chartDates ?>;
    let chartTotals  = <?= $chartTotals ?>;

    let loketNames   = <?= $loketNames ?>;
    let loketTotals  = <?= $loketTotals ?>;


    /* ======================
       LINE CHART 7 HARI
    ====================== */
    new Chart(document.getElementById('chart7Hari'), {
        type: 'line',
        data: {
            labels: chartDates,
            datasets: [{
                label: "Total Antrian",
                data: chartTotals,
                borderWidth: 3,
                borderColor: "#4e73df",
                backgroundColor: "rgba(78,115,223,0.25)",
                tension: 0.4,
                fill: true
            }]
        }
    });

    /* ======================
       BAR CHART LOKET
    ====================== */
    new Chart(document.getElementById('chartPerLoket'), {
        type: 'bar',
        data: {
            labels: loketNames,
            datasets: [{
                label: 'Jumlah Antrian',
                data: loketTotals,
                borderWidth: 2,
                backgroundColor: "rgba(28,200,138,0.55)",
                borderColor: "#1cc88a"
            }]
        }
    });
</script>

<?= $this->endSection() ?>
