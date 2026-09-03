<form id="formApproval" action="<?= base_url('approve/update/' . $approval->approval_id) ?>" method="POST">

    <div class="card mb-3">
        <div class="card-header">
            <i class="fas fa-user-check text-primary"></i> Edit Approval
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    
                    <div class="form-group">
                        <label>Kode Approval</label>
                        <input type="text" class="form-control" value="<?= $approval->approval_code ?? '' ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label>Nama Approval</label>
                        <input type="text" name="approval_name" id="approval_name" class="form-control" value="<?= $approval->approval_name ?? '' ?>" placeholder="Masukkan Nama Approval">
                        <small class="text-danger" id="error_approval_name"></small>
                    </div>

                    <div class="form-group">
                        <label>Pilih Menu</label>
                        <select name="approval_menu" id="approval_menu" class="form-control form-select2">
                            <option value="">-- Pilih Menu --</option>
                            <?php foreach ($menus as $m): ?>
                                <option value="<?= $m->id ?>" <?= (isset($approval->approval_menu) && $approval->approval_menu == $m->id) ? 'selected' : '' ?>>
                                    <?= $m->name ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-danger" id="error_approval_menu"></small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status Approval</label>
                        <br>
                        <input type="hidden" name="approval_status" id="approval_status_value" value="<?= $approval->approval_status ?? ($active_status->product_status_id ?? '') ?>">
                        <input type="checkbox" id="approval_status"
                            data-toggle="toggle" data-on="<?= htmlspecialchars($active_status->product_status_name ?? 'Aktif', ENT_QUOTES, 'UTF-8') ?>" data-off="<?= htmlspecialchars($inactive_status->product_status_name ?? 'Nonaktif', ENT_QUOTES, 'UTF-8') ?>"
                            data-onstyle="success" data-offstyle="secondary" 
                            <?= (isset($approval->approval_status) && $active_status && $approval->approval_status == $active_status->product_status_id) ? 'checked' : '' ?>>

                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Deskripsi Approval</label>
                        <textarea name="approval_description" id="approval_description" class="form-control" rows="3" placeholder="Masukkan Deskripsi Approval"><?= $approval->approval_description ?? '' ?></textarea>
                        <small class="text-danger" id="error_approval_description"></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-users text-primary"></i> Daftar User</span>
            <button type="button" id="btnTambahRow" class="btn btn-primary btn-sm ml-auto">
                <i class="fas fa-plus"></i> Tambah
            </button>
        </div>
        <div class="card-body" id="wrapperDetail"></div>
        <div class="card-footer text-right">
            <a href="<?= base_url('approve') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Simpan
            </button>
        </div>
    </div>

</form>

<!-- Template row -->
<template id="rowTemplate">
    <div class="row-detail form-row align-items-center mb-2">
        <input type="hidden" class="detail-id" value="">

        <div class="col-md-1">
            <label class="d-md-none">Urutan</label>
            <input type="number" class="form-control sequence-input row-input" min="1" value="1">
        </div>
        <div class="col-md-3">
            <label class="d-md-none">Employee</label>
            <select class="form-control employee-select row-input">
                <option value="">-- Pilih Employee --</option>
                <?php foreach ($employees as $e): ?>
                    <option value="<?= $e->employee_id ?>"
                        data-jabatan="<?= $e->position_name ?>"
                        data-divisi="<?= $e->department_name ?>">
                        <?= $e->employee_name ?> - <?= $e->position_name ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small class="text-danger error-employee-select d-block"></small>
        </div>
        <div class="col-md-2">
            <input type="text" class="form-control jabatan-display" placeholder="Jabatan" readonly>
        </div>
        <div class="col-md-2">
            <input type="text" class="form-control divisi-display" placeholder="Divisi" readonly>
        </div>
        <div class="col-md-2 text-center">
            <label class="mb-1">Wajib</label>
            <input type="checkbox" class="required-toggle row-input"
                   data-toggle="toggle" data-on="Ya" data-off="Tidak"
                   data-onstyle="success" data-offstyle="secondary" data-size="sm" checked>
        </div>
        <div class="col-md-2 text-right">
            <button type="button" class="btn btn-success btn-sm btn-save-row" style="display:none;" title="Simpan baris ini">
                <i class="fas fa-check"></i> Save
            </button>
            <button type="button" class="btn btn-outline-danger btn-sm btn-remove-row" title="Hapus baris ini">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </div>
