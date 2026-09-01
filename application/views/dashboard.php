<div class="callout callout-info">
	<h5><i class="fas fa-tachometer-alt mr-2"></i>Dashboard IT Support</h5>
	<p class="mb-0">Ringkasan kondisi ticket, employee, dan product saat ini.</p>
</div>

<div id="dashboard-loading" class="text-center text-muted py-4">
	<i class="fas fa-spinner fa-spin mr-2"></i> Memuat data dashboard...
</div>

<div id="dashboard-content" class="d-none">
	<div class="row">
		<div class="col-lg-4 col-md-6">
			<div class="small-box bg-info">
				<div class="inner">
					<h3 id="tickets-total">0</h3>
					<p>Total Ticket</p>
				</div>
				<div class="icon"><i class="fas fa-ticket-alt"></i></div>
				<a href="<?= base_url('ticket') ?>" class="small-box-footer">Lihat ticket <i class="fas fa-arrow-circle-right"></i></a>
			</div>
		</div>
		<div class="col-lg-4 col-md-6">
			<div class="small-box bg-success">
				<div class="inner">
					<h3 id="employees-total">0</h3>
					<p>Total Employee</p>
				</div>
				<div class="icon"><i class="fas fa-users"></i></div>
				<a href="<?= base_url('employee') ?>" class="small-box-footer">Lihat employee <i class="fas fa-arrow-circle-right"></i></a>
			</div>
		</div>
		<div class="col-lg-4 col-md-6">
			<div class="small-box bg-warning">
				<div class="inner">
					<h3 id="products-total">0</h3>
					<p>Total Product</p>
				</div>
				<div class="icon"><i class="fas fa-box"></i></div>
				<a href="<?= base_url('product') ?>" class="small-box-footer">Lihat product <i class="fas fa-arrow-circle-right"></i></a>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-lg-7 mb-4">
			<div class="card card-outline card-info h-100">
				<div class="card-header">
					<h5><i class="fas fa-chart-bar text-info mr-2"></i>Status Ticket</h5>
				</div>
				<div class="card-body">
					<div class="mb-3">
						<div class="d-flex justify-content-between mb-1"><span>Open</span><strong id="tickets-open">0</strong></div>
						<div class="progress progress-sm"><div id="bar-open" class="progress-bar bg-info" role="progressbar"></div></div>
					</div>
					<div class="mb-3">
						<div class="d-flex justify-content-between mb-1"><span>In Progress</span><strong id="tickets-in-progress">0</strong></div>
						<div class="progress progress-sm"><div id="bar-in-progress" class="progress-bar bg-warning" role="progressbar"></div></div>
					</div>
					<div class="mb-3">
						<div class="d-flex justify-content-between mb-1"><span>Done</span><strong id="tickets-done">0</strong></div>
						<div class="progress progress-sm"><div id="bar-done" class="progress-bar bg-success" role="progressbar"></div></div>
					</div>
					<div>
						<div class="d-flex justify-content-between mb-1"><span>Cancelled</span><strong id="tickets-cancelled">0</strong></div>
						<div class="progress progress-sm"><div id="bar-cancelled" class="progress-bar bg-danger" role="progressbar"></div></div>
					</div>
				</div>
			</div>
		</div>

		<div class="col-lg-5 mb-4">
			<div class="card card-outline card-success h-100">
				<div class="card-header">
					<h5><i class="fas fa-database text-success mr-2"></i>Status Data</h5>
				</div>
				<div class="card-body">
					<div class="d-flex justify-content-between border-bottom pb-2 mb-2">
						<span>Employee aktif</span><strong id="employees-active">0</strong>
					</div>
					<div class="d-flex justify-content-between border-bottom pb-2 mb-2">
						<span>Employee tidak aktif</span><strong id="employees-inactive">0</strong>
					</div>
					<div class="d-flex justify-content-between border-bottom pb-2 mb-2">
						<span>Product aktif</span><strong id="products-active">0</strong>
					</div>
					<div class="d-flex justify-content-between">
						<span>Product tidak aktif</span><strong id="products-inactive">0</strong>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="dashboard-error" class="alert alert-danger d-none">
	<i class="fas fa-exclamation-triangle mr-2"></i> Data dashboard gagal dimuat. Silakan refresh halaman.
</div>

<script>
window.addEventListener('load', function () {
	$.ajax({
		url: '<?= base_url('dashboard/get_data') ?>',
		type: 'GET',
		dataType: 'json',
		success: function (data) {
			var totalTickets = Number(data.tickets_total) || 0;
			var ticketStatuses = {
				open: Number(data.tickets_open) || 0,
				inProgress: Number(data.tickets_in_progress) || 0,
				done: Number(data.tickets_done) || 0,
				cancelled: Number(data.tickets_cancelled) || 0
			};

			$('#tickets-total').text(totalTickets);
			$('#employees-total').text(Number(data.employees_total) || 0);
			$('#products-total').text(Number(data.products_total) || 0);
			$('#tickets-open').text(ticketStatuses.open);
			$('#tickets-in-progress').text(ticketStatuses.inProgress);
			$('#tickets-done').text(ticketStatuses.done);
			$('#tickets-cancelled').text(ticketStatuses.cancelled);
			$('#employees-active').text(Number(data.employees_active) || 0);
			$('#employees-inactive').text(Number(data.employees_inactive) || 0);
			$('#products-active').text(Number(data.products_active) || 0);
			$('#products-inactive').text(Number(data.products_inactive) || 0);

			$('#bar-open').css('width', (totalTickets ? ticketStatuses.open / totalTickets * 100 : 0) + '%');
			$('#bar-in-progress').css('width', (totalTickets ? ticketStatuses.inProgress / totalTickets * 100 : 0) + '%');
			$('#bar-done').css('width', (totalTickets ? ticketStatuses.done / totalTickets * 100 : 0) + '%');
			$('#bar-cancelled').css('width', (totalTickets ? ticketStatuses.cancelled / totalTickets * 100 : 0) + '%');

			$('#dashboard-loading').addClass('d-none');
			$('#dashboard-content').removeClass('d-none');
		},
		error: function () {
			$('#dashboard-loading').addClass('d-none');
			$('#dashboard-error').removeClass('d-none');
		}
	});
});
</script>
