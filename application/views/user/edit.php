<div class="container-fluid">

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <form action="<?= base_url('user/edit/'.$user->id_user); ?>" method="post">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?= $user->username; ?>">
                            <?= form_error('username', '<small class="text-danger">', '</small>'); ?>
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password">
                            <small class="text-info">Kosongkan jika tidak ingin mengubah password</small>
                        </div>

                        <div class="form-group">
                            <label for="nama_lengkap">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?= $user->nama_lengkap; ?>">
                            <?= form_error('nama_lengkap', '<small class="text-danger">', '</small>'); ?>
                        </div>

                        <div class="form-group">
                            <label for="role">Role</label>
                            <select class="form-control" id="role" name="role">
                                <option value="">Pilih Role</option>
                                <option value="admin" <?= $user->role == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="operator" <?= $user->role == 'operator' ? 'selected' : ''; ?>>Operator</option>
                            </select>
                            <?= form_error('role', '<small class="text-danger">', '</small>'); ?>
                        </div>

                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="<?= base_url('user'); ?>" class="btn btn-secondary">Kembali</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div> 