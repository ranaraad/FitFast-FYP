<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\Item;
use App\Models\Store;
use App\Models\Payment;

class ExportController extends Controller
{
    public function exportUsers()
    {
        $users = User::with('role')->get();

        $filename = "users_export_" . date('Y-m-d_H-i-s') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');

            // Add BOM to fix UTF-8 encoding in Excel
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

            // Headers
            fputcsv($file, [
                'ID',
                'Name',
                'Email',
                'Role',
                'Email Verified',
                'Created At',
                'Updated At'
            ]);

            // Data
            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->role->name ?? 'No Role',
                    $user->email_verified_at ? 'Yes' : 'No',
                    $user->created_at->format('Y-m-d H:i:s'),
                    $user->updated_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

        /**
     * Export items to CSV
     */
    public function exportItems()
    {
        $items = Item::with(['store', 'category', 'users'])->get();

        $filename = "items_export_" . date('Y-m-d_H-i-s') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($items) {
            $file = fopen('php://output', 'w');

            // Add BOM to fix UTF-8 encoding in Excel
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

            // Headers
            fputcsv($file, [
                'ID',
                'Name',
                'Description',
                'Store',
                'Price',
                'Category',
                'Garment Type',
                'Total Stock',
                'Stock Status',
                'Colors Available',
                'Color Variants Details',
                'Size Stock Details',
                'Users Count',
                'Created At',
                'Updated At'
            ]);

            // Data
            foreach ($items as $item) {
                // Prepare color variants details
                $colorDetails = '';
                if ($item->color_variants && count($item->color_variants) > 0) {
                    $colorDetails = collect($item->color_variants)->map(function($colorData, $colorCode) {
                        $colorName = $colorData['name'] ?? $colorCode;
                        $stock = $colorData['stock'] ?? 0;
                        return "{$colorName}: {$stock}";
                    })->implode('; ');
                }

                // Prepare size stock details
                $sizeDetails = '';
                if ($item->size_stock && count($item->size_stock) > 0) {
                    $sizeDetails = collect($item->size_stock)->map(function($stock, $size) {
                        return "{$size}: {$stock}";
                    })->implode('; ');
                }

                // Determine stock status
                $stockStatus = 'Out of Stock';
                if ($item->stock_quantity > 10) {
                    $stockStatus = 'In Stock';
                } elseif ($item->stock_quantity > 0) {
                    $stockStatus = 'Low Stock';
                }

                fputcsv($file, [
                    $item->id,
                    $item->name,
                    $item->description ?? '',
                    $item->store->name ?? 'N/A',
                    number_format($item->price, 2),
                    $item->category->name ?? 'No Category',
                    $item->garment_type ? Item::getGarmentTypeName($item->garment_type) : 'Not Set',
                    $item->stock_quantity,
                    $stockStatus,
                    $item->color_variants ? count($item->color_variants) : 0,
                    $colorDetails,
                    $sizeDetails,
                    $item->users->count(),
                    $item->created_at->format('Y-m-d H:i:s'),
                    $item->updated_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export items with low stock (stock_quantity < 10)
     */
    public function exportLowStockItems()
    {
        $items = Item::with(['store', 'category'])
                    ->where('stock_quantity', '<', 10)
                    ->orderBy('stock_quantity', 'asc')
                    ->get();

        $filename = "low_stock_items_export_" . date('Y-m-d_H-i-s') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($items) {
            $file = fopen('php://output', 'w');

            // Add BOM to fix UTF-8 encoding in Excel
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

            // Headers
            fputcsv($file, [
                'ID',
                'Name',
                'Store',
                'Price',
                'Category',
                'Current Stock',
                'Stock Status',
                'Colors',
                'Urgency Level',
                'Created At'
            ]);

            // Data
            foreach ($items as $item) {
                // Determine urgency level
                $urgency = 'Medium';
                if ($item->stock_quantity == 0) {
                    $urgency = 'Critical - Out of Stock';
                } elseif ($item->stock_quantity < 5) {
                    $urgency = 'High - Very Low Stock';
                }

                // Prepare color info
                $colors = $item->color_variants ? count($item->color_variants) . ' colors' : 'No colors';

                fputcsv($file, [
                    $item->id,
                    $item->name,
                    $item->store->name ?? 'N/A',
                    number_format($item->price, 2),
                    $item->category->name ?? 'No Category',
                    $item->stock_quantity,
                    $item->stock_quantity == 0 ? 'Out of Stock' : 'Low Stock',
                    $colors,
                    $urgency,
                    $item->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export stores to CSV
     */
    public function exportStores()
    {
        $stores = Store::withCount([
            'items',
            'items as low_stock_items_count' => function($query) {
                $query->where('stock_quantity', '<', 10)->where('stock_quantity', '>', 0);
            },
            'items as critical_stock_items_count' => function($query) {
                $query->where('stock_quantity', '<', 5)->where('stock_quantity', '>', 0);
            },
            'items as out_of_stock_items_count' => function($query) {
                $query->where('stock_quantity', 0);
            },
            'orders'
        ])->get();

        $filename = "stores_export_" . date('Y-m-d_H-i-s') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($stores) {
            $file = fopen('php://output', 'w');

            // Add BOM to fix UTF-8 encoding in Excel
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

            // Headers
            fputcsv($file, [
                'ID',
                'Name',
                'Description',
                'Status',
                'Store Admin',
                'Contact Email',
                'Contact Phone',
                'Address',
                'Total Items',
                'Low Stock Items',
                'Critical Stock Items',
                'Out of Stock Items',
                'Total Orders',
                'Inventory Health',
                'Created At',
                'Updated At'
            ]);

            // Data
            foreach ($stores as $store) {
                // Parse contact info
                $contactInfo = $store->contact_info ?? [];
                $contactEmail = $contactInfo['email'] ?? 'N/A';
                $contactPhone = $contactInfo['phone'] ?? 'N/A';

                // Determine inventory health
                $inventoryHealth = 'Healthy';
                if ($store->critical_stock_items_count > 0) {
                    $inventoryHealth = 'Critical';
                } elseif ($store->low_stock_items_count > 0) {
                    $inventoryHealth = 'Warning';
                } elseif ($store->out_of_stock_items_count > 0) {
                    $inventoryHealth = 'Out of Stock Items';
                }

                fputcsv($file, [
                    $store->id,
                    $store->name,
                    $store->description ?? '',
                    ucfirst($store->status),
                    $store->user->name ?? 'Not Assigned',
                    $contactEmail,
                    $contactPhone,
                    $store->address ?? '',
                    $store->items_count,
                    $store->low_stock_items_count,
                    $store->critical_stock_items_count,
                    $store->out_of_stock_items_count,
                    $store->orders_count,
                    $inventoryHealth,
                    $store->created_at->format('Y-m-d H:i:s'),
                    $store->updated_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export stores with stock alerts
     */
    public function exportStoresWithAlerts()
    {
        $stores = Store::withCount([
            'items',
            'items as low_stock_items_count' => function($query) {
                $query->where('stock_quantity', '<', 10)->where('stock_quantity', '>', 0);
            },
            'items as critical_stock_items_count' => function($query) {
                $query->where('stock_quantity', '<', 5)->where('stock_quantity', '>', 0);
            },
            'items as out_of_stock_items_count' => function($query) {
                $query->where('stock_quantity', 0);
            }
        ])
        ->having('low_stock_items_count', '>', 0)
        ->orHaving('critical_stock_items_count', '>', 0)
        ->orHaving('out_of_stock_items_count', '>', 0)
        ->get();

        $filename = "stores_with_alerts_export_" . date('Y-m-d_H-i-s') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($stores) {
            $file = fopen('php://output', 'w');

            // Add BOM to fix UTF-8 encoding in Excel
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

            // Headers
            fputcsv($file, [
                'ID',
                'Store Name',
                'Status',
                'Total Items',
                'Critical Alerts',
                'Low Stock Alerts',
                'Out of Stock Items',
                'Total Alerts',
                'Alert Level',
                'Urgency',
                'Created At'
            ]);

            // Data
            foreach ($stores as $store) {
                $totalAlerts = $store->critical_stock_items_count + $store->low_stock_items_count + $store->out_of_stock_items_count;

                // Determine alert level and urgency
                $alertLevel = 'Low';
                $urgency = 'Monitor';

                if ($store->critical_stock_items_count > 0) {
                    $alertLevel = 'Critical';
                    $urgency = 'Immediate Action Required';
                } elseif ($store->low_stock_items_count > 5) {
                    $alertLevel = 'High';
                    $urgency = 'Action Required';
                } elseif ($store->low_stock_items_count > 0) {
                    $alertLevel = 'Medium';
                    $urgency = 'Review Needed';
                }

                fputcsv($file, [
                    $store->id,
                    $store->name,
                    ucfirst($store->status),
                    $store->items_count,
                    $store->critical_stock_items_count,
                    $store->low_stock_items_count,
                    $store->out_of_stock_items_count,
                    $totalAlerts,
                    $alertLevel,
                    $urgency,
                    $store->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

     /**
     * Export payments to CSV
     */
    public function exportPayments()
    {
        $payments = Payment::with(['order.user', 'paymentMethod'])
            ->latest()
            ->get();

        $filename = "payments_export_" . date('Y-m-d_H-i-s') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($payments) {
            $file = fopen('php://output', 'w');

            // Add BOM to fix UTF-8 encoding in Excel
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

            // Headers
            fputcsv($file, [
                'Payment ID',
                'Order ID',
                'Customer Name',
                'Customer Email',
                'Amount',
                'Payment Method',
                'Status',
                'Transaction ID',
                'Payment Date',
                'Created At',
                'Updated At'
            ]);

            // Data
            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->id,
                    $payment->order->id ?? 'N/A',
                    $payment->order->user->name ?? 'N/A',
                    $payment->order->user->email ?? 'N/A',
                    number_format($payment->amount, 2),
                    $payment->paymentMethod->type ?? 'Not specified',
                    ucfirst($payment->status),
                    $payment->transaction_id ?? 'N/A',
                    $payment->created_at->format('Y-m-d H:i:s'),
                    $payment->created_at->format('Y-m-d H:i:s'),
                    $payment->updated_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export payments by status
     */
    public function exportPaymentsByStatus($status)
    {
        $validStatuses = ['pending', 'completed', 'failed', 'refunded', 'processing'];

        if (!in_array($status, $validStatuses)) {
            return redirect()->back()->with('error', 'Invalid payment status.');
        }

        $payments = Payment::with(['order.user', 'paymentMethod'])
            ->where('status', $status)
            ->latest()
            ->get();

        $filename = "payments_{$status}_export_" . date('Y-m-d_H-i-s') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($payments, $status) {
            $file = fopen('php://output', 'w');

            // Add BOM to fix UTF-8 encoding in Excel
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

            // Headers
            fputcsv($file, [
                'Payment ID',
                'Order ID',
                'Customer Name',
                'Customer Email',
                'Amount',
                'Payment Method',
                'Status',
                'Transaction ID',
                'Payment Date',
                'Created At'
            ]);

            // Data
            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->id,
                    $payment->order->id ?? 'N/A',
                    $payment->order->user->name ?? 'N/A',
                    $payment->order->user->email ?? 'N/A',
                    number_format($payment->amount, 2),
                    $payment->paymentMethod->type ?? 'Not specified',
                    ucfirst($payment->status),
                    $payment->transaction_id ?? 'N/A',
                    $payment->created_at->format('Y-m-d H:i:s'),
                    $payment->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export payments by date range
     */
    public function exportPaymentsByDateRange(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $payments = Payment::with(['order.user', 'paymentMethod'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->latest()
            ->get();

        $filename = "payments_{$startDate}_to_{$endDate}_export_" . date('Y-m-d_H-i-s') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($payments, $startDate, $endDate) {
            $file = fopen('php://output', 'w');

            // Add BOM to fix UTF-8 encoding in Excel
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

            // Headers
            fputcsv($file, [
                'Payment ID',
                'Order ID',
                'Customer Name',
                'Customer Email',
                'Amount',
                'Payment Method',
                'Status',
                'Transaction ID',
                'Payment Date',
                'Date Range: ' . $startDate . ' to ' . $endDate
            ]);

            // Data
            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->id,
                    $payment->order->id ?? 'N/A',
                    $payment->order->user->name ?? 'N/A',
                    $payment->order->user->email ?? 'N/A',
                    number_format($payment->amount, 2),
                    $payment->paymentMethod->type ?? 'Not specified',
                    ucfirst($payment->status),
                    $payment->transaction_id ?? 'N/A',
                    $payment->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
