@extends('layouts.authentication')

@section('title', 'Leave a Review — etera')

@section('branding')
    <img src="{{ asset('assets/images/transparent.svg') }}" class="etera-auth-logo" alt="etera">
    <h2 class="etera-heading etera-heading-lg" style="text-align:center; margin-bottom: 0.5rem;">
        Share Your Experience
    </h2>
    <p class="etera-subtext" style="text-align:center; max-width: 360px; color: rgba(255,255,255,0.85);">
        Help others find the best garages and spare part shops by leaving an honest review.
    </p>
@endsection

@section('styles')
<style>
    /* Star rating */
    .star-rating { display: flex; gap: 6px; margin: 8px 0 4px; }
    .star-rating .star {
        font-size: 2rem;
        cursor: pointer;
        color: #d1d5db;
        transition: all 0.2s ease;
        user-select: none;
    }
    .star-rating .star:hover,
    .star-rating .star.selected {
        color: #f59e0b;
        transform: scale(1.15);
    }
    .star-rating .star.hover-preview { color: #fbbf24; }

    /* Success/Error alerts */
    .etera-alert {
        padding: 12px 16px;
        border-radius: var(--etera-radius-sm, 10px);
        margin-bottom: 1.25rem;
        font-size: 0.9rem;
        animation: etera-fade-in 0.4s ease-out;
    }
    .etera-alert-success {
        background: rgba(40,167,69,0.08);
        border: 1px solid rgba(40,167,69,0.25);
        color: #1e7e34;
    }
    .etera-alert-danger {
        background: rgba(220,53,69,0.06);
        border: 1px solid rgba(220,53,69,0.2);
        color: #dc3545;
    }
    .etera-alert-danger ul { margin: 0; padding-left: 18px; }

    /* Textarea styling */
    .etera-textarea {
        width: 100%;
        padding: 14px 16px;
        background: #f9fafb;
        border: 1px solid #d1d5db;
        border-radius: var(--etera-radius-sm, 10px);
        color: #1a1a2e;
        font-size: 1rem;
        font-family: 'Inter', sans-serif;
        transition: all 0.3s ease;
        outline: none;
        resize: vertical;
        min-height: 120px;
        box-sizing: border-box;
    }
    .etera-textarea::placeholder { color: #9ca3af; }
    .etera-textarea:focus {
        border-color: var(--etera-green, #28a745);
        box-shadow: 0 0 0 3px rgba(40,167,69,0.15);
        background: #fff;
    }

    /* Select styling */
    .etera-select {
        width: 100%;
        padding: 14px 16px;
        background: #f9fafb;
        border: 1px solid #d1d5db;
        border-radius: var(--etera-radius-sm, 10px);
        color: #1a1a2e;
        font-size: 1rem;
        font-family: 'Inter', sans-serif;
        transition: all 0.3s ease;
        outline: none;
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%236b7280' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10l-5 5z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 16px center;
        padding-right: 40px;
        box-sizing: border-box;
    }
    .etera-select:focus {
        border-color: var(--etera-green, #28a745);
        box-shadow: 0 0 0 3px rgba(40,167,69,0.15);
        background-color: #fff;
    }
</style>
@endsection

@section('content')

<div style="animation: etera-fade-in 0.6s ease-out">
    <div style="text-align: center; margin-bottom: 2rem;">
        <img src="{{ asset('assets/images/transparent.svg') }}" alt="etera" style="max-width: 120px; margin-bottom: 1rem;" class="d-xl-none">
        <h2 class="etera-heading" style="font-size: 1.5rem; margin-bottom: 0.5rem;">Leave a Review</h2>
        <p class="etera-subtext">Rate and review a garage or spare part shop</p>
    </div>

    {{-- Success --}}
    @if(session('success'))
        <div class="etera-alert etera-alert-success">
            ✓ {{ session('success') }}
        </div>
    @endif

    {{-- Errors --}}
    @if ($errors->any())
        <div class="etera-alert etera-alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('reviews.store') }}" method="POST" id="reviewForm">
        @csrf

        {{-- User dropdown --}}
        <div class="etera-input-group">
            <label>Select User <span style="color:#dc3545">*</span></label>
            <select name="user_id" id="user_id" class="etera-select" required>
                <option value="">— Select a garage or shop —</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Star Rating --}}
        <div class="etera-input-group">
            <label>Rating <span style="color:#dc3545">*</span></label>
            <div class="star-rating" id="star-container">
                @for($i = 1; $i <= 5; $i++)
                    <span class="star" data-value="{{ $i }}">&#9733;</span>
                @endfor
            </div>
            <input type="hidden" name="rating" id="rating" required>
            <div id="ratingError" class="etera-error-text" style="display:none;"></div>
        </div>

        {{-- Review text --}}
        <div class="etera-input-group">
            <label>Your Review</label>
            <textarea name="review" id="review" class="etera-textarea" rows="4" placeholder="Share your experience with this business..."></textarea>
        </div>

        <button type="submit" class="etera-btn etera-btn-primary etera-btn-block etera-btn-lg" id="submitBtn">
            Submit Review
        </button>
    </form>

    <div style="text-align: center; margin-top: 1.5rem;">
        <p class="etera-subtext" style="font-size: 0.9rem;">
            <a href="/login" class="etera-link">← Back to Login</a>
        </p>
    </div>
</div>

<script>
    const stars = document.querySelectorAll('.star');
    const ratingInput = document.getElementById('rating');
    let currentRating = 0;

    stars.forEach(star => {
        // Hover preview
        star.addEventListener('mouseenter', () => {
            const val = parseInt(star.dataset.value);
            stars.forEach((s, i) => {
                s.classList.toggle('hover-preview', i < val);
            });
        });

        // Click
        star.addEventListener('click', () => {
            currentRating = parseInt(star.dataset.value);
            ratingInput.value = currentRating;
            stars.forEach((s, i) => {
                s.classList.toggle('selected', i < currentRating);
                s.classList.remove('hover-preview');
            });
            document.getElementById('ratingError').style.display = 'none';
        });
    });

    // Mouse leave — restore to clicked state
    document.getElementById('star-container').addEventListener('mouseleave', () => {
        stars.forEach((s, i) => {
            s.classList.remove('hover-preview');
            s.classList.toggle('selected', i < currentRating);
        });
    });

    // Form validation
    document.getElementById('reviewForm').addEventListener('submit', function(e) {
        if (!ratingInput.value) {
            e.preventDefault();
            document.getElementById('ratingError').textContent = 'Please select a rating.';
            document.getElementById('ratingError').style.display = 'block';
        }
    });
</script>

@endsection
