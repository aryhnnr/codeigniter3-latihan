<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
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
            <p class="login-box-msg">Silakan login</p>

            <form id="form-login">
                <div class="input-group mb-3">
                    <input type="text" name="identifier" class="form-control" placeholder="Username / Email / No. HP">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
                    <div class="invalid-feedback" id="error-identifier"></div>
                </div>

                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Password">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                    <div class="invalid-feedback" id="error-password"></div>
                </div>

                <div class="form-group">
                    <label>Captcha</label>
                    <br>
                    <span id="captcha-wrapper"><?= $captcha_image ?></span>
                </div>
                <div class="form-group">
                    <label>Enter Captcha Text</label>
                    <input type="text" name="captcha" class="form-control">
                    <div class="invalid-feedback" id="error-captcha"></div>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
            <div class="social-auth-links text-center mb-3">
                <p>- OR -</p>
                <a href="<?= base_url("auth/forgot_password") ?>" class="btn btn-block btn-danger">
                    <i class="fa fa-lock"></i> Lupa kata sandi saya.
                </a>
            </div>
        </div>
    </div>
</div>
<script src="<?= base_url('assets/adminlte/plugins/jquery/jquery.min.js') ?>"></script>
<script src="<?= base_url('assets/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= base_url('assets/adminlte/dist/js/adminlte.min.js') ?>"></script>

<script>
$('#form-login').on('submit', function(e){
    e.preventDefault();
    $('.form-control').removeClass('is-invalid');
    $('.invalid-feedback').text('');

    $.ajax({
        url: '<?= base_url('auth/login') ?>',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response){
            if(response.status){
                window.location.href = response.redirect;
            } else {
                if(response.errors){
                    $.each(response.errors, function(field, message){
                        $('input[name="' + field + '"]').addClass('is-invalid');
                        $('#error-' + field).text(message);
                    });
                }
                if(response.captcha_image){
                    $('#captcha-wrapper').html(response.captcha_image);
                    $('input[name="captcha"]').val('');
                }
            }
        },
        error: function(){
            alert('Terjadi kesalahan, coba lagi');
        }
    });
});
</script>
</body>
</html>