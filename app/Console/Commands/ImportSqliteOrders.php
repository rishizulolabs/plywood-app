<?php

namespace App\Console\Commands;

use App\Models\DistributorProfile;
use App\Models\Inquiry;
use App\Models\InquiryItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\RestockRequest;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDO;

class ImportSqliteOrders extends Command
{
    protected $signature = 'import:sqlite-orders {--path=database/database.sqlite}';

    protected $description = 'Import customer orders and distributor purchase orders from the legacy SQLite database into MySQL';

    public function handle(): int
    {
        $path = base_path($this->option('path'));

        if (! is_file($path)) {
            $this->error("SQLite file not found: {$path}");

            return self::FAILURE;
        }

        $sqlite = new PDO('sqlite:'.$path);
        $sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $userMap = $this->mapUsers($sqlite);
        $productMap = $this->mapProducts($sqlite);
        $distributorMap = $this->mapDistributors($sqlite);

        $orders = $sqlite
            ->query('SELECT * FROM orders ORDER BY id')
            ->fetchAll(PDO::FETCH_ASSOC);

        $imported = 0;
        $skipped = 0;

        foreach ($orders as $row) {
            if (Order::query()->where('order_number', $row['order_number'])->exists()) {
                $this->line("Skipping {$row['order_number']} (already in MySQL)");
                $skipped++;

                continue;
            }

            $customerId = $userMap[(int) $row['customer_id']] ?? null;
            $distributorId = $distributorMap[(int) $row['distributor_profile_id']] ?? null;

            if (! $customerId || ! $distributorId) {
                $this->warn("Skipping {$row['order_number']} (missing customer or distributor mapping)");
                $skipped++;

                continue;
            }

            $inquiryStmt = $sqlite->prepare('SELECT * FROM inquiries WHERE id = ?');
            $inquiryStmt->execute([(int) $row['inquiry_id']]);
            $inquiryRow = $inquiryStmt->fetch(PDO::FETCH_ASSOC);

            if (! $inquiryRow) {
                $this->warn("Skipping {$row['order_number']} (inquiry not found in SQLite)");
                $skipped++;

                continue;
            }

            $itemStmt = $sqlite->prepare('SELECT * FROM inquiry_items WHERE inquiry_id = ?');
            $itemStmt->execute([(int) $row['inquiry_id']]);
            $itemRows = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

            $mappedItems = [];

            foreach ($itemRows as $itemRow) {
                $productId = $productMap[(int) $itemRow['product_id']] ?? null;

                if (! $productId) {
                    $this->warn("Skipping {$row['order_number']} (product #{$itemRow['product_id']} not mapped)");
                    continue 2;
                }

                $mappedItems[] = [
                    'product_id' => $productId,
                    'quantity' => (int) $itemRow['quantity'],
                    'customer_remarks' => $itemRow['customer_remarks'] ?? null,
                    'created_at' => $itemRow['created_at'] ?? now(),
                    'updated_at' => $itemRow['updated_at'] ?? now(),
                ];
            }

            if ($mappedItems === []) {
                $this->warn("Skipping {$row['order_number']} (no line items)");
                $skipped++;

                continue;
            }

            DB::transaction(function () use ($row, $inquiryRow, $mappedItems, $customerId, $distributorId) {
                $inquiry = Inquiry::create([
                    'inquiry_number' => $this->uniqueInquiryNumber($inquiryRow['inquiry_number'] ?? null),
                    'customer_id' => $customerId,
                    'distributor_profile_id' => $distributorId,
                    'status' => $inquiryRow['status'] ?? 'converted',
                    'customer_notes' => $inquiryRow['customer_notes'] ?? null,
                    'delivery_city' => $inquiryRow['delivery_city'] ?? 'Not specified',
                    'delivery_pincode' => $inquiryRow['delivery_pincode'] ?? '000000',
                    'expected_by' => $inquiryRow['expected_by'] ?? null,
                ]);

                Inquiry::query()
                    ->whereKey($inquiry->id)
                    ->update([
                        'created_at' => $inquiryRow['created_at'] ?? now(),
                        'updated_at' => $inquiryRow['updated_at'] ?? now(),
                    ]);

                foreach ($mappedItems as $item) {
                    $created = InquiryItem::create([
                        'inquiry_id' => $inquiry->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'customer_remarks' => $item['customer_remarks'],
                    ]);

                    InquiryItem::query()
                        ->whereKey($created->id)
                        ->update([
                            'created_at' => $item['created_at'],
                            'updated_at' => $item['updated_at'],
                        ]);
                }

                $order = Order::query()->create([
                    'order_number' => $row['order_number'],
                    'inquiry_id' => $inquiry->id,
                    'customer_id' => $customerId,
                    'distributor_profile_id' => $distributorId,
                    'total_amount' => $row['total_amount'],
                    'payment_status' => $row['payment_status'] ?? 'pending',
                    'fulfillment_status' => $row['fulfillment_status'] ?? 'processing',
                    'delivery_address' => $row['delivery_address'] ?? 'Not specified',
                    'invoice_path' => $row['invoice_path'] ?? null,
                ]);

                Order::query()
                    ->whereKey($order->id)
                    ->update([
                        'created_at' => $row['created_at'] ?? now(),
                        'updated_at' => $row['updated_at'] ?? now(),
                    ]);
            });

            $this->info("Imported {$row['order_number']}");
            $imported++;
        }

        $this->newLine();
        $this->info("Customer orders — imported: {$imported}, skipped: {$skipped}, total: ".Order::count());

        [$restockImported, $restockSkipped] = $this->importRestockRequests($sqlite, $productMap, $distributorMap);

        $this->newLine();
        $this->info("Distributor orders — imported: {$restockImported}, skipped: {$restockSkipped}, total: ".RestockRequest::count());

        return self::SUCCESS;
    }

