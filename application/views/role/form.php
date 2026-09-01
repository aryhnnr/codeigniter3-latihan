<?php $readonly = ($mode === 'detail'); ?>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <?= $mode === 'create' ? 'Tambah Role' : ($mode === 'edit' ? 'Edit Role' : 'Detail Role') ?>
        </h5>
    </div>
    <div class="card-body">
        <h6 class="font-weight-bold text-primary mb-3"><i class="fas fa-id-badge mr-1"></i> Role Information</h6>
        <form id="formRole">
            <input type="hidden" name="id" id="role_id" value="<?= $role->id ?? 0 ?>">

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Nama Role</label>
                    <input type="text" name="name" id="role_name" class="form-control"
                           value="<?= htmlspecialchars($role->name ?? '') ?>"
                           maxlength="50" <?= $readonly ? 'readonly' : '' ?>>
                </div>
                <div class="form-group col-md-4">
                    <label>Slug</label>
                    <input type="text" name="slug" id="role_slug" class="form-control"
                           value="<?= htmlspecialchars($role->slug ?? '') ?>"
                           maxlength="50" pattern="[A-Za-z0-9_\-]+" <?= $readonly ? 'readonly' : '' ?>>
                    <small class="form-text text-muted">Terisi otomatis dari nama, bisa diubah manual.</small>
                </div>
                <div class="form-group col-md-2">
                    <label>Status</label>
                    <select name="status" id="role_status" class="form-control" <?= $readonly ? 'disabled' : '' ?>>
                        <option value="1" <?= (!isset($role->status) || $role->status == 1) ? 'selected' : '' ?>>Aktif</option>
                        <option value="0" <?= (isset($role->status) && $role->status == 0) ? 'selected' : '' ?>>Nonaktif</option>
                    </select>
                </div>
            </div>

            <hr>

            <h6 class="font-weight-bold text-primary mb-3"><i class="fas fa-user-lock mr-1"></i> Hak Akses Menu</h6>

            <?php if (empty($menus)): ?>
                <p class="text-muted">Belum ada data menu. Tambahkan menu terlebih dahulu di halaman Master Menu.</p>
            <?php else: ?>
            <?php if ($is_unrestricted_role): ?>
                <small class="text-muted d-block mb-2">Admin dan Super Admin otomatis memiliki akses ke semua menu.</small>
            <?php endif; ?>
            <div class="table-responsive">
                <table class="table table-striped table-lg align-middle" id="table-permission">
                    <thead>
                        <tr>
                            <th class="align-middle">Menu</th>
                            <th class="text-center" width="10%">
                                View<br>
                                <?php if (!$readonly && !$is_unrestricted_role): ?>
                                    <div class="icheck-primary d-inline">
                                        <input type="checkbox" id="check-all-view" class="check-all" data-column="view">
                                        <label for="check-all-view"></label>
                                    </div>
                                <?php endif; ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($permission_menus as $permission_menu):
                            $menu = $permission_menu['menu'];
                            $children = $permission_menu['children'];
                            $parent_checked = $permission_menu['checked'];
                            $parent_checkbox_id = 'perm-view-' . (int) $menu->id;
                        ?>
                        <tr class="permission-parent-row">
                            <td class="font-weight-bold">
                                <?php if (!empty($menu->icon)): ?><i class="<?= htmlspecialchars($menu->icon) ?> mr-2"></i> <?php endif; ?>
                                <?= htmlspecialchars($menu->name) ?>
                            </td>
                            <td class="text-center">
                                <div class="icheck-primary d-inline">
                                    <input type="checkbox"
                                        id="<?= $parent_checkbox_id ?>"
                                        class="icheck-permission perm-view permission-parent"
                                        data-parent-id="<?= (int) $menu->id ?>"
                                        name="permissions[<?= (int) $menu->id ?>][view]"
                                        value="1" <?= $parent_checked ? 'checked' : '' ?> <?= ($readonly || $is_unrestricted_role) ? 'disabled' : '' ?>>
                                    <label for="<?= $parent_checkbox_id ?>"></label>
                                </div>
                            </td>
                        </tr>
                        <?php foreach ($children as $child_data):
                            $child = $child_data['menu'];
                            $checked = $child_data['checked'] ? 'checked' : '';
                            $checkbox_id = 'perm-view-' . (int) $child->id;
                        ?>
                        <tr class="permission-child-row">
                            <td class="pl-5">
                                <i class="fas fa-level-up-alt fa-rotate-90 text-muted mr-2"></i>
                                <?php if (!empty($child->icon)): ?><i class="<?= htmlspecialchars($child->icon) ?> mr-2"></i> <?php endif; ?>
                                <?= htmlspecialchars($child->name) ?>
                            </td>
                            <td class="text-center">
                                <div class="icheck-primary d-inline">
                                    <input type="checkbox"
                                        id="<?= $checkbox_id ?>"
                                        class="icheck-permission perm-view permission-child"
                                        data-parent-id="<?= (int) $menu->id ?>"
                                        name="permissions[<?= (int) $child->id ?>][view]"
                                        value="1" <?= $checked ?> <?= ($readonly || $is_unrestricted_role) ? 'disabled' : '' ?>>
                                    <label for="<?= $checkbox_id ?>"></label>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; // endforeach $children ?>
                        <?php endforeach; // endforeach $permission_menus ?>
                    </tbody>
                </table>
            </div>
            <?php endif; // endif empty($menus) ?>

            <?php if (!$readonly): ?>
            <div class="mt-3">
                <a href="<?= base_url('role') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i>Kembali</a>
                <button type="submit" class="btn btn-primary" id="btnSimpanRole">
                    <span class="btn-spinner spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    Simpan
                </button>
            </div>
            <?php else: ?>
                <a href="<?= base_url('role') ?>" class="btn btn-secondary mt-1"><i class="fas fa-arrow-left mr-1"></i>Kembali</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<script>
