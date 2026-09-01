<div class="card">
    <div class="card-header">
        <div class="card-tools">
            <a href="<?= base_url('purchase/create') ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Purchase
            </a>
        </div>
    </div>
    <div class="card-body">
       <div class="row mb-3 align-items-end">
            <div class="col-md-2 mb-2">
                <label for="filter_status" class="small mb-1">Status</label>
                <select id="filter_status" class="form-control select2">
                    <option value="">-- Semua Status --</option>
                    <?php foreach ($status as $ps): ?>
                        <option value="<?= $ps->product_status_id ?>"><?= $ps->product_status_name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2 mb-2">
                <label for="filter_supplier" class="small mb-1">Supplier</label>
                <select id="filter_supplier" class="form-control select2">
                    <option value="">-- Semua Supplier --</option>
                    <?php foreach ($suppliers as $s): ?>
                        <option value="<?= $s->id ?>"><?= $s->nama_supplier ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            

            <div class="col-md-3 mb-2">
                <label for="filter_date_range" class="small mb-1">Rentang Tanggal</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">
                            <i class="fa fa-calendar"></i>
                        </span>
                    </div>
                    <input type="text" id="filter_date_range" class="form-control" autocomplete="off">
                </div>
            </div>

            <div class="col-md-3 mb-2">
                <label for="filter_pembayaran" class="small mb-1">Pembayaran</label>
                <select id="filter_pembayaran" class="form-control select2">
                    <option value="">-- Semua Pembayaran --</option>
                    <?php foreach ($pembayaran as $p): ?>
                        <option value="<?= $p->payment_type ?>"><?= $p->payment_type ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2 mb-2">
                <button id="btn_filter" class="btn btn-primary btn-block">
                    <i class="fa fa-filter"></i> Filter
                </button>
            </div>
        </div>
        <table id="table-purchase" class="table table-bordered table-striped" style="width:100%">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Code Purchase</th>
                    <th>Nama Supplier</th>
                    <th>Tanggal</th>
                    <th>Tanggal Jatuh Tempo</th>
                    <th>Pembayaran</th>
                    <th>Status</th>
                    <th>Dibuat Oleh</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
<!-- modal detail -->
<div class="modal fade" id="modal-detail" tabindex="-1" role="dialog" aria-labelledby="modal-detail-label" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modal-detail-label"><i class="fas fa-info-circle mr-2"></i> Detail Purchase</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                 <div class="row">
                    <div class="col-md-6 p-3">
                        <div class="mb-3">
                            <strong class="d-block text-muted text-uppercase small mb-1">Code Purchase</strong>
                            <span class="fs-5 fw-bold text-dark" id="detail-code"></span>
                        </div>
                        <div class="mb-3">
                            <strong class="d-block text-muted text-uppercase small mb-1">Nama Supplier</strong>
                            <span class="fs-5 fw-bold text-dark" id="detail-name"></span>
                        </div>
                        <div class="mb-3">
                            <strong class="d-block text-muted text-uppercase small mb-1">Tanggal</strong>
                            <span class="fs-5 fw-bold text-dark" id="detail-purchase_date"></span>
                        </div>
                        <div class="mb-0">
                            <strong class="d-block text-muted text-uppercase small mb-1">Dibuat Oleh</strong>
                            <span class="fs-5 fw-bold text-dark" id="detail-create-by"></span>
                        </div>
                    </div>

                    <div class="col-md-6 p-3">
                        <div class="mb-3">
                            <strong class="d-block text-muted text-uppercase small mb-1">Tanggal Jatuh Tempo</strong>
                            <span class="fs-5 fw-bold text-dark" id="detail-due_date"></span>
                        </div>
                        <div class="mb-3">
                            <strong class="d-block text-muted text-uppercase small mb-1">Pembayaran</strong>
                            <span class="fs-5 fw-bold text-dark" id="detail-payment_type"></span>
                        </div>
                        <div class="mb-0">
                            <strong class="d-block text-muted text-uppercase small mb-1">Status</strong>
                            <span class="fs-5 fw-bold text-dark" id="detail-status"></span>
                        </div>
                    </div>
                </div>
                <!-- Detail Items -->
                <div class="card table-responsive" id="detail-items">
                    <table class="table table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Kuantitas</th>
                                <th>Harga</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="detail-items-body">
                        </tbody>
                    </table>
                </div>
                <!-- kiri Notes dan kanan subtotal discount, tax, dan grand total -->
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-light py-2">
                                <strong>Deskripsi</strong>
                            </div>
                            <div class="card-body py-2">
                                <p id="detail-notes" class="text-justify mb-0"></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm mb-0">
                            <tr>
                                <th class="text-muted">Subtotal</th>
                                <td>:</td>
                                <td id="detail-subtotal"></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Discount</th>
                                <td>:</td>
                                <td id="detail-discount"></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Pajak</th>
                                <td>:</td>
                                <td id="detail-tax"></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Grand Total</th>
                                <td>:</td>
                                <td id="detail-grand_total" class="font-weight-bold"></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
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
    var selectedStartDate = '';
    var selectedEndDate   = moment().format('YYYY-MM-DD');

    $('#filter_date_range').daterangepicker({
        autoUpdateInput: true,
        startDate: moment(),
        endDate: moment(),
        locale: {
            format: 'YYYY-MM-DD',
            applyLabel: 'Terapkan',
            cancelLabel: 'Batal',
            fromLabel: 'Dari',
            toLabel: 'Sampai',
            customRangeLabel: 'Custom',
            daysOfWeek: ['Min','Sen','Sel','Rab','Kam','Jum','Sab'],
            monthNames: ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'],
        }
    });

    // simpan tanggal terpilih (format YYYY-MM-DD) setiap kali user klik "Terapkan"
    $('#filter_date_range').on('apply.daterangepicker', function(ev, picker) {
        selectedStartDate = picker.startDate.format('YYYY-MM-DD');
        selectedEndDate   = picker.endDate.format('YYYY-MM-DD');
    });

    var table = $('#table-purchase').DataTable({
        processing: true,
        serverSide: false,
        responsive: true,
        lengthChange: true,
        autoWidth: false,
        ajax: {
            url: '<?= base_url('purchase/get_data') ?>',
            type: 'POST',
            dataSrc: '',
            data: function(data){
                data.status      = $('#filter_status').val();
                data.supplier_id = $('#filter_supplier').val();
                data.start_date  = selectedStartDate;
                data.end_date    = selectedEndDate;
            }
        },
        columns: [
            {
                data: null,
                render: function (data, type, row, meta) {
                    return meta.row + 1;
                },
                orderable: false,
                searchable: false
            },
            { data: 'purchase_code' },
            { data: 'nama_supplier' },
            { data: 'purchase_date' },
            { data: 'due_date' },
            { data: 'payment_type' },
            {
                data: 'product_status_name',
                render: function(data, type, row) {
                    return renderStatusBadge(data);
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return `
                        <a href="javascript:void(0)" onclick="detailData(${row.purchase_id})" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i> Detail
                        </a>
                        <a href="<?= base_url('purchase/edit/') ?>${row.purchase_id}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="${row.purchase_id}" data-code="${row.purchase_code}">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    `;
                }
            }
        ]
    });

    $('#btn_filter').on('click', function(){
        table.ajax.reload();
    });

    $(document).on('click', '.btn-delete', function(){
        var button = $(this);
        var purchaseId = button.data('id');
        var purchaseCode = button.data('code');

        Swal.fire({
            title: 'Hapus purchase ini?',
            text: 'Purchase ' + purchaseCode + ' akan dihapus permanen dan tidak bisa dikembalikan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc3545',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                deletePurchase(purchaseId, purchaseCode);
            }
        });

        function deletePurchase(purchaseId, purchaseCode) {
            $.ajax({
                url: '<?= base_url('purchase/delete') ?>/' + purchaseId,
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire('Berhasil', response.message, 'success');
                        table.ajax.reload();
                    } else {
                        Swal.fire('Gagal', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus purchase.', 'error');
                }
            });
        }
    });

});

