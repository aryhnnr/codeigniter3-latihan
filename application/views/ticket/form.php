<div class="card">
    <div class="card-body">
        <?= form_open('ticket/store', ['id' => 'form-ticket']) ?>
        <div class="form-group">
            <label>No Ticket</label>
            <input type="text" class="form-control" value="<?= $ticket_number_preview ?>" disabled>
            <small class="text-muted">Nomor ticket otomatis</small>
        </div>

        <div class="form-group">
            <label>Nama Pemohon</label>
            <input type="text"
                name="nama_pemohon"
                class="form-control"
                value="<?= set_value('nama_pemohon') ?>">

            <small class="text-danger" id="error_nama_pemohon">
                <?= form_error('nama_pemohon') ?>
            </small>
        </div>

        <div class="form-group">
            <label>Departemen</label>
            <select name="departemen_id" class="form-control form-select2">
                <option value="">-- Pilih Departemen --</option>

                <?php foreach ($departments as $d) : ?>
                    <option value="<?= $d->department_id ?>"
                        <?= set_select('departemen_id', $d->department_id) ?>>
                        <?= $d->department_name ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <small class="text-danger" id="error_departemen_id">
                <?= form_error('departemen_id') ?>
            </small>
        </div>

        <div class="form-group">
            <label>Judul Masalah</label>
            <input type="text"
                name="judul"
                class="form-control"
                value="<?= set_value('judul') ?>">

            <small class="text-danger" id="error_judul">
                <?= form_error('judul') ?>
            </small>
        </div>

        <div class="form-group">
            <label>Deskripsi Masalah</label>
            <textarea name="deskripsi"
                    class="form-control"
                    rows="4"><?= set_value('deskripsi') ?></textarea>

            <small class="text-danger" id="error_deskripsi">
                <?= form_error('deskripsi') ?>
            </small>
        </div>

        <div class="form-group">
            <label>Prioritas</label>

            <select name="prioritas" class="form-control form-select2">
                <option value="">-- Pilih Prioritas --</option>
                <option value="Low" <?= set_select('prioritas', 'Low') ?>>Low</option>
                <option value="Normal" <?= set_select('prioritas', 'Normal') ?>>Normal</option>
                <option value="High" <?= set_select('prioritas', 'High') ?>>High</option>
                <option value="Urgent" <?= set_select('prioritas', 'Urgent') ?>>Urgent</option>
            </select>

            <small class="text-danger" id="error_prioritas">
                <?= form_error('prioritas') ?>
            </small>
        </div>

        <a href="<?= base_url('ticket') ?>" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Batal
        </a>
        <!-- <a href="javascript:void(0)" class="btn btn-primary" onclick="simpanData()">Simpan Ticket</a> -->
        <button type="submit" class="btn btn-primary">
            Simpan Ticket
        </button>
        <?= form_close() ?>
    </div>
</div>

<script>
window.addEventListener('load', function () {
    $('.form-select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });
    $('#form-ticket').on('submit', function (e) {
        e.preventDefault();

        $('.text-danger').text('');

        Swal.fire({
            title: 'Simpan Ticket?',
            text: 'Pastikan data ticket sudah benar.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Simpan',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                simpanData();
            }
        });
    });
    function simpanData(){
        $.ajax({
            url: '<?= base_url('ticket/store') ?>',
            type: 'POST',
            data: $('#form-ticket').serialize(),
            dataType: 'json',
            beforeSend: function() {
                Swal.fire({
                    title: 'Menyimpan...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function(response){
                if (response.status === 'success') {
                    window.location.href = '<?= base_url('ticket') ?>';
                } else if (response.status === 'failed' && response.errors) {
                    Swal.close();
                    $.each(response.errors, function(field, message) {
                        $('#error_' + field).text(message);
                    });
                } else {
                    Swal.fire('Gagal', response.message || 'Terjadi kesalahan', 'error');
                }
            },
            error: function() {
                Swal.fire('Gagal', 'Terjadi kesalahan pada server', 'error');
            }
        });
    }

});

</script>