window.addEventListener('load', function () {
    $("#role_status").select2({
        theme: 'bootstrap4',
        width: '100%',
    });

    if ($('#table-permission').length) {
        $('#table-permission').DataTable({
            paging: false,
            info: false,
            ordering: true,
            order: [],
            columnDefs: [
                { orderable: false, targets: [1] }
            ],
            language: {
                search: 'Cari menu:',
                zeroRecords: 'Menu tidak ditemukan'
            }
        });
    }

    <?php if (!$readonly): ?>
    // Slug otomatis dari nama, berhenti auto-isi begitu user edit slug manual
    var slugDirty = <?= json_encode(!empty($role->slug ?? '')) ?>;
    $('#role_slug').on('input', function () { slugDirty = true; });

    function fillSlug() {
        if (slugDirty) return;
        var slug = $('#role_name').val()
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
        $('#role_slug').val(slug);
    }

    $('#role_name').on('input keyup change', fillSlug);
    fillSlug();

    function syncParentCheckbox(parentId) {
        var $children = $('.permission-child[data-parent-id="' + parentId + '"]');
        var $parent = $('.permission-parent[data-parent-id="' + parentId + '"]');
        if (!$children.length || !$parent.length) return;
        $parent.prop('checked', $children.filter(':checked').length === $children.length);
    }

    $('.permission-parent').on('change', function () {
        var checked = this.checked;
        var parentId = $(this).data('parent-id');
        $('.permission-child[data-parent-id="' + parentId + '"]').prop('checked', checked);
    });

    $('.permission-child').on('change', function () {
        syncParentCheckbox($(this).data('parent-id'));
    });

    // Checkbox "pilih semua" per kolom (View/Add/Edit/Delete)
    function bindCheckAll(column) {
        $('.check-all[data-column="' + column + '"]').on('change', function () {
            var checked = this.checked;
            $('.perm-' + column).prop('checked', checked);
        });
    }
    ['view'].forEach(bindCheckAll);
    // ['view', 'add', 'edit', 'delete'].forEach(bindCheckAll);

    $('#formRole').on('submit', function (e) {
        e.preventDefault();

        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();

        var $btn = $('#btnSimpanRole');
        $btn.prop('disabled', true);
        $btn.find('.btn-spinner').removeClass('d-none');

        Swal.fire({
            title: 'Apakah anda yakin?',
            text: "Role akan " + "<?= !empty($role->id) ? 'Diperbarui' : 'Ditambahkan' ?>",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, simpan!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('role/save') ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: $(this).serialize(),
                    success: function (result) {
                        if (result.status === 'success') {
                            window.location.href = '<?= base_url('role') ?>';
                        } else if (result.status === 'error_validation') {
                            $.each(result.errors, function (key, val) {
                                var $el = $('[name="' + key + '"]');
                                $el.addClass('is-invalid');
                                $el.after('<div class="invalid-feedback">' + val + '</div>');
                            });
                        } else {
                            Swal.fire('Gagal', result.message, 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'Terjadi kesalahan saat menyimpan data', 'error');
                    },
                    complete: function () {
                        $btn.prop('disabled', false);
                        $btn.find('.btn-spinner').addClass('d-none');
                    }
                });
            } else {
                $btn.prop('disabled', false);
                $btn.find('.btn-spinner').addClass('d-none');
            }
        });

        
    });
    <?php endif; ?>
});
</script>