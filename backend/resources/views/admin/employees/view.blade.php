@extends('layouts.admin')
@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
	<div class="page-content">
		<h3 class="">Employees List</h3>
		<div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-body">
						<div class="row align-items-right">
							<div class="col-lg-9 col-xl-10">
								<form class="">
									<div class="row row-cols-auto g-2">
										<div class="col">
											<div class="position-relative">
												<input type="text" class="form-control ps-5 radius-30 " placeholder="Search Employee..."> <span class="position-absolute top-50 product-show translate-middle-y"><i class="bx bx-search"></i></span>
											</div>
										</div>
										<div class="col">
											<div class="btn-group" role="group" aria-label="Button group with nested dropdown">
												<button type="button" class="btn btn-white radius-30">
												<i class="bx bx-filter"></i> Filter</button>
												<div class="btn-group" role="group">
												  <button id="btnGroupDrop1" type="button" class="btn btn-white radius-30 dropdown-toggle dropdown-toggle-nocaret px-1" data-bs-toggle="dropdown" aria-expanded="false">
													<i class='bx bx-chevron-down'></i>
												  </button>
												  <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
													<li><a class="dropdown-item" href="#">Name</a></li>
													<li><a class="dropdown-item" href="#">Phone</a></li>
													<li><a class="dropdown-item" href="#">Date Modified</a></li>
												  </ul>
												</div>
											  </div>
										</div>
										<div class="col">
											<div class="position-relative">
												<a href="/admin/add-employee" type="button" class="btn btn-primary radius-30 "><i class="bx bx-plus me-0"></i> Add Employee
										</a>
											</div>
										</div>
										{{-- <div class="col">
										<button type="button" class="btn btn-danger radius-30" data-bs-toggle="modal" data-bs-target="#selectedDelete"><i class="bx bx-trash me-0"></i> Delete</button>
									</div> --}}
									</div>
								</form>
							</div>
						</div>

				<div class="table-responsive lead-table">
					<table class="table mb-0 align-middle">
						<thead class="table-light">
							<tr>
								<th >
									<input class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
						  		</th>
								<th>Name</th>
								<th>Phone #</th>
								<th>Level</th>
								<th>Manager</th>
								<th>Completed files</th>
								<th>Remaining files</th>
								<th>Active files</th>
								<th>Actions</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
              @foreach($employees as $employee)
							<tr data-employee-id="{{ $employee->id }}">
								<td><input class="form-check-input" type="checkbox" value="" id="flexCheckDefault"></td>
								<td>
									<div class="d-flex align-items-center">
										<div>
											<h6 class="mb-0 font-14">{{$employee->name}}</h6>
											<p class="mb-0 font-13 text-secondary">{{$employee->email}}</p>
										</div>
									</div>
								</td>
								<td>{{$employee->phone_number}}</td>
								<td>{{$employee->level?->id ?? 'N/A'}}</td>
								<td>
									@if($employee->role === 'operator')
									<button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#assignManagerModal{{ $employee->id }}">
										{{ $employee->myManager?->manager?->name ?? 'Assign Manager' }}
									</button>
									
									<!-- Assign Manager Modal -->
									<div class="modal fade" id="assignManagerModal{{ $employee->id }}" tabindex="-1">
										<div class="modal-dialog">
											<div class="modal-content">
												<div class="modal-header">
													<h5 class="modal-title">Assign Manager to {{ $employee->name }}</h5>
													<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
												</div>
												<form action="{{ route('admin.employees.assign-manager', $employee->id) }}" method="POST">
													@csrf
													<div class="modal-body">
														<div class="mb-3">
															<label class="form-label">Select Manager</label>
															<select name="manager_id" class="form-select" required>
																<option value="">-- Select Manager --</option>
																@foreach(\App\Models\User::where('role', 'manager')->get() as $manager)
																	<option value="{{ $manager->id }}" {{ $employee->myManager?->manager_id == $manager->id ? 'selected' : '' }}>
																		{{ $manager->name }} ({{ $manager->email }})
																	</option>
																@endforeach
															</select>
															<small class="text-muted">
																<i class="bx bx-info-circle"></i> 
																An operator can only have one manager. Selecting a new manager will replace the current one.
															</small>
														</div>
													</div>
													<div class="modal-footer">
														<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
														<button type="submit" class="btn btn-primary">Assign Manager</button>
													</div>
												</form>
											</div>
										</div>
									</div>
									@else
									{{ $employee->myManager?->manager?->name ?? 'N/A' }}
									@endif
								</td>
								<td>{{$employee->proformaSelections()->where('active', false)->count()}}</td>
								<td>{{$employee->proformaSelections()->where('active', true)->count()}}</td>
								<td>{{$employee->proformaSelections()->where('active', true)->count()}}</td>
								<td>

									
									<a href="{{ route('admin.employees.edit', $employee->id) }}" class="btn radius-10 p-1">
										<i class="bx bx-edit me-0"></i>
									</a>
									<button type="button" class="btn radius-10 p-1 text-danger" data-bs-toggle="modal" data-bs-target="#singleDelete"><i class="bx bx-trash me-0"></i>
									</button>
									
									
									
									
									{{-- <a href="{{ route('admin.employees.edit-employee', $employee->id) }}" class="btn btn-primary p-1">
										<i class="bx bx-edit me-0"></i> Edit
									</a>
									
									 --}}
									
									
									
									<button type="button" class="btn radius-10 p-1 text-success" data-bs-toggle="modal" data-bs-target="#addfiles"><i class="bx bx-folder-plus me-0"></i>
									</button>
								</td>
							</tr>
            @endforeach
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

