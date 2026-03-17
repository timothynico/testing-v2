<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DendaSeeder extends Seeder
{
    public function run(): void
    {
        // ─── STEP 1: Kosongkan tabel ──────────────────────────────────────────
        DB::table('ytdenda')->truncate();
        $this->command->info('✓ Tabel ytdenda dikosongkan.');

        // ─── STEP 2: Isi data dasar dari ytbpbdtl ────────────────────────────
        $this->command->info('→ Mengambil data dari ytbpbdtl...');

        $dtls = DB::table('ytbpbdtl')
            ->where(function ($q) {
                $q->where('nreject_qty', '>', 0)
                  ->orWhere('nmissing_qty', '>', 0);
            })
            ->get();

        $this->command->info("  Ditemukan {$dtls->count()} baris.");

        $bar = $this->command->getOutput()->createProgressBar($dtls->count());
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% — %message%');
        $bar->setMessage('Memulai...');
        $bar->start();

        $inserted   = 0;
        $skipped_sj = 0;

        foreach ($dtls as $dtl) {
            $sj = DB::table('ytsjhdr')
                    ->where('cnosj', $dtl->cnosj)
                    ->first();

            if (!$sj) {
                $skipped_sj++;
                $bar->setMessage("Skip: SJ {$dtl->cnosj} tidak ditemukan");
                $bar->advance();
                continue;
            }

            $base = [
                'cnosj'          => $dtl->cnosj,
                'cno_grn'        => $dtl->cno_grn,
                'ckdbrg'         => $dtl->cpallet_type,
                'dtgl_mulai'     => Carbon::parse($dtl->created_at)->toDateString(),
                'ckdcust_liable' => $sj->ckdcust_from,
                'njmlbyr'        => 0,
                'cstatus'        => 'unpaid',   // was: 'open'
                'created_at'     => now(),
                'updated_at'     => now(),
            ];

            // nreject_qty → cjenis = 'damaged'  (was: 'reject')
            if ($dtl->nreject_qty > 0) {
                DB::table('ytdenda')->insert(array_merge($base, [
                    'nqty_denda' => $dtl->nreject_qty,
                    'cjenis'     => 'damaged',
                ]));
                $inserted++;
            }

            // nmissing_qty → cjenis = 'lost'  (was: 'missing')
            if ($dtl->nmissing_qty > 0) {
                DB::table('ytdenda')->insert(array_merge($base, [
                    'nqty_denda' => $dtl->nmissing_qty,
                    'cjenis'     => 'lost',
                ]));
                $inserted++;
            }

            $bar->setMessage("Insert: {$dtl->cnosj}");
            $bar->advance();
        }

        $bar->setMessage('Selesai.');
        $bar->finish();
        $this->command->newLine(2);
        $this->command->info("✓ Step 2 selesai: {$inserted} baris diinsert, {$skipped_sj} dilewati (SJ tidak ditemukan).");

        // ─── STEP 3: Update cnokontrak, nnominal, ntotalharga ─────────────────
        $this->command->info('→ Mengupdate cnokontrak, nnominal, ntotalharga...');

        $dendas = DB::table('ytdenda')->get();

        $bar2 = $this->command->getOutput()->createProgressBar($dendas->count());
        $bar2->setFormat(' %current%/%max% [%bar%] %percent:3s%% — %message%');
        $bar2->setMessage('Memulai...');
        $bar2->start();

        $updated     = 0;
        $skipped_agr = 0;

        foreach ($dendas as $denda) {
            // Customer internal (COMP*) tidak perlu kontrak & harga
            if (str_starts_with($denda->ckdcust_liable, 'COMP')) {
                $bar2->setMessage("Skip COMP: {$denda->ckdcust_liable}");
                $bar2->advance();
                continue;
            }

            // damaged → prefix ckdbrg '02' → pakai npenalty_damaged
            // lost    → prefix ckdbrg '01' → pakai npenalty_lost
            $penalty_col = $denda->cjenis === 'damaged' ? 'npenalty_damaged' : 'npenalty_lost';

            $agr = DB::table('ytagrdirhdr as h')
                ->join('ytagrdirdtl as d', 'h.nidagrdir', '=', 'd.nidagrdir')
                ->join('mbasic as mb', 'mb.cbasic', '=', 'd.cpallettype')
                ->where('h.ckdcust', $denda->ckdcust_liable)
                ->where('mb.ckdbrg', $denda->ckdbrg)
                ->orderByDesc('h.created_at')
                ->select(
                    'h.cnokontrak',
                    "d.{$penalty_col} as nnominal"
                )
                ->first();

            if (!$agr || is_null($agr->nnominal)) {
                $skipped_agr++;
                $bar2->setMessage("Skip: agr tidak ditemukan untuk {$denda->ckdbrg} / {$denda->ckdcust_liable}");
                $bar2->advance();
                continue;
            }

            DB::table('ytdenda')
                ->where('nidenda', $denda->nidenda)
                ->update([
                    'cnokontrak'  => $agr->cnokontrak,
                    'nnominal'    => $agr->nnominal,
                    'ntotalharga' => $denda->nqty_denda * $agr->nnominal,
                    'updated_at'  => now(),
                ]);

            $updated++;
            $bar2->setMessage("Update nidenda #{$denda->nidenda}");
            $bar2->advance();
        }

        $bar2->setMessage('Selesai.');
        $bar2->finish();
        $this->command->newLine(2);
        $this->command->info("✓ Step 3 selesai: {$updated} baris diupdate, {$skipped_agr} dilewati (agr tidak ditemukan).");

        // ─── Summary ──────────────────────────────────────────────────────────
        $this->command->newLine();
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ SUMMARY ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $total         = DB::table('ytdenda')->count();
        $denganNominal = DB::table('ytdenda')->whereNotNull('nnominal')->count();
        $tanpaNominal  = DB::table('ytdenda')->whereNull('nnominal')->count();
        $totalDamaged  = DB::table('ytdenda')->where('cjenis', 'damaged')->count();
        $totalLost     = DB::table('ytdenda')->where('cjenis', 'lost')->count();

        $this->command->table(
            ['Keterangan', 'Jumlah'],
            [
                ['Total denda',              $total],
                ['Jenis damaged',            $totalDamaged],
                ['Jenis lost',               $totalLost],
                ['Terisi nominal',           $denganNominal],
                ['Tanpa nominal (agr miss)', $tanpaNominal],
                ['SJ tidak ditemukan',       $skipped_sj],
            ]
        );
    }
}
