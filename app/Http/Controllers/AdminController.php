<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard()
    {
        // Get all pending and approved coaches
        $pendingCoaches = User::where('role', 'coach')->where('status', 'pending')->get();
        $approvedCoaches = User::where('role', 'coach')->where('status', 'approved')->get();
        $declinedCoaches = User::where('role', 'coach')->where('status', 'declined')->get();

        // Get stores
        $pendingStores = \App\Models\TeamStore::where('status', 'pending')->with('user')->get();
        $finalizedStores = \App\Models\TeamStore::where('status', 'submitted_to_admin')->with(['user', 'parentOrders'])->get();

        return view('admin.dashboard', compact('pendingCoaches', 'approvedCoaches', 'declinedCoaches', 'pendingStores', 'finalizedStores'));
    }

    public function approveCoach(User $user)
    {
        if ($user->role === 'coach') {
            $user->update(['status' => 'approved']);
            $user->notify(new \App\Notifications\CoachAccountApproved());
            return redirect()->route('admin.dashboard')->with('success', "Coach {$user->name} has been approved.");
        }
        return redirect()->route('admin.dashboard')->with('error', 'Action not allowed.');
    }

    public function declineCoach(User $user)
    {
        if ($user->role === 'coach') {
            $user->update(['status' => 'declined']);
            return redirect()->route('admin.dashboard')->with('success', "Coach {$user->name} has been declined.");
        }
        return redirect()->route('admin.dashboard')->with('error', 'Action not allowed.');
    }

    public function approveStore(\App\Models\TeamStore $store)
    {
        $store->update(['status' => 'approved']);
        $store->user->notify(new \App\Notifications\StoreApproved($store));
        return redirect()->route('admin.dashboard')->with('success', "Store {$store->name} activated.");
    }

    public function declineStore(\App\Models\TeamStore $store)
    {
        $store->update(['status' => 'declined']);
        return redirect()->route('admin.dashboard')->with('success', "Store {$store->name} declined.");
    }

    public function exportOrderCSV(\App\Models\TeamStore $store)
    {
        $orders = $store->parentOrders;
        
        $filename = "{$store->slug}-master-order.csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = ['Athlete Name', 'Gender Base', 'Item Selected', 'Size', 'Quantity', 'Special Notes'];

        $callback = function() use ($orders, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($orders as $order) {
                if (is_array($order->items_json)) {
                    foreach ($order->items_json as $item) {
                        fputcsv($file, [
                            $order->athlete_name,
                            $order->gender ?? 'Not Specified',
                            $item['name'] ?? 'Unknown Item',
                            $item['size'] ?? 'N/A',
                            $item['qty'] ?? 1,
                            $order->special_notes ?? ''
                        ]);
                    }
                }
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
