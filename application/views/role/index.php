<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Master Role</h5>
        <a href="<?= base_url('role/create') ?>" class="btn btn-primary ml-auto">
            <i class="fas fa-plus"></i> Tambah Role
        </a>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped" id="table-role" style="width:100%">
            <thead>
                <tr>
                    <th>Nama Role</th>
                    <th>Slug</th>
                    <th width="10%">Status</th>
                    <th width="15%">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script>
window.addEventListener('load', function () {
    var table = $('#table-role').DataTable({
        processing: true,
        serverSide: false,
        responsive: true,
        lengthChange: true,
        autoWidth: false,
        language: {
            emptyTable: 'Belum ada data role',
            processing: 'Memuat data...'
        },
        // dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>tip',
        ajax: {
            url: '<?= base_url('role/get_role_data') ?>',
            type: 'POST',
            dataSrc: function (response) {
                if (response.status == 'error') {
                    toastr.error(response.message);
                    return [];
                }
                return response;
            },
            error: function () {
                toastr.error('Gagal memuat data role');
            }
        },
        columns: [
            { data: 'name' },
            { data: 'slug' },
            { data: 'status' },
            { data: 'action', orderable: false, searchable: false }
        ]
    });

    $('#table-role').on('click', '.btn-delete', function () {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Apakah anda yakin?',
            text: "Role akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('role/delete_role/') ?>' + id,
                    type: 'POST',
                    dataType: 'json',
                    success: function (result) {
                        if (result.status === 'success') {
                            Swal.fire('Berhasil', result.message, 'success');
                            table.ajax.reload(null, false);
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
    });
});
</script>