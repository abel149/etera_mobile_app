@extends('layouts.admin')
@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
	<div class="page-content">
		<div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-header">
						<div class="d-flex align-items-center justify-content-between">
							<h5 class="mb-0">User Details</h5>
							<a href="{{ route('admin.users.approvals') }}" class="btn btn-secondary">
								<i class="bx bx-arrow-back"></i> Back to Approvals
							</a>
						</div>
					</div>
					<div class="card-body">
						<div class="row">
							<div class="col-md-6">
								<h6 class="mb-3">Basic Information</h6>
								<table class="table table-borderless">
									<tr>
										<td><strong>Full Name:</strong></td>
										<td>{{ $user->name }}</td>
									</tr>
									<tr>
										<td><strong>Email:</strong></td>
										<td>{{ $user->email }}</td>
									</tr>
									<tr>
										<td><strong>Phone Number:</strong></td>
										<td>{{ $user->phone_number }}</td>
									</tr>
									<tr>
										<td><strong>Role:</strong></td>
										<td>
											<span class="badge 
												@if($user->role == 'business_owner') bg-info
												@elseif($user->role == 'garage') bg-success
												@elseif($user->role == 'shop') bg-warning
												@endif">
												{{ ucfirst(str_replace('_', ' ', $user->role)) }}
											</span>
										</td>
									</tr>
									@if($user->store_id)
									<tr>
										<td><strong>Store ID:</strong></td>
										<td>{{ $user->store_id }}</td>
									</tr>
									@endif
									<tr>
										<td><strong>Registration Date:</strong></td>
										<td>{{ $user->created_at->format('M d, Y H:i A') }}</td>
									</tr>
								</table>
							</div>
							<div class="col-md-6">
								<h6 class="mb-3">Additional Information</h6>
								<table class="table table-borderless">
									@if($user->tin_number)
									<tr>
										<td><strong>TIN Number:</strong></td>
										<td>{{ $user->tin_number }}</td>
									</tr>
									@endif
									@if($user->location)
									<tr>
										<td><strong>Location:</strong></td>
										<td>{{ $user->location }}</td>
									</tr>
									@endif
									<tr>
										<td><strong>Approval Status:</strong></td>
										<td>
											@if($user->approved)
												<span class="badge bg-success">Approved</span>
												@if($user->approved_at)
													<br><small class="text-muted">{{ $user->approved_at->format('M d, Y H:i A') }}</small>
												@endif
											@else
												<span class="badge bg-warning">Pending Approval</span>
											@endif
										</td>
									</tr>
									@if($user->brands && $user->brands->count() > 0)
									<tr>
										<td><strong>Associated Brands:</strong></td>
										<td>
											@foreach($user->brands as $brand)
												<span class="badge bg-secondary me-1">{{ $brand->name }}</span>
											@endforeach
										</td>
									</tr>
									@endif
								</table>
							</div>
						</div>

						@if($user->license_image || $user->stamp_image)
						<hr>
						<div class="row">
							<div class="col-12">
								<h6 class="mb-3">Documents</h6>
								<div class="row">
									@if($user->license_image)
									<div class="col-md-6 mb-3">
										<div class="card">
											<div class="card-header">
												<h6 class="mb-0">Business License</h6>
											</div>
											<div class="card-body text-center">
												<img src="{{ Storage::url($user->license_image) }}" alt="Business License" class="img-fluid" style="max-height: 300px;">
												<br><br>
												<a href="{{ Storage::url($user->license_image) }}" target="_blank" class="btn btn-primary btn-sm">
													<i class="bx bx-download"></i> View Full Size
												</a>
											</div>
										</div>
									</div>
									@endif

									@if($user->stamp_image)
									<div class="col-md-6 mb-3">
										<div class="card">
											<div class="card-header">
												<h6 class="mb-0">Stamp Image</h6>
											</div>
											<div class="card-body text-center">
												<img src="{{ Storage::url($user->stamp_image) }}" alt="Stamp Image" class="img-fluid" style="max-height: 300px;">
												<br><br>
												<a href="{{ Storage::url($user->stamp_image) }}" target="_blank" class="btn btn-primary btn-sm">
													<i class="bx bx-download"></i> View Full Size
												</a>
											</div>
										</div>
									</div>
									@endif
								</div>
							</div>
						</div>
						@endif

						<hr>
						<div class="row">
							<div class="col-12">
								<h6 class="mb-3">Actions</h6>
								<div class="d-flex gap-2">
									@if(auth()->user()->isSuperAdmin())
										@if(!$user->approved)
											<form action="{{ route('admin.users.approve', $user->id) }}" method="POST">
												@csrf
												@method('PATCH')
												<button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to approve this user?')">
													<i class="bx bx-check"></i> Approve User
												</button>
											</form>
										@else
											<form action="{{ route('admin.users.revoke', $user->id) }}" method="POST">
												@csrf
												@method('PATCH')
												<button type="submit" class="btn btn-warning" onclick="return confirm('Are you sure you want to revoke approval for this user?')">
													<i class="bx bx-x"></i> Revoke Approval
												</button>
											</form>
										@endif
										<form action="{{ route('admin.users.delete', $user->id) }}" method="POST">
											@csrf
											@method('DELETE')
											<button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
												<i class="bx bx-trash"></i> Delete User
											</button>
										</form>
									@else
										<div class="alert alert-info">
											<i class="bx bx-info-circle"></i> Only superadmin can approve, revoke, or delete users.
										</div>
									@endif
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!--end page wrapper -->
@endsection