<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserReview;
use Illuminate\Support\Facades\Auth;

class UserReviewController extends Controller
{
    /**
     * Show ratings summary for garages & shops
     */
    public function index()
    {
        $users = User::whereIn('role', ['garage', 'shop'])
            ->with([
                'reviews' => function ($query) {
                    $query->latest();
                }
            ])
            ->withCount('reviews') // how many people rated
            ->withAvg('reviews', 'rating') // average rating
            ->get();

        return view('admin.ratings.index', compact('users'));
    }

    /**
     * Store a review
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'rating'  => 'required|integer|min:1|max:5',
            'review'  => 'nullable|string|max:1000',
        ]);

        UserReview::create([
            'user_id'     => $request->user_id,
            'reviewer_id' => Auth::check() ? Auth::id() : null,
            'rating'      => $request->rating,
            'review'      => $request->review,
        ]);

        return back()->with('success', 'Review submitted successfully!');
    }
}
