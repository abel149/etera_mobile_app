@extends('layouts.insurance')
@section('content')
<!--start page wrapper -->
				<div class="container">
					<div class="main-body">

						@if(session('success'))
							<div class="alert alert-success alert-dismissible fade show" role="alert">
								{{ session('success') }}
								<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
							</div>
						@endif
						@if(session('error'))
							<div class="alert alert-danger alert-dismissible fade show" role="alert">
								{{ session('error') }}
								<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
							</div>
						@endif

						<div class="row">
							<div class="col-lg-4">
								<div class="card">
									<div class="card-body">
										<div class="d-flex flex-column align-items-center text-center">
											<img src="{{asset('assets/images/avatars/avatar-9.jpg')}}" alt="Admin" class="rounded-circle p-1 bg-primary mt-2" width="150">
											<div class="mt-3">
												<h4>{{auth()->user()->name}}</h4>
												<p class="text-secondary mb-1">{{ucfirst(auth()->user()->role)}}</p>
												<p class="text-muted font-size-sm">Date Registered: {{auth()->user()->created_at->diffForHumans()}}</p>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-lg-8">
								<div class="card">
									<div class="card-body">
										<h4 class="text-center mb-4 mt-1">Account Details</h4>

										<form action="{{ route('user.profile.update') }}" method="POST">
											@csrf
											@method('PUT')

											<div class="row mb-3">
												<div class="col-sm-3">
													<h6 class="mb-0">Name</h6>
												</div>
												<div class="col-sm-9 text-secondary">
													<input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', auth()->user()->name) }}" />
													@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
												</div>
											</div>

											<div class="row mb-3">
												<div class="col-sm-3">
													<h6 class="mb-0">Email</h6>
												</div>
												<div class="col-sm-9 text-secondary">
													<input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', filter_var(auth()->user()->email, FILTER_VALIDATE_EMAIL) ? auth()->user()->email : '') }}" />
													@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
												</div>
											</div>

											<div class="row mb-3">
												<div class="col-sm-3">
													<h6 class="mb-0">Phone</h6>
												</div>
												<div class="col-sm-9 text-secondary">
													<input type="text" name="phone_number" class="form-control @error('phone_number') is-invalid @enderror" value="{{ old('phone_number', auth()->user()->phone_number) }}" />
													@error('phone_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
												</div>
											</div>

											<hr class="my-4">
											<h6 class="mb-3 text-muted">Change Password <small class="fw-normal">(leave blank to keep current)</small></h6>

											<div class="row mb-3">
												<div class="col-sm-3">
													<h6 class="mb-0">Current Password</h6>
												</div>
												<div class="col-sm-9 text-secondary">
													<input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" autocomplete="current-password" />
													@error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
												</div>
											</div>

											<div class="row mb-3">
												<div class="col-sm-3">
													<h6 class="mb-0">New Password</h6>
												</div>
												<div class="col-sm-9 text-secondary">
													<input type="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" />
													@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
												</div>
											</div>

											<div class="row mb-4">
												<div class="col-sm-3">
													<h6 class="mb-0">Confirm Password</h6>
												</div>
												<div class="col-sm-9 text-secondary">
													<input type="password" name="password_confirmation" class="form-control" autocomplete="new-password" />
												</div>
											</div>

											<div class="row">
												<div class="col-sm-3"></div>
												<div class="col-sm-9 text-secondary">
													<button type="submit" class="btn btn-primary px-4 radius-30">Save Changes</button>
												</div>
											</div>

										</form>

									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

		<!--end page wrapper -->
@endsection
