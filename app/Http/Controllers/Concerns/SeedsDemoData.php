<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\Session;

/**
 * Small helper used by the controllers to keep a working set of
 * requisitions in the session, so the Login -> Create -> Route for
 * Approval -> Approval Queue flow is fully clickable without a database.
 */
trait SeedsDemoData
{
    protected function requisitions(): array
    {
        if (! Session::has('requisitions')) {
            Session::put('requisitions', $this->defaultRequisitions());
        }

        return Session::get('requisitions');
    }

    protected function saveRequisitions(array $requisitions): void
    {
        Session::put('requisitions', $requisitions);
    }

    protected function serviceCatalog(): array
    {
        return [
            ['name' => 'Premium SaaS License', 'category' => 'Software', 'unit' => 'Service', 'price' => 1200],
            ['name' => 'Onboarding License', 'category' => 'Software', 'unit' => 'Service', 'price' => 100],
            ['name' => 'Office Supplies Bundle', 'category' => 'Supplies', 'unit' => 'Pack', 'price' => 65],
            ['name' => 'Field Equipment Rental', 'category' => 'Equipment', 'unit' => 'Service', 'price' => 340],
            ['name' => 'Freight & Logistics', 'category' => 'Logistics', 'unit' => 'Service', 'price' => 210],
            ['name' => 'Consulting Hours', 'category' => 'Professional', 'unit' => 'Hour', 'price' => 150],
        ];
    }

    protected function defaultRequisitions(): array
    {
        return [
            'PR-2026-080' => [
                'id' => 'PR-2026-080',
                'title' => 'Marketing Q3 Software Renewal',
                'requestor' => 'Johny Papa',
                'department' => 'Marketing',
                'needed_by' => '2026-07-10',
                'purpose' => 'Renewal of premium SaaS licenses and onboarding seats for the Marketing team.',
                'items' => [
                    ['name' => 'Premium SaaS License', 'qty' => 10, 'unit' => 'Service', 'price' => 1200],
                    ['name' => 'Onboarding License', 'qty' => 5, 'unit' => 'Service', 'price' => 100],
                ],
                'tax_rate' => 0,
                'status' => 'pending_approval',
                'urgency' => 'High',
                'date_submitted' => '2026-06-29',
                'approvers' => [
                    ['step' => 1, 'role' => 'Manager approval', 'name' => 'Sarah Jenkins', 'status' => 'approved'],
                    ['step' => 2, 'role' => 'Department Head Approval', 'name' => 'You (Dept Head)', 'status' => 'pending'],
                    ['step' => 3, 'role' => 'Finance approval', 'name' => 'Finance', 'status' => 'pending'],
                ],
                'budget_remaining' => 12500,
                'budget_total' => 50000,
                'budget_impact_area' => 'Marketing Q2',
                'budget_balance_before' => 15000,
            ],
            'PR-2026-081' => [
                'id' => 'PR-2026-081',
                'title' => 'Engineering Field Equipment',
                'requestor' => 'Jane Doe',
                'department' => 'Eng',
                'needed_by' => '2026-07-15',
                'purpose' => 'Replacement field equipment for the engineering team.',
                'items' => [
                    ['name' => 'Field Equipment Rental', 'qty' => 12, 'unit' => 'Service', 'price' => 340],
                ],
                'tax_rate' => 0,
                'status' => 'pending_approval',
                'urgency' => 'Medium',
                'date_submitted' => '2026-06-28',
                'approvers' => [
                    ['step' => 1, 'role' => 'Manager approval', 'name' => 'Sarah Jenkins', 'status' => 'pending'],
                    ['step' => 2, 'role' => 'Department Head Approval', 'name' => 'You (Dept Head)', 'status' => 'pending'],
                    ['step' => 3, 'role' => 'Finance approval', 'name' => 'Finance', 'status' => 'pending'],
                ],
                'budget_remaining' => 8000,
                'budget_total' => 20000,
                'budget_impact_area' => 'Engineering Q2',
                'budget_balance_before' => 8850,
            ],
            'PR-2026-090' => [
                'id' => 'PR-2026-090',
                'title' => 'Ops Office Supplies',
                'requestor' => 'Emily Johnson',
                'department' => 'Ops',
                'needed_by' => '2026-07-05',
                'purpose' => 'Monthly office supplies restock.',
                'items' => [
                    ['name' => 'Office Supplies Bundle', 'qty' => 13, 'unit' => 'Pack', 'price' => 65],
                ],
                'tax_rate' => 0,
                'status' => 'pending_approval',
                'urgency' => 'Low',
                'date_submitted' => '2026-06-29',
                'approvers' => [
                    ['step' => 1, 'role' => 'Manager approval', 'name' => 'Sarah Jenkins', 'status' => 'pending'],
                    ['step' => 2, 'role' => 'Department Head Approval', 'name' => 'You (Dept Head)', 'status' => 'pending'],
                    ['step' => 3, 'role' => 'Finance approval', 'name' => 'Finance', 'status' => 'pending'],
                ],
                'budget_remaining' => 3200,
                'budget_total' => 10000,
                'budget_impact_area' => 'Operations Q2',
                'budget_balance_before' => 4050,
            ],
        ];
    }

    protected function subtotal(array $requisition): float
    {
        $subtotal = 0;
        foreach ($requisition['items'] as $item) {
            $subtotal += $item['qty'] * $item['price'];
        }
        return $subtotal;
    }

    protected function total(array $requisition): float
    {
        $subtotal = $this->subtotal($requisition);
        return $subtotal + ($subtotal * ($requisition['tax_rate'] ?? 0) / 100);
    }
}
