<div class="card">
  <div class="card-body">

    <?= form_open('ticket/update/' . $ticket->id, ['id' => 'form-ticket-edit']) ?>
      <input type="hidden" name="id" value="<?= $ticket->id ?>">

      <div class="form-group">
        <label>No Ticket</label>
        <input type="text" class="form-control" value="<?= $ticket->ticket_number ?>" disabled>
        <small class="text-muted">Nomor ticket tidak dapat diubah</small>
      </div>

      <div class="form-group">
        <label>Nama Pemohon</label>
        <input type="text" name="nama_pemohon" class="form-control"
               value="<?= set_value('nama_pemohon', $ticket->nama_pemohon) ?>">
        <small class="text-danger" id="error_nama_pemohon"><?= form_error('nama_pemohon') ?></small>
      </div>

      <div class="form-group">
        <label>Departemen</label>
        <select name="departemen_id" class="form-control edit-select2">
          <option value="">-- Pilih Departemen --</option>
          <?php foreach ($departments as $d) : ?>
            <option value="<?= $d->department_id ?>"
              <?= set_select('departemen_id', $d->department_id, ($ticket->departemen_id == $d->department_id)) ?>>
              <?= $d->department_name ?>
            </option>
          <?php endforeach; ?>
        </select>
        <small class="text-danger" id="error_departemen_id"><?= form_error('departemen_id') ?></small>
      </div>

      <div class="form-group">
        <label>Judul Masalah</label>
        <input type="text" name="judul" class="form-control"
               value="<?= set_value('judul', $ticket->judul) ?>">
        <small class="text-danger" id="error_judul"><?= form_error('judul') ?></small>
      </div>

      <div class="form-group">
        <label>Deskripsi Masalah</label>
        <textarea name="deskripsi" class="form-control" rows="4"><?= set_value('deskripsi', $ticket->deskripsi) ?></textarea>
        <small class="text-danger" id="error_deskripsi"><?= form_error('deskripsi') ?></small>
      </div>

      <div class="form-group">
        <label>Prioritas</label>
        <select name="prioritas" class="form-control edit-select2">
          <option value="">-- Pilih Prioritas --</option>
          <?php foreach (['Low', 'Normal', 'High', 'Urgent'] as $p) : ?>
            <option value="<?= $p ?>" <?= set_select('prioritas', $p, ($ticket->prioritas == $p)) ?>>
              <?= $p ?>
            </option>
          <?php endforeach; ?>
        </select>
        <small class="text-danger" id="error_prioritas"><?= form_error('prioritas') ?></small>
      </div>

      <a href="<?= base_url('ticket') ?>" class="btn btn-secondary">
        <i class="fa fa-arrow-left"></i> Batal
      </a>
      <!-- <a href="javascript:void(0)" class="btn btn-primary" onclick="simpanPerubahanData()">Simpan Perubahan</a> -->
      <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan Perubahan</button>

    <?= form_close() ?>

  </div>
</div>

<script>
window.addEventListener('load', function () {
    $('.edit-select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });
    $('#form-ticket-edit').on('submit', function (e) {
        e.preventDefault();

        $('.text-danger').text('');

        Swal.fire({
            title: 'Update Ticket?',
            text: 'Pastikan data ticket sudah benar.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Simpan',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                simpanPerubahanData();
            }
        });
    });

    function simpanPerubahanData(){
      $.ajax({
        url: '<?= base_url('ticket/update/' . $ticket->id) ?>',
        type: 'POST',
        data: $('#form-ticket-edit').serialize(),
        dataType: 'json',
        beforeSend: function(){
          Swal.fire({
            title: 'Menyimpan...',
            allowOutsideClick: false,
            didOpen: () => {
              Swal.showLoading();
            }
          })
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
        error: function(){
          Swal.fire('Gagal', 'Terjadi kesalahan pada server', 'error');
        }
      })
    }

});
</script>