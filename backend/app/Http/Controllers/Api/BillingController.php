<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BillingStatement;
use App\Models\ProformaInvoice;
use App\Models\User;
use App\Services\BillingService;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function __construct(private BillingService $billing) {}

    // =====================================================================
    // GET /billing
    // Returns the current plan, current open-period summary, last statements,
    // and recent invoices (used by per_invoice users and as quick overview)
    // =====================================================================
    public function overview()
    {
        $ownerId = $this->getOwnerId();
        $owner   = User::find($ownerId);
        $summary = $this->billing->currentPeriodSummary($owner);

        $statements = $owner->billingStatements()
            ->take(12)
            ->get()
            ->map(fn($s) => $this->formatStatement($s));

        // Collect all poster_ids in this account (owner + employees)
        $accountIds = User::where(function ($q) use ($ownerId) {
            $q->where('id', $ownerId)->orWhere('registered_by', $ownerId);
        })->pluck('id');

        $recentInvoices = ProformaInvoice::with('proforma.brand')
            ->whereHas('proforma', fn($q) => $q->whereIn('poster_id', $accountIds))
            ->orderByDesc('created_at')
            ->take(10)
            ->get()
            ->map(fn($inv) => $this->formatInvoice($inv));

        return response()->json([
            'success'          => true,
            'billing_plan'     => $owner->billing_plan ?? 'per_invoice',
            'current_period'   => $summary,
            'statements'       => $statements,
            'recent_invoices'  => $recentInvoices,
        ]);
    }

    // =====================================================================
    // GET /billing/invoices?page=1
    // Paginated list of all ProformaInvoices for this account
    // =====================================================================
    public function invoices(Request $request)
    {
        $ownerId    = $this->getOwnerId();
        $accountIds = User::where(function ($q) use ($ownerId) {
            $q->where('id', $ownerId)->orWhere('registered_by', $ownerId);
        })->pluck('id');

        $invoices = ProformaInvoice::with('proforma.brand')
            ->whereHas('proforma', fn($q) => $q->whereIn('poster_id', $accountIds))
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json([
            'success'    => true,
            'data'       => $invoices->map(fn($inv) => $this->formatInvoice($inv)),
            'pagination' => [
                'current_page' => $invoices->currentPage(),
                'last_page'    => $invoices->lastPage(),
                'per_page'     => $invoices->perPage(),
                'total'        => $invoices->total(),
            ],
        ]);
    }

    // =====================================================================
    // PUT /billing/plan
    // Body: { "plan": "monthly" | "weekly" | "per_invoice" }
    // =====================================================================
    public function updatePlan(Request $request)
    {
        $validated = $request->validate([
            'plan' => ['required', 'in:per_invoice,monthly,weekly'],
        ]);

        $owner = User::find($this->getOwnerId());
        $owner->update(['billing_plan' => $validated['plan']]);

        return response()->json([
            'success' => true,
            'message' => 'Billing plan updated to ' . $validated['plan'],
            'billing_plan' => $validated['plan'],
        ]);
    }

    // =====================================================================
    // GET /billing/statements
    // Paginated list of billing statements for this owner
    // =====================================================================
    public function statements(Request $request)
    {
        $ownerId = $this->getOwnerId();

        $statements = BillingStatement::where('owner_id', $ownerId)
            ->orderBy('period_start', 'desc')
            ->paginate(12);

        return response()->json([
            'success' => true,
            'data'    => $statements->map(fn($s) => $this->formatStatement($s)),
            'pagination' => [
                'current_page' => $statements->currentPage(),
                'last_page'    => $statements->lastPage(),
                'per_page'     => $statements->perPage(),
                'total'        => $statements->total(),
            ],
        ]);
    }

    // =====================================================================
    // GET /billing/statements/{sku}
    // Full statement detail with per-proforma breakdown
    // =====================================================================
    public function statementDetail(string $sku)
    {
        $ownerId   = $this->getOwnerId();
        $statement = BillingStatement::where('sku', $sku)
            ->where('owner_id', $ownerId)
            ->first();

        if (!$statement) {
            return response()->json(['success' => false, 'message' => 'Statement not found'], 404);
        }

        // Collect all poster_ids in this account
        $accountIds = User::where(function ($q) use ($ownerId) {
            $q->where('id', $ownerId)->orWhere('registered_by', $ownerId);
        })->pluck('id');

        // Fetch proforma invoices belonging to this period
        $invoices = ProformaInvoice::with(['proforma.brand', 'proforma.poster'])
            ->whereHas('proforma', function ($q) use ($accountIds, $statement) {
                $q->whereIn('poster_id', $accountIds)
                  ->whereBetween('created_at', [
                      $statement->period_start->copy()->startOfDay(),
                      $statement->period_end->copy()->endOfDay(),
                  ]);
            })
            ->get()
            ->map(fn($inv) => [
                'proforma_id'    => $inv->proforma_id,
                'file_number'    => $inv->proforma->file_number ?? null,
                'brand'          => $inv->proforma->brand->name ?? null,
                'car_model'      => $inv->proforma->model ?? null,
                'type'           => $inv->type,
                'requested_by'   => $inv->proforma->poster->name ?? null,
                'created_at'     => $inv->created_at->toDateTimeString(),
                'subtotal'       => round((float) $inv->total_amount / 1.15, 2),
                'vat_amount'     => round((float) $inv->total_amount - ((float) $inv->total_amount / 1.15), 2),
                'total_amount'   => (float) $inv->total_amount,
                'is_paid'        => (bool) $inv->is_paid,
            ]);

        return response()->json([
            'success'   => true,
            'statement' => $this->formatStatement($statement),
            'invoices'  => $invoices,
        ]);
    }

    // =====================================================================
    // Private helpers
    // =====================================================================

    private function getOwnerId(): int
    {
        $user = auth()->user();
        return $user->registered_by ?? $user->id;
    }

    private function formatStatement(BillingStatement $s): array
    {
        return [
            'sku'            => $s->sku,
            'period_type'    => $s->period_type,
            'period_start'   => $s->period_start->toDateString(),
            'period_end'     => $s->period_end->toDateString(),
            'proforma_count' => $s->proforma_count,
            'subtotal'       => (float) $s->subtotal,
            'vat_amount'     => (float) $s->vat_amount,
            'total_amount'   => (float) $s->total_amount,
            'status'         => $s->status,
            'paid_at'        => $s->paid_at?->toDateTimeString(),
            'payment_method' => $s->payment_method,
            // Chapa field — populated later when payment integration is added
            'checkout_url'   => $s->chapa_checkout_url,
        ];
    }

    private function formatInvoice(ProformaInvoice $inv): array
    {
        $total    = (float) $inv->total_amount;
        $subtotal = round($total / 1.15, 2);
        $vat      = round($total - $subtotal, 2);

        return [
            'sku'          => $inv->sku,
            'proforma_id'  => $inv->proforma_id,
            'file_number'  => $inv->proforma->file_number ?? null,
            'brand'        => $inv->proforma->brand->name ?? null,
            'car_model'    => $inv->proforma->model ?? null,
            'type'         => $inv->type,
            'subtotal'     => $subtotal,
            'vat_amount'   => $vat,
            'total_amount' => $total,
            'is_paid'      => (bool) $inv->is_paid,
            'created_at'   => $inv->created_at->toDateTimeString(),
            // Chapa-ready: checkout_url populated when payment integration is added
            'checkout_url' => null,
        ];
    }
}
