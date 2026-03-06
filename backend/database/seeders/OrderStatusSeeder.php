<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\OrderStatus;

class OrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'pending',
                'label' => 'Pending',
                'color' => '#F59E0B',
                'icon' => 'clock',
                'description' => 'Order is pending confirmation',
                'sort_order' => 1,
                'is_default' => true,
                'is_final' => false,
                'is_active' => true,
                'allowed_next_statuses' => [2, 6], // Can go to processing or cancelled
            ],
            [
                'name' => 'processing',
                'label' => 'Processing',
                'color' => '#3B82F6',
                'icon' => 'cog',
                'description' => 'Order is being processed',
                'sort_order' => 2,
                'is_default' => false,
                'is_final' => false,
                'is_active' => true,
                'allowed_next_statuses' => [3, 6], // Can go to shipped or cancelled
            ],
            [
                'name' => 'shipped',
                'label' => 'Shipped',
                'color' => '#8B5CF6',
                'icon' => 'truck',
                'description' => 'Order has been shipped',
                'sort_order' => 3,
                'is_default' => false,
                'is_final' => false,
                'is_active' => true,
                'allowed_next_statuses' => [4, 5], // Can go to delivered or returned
            ],
            [
                'name' => 'delivered',
                'label' => 'Delivered',
                'color' => '#10B981',
                'icon' => 'check-circle',
                'description' => 'Order has been delivered',
                'sort_order' => 4,
                'is_default' => false,
                'is_final' => true,
                'is_active' => true,
                'allowed_next_statuses' => null, // Final status
            ],
            [
                'name' => 'completed',
                'label' => 'Completed',
                'color' => '#10B981',
                'icon' => 'check',
                'description' => 'Order is completed',
                'sort_order' => 5,
                'is_default' => false,
                'is_final' => true,
                'is_active' => true,
                'allowed_next_statuses' => null, // Final status
            ],
            [
                'name' => 'cancelled',
                'label' => 'Cancelled',
                'color' => '#EF4444',
                'icon' => 'x-circle',
                'description' => 'Order has been cancelled',
                'sort_order' => 6,
                'is_default' => false,
                'is_final' => true,
                'is_active' => true,
                'allowed_next_statuses' => null, // Final status
            ],
        ];

        // First, create all statuses
        $createdStatuses = [];
        foreach ($statuses as $status) {
            $allowedNext = $status['allowed_next_statuses'];
            unset($status['allowed_next_statuses']);
            
            $created = OrderStatus::updateOrCreate(
                ['name' => $status['name']],
                $status
            );
            
            $createdStatuses[$status['sort_order']] = $created;
        }
        
        // Then, update allowed_next_statuses with actual IDs
        foreach ($statuses as $status) {
            if (isset($status['allowed_next_statuses']) && $status['allowed_next_statuses'] != null) {
                $created = $createdStatuses[$status['sort_order']];
                $nextStatusIds = [];
                foreach ($status['allowed_next_statuses'] as $nextSortOrder) {
                    if (isset($createdStatuses[$nextSortOrder])) {
                        $nextStatusIds[] = $createdStatuses[$nextSortOrder]->id;
                    }
                }
                $created->update(['allowed_next_statuses' => $nextStatusIds]);
            }
        }
    }
}
