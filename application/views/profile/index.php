<div class="row">
    <!-- Kartu Info Profile -->
    <div class="col-md-4">
        <div class="card card-primary card-outline">
            <div class="card-body box-profile text-center">
                <div class="profile-user-img d-flex align-items-center justify-content-center mx-auto mb-3"
                     style="width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#667eea,#764ba2);font-size:2.5rem;color:#fff;">
                    <i class="fas fa-user"></i>
                </div>
                <h4 class="profile-username"><?= htmlspecialchars($user->username) ?></h4>
                <p class="text-muted">
                    <?php
                    $role_label = !empty($user->role_name) ? $user->role_name : (!empty($user->role) ? ucfirst($user->role) : 'User');
                    $is_admin   = (isset($user->role_slug) && $user->role_slug === 'admin') || (isset($user->role) && $user->role === 'admin') || (isset($user->role_id) && $user->role_id == 1);
                    ?>
                    <span class="badge badge-<?= $is_admin ? 'danger' : 'info' ?> px-3 py-1" style="font-size:.85rem;">
                        <?= htmlspecialchars($role_label) ?>
                    </span>
                </p>

                <ul class="list-group list-group-unbordered mt-3 text-left">
                    <li class="list-group-item">
                        <b><i class="fas fa-envelope mr-2 text-primary"></i> Email</b>
                        <span class="float-right"><?= htmlspecialchars($user->email ?? '-') ?></span>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fas fa-phone mr-2 text-success"></i> Nomor HP</b>
                        <span class="float-right"><?= htmlspecialchars($user->nomor_hp ?? '-') ?></span>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fas fa-circle mr-2 <?= $user->status == 1 ? 'text-success' : 'text-danger' ?>"></i> Status</b>
                        <span class="float-right">
                            <span class="badge badge-<?= $user->status == 1 ? 'success' : 'danger' ?>">
                                <?= $user->status == 1 ? 'Aktif' : 'Nonaktif' ?>
                            </span>
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Kartu Ubah Password -->
    <div class="col-md-8">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-lock mr-2"></i> Ubah Password</h5>
            </div>
            <div class="card-body">
                <?= form_open('profile/change_password', ['id' => 'form-change-pw']) ?>

                <div class="form-group">
                    <label>Password Saat Ini <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" name="current_password" id="inp-current-pw" class="form-control" placeholder="Masukkan password saat ini">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary btn-toggle-pw" data-target="inp-current-pw">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <small class="text-danger" id="error_current_password"></small>
                </div>

                <div class="form-group">
                    <label>Password Baru <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" name="new_password" id="inp-new-pw" class="form-control" placeholder="Minimal 6 karakter">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary btn-toggle-pw" data-target="inp-new-pw">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <small class="text-danger" id="error_new_password"></small>
                </div>

                <div class="form-group">
                    <label>Konfirmasi Password Baru <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" name="confirm_password" id="inp-confirm-pw" class="form-control" placeholder="Ulangi password baru">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary btn-toggle-pw" data-target="inp-confirm-pw">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <small class="text-danger" id="error_confirm_password"></small>
                </div>

                <!-- Strength indicator -->
                <div class="form-group">
                    <small class="text-muted">Kekuatan password:</small>
                    <div class="progress mt-1" style="height:6px;">
                        <div class="progress-bar" id="pw-strength-bar" role="progressbar" style="width:0%"></div>
                    </div>
                    <small id="pw-strength-label" class="text-muted"></small>
                </div>

                <hr>
                <button type="submit" class="btn btn-primary" id="btn-save-pw">
                    <i class="fas fa-key mr-1"></i> Ubah Password
                </button>

                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>

<script>
window.addEventListener('load', function(){

    // Toggle show/hide password
    $(document).on('click', '.btn-toggle-pw', function(){
        const targetId = $(this).data('target');
        const inp = $('#' + targetId);
        const ico = $(this).find('i');
        if (inp.attr('type') === 'password'){
            inp.attr('type', 'text');
            ico.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            inp.attr('type', 'password');
            ico.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Password strength meter
    $('#inp-new-pw').on('input', function(){
        const pw = $(this).val();
        let score = 0;
        if (pw.length >= 6) score++;
        if (pw.length >= 10) score++;
        if (/[A-Z]/.test(pw)) score++;
        if (/[0-9]/.test(pw)) score++;
        if (/[^A-Za-z0-9]/.test(pw)) score++;

        const levels = [
            { pct: 0,   cls: '',        label: '' },
            { pct: 20,  cls: 'bg-danger',  label: 'Sangat lemah' },
            { pct: 40,  cls: 'bg-warning', label: 'Lemah' },
            { pct: 60,  cls: 'bg-info',    label: 'Cukup' },
            { pct: 80,  cls: 'bg-primary', label: 'Kuat' },
            { pct: 100, cls: 'bg-success', label: 'Sangat kuat' },
        ];
        const lv = levels[score] || levels[0];
        $('#pw-strength-bar').css('width', lv.pct + '%').attr('class', 'progress-bar ' + lv.cls);
        $('#pw-strength-label').text(lv.label);
    });

    // Submit ubah password
    $('#form-change-pw').on('submit', function(e){
        e.preventDefault();
        $('.text-danger').text('');

        $.ajax({
            url: '<?= base_url('profile/change_password') ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            beforeSend: function(){
                Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            },
            success: function(res){
                if (res.status){
                    Swal.fire('Berhasil!', res.message, 'success').then(() => {
                        $('#form-change-pw')[0].reset();
                        $('#pw-strength-bar').css('width','0%').attr('class','progress-bar');
                        $('#pw-strength-label').text('');
                    });
                } else if (res.errors){
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
</script>
