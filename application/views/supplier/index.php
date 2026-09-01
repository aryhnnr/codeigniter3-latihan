<div class="card">
    <div class="card-header">
        <div class="card-tools">
            <a href="<?= base_url('supplier/create') ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah supplier
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3">
                <select id="filter_status" class="form-control select2">
                    <option value="">-- Semua Status --</option>
                    <option value="1">Aktif</option>
                    <option value="2">Tidak Aktif</option>
                </select>
            </div>
            <div class="col-md-3">
                <button id="btn_filter" class="btn btn-primary">
                    <i class="fa fa-filter"></i> Filter
                </button>
                <!-- <button id="btn_reset" class="btn btn-secondary">Reset</button> -->
            </div>
        </div>
        <table id="table-supplier" class="table table-bordered table-striped" style="width:100%">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Code Supplier</th>
                    <th>Nama Supplier</th>
                    <th>Status</th>
                    <th>Dibuat Oleh</th>
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
    var table = $('#table-supplier').DataTable({
        processing: true,
        serverSide: false,
        responsive: true,
        lengthChange: true,
        autoWidth: false,
        ajax: {
            url: '<?= base_url('supplier/get_data') ?>',
            type: 'POST',
            data: function(data){
                // data.department_id variable yang mau dikirim ke controller
                data.status = $('#filter_status').val();
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
            { data: 'code_supplier' },
            { data: 'nama_supplier' },
            {
                data: 'status',
                render: function(data, type, row) {
                    return data == 1
                        ? '<span class="badge badge-success">Aktif</span>'
                        : '<span class="badge badge-danger">Tidak Aktif</span>';
                }
            },
            {
                data: 'created_by_username',
                render: function(data, type, row) {
                    return data || '-'; 
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return `
                        <a href="<?= base_url('supplier/edit/') ?>${row.id}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="${row.id}" data-code="${row.code_supplier}">
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

    $('#table-supplier').on('click', '.btn-delete', function(){
        var button = $(this);
        Swal.fire({
            title: 'Hapus supplier ini?',
            text: 'Data supplier akan dihapus permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc3545',
            reverseButtons: true
        }).then(function(result){
            if (!result.isConfirmed) return;
            $.post('<?= base_url('supplier/delete') ?>', { 
                id: button.data('id') 
            }, function(response){
                if (response.status === 'success') {
                    Swal.fire('Berhasil', response.message, 'success');
                    table.ajax.reload(null, false);
                } else {
                    Swal.fire('Gagal', response.message, 'error');
                }
            }, 'json').fail(function(){
                Swal.fire('Gagal', 'Terjadi kesalahan pada server.', 'error');
            });
        });
    });
});
</script>