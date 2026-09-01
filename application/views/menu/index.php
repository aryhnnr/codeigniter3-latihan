<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Master Menu</h5>
        <button class="btn btn-primary ml-auto" onclick="formMenu(0)">
            <i class="fas fa-plus"></i> Tambah Menu
        </button>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped" id="table-menu" style="width:100%">
            <thead>
                <tr>
                <th>Nama Menu</th>
                <th>URL</th>
                <th>Icon</th>
                 <th>Parent</th>
                 <!-- <th>Urutan</th> -->
                 <th width="10%">Status</th>
                <th width="14%">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Modal Form Menu -->
<div class="modal fade" id="modalMenu" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formMenu" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="modalMenuTitle">Tambah Menu</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="menu_id" value="0">

                    <div class="form-group">
                        <label>Nama Menu</label>
                        <input type="text" name="name" id="menu_name" class="form-control" required maxlength="100">
                        <small class="text-danger d-none" data-error-for="name"></small>
                    </div>

                    <div class="form-group">
                        <label>URL / Controller</label>
                        <input type="text" name="url" id="menu_url" class="form-control" placeholder="contoh: employee, role/detail, javascript:;" required maxlength="150" pattern="(javascript:;|[A-Za-z0-9_-]+(/[A-Za-z0-9_-]+)*)">
                        <small class="form-text text-muted">Gunakan <code>javascript:;</code> untuk menu parent atau path controller seperti <code>role/detail</code>.</small>
                        <small class="text-danger d-none" data-error-for="url"></small>
                    </div>

                    <div class="form-group">
                        <label>Icon (FontAwesome class)</label>
                        <input type="text" name="icon" id="menu_icon" class="form-control" placeholder="contoh: fas fa-users">
                        <small class="form-text text-muted">Cari nama icon di <a href="https://fontawesome.com/v5/search" target="_blank" rel="noopener">fontawesome.com</a></small>
                    </div>

                    <div class="form-group">
                        <label>Parent Menu</label>
                        <select name="parent_id" id="menu_parent_id" class="form-control select2" style="width: 100%;">
                            <option value="0">-- Menu Utama --</option>
                            <?php foreach ($parent_menus as $parent): ?>
                                <option value="<?= (int) $parent->id ?>"><?= htmlspecialchars($parent->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">Menu utama tidak memiliki parent. Menu anak memakai ID parent ini.</small>
                            <small class="text-danger d-none" data-error-for="parent_id"></small>
                    </div>

                    <!-- <div class="form-group">
                        <label>Urutan Tampil</label>
                            <input type="text" id="menu_order_no" class="form-control" value="Otomatis" readonly>
                        <small class="form-text text-muted">Urutan dibuat otomatis saat disimpan dan menentukan posisi dalam kelompok menu.</small>
                        <small class="text-danger d-none" data-error-for="order_no"></small>
                    </div> -->

                    <div class="form-group">
                        <label>Status</label>
                        <br>
                        <?php
                            $active_status_id = '';
                            $inactive_status_id = '';
                            foreach ($status_list as $status) {
                                $status_name = strtolower(trim($status->product_status_name));
                                if ($status_name === 'aktif') {
                                        $active_status_id = (int) $status->product_status_id;
                                } elseif ($status_name === 'nonaktif') {
                                        $inactive_status_id = (int) $status->product_status_id;
                                }
                            }
                        ?>
                        <input type="hidden" name="status" value="<?= $inactive_status_id ?>">
                        <input type="checkbox" id="menu_status" name="status" value="<?= $active_status_id ?>"
                                        data-toggle="toggle" data-on="Aktif" data-off="Nonaktif"
                                        data-onstyle="success" data-offstyle="secondary" data-width="110"
                                        <?= $active_status_id !== '' ? 'checked' : '' ?>>
                        <small class="text-danger d-none" data-error-for="status"></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSimpanMenu">
                        <span class="btn-spinner spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
window.addEventListener('load', function () {

    function initParentSelect() {
        var $parent = $('#menu_parent_id');
        if ($parent.hasClass('select2-hidden-accessible')) {
            $parent.select2('destroy');
        }
        $parent.select2({
            theme: 'bootstrap4',
            width: '100%',
            dropdownParent: $('#modalMenu')
        });
    }

    initParentSelect();

    var table = $('#table-menu').DataTable({
        processing: true,
        serverSide: false,
        responsive: true,
        scrollX: true,
        ordering: false,
        language: {
            emptyTable: 'Belum ada data menu',
            processing: 'Memuat data...'
        },
        // dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>tip',
        ajax: {
            url: '<?= base_url('menu/get_menu_data') ?>',
            type: 'POST',
            dataSrc: function (response) {
                if (response.status == 'error') {
                    toastr.error(response.message);
                    return [];
                }
                return response;
            },
            error: function () {
                toastr.error('Gagal memuat data menu');
            }
        },
        columns: [
            { data: 'name' },
            { data: 'url' },
            { data: 'icon' },
            { data: 'parent' },
            // { data: 'order_no' },
            { data: 'status' },
            { data: 'action', orderable: false, searchable: false }
        ]
    });

    // delegasi event karena baris tabel di-generate ulang tiap reload ajax
    $('#table-menu').on('click', '.btn-edit', function () {
        formMenu($(this).data('id'));
    });

    $('#table-menu').on('click', '.btn-delete', function () {
        deleteMenu($(this).data('id'));
    });

    function refreshSidebar() {
        $.getJSON('<?= base_url('menu/get_sidebar_data') ?>').done(function (menus) {
            var $sidebar = $('#sidebar-menu-list');
            var roots = [];
            var childrenByParent = {};
            var currentSection = window.location.pathname.toLowerCase().split('/').filter(Boolean).pop() || '';

            $.each(menus, function (_, menu) {
                if (menu.parent_id === 0) {
                    roots.push(menu);
                } else {
                    childrenByParent[menu.parent_id] = childrenByParent[menu.parent_id] || [];
                    childrenByParent[menu.parent_id].push(menu);
                }
            });

            $sidebar.find('.dynamic-sidebar-menu').remove();
            $.each(roots, function (_, menu) {
                var children = childrenByParent[menu.id] || [];
                var $item = $('<li>', { 
                    class: 'nav-item dynamic-sidebar-menu' 
                });
                var $link = $('<a>', { 
                    class: 'nav-link' 
                });
                var $icon = $('<i>', { 
                    class: 'nav-icon ' + (menu.icon || '')
                 });

                if (children.length) {
                    var activeParent = false;
                    var $childList = $('<ul>', { class: 'nav nav-treeview' });
                    $.each(children, function (_, child) {
                        var childSection = (child.url || '').split('/')[0].toLowerCase();
                        var $childLink = $('<a>', {
                            href: '<?= base_url() ?>' + child.url,
                            class: 'nav-link' + (currentSection === childSection ? ' active' : '')
                        });
                        $childLink.append($('<i>', { 
                            class: 'nav-icon ' + (child.icon || '') 
                        }));
                        $childLink.append($('<p>').text(child.name));
                        $childList.append($('<li>', { 
                            class: 'nav-item' 
                        }).append($childLink));
                        activeParent = activeParent || currentSection === childSection;
                    });
                    $link.attr('href', 'javascript:;').addClass(activeParent ? 'active' : '');
                    $link.append($icon).append($('<p>').text(menu.name).append($('<i>', { class: 'right fas fa-angle-left' })));
                    $item.addClass('has-treeview' + (activeParent ? ' menu-open' : '')).append($link, $childList);
                } else {
                    if (menu.url === 'javascript:;') {
                        $item.removeClass('nav-item').addClass('nav-header').text(menu.name);
                        $sidebar.append($item);
                        return;
                    }
                    var menuSection = (menu.url || '').split('/')[0].toLowerCase();
                    $link.attr('href', '<?= base_url() ?>' + menu.url);
                    if (currentSection === menuSection) $link.addClass('active');
                    $link.append($icon).append($('<p>').text(menu.name));
                    $item.append($link);
                }
                $sidebar.append($item);
            });
        });
    }

    window.formMenu = function (id) {
        var form = $('#formMenu')[0];
        clearMenuErrors();
        form.reset();
        form.classList.remove('was-validated');
        $('#menu_id').val(0);
        $('#menu_parent_id').val(0).trigger('change');
            $('#menu_order_no').val('Otomatis');
        $('#menu_status').bootstrapToggle('on');

        if (!id || id == 0) {
            $('#modalMenuTitle').text('Tambah Menu');
            initParentSelect();
            $('#modalMenu').modal('show');
        } else {
            $('#modalMenuTitle').text('Edit Menu');
            $.ajax({
                url: '<?= base_url('menu/get/') ?>' + id,
                type: 'GET',
                dataType: 'json' // biar jQuery selalu parse JSON secara konsisten, tidak perlu JSON.parse manual
            }).done(function (data) {
                if (data.status === 'failed') {
                    toastr.error(data.message);
                    return;
                }
                $('#menu_id').val(data.id);
                $('#menu_name').val(data.name);
                $('#menu_url').val(data.url);
                $('#menu_icon').val(data.icon);
                $('#menu_order_no').val('Otomatis');
                $('#menu_status').bootstrapToggle(data.status == <?= (int) $active_status_id ?> ? 'on' : 'off');
                refreshParentMenus(data.parent_id || 0, data.id).always(function () {
                    $('#menu_parent_id').val(data.parent_id || 0).trigger('change');
                    $('#modalMenu').modal('show');
                });
            }).fail(function () {
                toastr.error('Gagal mengambil data menu');
            });
        }
    };

    $('#modalMenu').on('hidden.bs.modal', function () {
        clearMenuErrors();
        $('#formMenu')[0].reset();
        $('#menu_status').bootstrapToggle('on');
    });

    function refreshParentMenus(selectedId, excludeId) {
        excludeId = typeof excludeId === 'undefined' ? ($('#menu_id').val() || 0) : excludeId;
        return $.getJSON('<?= base_url('menu/get_parent_menus') ?>', { 
                exclude_id: excludeId 
            })
            .done(function (parents) {
                var $parent = $('#menu_parent_id');
                $parent.empty().append('<option value="0">-- Menu Utama --</option>');
                $.each(parents, function (_, parent) {
                    $('<option>', { 
                        value: parent.id, 
                        text: parent.name 
                    }).appendTo($parent);
                });
                $parent.val(selectedId || 0).trigger('change');
            });
    }

    function clearMenuErrors() {
        $('#formMenu .is-invalid').removeClass('is-invalid');
        $('#formMenu [data-error-for]').text('').addClass('d-none');
    }

    function showMenuErrors(errors) {
        $.each(errors, function (field, message) {
            var $field = field === 'status'
                ? $('#menu_status')
                : $('#formMenu [name="' + field + '"]').first();
            var $error = $('#formMenu [data-error-for="' + field + '"]');
            $field.addClass('is-invalid');
            $error.text(message).removeClass('d-none');
        });
    }

    $('#formMenu').on('input change', '[name]', function () {
        var field = $(this).attr('name');
        $('#formMenu [data-error-for="' + field + '"]').text('').addClass('d-none');
        $(this).removeClass('is-invalid');
    });

    window.deleteMenu = function (id) {
        Swal.fire({
            title: 'Apakah anda yakin?',
            text: "Menu akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('menu/delete_menu/') ?>' + id,
                    type: 'POST',
                    dataType: 'json',
                    success: function (result) {
                        if (result.status === 'success') {
                            Swal.fire('Berhasil', result.message, 'success');
                            table.ajax.reload(null, false); // memperbarui tabel tanpa reload halaman
                            refreshSidebar();
                        } else {
                            Swal.fire('Gagal', result.message, 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'Terjadi kesalahan saat menghapus data', 'error');
                    }
                });
            }
        });
    };

    $('#formMenu').on('submit', function (e) {
        e.preventDefault();

        clearMenuErrors();
        var form = this;
        if (!form.checkValidity()) {
            $.each(form.elements, function () {
                if (!this.validity.valid && this.name) {
                    showMenuErrors({ [this.name]: this.validationMessage });
                }
            });
            return;
        }

        var $btn = $('#btnSimpanMenu');
        $btn.prop('disabled', true);
        $btn.find('.btn-spinner').removeClass('d-none');

        $.ajax({
            url: '<?= base_url('menu/save_menu') ?>',
            type: 'POST',
            dataType: 'json',
            data: $(this).serialize(),
            success: function (result) {
                if (result.status === 'success') {
                    Swal.fire('Berhasil', result.message, 'success');
                    $('#modalMenu').modal('hide');
                    table.ajax.reload(null, false);
                            refreshParentMenus(0, 0);
                        refreshSidebar();
                } else if (result.status === 'error_validation') {
                    showMenuErrors(result.errors || {});
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
    });
});
</script>