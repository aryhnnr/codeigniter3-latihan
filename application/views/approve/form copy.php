<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0"><i class="fas fa-user-check mr-2 text-primary"></i> Setting Approve</h5>
        </div>
    </div>

    <div class="card-body">
        <div class="row align-items-end">
            <div class="col-md-12">
                <div class="form-group mb-0">
                    <label for="menu-approval">Pilih Menu</label>
                    <select id="menu-approval" class="form-control select2">
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-users mr-2"></i> Daftar User</h6>
        <button type="button" class="btn btn-primary ml-auto" id="btn-tambah-approver">
            <i class="fas fa-plus mr-1"></i> Tambah
        </button>
    </div>
    <div class="card-body">
        <div id="approver-list"></div>

        <div class="text-right mt-3">
            <button type="button" class="btn btn-success" id="btn-save-all-approver">
                <i class="fas fa-save mr-1"></i> Simpan
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    const employeeData = {};
    const approverList = document.getElementById('approver-list');

    function loadMenuData() {
        $.ajax({
            url: '<?= base_url('approve/get_menu_data') ?>',
            type: 'POST',
            dataType: 'json',
            success: function (response) {
                const menuSelect = $('#menu-approval');
                menuSelect.empty();
                menuSelect.append('<option value="">-- Pilih menu --</option>');

                if (!response || !response.length) {
                    return;
                }

                $.each(response, function (_, menu) {
                    menuSelect.append('<option value="' + menu.id + '">' + menu.name + '</option>');
                });
            },
            error: function () {
                Swal.fire({
                    title: 'Gagal',
                    text: 'Data menu gagal dimuat.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    }

    function loadEmployeeData() {
        $.ajax({
            url: '<?= base_url('approve/get_employee_data') ?>',
            type: 'POST',
            dataType: 'json',
            success: function (response) {
                if (!response || !response.length) {
                    return;
                }

                $.each(response, function (_, employee) {
                    employeeData[employee.id] = {
                        id: employee.id,
                        name: employee.name,
                        position: employee.position,
                        department: employee.department
                    };
                });
            },
            error: function () {
                Swal.fire({
                    title: 'Gagal',
                    text: 'Data employee gagal dimuat.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    }

    function fillEmployeeMeta(target) {
        const selectEl = target && target.closest ? target : null;
        const activeSelect = selectEl && selectEl.classList && selectEl.classList.contains('employee-select') ? 
            selectEl : (typeof $ !== 'undefined' ? 
            $(target).closest('.employee-select')[0] : null);

        if (!activeSelect) return;

        const formCard = activeSelect.closest('[data-form-id]');
        if (!formCard) return;

        const formId = formCard.dataset.formId;
        const positionInput = document.getElementById('employee-position-' + formId);
        const departmentInput = document.getElementById('employee-department-' + formId);
        const selectedOption = activeSelect.options[activeSelect.selectedIndex];

        if (!selectedOption || !selectedOption.value) {
            positionInput.value = '';
            departmentInput.value = '';
            return;
        }

        positionInput.value = selectedOption.dataset.position || '';
        departmentInput.value = selectedOption.dataset.department || '';
    }

    function initApproverEvents() {
        document.getElementById('btn-tambah-approver').addEventListener('click', function () {
            const formId = 'employee-form-' + Date.now();
            const employeeOptions = Object.values(employeeData).map(function (employee) {
                return '<option value="' + employee.id + '" data-position="' + (employee.position || '') + '" data-department="' + (employee.department || '') + '">' + employee.name + ' - ' + employee.position + '</option>';
            }).join('');

            const formHtml = `
                <div class="row align-items-center mb-2 p-2 border rounded bg-light" data-form-id="${formId}">
                    <div class="col-md-4">
                        <select id="employee-select-${formId}" class="form-control employee-select select2">
                            <option value="" selected disabled hidden>-- Pilih Employee --</option>
                            ${employeeOptions}
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" id="employee-position-${formId}" class="form-control employee-position" readonly placeholder="Jabatan">
                    </div>
                    <div class="col-md-3">
                        <input type="text" id="employee-department-${formId}" class="form-control employee-department" readonly placeholder="Divisi">
                    </div>
                    <div class="col-md-2 text-right">
                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-form" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;

            approverList.insertAdjacentHTML('beforeend', formHtml);

            const $newSelect = $('#employee-select-' + formId);
            if (!$newSelect.hasClass('select2-hidden-accessible')) {
                $newSelect.select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    placeholder: 'Pilih Employee',
                    allowClear: true
                });
                $newSelect.val(null).trigger('change');
            }
        });

        approverList.addEventListener('change', function (event) {
            const target = event.target;
            if (!target || !target.classList || !target.classList.contains('employee-select')) return;
            fillEmployeeMeta(target);
        });

        $(document).on('select2:select', '.employee-select', function () {
            fillEmployeeMeta(this);
        });

        approverList.addEventListener('click', function (event) {
            const deleteBtn = event.target.closest('.btn-delete-form');
            if (deleteBtn) {
                const formCard = deleteBtn.closest('[data-form-id]');
                if (formCard) {
                    formCard.remove();
                }
            }
        });

        document.getElementById('btn-save-all-approver').addEventListener('click', function () {
            const rows = approverList.querySelectorAll('[data-form-id]');

            if (rows.length === 0) {
                Swal.fire({
                    title: 'Belum ada data',
                    text: 'Silakan tambah user approver terlebih dahulu.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return;
            }

            let isValid = true;
            rows.forEach(function (row) {
                const select = row.querySelector('.employee-select');
                if (!select.value) {
                    isValid = false;
                }
            });

            if (!isValid) {
                Swal.fire({
                    title: 'Ada data yang belum dipilih',
                    text: 'Pastikan semua baris user sudah memilih nama employee.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return;
            }

            Swal.fire({
                title: 'Berhasil',
                text: 'Daftar user approver berhasil disimpan.',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        });
    }

    window.addEventListener('load', function () {
        if (typeof $ === 'undefined') return;

        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: 'Pilih Menu',
            allowClear: true
        });

        initApproverEvents();
        loadMenuData();
        loadEmployeeData();
    });
})();
</script>