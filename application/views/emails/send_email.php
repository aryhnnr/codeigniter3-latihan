<div style='background-color: #f4f5f7; padding: 40px 20px; font-family: Arial, sans-serif; line-height: 1.6;'>
    <div style='max-width: 550px; background-color: #ffffff; margin: 0 auto; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); padding: 30px; border-top: 4px solid #0056b3;'>

        <h2 style='color: #333333; margin-top: 0; font-size: 20px;'>
            Halo, <?= htmlspecialchars($username) ?>!
        </h2>

        <p style='color: #555555; font-size: 15px;'>
            Kami menerima permintaan untuk mereset password akun Anda di <strong>Sistem Tiket</strong>. Silakan klik tombol di bawah ini untuk melanjutkan:
        </p>

        <div style='text-align: center; margin: 30px 0;'>
            <a href='<?= $reset_link ?>' style='background-color: #0056b3; color: #ffffff; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; font-size: 15px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);'>Reset Password Anda</a>
        </div>

        <p style='color: #777777; font-size: 13px; font-style: italic; background-color: #fff3cd; padding: 10px; border-left: 3px solid #ffc107; border-radius: 4px;'>
            Link ini berlaku selama <strong>1 jam</strong>. Jika Anda tidak meminta reset password, silakan abaikan email ini dengan aman.
        </p>

        <hr style='border: 0; border-top: 1px solid #eeeeee; margin: 30px 0;'>
        <p style='color: #aaaaaa; font-size: 12px; text-align: center; margin-bottom: 0;'>
            Email ini dikirim secara otomatis oleh Sistem Tiket. Jangan membalas email ini.
        </p>
    </div>
</div>