@extends('layouts.admin')
@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
	<div class="page-content">
		<!-- Stats Section (Collapsible) -->
		<div class="card radius-10 mb-3">
			<div class="card-header d-flex align-items-center justify-content-between py-2" style="cursor:pointer;" data-bs-toggle="collapse" data-bs-target="#statsRowCollapse" aria-expanded="false">
				<h6 class="mb-0"><i class="bx bx-bar-chart-alt-2 me-2"></i>Dashboard Statistics</h6>
				<i class="bx bx-chevron-down fs-4 stats-chevron" style="transition:transform 0.3s ease;"></i>
			</div>
			<div class="collapse" id="statsRowCollapse">
				<div class="card-body pb-1">
					<div class="row">
					<div class="col-12 col-md-4 col-sm-6 col-lg-4">
						<div class="card radius-10">
							<div class="card-body">
								<div class="d-flex align-items-center justify-content-between">
									<div>
										<p class="mb-0">Total Proformas from Insurances</p>
										@if(auth()->user()->is_superadmin == 1)
										<h5 class="mb-0" id="stat-insurance-total">{{\App\Models\Proforma::fromInsurances()->count()}}</h5>
										@else
										<h5 class="mb-0" id="stat-insurance-total">{{\App\Models\Proforma::fromInsurances()->where('processed_by', auth()->id())->count()}}</h5>
										@endif
									</div>
									<div id="chart4"></div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-12 col-md-4 col-sm-6 col-lg-4">
						<div class="card radius-10">
							<div class="card-body">
								<div class="d-flex align-items-center justify-content-between">
									<div>
										<p class="mb-0">Total Completed Proformas from Insurances</p>
										@if(auth()->user()->is_superadmin == 1)
										<h5 class="mb-0" id="stat-insurance-completed">{{\App\Models\Proforma::fromInsurances()->where('status', 'completed')->count()}}</h5>
										@else
										<h5 class="mb-0" id="stat-insurance-completed">{{\App\Models\Proforma::fromInsurances()->where('processed_by', auth()->id())->where('status', 'completed')->count()}}</h5>
										@endif
									</div>
									<div id="chart4"></div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-12 col-md-4 col-sm-6 col-lg-4">
						<div class="card radius-10">
							<div class="card-body">
								<div class="d-flex align-items-center justify-content-between">
									<div>
										<p class="mb-0">Total Proformas from Others</p>
										@if(auth()->user()->is_superadmin == 1)
										<h5 class="mb-0" id="stat-others-total">{{\App\Models\Proforma::fromOthers()->count()}}</h5>
										@else
										<h5 class="mb-0" id="stat-others-total">{{\App\Models\Proforma::fromOthers()->where('processed_by', auth()->id())->count()}}</h5>
										@endif
									</div>
									<div id="chart4"></div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-12 col-md-4 col-sm-6 col-lg-4">
						<div class="card radius-10">
							<div class="card-body">
								<div class="d-flex align-items-center justify-content-between">
									<div>
										<p class="mb-0">Total Completed Proformas from Others</p>
										@if(auth()->user()->is_superadmin == 1)
										<h5 class="mb-0" id="stat-others-completed">{{\App\Models\Proforma::fromOthers()->where('status', 'completed')->count()}}</h5>
										@else
										<h5 class="mb-0" id="stat-others-completed">{{\App\Models\Proforma::fromOthers()->where('processed_by', auth()->id())->where('status', 'completed')->count()}}</h5>
										@endif
									</div>
									<div id="chart4"></div>
								</div>
							</div>
						</div>
					</div>
					
					@if(auth()->user()->is_superadmin == 1)
					<div class="col-12 col-md-4 col-sm-6 col-lg-4">
						<div class="card radius-10">
							<div class="card-body">
								<div class="d-flex align-items-center justify-content-between">
									<div>
										<p class="mb-0">Total Users</p>
										<h5 class="mb-0">{{\App\Models\User::count()}}</h5>
									</div>
									<div id="chart1"></div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-12 col-md-4 col-sm-6 col-lg-4">
						<div class="card radius-10">
							<div class="card-body">
								<div class="d-flex align-items-center justify-content-between">
									<div>
										<p class="mb-0">Total Admins</p>
										<h5 class="mb-0">{{\App\Models\User::where('role','admin')->count()}}</h5>
									</div>
									<div id="chart2"></div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-12 col-md-4 col-sm-6 col-lg-4">
						<div class="card radius-10">
							<div class="card-body">
								<div class="d-flex align-items-center justify-content-between">
									<div>
										<p class="mb-0">Total Operators</p>
										<h5 class="mb-0">{{\App\Models\User::where('role','employee')->count()}}</h5>
									</div>
									<div id="chart1"></div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-12 col-md-4 col-sm-6 col-lg-4">
						<div class="card radius-10">
							<div class="card-body">
								<div class="d-flex align-items-center justify-content-between">
									<div>
										<p class="mb-0">Total Insurances</p>
										<h5 class="mb-0">{{\App\Models\User::where('role','insurance')->count()}}</h5>
									</div>
									<div id="chart2"></div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-12 col-md-4 col-sm-6 col-lg-4">
						<div class="card radius-10">
							<div class="card-body">
								<div class="d-flex align-items-center justify-content-between">
									<div>
										<p class="mb-0">Total Customers</p>
										<h5 class="mb-0">{{\App\Models\User::where('role','others')->count()}}</h5>
									</div>
									<div id="chart1"></div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-12 col-md-4 col-sm-6 col-lg-4">
						<div class="card radius-10">
							<div class="card-body">
								<div class="d-flex align-items-center justify-content-between">
									<div>
										<p class="mb-0">Total Marketers</p>
										<h5 class="mb-0">{{\App\Models\User::where('role','marketer')->count()}}</h5>
									</div>
									<div id="chart2"></div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-12 col-md-4 col-sm-6 col-lg-4">
						<div class="card radius-10">
							<div class="card-body">
								<div class="d-flex align-items-center justify-content-between">
									<div>
										<p class="mb-0">Total Garages</p>
										<h5 class="mb-0">{{\App\Models\User::where('role','garage')->count()}}</h5>
									</div>
									<div id="chart3"></div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-12 col-md-4 col-sm-6 col-lg-4">
						<div class="card radius-10">
							<div class="card-body">
								<div class="d-flex align-items-center justify-content-between">
									<div>
										<p class="mb-0">Total Sparepart Shops</p>
										<h5 class="mb-0">{{\App\Models\User::where('role','shop')->count()}}</h5>
									</div>
									<div id="chart4"></div>
								</div>
							</div>
						</div>
					</div>
					@endif
				</div>
				</div>
			</div>
		</div>
		<!--end stats row-->
		@if(auth()->user()->is_superadmin == 1)
		<!-- Create Admin Card -->
		<div class="card radius-10 mb-3">
			<div class="card-header d-flex align-items-center justify-content-between">
				<h5 class="mb-0"><i class="bx bx-user-plus me-2"></i>Create New Admin</h5>
				<button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#createAdminCollapse">
					<i class="bx bx-plus me-1"></i>Expand
				</button>
			</div>
			<div class="collapse" id="createAdminCollapse">
				<div class="card-body">
					<form class="row g-3" action="{{ url('/admin/create-admin') }}" method="POST">
						@csrf
						<div class="col-md-4">
							<label for="admin-name" class="form-label">Name <span class="text-danger">*</span></label>
							<input type="text" name="name" class="form-control" id="admin-name" placeholder="Admin Name" required value="{{ old('name') }}">
						</div>
						<div class="col-md-4">
							<label for="admin-phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
							<input type="text" name="phone_number" class="form-control" id="admin-phone" placeholder="09..." required value="{{ old('phone_number') }}">
						</div>
						<div class="col-md-4">
							<label for="admin-email" class="form-label">Email <small class="text-muted">(optional)</small></label>
							<input type="email" name="email" class="form-control" id="admin-email" placeholder="Email..." value="{{ old('email') }}">
						</div>
						<div class="col-md-12 d-flex justify-content-end">
							<button type="submit" class="btn btn-primary radius-30 px-4">
								<i class="bx bx-user-plus me-1"></i> Create Admin
							</button>
						</div>
					</form>
					<p class="text-muted mt-2 mb-0"><small>Default password: <code>123456</code></small></p>
				</div>
			</div>
		</div>

		@if(session('admin_created'))
		<script>
		document.addEventListener('DOMContentLoaded', function() {
			Swal.fire({
				icon: 'success',
				title: 'Admin Created!',
				text: @json(session('admin_created')),
				confirmButtonColor: '#0d6efd'
			});
		});
		</script>
		@endif

		@if(session('admin_error'))
		<script>
		document.addEventListener('DOMContentLoaded', function() {
			Swal.fire({
				icon: 'error',
				title: 'Error',
				text: @json(session('admin_error')),
				confirmButtonColor: '#dc3545'
			});
		});
		</script>
		@endif

		@if($errors->any())
		<script>
		document.addEventListener('DOMContentLoaded', function() {
			Swal.fire({
				icon: 'error',
				title: 'Validation Error',
				text: @json($errors->first()),
				confirmButtonColor: '#dc3545'
			});
		});
		</script>
		@endif

		@endif

		<!--end row-->
		<div class="card radius-10">
			<div class="card-body">
				<div class="table-responsive lead-table">
					<table class="table mb-0 align-middle">
						<thead class="table-light">
							<tr>
								<th>File #</th>
								<th>From</th>
								<th>Customer Name</th>
								<th>Garage Proforma</th>
								<th>Sparepart Proforma</th>
								<th>Status</th>
								<th>
									Remaining Time
									<i class="bx bx-info-circle ms-1" 
									   data-bs-toggle="tooltip" 
									   data-bs-placement="top" 
									   title="Shows remaining time for Etera-Chereta proformas (HH:MM format)"></i>
								</th>
								<th>Created At</th>
							</tr>
						</thead>
									@php
										$allProformas = \App\Models\Proforma::with('poster')->whereHas('poster')->where('processed_by', auth()->id())->orderBy('created_at', 'desc')->get();
									@endphp
						<tbody id="proformaTableBody">
						@foreach($allProformas as $proforma)
							@php try { @endphp
							<tr>
								<td>{{$proforma->file_number ?? 'N/A'}}</td>
                        @php
                          $label = $proforma->poster ? ($proforma->poster->role == 'business_owner' ? 'Business Owner' : ucfirst($proforma->poster->role)) : 'Unknown';
                        @endphp
								<td>{{$label}}</td>
								<td>
									<div class="d-flex align-items-center">
											<h6 class="mb-0 font-14">{{$proforma->customer_name ?? 'N/A'}}</h6>
									</div>
								</td>
                                @if($proforma->poster && $proforma->isFromOthers())
                                    <td>N/A</td>
                                @else
                					<td>{{$proforma->applicationsFromGarages ? $proforma->applicationsFromGarages->count() : 0}} Garages Applied</td>
                                @endif
                					<td>{{$proforma->applicationsFromShops ? $proforma->applicationsFromShops->count() : 0}} Shops Applied</td>
                						<td>
                                @if($proforma->status == 'completed')
               						<div class="badge rounded-pill bg-secondary w-100">{{ucfirst($proforma->status)}}</div>
               					@elseif($proforma->status == 'published')
                                <div class="badge rounded-pill bg-info w-100">{{ucfirst($proforma->status)}}</div>
                                @elseif($proforma->status == 'pending' || $proforma->status == 'opened')
                                <div class="badge rounded-pill bg-warning w-100">{{($proforma->selected() && $proforma->status == 'pending') ? "File Assigned" : ucfirst($proforma->status)}}</div>
                                @elseif($proforma->status == 'closed')
                                <div class="badge rounded-pill bg-danger w-100">{{ucfirst($proforma->status)}}</div>
                                @elseif($proforma->status == 'rejected')
                                <div class="badge rounded-pill bg-danger w-100">{{ucfirst($proforma->status)}}</div>
                                @endif
                						</td>
								<td>
									@if($proforma->isEteraCheretaMode())
										<span class="badge rounded-pill bg-primary w-100" 
											  data-remaining-time="{{ $proforma->timer_expires_at?->toISOString() }}">
											{{ $proforma->getFormattedRemainingTime() }}
										</span>
									@else
										<span class="text-muted">N/A</span>
									@endif
								</td>
                                <td>{{$proforma->created_at ? $proforma->created_at->format('D M d, Y h:i A') : 'N/A'}}</td>
             					</tr>
							@php } catch (Exception $e) { /* skip */ } @endphp
                            @endforeach
									</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!--end page wrapper -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Track known proforma IDs from initial page load
    let knownIds = new Set();
    document.querySelectorAll('#proformaTableBody tr td:first-child').forEach(td => {
        knownIds.add(td.textContent.trim());
    });

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    function buildStatusBadge(status) {
        const s = (status || 'pending').toLowerCase();
        const map = {
            'completed': 'bg-secondary',
            'published': 'bg-info',
            'pending': 'bg-warning',
            'opened': 'bg-warning',
            'closed': 'bg-danger',
            'rejected': 'bg-danger'
        };
        const cls = map[s] || 'bg-warning';
        return '<div class="badge rounded-pill ' + cls + ' w-100">' + (s === 'pending' ? 'Pending' : s.charAt(0).toUpperCase() + s.slice(1)) + '</div>';
    }

    function pollProformas() {
        fetch('/api/admin/proformas', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.proformas) return;

            // Update stat counters
            const s = data.stats;
            const el1 = document.getElementById('stat-insurance-total');
            const el2 = document.getElementById('stat-insurance-completed');
            const el3 = document.getElementById('stat-others-total');
            const el4 = document.getElementById('stat-others-completed');
            if (el1) el1.textContent = s.insurance_total;
            if (el2) el2.textContent = s.insurance_completed;
            if (el3) el3.textContent = s.others_total;
            if (el4) el4.textContent = s.others_completed;

            // Find new proformas
            const tbody = document.getElementById('proformaTableBody');
            let hasNew = false;

            data.proformas.forEach(p => {
                const fileNum = p.file_number || 'N/A';
                if (knownIds.has(fileNum)) return;

                hasNew = true;
                knownIds.add(fileNum);

                let remainingTimeCell = '<span class="text-muted">N/A</span>';
                if (p.is_etera_chereta && p.timer_expires_at) {
                    remainingTimeCell = '<span class="badge rounded-pill bg-primary w-100" data-remaining-time="' + p.timer_expires_at + '">' + (p.remaining_time || 'N/A') + '</span>';
                }

                const garageCell = p.is_from_others ? 'N/A' : (p.garage_count || 0) + ' Garages Applied';

                const newRow = document.createElement('tr');
                newRow.innerHTML = `
                    <td>${fileNum}</td>
                    <td>${p.from}</td>
                    <td><div class="d-flex align-items-center"><h6 class="mb-0 font-14">${p.customer_name}</h6></div></td>
                    <td>${garageCell}</td>
                    <td>${p.shop_count || 0} Shops Applied</td>
                    <td>${buildStatusBadge(p.status)}</td>
                    <td>${remainingTimeCell}</td>
                    <td>${p.created_at}</td>
                `;

                newRow.style.backgroundColor = '#d4edda';
                newRow.style.transition = 'background-color 3s ease';

                if (tbody) {
                    tbody.insertBefore(newRow, tbody.firstChild);
                    setTimeout(() => { newRow.style.backgroundColor = ''; }, 200);
                }

                // Browser notification
                if ('Notification' in window && Notification.permission === 'granted') {
                    new Notification('New Proforma', {
                        body: 'New proforma from ' + p.from + ' (' + fileNum + ')',
                        icon: '/favicon.ico'
                    });
                }
            });

            if (hasNew) {
                console.log('🔔 New proformas detected and added to table');
            }
        })
        .catch(err => console.error('Polling error:', err));
    }

    // Request notification permission
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }

    // Poll every 30 seconds
    setInterval(pollProformas, 30000);
    console.log('✅ Admin dashboard polling started (every 30s)');
});
</script>

<script>
// Rotate chevron on stats collapse toggle
document.getElementById('statsRowCollapse')?.addEventListener('show.bs.collapse', function() {
    document.querySelector('.stats-chevron').style.transform = 'rotate(180deg)';
});
document.getElementById('statsRowCollapse')?.addEventListener('hide.bs.collapse', function() {
    document.querySelector('.stats-chevron').style.transform = 'rotate(0deg)';
});
</script>

@endsection