function renderStatusBadge(statusName) {
    const statusMap = {
        'Aktif':      'badge-success',
        'Nonaktif':   'badge-secondary',
        'Tertunda':   'badge-secondary',
        'Diproses':   'badge-warning',
        'Diterima':   'badge-info',
        'Selesai':    'badge-success',
        'Dibatalkan': 'badge-danger',
    };
    const cssClass = statusMap[statusName] || 'badge-secondary';
    return `<span class="badge ${cssClass}">${statusName || 'Unknown'}</span>`;
}

function detailData(purchaseId) {
    $.ajax({
        url: '<?= base_url('purchase/detail') ?>/' + purchaseId,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            const header = response.header;
            const details = response.details;

            function formatRupiah(number) {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(number);
            }

            $('#detail-code').text(header.purchase_code);
            $('#detail-name').text(header.nama_supplier);
            $('#detail-purchase_date').text(header.purchase_date);
            $('#detail-due_date').text(header.due_date);
            $('#detail-payment_type').text(header.payment_type);
            $('#detail-status').html(renderStatusBadge(header.product_status_name));

            const itemsBody = $('#detail-items-body');
            itemsBody.empty();

            let subtotalAll = 0;

            details.forEach(item => {
                const qty = parseInt(item.qty);
                const price = parseFloat(item.price);
                const subtotal = qty * price;
                subtotalAll += subtotal;

                itemsBody.append(`
                    <tr>
                        <td>${item.product_name}</td>
                        <td>${qty}</td>
                        <td class="text-right">${formatRupiah(price)}</td>
                        <td class="text-right">${formatRupiah(subtotal)}</td>
                    </tr>
                `);
            });

            $('#detail-subtotal').text(formatRupiah(subtotalAll));
            $('#detail-discount').text(formatRupiah(parseFloat(header.discount)));
            $('#detail-tax').text(formatRupiah(parseFloat(header.tax)));
            $('#detail-grand_total').text(formatRupiah(parseFloat(header.grand_total)));
            $('#detail-notes').text(header.notes || '-');

            $('#modal-detail').modal('show');
        },
        error: function() {
            alert('Gagal memuat detail purchase.');
        }
    });
}
</script> -->