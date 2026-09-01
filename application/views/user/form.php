<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-<?= isset($mode) && $mode === 'edit' ? 'user-edit' : 'user-plus' ?> mr-2"></i>
            <?= isset($mode) && $mode === 'edit' ? 'Edit Akun User' : 'Tambah User Baru' ?>
        </h5>
    </div>
    <div class="card-body">
        <?= form_open(isset($mode) && $mode === 'edit' ? 'user/update/' . $user->user_id : 'user/store', ['id' => 'form-user']) ?>

        <div class="form-group">
            <label>Employee</label>
            <?php if (isset($mode) && $mode === 'edit'): ?>
                <input type="text" class="form-control" value="<?= htmlspecialchars($user->employee_code . ' - ' . $user->employee_name) ?>" disabled>
                <small class="form-text text-muted">Employee terhubung tidak diubah dari halaman edit akun.</small>
            <?php else: ?>
            <select name="employee_id" class="form-control form-select2">
                <option value="">-- Pilih Employee --</option>
                <?php foreach ($employees as $e): ?>
                    <option value="<?= $e->employee_id ?>">
                        <?= htmlspecialchars($e->employee_code) ?> – <?= htmlspecialchars($e->employee_name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small class="text-danger" id="error_employee_id"></small>
            <?php if (empty($employees)): ?>
                <small class="text-warning"><i class="fas fa-exclamation-triangle"></i> Semua employee aktif sudah memiliki akun.</small>
            <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Username <span class="text-danger">*</span></label>
                    <input type="text" name="username" class="form-control" placeholder="min. 4 karakter" autocomplete="off" value="<?= isset($user) ? htmlspecialchars($user->username) : '' ?>">
                    <small class="text-danger" id="error_username"></small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Role <span class="text-danger">*</span></label>
                    <select name="role" class="form-control form-select2">
                        <option value="">-- Pilih Role --</option>
                        <option value="admin" <?= isset($user) && $user->role === 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="staff" <?= isset($user) && $user->role === 'staff' ? 'selected' : '' ?>>Staff</option>
                    </select>
                    <small class="text-danger" id="error_role"></small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" placeholder="email@domain.com" autocomplete="off" value="<?= isset($user) ? htmlspecialchars($user->email) : '' ?>">
                    <small class="text-danger" id="error_email"></small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Nomor HP</label>
                    <input type="text" name="nomor_hp" class="form-control" placeholder="08xxxxxxxxxx" value="<?= isset($user) ? htmlspecialchars($user->nomor_hp ?? '') : '' ?>">
                    <small class="text-danger" id="error_nomor_hp"></small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Password <?= isset($mode) && $mode === 'edit' ? '<span class="text-muted font-weight-normal">(kosongkan jika tidak diubah)</span>' : '<span class="text-danger">*</span>' ?></label>
                    <div class="input-group">
                        <input type="password" name="password" id="inp-password" class="form-control" placeholder="min. 6 karakter" autocomplete="new-password">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary" id="btn-toggle-pw" tabindex="-1">
                                <i class="fas fa-eye" id="ico-pw"></i>
                            </button>
                        </div>
                    </div>
                    <small class="text-danger" id="error_password"></small>
                </div>
            </div>
            <div class="col-md-6 d-flex align-items-center mt-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-gen-pw">
                    <i class="fas fa-random"></i> Generate Password
                </button>
            </div>
        </div>

        <hr>
        <a href="<?= base_url('user') ?>" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> <?= isset($mode) && $mode === 'edit' ? 'Simpan Perubahan' : 'Simpan User' ?>
        </button>

        <?= form_close() ?>
    </div>
</div>

<script>
window.addEventListener('load', function(){
    $('.form-select2').select2({ theme: 'bootstrap4', width: '100%' });

    // Toggle password visibility
    $('#btn-toggle-pw').on('click', function(){
        const inp = $('#inp-password');
        const ico = $('#ico-pw');
        if (inp.attr('type') === 'password'){
            inp.attr('type', 'text');
            ico.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            inp.attr('type', 'password');
            ico.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Generate random password
    $('#btn-gen-pw').on('click', function(){
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789@#!';
        let pw = '';
        for (let i = 0; i < 10; i++) pw += chars.charAt(Math.floor(Math.random() * chars.length));
        $('#inp-password').val(pw).attr('type', 'text');
        $('#ico-pw').removeClass('fa-eye').addClass('fa-eye-slash');
        Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: 'Password: ' + pw, showConfirmButton: false, timer: 4000 });
    });

    // Submit
    $('#form-user').on('submit', function(e){
        e.preventDefault();
        $('.text-danger').text('');

        Swal.fire({
            title: '<?= isset($mode) && $mode === 'edit' ? 'Simpan perubahan akun?' : 'Simpan User?' ?>',
            text: 'Pastikan data sudah benar.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Simpan',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: '<?= base_url(isset($mode) && $mode === 'edit' ? 'user/update/' . $user->user_id : 'user/store') ?>',
                type: 'POST',
                data: $('#form-user').serialize(),
                dataType: 'json',
                beforeSend: function(){
                    Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                },
                success: function(res){
                    if (res.status === 'success'){
                        Swal.fire('Berhasil', res.message, 'success').then(() => {
                            window.location.href = '<?= base_url('user') ?>';
                        });
                    } else if (res.status === 'failed' && res.errors){
                        Swal.close();
                        $.each(res.errors, (field, msg) => $('#error_' + field).text(msg));
                    } else {
                        Swal.fire('Gagal', res.message || 'Terjadi kesalahan', 'error');
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
