<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Get active events for display
     */
    public function index(Request $request)
    {
        $query = Event::query();

        // Filter by position (only if not empty)
        if ($request->filled('position')) {
            $query->byPosition($request->position);
        }

        // Filter by status (only if not empty)
        if ($request->filled('status')) {
            if ($request->status === 'live') {
                // For "live" status, include both live and upcoming events
                $query->where(function($q) {
                    $q->live()->orWhere(function($subQ) {
                        $subQ->upcoming();
                    });
                });
            } elseif ($request->status === 'upcoming') {
                $query->upcoming();
            } elseif ($request->status === 'expired') {
                $query->expired();
            } else {
                // If status is provided but not recognized, get live or upcoming
                $query->where(function($q) {
                    $q->live()->orWhere(function($subQ) {
                        $subQ->upcoming();
                    });
                });
            }
        } else {
            // Default: get live and upcoming events
            $query->where(function($q) {
                $q->live()->orWhere(function($subQ) {
                    $subQ->upcoming();
                });
            });
        }

        // Sort by priority and sort_order
        $query->orderBy('priority', 'desc')
              ->orderBy('sort_order', 'asc')
              ->orderBy('start_date', 'asc');

        $events = $query->get();

        logger($events);

        return response()->json($events);
    }

    /**
     * Get single event by slug
     */
    public function show($slug)
    {
        $event = Event::where('slug', $slug)
            ->where('is_active', true)
            ->with('products')
            ->firstOrFail();

        return response()->json($event);
    }
}

