<div class="card">
    <div class="card-header">
        <div class="card-tools">
            <button class="btn btn-primary" id="btn_add">
                <i class="fas fa-plus"></i> Tambah Position
            </button>
        </div>
        
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped" id="table-data" style="width:100%">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th>Nama Position</th>
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
                    <h5 class="modal-title" id="modalFormLabel">Tambah Position</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="input_id">
                    <div class="form-group">
                        <label>Nama Position</label>
                        <input type="text" name="position_name" id="input_position_name" class="form-control">
                        <small class="text-danger" id="error_position_name"></small>
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
                        <input type="hidden" name="status" id="position_status_hidden" value="<?= $inactive_status_id ?>">
                        <input type="checkbox" id="position_status" value="<?= $active_status_id ?>"
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
    $('#position_status').bootstrapToggle();
    $('#position_status').bootstrapToggle('off');
    $('#position_status_hidden').val($('#position_status').data('inactive-status-id'));
    $('#position_status').on('change', function () {
        var activeStatusId = $(this).data('active-status-id');
        var inactiveStatusId = $(this).data('inactive-status-id');
        $('#position_status_hidden').val($(this).prop('checked') ? activeStatusId : inactiveStatusId);
    });

    table = $('#table-data').DataTable({
        processing: true,
        serverSide: false,
        responsive: true,
        ajax: {
            url: '<?= base_url('position/get_data') ?>',
            type: 'POST',
            dataSrc: 'data'
        },
        columns: [
            { data: 'no', orderable: false, searchable: false },
            { data: 'position_name' },
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
                        <button type="button" class="btn btn-sm btn-warning btn-edit" data-id="${row.position_id}">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="${row.position_id}">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    `;
                }
            }
        ]
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
            url: '<?= base_url('position/store') ?>',
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

// buka modal untuk tambah (id=0) atau edit (id>0)
function openForm(id) {
    $('#formData')[0].reset();
    $('.text-danger').text('');
    $('#input_id').val(id);

    var activeStatusId = $('#position_status').data('active-status-id');
    var inactiveStatusId = $('#position_status').data('inactive-status-id');

    if (id == 0) {
        $('#position_status').bootstrapToggle('off');
        $('#position_status_hidden').val(inactiveStatusId);
    } else {
        $('#position_status').bootstrapToggle(activeStatusId ? 'on' : 'off');
        $('#position_status_hidden').val(inactiveStatusId);
    }

    if (id == 0) {
        $('#modalFormLabel').text('Tambah Position');
        $('#modalForm').modal('show');
    } else {
        $('#modalFormLabel').text('Edit Position');

        $.ajax({
            url: '<?= base_url('position/get_by_id/') ?>' + id,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                if (data.status === 'failed') {
                    Swal.fire('Gagal', data.message, 'error');
                    return;
                }
                $('#input_position_name').val(data.position_name);
                var isActive = Number(data.status) === Number(activeStatusId);
                $('#position_status').bootstrapToggle(isActive ? 'on' : 'off');
                $('#position_status_hidden').val(isActive ? activeStatusId : inactiveStatusId);
                $('#modalForm').modal('show');
            },
            error: function () {
                Swal.fire('Gagal', 'Gagal memuat data.', 'error');
            }
        });
    }
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
                url: '<?= base_url('position/delete') ?>',
                type: 'POST',
                data: { 
                    id: id 
                },
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