<div class="card">
    <div class="card-header d-flex align-items-center">
        <!-- <h3 class="card-title">Data Ticket</h3> -->
         <div>
            <h5 class="mb-0"><i class="fas fa-ticket-alt mr-2"></i>Data Ticket</h5>
            <!-- <small class="text-muted">Kelola akun dan data employee yang terhubung.</small> -->
        </div>
        <a href="<?= base_url('ticket/create') ?>" class="btn btn-primary ml-auto">
            <i class="fas fa-plus"></i> Tambah Ticket
        </a>
    </div>
    <div class="card-body">
         <!-- Form Filter -->
        <form id="form-filter" class="d-flex flex-wrap align-items-end my-3" style="gap: 10px;">

            <div style="min-width: 180px;">
                <!-- <label class="small text-muted mb-1">Status</label> -->
                <select name="status" id="filter-status " class="form-control select2" style="width: 100%;">
                    <option value="">-- Semua Status --</option>
                    <?php foreach ($status_list as $s) : ?>
                        <option value="<?= $s ?>"><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="min-width: 180px;">
                <!-- <label class="small text-muted mb-1">Prioritas</label> -->
                <select name="prioritas" id="filter-prioritas" class="form-control select2" style="width: 100%;">
                    <option value="">-- Semua Prioritas --</option>
                    <?php foreach ($prioritas_list as $p) : ?>
                        <option value="<?= $p ?>"><?= $p ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="min-width: 180px;">
                <!-- <label class="small text-muted mb-1">Teknisi</label> -->
                <select name="teknisi_id" id="filter-teknisi" class="form-control select2" style="width: 100%;">
                    <option value="">-- Semua Teknisi --</option>
                    <?php foreach ($teknisi as $t) : ?>
                        <option value="<?= $t->id ?>"><?= $t->nama ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <button type="submit" id="btn-filter" class="btn btn-primary">
                Filter
                </button>
            </div>

        </form>

        <table id="table-ticket" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>No</th>
                <th>No Ticket</th>
                <th>Pemohon</th>
                <th>Departemen</th>
                <th>Judul</th>
                <th>Prioritas</th>
                <th>Status</th>
                <th>Teknisi</th>
                <th>Dibuat Oleh</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; ?>
            <?php foreach ($tickets as $t) : ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= $t->ticket_number ?></td>
                <td><?= $t->nama_pemohon ?></td>
                <td><?= $t->department_name ?></td>
                <td><?= $t->judul ?></td>
                <td>
                    <span class="badge <?= prioritas_badge_class($t->prioritas) ?>">
                        <?= $t->prioritas ?></td>
                    </span>
                    
                <td>
                    <span class="badge <?= status_badge_class($t->status) ?>">
                        <?= $t->status ?>
                    </span>
                </td>
                <td><?= $t->teknisi_name ? $t->teknisi_name : '-' ?></td>
                <td><?= $t->created_by_username ? $t->created_by_username : '-' ?></td>
                <td class="d-flex flex-wrap">
                    <button type="button" class="btn btn-sm btn-info mb-2 mr-2 btn-detail" data-id="<?= $t->id ?>"><i class="fas fa-eye"></i> Detail</button>
                    <?php if ($t->status == 'OPEN' || $t->status == 'IN PROGRESS') : ?>
                        <a href="<?= base_url('ticket/edit/' . $t->id) ?>"
                        class="btn btn-sm btn-warning mb-2 mr-2">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        </table>
    </div>