    private function importRestockRequests(PDO $sqlite, array $productMap, array $distributorMap): array
    {
        $imported = 0;
        $skipped = 0;

        $rows = $sqlite
            ->query('SELECT * FROM restock_requests ORDER BY id')
            ->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            if (RestockRequest::query()->where('request_number', $row['request_number'])->exists()) {
                $this->line("Skipping {$row['request_number']} (already in MySQL)");
                $skipped++;

                continue;
            }

            $distributorId = $distributorMap[(int) $row['distributor_profile_id']] ?? null;
            $productId = $productMap[(int) $row['product_id']] ?? null;

            if (! $distributorId || ! $productId) {
                $this->warn("Skipping {$row['request_number']} (missing distributor or product mapping)");
                $skipped++;

                continue;
            }

            $request = RestockRequest::query()->create([
                'request_number' => $row['request_number'],
                'distributor_profile_id' => $distributorId,
                'product_id' => $productId,
                'quantity' => (int) $row['quantity'],
                'unit_price' => $row['unit_price'],
                'total_amount' => $row['total_amount'],
                'status' => $row['status'] ?? 'pending',
            ]);

            RestockRequest::query()
                ->whereKey($request->id)
                ->update([
                    'created_at' => $row['created_at'] ?? now(),
                    'updated_at' => $row['updated_at'] ?? now(),
                ]);

            $this->info("Imported {$row['request_number']}");
            $imported++;
        }

        return [$imported, $skipped];
    }

    private function mapUsers(PDO $sqlite): array
    {
        $map = [];

        foreach ($sqlite->query('SELECT id, email FROM users') as $row) {
            $mysqlUser = User::query()->where('email', $row['email'])->first();
            if ($mysqlUser) {
                $map[(int) $row['id']] = $mysqlUser->id;
            }
        }

        return $map;
    }

    private function mapProducts(PDO $sqlite): array
    {
        $map = [];

        foreach ($sqlite->query('SELECT id, name FROM products') as $row) {
            $mysqlProduct = Product::query()
                ->where('name', $row['name'])
                ->orderBy('id')
                ->first();

            if ($mysqlProduct) {
                $map[(int) $row['id']] = $mysqlProduct->id;
            }
        }

        return $map;
    }

    private function mapDistributors(PDO $sqlite): array
    {
        $map = [];

        foreach ($sqlite->query('SELECT id, user_id FROM distributor_profiles') as $row) {
            $userStmt = $sqlite->prepare('SELECT email FROM users WHERE id = ?');
            $userStmt->execute([(int) $row['user_id']]);
            $email = $userStmt->fetchColumn();

            if (! $email) {
                continue;
            }

            $mysqlProfile = DistributorProfile::query()
                ->whereHas('user', fn ($query) => $query->where('email', $email))
                ->first();

            if ($mysqlProfile) {
                $map[(int) $row['id']] = $mysqlProfile->id;
            }
        }

        return $map;
    }

    private function uniqueInquiryNumber(?string $inquiryNumber): string
    {
        $base = $inquiryNumber ?: 'INQ-IMPORT-'.now()->format('YmdHis');

        if (! Inquiry::query()->where('inquiry_number', $base)->exists()) {
            return $base;
        }

        $suffix = 1;
        $candidate = $base.'-'.$suffix;

        while (Inquiry::query()->where('inquiry_number', $candidate)->exists()) {
            $suffix++;
            $candidate = $base.'-'.$suffix;
        }

        return $candidate;
    }
}
