<div class="card">
    <form id="purchase-form" action="<?= base_url('purchase/store') ?>" method="POST" novalidate>
        <div class="card-body">
            <div class="form-row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="purchase_code">Code Purchase</label>
                        <input type="text" class="form-control" id="purchase_code" name="purchase_code" value="<?= $purchase_code_preview ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="purchase_date">Tanggal</label>
                        <div class="input-group date">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            </div>
                            <input type="text" class="form-control" id="purchase_date" name="purchase_date" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <small class="text-danger d-block" id="error_purchase_date"></small>
                    </div>
                    <div class="form-group">
                        <label for="supplier_id">Nama Supplier</label>
                        <select class="form-control select2" id="supplier_id" name="supplier_id" required>
                            <option value="">-- Pilih Supplier --</option>
                            <?php foreach ($suppliers as $supplier): ?>
                                <option value="<?= $supplier->id ?>"><?= $supplier->nama_supplier ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-danger d-block" id="error_supplier_id"></small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="due_date">Tanggal Jatuh Tempo</label>
                        <div class="input-group date">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            </div>
                            <input type="text" class="form-control" id="due_date" name="due_date" value="<?= date('Y-m-d') ?>" required>

                        </div>
                        <small class="text-danger d-block" id="error_due_date"></small>
                    </div>
                    <div class="form-group">
                        <label for="payment_type">Pembayaran</label>
                        <select class="form-control select2" id="payment_type" name="payment_type" required>
                            <option value="">-- Pilih Pembayaran --</option>
                            <option value="cash">Cash</option>
                            <option value="credit">Credit</option>
                        </select>
                        <small class="text-danger d-block" id="error_payment_type"></small>
                    </div>
                </div>
            </div>

            <div>
                <div class="d-flex justify-content-between align-items-center mb-2"> 
                    <h5>Produk</h5>
                    <button type="button" class="btn btn-success" id="add-product" data-toggle="modal" data-target="#productModal">
                        <i class="fas fa-plus mr-1"></i>Tambah Produk
                    </button>
                </div>
                <table class="table table-bordered table-striped" id="product_table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Produk</th>
                            <th>Qty</th>
                            <th>Harga</th>
                            <th>Subtotal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            <div class="row mt-4">
                <div class="col-md-6 mb-3 mb-md-0">
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="7" placeholder="Tulis catatan purchase..."></textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-2">
                        <label for="subtotal">Subtotal</label>
                        <input type="text" class="form-control" id="subtotal" value="0" readonly>
                    </div>
                    <div class="form-group mb-2">
                        <label for="discount">Diskon</label>
                        <input type="text" class="form-control money-input" id="discount_display" inputmode="numeric" value="0">
                        <input type="hidden" id="discount" name="discount" value="0">
                    </div>
                    <div class="form-group mb-2">
                        <label for="tax">Pajak</label>
                        <input type="text" class="form-control money-input" id="tax_display" inputmode="numeric" value="0">
                        <input type="hidden" id="tax" name="tax" value="0">
                    </div>
                    <div class="form-group mb-0 pt-2">
                        <label for="grand_total" class="font-weight-bold">Grand Total</label>
                        <input type="text" class="form-control font-weight-bold" id="grand_total" value="0" readonly>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-right">
            <a href="<?= base_url('purchase') ?>" class="btn btn-secondary mr-2">
                <i class="fas fa-arrow-left mr-1"></i>Batal
            </a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Simpan</button>
        </div>
    </form>
</div>

<div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="productModalLabel"><i class="fas fa-box-open mr-1"></i> Tambah Produk</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="product_index">
                <div class="form-group">
                    <label for="product_id">Nama Produk</label>
                    <select class="form-control select2" id="product_id">
                        <option value="">-- Pilih Produk --</option>
                        <?php foreach ($product as $item): ?>
                            <option value="<?= $item->product_id ?>"><?= html_escape($item->product_name) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-danger d-block" id="error_product_id"></small>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="product_qty">Qty</label>
                        <input type="text" class="form-control qty-input" id="product_qty" inputmode="numeric">
                        <small class="text-danger d-block" id="error_product_qty"></small>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="product_price">Harga</label>
                        <input type="text" class="form-control money-input" id="product_price" inputmode="numeric">
                        <small class="text-danger d-block" id="error_product_price"></small>
                    </div>
                </div>
                <div class="text-danger small" id="product-error"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="save-product">Simpan Produk</button>
            </div>
        </div>
    </div>
