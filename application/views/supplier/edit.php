<div class="card">
    <div class="card-header"><h5>Edit Supplier</h5></div>
    <div class="card-body">
        <form id="form-supplier">
            <div class="form-group">
                <label>Code Supplier</label>
                <input type="text" class="form-control" value="<?= $supplier->code_supplier ?>" disabled>
            </div>
            <div class="form-group">
                <label>Nama Supplier</label>
                <input type="text" name="nama_supplier" class="form-control" value="<?= html_escape($supplier->nama_supplier) ?>">
                <small class="text-danger" id="error_nama_supplier"></small>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control select2">
                    <?php foreach ($status as $item): ?>
                        <option value="<?= $item->product_status_id ?>" <?= $supplier->status == $item->product_status_id ? 'selected' : '' ?>><?= $item->product_status_name ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="text-danger" id="error_status"></small>
            </div>
            <a href="<?= base_url('supplier') ?>" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
</div>
<script>
window.addEventListener('load', function () {
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });
    $('#form-supplier').on('submit', function (event) {
        event.preventDefault();
        $('.text-danger').text('');
        Swal.fire({
            title: 'Update supplier?',
            text: 'Pastikan perubahan data supplier sudah benar.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, update',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.post('<?= base_url('supplier/update/' . $supplier->id) ?>', $('#form-supplier').serialize(), function (response) {
                if (response.status === 'success') {
                    window.location.href = '<?= base_url('supplier') ?>';
                } else if (response.errors) {
                    $.each(response.errors, function (field, message) { $('#error_' + field).text(message); });
                } else {
                    Swal.fire('Gagal', response.message, 'error');
                }
            }, 'json').fail(function () { Swal.fire('Gagal', 'Terjadi kesalahan pada server.', 'error'); });
        });
    });
});
</script>
