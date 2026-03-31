@extends('layouts.insurance')
@section('content')
<!--start page wrapper -->
				<div class="container">
					<div class="main-body">
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
												<!-- <button class="btn btn-danger radius-30">Delete Account</button> -->
											</div>
										</div>
										
									</div>
								</div>
							</div>
							<div class="col-lg-8">
								<div class="card">
									<div class="card-body">
										<h4 class="text-center mb-4 mt-1">Account Details</h4>
										<div class="row mb-3">
											<div class="col-sm-3">
												<h6 class="mb-0">Name</h6>
											</div>
											<div class="col-sm-9 text-secondary">
												<input type="text" class="form-control" value="{{auth()->user()->name}}" />
											</div>
										</div>
										<div class="row mb-3">
											<div class="col-sm-3">
												<h6 class="mb-0">Email</h6>
											</div>
											<div class="col-sm-9 text-secondary">
												<input type="text" class="form-control" value="{{auth()->user()->email}}" />
											</div>
										</div>
										<div class="row mb-3">
											<div class="col-sm-3">
												<h6 class="mb-0">Phone</h6>
											</div>
											<div class="col-sm-9 text-secondary">
												<input type="text" class="form-control" value="{{auth()->user()->phone_number}}" />
											</div>
										</div>
										<div class="row mb-3">
											<div class="col-sm-3">
												<h6 class="mb-0">Password</h6>
											</div>
											<div class="col-sm-9 text-secondary">
												<input type="text" class="form-control" value="********" />
											</div>
										</div>
										<div class="row">
											<div class="col-sm-3"></div>
											<div class="col-sm-9 text-secondary">
												<input type="button" class="btn btn-primary px-4 radius-30" value="Save Changes" />
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
