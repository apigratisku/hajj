<!-- Content Body -->
<div class="container-fluid">

    <?php if ($this->session->flashdata('message')) : ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $this->session->flashdata('message'); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <a href="<?= base_url('user/add'); ?>" class="btn btn-sm" style="background: var(--gold); color: var(--dark-brown); border: none;">Tambah User</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Username</th>
                            <th>Nama Lengkap</th>
                            <th>Role</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($users as $user) : ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= $user->username; ?></td>
                            <td><?= $user->nama_lengkap; ?></td>
                            <td><?= $user->role; ?></td>
                            <td>
                                <a href="<?= base_url('user/edit/'.$user->id_user); ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="<?= base_url('user/delete/'.$user->id_user); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus user ini?')">Hapus</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div> 