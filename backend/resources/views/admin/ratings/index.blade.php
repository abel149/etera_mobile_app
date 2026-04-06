@extends('layouts.admin')

@section('content')
<div class="container py-4 page-wrapper">
    <h4 class="mb-4">Garage & Shop Ratings</h4>

    <table class="table table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th>User</th>
                <th>Rating</th>
                <th>Reviews</th>
                <th>People Rated</th>
            </tr>
        </thead>
        <tbody>
        @foreach($users as $user)
            <tr>
                {{-- User name --}}
                <td>{{ $user->name }}</td>

                {{-- Rating --}}
                <td>
                    <strong>{{ number_format($user->reviews_avg_rating ?? 0, 1) }}</strong>
                    <div>
                        @for($i = 1; $i <= 5; $i++)
                            <i class="bi bi-star{{ $i <= round($user->reviews_avg_rating) ? '-fill text-warning' : '' }}"></i>
                        @endfor
                    </div>
                </td>

                {{-- View Reviews --}}
                <td>
                    <button class="btn btn-sm btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#reviewsModal{{ $user->id }}">
                        View Reviews
                    </button>

                    {{-- Modal --}}
                    <div class="modal fade" id="reviewsModal{{ $user->id }}" tabindex="-1">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        Reviews for {{ $user->name }}
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    @forelse($user->reviews as $review)
                                        <div class="border-bottom mb-3 pb-2">
                                            <strong>
                                                Rating: {{ $review->rating }}/5
                                            </strong>
                                            <p class="mb-1">
                                                {{ $review->review ?? 'No comment' }}
                                            </p>
                                            <small class="text-muted">
                                                {{ $review->created_at->diffForHumans() }}
                                            </small>
                                        </div>
                                    @empty
                                        <p class="text-muted">No reviews yet.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </td>

                {{-- Count --}}
                <td>
                    {{ $user->reviews_count }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
