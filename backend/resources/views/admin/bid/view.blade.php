@extends('layouts.admin')
@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
	<div class="page-content">
		<h3 class="">Bid List</h3>
		<div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-body">
						<div class="row align-items-right">
							<div class="col-lg-9 col-xl-10">
								<form class="">
									<div class="row row-cols-lg-2 row-cols-xl-auto g-2">
										<div class="col">
											<div class="position-relative">
												<input type="text" class="form-control ps-5" placeholder="Search Product..."> <span class="position-absolute top-50 product-show translate-middle-y"><i class="bx bx-search"></i></span>
											</div>
										</div>
										<div class="col">
											<div class="btn-group" role="group" aria-label="Button group with nested dropdown">
												<button type="button" class="btn btn-white">
												<i class="bx bx-filter"></i> Filter</button>
												<div class="btn-group" role="group">
												  <button id="btnGroupDrop1" type="button" class="btn btn-white dropdown-toggle dropdown-toggle-nocaret px-1" data-bs-toggle="dropdown" aria-expanded="false">
													<i class='bx bx-chevron-down'></i>
												  </button>
												  <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
													<li><a class="dropdown-item" href="#">Name</a></li>
													<li><a class="dropdown-item" href="#">File number</a></li>
													<li><a class="dropdown-item" href="#">Date Modified</a></li>
												  </ul>
												</div>
											  </div>
										</div>

									</div>
								</form>
							</div>
						</div>

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
		<!--end row-->


	</div>
</div>
</div>
</div>
<!--end page wrapper -->
@endsection