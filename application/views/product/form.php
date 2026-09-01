<div class="card">
    <div class="card-body">
        <form id="form_product">
            <div class="form-group">
                <label>Product Code</label>
                <input type="text" class="form-control" value="<?= $product_code_preview ?>" disabled>
                <small class="text-muted">Product Code otomatis</small>
            </div>

            <div class="form-group">
                <label>Nama Produk</label>
                <input type="text" name="product_name" class="form-control">
                <small class="text-danger" id="error_product_name"></small>
            </div>

            <div class="form-group">
                <label>Category</label>
                <select name="category_id" class="form-control form-select2">
                    <option value="">-- Pilih Category --</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $c->category_id ?>"><?= $c->category_name ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="text-danger" id="error_category_id"></small>
            </div>

            <div class="form-group">
                <label>Brand</label>
                <select name="brand_id" class="form-control form-select2">
                    <option value="">-- Pilih Brand --</option>
                    <?php foreach ($brands as $b): ?>
                        <option value="<?= $b->brand_id ?>"><?= $b->brand_name ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="text-danger" id="error_brand_id"></small>
            </div>

            <div class="form-group">
                <label>Unit</label>
                <select name="unit_id" class="form-control form-select2">
                    <option value="">-- Pilih Unit --</option>
                    <?php foreach ($units as $u): ?>
                        <option value="<?= $u->unit_id ?>"><?= $u->unit_name ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="text-danger" id="error_unit_id"></small>
            </div>

            <div class="form-group">
                <label>Product Type</label>
                <select name="product_type" class="form-control form-select2">
                    <option value="">-- Pilih Product Type --</option>
                    <?php foreach ($product_type as $pt): ?>
                        <option value="<?= $pt->product_type_id ?>"><?= $pt->product_type_name ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="text-danger" id="error_product_type"></small>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control form-select2">
                    <option value="">-- Pilih Status --</option>
                    <?php foreach ($product_status as $ps): ?>
                        <option value="<?= $ps->product_status_id ?>"><?= $ps->product_status_name ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="text-danger" id="error_status"></small>
            </div>

            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
                <small class="text-danger" id="error_description"></small>
            </div>
            <a href="<?= base_url('product') ?>" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
    </div>
</div>

<script>
window.addEventListener('load', function () {
    $('.form-select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    $('#form_product').on('submit', function(e) {
        e.preventDefault();
        $('.text-danger').text('');

        Swal.fire({
            title: 'Simpan data ini?',
            text: 'Pastikan data yang Anda isi sudah benar.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, simpan',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                submitProduct();
            }
        });
    });

    function submitProduct() {
        $.ajax({
            url: '<?= base_url('product/store') ?>',
            type: 'POST',
            data: $('#form_product').serialize(),
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
            success: function(response) {
                if (response.status === 'success') {
                    window.location.href = '<?= base_url('product') ?>';
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