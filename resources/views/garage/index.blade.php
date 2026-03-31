@extends('layouts.admin')
@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
	<div class="page-content">
		
		<div class="row row-cols-1 row-cols-md-2 row-cols-xl-2">
			<div class="col">
				<div class="card radius-10">
					<div class="card-body">
						<div class="d-flex align-items-center justify-content-between">
							<div>
								<p class="mb-0">Total Users</p>
								<h5 class="mb-0">90K</h5>
							</div>
							<div id="chart1"></div>
						</div>
					</div>
				</div>
			</div>
			<div class="col">
				<div class="card radius-10">
					<div class="card-body">
						<div class="d-flex align-items-center justify-content-between">
							<div>
								<p class="mb-0">Insurances</p>
								<h5 class="mb-0">42.8K</h5>
							</div>
							<div id="chart2"></div>
						</div>
					</div>
				</div>
			</div>
			<div class="col">
				<div class="card radius-10">
					<div class="card-body">
						<div class="d-flex align-items-center justify-content-between">
							<div>
								<p class="mb-0">Guarages</p>
								<h5 class="mb-0">25.2K</h5>
							</div>
							<div id="chart3"></div>
						</div>
					</div>
				</div>
			</div>
			<div class="col">
				<div class="card radius-10">
					<div class="card-body">
						<div class="d-flex align-items-center justify-content-between">
							<div>
								<p class="mb-0">Sparepart Shops</p>
								<h5 class="mb-0">22K</h5>
							</div>
							<div id="chart4"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!--end row-->
		
		<!--end row-->
		<div class="card radius-10">
			<div class="card-body">
				<div class="table-responsive lead-table">
					<table class="table mb-0 align-middle">
						<thead class="table-light">
							<tr>
								<th>File #</th>
								<th>Customer Name</th>
								<th>Guarage Proforma</th>
								<th>Sparepart Proforma</th>
								<th>Progress</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>75849</td>
								
								<td>2 added</td>
								<td>1 added</td>
								<td class=" w-25">
									<div class="progress radius-10" style="height:4.5px;">
										<div class="progress-bar bg-primary" role="progressbar" style="width: 66%"></div>
									</div>
								</td>
								<td>
									<div class="badge rounded-pill bg-primary w-100">In Progress</div>
								</td>
							</tr>
							<tr>
							<td>32749</td>
								<td>
									<div class="d-flex align-items-center">
											<h6 class="mb-0 font-14">David Buckley</h6>
									</div>
								</td>
								<td>2 added</td>
								<td>1 added</td>
								<td class=" w-25">
									<div class="progress radius-10" style="height:4.5px;">
										<div class="progress-bar bg-danger" role="progressbar" style="width: 76%"></div>
									</div>
								</td>
								<td>
									<div class="badge rounded-pill bg-danger w-100">Cancelled</div>
								</td>
							</tr>
							<tr>
							<td>34769</td>
								<td>
									<div class="d-flex align-items-center">
											<h6 class="mb-0 font-14">James Caviness</h6>
									</div>
								</td>
								<td>3 added</td>
								<td>3 added</td>
								<td class=" w-25">
									<div class="progress radius-10" style="height:4.5px;">
										<div class="progress-bar bg-success" role="progressbar" style="width: 100%"></div>
									</div>
								</td>
								<td>
									<div class="badge rounded-pill bg-success w-100">Completed</div>
								</td>
							</tr>
							<tr>
							<td>97234</td>
								<td>
									<div class="d-flex align-items-center">
											<h6 class="mb-0 font-14">John Roman</h6>
									</div>
								</td>
								<td>3 added</td>
								<td>2 added</td>
								<td class=" w-25">
									<div class="progress radius-10" style="height:4.5px;">
										<div class="progress-bar bg-primary" role="progressbar" style="width: 58%"></div>
									</div>
								</td>
								<td>
									<div class="badge rounded-pill bg-primary w-100">In Progress</div>
								</td>
							</tr>
							<tr>
							<td>83459</td>
								<td>
									<div class="d-flex align-items-center">
											<h6 class="mb-0 font-14">Johnney Seitz</h6>
									</div>
								</td>
								<td>2 added</td>
								<td>2 added</td>
								<td class=" w-25">
									<div class="progress radius-10" style="height:4.5px;">
										<div class="progress-bar bg-danger" role="progressbar" style="width: 66%"></div>
									</div>
								</td>
								<td>
									<div class="badge rounded-pill bg-danger w-100">Cancelled</div>
								</td>
							</tr>
							<tr>
							<td>58932</td>
								<td>
									<div class="d-flex align-items-center">
											<h6 class="mb-0 font-14">Ronald Waters</h6>
									</div>
								</td>
								<td>3 added</td>
								<td>3 added</td>
								<td class=" w-25">
									<div class="progress radius-10" style="height:4.5px;">
										<div class="progress-bar bg-success" role="progressbar" style="width: 100%"></div>
									</div>
								</td>
								<td>
									<div class="badge rounded-pill bg-success w-100">Completed</div>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
<!--end page wrapper -->

@endsection