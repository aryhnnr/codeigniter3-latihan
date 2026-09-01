<script>
(function () {
	if (window.purchaseSharedScriptLoaded) {
		return;
	}
	window.purchaseSharedScriptLoaded = true;

	var page = <?= json_encode($purchase_script_page) ?>;

	function formatMoney(value) {
		return Number(value).toLocaleString('id-ID', {
			minimumFractionDigits: 0
		});
	}

	function parseMoney(value) {
		return Number(String(value).replace(/\./g, '').replace(/[^\d-]/g, '')) || 0;
	}

	function initSelects() {
		$('.select2').select2({
			theme: 'bootstrap4',
			width: '100%'
		});
	}

	function renderStatusBadge(statusName) {
		var statusMap = {
			'Aktif': 'badge-success',
			'Nonaktif': 'badge-secondary',
			'Tertunda': 'badge-secondary',
			'Diproses': 'badge-warning',
			'Diterima': 'badge-info',
			'Selesai': 'badge-success',
			'Dibatalkan': 'badge-danger'
		};

		return `<span class="badge ${statusMap[statusName] || 'badge-secondary'}">${statusName || 'Unknown'}</span>`;
	}

	function initIndex() {
		if ($.fn.dataTable.isDataTable('#table-purchase')) {
			return;
		}
		initSelects();
		var selectedStartDate = '';
		var selectedEndDate = moment().format('YYYY-MM-DD');

		$('#filter_date_range').daterangepicker({
			autoUpdateInput: true,
			startDate: moment(),
			endDate: moment(),
			locale: {
				format: 'YYYY-MM-DD',
				separator: '/',
				applyLabel: 'Terapkan',
				cancelLabel: 'Batal',
				fromLabel: 'Dari',
				toLabel: 'Sampai',
				customRangeLabel: 'Custom',
				daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
				monthNames: [
					'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
					'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
				]
			}
		}).on('apply.daterangepicker', function (event, picker) {
			selectedStartDate = picker.startDate.format('YYYY-MM-DD');
			selectedEndDate = picker.endDate.format('YYYY-MM-DD');
		});

		var table = $('#table-purchase').DataTable({
			processing: true,
			serverSide: false,
			responsive: true,
			lengthChange: true,
			autoWidth: false,
			ajax: {
				url: '<?= base_url('purchase/get_data') ?>',
				type: 'POST',
				dataSrc: '',
				data: function (data) {
					data.status = $('#filter_status').val();
					data.supplier_id = $('#filter_supplier').val();
					data.start_date = selectedStartDate;
					data.end_date = selectedEndDate;
                    data.payment_type = $('#filter_pembayaran').val();
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
				{ data: 'purchase_code' },
				{ data: 'nama_supplier' },
				{ data: 'purchase_date' },
				{ data: 'due_date' },
				{ data: 'payment_type' },
				{
					data: 'product_status_name',
					render: function (data) {
						return renderStatusBadge(data);
					}
				},
				{
					data: 'created_by_username',
					render: function(data, type, row) {
						return data || '-';
					}
				},
				{
					data: null,
					orderable: false,
					searchable: false,
					width: "150px",
					render: function (data, type, row) {
						return `
						<div class="d-flex flex-wrap mb-n2">
                            <a href="javascript:void(0)" onclick="detailData('${row.purchase_id}')" class="btn btn-sm btn-info mb-2 mr-2"><i class="fas fa-eye"></i> Detail</a>
							<a href="<?= base_url('purchase/edit/') ?>${row.purchase_id}" class="btn btn-sm btn-warning mb-2 mr-2"><i class="fas fa-edit"></i> Edit</a>
							<button type="button" class="btn btn-sm btn-danger btn-delete mb-2 mr-2" data-id="${row.purchase_id}" data-code="${row.purchase_code}"><i class="fas fa-trash"></i> Hapus</button>
                        </div>   `;
					}
				}
			]
		});

		$('#btn_filter').on('click', function () {
			table.ajax.reload();
		});

		$(document).on('click', '.btn-delete', function () {
			var button = $(this);
			Swal.fire({
				title: 'Hapus purchase ini?',
				text: 'Purchase ' + button.data('code') + ' akan dihapus permanen dan tidak bisa dikembalikan.',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonText: 'Ya, hapus',
				cancelButtonText: 'Batal',
				confirmButtonColor: '#dc3545',
				reverseButtons: true
			}).then(function (result) {
				if (!result.isConfirmed) {
					return;
				}

				$.ajax({
					url: '<?= base_url('purchase/delete') ?>/' + button.data('id'),
					type: 'POST',
					dataType: 'json',
					success: function (response) {
						if (response.status === 'success') {
							Swal.fire('Berhasil', response.message, 'success');
							table.ajax.reload();
							return;
						}

						Swal.fire('Gagal', response.message, 'error');
					},
					error: function () {
						Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus purchase.', 'error');
					}
				});
			});
		});
	}

	window.detailData = function (purchaseId) {
		$.getJSON('<?= base_url('purchase/detail') ?>/' + purchaseId, function (response) {
			console.log(response.header);
			console.log(response.details);
			var header = response.header;
			var total = 0;
			
			$('#detail-code').text(header.purchase_code);
			$('#detail-name').text(header.nama_supplier);
			$('#detail-purchase_date').text(header.purchase_date);
			$('#detail-due_date').text(header.due_date);
			$('#detail-payment_type').text(header.payment_type);
			$('#detail-status').html(renderStatusBadge(header.product_status_name));
			$('#detail-create-by').text(header.created_by_username ? header.created_by_username : '-');
			$('#detail-items-body').empty();

			response.details.forEach(function (item) {
				var subtotal = Number(item.qty) * Number(item.price);
				total += subtotal;
				$('#detail-items-body').append(`
					<tr>
						<td>${item.product_name}</td>
						<td>${item.qty}</td>
						<td class="text-right">${formatMoney(item.price)}</td>
						<td class="text-right">${formatMoney(subtotal)}</td>
					</tr>
				`);
			});
			$('#detail-subtotal').text(formatMoney(total));
			$('#detail-discount').text(formatMoney(header.discount));
			$('#detail-tax').text(formatMoney(header.tax));
			$('#detail-grand_total').text(formatMoney(header.grand_total));
			$('#detail-notes').text(header.notes || '-');
			$('#modal-detail').modal('show');

		}).fail(function () { 
            Swal.fire('Gagal', 'Gagal memuat detail purchase.', 'error'); 
        });
	};

	function initPurchaseForm(isEdit) {
		if ($.fn.dataTable.isDataTable('#product_table')) {
			return;
		}
		initSelects();
		$('.input-group.date').datepicker({
			format: 'yyyy-mm-dd',
			autoclose: true,
			todayHighlight: true
		});

		var productRows = isEdit ? <?= json_encode(array_map(function ($item) {
			return [
				'purchase_detail_id' => (int) $item->purchase_detail_id,
				'product_id' => (string) $item->product_id,
				'product_name' => $item->product_name,
				'qty' => (int) $item->qty,
				'price' => (float) $item->price
			];

		}, $items ?? []), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?> :
			(JSON.parse(sessionStorage.getItem(<?= json_encode($purchase_draft_storage_key ?? '') ?>)) || []);

		var storageKey = <?= json_encode($purchase_draft_storage_key ?? '') ?>;
		var table = $('#product_table').DataTable({
			data: productRows,
			responsive: true,
			autoWidth: false,
			paging: false,
			searching: false,
			info: false,
			ordering: false,
			columns: [
			
				{
					data: null,
					render: function (data, type, row, meta) {
						return meta.row + 1;
					}
				},
				{ data: 'product_name' },
				{ data: 'qty', render: formatMoney },
				{ data: 'price', render: formatMoney },
				{
					data: null,
					render: function (data) {
						return formatMoney(data.qty * data.price);
					}
				},
				{
					data: null,
					render: function () {
						return `
						<div class="d-flex flex-wrap mb-n2">
                            <button type="button" class="btn btn-sm btn-warning mb-2 mr-2 edit-product"><i class="fas fa-edit"></i> Edit</button>
							<button type="button" class="btn btn-sm btn-danger mb-2 mr-2 delete-product"><i class="fas fa-trash"></i> Hapus</button>
						</div>
                        `;
					}
				}
			]
		});

		function updateTotals() {
			var subtotal = productRows.reduce(function (total, row) {
				return total + Number(row.qty) * Number(row.price);
			}, 0);
			var discount = Math.max(0, parseMoney($('#discount_display').val()));
			var tax = Math.max(0, parseMoney($('#tax_display').val()));

			$('#discount_display').val(formatMoney(discount));
			$('#tax_display').val(formatMoney(tax));
			$('#discount').val(discount);
			$('#tax').val(tax);
			$('#subtotal').val(formatMoney(subtotal));
			$('#grand_total').val(formatMoney(Math.max(0, subtotal - discount + tax)));
		}

		function saveDraft() {
			if (isEdit) {
				return;
			}

			if (productRows.length) {
				sessionStorage.setItem(storageKey, JSON.stringify(productRows));
			} else {
				sessionStorage.removeItem(storageKey);
			}
		}

		function clearProductErrors() {
			$('#product-error, #error_product_id, #error_product_qty, #error_product_price').text('');
		}

		function setProductError(field, message) {
			clearProductErrors();
			$('#error_' + field).text(message);
		}

		function redraw() {
			table.clear().rows.add(productRows).draw();
			updateTotals();
			saveDraft();
		}

		updateTotals();
		$('#add-product').on('click', function () {
			$('#product_index').val('');
			$('#product_id').val('').trigger('change');
            $('#product_qty, #product_price').val('');
			clearProductErrors();
			// $('#productModalLabel').text('Tambah Produk');
		});
        
		$('#save-product').on('click', function () {
			var productId   = $('#product_id').val();
			var index       = $('#product_index').val();
			var qty         = parseMoney($('#product_qty').val());
			var price       = parseMoney($('#product_price').val());
			var productName = $('#product_id option:selected').text();

			clearProductErrors();
			if (!productId) {
				return setProductError('product_id', 'Produk tidak boleh kosong.');
			}
			if (!Number.isInteger(qty) || qty < 1) {
				return setProductError('product_qty', 'Kuantitas harus berupa angka bulat lebih besar dari 0.');
			}
			if (!Number.isFinite(price) || price < 0) {
				return setProductError('product_price', 'Harga harus berupa angka yang valid dan tidak negatif.');
			}
			if (productRows.some(function (item, rowIndex) {
				return String(item.product_id) === String(productId) && String(rowIndex) !== String(index);
			})) {
				return setProductError('product_id', 'Produk tersebut sudah ditambahkan.');
			}

			var row = {
				product_id: productId,
				product_name: productName,
				qty: qty,
				price: price
			};
			if (isEdit) {
				var oldRow = index === '' ? null : productRows[Number(index)];
				var url = index === ''
					? '<?= base_url('purchase/detail_store') ?>/' + <?= (int) ($purchase['header']->purchase_id ?? 0) ?>
					: '<?= base_url('purchase/detail_update') ?>/' + <?= (int) ($purchase['header']->purchase_id ?? 0) ?> + '/' + oldRow.purchase_detail_id;

				$.post(url, row, function (response) {
					if (response.status !== 'success') {
						return Swal.fire('Gagal', response.message, 'error');
					}

					row = response.item;
					if (index === '') {
						productRows.push(row);
					} else {
						productRows[Number(index)] = row;
					}

					redraw();
					$('#productModal').modal('hide');
				}, 'json');
			} else {
				if (index === '') {
					productRows.push(row);
				} else {
					productRows[Number(index)] = row;
				}

				redraw();
				$('#productModal').modal('hide');
			}
		});
		$('#product_table tbody').on('click', '.edit-product', function () {
			var index = table.row($(this).closest('tr')).index();
			var row = productRows[index];

			$('#product_index').val(index);
			$('#product_id').val(row.product_id).trigger('change');
			$('#product_qty').val(formatMoney(row.qty));
			$('#product_price').val(formatMoney(row.price));
			clearProductErrors();
			// $('#productModalLabel').text('Edit Produk');
			$('#productModal').modal('show');
		});
		$('#product_table tbody').on('click', '.delete-product', function () {
			var index = table.row($(this).closest('tr')).index();
			var row = productRows[index];

			if (!isEdit) {
				productRows.splice(index, 1);
				redraw();
				return;
			}

			$.post('<?= base_url('purchase/detail_delete') ?>/' + <?= (int) ($purchase['header']->purchase_id ?? 0) ?> + '/' + row.purchase_detail_id, function (response) {
				if (response.status !== 'success') {
					return Swal.fire('Gagal', response.message, 'error');
				}

				productRows.splice(index, 1);
				redraw();
			}, 'json');
		});
		$('.money-input').on('input', function () {
			var value = parseMoney($(this).val());
			$(this).val(formatMoney(value));
			updateTotals();
		});
		$('.qty-input').on('input', function () {
			var value = parseMoney($(this).val());
			$(this).val(value ? formatMoney(value) : '');
		});
		$('#purchase-form').on('submit', function (event) {
			event.preventDefault();

			if (!productRows.length) {
				return Swal.fire('Perhatian', 'Tambahkan minimal satu produk.', 'warning');
			}

			var formData = new FormData(this);
			productRows.forEach(function (row) {
				formData.append('product_id[]', row.product_id);
				formData.append('qty[]', row.qty);
				formData.append('price[]', row.price);
			});

			Swal.fire({
				title: isEdit ? 'Update data ini?' : 'Simpan data ini?',
				icon: 'question',
				showCancelButton: true,
				confirmButtonText: isEdit ? 'Ya, update' : 'Ya, simpan',
				cancelButtonText: 'Batal',
				reverseButtons: true
			}).then(function (result) {
				if (!result.isConfirmed) {
					return;
				}
				$.ajax({
					url: $('#purchase-form').attr('action'),
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					dataType: 'json',
					success: function (response) {
						if (response.status === 'success') {
							if (!isEdit) {
								sessionStorage.removeItem(storageKey);
							}
							window.location.href = '<?= base_url('purchase') ?>';
						} else if (response.errors) {
							$.each(response.errors, function (field, message) {
								$('#error_' + field).text(message);
							}); 
						} else {
							Swal.fire('Gagal', response.message || 'Data tidak dapat disimpan.', 'error');
						}
					},
					error: function () {
						Swal.fire('Gagal', 'Terjadi kesalahan pada server.', 'error');
					}
				});
			});
		});
	}

	window.addEventListener('load', function () {
		if (page === 'index') initIndex();
		if (page === 'create') initPurchaseForm(false);
		if (page === 'edit') initPurchaseForm(true);
	});


}());
</script>
