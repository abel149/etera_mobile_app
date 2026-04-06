@extends('layouts.admin')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center justify-content-between">
                            <h4 class="mb-0"><i class="bx bx-task me-2"></i>Proforma Statuses</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filters (all client-side) -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select id="filterStatus" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="published">Published</option>
                                    <option value="closed">Closed</option>
                                    <option value="completed">Completed</option>
                                    <option value="sent_to_owner">Sent to Owner</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Processed By</label>
                                <select id="filterAdmin" class="form-select">
                                    <option value="">All Admins</option>
                                    @foreach($admins as $admin)
                                        <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Search</label>
                                <div class="position-relative">
                                    <input type="text" id="liveSearch" class="form-control" placeholder="License plate or phone number" autocomplete="off">
                                    <i class="bx bx-search position-absolute" style="right:12px; top:50%; transform:translateY(-50%); color:#aaa;"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card border-0 bg-light-primary">
                                    <div class="card-body text-center py-3">
                                        <h3 class="mb-0" id="statTotal">0</h3>
                                        <small class="text-muted">Total Assigned</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-0 bg-light-info">
                                    <div class="card-body text-center py-3">
                                        <h3 class="mb-0" id="statPublished">0</h3>
                                        <small class="text-muted">Published</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-0 bg-light-success">
                                    <div class="card-body text-center py-3">
                                        <h3 class="mb-0" id="statCompleted">0</h3>
                                        <small class="text-muted">Completed</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-0 bg-light-warning">
                                    <div class="card-body text-center py-3">
                                        <h3 class="mb-0" id="statClosed">0</h3>
                                        <small class="text-muted">Closed</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>File Number</th>
                                        <th>Customer</th>
                                        <th>Brand</th>
                                        <th>Status</th>
                                        <th>Processed By</th>
                                        <th>Floated At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($proformas as $index => $proforma)
                                    <tr class="proforma-row" data-plate="{{ strtolower($proforma->license_plate_number ?? '') }}" data-phone="{{ strtolower($proforma->customer_phone_number ?? '') }}" data-status="{{ $proforma->status }}" data-admin="{{ $proforma->processed_by }}">
                                        <td class="row-number">{{ $index + 1 }}</td>
                                        <td><strong>{{ $proforma->file_number }}</strong></td>
                                        <td>{{ $proforma->customer_name }}</td>
                                        <td>{{ $proforma->brand?->name ?? 'N/A' }}</td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'pending' => 'warning',
                                                    'published' => 'info',
                                                    'closed' => 'danger',
                                                    'completed' => 'success',
                                                    'sent_to_owner' => 'primary',
                                                ];
                                                $color = $statusColors[$proforma->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $color }}">{{ ucfirst(str_replace('_', ' ', $proforma->status)) }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-dark">{{ $proforma->processedBy?->name ?? 'Unassigned' }}</span>
                                        </td>
                                        <td>{{ $proforma->updated_at?->format('d M Y, h:i A') }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="showTimeline({{ $proforma->id }})">
                                                <i class="bx bx-show"></i> View
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                    <tr id="emptyRow" style="display:none;">
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="bx bx-info-circle me-1"></i>No proformas found matching the filters.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Timeline Modal -->
<div class="modal fade" id="timelineModal" tabindex="-1" aria-labelledby="timelineModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border:none; border-radius:16px; overflow:hidden;">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border:none;">
                <h5 class="modal-title text-white" id="timelineModalLabel">
                    <i class="bx bx-history me-2"></i>Proforma Lifecycle
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="timelineBody" style="background: #fafbff;">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading timeline...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline-container {
    position: relative;
    padding: 0;
}
.timeline-header-card {
    background: linear-gradient(135deg, #f8f9ff 0%, #eef1ff 100%);
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 24px;
    border: 1px solid #e2e6f5;
}
.timeline-header-card h6 {
    margin: 0;
    color: #333;
    font-weight: 600;
}
.timeline-header-card .text-muted {
    font-size: 0.85rem;
}
.timeline-track {
    position: relative;
    padding-left: 40px;
}
.timeline-track::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 3px;
    background: linear-gradient(to bottom, #667eea, #764ba2, #e0e0e0);
    border-radius: 3px;
}
.timeline-item {
    position: relative;
    margin-bottom: 20px;
    animation: fadeInUp 0.4s ease forwards;
    opacity: 0;
}
.timeline-item:nth-child(1) { animation-delay: 0.05s; }
.timeline-item:nth-child(2) { animation-delay: 0.1s; }
.timeline-item:nth-child(3) { animation-delay: 0.15s; }
.timeline-item:nth-child(4) { animation-delay: 0.2s; }
.timeline-item:nth-child(5) { animation-delay: 0.25s; }
.timeline-item:nth-child(6) { animation-delay: 0.3s; }
.timeline-item:nth-child(7) { animation-delay: 0.35s; }
.timeline-item:nth-child(8) { animation-delay: 0.4s; }

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.timeline-dot {
    position: absolute;
    left: -33px;
    top: 8px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 14px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    z-index: 2;
}
.timeline-card {
    background: #fff;
    border-radius: 10px;
    padding: 14px 18px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    border: 1px solid #eee;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.timeline-card:hover {
    transform: translateX(4px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
}
.timeline-card.current {
    border-left: 4px solid #ffc107;
    background: linear-gradient(135deg, #fffdf0 0%, #fff9e6 100%);
}
.timeline-action {
    font-weight: 600;
    font-size: 0.95rem;
    color: #333;
}
.timeline-meta {
    display: flex;
    gap: 16px;
    margin-top: 4px;
    font-size: 0.82rem;
    color: #888;
}
.timeline-meta i {
    font-size: 12px;
    margin-right: 3px;
}
.timeline-details {
    margin-top: 6px;
    font-size: 0.82rem;
    color: #666;
    padding: 6px 10px;
    background: #f7f8fc;
    border-radius: 6px;
}
</style>

<script>
function showTimeline(proformaId) {
    const modal = new bootstrap.Modal(document.getElementById('timelineModal'));
    const body = document.getElementById('timelineBody');
    
    body.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading timeline...</p>
        </div>`;
    
    modal.show();

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    fetch('/admin/proforma/' + proformaId + '/timeline', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        let html = `
            <div class="timeline-header-card">
                <h6><i class="bx bx-file me-2"></i>File #${data.file_number}</h6>
                <div class="text-muted mt-1">
                    <span class="me-3"><i class="bx bx-user me-1"></i>${data.customer_name}</span>
                    <span class="me-3"><i class="bx bx-car me-1"></i>${data.brand} ${data.model} (${data.year})</span>
                </div>
            </div>
            <div class="timeline-container">
                <div class="timeline-track">`;
        
        data.timeline.forEach(item => {
            const isCurrent = item.is_current ? 'current' : '';
            html += `
                <div class="timeline-item">
                    <div class="timeline-dot" style="background: ${item.color};">
                        <i class="bx ${item.icon}"></i>
                    </div>
                    <div class="timeline-card ${isCurrent}">
                        <div class="timeline-action">${item.action}</div>
                        <div class="timeline-meta">
                            <span><i class="bx bx-calendar"></i>${item.date || 'N/A'}</span>
                            <span><i class="bx bx-user"></i>${item.user}</span>
                        </div>
                        ${item.details ? `<div class="timeline-details">${item.details}</div>` : ''}
                    </div>
                </div>`;
        });

        html += '</div></div>';
        body.innerHTML = html;
    })
    .catch(err => {
        body.innerHTML = `
            <div class="text-center py-5 text-danger">
                <i class="bx bx-error-circle" style="font-size:3rem;"></i>
                <p class="mt-2">Failed to load timeline. Please try again.</p>
            </div>`;
        console.error('Timeline error:', err);
    });
}

// All-client-side filtering: status dropdown, admin dropdown, search
(function() {
    const statusFilter = document.getElementById('filterStatus');
    const adminFilter = document.getElementById('filterAdmin');
    const searchInput = document.getElementById('liveSearch');
    const rows = document.querySelectorAll('tr.proforma-row');
    const emptyRow = document.getElementById('emptyRow');
    const statTotal = document.getElementById('statTotal');
    const statPublished = document.getElementById('statPublished');
    const statCompleted = document.getElementById('statCompleted');
    const statClosed = document.getElementById('statClosed');

    function filterRows() {
        const statusVal = statusFilter ? statusFilter.value : '';
        const adminVal = adminFilter ? adminFilter.value : '';
        const searchTerm = (searchInput ? searchInput.value : '').toLowerCase().trim();

        let total = 0, published = 0, completed = 0, closed = 0;
        let visibleIndex = 1;

        rows.forEach(row => {
            const rowStatus = row.getAttribute('data-status') || '';
            const rowAdmin = row.getAttribute('data-admin') || '';
            const rowPlate = row.getAttribute('data-plate') || '';
            const rowPhone = row.getAttribute('data-phone') || '';

            // Check all three filters
            const matchStatus = !statusVal || rowStatus === statusVal;
            const matchAdmin = !adminVal || rowAdmin === adminVal;
            const matchSearch = !searchTerm || rowPlate.includes(searchTerm) || rowPhone.includes(searchTerm);

            const visible = matchStatus && matchAdmin && matchSearch;
            row.style.display = visible ? '' : 'none';

            if (visible) {
                // Update row number
                const numCell = row.querySelector('.row-number');
                if (numCell) numCell.textContent = visibleIndex;
                visibleIndex++;

                total++;
                if (rowStatus === 'published') published++;
                else if (rowStatus === 'completed') completed++;
                else if (rowStatus === 'closed') closed++;
            }
        });

        // Update stat counters
        statTotal.textContent = total;
        statPublished.textContent = published;
        statCompleted.textContent = completed;
        statClosed.textContent = closed;

        // Show/hide empty message
        if (emptyRow) emptyRow.style.display = total === 0 ? '' : 'none';
    }

    // Bind events
    if (statusFilter) statusFilter.addEventListener('change', filterRows);
    if (adminFilter) adminFilter.addEventListener('change', filterRows);
    if (searchInput) searchInput.addEventListener('input', filterRows);

    // Run once on page load to set initial stats
    filterRows();
})();
</script>
@endsection
