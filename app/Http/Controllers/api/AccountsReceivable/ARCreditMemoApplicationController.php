<?php

namespace App\Http\Controllers\api\AccountsReceivable;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\ARCreditMemoApplication;

class ARCreditMemoApplicationController extends Controller
{
    /**
     * List AR credit memo applications with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        $customerCode = $request->get('customer_code');
        $sourceArId = $request->get('source_ar_id');
        $targetArId = $request->get('target_ar_id');
        $startDate = $request->get('start_date'); // YYYY-MM-DD
        $endDate = $request->get('end_date');     // YYYY-MM-DD
        $limit = intval($request->get('limit', 100));

        $query = ARCreditMemoApplication::query()
            ->with(['sourceAccountsReceivable', 'targetAccountsReceivable', 'creator'])
            ->orderBy('application_date', 'desc')
            ->orderBy('id', 'desc');

        if (!empty($sourceArId)) {
            $query->where('source_ar_id', $sourceArId);
        }
        if (!empty($targetArId)) {
            $query->where('target_ar_id', $targetArId);
        }

        if (!empty($customerCode)) {
            $query->where(function ($q) use ($customerCode) {
                $q->whereHas('sourceAccountsReceivable', function ($qq) use ($customerCode) {
                    $qq->where('customer_code', $customerCode);
                })
                ->orWhereHas('targetAccountsReceivable', function ($qq) use ($customerCode) {
                    $qq->where('customer_code', $customerCode);
                });
            });
        }

        if (!empty($startDate) && !empty($endDate)) {
            $query->whereBetween('application_date', [$startDate, $endDate]);
        } elseif (!empty($startDate)) {
            $query->whereDate('application_date', '>=', $startDate);
        } elseif (!empty($endDate)) {
            $query->whereDate('application_date', '<=', $endDate);
        }

        $data = $query->limit($limit)->get();

        return response()->json([
            'success' => true,
            'count' => $data->count(),
            'total_credit_amount' => round($data->sum('credit_amount'), 2),
            'data' => $data,
        ]);
    }
}