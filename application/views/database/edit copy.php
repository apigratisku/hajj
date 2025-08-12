<!-- Content Body -->
<div class="content-body">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Edit Data Transaksi</h5>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('database/update/' . $transaksi->id_transaksi) ?>" method="POST">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="kode_transaksi">Kode Transaksi</label>
                                    <input type="text" class="form-control" id="kode_transaksi" name="kode_transaksi" value="<?= $transaksi->kode_transaksi ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="id_kapal">Kapal</label>
                                    <select class="form-control" id="id_kapal" name="id_kapal">
                                        <option value="">Pilih Kapal</option>
                                        <?php foreach ($kapal as $k): ?>
                                            <option value="<?= $k->id_kapal ?>" <?= ($transaksi->id_kapal == $k->id_kapal) ? 'selected' : '' ?>>
                                                <?= $k->nama_kapal ?> - <?= $k->perusahaan ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="waktu_mulai_sandar">Waktu Mulai Sandar</label>
                                    <input type="datetime-local" class="form-control" id="waktu_mulai_sandar" name="waktu_mulai_sandar" 
                                           value="<?= date('Y-m-d\TH:i', strtotime($transaksi->waktu_mulai_sandar)) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="waktu_selesai_sandar">Waktu Selesai Sandar</label>
                                    <input type="datetime-local" class="form-control" id="waktu_selesai_sandar" name="waktu_selesai_sandar" 
                                           value="<?= $transaksi->waktu_selesai_sandar ? date('Y-m-d\TH:i', strtotime($transaksi->waktu_selesai_sandar)) : '' ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="volume_air">Volume Air (L)</label>
                                    <input type="number" step="0.01" class="form-control" id="volume_air" name="volume_air" value="<?= $transaksi->volume_air ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="liter_per_menit">Liter Per Menit</label>
                                    <input type="number" step="0.01" class="form-control" id="liter_per_menit" name="liter_per_menit" value="<?= $transaksi->liter_per_menit ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="volume_total">Volume Total (L)</label>
                                    <input type="number" step="0.01" class="form-control" id="volume_total" name="volume_total" value="<?= $transaksi->volume_total ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status_sandar">Status Sandar</label>
                                    <select class="form-control" id="status_sandar" name="status_sandar">
                                        <option value="Sandar" <?= ($transaksi->status_sandar == 'Sandar') ? 'selected' : '' ?>>Sandar</option>
                                        <option value="Tidak Sandar" <?= ($transaksi->status_sandar == 'Tidak Sandar') ? 'selected' : '' ?>>Tidak Sandar</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="air_tawar_valve">Status Valve</label>
                                    <select class="form-control" id="air_tawar_valve" name="air_tawar_valve">
                                        <option value="Open" <?= ($transaksi->air_tawar_valve == 'Open') ? 'selected' : '' ?>>Open</option>
                                        <option value="Close" <?= ($transaksi->air_tawar_valve == 'Close') ? 'selected' : '' ?>>Close</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                <a href="<?= base_url('database') ?>" class="btn btn-secondary">Kembali</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize select2 if you want to use it
    if ($.fn.select2) {
        $('#id_kapal').select2({
            theme: 'bootstrap4'
        });
    }
});
</script> 