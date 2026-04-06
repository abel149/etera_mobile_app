@extends('layouts.marketer')
@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
	<div class="page-content">
		@php
    $i=1;
    $myUsers = auth()->user()->myRegistrations;
    $myTotalUsers = $myUsers ? $myUsers->count() : 0;
    $myTotalInsurances = $myUsers ? $myUsers->where('role','insurance')->count() : 0;
    $myTotalShops = $myUsers ? $myUsers->where('role','shop')->count() : 0;
    @endphp
		<div class="row row-cols-2 row-cols-md-3 row-cols-xl-4">
			<div class="col">
				<div class="card radius-10">
					<div class="card-body">
						<div class="d-flex align-items-center justify-content-between"  style="min-height: 100px;">
							<div>
								<h6 class="mb-0">Total Users Registered by You</h6>
								<h2 class="mb-0">{{$myTotalUsers}}</h2>
							</div>
							<div id="chart1"></div>
						</div>
					</div>
				</div>
			</div>
			<div class="col">
				<div class="card radius-10">
					<div class="card-body">						
						<div class="d-flex align-items-center justify-content-between"  style="min-height: 100px;">
							<div>
								<h6 class="mb-0">Insurances</h6>
								<h2 class="mb-0">{{$myTotalInsurances}}</h2>
							</div>
							<div id="chart3"></div>
						</div>
					</div>
				</div>
			</div>
			<div class="col">
				<div class="card radius-10">
					<div class="card-body">						
						<div class="d-flex align-items-center justify-content-between"  style="min-height: 100px;">
							<div>
								<h6 class="mb-0">Garages</h6>
								<h2 class="mb-0">{{$myUsers ? $myUsers->where('role','garage')->count() : 0}}</h2>
							</div>
							<div id="chart4"></div>
						</div>
					</div>
				</div>
			</div>
			<div class="col">
				<div class="card radius-10">
					<div class="card-body">						
						<div class="d-flex align-items-center justify-content-between"  style="min-height: 100px;">
							<div>
								<h6 class="mb-0">Shops</h6>
								<h2 class="mb-0">{{$myTotalShops}}</h2>
							</div>
							<div id="chart5"></div>
						</div>
					</div>
				</div>
			</div>
		</div>


		<!--end row-->
		
		<!--end row-->
		<div class="card radius-10">
			<div class="card-body">
				<h5 class="">Top Last Registered Users</h5>
				<div class="table-responsive lead-table">
					<table class="table mb-0 align-middle">
						<thead class="table-light">
							<tr>
								<th>#</th>
								<th>Name</th>
								<th>Role</th>
								<th>Phone Number</th>
								<th>Register Date</th>
							</tr>
						</thead>
						<tbody>
            @if($myUsers && $myUsers->count() > 0)
              @foreach($myUsers as $user)
								<tr>
									<td>{{$i++}}</td>
									<td>
										<div class="d-flex align-items-center">
											<div>
												<h6 class="mb-0 font-14">{{$user->name}}</h6>
												<p class="mb-0 font-13 text-secondary">Email</p>
											</div>
										</div>
									</td>
									<td>{{ucfirst($user->role)}}</td>
									<td>{{$user->phone_number}}</td>
									<td>{{$user->created_at->format('Y-m-d')}} - {{$user->created_at->diffForHumans()}}</td>
								</tr>
                @endforeach
            @else
              <tr>
                <td colspan="5" class="text-center text-muted">
                  <i class="bx bx-info-circle me-2"></i>
                  No users registered yet. Start by registering your first user!
                </td>
              </tr>
            @endif
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
<!--end page wrapper -->

@endsection
