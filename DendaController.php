<?php

namespace App\Http\Controllers;

use App\Models\Denda;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DendaController extends Controller
{
    // ─── Blade view — semua data langsung di-pass ke view ─────────────────────

    public function index()
    {
        $user    = Auth::user();
        $ckdcust = $user->ckdcust ?? null;

        $base = DB::table('ytdenda as d')
            ->join('ymcust as c', 'c.ckdcust', '=', 'd.ckdcust_liable')
            ->when($ckdcust, fn ($q) => $q->where('d.ckdcust_liable', $ckdcust));

        // ── Summary cards ──────────────────────────────────────────────────────
        $totalDenda = (clone $base)->count();

        $totalOutstandingDenda = (clone $base)
            ->where('d.cstatus', Denda::STATUS_UNPAID)
            ->sum(DB::raw('d.ntotalharga - d.njmlbyr'));

        $totalDendaPaid = (clone $base)
            ->where('d.cstatus', Denda::STATUS_PAID)
            ->whereMonth('d.updated_at', now()->month)
            ->whereYear('d.updated_at', now()->year)
            ->sum('d.njmlbyr');

        $totalDendaWaived = (clone $base)
            ->where('d.cstatus', Denda::STATUS_WAIVED)
            ->sum('d.ntotalharga');

        // ── List data untuk tabel ──────────────────────────────────────────────
        $dendaList = (clone $base)
            ->select([
                'd.nidenda',
                'c.cnmcust',
                'd.cjenis',
                'd.dtgl_mulai                               as dtglterbit',
                'd.dtgl_selesai                             as dtgljatuh',
                DB::raw("COALESCE(d.cnosj, d.cnofakturpnj)  as cnoreferensi"),
                'd.ntotalharga                              as njmldenda',
                'd.njmlbyr',
                'd.cstatus',
            ])
            ->orderByDesc('d.nidenda')
            ->get();

        $dendaJson = $dendaList->map(function ($item) {
            $totalAmount  = (float) ($item->njmldenda ?? 0);
            $paidAmount   = (float) ($item->njmlbyr ?? 0);
            $outstanding  = max($totalAmount - $paidAmount, 0);

            return [
                'id'             => (int) $item->nidenda,
                'penalty_number' => 'DND-' . str_pad((string) $item->nidenda, 6, '0', STR_PAD_LEFT),
                'customer'       => $item->cnmcust,
                'penalty_type'   => $item->cjenis,
                'issue_date'     => $item->dtglterbit,
                'due_date'       => $item->dtgljatuh,
                'reference'      => $item->cnoreferensi,
                'total_amount'   => $totalAmount,
                'paid_amount'    => $paidAmount,
                'outstanding'    => $outstanding,
                'status'         => $item->cstatus,
            ];
        })->values();

        return view('finance.penalty.index', compact(
            'totalDenda',
            'totalOutstandingDenda',
            'totalDendaPaid',
            'totalDendaWaived',
            'dendaList',
            'dendaJson',
        ));
    }

    // ─── Mark as Paid ─────────────────────────────────────────────────────────

    public function markPaid(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdmin();

        $denda = Denda::findOrFail($id);

        $denda->update([
            'njmlbyr' => $denda->ntotalharga,
            'cstatus' => Denda::STATUS_PAID,
        ]);

        return response()->json(['message' => 'Penalty marked as paid.']);
    }

    // ─── Waive ────────────────────────────────────────────────────────────────

    public function waive(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdmin();

        $denda = Denda::findOrFail($id);

        $denda->update([
            'cstatus' => Denda::STATUS_WAIVED,
        ]);

        return response()->json(['message' => 'Penalty waived.']);
    }

    // ─── Helper ───────────────────────────────────────────────────────────────

    private function authorizeAdmin(): void
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->isSuperAdmin()) {
            abort(403, 'Unauthorized.');
        }
    }
}
