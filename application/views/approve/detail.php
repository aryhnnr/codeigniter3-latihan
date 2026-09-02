<div class="card mb-3">
    <div class="card-header">
        <i class="fas fa-user-check text-primary"></i> Detail Approval
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Kode Approval</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($approval->approval_code) ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Nama Approval</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($approval->approval_name) ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Pilih Menu</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($approval->menu_name) ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($approval->product_status_name) ?>" readonly>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Deskripsi Approval</label>
                    <textarea class="form-control" rows="3" readonly><?= htmlspecialchars($approval->approval_description) ?></textarea>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-users"></i> Daftar User Approval</span>
    </div>

    <div class="card-body">
        <!-- Header kolom -->
        <div class="row px-2 pb-2 font-weight-bold text-muted small mx-0">
            <div class="col-md-1">Urutan</div>
            <div class="col-md-4">Nama Employee</div>
            <div class="col-md-3">Jabatan</div>
            <div class="col-md-3">Divisi</div>
            <div class="col-md-1 text-center">Wajib</div>
        </div>

        <div id="wrapperDetail"></div>
    </div>
    <div class="card-footer text-right">
        <a href="<?= site_url('approve') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
</div>

<template id="approvalUserRowTemplate">
    <div class="row mb-2 align-items-center approval-user-row py-2">
        <div class="col-md-1 text-center">
            <input type="number" class="form-control" value="{approval_sequence}" readonly>
        </div>
        <div class="col-md-4">
            <input type="text" class="form-control" value="{employee_name}" readonly>
        </div>
        <div class="col-md-3">
            <input type="text" class="form-control" value="{position_name}" readonly>
        </div>
        <div class="col-md-3">
            <input type="text" class="form-control" value="{department_name}" readonly>
        </div>
        <div class="col-md-1 text-center">
            {approval_is_required_badge}
        </div>
    </div>
</template>

<script>
window.addEventListener('load', function () {
    const approvalUsers = <?= json_encode($approval_users) ?>;
    const wrapperDetail = document.getElementById('wrapperDetail');
    const rowTemplate = document.getElementById('approvalUserRowTemplate').innerHTML;

    approvalUsers.forEach(user => {
        const rowHtml = rowTemplate
            .replace('{employee_name}', user.employee_name)
            .replace('{position_name}', user.position_name || '-')
            .replace('{department_name}', user.department_name || '-')
            .replace('{approval_sequence}', user.approval_sequence)
            .replace('{approval_is_required_badge}', user.approval_is_required ? '<span class="badge badge-success">Wajib</span>' : '<span class="badge badge-secondary">Tidak</span>');

        wrapperDetail.insertAdjacentHTML('beforeend', rowHtml);
    });
});
</script>