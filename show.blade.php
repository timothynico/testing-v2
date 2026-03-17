@extends('layouts.app')

@section('title', __('Penalty Details'))

@section('header-left')
    <div>
        <h2 class="h5 fw-semibold mb-0">{{ __('Penalty Details') }}</h2>
        <span class="text-muted small">{{ __('Manage customer liable and penalty amount') }}</span>
    </div>
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <span><i class="bi bi-file-text me-2"></i>{{ __('Penalty Information') }}</span>
            <a href="{{ route('denda.index') }}" class="btn btn-sm btn-light">
                <i class="bi bi-arrow-left me-1"></i>{{ __('Back to List') }}
            </a>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <small class="text-muted d-block">{{ __('Penalty No') }}</small>
                    <strong>DND-{{ str_pad((string) $denda->nidenda, 6, '0', STR_PAD_LEFT) }}</strong>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">{{ __('Type') }}</small>
                    <span class="badge bg-secondary text-uppercase">{{ $denda->cjenis }}</span>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">{{ __('Penalty Qty') }}</small>
                    <strong>{{ number_format((float) ($denda->nqty_denda ?? 0), 0, ',', '.') }}</strong>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">{{ __('Status') }}</small>
                    <span class="badge bg-{{ $denda->cstatus === 'paid' ? 'success' : ($denda->cstatus === 'waived' ? 'secondary' : 'warning') }}">
                        {{ strtoupper($denda->cstatus) }}
                    </span>
                </div>

                <div class="col-md-4">
                    <small class="text-muted d-block">{{ __('No SJ') }}</small>
                    <strong>{{ $denda->cnosj ?? '-' }}</strong>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">{{ __('No GRN') }}</small>
                    <strong>{{ $denda->cno_grn ?? '-' }}</strong>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">{{ __('Pallet Type') }}</small>
                    <strong>{{ $denda->ckdbrg ?? '-' }}</strong>
                </div>

                <div class="col-md-4">
                    <small class="text-muted d-block">{{ __('Contract') }}</small>
                    <strong>{{ $denda->cnokontrak ?? '-' }}</strong>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">{{ __('Amount') }}</small>
                    <strong>Rp {{ number_format((float) ($denda->nnominal ?? 0), 0, ',', '.') }}</strong>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">{{ __('Total Price') }}</small>
                    <strong>Rp {{ number_format((float) ($denda->ntotalharga ?? 0), 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>
    </div>

    @if(Auth::user()->isAdmin() || Auth::user()->isSuperAdmin())
        <div class="card">
            <div class="card-header">{{ __('Edit Customer Liable') }}</div>
            <div class="card-body">
                <form method="POST" action="{{ route('denda.update-liable', $denda->nidenda) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">{{ __('Customer Liable') }}</label>
                        <select name="ckdcust_liable" class="form-select @error('ckdcust_liable') is-invalid @enderror">
                            @foreach($liableOptions as $code => $name)
                                <option value="{{ $code }}" @selected($code === $denda->ckdcust_liable)>
                                    {{ $name }} ({{ $code }})
                                </option>
                            @endforeach
                        </select>
                        @error('ckdcust_liable')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">
                            {{ __('Options are taken from the current customer and related customers in ytbpbhdr/ytbpbdtl tables.') }}
                        </small>
                    </div>

                    <button class="btn btn-success" type="submit">
                        <i class="bi bi-save me-1"></i>{{ __('Save Changes') }}
                    </button>
                </form>
            </div>
        </div>
    @endif
@endsection
