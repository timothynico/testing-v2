<?php

namespace App\Http\Controllers;

use App\Models\Denda;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DendaController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $ckdcust = $user->ckdcust ?? null;

        $base = DB::table('ytdenda as d')
            ->join('ymcust as c', 'c.ckdcust', '=', 'd.ckdcust_liable')
            ->when($ckdcust, fn ($q) => $q->where('d.ckdcust_liable', $ckdcust));

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

        $dendaList = (clone $base)
            ->select([
                'd.nidenda',
                'c.cnmcust',
                'd.cjenis',
                'd.dtgl_mulai as dtglterbit',
                'd.dtgl_selesai as dtgljatuh',
                DB::raw('COALESCE(d.cnosj, d.cnofakturpnj) as cnoreferensi'),
                'd.ntotalharga as njmldenda',
                'd.njmlbyr',
                'd.cstatus',
            ])
            ->orderByDesc('d.nidenda')
            ->get();

        $dendaJson = $dendaList->map(function ($item) {
            $totalAmount = (float) ($item->njmldenda ?? 0);
            $paidAmount = (float) ($item->njmlbyr ?? 0);
            $outstanding = max($totalAmount - $paidAmount, 0);

            return [
                'id' => (int) $item->nidenda,
                'penalty_number' => 'DND-' . str_pad((string) $item->nidenda, 6, '0', STR_PAD_LEFT),
                'customer' => $item->cnmcust,
                'penalty_type' => $item->cjenis,
                'issue_date' => $item->dtglterbit,
                'due_date' => $item->dtgljatuh,
                'reference' => $item->cnoreferensi,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'outstanding' => $outstanding,
                'status' => $item->cstatus,
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

    public function show(int $id): View
    {
        $denda = DB::table('ytdenda as d')
            ->leftJoin('ymcust as c', 'c.ckdcust', '=', 'd.ckdcust_liable')
            ->where('d.nidenda', $id)
            ->select('d.*', 'c.cnmcust as liable_name')
            ->first();

        abort_unless($denda, 404);

        $this->authorizeDendaAccess($denda->ckdcust_liable ?? null);

        $liableOptions = $this->buildLiableCustomerOptions($denda);

        return view('finance.penalty.show', [
            'denda' => $denda,
            'liableOptions' => $liableOptions,
        ]);
    }

    public function updateLiable(Request $request, int $id): RedirectResponse
    {
        $this->authorizeAdmin();

        $denda = DB::table('ytdenda')->where('nidenda', $id)->first();
        abort_unless($denda, 404);

        $liableOptions = $this->buildLiableCustomerOptions($denda);
        $allowedCodes = array_keys($liableOptions);

        $validated = $request->validate([
            'ckdcust_liable' => ['required', 'string', 'in:' . implode(',', $allowedCodes)],
        ]);

        $newLiable = $validated['ckdcust_liable'];

        DB::transaction(function () use ($denda, $newLiable) {
            $payload = [
                'ckdcust_liable' => $newLiable,
                'updated_at' => now(),
            ];

            if (str_starts_with($newLiable, 'COMP')) {
                $payload['cnokontrak'] = null;
                $payload['nnominal'] = null;
                $payload['ntotalharga'] = 0;
            } else {
                $penaltyColumn = $denda->cjenis === 'damaged' ? 'npenalty_damaged' : 'npenalty_lost';

                $agreement = DB::table('ytagrdirhdr as h')
                    ->join('ytagrdirdtl as d', 'h.nidagrdir', '=', 'd.nidagrdir')
                    ->join('mbasic as mb', 'mb.cbasic', '=', 'd.cpallettype')
                    ->where('h.ckdcust', $newLiable)
                    ->where('mb.ckdbrg', $denda->ckdbrg)
                    ->orderByDesc('h.created_at')
                    ->select('h.cnokontrak', DB::raw("d.{$penaltyColumn} as nnominal"))
                    ->first();

                if (!$agreement || is_null($agreement->nnominal)) {
                    abort(422, 'Kontrak/nominal untuk customer liable tidak ditemukan.');
                }

                $payload['cnokontrak'] = $agreement->cnokontrak;
                $payload['nnominal'] = $agreement->nnominal;
                $payload['ntotalharga'] = ((float) $denda->nqty_denda) * ((float) $agreement->nnominal);
            }

            $newTotal = (float) ($payload['ntotalharga'] ?? 0);
            $newPaid = min((float) $denda->njmlbyr, $newTotal);
            $payload['njmlbyr'] = $newPaid;

            if ($denda->cstatus !== Denda::STATUS_WAIVED) {
                $payload['cstatus'] = $newPaid >= $newTotal && $newTotal > 0
                    ? Denda::STATUS_PAID
                    : Denda::STATUS_UNPAID;
            }

            DB::table('ytdenda')->where('nidenda', $denda->nidenda)->update($payload);
        });

        return redirect()
            ->route('denda.show', $id)
            ->with('success', 'Customer liable berhasil diperbarui dan nominal denda sudah disesuaikan.');
    }

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

    public function waive(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdmin();

        $denda = Denda::findOrFail($id);

        $denda->update([
            'cstatus' => Denda::STATUS_WAIVED,
        ]);

        return response()->json(['message' => 'Penalty waived.']);
    }

    private function buildLiableCustomerOptions(object $denda): array
    {
        $codes = collect([$denda->ckdcust_liable]);

        $headerCodes = $this->extractCustomerCodesFromTable(
            'ytbpbhdr',
            array_filter([
                ['cnosj', $denda->cnosj ?? null],
                ['cno_grn', $denda->cno_grn ?? null],
            ])
        );

        $detailCodes = $this->extractCustomerCodesFromTable(
            'ytbpbdtl',
            array_filter([
                ['cnosj', $denda->cnosj ?? null],
                ['cno_grn', $denda->cno_grn ?? null],
            ])
        );

        $codes = $codes->merge($headerCodes)->merge($detailCodes)->filter()->unique()->values();

        $customers = DB::table('ymcust')
            ->whereIn('ckdcust', $codes)
            ->pluck('cnmcust', 'ckdcust');

        $options = [];
        foreach ($codes as $code) {
            $options[$code] = $customers[$code] ?? $code;
        }

        return $options;
    }

    private function extractCustomerCodesFromTable(string $table, array $filters): array
    {
        if (!Schema::hasTable($table)) {
            return [];
        }

        $columnCandidates = ['ckdcust', 'ckdcust_from', 'ckdcust_to', 'ckdcust_liable'];
        $columns = Schema::getColumnListing($table);

        $selectable = array_values(array_intersect($columnCandidates, $columns));
        if (empty($selectable)) {
            return [];
        }

        $query = DB::table($table)->select($selectable);

        foreach ($filters as [$column, $value]) {
            if ($value !== null && in_array($column, $columns, true)) {
                $query->orWhere($column, $value);
            }
        }

        return $query->limit(50)->get()->flatMap(function ($row) use ($selectable) {
            return collect($selectable)->map(fn ($col) => $row->{$col} ?? null);
        })->filter()->unique()->values()->all();
    }

    private function authorizeAdmin(): void
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->isSuperAdmin()) {
            abort(403, 'Unauthorized.');
        }
    }

    private function authorizeDendaAccess(?string $liableCode): void
    {
        $user = Auth::user();

        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return;
        }

        if (($user->ckdcust ?? null) !== $liableCode) {
            abort(403, 'Unauthorized.');
        }
    }
}
