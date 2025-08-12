<!-- Content Body -->
<div class="content-body">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Database Transaksi</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Nama Kapal</th>
                                    <th>Waktu Kedatangan</th>
                                    <th>Waktu Keberangkatan</th>
                                    <th>Durasi Menit</th>
                                    <th>Volume Air</th>
  
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                function selisihMenit($waktuAkhir, $waktuAwal) {
                                 return round((strtotime($waktuAkhir) - strtotime($waktuAwal)) / 60);
                                 }
                                ?>
                                <?php if (empty($transaksi)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">Tidak ada data</td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1; foreach ($transaksi as $t): ?>
                                        
                                        <tr>
                                            <td><?= date('d/m/Y H:i:s'); ?></td>
                                            <td><?= $t->nama_kapal ?></td>
                                            <td><?= date('Y-m-d H:i:s', strtotime($t->waktu_selesai_sandar) - (selisihMenit($t->waktu_selesai_sandar, $t->waktu_mulai_sandar) * 60)); ?></td>
                                            <td><?= $t->waktu_selesai_sandar ?></td>
                                            <td>
                                            <?php
                                            echo selisihMenit($t->waktu_selesai_sandar, $t->waktu_mulai_sandar);
                                             ?>
                                             </td>
                                            <td><?= $t->volume_total ?></td>
                                            
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 