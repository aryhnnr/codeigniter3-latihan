<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="<?= base_url('assets/adminlte/plugins/fontawesome-free/css/all.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/adminlte/dist/css/adminlte.min.css') ?>">
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <div class="login-logo">
        <b>Admin</b>LTE
    </div>

    <div class="card">
        <div class="card-body login-card-body">

            <?php if(!$valid_token): ?>

                <p class="login-box-msg text-danger">
                    Link reset password tidak valid atau sudah kadaluarsa.
                </p>
                <p class="text-center">
                    <a href="<?= base_url('auth/forgot-password') ?>">Minta link baru</a>
                </p>

            <?php else: ?>

                <p class="login-box-msg">Masukkan password baru</p>

                <div id="alert-box" class="alert alert-danger" style="display:none;"></div>

                <form id="form-reset">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                    <div class="input-group mb-3">
                        <input type="password" name="password" class="form-control" placeholder="Password baru">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                        <div class="invalid-feedback" id="error-password"></div>
                    </div>

                    <div class="input-group mb-3">
                        <input type="password" name="password_confirm" class="form-control" placeholder="Konfirmasi password baru">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                        <div class="invalid-feedback" id="error-password_confirm"></div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Simpan Password Baru</button>
                </form>

            <?php endif; ?>

        </div>
    </div>
</div>
<script src="<?= base_url('assets/adminlte/plugins/jquery/jquery.min.js') ?>"></script>
<script src="<?= base_url('assets/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= base_url('assets/adminlte/dist/js/adminlte.min.js') ?>"></script>

<script>
$('#form-reset').on('submit', function(e){
    e.preventDefault();

    $('.form-control').removeClass('is-invalid');
    $('.invalid-feedback').text('');
    $('#alert-box').hide();

    var $btn = $(this).find('button[type=submit]');
    $btn.prop('disabled', true).text('Menyimpan...');

    $.ajax({
        url: '<?= base_url('auth/reset-password') ?>',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response){
            $btn.prop('disabled', false).text('Simpan Password Baru');

            if(response.status){
                window.location.href = '<?= base_url('auth') ?>?reset=success';
            } else {
                if(response.errors){
                    $.each(response.errors, function(field, message){
                        $('input[name="' + field + '"]').addClass('is-invalid');
                        $('#error-' + field).text(message);
                    });
                }
                if(response.message){
                    $('#alert-box').text(response.message).show();
                }
            }
        },
        error: function(){
            $btn.prop('disabled', false).text('Simpan Password Baru');
            alert('Terjadi kesalahan, coba lagi');
        }
    });
});
</script>
</body>
</html>