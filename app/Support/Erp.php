<?php

namespace App\Support;

use Illuminate\Support\Facades\Session;

/**
 * Frontend-only data layer.
 *
 * No database, no migrations, no models — every "requisition" is a plain
 * nested array kept in the session. This is enough to make the 3 screens
 * fully interactive (create -> route -> approve) without needing MySQL,
 * SQLite, or any server-side storage beyond the session file driver.
 */
class Erp
{
    const SESSION_KEY = 'erp.requisitions';

    /** Read all requisitions, seeding demo data the first time. */
    public static function all(): array
    {
        if (! Session::has(self::SESSION_KEY)) {
            Session::put(self::SESSION_KEY, self::seed());
        }

        return Session::get(self::SESSION_KEY);
    }

    public static function find(int $id): ?array
    {
        foreach (self::all() as $r) {
            if ($r['id'] === $id) {
                return $r;
            }
        }

        return null;
    }

    public static function save(array $requisition): void
    {
        $all = self::all();
        $found = false;

        foreach ($all as $i => $r) {
            if ($r['id'] === $requisition['id']) {
                $all[$i] = $requisition;
                $found = true;
                break;
            }
        }

        if (! $found) {
            $all[] = $requisition;
        }

        Session::put(self::SESSION_KEY, $all);
    }

    public static function nextId(): int
    {
        $all = self::all();

        return $all ? max(array_column($all, 'id')) + 1 : 1;
    }

    public static function nextCode(): string
    {
        return 'PR-' . date('Y') . '-' . str_pad((string) (count(self::all()) + 90), 3, '0', STR_PAD_LEFT);
    }

    /** Convert nested arrays to stdClass so Blade can use -> like it would with Eloquent. */
    public static function obj(array $data): object
    {
        return json_decode(json_encode($data));
    }

    public static function urgencyBadge(string $urgency): string
    {
        return match ($urgency) {
            'High' => 'bg-red-500 text-white',
            'Medium' => 'bg-orange-400 text-white',
            default => 'bg-green-500 text-white',
        };
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'pending_approval' => 'Pending Approval',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => 'Draft',
        };
    }

    public static function lineTotal(array $item): float
    {
        return round(($item['qty'] ?? 0) * ($item['unit_price'] ?? 0), 2);
    }

    /** Demo data matching the reference screens, seeded once per session. */
    protected static function seed(): array
    {
        return [
            [
                'id' => 1,
                'code' => 'PR-2026-090',
                'title' => 'Premium SaaS + Onboarding Licenses',
                'requestor' => 'Johny papa',
                'department' => 'Marketing',
                'purpose' => 'Marketing team tooling for Q2 campaign onboarding.',
                'needed_by' => '2026-07-15',
                'created_at' => '2026-06-29 09:00:00',
                'subtotal' => 12500, 'tax_rate' => 0, 'total' => 12500,
                'urgency' => 'High', 'workflow_type' => 'sequential',
                'current_step' => 2, 'status' => 'pending_approval',
                'items' => [
                    ['item_name' => 'Premium SaaS', 'description' => 'Annual seats', 'qty' => 10, 'unit' => 'seat', 'unit_price' => 1200],
                    ['item_name' => 'Onboarding Licenses', 'description' => 'New hire onboarding', 'qty' => 5, 'unit' => 'license', 'unit_price' => 100],
                ],
                'approval_steps' => [
                    ['step_number' => 1, 'role_label' => 'Manager Approval', 'approver_name' => 'Sarah Jenkins', 'required' => true, 'status' => 'approved', 'comment' => null],
                    ['step_number' => 2, 'role_label' => 'Department Head Approval', 'approver_name' => 'You (Dept Head)', 'required' => true, 'status' => 'pending', 'comment' => null],
                    ['step_number' => 3, 'role_label' => 'Finance Approval', 'approver_name' => 'Finance Manager', 'required' => true, 'status' => 'pending', 'comment' => null],
                ],
            ],
            [
                'id' => 2,
                'code' => 'PR-2026-091',
                'title' => 'Dev tooling licenses',
                'requestor' => 'Jane Doe',
                'department' => 'Eng',
                'purpose' => 'Engineering tooling renewal.',
                'needed_by' => '2026-07-20',
                'created_at' => '2026-06-28 09:00:00',
                'subtotal' => 4200, 'tax_rate' => 0, 'total' => 4200,
                'urgency' => 'Medium', 'workflow_type' => 'sequential',
                'current_step' => 1, 'status' => 'pending_approval',
                'items' => [
                    ['item_name' => 'Dev tooling seats', 'description' => 'Renewal', 'qty' => 12, 'unit' => 'seat', 'unit_price' => 350],
                ],
                'approval_steps' => [
                    ['step_number' => 1, 'role_label' => 'Manager Approval', 'approver_name' => 'Manager', 'required' => true, 'status' => 'pending', 'comment' => null],
                ],
            ],
            [
                'id' => 3,
                'code' => 'PR-2026-092',
                'title' => 'Office supplies',
                'requestor' => 'Emily Johnson',
                'department' => 'Ops',
                'purpose' => 'Routine office supplies restock.',
                'needed_by' => '2026-07-10',
                'created_at' => '2026-06-29 09:00:00',
                'subtotal' => 850, 'tax_rate' => 0, 'total' => 850,
                'urgency' => 'Low', 'workflow_type' => 'sequential',
                'current_step' => 1, 'status' => 'pending_approval',
                'items' => [
                    ['item_name' => 'Office supplies', 'description' => 'Restock', 'qty' => 1, 'unit' => 'box', 'unit_price' => 850],
                ],
                'approval_steps' => [
                    ['step_number' => 1, 'role_label' => 'Manager Approval', 'approver_name' => 'Manager', 'required' => true, 'status' => 'pending', 'comment' => null],
                ],
            ],
        ];
    }
}
