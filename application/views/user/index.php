<div class="card">
    <div class="card-header d-flex align-items-center">
        <div>
            <h5 class="mb-0"><i class="fas fa-user-cog mr-2"></i> Manajemen User</h5>
            <small class="text-muted">Kelola akun dan data employee yang terhubung.</small>
        </div>
        <a href="<?= base_url('user/create') ?>" class="btn btn-primary btn-sm ml-auto">
            <i class="fas fa-plus"></i> Tambah User
        </a>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="filter-user" class="small text-muted mb-1">Cari user atau employee</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                    <input type="search" id="filter-user" class="form-control" placeholder="Nama, username, email...">
                </div>
            </div>
        </div>
        <div class="table-responsive">
        <table class="table table-hover table-striped align-middle" id="table-user">
            <thead>
                <tr>
                    <th width="40">#</th>
                    <th>Employee</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Nomor HP</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th width="150">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($users as $u): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>
                            <?php if ($u->employee_name): ?>
                                <span class="font-weight-bold"><?= htmlspecialchars($u->employee_name) ?></span>
                                <br><small class="text-muted"><?= htmlspecialchars($u->employee_code) ?></small>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($u->username) ?></td>
                        <td><?= htmlspecialchars($u->email) ?></td>
                        <td><?= htmlspecialchars($u->nomor_hp ?? '-') ?></td>
                        <td>
                            <?php if ($u->role === 'admin'): ?>
                                <span class="badge badge-danger">Admin</span>
                            <?php else: ?>
                                <span class="badge badge-info">Staff</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($u->status == 1): ?>
                                <span class="badge badge-success" id="status-badge-<?= $u->user_id ?>">Aktif</span>
                            <?php else: ?>
                                <span class="badge badge-secondary" id="status-badge-<?= $u->user_id ?>">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($u->employee_id): ?>
                                <a href="<?= base_url('user/edit/' . $u->user_id) ?>"
                                class="btn btn-sm btn-outline-primary"
                                title="Edit akun user">
                                    <i class="fas fa-user-edit"></i>
                                    <span class="d-none d-lg-inline"> Edit Akun</span>
                                </a>
                            <?php endif; ?>
                            <?php if ($u->user_id != $this->session->userdata('user_id')): ?>
                                <button class="btn btn-sm btn-warning btn-toggle-status"
                                        data-id="<?= $u->user_id ?>"
                                        data-status="<?= $u->status ?>"
                                        title="<?= $u->status == 1 ? 'Nonaktifkan' : 'Aktifkan' ?>">
                                    <i class="fas fa-<?= $u->status == 1 ? 'ban' : 'check' ?>"></i>
                                </button>
                            <?php else: ?>
                                <span class="badge badge-light">Akun saya</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Belum ada data user.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<script>
window.addEventListener('load', function(){
    var userTable = $('#table-user').DataTable({
        responsive: true,
        autoWidth: false,
        order: [[0, 'asc']],
        dom: 'rtip'
    });

    $('#filter-user').on('keyup search', function(){
        userTable.search(this.value).draw();
    });

    $(document).on('click', '.btn-toggle-status', function(){
        const btn    = $(this);
        const userId = btn.data('id');
        const status = btn.data('status');
        const label  = status == 1 ? 'nonaktifkan' : 'aktifkan';

        Swal.fire({
            title: 'Konfirmasi',
            text: 'Yakin ingin ' + label + ' user ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, ' + label,
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: '<?= base_url('user/toggle_status/') ?>' + userId,
                type: 'POST',
                dataType: 'json',
                success: function(res){
                    if (res.status){
                        Swal.fire('Berhasil', res.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                },
                error: function(){
                    Swal.fire('Gagal', 'Terjadi kesalahan pada server', 'error');
                }
            });
        });
    });
});
</script>
