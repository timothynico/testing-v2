@extends('layouts.app')

@section('title', __('Detail Denda'))

@section('header-left')
    <div>
        <h2 class="h5 fw-semibold mb-0">{{ __('Detail Denda') }}</h2>
        <span class="text-muted small">{{ __('Kelola customer liable dan nominal denda') }}</span>
    </div>
@endsection

@section('content')

    {{-- ── Back button ─────────────────────────────────────────────────────────── --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <a href="{{ route('denda.index') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i>{{ __('Kembali ke List') }}
        </a>
    </div>

    {{-- ── Flash messages ──────────────────────────────────────────────────────── --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ── PHP helpers ─────────────────────────────────────────────────────────── --}}
    @php
        $totalAmount   = (float) ($denda->ntotalharga ?? 0);
        $paidAmount    = (float) ($denda->njmlbyr     ?? 0);
        $outstanding   = max($totalAmount - $paidAmount, 0);
        $penaltyNumber = 'DND-' . str_pad((string) $denda->nidenda, 6, '0', STR_PAD_LEFT);

        $statusMap = [
            'unpaid' => ['cls' => 'bg-danger',         'icon' => 'bi-x-circle',     'label' => __('UNPAID')],
            'paid'   => ['cls' => 'bg-success',        'icon' => 'bi-check-circle', 'label' => __('PAID')],
            'waived' => ['cls' => 'bg-info text-dark', 'icon' => 'bi-shield-check', 'label' => __('WAIVED')],
        ];
        $statusInfo = $statusMap[$denda->cstatus] ?? [
            'cls'   => 'bg-secondary',
            'icon'  => 'bi-question-circle',
            'label' => strtoupper($denda->cstatus),
        ];

        $typeMap = [
            'damaged'         => ['cls' => 'badge-damaged',         'icon' => 'bi-tools',            'label' => __('Damaged')],
            'lost'            => ['cls' => 'badge-lost',            'icon' => 'bi-question-diamond', 'label' => __('Lost')],
            'overdue_payment' => ['cls' => 'badge-overdue-payment', 'icon' => 'bi-calendar-x',       'label' => __('Overdue Payment')],
        ];
        $typeInfo = $typeMap[$denda->cjenis] ?? [
            'cls'  => 'bg-secondary',
            'icon' => 'bi-dash-circle',
            'label'=> $denda->cjenis,
        ];
    @endphp

    {{-- ── Summary stat cards ─────────────────────────────────────────────────── --}}
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1">{{ __('Total Harga') }}</p>
                            <h3 class="mb-0 fw-bold">Rp {{ number_format($totalAmount, 0, ',', '.') }}</h3>
                        </div>
                        <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                            <i class="bi bi-slash-circle fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1">{{ __('Outstanding') }}</p>
                            <h3 class="mb-0 fw-bold text-warning">Rp {{ number_format($outstanding, 0, ',', '.') }}</h3>
                        </div>
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-clock-history fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1">{{ __('Jumlah Bayar') }}</p>
                            <h3 class="mb-0 fw-bold text-success">Rp {{ number_format($paidAmount, 0, ',', '.') }}</h3>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-check-circle fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1">{{ __('Qty Denda') }}</p>
                            <h3 class="mb-0 fw-bold text-secondary">
                                {{ number_format((float) ($denda->nqty_denda ?? 0), 0, ',', '.') }}
                            </h3>
                        </div>
                        <div class="stat-icon bg-secondary bg-opacity-10 text-secondary">
                            <i class="bi bi-box-seam fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">

        {{-- ── Left : Informasi Denda ───────────────────────────────────────────── --}}
        <div class="{{ (Auth::user()->isAdmin() || Auth::user()->isSuperAdmin()) ? 'col-lg-8' : 'col-12' }}">
            <div class="card">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <span>
                        <i class="bi bi-file-text me-2"></i>{{ __('Informasi Denda') }}
                    </span>
                    <span class="badge bg-white text-success fw-semibold small">{{ $penaltyNumber }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <tbody>
                                <tr>
                                    <th class="px-3">{{ __('No Denda') }}</th>
                                    <td class="fw-semibold text-success px-3">{{ $penaltyNumber }}</td>
                                </tr>
                                <tr>
                                    <th class="px-3">{{ __('Jenis') }}</th>
                                    <td class="px-3">
                                        <span class="badge {{ $typeInfo['cls'] }} rounded-pill">
                                            <i class="bi {{ $typeInfo['icon'] }} me-1"></i>{{ $typeInfo['label'] }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="px-3">{{ __('Status') }}</th>
                                    <td class="px-3">
                                        <span class="badge {{ $statusInfo['cls'] }} rounded-pill">
                                            <i class="bi {{ $statusInfo['icon'] }} me-1"></i>{{ $statusInfo['label'] }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="px-3">{{ __('Customer Liable') }}</th>
                                    <td class="fw-medium px-3">{{ $denda->liable_name ?? $denda->ckdcust_liable }}</td>
                                </tr>
                                <tr>
                                    <th class="px-3">{{ __('No SJ') }}</th>
                                    <td class="px-3">
                                        @if($denda->cnosj)
                                            <span class="text-primary small">{{ $denda->cnosj }}</span>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="px-3">{{ __('No GRN') }}</th>
                                    <td class="px-3">
                                        @if($denda->cno_grn)
                                            <span class="text-primary small">{{ $denda->cno_grn }}</span>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="px-3">{{ __('Pallet Type') }}</th>
                                    <td class="px-3">{{ $denda->ckdbrg ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="px-3">{{ __('Kontrak') }}</th>
                                    <td class="px-3">{{ $denda->cnokontrak ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="px-3">{{ __('Nominal') }}</th>
                                    <td class="fw-medium px-3">Rp {{ number_format((float) ($denda->nnominal ?? 0), 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <th class="px-3">{{ __('Qty Denda') }}</th>
                                    <td class="px-3">{{ number_format((float) ($denda->nqty_denda ?? 0), 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- ── Amount summary boxes ───────────────────────────────── --}}
                    <div class="row g-2 mx-0 px-3 py-3 border-top">
                        <div class="col-4">
                            <div class="bg-light rounded p-3 text-center">
                                <p class="text-muted small mb-1">{{ __('Total Harga') }}</p>
                                <p class="fw-bold mb-0 small">Rp {{ number_format($totalAmount, 0, ',', '.') }}</p>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="bg-light rounded p-3 text-center">
                                <p class="text-muted small mb-1">{{ __('Jumlah Bayar') }}</p>
                                <p class="fw-bold text-success mb-0 small">Rp {{ number_format($paidAmount, 0, ',', '.') }}</p>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="bg-light rounded p-3 text-center">
                                <p class="text-muted small mb-1">{{ __('Outstanding') }}</p>
                                <p class="fw-bold {{ $outstanding > 0 ? 'text-danger' : 'text-muted' }} mb-0 small">
                                    Rp {{ number_format($outstanding, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Right : Edit Customer Liable (admin only) ───────────────────────── --}}
        @if(Auth::user()->isAdmin() || Auth::user()->isSuperAdmin())
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <i class="bi bi-person-gear me-2"></i>{{ __('Edit Customer Liable') }}
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            {{ __('Pilihan diambil dari customer saat ini dan customer terkait pada tabel ytbpbhdr/ytbpbdtl.') }}
                        </p>

                        <form method="POST" action="{{ route('penalty.update-liable', $denda->nidenda) }}" id="formUpdateLiable">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label small fw-semibold" for="ckdcust_liable">
                                    {{ __('Customer Liable') }}
                                </label>
                                <select name="ckdcust_liable" id="ckdcust_liable"
                                    class="form-select form-select-sm @error('ckdcust_liable') is-invalid @enderror">
                                    @foreach($liableOptions as $code => $name)
                                        <option value="{{ $code }}" @selected($code === $denda->ckdcust_liable)>
                                            {{ $name }} ({{ $code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('ckdcust_liable')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="alert alert-warning py-2 small mb-3">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                {{ __('Mengubah customer liable akan menghitung ulang total denda.') }}
                            </div>

                            <button class="btn btn-sm btn-success w-100" type="submit">
                                <i class="bi bi-save me-1"></i>{{ __('Simpan Perubahan') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif

    </div>

@endsection

@push('styles')
    <style>
        .table > :not(caption) > * > * {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
        }

        .table tbody th {
            background-color: #f8f9fa;
            font-weight: 600;
            font-size: 0.75rem;
            letter-spacing: 0.3px;
            color: #6c757d;
            width: 35%;
        }

        .table tbody tr { transition: background-color 0.15s ease; }
        .table tbody tr:hover { background-color: #f1f8f4; }

        .badge { font-weight: 500; font-size: 0.75rem; }
        .rounded-pill { border-radius: 50rem !important; }

        .stat-card {
            transition: transform 0.15s, box-shadow 0.15s;
            border: 1px solid #dee2e6;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .stat-icon {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            flex-shrink: 0;
        }

        .badge-damaged         { background-color: #6f42c1; color: #fff; }
        .badge-lost            { background-color: #dc3545; color: #fff; }
        .badge-overdue-payment { background-color: #0dcaf0; color: #000; }

        .form-select:focus {
            border-color: #198754;
            box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('formUpdateLiable')?.addEventListener('submit', function (e) {
                if (!confirm('{{ __("Ubah customer liable? Total denda akan dihitung ulang.") }}')) {
                    e.preventDefault();
                }
            });
        });
    </script>
@endpush
