@extends('layouts.employee')
@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
	<div class="page-content">
		
		<div class="row row-cols-1 row-cols-md-2 row-cols-xl-2">
			<div class="col">
				<div class="card radius-10">
					<div class="card-body">
						<div class="text-center" style="min-height: 200px;">
							<div style="margin:auto 0;">
							<h6 class="mb-0 pt-5 text-secondary">Balance</h6>
							<h1 class="my-1 pb-4">{{auth()->user()->balance}} <span class="text-purple">ETB</span></h1>
							<button @if(auth()->user()->balance == 0) disabled @endif type="button" class="btn btn-primary radius-30" data-bs-toggle="modal" data-bs-target="#withdraw-all">Withdraw</button>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col">
				<div class="card radius-10">
					<div class="card-body">
						<div class="d-flex align-items-center justify-content-between"  style="min-height: 200px;">
							<div>
								<h6 class="mb-0">Total Files</h6>
								<h2 class="mb-0">{{auth()->user()->proformaSelections->count()}}</h2>
							</div>
							<div id="chart2"></div>
						</div>
					</div>
				</div>
			</div>
			<div class="col">
				<div class="card radius-10">
					<div class="card-body">						
						<div class="d-flex align-items-center justify-content-between"  style="min-height: 200px;">
							<div>
								<h6 class="mb-0">My Files</h6>
								<h2 class="mb-0">23</h2>
							</div>
							<div id="chart3"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!--end row-->
		

	</div>
</div>
<!--end page wrapper -->

@endsection
