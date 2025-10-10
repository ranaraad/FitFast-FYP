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

        return view('cms.pages.reviews.index', compact('reviews'));
    }

    public function show(Review $review)
    {
        $review->load(['user', 'item']);
        return view('cms.pages.reviews.show', compact('review'));
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

        return view('cms.pages.reviews.item-reviews', compact('item', 'reviews'));
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

        return view('cms.pages.reviews.user-reviews', compact('user', 'reviews'));
    }
}