</template>

<script>
window.addEventListener('load', function () {

    $('.form-select2').select2({ 
        theme: 'bootstrap4', 
        width: '100%' 
    });

    $('#approval_status').bootstrapToggle();
    $('#approval_status').on('change', function() {
        $('#approval_status_value').val(this.checked
            ? <?= (int) ($active_status->product_status_id ?? 0) ?>
            : <?= (int) ($inactive_status->product_status_id ?? 0) ?>
        );
    });


    const APPROVAL_ID = <?= (int) $approval->approval_id ?>;
    const existingDetails = <?= json_encode($approval_users ?? []) ?>;

    function fillJabatanDivisi($row) {
        var selected = $row.find('.employee-select option:selected');
        $row.find('.jabatan-display').val(selected.data('jabatan') || '');
        $row.find('.divisi-display').val(selected.data('divisi') || '');
    }

    function refreshEmployeeOptions() {
        var usedIds = [];
        $('#wrapperDetail .employee-select').each(function() {
            var val = $(this).val();
            if (val) usedIds.push(val);
        });

        $('#wrapperDetail .employee-select').each(function() {
            var $select = $(this);
            var currentValue = $select.val();

            $select.find('option').each(function() {
                var optionValue = $(this).val();
                if (!optionValue) return;

                var isUsedElsewhere = usedIds.includes(optionValue) && optionValue !== currentValue;
                $(this).prop('disabled', isUsedElsewhere);
            });

            $select.trigger('change.select2');
        });
    }

    function addRow(existing = null) {
        let template = document.getElementById('rowTemplate').content.cloneNode(true);
        $('#wrapperDetail').append(template);

        let $row = $('#wrapperDetail .row-detail').last();
        $row.find('.employee-select').select2({ theme: 'bootstrap4', width: '100%' });
        $row.find('.required-toggle').bootstrapToggle();

        let totalRow = $('#wrapperDetail .row-detail').length;
        $row.find('.sequence-input').val(totalRow);

        if (existing) {
            $row.find('.detail-id').val(existing.approval_detail_id);
            $row.find('.sequence-input').val(existing.approval_sequence);
            $row.find('.employee-select').val(existing.approval_user_id).trigger('change');
            fillJabatanDivisi($row);   // panggil di sini
            $row.find('.required-toggle').prop('checked', existing.approval_is_required == 1).change();
            hideSaveButton($row);
        } else {
            showSaveButton($row);
        }
    }

    // load semua data lama
    if (existingDetails.length > 0) {
        existingDetails.forEach(function(item) {
            addRow(item);
        });
        refreshEmployeeOptions();
    } else {
        addRow();
    }

    $('#btnTambahRow').on('click', function() {
        addRow();
        refreshEmployeeOptions();
    });

    function showSaveButton($row) {
        $row.find('.btn-save-row').show();
    }
    function hideSaveButton($row) {
        $row.find('.btn-save-row').hide();
    }

    $('#wrapperDetail').on('change input', '.row-input', function() {
        var $row = $(this).closest('.row-detail');
        showSaveButton($row);
    });

    $('#wrapperDetail').on('change', '.employee-select', function() {
        var $row = $(this).closest('.row-detail');
        fillJabatanDivisi($row);
        refreshEmployeeOptions();
    });


    

    $('#wrapperDetail').on('click', '.btn-save-row', function() {
        var $row = $(this).closest('.row-detail');
        var $btn = $(this);

        var detailId  = $row.find('.detail-id').val();
        var userId    = $row.find('.employee-select').val();
        var sequence  = $row.find('.sequence-input').val();
        var isRequired = $row.find('.required-toggle').is(':checked') ? 1 : 0;

        $row.find('.error-employee-select').text('');

        if (!userId) {
            $row.find('.error-employee-select').text('Employee wajib dipilih.');
            return;
        }

        $btn.prop('disabled', true);

        $.ajax({
            url: '<?= base_url('approve/save_detail') ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                approval_id: APPROVAL_ID,
                detail_id: detailId,
                user_id: userId,
                sequence: sequence,
                is_required: isRequired
            },
            success: function(res) {
                if (res.status === 'success') {
                    $row.find('.detail-id').val(res.id);
                    hideSaveButton($row);
                    Swal.fire({ icon: 'success', title: 'Tersimpan', timer: 1000, showConfirmButton: false });
                } else {
                    Swal.fire('Gagal', res.message || 'Gagal menyimpan baris.', 'error');
                }
            },
            error: function() {
                Swal.fire('Gagal', 'Terjadi kesalahan pada server.', 'error');
            },
            complete: function() {
                $btn.prop('disabled', false);
            }
        });
    });

    $('#wrapperDetail').on('click', '.btn-remove-row', function() {
        if ($('.row-detail').length <= 1) {
            Swal.fire('Info', 'Minimal harus ada 1 baris approval', 'info');
            return;
        }

        var $row = $(this).closest('.row-detail');
        var detailId = $row.find('.detail-id').val();

        if (!detailId) {
            $row.remove();
            renumberSequence();
            refreshEmployeeOptions();
            return;
        }

        Swal.fire({
            title: 'Hapus baris ini?',
            text: 'Data approval user ini akan dihapus permanen dari database.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc3545'
        }).then(function(result) {
            if (!result.isConfirmed) return;

            $.ajax({
                url: '<?= base_url('approve/delete_detail/') ?>' + detailId,
                type: 'POST',
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        $row.remove();
                        renumberSequence();
                        refreshEmployeeOptions();
                        Swal.fire({ icon: 'success', title: 'Terhapus', timer: 1000, showConfirmButton: false });
                    } else {
                        Swal.fire('Gagal', res.message || 'Gagal menghapus baris.', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus baris.', 'error');
                }
            });
        });
    });

    function renumberSequence() {
        $('#wrapperDetail .row-detail').each(function(index) {
            $(this).find('.sequence-input').val(index + 1);
        });
    }

    $('#formApproval').on('submit', function(e) {
        e.preventDefault();

        Swal.fire({
            title: 'Simpan perubahan?',
            text: 'Semua data (header & daftar user) akan disimpan.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, simpan',
            cancelButtonText: 'Batal'
        }).then(function(result) {
            if (!result.isConfirmed) return;

            $('small.text-danger').text('');

            Swal.fire({
                title: 'Menyimpan...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
            var $dirtyRows = $('#wrapperDetail .row-detail').filter(function() {
                return $(this).find('.btn-save-row').is(':visible');
            });

            var hasError = false;
            $dirtyRows.each(function() {
                var $row = $(this);
                if (!$row.find('.employee-select').val()) {
                    $row.find('.error-employee-select').text('Employee wajib dipilih.');
                    hasError = true;
                }
            });

            if (hasError) {
                Swal.close();
                Swal.fire('Gagal', 'Ada baris dengan employee yang belum dipilih.', 'error');
                return;
            }

            saveAllDirtyRows($dirtyRows, 0, function() {
                submitHeader();
            });
        });
    });

    function saveAllDirtyRows($rows, index, onComplete) {
        if (index >= $rows.length) {
            onComplete();
            return;
        }

        var $row = $($rows[index]);
        var detailId   = $row.find('.detail-id').val();
        var userId     = $row.find('.employee-select').val();
        var sequence   = $row.find('.sequence-input').val();
        var isRequired = $row.find('.required-toggle').is(':checked') ? 1 : 0;

        $.ajax({
            url: '<?= base_url('approve/save_detail') ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                approval_id: <?= (int) $approval->approval_id ?>,
                detail_id: detailId,
                user_id: userId,
                sequence: sequence,
                is_required: isRequired
            },
            success: function(res) {
                if (res.status === 'success') {
                    $row.find('.detail-id').val(res.id);
                    hideSaveButton($row);
                    saveAllDirtyRows($rows, index + 1, onComplete);
                } else {
                    Swal.close();
                    Swal.fire('Gagal', 'Gagal menyimpan salah satu baris: ' + (res.message || ''), 'error');
                }
            },
            error: function() {
                Swal.close();
                Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan baris.', 'error');
            }
        });
    }

    // submit
    function submitHeader() {
        $.ajax({
            url: $('#formApproval').attr('action'),
            type: 'POST',
            data: $('#formApproval').serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    window.location.href = '<?= base_url("approve") ?>';
                } else {
                    Swal.close();
                    Swal.fire('Gagal', res.message || 'Gagal menyimpan header.', 'error');
                }
            },
            error: function(xhr) {
                Swal.close();
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    $.each(xhr.responseJSON.errors, function(field, message) {
                        $('#error_' + field).text(message);
                    });
                } else {
                    Swal.fire('Gagal', 'Terjadi kesalahan pada server', 'error');
                }
            }
        });
    }

});
</script>