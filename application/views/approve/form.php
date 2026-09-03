<form id="formApproval" action="<?= base_url('approve/store') ?>" method="POST">

    <div class="card mb-3">
        <div class="card-header">
            <i class="fas fa-user-check text-primary"></i> Setting Approve
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Kode Approval</label>
                        <input type="text" class="form-control" id="preview_code" value="<?= $preview_code ?? '' ?>" readonly>
                        <small class="text-muted">Kode digenerate otomatis oleh sistem</small>
                    </div>

                    <div class="form-group">
                        <label>Nama Approval</label>
                        <input type="text" name="approval_name" id="approval_name" class="form-control" placeholder="Masukkan Nama Approval">
                        <small class="text-danger" id="error_approval_name"></small>
                    </div>

                    <div class="form-group">
                        <label>Pilih Menu</label>
                        <select name="approval_menu" id="approval_menu" class="form-control form-select2">
                            <option value="">-- Pilih Menu --</option>
                            <?php foreach ($menus as $m): ?>
                                <option value="<?= $m->id ?>"><?= $m->name ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-danger" id="error_approval_menu"></small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Deskripsi Approval</label>
                        <textarea name="approval_description" id="approval_description" class="form-control" rows="3" placeholder="Masukkan Deskripsi Approval"></textarea>
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
        <div class="card-body" id="wrapperDetail">
            <!-- Row akan di-generate oleh JS, mulai dengan 1 baris -->
        </div>
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
        <div class="col-md-1">
            <label class="d-md-none">Urutan</label>
            <input type="number" name="approval_sequence[]" class="form-control sequence-input" min="1" value="1">
        </div>
        <div class="col-md-3">
            <label class="d-md-none">Employee</label>
            <select name="approval_user_id[]" class="form-control employee-select">
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
        <div class="col-md-3">
            <input type="text" class="form-control jabatan-display" placeholder="Jabatan" readonly>
        </div>
        <div class="col-md-2">
            <input type="text" class="form-control divisi-display" placeholder="Divisi" readonly>
        </div>
        <div class="col-md-2 text-center">
            <label class="mb-1">Wajib</label>
            <input type="checkbox" name="approval_is_required[]" class="required-toggle" 
                   data-toggle="toggle" data-on="Wajib" data-off="Tidak" 
                   data-onstyle="success" data-offstyle="secondary" data-size="sm" checked>
            <!-- checkbox value 'on' otomatis terkirim kalau checked, kosong kalau tidak -->
        </div>
        <div class="col-md-1 text-right">
            <button type="button" class="btn btn-outline-danger btn-sm btn-remove-row">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </div>
</template>

<script>
window.addEventListener('load', function () {

    $('.form-select2').select2({ theme: 'bootstrap4', width: '100%' });

    var originalOptionsHtml = document.getElementById('rowTemplate').content
        .querySelector('.employee-select').innerHTML;

    function addRow() {
        let template = document.getElementById('rowTemplate').content.cloneNode(true);
        $('#wrapperDetail').append(template);

        let $lastRow = $('#wrapperDetail .row-detail').last();

        $lastRow.find('.employee-select').select2({
            theme: 'bootstrap4',
            width: '100%'
        });

        let totalRow = $('#wrapperDetail .row-detail').length;
        $lastRow.find('.sequence-input').val(totalRow);
        $lastRow.find('.required-toggle').bootstrapToggle();

        refreshEmployeeOptions();
    }

    addRow();

    $('#btnTambahRow').on('click', function() {
        addRow();
    });

    $('#wrapperDetail').on('click', '.btn-remove-row', function() {
        if ($('.row-detail').length > 1) {
            $(this).closest('.row-detail').remove();
            renumberSequence();
            refreshEmployeeOptions();
        } else {
            Swal.fire('Info', 'Minimal harus ada 1 baris approval', 'info');
        }
    });

    function renumberSequence() {
        $('#wrapperDetail .row-detail').each(function(index) {
            $(this).find('.sequence-input').val(index + 1);
        });
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

    $('#wrapperDetail').on('change', '.employee-select', function() {
        let $select = $(this);
        let selected = $select.find('option:selected');
        let $row = $select.closest('.row-detail');

        $row.find('.jabatan-display').val(selected.data('jabatan') || '');
        $row.find('.divisi-display').val(selected.data('divisi') || '');
        $row.find('.error-employee-select').text('');

        refreshEmployeeOptions();
    });

    // Submit
    $('#formApproval').on('submit', function(e) {
        e.preventDefault();

        var hasEmpty = false;
        $('#wrapperDetail .employee-select').each(function() {
            if (!$(this).val()) hasEmpty = true;
        });

        if (hasEmpty) {
            Swal.fire('Gagal', 'Semua baris harus memilih employee.', 'error');
            return;
        }

        Swal.fire({
            title: 'Simpan Approval?',
            text: 'Pastikan data approval dan employee sudah benar.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Simpan',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then(function(result) {
            if (!result.isConfirmed) {
                return;
            }

            $('small.text-danger').text('');

            $.ajax({
                url: $('#formApproval').attr('action'),
                method: 'POST',
                data: $('#formApproval').serialize(),
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        window.location.href = '<?= base_url("approve") ?>';
                    } else if (res.errors && typeof res.errors === 'object') {
                        $.each(res.errors, function(fieldName, message) {
                            if (fieldName === 'approval_user_id') {
                                $('#wrapperDetail').find('.error-employee-select').text(message);
                            } else if (fieldName === 'general') {
                                Swal.fire('Gagal', message, 'error');
                            } else {
                                $('#error_' + fieldName).text(message);
                            }
                        });
                    } else if (res.message) {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON) {
                        let res = xhr.responseJSON;
                        if (res.errors && typeof res.errors === 'object') {
                            $.each(res.errors, function(fieldName, message) {
                                if (fieldName === 'approval_user_id') {
                                    $('#wrapperDetail').find('.error-employee-select').text(message);
                                } else if (fieldName === 'general') {
                                    Swal.fire('Gagal', message, 'error');
                                } else {
                                    $('#error_' + fieldName).text(message);
                                }
                            });
                        } else if (res.message) {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    } else {
                        Swal.fire('Gagal', 'Terjadi kesalahan pada server', 'error');
                    }
                }
            });
        });
    });

});
</script>