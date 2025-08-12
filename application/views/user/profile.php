<!-- Content Body -->
<div class="content-body">
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Profile</h5>
                </div>
                <div class="card-body">
                    <?= validation_errors('<div class="alert alert-danger">', '</div>') ?>
                    
                    <?= form_open('user/update_profile', ['method' => 'post']) ?>
                        <div class="row mb-3">
                            <label for="username" class="col-sm-4 col-form-label">Username</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="username" value="<?= $user->username ?>" readonly>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <label for="nama_lengkap" class="col-sm-4 col-form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?= set_value('nama_lengkap', $user->nama_lengkap) ?>" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <label for="role" class="col-sm-4 col-form-label">Role</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="role" value="<?= ucfirst($user->role) ?>" readonly>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-sm-8 offset-sm-4">
                                <button type="submit" class="btn btn-primary">Update Profile</button>
                            </div>
                        </div>
                    <?= form_close() ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Ganti Password</h5>
                </div>
                <div class="card-body">
                    <?= form_open('user/change_password', ['method' => 'post']) ?>
                        <div class="row mb-3">
                            <label for="current_password" class="col-sm-4 col-form-label">Password Lama <span class="text-danger">*</span></label>
                            <div class="col-sm-8">
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <label for="new_password" class="col-sm-4 col-form-label">Password Baru <span class="text-danger">*</span></label>
                            <div class="col-sm-8">
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                <small class="text-muted">Password minimal 6 karakter</small>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <label for="confirm_password" class="col-sm-4 col-form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                            <div class="col-sm-8">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-sm-8 offset-sm-4">
                                <button type="submit" class="btn btn-danger">Ganti Password</button>
                            </div>
                        </div>
                    <?= form_close() ?>
                </div>
            </div>
        </div>
    </div>
</div> 