</div>
<!-- Modal Detail Ticket -->
<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-ticket-alt mr-2"></i> Detail TIcket 
                    <span class="badge badge-light ml-2" id="modalTicketNumber"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <!-- KOLOM KIRI: Data ticket (read-only) -->
                    <div class="col-md-6">
                        <table class="table table-striped mb-3">
                            <tr>
                                <th style="width: 120px;">Pemohon</th>
                                <td id="modalPemohon"></td>
                            </tr>
                            <tr>
                                <th>Departemen</th>
                                <td id="modalDepartemen"></td>
                            </tr>
                            <tr>
                                <th>Judul</th>
                                <td id="modalJudul"></td>
                            </tr>
                            <tr>
                                <th>Prioritas</th>
                                <td id="modalPrioritas"></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td id="modalStatus"></td>
                            </tr>
                            <tr>
                                <th>Dibuat Oleh</th>
                                <td id="modalCreateBy"></td>
                            </tr>
                        </table>

                        <div class="card">
                            <div class="card-header bg-light py-2">
                                <strong><i class="fas fa-align-left mr-1"></i> Deskripsi</strong>
                            </div>
                            <div class="card-body py-2">
                                <p id="modalDeskripsi" class="text-justify mb-0"></p>
                            </div>
                        </div>
                    </div>

                    <!-- KOLOM KANAN: Form Assign Teknisi + Form Update Status -->
                    <div class="col-md-6">

                        <div class="card card-outline card-primary mb-3">
                            <div class="card-header py-2">
                                <h6 class="card-title mb-0">Assign Teknisi</h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-2">Teknisi saat ini: <strong id="teknisi-text">-</strong></p>

                                <select id="select-teknisi" class="form-control modal-select2 mb-2">
                                    <option value="">-- Pilih Teknisi --</option>
                                    <?php foreach ($teknisi as $t) : ?>
                                        <option value="<?= $t->id ?>"><?= $t->nama ?></option>
                                    <?php endforeach; ?>
                                </select>

                                <button type="button" id="btn-assign" class="btn btn-primary btn-block mt-2">
                                Assign
                                </button>
                            </div>
                        </div>
                        
                        <div class="card card-outline card-warning mb-3" id="card-update-status">
                            <div class="card-header py-2">
                                <h6 class="card-title mb-0">Update Status</h6>
                            </div>
                            <div class="card-body" id="update-status-area">
                                <!-- Diisi otomatis via JS setelah detail ticket dimuat -->
                            </div>
                        </div>

                    </div>

                </div>
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
        width: '200px'
    });

    $('#table-ticket').DataTable({
        responsive: true,
        lengthChange: true,
        autoWidth: false,
    });
    // Dropdown
    function getAllowedNextStatus(currentStatus) {
        var map = {
            'OPEN': ['IN PROGRESS', 'CANCELLED'],
            'IN PROGRESS': ['DONE', 'CANCELLED'],
            'DONE': [],
            'CANCELLED': []
        };
        return map[currentStatus] || [];
    }
    var statusColorMap = {
        <?php foreach (['OPEN', 'IN PROGRESS', 'DONE', 'CANCELLED'] as $s) : ?>
            '<?= $s ?>': '<?= status_badge_class($s) ?>',
        <?php endforeach; ?>
    };

    var prioritasColorMap = {
        <?php foreach (['Low', 'Normal', 'High', 'Urgent'] as $p) : ?>
            '<?= $p ?>': '<?= prioritas_badge_class($p) ?>',
        <?php endforeach; ?>
    };

    function statusBadge(status) {
        var badge = statusColorMap[status] || 'badge-secondary';
        return '<span class="badge ' + badge + '">' + status + '</span>';
    }

    function prioritasBadge(prioritas) {
        var badge = prioritasColorMap[prioritas] || 'badge-secondary';
        return '<span class="badge ' + badge + '">' + prioritas + '</span>';
    }

    // Fungsi untuk load & render detail ticket ke modal
    function loadDetailTicket(id) {
        $.ajax({
            url: '<?= base_url("ticket/get_detail_json") ?>/' + id,
            method: 'GET',
            dataType: 'json',
            success: function (res) {
                if (!res.status) {
                    alert(res.message);
                    return;
                }

                var d = res.data;

                $('#modalDetail').data('current-id', d.id);
                $('#modalTicketNumber').text(d.ticket_number);
                $('#modalPemohon').text(d.nama_pemohon);
                $('#modalDepartemen').text(d.department_name);
                $('#modalJudul').text(d.judul);
                $('#modalDeskripsi').text(d.deskripsi);
                $('#modalPrioritas').html(prioritasBadge(d.prioritas));
                $('#modalStatus').html(statusBadge(d.status));
                $('#modalCreateBy').text(d.created_by_username ? d.created_by_username : '-');
                $('#teknisi-text').text(d.teknisi_name ? d.teknisi_name : '-');

                var isFinal = (d.status === 'DONE' || d.status === 'CANCELLED');

                $('#select-teknisi').val(d.teknisi_id ? d.teknisi_id : '').trigger('change');
                $('#select-teknisi').prop('disabled', isFinal);

                // Tombol Assign disembunyikan total kalau status sudah final
                $('#btn-assign').toggle(!isFinal);

                if (d.status === 'OPEN' && !d.teknisi_id) {
                    $('#card-update-status').hide();
                } else {
                    $('#card-update-status').show();
                    var statusHtml = '';
                    // Menentukan  dropdown status
                    var statusOptions = isFinal ? [d.status] : getAllowedNextStatus(d.status);

                    statusHtml += '<select id="select-status" class="form-control select2 mb-2" ' + (isFinal ? 'disabled' : '') + '>';
                    statusOptions.forEach(function (s) {
                        statusHtml += '<option value="' + s + '"' + (s === d.status ? ' selected' : '') + '>' + s + '</option>';
                    });
                    statusHtml += '</select>';

                    statusHtml += '<textarea id="catatan-teknisi" class="form-control my-2 " rows="3" placeholder="Catatan (wajib diisi untuk DONE/CANCELLED)" ' + (isFinal ? 'disabled' : '') + '>' + (d.catatan_teknisi ? d.catatan_teknisi : '') + '</textarea>';

                    // Tombol Update Status HANYA muncul kalau belum final
                    if (!isFinal) {
                        statusHtml += '<button type="button" id="btn-update-status" class="btn btn-warning btn-block">Update Status</button>';
                    }
                }

                $('#update-status-area').html(statusHtml);
                $('#select-teknisi').select2({
                    dropdownParent: $('#modalDetail'),
                    theme: 'bootstrap4',
                    width: '100%'
                });

                if ($('#select-status').length) {
                    $('#select-status').select2({
                        dropdownParent: $('#modalDetail'),
                        theme: 'bootstrap4',
                        width: '100%'
                    });
                }

                $('#modalDetail').modal('show');
            },
            error: function () {
                alert('Gagal mengambil data ticket.');
            }
        });
    }

    // Buka modal saat klik tombol Detail
    $(document).on('click', '.btn-detail', function () {
        var id = $(this).data('id');
        loadDetailTicket(id);
    });

    // Assign teknisi, dengan konfirmasi SweetAlert
    $(document).on('click', '#btn-assign', function () {
        var ticket_id = $('#modalDetail').data('current-id');
        var teknisi_id = $('#select-teknisi').val();
        var teknisi_name = $('#select-teknisi option:selected').text();

        if (!teknisi_id) {
            Swal.fire('Gagal', 'Pilih teknisi terlebih dahulu.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Assign Teknisi?',
            text: 'Ticket akan di-assign ke ' + teknisi_name + '.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Assign',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: '<?= base_url("ticket/assign_teknisi_ajax") ?>',
                method: 'POST',
                data: { ticket_id: ticket_id, teknisi_id: teknisi_id },
                dataType: 'json',
                success: function (res) {
                    if (res.status) {
                        Swal.fire('Berhasil', res.message, 'success');
                        loadDetailTicket(ticket_id);
                        refreshTable();
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                },
                error: function () {
                    Swal.fire('Gagal', 'Terjadi kesalahan, coba lagi.', 'error');
                }
            });
        });
    });

    // Update status, dengan konfirmasi SweetAlert (khusus DONE/CANCELLED ditegaskan lebih jelas)
    $(document).on('click', '#btn-update-status', function () {
        var ticket_id = $('#modalDetail').data('current-id');
        var status_baru = $('#select-status').val();
        var catatan = $('#catatan-teknisi').val();

        var isFinalTarget = (status_baru === 'DONE' || status_baru === 'CANCELLED');

        // Validasi ringan di frontend, validasi sesungguhnya tetap di server
        if (isFinalTarget && !catatan.trim()) {
            Swal.fire('Gagal', 'Catatan wajib diisi untuk status ' + status_baru + '.', 'warning');
            return;
        }

        var confirmText = isFinalTarget
            ? 'Status akan diubah menjadi ' + status_baru + '. Tindakan ini bersifat final dan tidak dapat diubah lagi setelahnya.'
            : 'Status akan diubah menjadi ' + status_baru + '.';

        Swal.fire({
            title: 'Ubah Status?',
            text: confirmText,
            icon: isFinalTarget ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Ubah',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: '<?= base_url("ticket/update_status_ajax") ?>/' + ticket_id,
                method: 'POST',
                data: { status: status_baru, catatan_teknisi: catatan },
                dataType: 'json',
                success: function (res) {
                    if (res.status) {
                        Swal.fire('Berhasil', res.message, 'success');
                        loadDetailTicket(ticket_id);
                        refreshTable();
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                },
                error: function () {
                    Swal.fire('Gagal', 'Terjadi kesalahan, coba lagi.', 'error');
                }
            });
        });
    });

    function refreshTable() {
        $.ajax({
            url: '<?= base_url("ticket/get_data_json") ?>',
            type: 'GET',
            dataType: 'json',
            data: {
                status: $('#filter-status').val(),
                prioritas: $('#filter-prioritas').val(),
                teknisi_id: $('#filter-teknisi').val()
            },
            success: function (res) {
                var html = '';
                if (res.data.length > 0) {
                    $.each(res.data, function (i, t) {
                        html += `
                            <tr>
                                <td>${i + 1}</td>
                                <td>${t.ticket_number}</td>
                                <td>${t.nama_pemohon}</td>
                                <td>${t.department_name}</td>
                                <td>${t.judul}</td>
                                <td>${prioritasBadge(t.prioritas)}</td>
                                <td>${statusBadge(t.status)}</td>
                                <td>${t.teknisi_name ? t.teknisi_name : '-'}</td>
                                <td>${t.created_by_username ? t.created_by_username : '-'}</td>
                                <td class="d-flex flex-wrap">
                                    <button type="button" class="btn btn-sm btn-info btn-detail mr-2 mb-2" data-id="${t.id}"><i class="fas fa-eye"></i> Detail</button>
                                    ${(t.status === 'OPEN' || t.status === 'IN PROGRESS') ? `<a href="<?= base_url('ticket/edit/') ?>${t.id}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>` : ''}
                                </td>
                            </tr>
                        `;
                    });
                }

                $('#table-ticket').DataTable().destroy();
                $('#table-ticket tbody').html(html);
                $('#table-ticket').DataTable({
                    responsive: true,
                    lengthChange: true,
                    autoWidth: false,
                    language: { emptyTable: "Tidak ada data ticket." }
                });
            },
            error: function (xhr) {
                console.log(xhr.responseText);
                alert('Gagal mengambil data ticket.');
            }
        });
    }

    // Form filter
    $('#form-filter').on('submit', function (e) {
        e.preventDefault();
        refreshTable();
    });

});
</script>