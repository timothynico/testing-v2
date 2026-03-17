@extends('layouts.app')

@section('title', __('Penalty Monitoring'))

@section('header-left')
    <div>
        <h2 class="h5 fw-semibold mb-0">{{ __('Penalty Monitoring') }}</h2>
        <span class="text-muted small">{{ __('Track and manage customer penalties & fines') }}</span>
    </div>
@endsection

@section('content')

    <!-- Search & Filter Bar -->
    <div class="card mb-3">
        <div class="card-body py-2">

            <!-- Info Banner -->
            <div class="alert alert-info py-2 mb-3 small">
                <i class="bi bi-info-circle me-2"></i>
                <strong>{{ __('Penalty System') }}:</strong>
                {{ __('Penalties are automatically calculated based on late returns, damaged pallets, or overdue payments.') }}
            </div>

            {{-- ROW 1 : MONTH FILTER + SEARCH --}}
            <div class="d-flex flex-row align-items-center gap-2 mb-3">

                <!-- Month/Year Filter -->
                <div class="input-group input-group-sm flex-shrink-0" style="width:180px">
                    <span class="input-group-text bg-white text-muted">
                        <i class="bi bi-calendar3"></i>
                    </span>
                    <input type="text" class="form-control" id="monthYearFilter"
                        placeholder="{{ __('All Months') }}" readonly>
                    <button type="button" class="btn btn-outline-secondary" id="clearMonthFilter">
                        <i class="bi bi-x"></i>
                    </button>
                </div>

                <!-- Search -->
                <div class="flex-grow-1">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control" id="searchInput"
                            placeholder="{{ __('Search penalty number, customer...') }}">
                        <button type="button" class="btn btn-outline-secondary d-none" id="clearSearch">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- ROW 2 : STATUS PILLS + TYPE PILLS --}}
            <div class="d-flex align-items-center gap-2 flex-wrap pt-2 border-top" id="dendaStatusFilters">
                <span class="text-muted small fw-semibold me-2">{{ __('Status') }}</span>

                <button type="button" class="btn btn-sm btn-success rounded-pill status-filter active-filter"
                    data-status="all">
                    {{ __('All') }}
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill status-filter"
                    data-status="unpaid">
                    {{ __('Unpaid') }}
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill status-filter"
                    data-status="paid">
                    {{ __('Paid') }}
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill status-filter"
                    data-status="waived">
                    {{ __('Waived') }}
                </button>

                <span class="vr mx-1"></span>
                <span class="text-muted small fw-semibold me-2">{{ __('Type') }}</span>

                <button type="button" class="btn btn-sm btn-secondary rounded-pill type-filter active-type"
                    data-type="all">
                    {{ __('All Types') }}
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill type-filter"
                    data-type="damaged">
                    {{ __('Damaged') }}
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill type-filter"
                    data-type="lost">
                    {{ __('Lost') }}
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill type-filter"
                    data-type="overdue_payment">
                    {{ __('Overdue Payment') }}
                </button>

                @if(Auth::user()->isAdmin() || Auth::user()->isSuperAdmin())
                    <div class="ms-auto d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-danger" id="btnExportDenda">
                            <i class="bi bi-download me-1"></i>{{ __('Export') }}
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

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

    <!-- Summary Cards -->
    <div class="row mb-3" id="dendaSummaryCards">
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1">{{ __('Total Penalties') }}</p>
                            <h3 class="mb-0 fw-bold">{{ number_format($totalDenda ?? 0) }}</h3>
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
                            <p class="text-muted small mb-1">{{ __('Total Outstanding') }}</p>
                            <h3 class="mb-0 fw-bold text-warning">
                                Rp {{ number_format($totalOutstandingDenda ?? 0, 0, ',', '.') }}
                            </h3>
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
                            <p class="text-muted small mb-1">{{ __('Paid This Month') }}</p>
                            <h3 class="mb-0 fw-bold text-success">
                                Rp {{ number_format($totalDendaPaid ?? 0, 0, ',', '.') }}
                            </h3>
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
                            <p class="text-muted small mb-1">{{ __('Waived') }}</p>
                            <h3 class="mb-0 fw-bold text-secondary">
                                Rp {{ number_format($totalDendaWaived ?? 0, 0, ',', '.') }}
                            </h3>
                        </div>
                        <div class="stat-icon bg-secondary bg-opacity-10 text-secondary">
                            <i class="bi bi-shield-check fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center bg-success text-white">
            <span>
                <i class="bi bi-slash-circle-fill me-2"></i>{{ __('Penalty List') }}
            </span>
            <div class="d-flex gap-2 align-items-center">
                <div class="dropdown">
                    <button type="button" id="columnToggle"
                        class="header-action d-flex align-items-center gap-1 dropdown-toggle"
                        data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                        <i class="bi bi-gear"></i>
                        {{ __('Columns') }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" id="columnMenu" aria-labelledby="columnToggle">
                        <li class="dropdown-header">{{ __('Show/Hide Columns') }}</li>
                        <li>
                            <label class="dropdown-item">
                                <input class="form-check-input me-2 column-toggle" type="checkbox"
                                    value="penalty_number" id="col_penalty_number" checked>
                                {{ __('Penalty Number') }}
                            </label>
                        </li>
                        <li>
                            <label class="dropdown-item">
                                <input class="form-check-input me-2 column-toggle" type="checkbox"
                                    value="customer" id="col_customer" checked>
                                {{ __('Customer') }}
                            </label>
                        </li>
                        <li>
                            <label class="dropdown-item">
                                <input class="form-check-input me-2 column-toggle" type="checkbox"
                                    value="penalty_type" id="col_penalty_type" checked>
                                {{ __('Penalty Type') }}
                            </label>
                        </li>
                        <li>
                            <label class="dropdown-item">
                                <input class="form-check-input me-2 column-toggle" type="checkbox"
                                    value="issue_date" id="col_issue_date" checked>
                                {{ __('Issue Date') }}
                            </label>
                        </li>
                        <li>
                            <label class="dropdown-item">
                                <input class="form-check-input me-2 column-toggle" type="checkbox"
                                    value="due_date" id="col_due_date" checked>
                                {{ __('Due Date') }}
                            </label>
                        </li>
                        <li>
                            <label class="dropdown-item">
                                <input class="form-check-input me-2 column-toggle" type="checkbox"
                                    value="reference" id="col_reference" checked>
                                {{ __('Reference') }}
                            </label>
                        </li>
                        <li>
                            <label class="dropdown-item">
                                <input class="form-check-input me-2 column-toggle" type="checkbox"
                                    value="total_amount" id="col_total_amount" checked>
                                {{ __('Total Amount') }}
                            </label>
                        </li>
                        <li>
                            <label class="dropdown-item">
                                <input class="form-check-input me-2 column-toggle" type="checkbox"
                                    value="paid_amount" id="col_paid_amount" checked>
                                {{ __('Paid Amount') }}
                            </label>
                        </li>
                        <li>
                            <label class="dropdown-item">
                                <input class="form-check-input me-2 column-toggle" type="checkbox"
                                    value="outstanding" id="col_outstanding" checked>
                                {{ __('Outstanding') }}
                            </label>
                        </li>
                        <li>
                            <label class="dropdown-item">
                                <input class="form-check-input me-2 column-toggle" type="checkbox"
                                    value="status" id="col_status" checked>
                                {{ __('Status') }}
                            </label>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <button class="dropdown-item text-center small" id="resetColumns">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>{{ __('Reset to Default') }}
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="dendaTable">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3 py-2 col-penalty_number">{{ __('Penalty Number') }}</th>
                            <th class="px-3 py-2 col-customer">{{ __('Customer') }}</th>
                            <th class="px-3 py-2 text-center col-penalty_type">{{ __('Type') }}</th>
                            <th class="px-3 py-2 text-center col-issue_date">{{ __('Issue Date') }}</th>
                            <th class="px-3 py-2 text-center col-due_date">{{ __('Due Date') }}</th>
                            <th class="px-3 py-2 col-reference">{{ __('Reference') }}</th>
                            <th class="px-3 py-2 text-end col-total_amount">{{ __('Total Amount') }}</th>
                            <th class="px-3 py-2 text-end col-paid_amount">{{ __('Paid') }}</th>
                            <th class="px-3 py-2 text-end col-outstanding">{{ __('Outstanding') }}</th>
                            <th class="px-3 py-2 text-center col-status">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody id="dendaTableBody">
                        <!-- Populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer d-flex justify-content-between align-items-center py-2">
            <p class="text-muted small mb-0" id="paginationInfo">
                {{ __('Showing 0 to 0 of 0 penalties') }}
            </p>
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm mb-0" id="paginationControls"></ul>
            </nav>
        </div>
    </div>

    <!-- Penalty Detail Modal -->
    <div class="modal fade" id="dendaDetailModal" tabindex="-1" aria-labelledby="dendaDetailModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="dendaDetailModalLabel">
                        <i class="bi bi-slash-circle-fill me-2"></i>
                        {{ __('Penalty Detail') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="dendaDetailModalBody"></div>
                <div class="modal-footer">
                    @if(Auth::user()->isAdmin() || Auth::user()->isSuperAdmin())
                        <button type="button" class="btn btn-outline-success btn-sm" id="btnMarkPaid">
                            <i class="bi bi-check-circle me-1"></i>{{ __('Mark as Paid') }}
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnWaivePenalty">
                            <i class="bi bi-shield-check me-1"></i>{{ __('Waive Penalty') }}
                        </button>
                    @endif
                    <button type="button" class="btn btn-secondary btn-sm"
                        data-bs-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── FIX: gunakan json_encode agar Blade tidak salah hitung kurung --}}
    <script>
        const allDendaData = {!! json_encode($dendaJson) !!};
        const dendaDetailBaseUrl = "{{ url('denda') }}";
    </script>
@endsection

@push('styles')
    {{-- ── FIX 1: Flatpickr CORE CSS wajib dimuat sebelum plugin CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">

    <style>
        .table> :not(caption)>*>* {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
        }

        .table thead th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            color: #6c757d;
            border-bottom: 2px solid #dee2e6;
        }

        .table tbody tr {
            transition: background-color 0.15s ease;
            cursor: pointer;
        }

        .table tbody tr:hover { background-color: #f1f8f4; }
        .denda-row-clickable:hover { background-color: #e8f5e9 !important; }

        .badge { font-weight: 500; font-size: 0.75rem; }
        .rounded-pill { border-radius: 50rem !important; }
        .input-group-text { border-right: 0; }
        .input-group .form-control { border-left: 0; }
        .input-group .form-control:focus { border-left: 0; box-shadow: none; }
        .input-group:focus-within .input-group-text { border-color: #198754; }
        .input-group:focus-within .form-control     { border-color: #198754; }

        .pagination-sm .page-link { padding: 0.25rem 0.5rem; font-size: 0.8125rem; line-height: 1.5; }
        .pagination { gap: 0.25rem; }
        .page-item .page-link { border: 1px solid #dee2e6; color: #495057; border-radius: 0.25rem; transition: all 0.15s ease; }
        .page-item .page-link:hover  { background-color: #f8f9fa; border-color: #198754; color: #198754; }
        .page-item.active .page-link { background-color: #198754; border-color: #198754; color: white; }
        .page-item.disabled .page-link { background-color: #fff; border-color: #dee2e6; color: #6c757d; cursor: not-allowed; }

        /* ── FIX 2: active-filter pakai kelas konkret (btn-success), bukan btn-brand yang tidak ada --*/
        .status-filter.active-filter { background-color: #198754 !important; border-color: #198754 !important; color: white !important; }
        .type-filter.active-type     { background-color: #6c757d !important; border-color: #6c757d !important; color: white !important; }

        #columnMenu { min-width: 220px; max-width: 280px; }
        #columnMenu .dropdown-item { padding: 0.5rem 1rem; cursor: pointer; display: flex; align-items: center; font-size: 0.875rem; }
        #columnMenu .dropdown-item:hover  { background-color: #f8f9fa; }
        #columnMenu .dropdown-item:active { background-color: #e9ecef; }
        #columnMenu .form-check-input { cursor: pointer; margin-top: 0; flex-shrink: 0; }
        #columnMenu .dropdown-header { padding: 0.5rem 1rem; font-size: 0.75rem; font-weight: 600; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; }
        #columnMenu .dropdown-divider { margin: 0.5rem 0; }
        #columnMenu label { user-select: none; margin-bottom: 0; }

        .col-hidden { display: none !important; }
        .header-action { background: none; border: none; color: white; cursor: pointer; font-size: 0.875rem; padding: 0.25rem 0.5rem; }
        .header-action:hover { opacity: 0.8; }

        .stat-card { transition: transform 0.15s, box-shadow 0.15s; border: 1px solid #dee2e6; }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .stat-icon { width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 0.5rem; }

        .badge-damaged         { background-color: #6f42c1; color: #fff; }
        .badge-lost            { background-color: #dc3545; color: #fff; }
        .badge-overdue-payment { background-color: #0dcaf0; color: #000; }

        @keyframes pulse-danger {
            0%   { opacity: 1; }
            50%  { opacity: 0.5; }
            100% { opacity: 1; }
        }
        .overdue-pulse { animation: pulse-danger 1.8s ease-in-out infinite; }
    </style>
@endpush

@push('scripts')
    {{-- ── FIX 1: Flatpickr CORE JS wajib dimuat sebelum plugin --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // ── State ────────────────────────────────────────────────────────────────
            let currentPage   = 1;
            let currentStatus = 'all';
            let currentType   = 'all';
            let currentSearch = '';
            // ── FIX 3: Default null (tampilkan semua data), bukan new Date() yang menyebabkan
            //           tabel kosong jika data tidak ada di bulan ini ──────────────────
            let selectedMonth = null;
            const itemsPerPage = 10;

            let visibleColumns = {
                penalty_number : true,
                customer       : true,
                penalty_type   : true,
                issue_date     : true,
                due_date       : true,
                reference      : true,
                total_amount   : true,
                paid_amount    : true,
                outstanding    : true,
                status         : true,
            };

            // ── Month picker ─────────────────────────────────────────────────────────
            // ── FIX 4: Hapus defaultDate agar awal tidak ter-filter ke bulan ini,
            //           dan gunakan cara pemanggilan plugin yang aman ─────────────────
            const monthPicker = flatpickr('#monthYearFilter', {
                plugins: [
                    new monthSelectPlugin({
                        shorthand  : true,
                        dateFormat : 'M Y',
                        altFormat  : 'F Y',
                    })
                ],
                onChange: function (selectedDates) {
                    selectedMonth = selectedDates.length > 0 ? selectedDates[0] : null;
                    currentPage   = 1;
                    filterAndRender();
                },
            });

            // ── DOM refs ─────────────────────────────────────────────────────────────
            const tableBody          = document.getElementById('dendaTableBody');
            const paginationInfo     = document.getElementById('paginationInfo');
            const paginationControls = document.getElementById('paginationControls');
            const searchInput        = document.getElementById('searchInput');
            const clearSearchBtn     = document.getElementById('clearSearch');
            const clearMonthBtn      = document.getElementById('clearMonthFilter');
            const statusFilterBtns   = document.querySelectorAll('.status-filter');
            const typeFilterBtns     = document.querySelectorAll('.type-filter');
            const columnToggles      = document.querySelectorAll('.column-toggle');
            const resetColumnsBtn    = document.getElementById('resetColumns');
            const columnMenu         = document.getElementById('columnMenu');

            // ── Column visibility ────────────────────────────────────────────────────
            function updateColumnVisibility() {
                Object.keys(visibleColumns).forEach(col => {
                    document.querySelectorAll(`.col-${col}`).forEach(el => {
                        el.classList.toggle('col-hidden', !visibleColumns[col]);
                    });
                });
                localStorage.setItem('dendaColumns', JSON.stringify(visibleColumns));
            }

            const savedColumns = localStorage.getItem('dendaColumns');
            if (savedColumns) {
                try {
                    visibleColumns = JSON.parse(savedColumns);
                } catch (e) {
                    // Jika localStorage corrupt, pakai default
                    localStorage.removeItem('dendaColumns');
                }
                Object.keys(visibleColumns).forEach(col => {
                    const cb = document.getElementById(`col_${col}`);
                    if (cb) cb.checked = visibleColumns[col];
                });
                updateColumnVisibility();
            }

            columnToggles.forEach(toggle => {
                toggle.addEventListener('change', function () {
                    visibleColumns[this.value] = this.checked;
                    updateColumnVisibility();
                });
            });

            columnMenu.addEventListener('click', function (e) {
                if (e.target.classList.contains('column-toggle') ||
                    e.target.closest('label.dropdown-item')) e.stopPropagation();
            });

            resetColumnsBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                Object.keys(visibleColumns).forEach(col => {
                    visibleColumns[col] = true;
                    const cb = document.getElementById(`col_${col}`);
                    if (cb) cb.checked = true;
                });
                updateColumnVisibility();
                bootstrap.Dropdown.getInstance(document.getElementById('columnToggle'))?.hide();
            });

            // ── Helpers ──────────────────────────────────────────────────────────────
            function formatCurrency(amount) {
                return 'Rp ' + Number(amount).toLocaleString('id-ID');
            }

            // ── FIX 5: Parse tanggal dengan benar agar tidak kena bug timezone.
            //           "YYYY-MM-DD" di-parse sebagai UTC oleh browser, gunakan split agar
            //           selalu local date. ────────────────────────────────────────────
            function parseLocalDate(dateStr) {
                if (!dateStr) return null;
                // Ambil hanya bagian tanggal (YYYY-MM-DD), abaikan waktu jika ada
                const parts = String(dateStr).split('T')[0].split('-');
                if (parts.length < 3) return new Date(dateStr);
                return new Date(
                    parseInt(parts[0], 10),
                    parseInt(parts[1], 10) - 1,
                    parseInt(parts[2], 10)
                );
            }

            function formatDate(dateStr) {
                if (!dateStr) return '-';
                const d  = parseLocalDate(dateStr);
                if (!d || isNaN(d)) return '-';
                const dd = String(d.getDate()).padStart(2, '0');
                const mm = String(d.getMonth() + 1).padStart(2, '0');
                const yy = String(d.getFullYear()).slice(-2);
                return `${dd}/${mm}/${yy}`;
            }

            function getPenaltyTypeBadge(type) {
                const map = {
                    damaged         : { cls: 'badge-damaged',         icon: 'bi-tools',            label: '{{ __("Damaged") }}' },
                    lost            : { cls: 'badge-lost',            icon: 'bi-question-diamond', label: '{{ __("Lost") }}' },
                    overdue_payment : { cls: 'badge-overdue-payment', icon: 'bi-calendar-x',       label: '{{ __("Overdue Payment") }}' },
                };
                const t = map[type] || { cls: 'bg-secondary', icon: 'bi-dash-circle', label: type };
                return `<span class="badge ${t.cls} rounded-pill"><i class="${t.icon} me-1"></i>${t.label}</span>`;
            }

            function getStatusBadge(status) {
                const map = {
                    unpaid : { cls: 'bg-danger',         icon: 'bi-x-circle',     label: '{{ __("Unpaid") }}' },
                    paid   : { cls: 'bg-success',        icon: 'bi-check-circle', label: '{{ __("Paid") }}' },
                    waived : { cls: 'bg-info text-dark', icon: 'bi-shield-check', label: '{{ __("Waived") }}' },
                };
                const s = map[status] || { cls: 'bg-secondary', icon: 'bi-question-circle', label: status };
                return `<span class="badge ${s.cls} rounded-pill"><i class="${s.icon} me-1"></i>${s.label}</span>`;
            }

            // ── Filter & render ──────────────────────────────────────────────────────
            function filterAndRender() {
                let filtered = allDendaData.filter(d => {
                    if (currentStatus !== 'all' && d.status       !== currentStatus) return false;
                    if (currentType   !== 'all' && d.penalty_type !== currentType)   return false;

                    // ── FIX 5: Gunakan parseLocalDate agar perbandingan bulan tidak meleset
                    if (selectedMonth) {
                        const dt = parseLocalDate(d.issue_date);
                        if (!dt) return false;
                        if (dt.getMonth()    !== selectedMonth.getMonth()    ||
                            dt.getFullYear() !== selectedMonth.getFullYear()) return false;
                    }

                    if (currentSearch) {
                        const q = currentSearch.toLowerCase();
                        return String(d.penalty_number).toLowerCase().includes(q) ||
                               String(d.customer || '').toLowerCase().includes(q)  ||
                               String(d.reference || '').toLowerCase().includes(q);
                    }

                    return true;
                });

                const total    = filtered.length;
                const lastPage = Math.max(1, Math.ceil(total / itemsPerPage));
                currentPage    = Math.min(currentPage, lastPage);
                const offset   = (currentPage - 1) * itemsPerPage;
                const page     = filtered.slice(offset, offset + itemsPerPage);

                renderTable(page);

                const from = total > 0 ? offset + 1 : 0;
                const to   = Math.min(offset + itemsPerPage, total);
                paginationInfo.textContent =
                    `{{ __('Showing') }} ${from} {{ __('to') }} ${to} {{ __('of') }} ${total} ` +
                    (total === 1 ? `{{ __('penalty') }}` : `{{ __('penalties') }}`);

                renderPagination(currentPage, lastPage);
            }

            // ── Render table ─────────────────────────────────────────────────────────
            function renderTable(data) {
                if (data.length === 0) {
                    const colCount = Object.values(visibleColumns).filter(Boolean).length;
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="${colCount}" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-inbox display-4 d-block mb-3"></i>
                                    <p class="mb-1 fw-semibold">{{ __('No penalties found') }}</p>
                                    <p class="small mb-0">{{ __('Try adjusting your filters or search term') }}</p>
                                </div>
                            </td>
                        </tr>`;
                    return;
                }

                const today = new Date();
                today.setHours(0, 0, 0, 0); // Normalisasi ke tengah malam lokal

                tableBody.innerHTML = data.map(d => {
                    // ── FIX 5: Gunakan parseLocalDate untuk due_date juga
                    const dueDate   = parseLocalDate(d.due_date);
                    const isOverdue = dueDate && dueDate < today && d.outstanding > 0 &&
                                      d.status !== 'paid' && d.status !== 'waived';
                    const dueCls    = isOverdue ? 'text-danger fw-bold overdue-pulse' : '';

                    return `
                    <tr class="denda-row-clickable" data-denda-id="${d.id}">
                        <td class="px-3 py-2 col-penalty_number">
                            <span class="fw-semibold text-success">${d.penalty_number}</span>
                        </td>
                        <td class="px-3 py-2 col-customer">
                            <span class="fw-medium">${d.customer}</span>
                        </td>
                        <td class="px-3 py-2 text-center col-penalty_type">
                            ${getPenaltyTypeBadge(d.penalty_type)}
                        </td>
                        <td class="px-3 py-2 text-center col-issue_date">
                            ${formatDate(d.issue_date)}
                        </td>
                        <td class="px-3 py-2 text-center col-due_date ${dueCls}">
                            ${formatDate(d.due_date)}
                            ${isOverdue ? '<i class="bi bi-exclamation-circle ms-1"></i>' : ''}
                        </td>
                        <td class="px-3 py-2 col-reference">
                            ${d.reference
                                ? `<span class="text-primary small">${d.reference}</span>`
                                : '<span class="text-muted small">-</span>'}
                        </td>
                        <td class="px-3 py-2 text-end col-total_amount">
                            <span class="fw-medium">${formatCurrency(d.total_amount)}</span>
                        </td>
                        <td class="px-3 py-2 text-end col-paid_amount">
                            <span class="text-success">${formatCurrency(d.paid_amount)}</span>
                        </td>
                        <td class="px-3 py-2 text-end col-outstanding">
                            <span class="${d.outstanding > 0 ? 'text-danger fw-medium' : 'text-muted'}">
                                ${formatCurrency(d.outstanding)}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-center col-status">
                            ${getStatusBadge(d.status)}
                        </td>
                    </tr>`;
                }).join('');

                updateColumnVisibility();

                // Row click → halaman detail
                document.querySelectorAll('.denda-row-clickable').forEach(row => {
                    row.addEventListener('click', function () {
                        const id   = this.getAttribute('data-denda-id');
                        if (id) {
                            window.location.href = `${dendaDetailBaseUrl}/${id}`;
                        }
                    });
                });
            }

            // ── Detail modal ─────────────────────────────────────────────────────────
            let activeModalId  = null;
            let activeModalObj = null; // ── FIX 6: Simpan referensi modal agar tidak double-instance

            function openDetailModal(item) {
                activeModalId = item.id;

                // ── FIX 6: Gunakan getOrCreateInstance agar tidak error "modal already exists"
                const modalEl = document.getElementById('dendaDetailModal');
                activeModalObj = bootstrap.Modal.getOrCreateInstance(modalEl);
                const body     = document.getElementById('dendaDetailModalBody');

                body.innerHTML = `
                    <div class="row g-3">
                        <div class="col-md-6">
                            <p class="text-muted small mb-1">{{ __('Penalty Number') }}</p>
                            <p class="fw-semibold text-success mb-0">${item.penalty_number}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted small mb-1">{{ __('Status') }}</p>
                            <p class="mb-0">${getStatusBadge(item.status)}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted small mb-1">{{ __('Customer') }}</p>
                            <p class="fw-medium mb-0">${item.customer}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted small mb-1">{{ __('Penalty Type') }}</p>
                            <p class="mb-0">${getPenaltyTypeBadge(item.penalty_type)}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted small mb-1">{{ __('Issue Date') }}</p>
                            <p class="mb-0">${formatDate(item.issue_date)}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted small mb-1">{{ __('Due Date') }}</p>
                            <p class="mb-0">${formatDate(item.due_date)}</p>
                        </div>
                        <div class="col-12"><hr class="my-1"></div>
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">{{ __('Total Amount') }}</p>
                            <p class="fw-bold mb-0">${formatCurrency(item.total_amount)}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">{{ __('Paid Amount') }}</p>
                            <p class="fw-bold text-success mb-0">${formatCurrency(item.paid_amount)}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">{{ __('Outstanding') }}</p>
                            <p class="fw-bold ${item.outstanding > 0 ? 'text-danger' : 'text-muted'} mb-0">${formatCurrency(item.outstanding)}</p>
                        </div>
                        ${item.reference ? `
                        <div class="col-12">
                            <p class="text-muted small mb-1">{{ __('Reference') }}</p>
                            <p class="mb-0 small">${item.reference}</p>
                        </div>` : ''}
                        ${item.notes ? `
                        <div class="col-12">
                            <p class="text-muted small mb-1">{{ __('Notes') }}</p>
                            <p class="mb-0 small">${item.notes}</p>
                        </div>` : ''}
                    </div>`;

                activeModalObj.show();
            }

            // ── FIX 7: Handler mark-paid & waive — tambah konfirmasi + feedback error ─
            document.getElementById('btnMarkPaid')?.addEventListener('click', async function () {
                if (!activeModalId) return;
                if (!confirm('{{ __("Mark this penalty as paid?") }}')) return;

                this.disabled = true;
                try {
                    const res = await fetch(`/penalty/${activeModalId}/mark-paid`, {
                        method : 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept'      : 'application/json',
                        },
                    });
                    if (res.ok) {
                        location.reload();
                    } else {
                        const data = await res.json().catch(() => ({}));
                        alert(data.message || '{{ __("Failed to mark as paid.") }}');
                        this.disabled = false;
                    }
                } catch (e) {
                    console.error(e);
                    alert('{{ __("An error occurred. Please try again.") }}');
                    this.disabled = false;
                }
            });

            document.getElementById('btnWaivePenalty')?.addEventListener('click', async function () {
                if (!activeModalId) return;
                if (!confirm('{{ __("Waive this penalty?") }}')) return;

                this.disabled = true;
                try {
                    const res = await fetch(`/penalty/${activeModalId}/waive`, {
                        method : 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept'      : 'application/json',
                        },
                    });
                    if (res.ok) {
                        location.reload();
                    } else {
                        const data = await res.json().catch(() => ({}));
                        alert(data.message || '{{ __("Failed to waive penalty.") }}');
                        this.disabled = false;
                    }
                } catch (e) {
                    console.error(e);
                    alert('{{ __("An error occurred. Please try again.") }}');
                    this.disabled = false;
                }
            });

            // ── FIX 8: Export button handler ──────────────────────────────────────────
            document.getElementById('btnExportDenda')?.addEventListener('click', function () {
                // Ambil data yang sedang di-filter
                let filtered = allDendaData.filter(d => {
                    if (currentStatus !== 'all' && d.status       !== currentStatus) return false;
                    if (currentType   !== 'all' && d.penalty_type !== currentType)   return false;
                    if (selectedMonth) {
                        const dt = parseLocalDate(d.issue_date);
                        if (!dt) return false;
                        if (dt.getMonth()    !== selectedMonth.getMonth() ||
                            dt.getFullYear() !== selectedMonth.getFullYear()) return false;
                    }
                    if (currentSearch) {
                        const q = currentSearch.toLowerCase();
                        return String(d.penalty_number).toLowerCase().includes(q) ||
                               String(d.customer || '').toLowerCase().includes(q)  ||
                               String(d.reference || '').toLowerCase().includes(q);
                    }
                    return true;
                });

                if (filtered.length === 0) {
                    alert('{{ __("No data to export.") }}');
                    return;
                }

                // Generate CSV
                const headers = [
                    'Penalty Number', 'Customer', 'Type', 'Issue Date', 'Due Date',
                    'Reference', 'Total Amount', 'Paid Amount', 'Outstanding', 'Status'
                ];

                const rows = filtered.map(d => [
                    d.penalty_number,
                    d.customer,
                    d.penalty_type,
                    formatDate(d.issue_date),
                    formatDate(d.due_date),
                    d.reference || '',
                    d.total_amount,
                    d.paid_amount,
                    d.outstanding,
                    d.status,
                ].map(v => `"${String(v).replace(/"/g, '""')}"`).join(','));

                const csv     = [headers.join(','), ...rows].join('\r\n');
                const blob    = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
                const url     = URL.createObjectURL(blob);
                const a       = document.createElement('a');
                a.href        = url;
                a.download    = `penalties_${new Date().toISOString().slice(0,10)}.csv`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            });

            // ── Pagination ───────────────────────────────────────────────────────────
            function renderPagination(current, last) {
                if (last <= 1) { paginationControls.innerHTML = ''; return; }

                let start = Math.max(1, current - 2);
                let end   = Math.min(last, current + 2);
                if (current <= 3)       end   = Math.min(5, last);
                if (current > last - 3) start = Math.max(1, last - 4);

                let html = `
                    <li class="page-item ${current === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="${current - 1}"><i class="bi bi-chevron-left"></i></a>
                    </li>`;

                if (start > 1) {
                    html += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`;
                    if (start > 2) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }

                for (let i = start; i <= end; i++) {
                    html += `<li class="page-item ${i === current ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                }

                if (end < last) {
                    if (end < last - 1) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                    html += `<li class="page-item"><a class="page-link" href="#" data-page="${last}">${last}</a></li>`;
                }

                html += `
                    <li class="page-item ${current === last ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="${current + 1}"><i class="bi bi-chevron-right"></i></a>
                    </li>`;

                paginationControls.innerHTML = html;

                paginationControls.querySelectorAll('.page-link').forEach(link => {
                    link.addEventListener('click', function (e) {
                        e.preventDefault();
                        const li = this.closest('.page-item');
                        if (li.classList.contains('disabled') || li.classList.contains('active')) return;
                        const p = parseInt(this.getAttribute('data-page'));
                        if (p) { currentPage = p; filterAndRender(); }
                    });
                });
            }

            // ── Event listeners ──────────────────────────────────────────────────────
            statusFilterBtns.forEach(btn => {
                btn.addEventListener('click', function () {
                    statusFilterBtns.forEach(b => {
                        b.classList.remove('active-filter', 'btn-success');
                        b.classList.add('btn-outline-secondary');
                    });
                    this.classList.add('active-filter');
                    this.classList.remove('btn-outline-secondary');
                    currentStatus = this.getAttribute('data-status');
                    currentPage   = 1;
                    filterAndRender();
                });
            });

            typeFilterBtns.forEach(btn => {
                btn.addEventListener('click', function () {
                    typeFilterBtns.forEach(b => {
                        b.classList.remove('active-type', 'btn-secondary');
                        b.classList.add('btn-outline-secondary');
                    });
                    this.classList.add('active-type');
                    this.classList.remove('btn-outline-secondary');
                    currentType = this.getAttribute('data-type');
                    currentPage = 1;
                    filterAndRender();
                });
            });

            searchInput.addEventListener('input', function () {
                currentSearch = this.value.trim();
                currentPage   = 1;
                filterAndRender();
                clearSearchBtn.classList.toggle('d-none', !currentSearch);
            });

            clearSearchBtn.addEventListener('click', function () {
                searchInput.value = '';
                currentSearch     = '';
                currentPage       = 1;
                this.classList.add('d-none');
                filterAndRender();
            });

            clearMonthBtn.addEventListener('click', function () {
                monthPicker.clear();
                selectedMonth = null;
                currentPage   = 1;
                filterAndRender();
            });

            // ── Initial render ───────────────────────────────────────────────────────
            filterAndRender();

        }); // end DOMContentLoaded
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.startPenaltyMonitoringTour = function () {
                const driver    = window.driver.js.driver;
                const driverObj = driver({
                    showProgress: true,
                    showButtons : ['next', 'previous', 'close'],
                    steps: [
                        {
                            popover: {
                                title      : "⚠️ {{ __('Penalty Monitoring') }}",
                                description: "{{ __('This page lets you track all customer penalties such as late returns, damaged or lost pallets, and overdue payments.') }}"
                            }
                        },
                        {
                            element: '#monthYearFilter',
                            popover: {
                                title      : "📅 {{ __('Month Filter') }}",
                                description: "{{ __('Filter penalties by the month they were issued.') }}",
                                side: 'bottom', align: 'start'
                            }
                        },
                        {
                            element: '#searchInput',
                            popover: {
                                title      : "🔍 {{ __('Search') }}",
                                description: "{{ __('Search by penalty number, customer name, or reference document.') }}",
                                side: 'bottom', align: 'start'
                            }
                        },
                        {
                            element: '#dendaStatusFilters',
                            popover: {
                                title      : "📌 {{ __('Status & Type Filters') }}",
                                description: "{{ __('Filter by payment status (Unpaid, Paid, Waived) or by penalty type (Damaged, Lost, Overdue Payment).') }}",
                                side: 'top', align: 'start'
                            }
                        },
                        {
                            element: '#dendaSummaryCards',
                            popover: {
                                title      : "📊 {{ __('Summary Cards') }}",
                                description: "{{ __('Quick overview of total penalties, outstanding balance, paid this month, and waived amounts.') }}",
                                side: 'bottom', align: 'start'
                            }
                        },
                        {
                            element: '#columnToggle',
                            popover: {
                                title      : "⚙️ {{ __('Column Settings') }}",
                                description: "{{ __('Show or hide columns to customize your view.') }}",
                                side: 'left', align: 'start'
                            }
                        },
                        {
                            element: '#dendaTable',
                            popover: {
                                title      : "📋 {{ __('Penalty List') }}",
                                description: "{{ __('Each row represents a penalty issued to a customer. Overdue rows are highlighted with a blinking due date.') }}",
                                side: 'top', align: 'start'
                            }
                        },
                        {
                            popover: {
                                title      : "🖱️ {{ __('View Details') }}",
                                description: "{{ __('Click any row to view full penalty details and take action.') }}"
                            }
                        },
                        {
                            popover: {
                                title      : "🎉 {{ __('You're All Set!') }}",
                                description: "{{ __('You can now monitor and manage customer penalties effectively.') }}" +
                                             "<br><br><strong>{{ __('Tip:') }}</strong> " +
                                             "{{ __('Use the Type filter to focus on a specific penalty category.') }}"
                            }
                        }
                    ]
                });

                driverObj.drive();
                return driverObj;
            };

            window.startProductTour = window.startPenaltyMonitoringTour;
        });
    </script>
@endpush
