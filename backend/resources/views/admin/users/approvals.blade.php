@extends('layouts.admin')
@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
	<div class="page-content">
		<h3 class="">User Approval Management</h3>
		<div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-body">
						<div class="row align-items-center">
							<div class="col-lg-9 col-xl-10">
								<form class="" method="GET">
									<div class="row row-cols-auto g-2">
										<div class="col">
											<div class="position-relative">
												<input type="text" class="form-control ps-5" placeholder="Search Users..." name="search" value="{{ request('search') }}">
												<span class="position-absolute top-50 product-show translate-middle-y"><i class="bx bx-search"></i></span>
											</div>
										</div>
										<div class="col">
											<select class="form-select" name="role">
												<option value="">All Roles</option>
												<option value="business_owner" {{ request('role') == 'business_owner' ? 'selected' : '' }}>Business Owner</option>
												<option value="garage" {{ request('role') == 'garage' ? 'selected' : '' }}>Garage</option>
												<option value="shop" {{ request('role') == 'shop' ? 'selected' : '' }}>Spare Part Shop</option>
											</select>
										</div>
										<div class="col">
											<select class="form-select" name="status">
												<option value="">All Status</option>
												<option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Approval</option>
												<option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
											</select>
										</div>
										<div class="col">
											<button type="submit" class="btn btn-primary">Filter</button>
										</div>
									</div>
								</form>
							</div>
						</div>
						<div class="table-responsive">
							<table class="table mb-0">
								<thead class="table-light">
									<tr>
										<th>User Info</th>
										<th>Role</th>
										<th>Contact</th>
										<th>Additional Info</th>
										<th>Documents</th>
										<th>Status</th>
										<th>Registered</th>
										<th>Actions</th>
									</tr>
								</thead>
								<tbody>
									@forelse($users as $user)
									<tr>
										<td>
											<div class="d-flex align-items-center">
												<div class="ms-2">
													<h6 class="mb-0 font-14">{{ $user->name }}</h6>
													<p class="mb-0 font-13 text-secondary">{{ $user->email }}</p>
												</div>
											</div>
										</td>
										<td>
											<span class="badge 
												@if($user->role == 'business_owner') bg-info
												@elseif($user->role == 'garage') bg-success
												@elseif($user->role == 'shop') bg-warning
												@endif">
												{{ ucfirst(str_replace('_', ' ', $user->role)) }}
											</span>
											@if($user->store_id)
												<br><small class="text-muted">ID: {{ $user->store_id }}</small>
											@endif
										</td>
										<td>
											<p class="mb-0 font-13">{{ $user->phone_number }}</p>
											@if($user->location)
												<small class="text-muted">{{ Str::limit($user->location, 30) }}</small>
											@endif
										</td>
										<td>
											@if($user->tin_number)
												<p class="mb-0 font-13"><strong>TIN:</strong> {{ $user->tin_number }}</p>
											@endif
											@if($user->brands && $user->brands->count() > 0)
												<small class="text-muted"><strong>Brands:</strong> {{ $user->brands->pluck('name')->implode(', ') }}</small>
											@endif
										</td>
										<td>
											@if($user->license_image)
												<a href="{{ Storage::url($user->license_image) }}" target="_blank" class="btn btn-sm btn-outline-primary mb-1">
													<i class="bx bx-file"></i> License
												</a><br>
											@endif
											@if($user->stamp_image)
												<a href="{{ Storage::url($user->stamp_image) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
													<i class="bx bx-file"></i> Stamp
												</a>
											@endif
										</td>
										<td>
											@if($user->approved)
												<span class="badge bg-success">Approved</span>
												@if($user->approved_at)
													<br><small class="text-muted">{{ $user->approved_at->format('M d, Y') }}</small>
												@endif
											@else
												<span class="badge bg-warning">Pending</span>
											@endif
										</td>
										<td>
											<small class="text-muted">{{ $user->created_at->format('M d, Y') }}</small>
										</td>
										<td>
											<div class="d-flex order-actions">
												@if(auth()->user()->isSuperAdmin())
													@if(!$user->approved)
														<form action="{{ route('admin.users.approve', $user->id) }}" method="POST" class="me-1">
															@csrf
															@method('PATCH')
															<button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to approve this user?')">
																<i class="bx bx-check"></i> Approve
															</button>
														</form>
													@else
														<form action="{{ route('admin.users.revoke', $user->id) }}" method="POST" class="me-1">
															@csrf
															@method('PATCH')
															<button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Are you sure you want to revoke approval for this user?')">
																<i class="bx bx-x"></i> Revoke
															</button>
														</form>
													@endif
													<a href="{{ route('admin.users.view', $user->id) }}" class="btn btn-sm btn-primary me-1">
														<i class="bx bx-show"></i> View
													</a>
													<form action="{{ route('admin.users.delete', $user->id) }}" method="POST" class="d-inline">
														@csrf
														@method('DELETE')
														<button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
															<i class="bx bx-trash"></i> Delete
														</button>
													</form>
												@else
													<a href="{{ route('admin.users.view', $user->id) }}" class="btn btn-sm btn-primary">
														<i class="bx bx-show"></i> View
													</a>
												@endif
											</div>
										</td>
									</tr>
									@empty
									<tr>
										<td colspan="8" class="text-center py-4">
											<div class="d-flex flex-column align-items-center">
												<i class="bx bx-user-x font-50 text-muted mb-3"></i>
												<h6 class="text-muted">No users found</h6>
												<p class="text-muted">Try adjusting your search criteria</p>
											</div>
										</td>
									</tr>
									@endforelse
								</tbody>
							</table>
						</div>
						
						<!-- Pagination -->
						@if($users->hasPages())
						<div class="d-flex justify-content-center mt-3">
							{{ $users->appends(request()->query())->links() }}
						</div>
						@endif
					</div>
				</div>
			</div>
		</div>
		
		<!-- Statistics Cards -->
		<div class="row mt-4">
			<div class="col-12 col-lg-3">
				<div class="card radius-10 border-start border-0 border-3 border-info">
					<div class="card-body">
						<div class="d-flex align-items-center">
							<div>
								<p class="mb-0 text-secondary">Pending Approvals</p>
								<h4 class="my-1 text-info">{{ $stats['pending'] ?? 0 }}</h4>
							</div>
							<div class="widgets-icons-2 rounded-circle bg-gradient-scooter text-white ms-auto">
								<i class="bx bx-time"></i>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-12 col-lg-3">
				<div class="card radius-10 border-start border-0 border-3 border-success">
					<div class="card-body">
						<div class="d-flex align-items-center">
							<div>
								<p class="mb-0 text-secondary">Approved Users</p>
								<h4 class="my-1 text-success">{{ $stats['approved'] ?? 0 }}</h4>
							</div>
							<div class="widgets-icons-2 rounded-circle bg-gradient-ohhappiness text-white ms-auto">
								<i class="bx bx-check-circle"></i>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-12 col-lg-3">
				<div class="card radius-10 border-start border-0 border-3 border-warning">
					<div class="card-body">
						<div class="d-flex align-items-center">
							<div>
								<p class="mb-0 text-secondary">Business Owners</p>
								<h4 class="my-1 text-warning">{{ $stats['business_owners'] ?? 0 }}</h4>
							</div>
							<div class="widgets-icons-2 rounded-circle bg-gradient-blooker text-white ms-auto">
								<i class="bx bx-briefcase"></i>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-12 col-lg-3">
				<div class="card radius-10 border-start border-0 border-3 border-danger">
					<div class="card-body">
						<div class="d-flex align-items-center">
							<div>
								<p class="mb-0 text-secondary">Garages & Shops</p>
								<h4 class="my-1 text-danger">{{ $stats['garages_shops'] ?? 0 }}</h4>
							</div>
							<div class="widgets-icons-2 rounded-circle bg-gradient-bloody text-white ms-auto">
								<i class="bx bx-wrench"></i>
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