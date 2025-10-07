<?php

namespace App\Http\Controllers\CMS;

use App\Models\Review;
use App\Models\User;
use App\Models\Item;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::with(['user', 'item'])
            ->latest()
            ->paginate(10);

        return view('cms.reviews.index', compact('reviews'));
    }

    public function create()
    {
        $users = User::all();
        $items = Item::all();
        return view('cms.reviews.create', compact('users', 'items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'item_id' => 'required|exists:items,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Check if user already reviewed this item
        $existingReview = Review::where('user_id', $validated['user_id'])
            ->where('item_id', $validated['item_id'])
            ->first();

        if ($existingReview) {
            return redirect()->back()
                ->with('error', 'This user has already reviewed this item.')
                ->withInput();
        }

        Review::create($validated);

        return redirect()->route('cms.reviews.index')
            ->with('success', 'Review created successfully.');
    }

    public function show(Review $review)
    {
        $review->load(['user', 'item']);
        return view('cms.reviews.show', compact('review'));
    }

    public function edit(Review $review)
    {
        $users = User::all();
        $items = Item::all();
        return view('cms.reviews.edit', compact('review', 'users', 'items'));
    }

    public function update(Request $request, Review $review)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'item_id' => 'required|exists:items,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Check if another review exists with same user and item (excluding current review)
        $existingReview = Review::where('user_id', $validated['user_id'])
            ->where('item_id', $validated['item_id'])
            ->where('id', '!=', $review->id)
            ->first();

        if ($existingReview) {
            return redirect()->back()
                ->with('error', 'This user has already reviewed this item.')
                ->withInput();
        }

        $review->update($validated);

        return redirect()->route('cms.reviews.index')
            ->with('success', 'Review updated successfully.');
    }

    public function destroy(Review $review)
    {
        $review->delete();

        return redirect()->route('cms.reviews.index')
            ->with('success', 'Review deleted successfully.');
    }

    /**
     * Get reviews for a specific item
     */
    public function itemReviews(Item $item)
    {
        $reviews = $item->reviews()
            ->with('user')
            ->latest()
            ->paginate(10);

        return view('cms.reviews.item-reviews', compact('item', 'reviews'));
    }

    /**
     * Get reviews by a specific user
     */
    public function userReviews(User $user)
    {
        $reviews = $user->reviews()
            ->with('item')
            ->latest()
            ->paginate(10);

        return view('cms.reviews.user-reviews', compact('user', 'reviews'));
    }
}
