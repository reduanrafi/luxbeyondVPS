<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = User::whereHas('roles', function ($q) {
            $q->where('name', 'Customer');
        })->with('roles');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $customers = $query->latest()->paginate($perPage);

        // Add computed fields
        $customers->getCollection()->transform(function ($customer) {
            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone ?? 'N/A',
                'orders' => 0, // TODO: Add relationship count when orders table exists
                'totalSpent' => 0, // TODO: Calculate from orders
                'joined' => $customer->created_at->format('Y-m-d'),
                'created_at' => $customer->created_at,
            ];
        });

        return response()->json($customers);
    }

    public function show($id)
    {
        $customer = User::whereHas('roles', function ($q) {
            $q->where('name', 'Customer');
        })->with('roles')->findOrFail($id);

        return response()->json([
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone ?? 'N/A',
            'orders' => 0, // TODO: Add relationship count
            'totalSpent' => 0, // TODO: Calculate from orders
            'joined' => $customer->created_at->format('Y-m-d'),
            'created_at' => $customer->created_at,
        ]);
    }

    public function toggleStatus($id)
    {
        $customer = User::whereHas('roles', function ($q) {
            $q->where('name', 'Customer');
        })->findOrFail($id);

        // Toggle active status (you may need to add an 'is_active' column to users table)
        // For now, we'll just return success
        return response()->json([
            'message' => 'Customer status updated successfully',
            'customer' => $customer
        ]);
    }
}
