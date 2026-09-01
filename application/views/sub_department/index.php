<div class="card">
    <div class="card-header">
        <div class="card-tools">
            <button class="btn btn-primary" id="btn_add">
                <i class="fas fa-plus"></i> Tambah Sub Department
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3">
                <select id="filter_department" class="form-control select2">
                    <option value="">Pilih Department</option>
                    <?php foreach ($department as $item) : ?>
                        <option value="<?= $item->department_id ?>"><?= $item->department_name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button id="btn_filter" class="btn btn-primary">
                    <i class="fa fa-filter"></i> Filter
                </button>
                <!-- <button id="btn_reset" class="btn btn-secondary">Reset</button> -->
            </div>
        </div>
        <table class="table table-bordered table-striped" id="table-data" style="width:100%">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th>Sub Department</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th width="15%">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Modal Form -->
<div class="modal fade" id="modalForm" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formData">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalFormLabel">Tambah Sub Department</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="input_id">
                    <div class="form-group">
                        <label>Nama Sub Department</label>
                        <input type="text" name="sub_department_name" id="input_sub_department_name" class="form-control">
                        <small class="text-danger" id="error_sub_department_name"></small>
                    </div>
                    <div class="form-group">
                        <label>Department</label>
                        <select name="department_id" id="department_id" class="form-control select2">
                            <option value="">Pilih Department</option>
                            <?php foreach ($department as $item) : ?>
                                <option value="<?= $item->department_id ?>"><?= $item->department_name ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-danger" id="error_department_id"></small>
                    </div>
                    
                    <div class="form-group">
                        <label>Status</label>
                        <br>
                        <?php
                            $active_status_id = '';
                            $inactive_status_id = '';
                            foreach ($status as $item) {
                                $status_name = strtolower(trim($item->product_status_name));
                                if ($status_name === 'aktif') {
                                    $active_status_id = (int) $item->product_status_id;
                                } elseif ($status_name === 'nonaktif' || $status_name === 'tidak aktif') {
                                    $inactive_status_id = (int) $item->product_status_id;
                                }
                            }
                        ?>
                        <input type="hidden" name="status" id="sub_department_status_hidden" value="<?= $inactive_status_id ?>">
                        <input type="checkbox" id="sub_department_status" value="<?= $active_status_id ?>"
                            data-toggle="toggle" data-on="Aktif" data-off="Nonaktif"
                            data-onstyle="success" data-offstyle="secondary" data-width="110"
                            data-active-status-id="<?= $active_status_id ?>"
                            data-inactive-status-id="<?= $inactive_status_id ?>">
                        <small class="text-danger" id="error_status"></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var table;

