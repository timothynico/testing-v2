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

        return view('finance.penalty.index', compact(
            'totalDenda',
            'totalOutstandingDenda',
            'totalDendaPaid',
            'totalDendaWaived',
            'dendaList',
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