</div>

<!-- <script>
window.addEventListener('load', function () {
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    $('.input-group.date').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true
    });

    
    const STORAGE_KEY = <?= json_encode($purchase_draft_storage_key) ?>;
    var productRows = JSON.parse(sessionStorage.getItem(STORAGE_KEY)) || [];
    var productTable = $('#product_table').DataTable({
        data: productRows,
        responsive: true,
        autoWidth: false,
        paging: false,
        searching: false,
        info: false,
        ordering: false,
        columns: [
            { data: null, render: function (data, type, row, meta) { return meta.row + 1; } },
            { data: 'product_name' },
            { data: 'qty', render: formatMoney },
            { data: 'price', render: formatMoney },
            { data: null, render: function (data) { return formatMoney(data.qty * data.price); } },
            { data: null, render: function () {
                return '<button type="button" class="btn btn-sm btn-warning edit-product mr-1" title="Edit"><i class="fas fa-edit"></i> edit</button>' +
                    '<button type="button" class="btn btn-sm btn-danger delete-product" title="Hapus"><i class="fas fa-trash"></i> hapus</button>';
            } }
        ]
    });

    if (productRows.length > 0) {
        updateTotals();
    }

    function saveToStorage() {
        sessionStorage.setItem(STORAGE_KEY, JSON.stringify(productRows));
    }

    function formatMoney(value) {
        return Number(value).toLocaleString('id-ID', { minimumFractionDigits: 0 });
    }

    function parseMoney(value) {
        return Number(String(value).replace(/\./g, '').replace(/[^\d-]/g, '')) || 0;
    }

    function syncMoneyInput(input) {
        var value = parseMoney($(input).val());
        $(input).val(formatMoney(value));
        return value;
    }

    function syncQuantityInput(input) {
        var value = parseMoney($(input).val());
        $(input).val(value ? formatMoney(value) : '');
        return value;
    }

    function updateTotals() {
        var subtotal = productRows.reduce(function (total, row) {
            return total + (Number(row.qty) * Number(row.price));
        }, 0);
        var discount = Math.max(0, parseMoney($('#discount_display').val()));
        var tax = Math.max(0, parseMoney($('#tax_display').val()));
        $('#discount_display').val(formatMoney(discount));
        $('#tax_display').val(formatMoney(tax));
        $('#discount, #tax').val(function () {
            return $(this).attr('id') === 'discount' ? discount : tax;
        });
        var grandTotal = Math.max(0, subtotal - discount + tax);

        $('#subtotal').val(formatMoney(parseFloat(subtotal)));
        $('#grand_total').val(formatMoney(grandTotal));
    }

    function resetProductModal() {
        $('#product_index').val('');
        $('#product_id').val('').trigger('change');
        $('#product_qty, #product_price').val('');
        clearProductErrors();
        $('#productModalLabel').text('Tambah Produk');
    }

    function clearProductErrors() {
        $('#product-error, #error_product_id, #error_product_qty, #error_product_price').text('');
    }

    function setProductError(field, message) {
        clearProductErrors();
        $('#error_' + field).text(message);
    }

    function clearFormErrors() {
        $('#error_purchase_date, #error_supplier_id, #error_due_date, #error_payment_type').text('');
    }

    function validateForm() {
        clearFormErrors();
        var valid = true;
        var fields = [
            { id: 'purchase_date', message: 'Tanggal purchase wajib diisi.' },
            { id: 'supplier_id', message: 'Supplier wajib dipilih.' },
            { id: 'due_date', message: 'Tanggal jatuh tempo wajib diisi.' },
            { id: 'payment_type', message: 'Pembayaran wajib dipilih.' }
        ];
        fields.forEach(function (field) {
            if (!$('#' + field.id).val()) {
                $('#error_' + field.id).text(field.message);
                valid = false;
            }
        });
        return valid;
    }
    

    $('#add-product').on('click', resetProductModal);

    $('#save-product').on('click', function () {
        var productId = $('#product_id').val();
        var productName = $('#product_id option:selected').text();
        var qty = parseMoney($('#product_qty').val());
        var price = parseMoney($('#product_price').val());
        var index = $('#product_index').val();

        clearProductErrors();
        if (!productId) {
            setProductError('product_id', 'Produk tidak boleh kosong.');
            return;
        }
        if (!Number.isInteger(qty)) {
            setProductError('product_qty', 'Kuantitas harus berupa angka bulat.');
            return;
        }
        if (qty < 1) {
            setProductError('product_qty', 'Kuantitas harus lebih besar dari 0.');
            return;
        }
        if (!Number.isFinite(price)) {
            setProductError('product_price', 'Harga harus berupa angka yang valid.');
            return;
        }
        if (price < 0) {
            setProductError('product_price', 'Harga tidak boleh negatif.');
            return;
        }

        var duplicate = productRows.some(function (item, rowIndex) {
            return String(item.product_id) === String(productId) && String(rowIndex) !== String(index);
        });
        if (duplicate) {
            setProductError('product_id', 'Produk tersebut sudah ditambahkan.');
            return;
        }

        var row = { product_id: productId, product_name: productName, qty: qty, price: price };
        if (index === '') {
            productRows.push(row);
        } else {
            productRows[Number(index)] = row;
        }
        productTable.clear().rows.add(productRows).draw();
        updateTotals();
        saveToStorage();
        $('#productModal').modal('hide');
    });

    $('#product_table tbody').on('click', '.edit-product', function () {
        var index = productTable.row($(this).closest('tr')).index();
        var row = productRows[index];
        $('#product_index').val(index);
        $('#product_id').val(row.product_id).trigger('change');
        $('#product_qty').val(formatMoney(row.qty));
        $('#product_price').val(formatMoney(row.price));
        clearProductErrors();
        $('#productModalLabel').text('Edit Produk');
        $('#productModal').modal('show');
    });

    $('#product_table tbody').on('click', '.delete-product', function () {
        var index = productTable.row($(this).closest('tr')).index();
        productRows.splice(index, 1);
        productTable.clear().rows.add(productRows).draw();
        updateTotals();
        saveToStorage(); 
    });

    $('.money-input').on('input', function () {
        syncMoneyInput(this);
        updateTotals();
    });

    $('.qty-input').on('input', function () {
        syncQuantityInput(this);
    });

    $('#purchase-form').on('submit', function (event) {
        event.preventDefault();
        if (!validateForm()) {
            return;
        }
        if (!productRows.length) {
            Swal.fire('Perhatian', 'Tambahkan minimal satu produk.', 'warning');
            return;
        }

        var formData = new FormData(this);
        productRows.forEach(function (row) {
            formData.append('product_id[]', row.product_id);
            formData.append('qty[]', row.qty);
            formData.append('price[]', row.price);
        });

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

        function submitProduct() {
            $.ajax({
                url: $('#purchase-form').attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        sessionStorage.removeItem(STORAGE_KEY);
                        window.location.href = '<?= base_url('purchase') ?>';
                    } else if (response.status === 'failed' && response.errors) {
                        clearFormErrors();
                        $.each(response.errors, function (field, message) {
                            $('#error_' + field).text(message);
                        });
                    } else {
                        Swal.fire('Gagal', response.message || 'Data tidak dapat disimpan.', 'error');
                    }
                },
                error: function () {
                    Swal.fire('Gagal', 'Terjadi kesalahan pada server.', 'error');
                }
            });
        }
    });
});
</script> -->