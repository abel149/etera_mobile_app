
@extends('layouts.admin')
@section('content')
<div class="page-wrapper">
	<div class="page-content">
		<h3>Admins List</h3>

		@if(session('success'))
			<div class="alert alert-success alert-dismissible fade show" role="alert">
				{{ session('success') }}
				<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
			</div>
		@endif

		@if($errors->any())
			<div class="alert alert-danger alert-dismissible fade show" role="alert">
				<ul class="mb-0">
					@foreach($errors->all() as $error)
						<li>{{ $error }}</li>
					@endforeach
				</ul>
				<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
			</div>
		@endif

		<div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-body">
						<div class="table-responsive lead-table">
							<table class="table mb-0 align-middle">
								<thead class="table-light">
									<tr>
										<th>#</th>
										<th>Name</th>
										<th>Phone</th>
										<th>Email</th>
										<th>Role</th>
										<th>Registered</th>
										<th>Actions</th>
									</tr>
								</thead>
								<tbody>
									@foreach($admins as $index => $admin)
									<tr>
										<td>{{ $index + 1 }}</td>
										<td>
											<h6 class="mb-0 font-14">{{ $admin->name }}</h6>
										</td>
										<td>{{ $admin->phone_number }}</td>
										<td>{{ $admin->email ?? '-' }}</td>
										<td>
											<span class="badge {{ $admin->role === 'superadmin' ? 'bg-danger' : 'bg-primary' }}">
												{{ ucfirst($admin->role) }}
											</span>
										</td>
										<td>{{ $admin->created_at ? $admin->created_at->format('D M d, Y') : '-' }}</td>
										<td>
											<button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#adminDetailModal{{ $admin->id }}">
												<i class="bx bx-show me-0"></i>
											</button>
											@if(auth()->user()->role === 'superadmin' && $admin->id !== auth()->id() && $admin->role !== 'superadmin')
											<button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteAdminModal{{ $admin->id }}">
												<i class="bx bx-trash me-0"></i>
											</button>
											@endif
										</td>
									</tr>

									{{-- Detail Modal --}}
									<div class="modal fade" id="adminDetailModal{{ $admin->id }}" tabindex="-1" aria-hidden="true">
										<div class="modal-dialog modal-dialog-centered">
											<div class="modal-content shadow-lg border-0 rounded-4">
												<div class="modal-header" style="background: #0d6efd; color: white;">
													<h5 class="modal-title fw-bold" style="color: white;">Admin Details</h5>
													<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
												</div>
												<div class="modal-body p-4">
													<p><strong>Name:</strong> {{ $admin->name }}</p>
													<p><strong>Email:</strong> {{ $admin->email ?? '-' }}</p>
													<p><strong>Phone:</strong> {{ $admin->phone_number }}</p>
													<p><strong>Role:</strong> {{ ucfirst($admin->role) }}</p>
													<p><strong>Registered:</strong> {{ $admin->created_at }}</p>

													@if(auth()->user()->role === 'superadmin' && $admin->role !== 'superadmin')
													<hr>
													<h6 class="mb-3">Edit Admin</h6>
													<form action="{{ route('admin.admins.update', $admin->id) }}" method="POST">
														@csrf
														@method('PUT')
														<div class="mb-3">
															<label class="form-label">Name</label>
															<input type="text" name="name" class="form-control" value="{{ $admin->name }}" required>
														</div>
														<div class="mb-3">
															<label class="form-label">Phone Number</label>
															<input type="text" name="phone_number" class="form-control" value="{{ $admin->phone_number }}" required>
														</div>
														<div class="mb-3">
															<label class="form-label">Email</label>
															<input type="email" name="email" class="form-control" value="{{ $admin->email }}">
														</div>
														{{-- Superadmin cannot change admin's password --}}
														<p class="text-muted small"><i class="bx bx-lock-alt me-1"></i>Password cannot be changed by superadmin. Admin must change their own password from their profile.</p>
														<button type="submit" class="btn btn-primary w-100">
															<i class="bx bx-save me-1"></i>Save Changes
														</button>
													</form>
													@endif
												</div>
												<div class="modal-footer border-0">
													<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
												</div>
											</div>
										</div>
									</div>

									{{-- Delete Modal --}}
									@if(auth()->user()->role === 'superadmin' && $admin->id !== auth()->id() && $admin->role !== 'superadmin')
									<div class="modal fade" id="deleteAdminModal{{ $admin->id }}" tabindex="-1" aria-hidden="true">
										<div class="modal-dialog modal-dialog-centered">
											<div class="modal-content">
												<div class="modal-header">
													<h5 class="modal-title">Delete Admin</h5>
													<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
												</div>
												<div class="modal-body">Are you sure you want to delete admin <strong>{{ $admin->name }}</strong>?</div>
												<div class="modal-footer">
													<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
													<form action="{{ route('admin.admins.destroy', $admin->id) }}" method="POST" style="display:inline;">
														@csrf
														@method('DELETE')
														<button type="submit" class="btn btn-danger">Delete</button>
													</form>
												</div>
											</div>
										</div>
									</div>
									@endif

									@endforeach
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