<!-- Selected Delete Modal -->
<div class="modal fade" id="selectedDelete" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Delete Garage Users</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">Are you sure you want to delete the selected garage users?</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-outline-secondary radius-30" data-bs-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-danger radius-30">Delete</button>
			</div>
		</div>
	</div>
</div>
<!-- End Selected Delete Modal -->

<!-- Single Delete Modal -->
<div class="modal fade" id="singleDelete" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Delete</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">Are you sure you want to delete this garage user?</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-outline-secondary radius-30" data-bs-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-danger radius-30">Delete</button>
			</div>
		</div>
	</div>
</div>
<!-- End Single Delete Modal -->

<!-- Add Files Modal -->
<div class="modal fade" id="addfiles" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Add Files</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form id="addFilesForm" method="POST" action="">
					@csrf
					<input type="hidden" id="employeeId" name="employee_id">
					<label class="form-label">Enter the amount of files to add to this user</label>
					<input type="number" name="file_count" class="form-control" placeholder="Enter number of files" min="1" max="100" required>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-outline-secondary radius-30" data-bs-dismiss="modal">Cancel</button>
				<button type="submit" form="addFilesForm" class="btn btn-success radius-30">Add</button>
			</div>
		</div>
	</div>
</div>
<!-- End Add Files Modal -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle file assignment modal
    const addFilesModal = document.getElementById('addfiles');
    if (addFilesModal) {
        addFilesModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const employeeId = button.closest('tr').getAttribute('data-employee-id');
            document.getElementById('employeeId').value = employeeId;
            document.getElementById('addFilesForm').action = '{{ route("admin.employees.assign-files", ":id") }}'.replace(':id', employeeId);
        });
    }
    
    // Handle delete modals
    const singleDeleteModal = document.getElementById('singleDelete');
    if (singleDeleteModal) {
        singleDeleteModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const employeeId = button.closest('tr').querySelector('input[type="checkbox"]').value || 
                              button.closest('tr').getAttribute('data-employee-id');
            // Set up delete form action
            const deleteForm = document.createElement('form');
            deleteForm.method = 'POST';
            deleteForm.action = '{{ route("admin.employees.destroy", ":id") }}'.replace(':id', employeeId);
            deleteForm.innerHTML = '@csrf @method("DELETE")';
            document.body.appendChild(deleteForm);
        });
    }
});
</script>
@endsection
