@extends('layouts.admin')
@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
	<div class="page-content">
		<h3 class="">Withdraw Requests</h3>
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
												<input type="text" class="form-control ps-5" placeholder="Search Request..."> <span class="position-absolute top-50 product-show translate-middle-y"><i class="bx bx-search"></i></span>
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
													<li><a class="dropdown-item" href="#">Status</a></li>
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
								<th>User</th>
								<th>Role</th>
								<th>Balance</th>
								<th>Status</th>
								<th class="text-end"></th>
							</tr>
						</thead>
						<tbody>
            @foreach ($withdrawals as $withdraw)
							<tr>
								<td>
									<div class="d-flex align-items-center">
											<h6 class="mb-0 font-14">{{$withdraw->owner->name}}</h6>
									</div>
								</td>
								<td>{{$withdraw->owner->role}}</td>
								<td>{{$withdraw->amount}} Br</td>

<td>
    @if($withdraw->status === 'pending')
        <div class="badge rounded-pill bg-warning w-100">Pending</div>
    @elseif($withdraw->status === 'approved')
        <div class="badge rounded-pill bg-success w-100">Approved</div>
    @elseif($withdraw->status === 'rejected')
        <div class="badge rounded-pill bg-danger w-100">Rejected</div>
    @endif
</td>

								<td class="text-end">	
 <button onclick="handleStatusChange(`{{$withdraw->id}}`)" type="button" 
            class="btn radius-10 p-1"
            data-id="{{ $withdraw->id }}"
            data-name="{{ $withdraw->owner->name }}"
            data-amount="{{ $withdraw->amount }}"
            data-bs-toggle="modal"
            data-bs-target="#view">
        <i class="bx bx-show me-0"></i>
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

<!-- View Modal -->
<div class="modal fade" id="view" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Balance Withdrawal</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<input type="text" class="form-control" id="input1" placeholder="TXN Number...">
			</div>

<div class="modal-footer">
	{{-- action="{{ route('withdraw.approve', $withdraw->id) }}"  --}}
    <form method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" name="_id" id="txn_number" value="">
        <button type="submit" class="btn btn-success radius-30">Approve</button>
    </form>
    <form method="POST">
		{{-- action="{{ route('withdraw.reject', $withdraw->id) }}"  --}}
        @csrf
        @method('PUT')
        <input type="hidden" name="_id" id="txn_number" value="">
        <button type="submit" class="btn btn-danger radius-30">Reject</button>
    </form>
</div>
		</div>
	</div>
</div>

<script>
    function handleStatusChange(id) {
        const inputs = document.querySelectorAll('#txn_number');
        
        inputs.forEach(input => input.value = id);
    }
</script>
@endsection
