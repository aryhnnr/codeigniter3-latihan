<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lupa Password</title>
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
            <p class="login-box-msg">Masukkan email untuk reset password</p>

            <div id="alert-box" class="alert" style="display:none;"></div>

            <form id="form-forgot">
                <div class="input-group mb-3">
                    <input type="email" name="email" class="form-control" placeholder="Email">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                    <div class="invalid-feedback" id="error-email"></div>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Kirim Link Reset</button>
            </form>

            <p class="mt-3 mb-0">
                <a href="<?= base_url('auth') ?>">Kembali ke halaman login</a>
            </p>
        </div>
    </div>
</div>
<script src="<?= base_url('assets/adminlte/plugins/jquery/jquery.min.js') ?>"></script>
<script src="<?= base_url('assets/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= base_url('assets/adminlte/dist/js/adminlte.min.js') ?>"></script>

<script>
$('#form-forgot').on('submit', function(e){
    e.preventDefault();

    $('.form-control').removeClass('is-invalid');
    $('.invalid-feedback').text('');
    $('#alert-box').hide();

    var $btn = $(this).find('button[type=submit]');
    $btn.prop('disabled', true).text('Mengirim...');

    $.ajax({
        url: '<?= base_url('auth/forgot-password') ?>',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response){
            $btn.prop('disabled', false).text('Kirim Link Reset');

            if(response.status){
                $('#alert-box')
                    .removeClass('alert-danger')
                    .addClass('alert-success')
                    .text(response.message)
                    .show();
                $('#form-forgot')[0].reset();
            } else {
                if(response.errors){
                    $.each(response.errors, function(field, message){
                        $('input[name="' + field + '"]').addClass('is-invalid');
                        $('#error-' + field).text(message);
                    });
                }
                if(response.message){
                    $('#alert-box')
                        .removeClass('alert-success')
                        .addClass('alert-danger')
                        .text(response.message)
                        .show();
                }
            }
        },
        error: function(){
            $btn.prop('disabled', false).text('Kirim Link Reset');
            alert('Terjadi kesalahan, coba lagi');
        }
    });
});
</script>
</body>
</html>