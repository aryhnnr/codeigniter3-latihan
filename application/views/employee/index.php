<div class="card">
    <div class="card-header">
        <div class="card-tools">
            <a href="<?= base_url('employee/create') ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah employee
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3">
                <select id="filter_status" class="form-control select2">
                    <option value="">-- Semua Status --</option>
                    <option value="1">Aktif</option>
                    <option value="0">Tidak Aktif</option>
                </select>
            </div>
            <div class="col-md-3">
                <select id="filter_departement" class="form-control select2">
                    <option value="">-- Semua Departement --</option>
                    <?php foreach ($departement_list as $departement): ?>
                        <option value="<?= $departement->department_id ?>"><?= $departement->department_name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select id="filter_position" class="form-control select2">
                    <option value="">-- Semua Position --</option>
                    <?php foreach ($position_list as $position): ?>
                        <option value="<?= $position->position_id ?>"><?= $position->position_name ?></option>
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
        <table id="table-employee" class="table table-bordered table-striped" style="width:100%">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Code Employee</th>
                    <th>Nama Employee</th>
                    <th>Departemen</th>
                    <th>Posisi</th>
                    <th>Salary</th>
                    <th>Status</th>
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
                   <li class="fa fa-user mr-2"></li> Detail Employee <span class="badge badge-light ml-2" id="detail-code-header"></span></h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="150">Code</th>
                        <td>:</td>
                        <td id="detail-code"></td>
                    </tr>
                    <tr>
                        <th>Nama</th>
                        <td>:</td>
                        <td id="detail-name"></td>
                    </tr>
                    <tr>
                        <th>Role</th>
                        <td>:</td>
                        <td id="detail-role"></td>
                    </tr>
                    <tr>
                        <th>Departemen</th>
                        <td>:</td>
                        <td id="detail-department"></td>
                    </tr>
                    <tr>
                        <th>Sub Departemen</th>
                        <td>:</td>
                        <td id="detail-sub-department"></td>
                    </tr>
                    <tr>
                        <th>Posisi</th>
                        <td>:</td>
                        <td id="detail-position"></td>
                    </tr>
                    <tr>
                        <th>Salary</th>
                        <td>:</td>
                        <td id="detail-salary"></td>
                    </tr>
                    <tr>
                        <th>Join Date</th>
                        <td>:</td>
                        <td id="detail-join-date"></td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
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
    var table = $('#table-employee').DataTable({
        processing: true,
        serverSide: false,
        responsive: true,
        lengthChange: true,
        autoWidth: false,
        ajax: {
            url: '<?= base_url('employee/get_data') ?>',
            type: 'POST',
            data: function(data){
                // data.department_id variable yang mau dikirim ke controller
                data.status = $('#filter_status').val();
                data.department_id = $('#filter_departement').val();
                data.position_id = $('#filter_position').val();
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
            { data: 'employee_code' },
            {
                data: null,
                render: function(data, type, row) {
                    return `
                        <div>
                            <strong>${row.employee_name || '-'}</strong>
                            <div class="text-muted small">
                                Role: ${row.role_name || '-'} 
                            </div>
                        </div>
                    `;
                }
            },
            // { data: 'department_name' },
            {
                data: null,
                render: function(data, type, row) {
                    return `
                        <div>
                            <strong>${row.department_name || '-'}</strong>
                            <div class="text-muted small">
                                ${row.sub_department_name || '-'} 
                            </div>
                        </div>
                    `;
                }
            },
            { data: 'position_name' },
            { data: 'salary',
                render: function(data, type, row) {
                    return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                    }).format(data);
                }
            },
            {
                data: 'status',
                render: function(data, type, row) {
                    return data == 1
                        ? '<span class="badge badge-success">Aktif</span>'
                        : '<span class="badge badge-danger">Tidak Aktif</span>';
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return `
                        <a href="javascript:void(0)" onclick="detailData(${row.employee_id})" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i> Detail
                        </a>
                        <a href="<?= base_url('employee/edit/') ?>${row.employee_id}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>`;
                }
            }
        ]
    });

    $('#btn_filter').on('click', function(){
        table.ajax.reload();
    });
});

function detailData(id) {
    $.ajax({
        url: '<?= base_url('employee/get_detail/') ?>' + id,
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            $('#detail-code-header').text(response.employee_code);
            $('#detail-code').text(response.employee_code);
            $('#detail-name').text(response.employee_name);
            $('#detail-role').text(response.role_name || '-');
            $('#detail-department').text(response.department_name || '-');
            $('#detail-sub-department').text(response.sub_department_name || '-');
            $('#detail-position').text(response.position_name);
            $('#detail-salary').text('Rp ' + Number(response.salary).toLocaleString('id-ID'));
            $('#detail-join-date').text(response.join_date);
            $('#modalDetail').modal('show');
        },
        error: function () {
            Swal.fire('Gagal', 'Data tidak ditemukan', 'error');
        }
    });
}
</script>