window.addEventListener('load', function () {
    $('#department_id').select2({
        theme: 'bootstrap4',
        width: '100%',
        dropdownParent: $('#modalForm'),
        placeholder: 'Pilih Department',
        allowClear: true
    });

    $('#filter_department').select2({
        theme: 'bootstrap4',
        width: '100%',
        placeholder: 'Pilih Department',
        allowClear: true
    });

    $('#sub_department_status').bootstrapToggle();
    $('#sub_department_status').bootstrapToggle('off');
    $('#sub_department_status_hidden').val($('#sub_department_status').data('inactive-status-id'));
    $('#sub_department_status').on('change', function () {
        var activeStatusId = $(this).data('active-status-id');
        var inactiveStatusId = $(this).data('inactive-status-id');
        $('#sub_department_status_hidden').val($(this).prop('checked') ? activeStatusId : inactiveStatusId);
    });

    table = $('#table-data').DataTable({
        processing: true,
        serverSide: false,
        responsive: true,
        ajax: {
            url: '<?= base_url('sub_department/get_data') ?>',
            type: 'POST',
            dataSrc: 'data',
            data: function(data){
                data.department_id = $('#filter_department').val();
            }
        },
        columns: [
            { data: 'no', orderable: false, searchable: false },
            { data: 'sub_department_name' },
            { data: 'department_name' },
            {
                data: 'product_status_name',
                render: function (data, type, row) {
                    var statusName = (data || '').toLowerCase().trim();
                    var activeNames = ['aktif', 'active'];
                    var inactiveNames = ['nonaktif', 'tidak aktif', 'tidak_aktif', 'inactive'];

                    if (activeNames.indexOf(statusName) !== -1) {
                        return '<span class="badge badge-success">Aktif</span>';
                    }

                    if (inactiveNames.indexOf(statusName) !== -1) {
                        return '<span class="badge badge-secondary">Nonaktif</span>';
                    }

                    return '<span class="badge badge-secondary">Nonaktif</span>';
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return `
                        <button type="button" class="btn btn-sm btn-warning btn-edit" data-id="${row.sub_department_id}">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="${row.sub_department_id}">
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

    // tombol tambah
    $('#btn_add').on('click', function () {
        openForm(0);
    });

    // tombol edit (delegasi karena elemen dinamis dari DataTables)
    $(document).on('click', '.btn-edit', function () {
        openForm($(this).data('id'));
    });

    // tombol delete
    $(document).on('click', '.btn-delete', function () {
        var id = $(this).data('id');
        confirmDelete(id);
    });

    // submit form (insert & update)
    $('#formData').on('submit', function (e) {
        e.preventDefault();
        $('.text-danger').text('');

        var formData = $(this).serialize();

        $.ajax({
            url: '<?= base_url('sub_department/store') ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    Swal.fire('Berhasil', response.message, 'success');
                    $('#modalForm').modal('hide');
                    table.ajax.reload(null, false);
                } else if (response.errors) {
                    $.each(response.errors, function (field, message) {
                        $('#error_' + field).text(message);
                    });
                } else {
                    Swal.fire('Gagal', response.message, 'error');
                }
            },
            error: function () {
                Swal.fire('Gagal', 'Terjadi kesalahan pada server.', 'error');
            }
        });
    });
});

function openForm(id) {
    $('#formData')[0].reset();
    $('.text-danger').text('');
    $('#input_id').val(id);
    $('#department_id').val('');

    var activeStatusId = $('#sub_department_status').data('active-status-id');
    var inactiveStatusId = $('#sub_department_status').data('inactive-status-id');

    if (id == 0) {
        $('#sub_department_status').bootstrapToggle('off');
        $('#sub_department_status_hidden').val(inactiveStatusId);
        $('#department_id').val('').trigger('change');
        $('#modalFormLabel').text('Tambah Sub Department');
        $('#modalForm').modal('show');
        return;
    }

    $('#modalFormLabel').text('Edit Sub Department');

    $('#department_id').val('').trigger('change');

    $.ajax({
        url: '<?= base_url('sub_department/get_by_id/') ?>' + id,
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            if (data.status === 'failed') {
                Swal.fire('Gagal', data.message, 'error');
                return;
            }
            $('#input_sub_department_name').val(data.sub_department_name);
            $('#department_id').val(data.department_id).trigger('change');
            var isActive = Number(data.status) === Number(activeStatusId);
            $('#sub_department_status').bootstrapToggle(isActive ? 'on' : 'off');
            $('#sub_department_status_hidden').val(isActive ? activeStatusId : inactiveStatusId);
            $('#modalForm').modal('show');
        },
        error: function () {
            Swal.fire('Gagal', 'Gagal memuat data.', 'error');
        }
    });
}

function confirmDelete(id) {
    Swal.fire({
        title: 'Hapus data ini?',
        text: 'Data yang dihapus tidak bisa dikembalikan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc3545',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= base_url('sub_department/delete') ?>',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        Swal.fire('Berhasil', response.message, 'success');
                        table.ajax.reload(null, false);
                    } else {
                        Swal.fire('Gagal', response.message, 'error');
                    }
                },
                error: function () {
                    Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus.', 'error');
                }
            });
        }
    });
}
</script>