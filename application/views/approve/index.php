<div class="card">
    <div class="card-header">
        <div class="card-tools">
            <a href="<?= base_url('approve/create') ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Approval
            </a>
        </div>
    </div>
    <div class="card-body">
       <!-- <div class="row mb-3 align-items-end">
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
        </div> -->
        <table id="table-approval" class="table table-bordered table-striped" style="width:100%">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Code Approval</th>
                    <th>Nama Approval</th>
                    <th>Menu Approval</th>
                    <th>Status</th>
                    <th>Dibuat Oleh</th>
                    <th>Tanggal Dibuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script>
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

    var table = $('#table-approval').DataTable({
        processing: true,
        serverSide: false,
        responsive: true,
        lengthChange: true,
        autoWidth: false,
        ajax: {
            url: '<?= base_url('approve/get_data') ?>',
            type: 'POST',
            dataSrc: '',
            data: function(data){
                // data.status      = $('#filter_status').val();
                // data.supplier_id = $('#filter_supplier').val();
                // data.start_date  = selectedStartDate;
                // data.end_date    = selectedEndDate;
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
            { 
                data: null,
                render: function(data, type, row) {
                    return `
                        <div>
                            <a href="<?= base_url('approve/detail/') ?>${row.approval_id}" class="text-decoration-none">
                                <strong>${row.approval_code || '-'}</strong>
                        </div>
                    `;
                }
            },
            { data: 'approval_name' },
            { data: 'approval_menu' },
            {
                data: 'product_status_name',
                render: function(data, type, row) {
                    return renderStatusBadge(data);
                }
            },
            { data: 'created_by_name' },
            { data: 'created_at' },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return `

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

    // $('#btn_filter').on('click', function(){
    //     table.ajax.reload();
    // });

    // $(document).on('click', '.btn-delete', function(){
    //     var button = $(this);
    //     var purchaseId = button.data('id');
    //     var purchaseCode = button.data('code');

    //     Swal.fire({
    //         title: 'Hapus purchase ini?',
    //         text: 'Purchase ' + purchaseCode + ' akan dihapus permanen dan tidak bisa dikembalikan.',
    //         icon: 'warning',
    //         showCancelButton: true,
    //         confirmButtonText: 'Ya, hapus',
    //         cancelButtonText: 'Batal',
    //         confirmButtonColor: '#dc3545',
    //         reverseButtons: true
    //     }).then((result) => {
    //         if (result.isConfirmed) {
    //             deletePurchase(purchaseId, purchaseCode);
    //         }
    //     });

    //     function deletePurchase(purchaseId, purchaseCode) {
    //         $.ajax({
    //             url: '<?= base_url('purchase/delete') ?>/' + purchaseId,
    //             type: 'POST',
    //             dataType: 'json',
    //             success: function(response) {
    //                 if (response.status === 'success') {
    //                     Swal.fire('Berhasil', response.message, 'success');
    //                     table.ajax.reload();
    //                 } else {
    //                     Swal.fire('Gagal', response.message, 'error');
    //                 }
    //             },
    //             error: function() {
    //                 Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus purchase.', 'error');
    //             }
    //         });
    //     }
    // });

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

// function detailData(purchaseId) {
//     $.ajax({
//         url: '<?= base_url('purchase/detail') ?>/' + purchaseId,
//         type: 'GET',
//         dataType: 'json',
//         success: function(response) {
//             const header = response.header;
//             const details = response.details;

//             function formatRupiah(number) {
//                 return new Intl.NumberFormat('id-ID', {
//                     style: 'currency',
//                     currency: 'IDR',
//                     minimumFractionDigits: 0
//                 }).format(number);
//             }

//             $('#detail-code').text(header.purchase_code);
//             $('#detail-name').text(header.nama_supplier);
//             $('#detail-purchase_date').text(header.purchase_date);
//             $('#detail-due_date').text(header.due_date);
//             $('#detail-payment_type').text(header.payment_type);
//             $('#detail-status').html(renderStatusBadge(header.product_status_name));

//             const itemsBody = $('#detail-items-body');
//             itemsBody.empty();

//             let subtotalAll = 0;

//             details.forEach(item => {
//                 const qty = parseInt(item.qty);
//                 const price = parseFloat(item.price);
//                 const subtotal = qty * price;
//                 subtotalAll += subtotal;

//                 itemsBody.append(`
//                     <tr>
//                         <td>${item.product_name}</td>
//                         <td>${qty}</td>
//                         <td class="text-right">${formatRupiah(price)}</td>
//                         <td class="text-right">${formatRupiah(subtotal)}</td>
//                     </tr>
//                 `);
//             });

//             $('#detail-subtotal').text(formatRupiah(subtotalAll));
//             $('#detail-discount').text(formatRupiah(parseFloat(header.discount)));
//             $('#detail-tax').text(formatRupiah(parseFloat(header.tax)));
//             $('#detail-grand_total').text(formatRupiah(parseFloat(header.grand_total)));
//             $('#detail-notes').text(header.notes || '-');

//             $('#modal-detail').modal('show');
//         },
//         error: function() {
//             alert('Gagal memuat detail purchase.');
//         }
//     });
// }
</script>