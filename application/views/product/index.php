<div class="card">
    <div class="card-header">
        <div class="card-tools">
            <a href="<?= base_url('product/create') ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Product
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3 align-items-end">
            <div class="col-md-2">
                <label for="filter_status" class="small mb-1">Status</label>
                <select id="filter_status" class="form-control select2">
                    <option value="">-- Semua Status --</option>
                    <?php foreach ($product_status as $ps): ?>
                        <option value="<?= $ps->product_status_id ?>"><?= $ps->product_status_name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="filter_category" class="small mb-1">Category</label>
                <select id="filter_category" class="form-control select2" style="width: 100%;">
                    <option value="">-- Semua Category --</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category->category_id ?>"><?= $category->category_name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="filter_brand" class="small mb-1">Brand</label>
                <select id="filter_brand" class="form-control select2" style="width: 100%;">
                    <option value="">-- Semua Brand --</option>
                    <?php foreach ($brands as $brand): ?>
                        <option value="<?= $brand->brand_id ?>"><?= $brand->brand_name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="filter_type" class="small mb-1">Type</label>
                <select id="filter_type" class="form-control select2" style="width: 100%;">
                    <option value="">-- Semua Type --</option>
                    <?php foreach ($product_type as $type): ?>
                        <option value="<?= $type->product_type_id ?>"><?= $type->product_type_name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button id="btn_filter" class="btn btn-primary w-100">
                    <i class="fa fa-filter"></i> Filter
                </button>
            </div>
        </div>
        <table id="table-product" class="table table-bordered table-striped" style="width:100%">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Code Product</th>
                    <th>Nama Product</th>
                    <th>Category</th>
                    <th>Brand</th>
                    <th>Unit</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Dibuat Oleh</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalDetail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-box mr-2"></i> Detail Product 
                    <span class="badge badge-light ml-2" id="detail-code-header"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <th width="150" class="text-muted">Code Product</th>
                        <td width="10">:</td>
                        <td id="detail-code" class="font-weight-bold"></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Nama Product</th>
                        <td>:</td>
                        <td id="detail-name" class="font-weight-bold"></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Category</th>
                        <td>:</td>
                        <td id="detail-category"></td>
                    </tr>
                    
                    <tr>
                        <th class="text-muted">Brand</th>
                        <td>:</td>
                        <td id="detail-brand"></td>
                    </tr>
                    <tr>
                    <th class="text-muted">Unit</th>
                        <td>:</td>
                        <td id="detail-unit"></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Product Type</th>
                        <td>:</td>
                        <td id="detail-type"></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Status</th>
                        <td>:</td>
                        <td id="detail-status"></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Dibuat Oleh</th>
                        <td>:</td>
                        <td id="detail-create-by"></td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <hr class="my-2">
                        </td>
                    </tr>
                </table>
                <div class="card">
                    <div class="card-header bg-light py-2">
                        <strong><i class="fas fa-align-left mr-1"></i> Deskripsi</strong>
                    </div>
                    <div class="card-body py-2">
                        <p id="detail-deskripsi" class="text-justify mb-0"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
window.addEventListener('load', function () {
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    var table = $('#table-product').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        lengthChange: true,
        autoWidth: false,
        order: [],
        ajax: {
            url: '<?= base_url('product/get_data') ?>',
            type: 'POST',
            data: function(data){
                data.status         = $('#filter_status').val();
                data.category_id    = $('#filter_category').val();
                data.brand_id       = $('#filter_brand').val();
                data.product_type   = $('#filter_type').val();
            }
        },
        columns: [
            { data: 'no', orderable: false, searchable: false },
            { data: 'product_code' },
            { data: 'product_name' },
            { data: 'category_name' },
            { data: 'brand_name' },
            { data: 'unit_name' },
            { data: 'product_type_name' },
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
                width: "150px",
                render: function (data, type, row) {
                    return `
                        <div class="d-flex flex-wrap mb-n2">
                            <a href="javascript:void(0)" onclick="detailData(${row.product_id})" class="btn btn-sm btn-info mr-2 mb-2">
                                <i class="fas fa-eye"></i> Detail
                            </a>
                            <a href="<?= base_url('product/edit/') ?>${row.product_id}" class="btn btn-sm btn-warning mr-2 mb-2">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <button type="button" class="btn btn-sm btn-danger btn-delete" onclick="tombolDelete(this)" data-id="${row.product_id}" data-code="${row.product_code}">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </div>`;
                }
            }
        ]
    });

    $('#btn_filter').on('click', function(){
        table.ajax.reload();
    });

    // taruh function delete di dalam sini supaya bisa akses variabel 'table'
    window.tombolDelete = function(btn) {
        const id = $(btn).data('id');
        const code = $(btn).data('code');

        Swal.fire({
            title: 'Hapus product ini?',
            text: 'Product ' + code + ' akan dihapus permanen dan tidak bisa dikembalikan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc3545',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('product/delete') ?>',
                    type: 'POST',
                    data: { id: id },
                    dataType: 'json',
                    beforeSend: function() {
                        Swal.fire({
                            title: 'Menghapus...',
                            allowOutsideClick: false,
                            didOpen: () => { Swal.showLoading(); }
                        });
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire('Berhasil', response.message, 'success');
                            table.ajax.reload(null, false);
                        } else {
                            Swal.fire('Gagal', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Gagal', 'Terjadi kesalahan pada server', 'error');
                    }
                });
            }
        });
    };
});

function detailData(id) {
    $.ajax({
        url: '<?= base_url('product/get_detail/') ?>' + id,
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            $('#detail-code-header').text(response.product_code);
            $('#detail-code').text(response.product_code);
            $('#detail-name').text(response.product_name);
            $('#detail-category').text(response.category_name);
            $('#detail-brand').text(response.brand_name);
            $('#detail-unit').text(response.unit_name);
            $('#detail-type').text(response.product_type_name);
            const statusBadge = response.status == 1
                ? '<span class="badge badge-success">Aktif</span>'
                : '<span class="badge badge-danger">Tidak Aktif</span>';
            $('#detail-status').html(statusBadge);
            $('#detail-create-by').text(response.created_by_username ? response.created_by_username : '-');
            $('#detail-deskripsi').text(response.description);
            $('#modalDetail').modal('show');
        },
        error: function () {
            Swal.fire('Gagal', 'Data tidak ditemukan', 'error');
        }
    });

}


</script>