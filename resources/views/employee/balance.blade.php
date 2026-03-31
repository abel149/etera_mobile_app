@extends('layouts.employee')
@section('content')
<div class="row row-cols-12 row-cols-lg-12 row-cols-xl-12">
	<div class="col mx-auto">
	<div class=" my-5 my-lg-0 shadow-none ">
	<div class="row">
	<div class="col col-12 col-md-8 col-lg-8 col-xl-6 mx-auto">
		<div class="card radius-10 shadow">
			<div class="card-body">
								<div class="text-center">
									<h6 class="mb-0 pt-5 text-secondary">Balance</h6>
									<h1 class="my-1 pb-4">{{auth()->user()->balance}} <span class="text-purple">ETB</span></h1>
									<button @if(auth()->user()->balance == 0) disabled @endif
 type="button" class="btn btn-primary radius-30" data-bs-toggle="modal" data-bs-target="#withdraw-some">Send Withdrawal Request</button> &nbsp;
								</div>
			</div>
		</div>
	</div>
	</div>

		<div class=" ">
								<ul class="list-group list-group-flush radius-10">
                @foreach(auth()->user()->MyWithdrawalRequests() as $request)
									<li class="list-group-item d-flex align-items-center radius-10 mb-2 border">
										<div class="d-flex align-items-center">
											<div class="flex-grow-1 ms-2">
												<div>
													<h6 class="mb-0 font-20">{{$request->amount}} ETB</h6>
													<p class="mb-0 font-13 text-secondary">Under review</p>
												</div>
											</div>
										</div>
										<div class="ms-auto badge rounded-pill bg-warning">{{$request->status}}</div>
									</li>
                @endforeach
								</ul>
							</div>
						</div>
					</div>
				</div>
				<!--end row-->

    <form action="{{route('withdraw.store')}}" method="POST">
    @csrf
<!-- Withdraw Some Modal -->
<div class="modal fade" id="withdraw-some" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">

		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Withdraw Money</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<label class="form-label">Enter the amount to withdraw</label>
        <input type="text" class="form-control" name="amount" placeholder="">
				<label class="form-label">Select the bank you want to withdraw to</label>
				<select name="bank_name" class="form-select" data-placeholder="Choose Bank...">
                    <option>CBE</option>
                    <option>Abyssiniya</option>
                    <option>Awash</option>
                    <option>Dashen</option>
                    <option>Enat</option>
                    <option>Wegagen</option>
                    <option>Tsedey</option>
                </select>


				<label class="form-label">Enter the account number of the bank</label>
        <input type="text" class="form-control" name="account_number" placeholder="">
            </div>

			<div class="modal-footer">
				<button type="button" class="btn btn-outline-secondary radius-30" data-bs-dismiss="modal">Cancel</button>
				<button type="submit" class="btn btn-primary radius-30" data-bs-dismiss="modal" onclick="notification('Withdrawal Request Sent Successfully')">Withdraw</button>
			</div>
		</div>
	</div>
</div>
<!-- Withdraw Some Modal -->

  </form>
<!-- Withdraw All Modal -->
<div class="modal fade" id="withdraw-all" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Withdraw All</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<label class="form-label">Select the bank you want to withdraw to</label>
				<select class="form-select" id="multiple-select-clear-field" data-placeholder="Add Brands...">
                    <option>CBE</option>
                    <option>Abyssiniya</option>
                    <option>Awash</option>
                    <option>Dashen</option>
                    <option>Enat</option>
                    <option>Wegagen</option>
                    <option>Tsedey</option>
                </select>
            </div>
			<div class="modal-footer">
				<button type="button" class="btn btn-outline-secondary radius-30" data-bs-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-success radius-30" data-bs-dismiss="modal" onclick="notification('Withdrawal Request Sent Successfully')">Withdraw</button>
			</div>
		</div>
	</div>
</div>
<!-- End Withdraw All Modal -->
@endsection
