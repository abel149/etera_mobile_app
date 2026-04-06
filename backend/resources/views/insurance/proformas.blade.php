@extends('layouts.insurance')
@section('content')
<div class="margin-top-15 margin-bottom-45n"></div>
@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@elseif ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                <script>console.error('❌ Validation Error: {{ $error }}');</script>
            @endforeach
        </ul>
    </div>
@else
    <script>
        console.log('ℹ️ No success or error messages in session.');
    </script>
@endif

<style type="text/css">
	.table td:last-child {
      white-space: nowrap;
      width: 1%;
    }
</style>

<h3 class="">Received Proforma</h3>

<div class="row row-cols-12 row-cols-lg-12 row-cols-xl-12">
	<div class="col mx-auto">
	<div class=" my-5 my-lg-0 shadow-none ">
		<div class="card radius-10">
			<div class="card-body">
				<div class="row align-items-right">
					<div class="col-lg-9 col-xl-10">
						<form class="">
							<div class="row row-cols-lg-2 row-cols-xl-auto g-2">
								<div class="col">
									<div class="position-relative">
										<input id="searchInput" type="text" class="form-control ps-5 radius-30" placeholder="Search License Plate or Phone...">
										<span class="position-absolute top-50 product-show translate-middle-y">
											<i class="bx bx-search"></i>
										</span>
									</div>
								</div>

								<div class="col">
									<div class="btn-group" role="group">
										<button type="button" class="btn btn-white radius-30">
											<i class="bx bx-filter"></i> Filter
										</button>
										<div class="btn-group" role="group">
											<button id="btnGroupDrop1" type="button" class="btn btn-white radius-30 dropdown-toggle dropdown-toggle-nocaret px-1" data-bs-toggle="dropdown">
												<i class='bx bx-chevron-down'></i>
											</button>
											<ul class="dropdown-menu">
												<li><a class="dropdown-item" href="#">Name</a></li>
												<li><a class="dropdown-item" href="#">Tin #</a></li>
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
					<table id="proformaTable" class="table mb-0 align-middle">
						<thead class="table-light">
							<tr>
								<th>File #</th>
								<th>Customer Name</th>
								<th>Car Brand</th>
								<th>Model</th>
								<th>Year</th>
								<th>License Plate</th>
								<th>Phone #</th>
								<th>Show</th>
							</tr>
						</thead>

						<tbody>
						@foreach($proformas as $proforma)
							<tr>
								<td>#{{$proforma->file_number}}</td>
								<td>{{$proforma->customer_name}}</td>
								<td>{{$proforma->brand->name}}</td>
								<td>{{$proforma->model}}</td>
								<td>{{$proforma->year}}</td>
								<td class="plate">{{$proforma->license_plate_number}}</td>
								<td class="phone">{{$proforma->customer_phone_number}}</td>
								<td>
									<a class="btn" data-id="{{$proforma->id}}" data-bs-toggle="modal" data-bs-target="#pass">
										<i class="bx bx-show me-0"></i>
									</a>
								</td>
							</tr>
						@endforeach
						</tbody>

					</table>
				</div>

			</div>
		</div>
	</div>
	</div>
</div>

<!-- Password Modal -->
<div class="modal fade" id="pass" tabindex="-1">
   	<form action="/check-password" method="POST">
	@csrf
    <div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Password Required</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>

			<div class="modal-body">
				<label class="form-label">Enter your password to access this file</label>
                <input type="password" name="password" class="form-control" placeholder="********">
            </div>

            <input type="hidden" name="proforma" id="proformaId">

			<div class="modal-footer">
				<button type="button" class="btn btn-outline-secondary radius-30" data-bs-dismiss="modal">Cancel</button>
				<button type="submit" class="btn btn-primary radius-30">Enter</button>
			</div>
		</div>
	</div>
    </form>
</div>

<script>
	// Assign proforma ID in modal
	document.getElementById('pass').addEventListener('show.bs.modal', function (event) {
		let proformaId = event.relatedTarget.getAttribute('data-id');
		document.getElementById('proformaId').value = proformaId;
	});

	// ============================
	//  🔎 JS SEARCH FUNCTIONALITY
	// ============================
	document.getElementById("searchInput").addEventListener("keyup", function () {
		let filter = this.value.toLowerCase();
		let rows = document.querySelectorAll("#proformaTable tbody tr");
		
		rows.forEach(row => {
			let plate = row.querySelector(".plate").textContent.toLowerCase();
			let phone = row.querySelector(".phone").textContent.toLowerCase();

			if (plate.includes(filter) || phone.includes(filter)) {
				row.style.display = "";
			} else {
				row.style.display = "none";
			}
		});
	});
</script>

@endsection
