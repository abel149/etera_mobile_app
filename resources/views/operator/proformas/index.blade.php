@extends('layouts.operator')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">My Proformas</h3>
            
            @php
                $user = auth()->user();
                $quota = $user->file_quota ?? 0;
                $activeFiles = $user->proformaSelections()
                    ->whereHas('proforma', function($q) {
                        $q->where('status', '!=', 'returned');
                    })
                    ->where('active', true)
                    ->count();
                $remaining = max(0, $quota - $activeFiles);
            @endphp
            
            @if($remaining > 0)
                <form action="{{ route('operator.proformas.take') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-plus-circle me-1"></i> Take {{ $remaining }} File(s)
                    </button>
                </form>
            @endif
        </div>
        
        <!-- Proforma List -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>File #</th>
                                <th>Customer</th>
                                <th>Car</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($proformas as $proforma)
                            <tr>
                                <td>{{ $proforma->file_number ?? 'N/A' }}</td>
                                <td>{{ $proforma->poster->name ?? 'N/A' }}</td>
                                <td>{{ $proforma->brand->name ?? '' }} {{ $proforma->model ?? '' }} ({{ $proforma->year ?? '' }})</td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'published' => 'info',
                                            'in_progress' => 'primary',
                                            'completed' => 'success',
                                            'closed' => 'secondary',
                                            'rejected' => 'danger',
                                            'returned' => 'dark',
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$proforma->status] ?? 'secondary' }}">
                                        {{ ucfirst(str_replace('_', ' ', $proforma->status)) }}
                                    </span>
                                </td>
                                <td>{{ $proforma->created_at->format('M d, Y') }}</td>
                                <td>
                                                <div class="d-flex gap-2">
                                                    @if(auth()->user()->role === 'operator')
                                                        <a href="{{ route('operator.proforma.show', $proforma->id) }}" class="btn btn-sm btn-outline-primary">
                                                            <i class="bx bx-show me-0"></i>
                                                        </a>
                                                    @elseif(auth()->user()->role === 'admin' || auth()->user()->role === 'superadmin')
                                                        <a href="/admin/post-proforma?proforma_id={{ $proforma->id }}" class="btn btn-sm btn-outline-primary">
                                                            <i class="bx bx-show me-0"></i>
                                                        </a>
                                                    @endif
                                                        @if(($proforma->status == 'pending' || $proforma->status == 'opened') && $proforma?->selected())
                                                            <a href="/float?proforma_id={{ $proforma->id }}" class="btn btn-sm btn-primary">Float</a>
                                                        @endif

                                                        @if($proforma->status !== 'closed' && $proforma->status !== 'completed'  && $proforma->status !== 'payment collected') 
                                                            @if(!$proforma->applications->isEmpty())
                                                                <form action="{{ route('proforma.close', $proforma->id) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <button type="submit" class="btn btn-primary btn-sm"
                                                                        @if($proforma->status === 'pending' || $proforma->status === 'opened') hidden @endif>
                                                                        Close
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        @endif
                                                        @if($proforma->status == 'closed')
                                                                <form action="{{ route('proforma.paid', $proforma->id) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <button type="submit" class="btn btn-primary btn-sm"
                                                                        @if($proforma->status === 'pending' || $proforma->status === 'opened') hidden @endif>
                                                                        Send to manager
                                                                    </button>
                                                                </form>
                                                            
                                                        @endif
                                                    
                                                </div>
                                            </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No proformas assigned yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="mt-3">
                    {{ $proformas->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
