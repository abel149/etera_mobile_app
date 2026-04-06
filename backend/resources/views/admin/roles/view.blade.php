@extends('layouts.admin')
@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
	<div class="page-content">
		<h3 class="">Roles List</h3>
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
												<a href="{{route('operators.role.create')}}" type="button" class="btn btn-primary radius-30 "><i class="bx bx-plus me-0"></i> Add Role
										</a>
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
								<th>Name</th>
								<th>Rank</th>
								<th>Status Label</th>
								<th>Users</th>
								<th class="w-25 text-end"></th>
							</tr>
						</thead>
						<tbody>
            @foreach ($levels as $level)
							<tr>
								<td>{{$level->name}}</td>
								<td>{{$level->rank}}</td>
								<td>{{$level->status_label}}</td>
								<td>{{$level->users()->count() }} user are assigned</td>
								<td class="text-end"><button type="button" class="btn radius-10 p-1"><i class="bx bx-edit me-0"></i>
										</button>
										<button type="button" class="btn radius-10 p-1 text-danger" data-bs-toggle="modal" data-bs-target="#singleDelete"><i class="bx bx-trash me-0"></i>
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
@endsection
