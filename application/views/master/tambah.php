<!-- Content Body -->
<div class="content-body">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white;">
                    <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Tambah Data Agent</h5>
                </div>
                <div class="card-body">
                    <?= validation_errors('<div class="alert alert-danger">', '</div>') ?>
                    
                    <?= form_open('master/tambah', ['method' => 'post']) ?>
                        <div class="row mb-3">
                            <label for="nama_agent" class="col-sm-3 col-form-label">Nama Agent <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="nama_agent" name="nama_agent" value="<?= set_value('nama_agent') ?>" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <label for="hp" class="col-sm-3 col-form-label">HP <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="hp" name="hp" value="<?= set_value('hp') ?>" required>
                            </div>
                        </div>
                        
                       
                        
                        <div class="row">
                            <div class="col-sm-9 offset-sm-3">
                                <button type="submit" class="btn" style="background: var(--primary-color); color: white; border: none;">Simpan</button>
                                <a href="<?= base_url('master') ?>" class="btn" style="background: var(--secondary-color); color: white; border: none;">Kembali</a>
                            </div>
                        </div>
                    <?= form_close() ?>
                </div>
            </div>
        </div>
    </div>
</div> 