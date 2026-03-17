@extends('layouts.app')

@section('title', __('Invoice Management'))

@section('header-left')
    <div>
        <h2 class="h5 fw-semibold mb-0">{{ __('Invoice Management') }}</h2>
        <span class="text-muted small">{{ __('Track and manage customer invoices') }}</span>
    </div>
@endsection

@section('content')
    <!-- Search & Filter Bar -->
    <div class="card mb-3">
        <div class="card-body py-2">

            <!-- Info Banner -->
            <div class="alert alert-info py-2 mb-3 small">
                <i class="bi bi-info-circle me-2"></i>
                <strong>{{ __('Auto-Generated Invoices') }}:</strong>
                {{ __('Invoices are automatically generated monthly for each customer based on their pallet usage.') }}
            </div>

            {{-- ROW 1 : MONTH FILTER + SEARCH --}}
            <div class="d-flex flex-row align-items-center gap-2 mb-3">
                <!-- Month/Year Filter -->
                <div class="input-group input-group-sm flex-shrink-0" style="width:180px">
                    <span class="input-group-text bg-white text-muted">
                        <i class="bi bi-calendar3"></i>
                    </span>

                    <input type="text" class="form-control" id="monthYearFilter" placeholder="{{ __('All Months') }}"
                        readonly>

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
                            placeholder="{{ __('Search invoice...') }}">

                        <button type="button" class="btn btn-outline-secondary d-none" id="clearSearch">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- ROW 2 : STATUS PILLS --}}
            <div class="d-flex align-items-center gap-2 flex-wrap pt-2 border-top" id="invoiceStatusFilters">
                <span class="text-muted small fw-semibold me-2">{{ __('Status') }}</span>

                <button type="button" class="btn btn-sm btn-brand rounded-pill status-filter active-filter"
                    data-status="all">
                    {{ __('All') }}
                </button>

                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill status-filter"
                    data-status="draft">
                    {{ __('Draft') }}
                </button>

                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill status-filter"
                    data-status="sent">
                    {{ __('Sent') }}
                </button>

                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill status-filter"
                    data-status="partially_paid">
                    {{ __('Partially Paid') }}
                </button>

                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill status-filter"
                    data-status="paid">
                    {{ __('Paid') }}
                </button>

                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill status-filter"
                    data-status="overdue">
                    {{ __('Overdue') }}
                </button>

                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill status-filter"
                    data-status="cancelled">
                    {{ __('Cancelled') }}
                </button>
                @if(Auth::user()->isAdmin() || Auth::user()->isSuperAdmin())
                    <form action="api/generateInvoice" method="POST">
                        @csrf
                        <button class="btn btn-primary" type="submit" name="btnGenerateInvoice">{{ __('Generate Invoice') }}</button>
                    </form>
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

    <!-- Summary Cards -->
    <div class="row mb-3" id="invoiceSummaryCards">
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1">{{ __('Total Invoices') }}</p>
                            <h3 class="mb-0 fw-bold">{{ number_format($totalInvoice[0]->total) }}</h3>
                        </div>
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-receipt fs-3"></i>
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
                            <h3 class="mb-0 fw-bold text-warning">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</h3>
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
                            <h3 class="mb-0 fw-bold text-success">Rp {{ number_format($totalInvoicePaid, 0, ',', '.') }}</h3>
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
                            <p class="text-muted small mb-1">{{ __('Overdue') }}</p>
                            <h3 class="mb-0 fw-bold text-danger">Rp {{ number_format($totalInvoiceOverdue, 0, ',', '.') }}</h3>
                        </div>
                        <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                            <i class="bi bi-exclamation-triangle fs-3"></i>
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
                <i class="bi bi-receipt-cutoff me-2"></i>{{ __('Invoices List') }}
            </span>
            <div class="d-flex gap-2 align-items-center">
                <!-- Column Visibility Dropdown -->
                <div class="dropdown">
                    <button type="button" id="columnToggle"
                        class="header-action d-flex align-items-center gap-1 dropdown-toggle" data-bs-toggle="dropdown"
                        data-bs-display="static" aria-expanded="false">
                        <i class="bi bi-gear"></i>
                        {{ __('Columns') }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" id="columnMenu" aria-labelledby="columnToggle">
                        <li class="dropdown-header">{{ __('Show/Hide Columns') }}</li>
                        <li>
                            <label class="dropdown-item">
                                <input class="form-check-input me-2 column-toggle" type="checkbox" value="invoice_number"
                                    id="col_invoice_number" checked>
                                {{ __('Invoice Number') }}
                            </label>
                        </li>
                        <li>
                            <label class="dropdown-item">
                                <input class="form-check-input me-2 column-toggle" type="checkbox" value="customer"
                                    id="col_customer" checked>
                                {{ __('Customer') }}
                            </label>
                        </li>
                        <li>
                            <label class="dropdown-item">
                                <input class="form-check-input me-2 column-toggle" type="checkbox" value="issue_date"
                                    id="col_issue_date" checked>
                                {{ __('Issue Date') }}
                            </label>
                        </li>
                        <li>
                            <label class="dropdown-item">
                                <input class="form-check-input me-2 column-toggle" type="checkbox" value="due_date"
                                    id="col_due_date" checked>
                                {{ __('Due Date') }}
                            </label>
                        </li>
                        <li>
                            <label class="dropdown-item">
                                <input class="form-check-input me-2 column-toggle" type="checkbox" value="period"
                                    id="col_period" checked>
                                {{ __('Billing Period') }}
                            </label>
                        </li>
                        <li>
                            <label class="dropdown-item">
                                <input class="form-check-input me-2 column-toggle" type="checkbox" value="total_amount"
                                    id="col_total_amount" checked>
                                {{ __('Total Amount') }}
                            </label>
                        </li>
                        <li>
                            <label class="dropdown-item">
                                <input class="form-check-input me-2 column-toggle" type="checkbox" value="paid_amount"
                                    id="col_paid_amount" checked>
                                {{ __('Paid Amount') }}
                            </label>
                        </li>
                        <li>
                            <label class="dropdown-item">
                                <input class="form-check-input me-2 column-toggle" type="checkbox" value="outstanding"
                                    id="col_outstanding" checked>
                                {{ __('Outstanding') }}
                            </label>
                        </li>
                        <li>
                            <label class="dropdown-item">
                                <input class="form-check-input me-2 column-toggle" type="checkbox" value="status"
                                    id="col_status" checked>
                                {{ __('Status') }}
                            </label>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
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
                <table class="table table-hover align-middle mb-0" id="invoiceTable">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3 py-2 col-invoice_number">{{ __('Invoice Number') }}</th>
                            <th class="px-3 py-2 col-customer">{{ __('Customer') }}</th>
                            <th class="px-3 py-2 text-center col-issue_date">{{ __('Issue Date') }}</th>
                            <th class="px-3 py-2 text-center col-due_date">{{ __('Due Date') }}</th>
                            <th class="px-3 py-2 text-center col-period">{{ __('Billing Period') }}</th>
                            <th class="px-3 py-2 text-end col-total_amount">{{ __('Total Amount') }}</th>
                            <th class="px-3 py-2 text-end col-paid_amount">{{ __('Paid') }}</th>
                            <th class="px-3 py-2 text-end col-outstanding">{{ __('Outstanding') }}</th>
                            <th class="px-3 py-2 text-center col-status">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody id="invoiceTableBody">
                        <!-- Content will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center py-2">
            <!-- Left: Info text -->
            <p class="text-muted small mb-0" id="paginationInfo">
                {{ __('Showing 0 to 0 of 0 invoices') }}
            </p>

            <!-- Right: Pagination -->
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm mb-0" id="paginationControls">
                    <!-- Pagination will be populated by JavaScript -->
                </ul>
            </nav>
        </div>
    </div>

    <!-- Hidden data container -->
    <script>
        // Sample data - replace with actual data from backend
        // const allInvoicesData = [{
        //         id: 1,
        //         invoice_number: 'INV/2026/001',
        //         customer: 'PT Ultrajaya Milk Industry',
        //         issue_date: '2026-01-01',
        //         due_date: '2026-01-31',
        //         billing_period_start: '2026-01-01',
        //         billing_period_end: '2026-01-31',
        //         total_amount: 25000000,
        //         paid_amount: 25000000,
        //         outstanding: 0,
        //         status: 'paid'
        //     },
        //     {
        //         id: 2,
        //         invoice_number: 'INV/2026/002',
        //         customer: 'PT Mayora Indah, Tbk',
        //         issue_date: '2026-01-01',
        //         due_date: '2026-01-31',
        //         billing_period_start: '2026-01-01',
        //         billing_period_end: '2026-01-31',
        //         total_amount: 18500000,
        //         paid_amount: 10000000,
        //         outstanding: 8500000,
        //         status: 'partially_paid'
        //     },
        //     {
        //         id: 3,
        //         invoice_number: 'INV/2026/003',
        //         customer: 'PT Tirtakencana Tatawarna',
        //         issue_date: '2026-01-05',
        //         due_date: '2026-02-04',
        //         billing_period_start: '2026-01-01',
        //         billing_period_end: '2026-01-31',
        //         total_amount: 32000000,
        //         paid_amount: 0,
        //         outstanding: 32000000,
        //         status: 'sent'
        //     },
        //     {
        //         id: 4,
        //         invoice_number: 'INV/2026/004',
        //         customer: 'PT Forindoprima Perkasa',
        //         issue_date: '2025-12-15',
        //         due_date: '2026-01-14',
        //         billing_period_start: '2026-01-01',
        //         billing_period_end: '2026-01-30',
        //         total_amount: 15000000,
        //         paid_amount: 0,
        //         outstanding: 15000000,
        //         status: 'overdue'
        //     },
        //     {
        //         id: 5,
        //         invoice_number: 'INV/2026/005',
        //         customer: 'PT Unilever Oleochemical',
        //         issue_date: '2026-01-10',
        //         due_date: '2026-02-09',
        //         billing_period_start: '2026-01-01',
        //         billing_period_end: '2026-01-31',
        //         total_amount: 45000000,
        //         paid_amount: 45000000,
        //         outstanding: 0,
        //         status: 'paid'
        //     },
        //     {
        //         id: 6,
        //         invoice_number: 'INV/2026/006',
        //         customer: 'PT Lotte Chemical Indonesia',
        //         issue_date: '2026-01-15',
        //         due_date: '2026-02-14',
        //         billing_period_start: '2026-01-01',
        //         billing_period_end: '2026-01-31',
        //         total_amount: 28000000,
        //         paid_amount: 0,
        //         outstanding: 28000000,
        //         status: 'sent'
        //     },
        //     {
        //         id: 7,
        //         invoice_number: 'INV/2026/007',
        //         customer: 'PT Angkasa Pura',
        //         issue_date: '2026-01-20',
        //         due_date: '2026-02-19',
        //         billing_period_start: '2026-01-01',
        //         billing_period_end: '2026-01-31',
        //         total_amount: 12000000,
        //         paid_amount: 5000000,
        //         outstanding: 7000000,
        //         status: 'partially_paid'
        //     },
        //     {
        //         id: 8,
        //         invoice_number: 'DRAFT/2026/001',
        //         customer: 'PT Sumber Alfaria Trijaya',
        //         issue_date: '2026-01-25',
        //         due_date: '2026-02-24',
        //         billing_period_start: '2026-01-01',
        //         billing_period_end: '2026-01-31',
        //         total_amount: 22000000,
        //         paid_amount: 0,
        //         outstanding: 22000000,
        //         status: 'draft'
        //     }
        // ];
        const allInvoicesData =[];
    </script>
@endsection

@push('styles')
    <!-- Flatpickr Month Select Plugin -->
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

        .table tbody tr:hover {
            background-color: #f1f8f4;
        }

        .invoice-row-clickable:hover {
            background-color: #e8f5e9 !important;
        }

        .badge {
            font-weight: 500;
            font-size: 0.75rem;
        }

        .rounded-pill {
            border-radius: 50rem !important;
        }

        .input-group-text {
            border-right: 0;
        }

        .input-group .form-control {
            border-left: 0;
        }

        .input-group .form-control:focus {
            border-left: 0;
            box-shadow: none;
        }

        .input-group:focus-within .input-group-text {
            border-color: #198754;
        }

        .input-group:focus-within .form-control {
            border-color: #198754;
        }

        .pagination-sm .page-link {
            padding: 0.25rem 0.5rem;
            font-size: 0.8125rem;
            line-height: 1.5;
        }

        .pagination {
            gap: 0.25rem;
        }

        .page-item .page-link {
            border: 1px solid #dee2e6;
            color: #495057;
            border-radius: 0.25rem;
            transition: all 0.15s ease;
        }

        .page-item .page-link:hover {
            background-color: #f8f9fa;
            border-color: #198754;
            color: #198754;
        }

        .page-item.active .page-link {
            background-color: #198754;
            border-color: #198754;
            color: white;
        }

        .page-item.disabled .page-link {
            background-color: #fff;
            border-color: #dee2e6;
            color: #6c757d;
            cursor: not-allowed;
        }

        .status-filter.active-filter {
            background-color: #198754 !important;
            border-color: #198754 !important;
            color: white !important;
        }

        /* Column visibility dropdown styles */
        #columnMenu {
            min-width: 220px;
            max-width: 280px;
        }

        #columnMenu .dropdown-item {
            padding: 0.5rem 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            font-size: 0.875rem;
        }

        #columnMenu .dropdown-item:hover {
            background-color: #f8f9fa;
        }

        #columnMenu .dropdown-item:active {
            background-color: #e9ecef;
        }

        #columnMenu .form-check-input {
            cursor: pointer;
            margin-top: 0;
            flex-shrink: 0;
        }

        #columnMenu .dropdown-header {
            padding: 0.5rem 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        #columnMenu .dropdown-divider {
            margin: 0.5rem 0;
        }

        /* Prevent text selection on checkbox labels */
        #columnMenu label {
            user-select: none;
            margin-bottom: 0;
        }

        /* Hidden columns */
        .col-hidden {
            display: none !important;
        }

        .header-action {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 0.875rem;
            padding: 0.25rem 0.5rem;
        }

        .header-action:hover {
            opacity: 0.8;
        }

        /* Stat Cards */
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
        }
    </style>
@endpush

@push('scripts')
    <!-- Flatpickr Month Select Plugin -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // State management
            let currentPage = 1;
            let currentStatus = 'all';
            let currentSearch = '';
            let selectedMonth = new Date(); // Initialize with current month/year
            const itemsPerPage = 10;

            // Column visibility state
            let visibleColumns = {
                invoice_number: true,
                customer: true,
                issue_date: true,
                due_date: true,
                period: true,
                total_amount: true,
                paid_amount: true,
                outstanding: true,
                status: true
            };

            // Month/Year picker using Flatpickr
            const monthPicker = flatpickr('#monthYearFilter', {
                plugins: [
                    new monthSelectPlugin({
                        shorthand: true,
                        dateFormat: "M Y",
                        altFormat: "F Y"
                    })
                ],
                defaultDate: new Date(), // Set default to current month/year
                onChange: function(selectedDates, dateStr) {
                    if (selectedDates.length > 0) {
                        selectedMonth = selectedDates[0];
                        filterAndRender();
                    }
                }
            });

            // DOM elements
            const tableBody = document.getElementById('invoiceTableBody');
            const paginationInfo = document.getElementById('paginationInfo');
            const paginationControls = document.getElementById('paginationControls');
            const searchInput = document.getElementById('searchInput');
            const clearSearchBtn = document.getElementById('clearSearch');
            const statusFilterButtons = document.querySelectorAll('.status-filter');
            const columnToggles = document.querySelectorAll('.column-toggle');
            const resetColumnsBtn = document.getElementById('resetColumns');
            const columnMenu = document.getElementById('columnMenu');
            const monthYearInput = document.getElementById('monthYearFilter');
            const clearMonthBtn = document.getElementById('clearMonthFilter');

            // Column visibility management
            function updateColumnVisibility() {
                Object.keys(visibleColumns).forEach(col => {
                    const elements = document.querySelectorAll(`.col-${col}`);
                    elements.forEach(el => {
                        if (visibleColumns[col]) {
                            el.classList.remove('col-hidden');
                        } else {
                            el.classList.add('col-hidden');
                        }
                    });
                });

                // Save to localStorage
                localStorage.setItem('invoiceColumns', JSON.stringify(visibleColumns));
            }

            // Load saved column visibility
            const savedColumns = localStorage.getItem('invoiceColumns');
            if (savedColumns) {
                visibleColumns = JSON.parse(savedColumns);
                // Update checkboxes
                Object.keys(visibleColumns).forEach(col => {
                    const checkbox = document.getElementById(`col_${col}`);
                    if (checkbox) {
                        checkbox.checked = visibleColumns[col];
                    }
                });
                updateColumnVisibility();
            }

            // Column toggle handlers
            columnToggles.forEach(toggle => {
                toggle.addEventListener('change', function(e) {
                    const column = this.value;
                    visibleColumns[column] = this.checked;
                    updateColumnVisibility();
                });
            });

            // Prevent dropdown from closing when clicking inside menu items
            columnMenu.addEventListener('click', function(e) {
                if (e.target.classList.contains('column-toggle') ||
                    e.target.closest('label.dropdown-item')) {
                    e.stopPropagation();
                }
            });

            // Reset columns button
            resetColumnsBtn.addEventListener('click', function(e) {
                e.stopPropagation();

                Object.keys(visibleColumns).forEach(col => {
                    visibleColumns[col] = true;
                    const checkbox = document.getElementById(`col_${col}`);
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });
                updateColumnVisibility();

                // Close the dropdown
                const dropdown = bootstrap.Dropdown.getInstance(document.getElementById('columnToggle'));
                if (dropdown) {
                    dropdown.hide();
                }
            });

            // Filter and render data
            function filterAndRender() {
                let filteredData = allInvoicesData.filter(invoice => {
                    // Status filter
                    if (currentStatus !== 'all' && invoice.status !== currentStatus) {
                        return false;
                    }

                    // Month/Year filter (by billing period)
                    if (selectedMonth) {
                        const invoiceDate = new Date(invoice.billing_period_start);
                        const filterMonth = selectedMonth.getMonth();
                        const filterYear = selectedMonth.getFullYear();

                        if (invoiceDate.getMonth() !== filterMonth || invoiceDate.getFullYear() !==
                            filterYear) {
                            return false;
                        }
                    }

                    // Search filter
                    if (currentSearch) {
                        const searchLower = currentSearch.toLowerCase();
                        return (
                            invoice.invoice_number.toLowerCase().includes(searchLower) ||
                            invoice.customer.toLowerCase().includes(searchLower)
                        );
                    }
                    return true;
                });

                const total = filteredData.length;
                const lastPage = Math.max(1, Math.ceil(total / itemsPerPage));
                currentPage = Math.min(currentPage, lastPage);
                const offset = (currentPage - 1) * itemsPerPage;
                const paginatedData = filteredData.slice(offset, offset + itemsPerPage);

                renderTable(paginatedData);

                const from = total > 0 ? offset + 1 : 0;
                const to = Math.min(offset + itemsPerPage, total);
                paginationInfo.textContent =
                    `{{ __('Showing') }} ${from} {{ __('to') }} ${to} {{ __('of') }} ${total} ${total === 1 ? '{{ __('invoice') }}' : '{{ __('invoices') }}'}`;

                renderPagination(currentPage, lastPage);
            }

            // Format currency
            function formatCurrency(amount) {
                return 'Rp ' + amount.toLocaleString('id-ID');
            }

            // Render table rows
            function renderTable(data) {
                if (data.length === 0) {
                    const visibleColCount = Object.values(visibleColumns).filter(v => v).length;
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="${visibleColCount}" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-inbox display-4 d-block mb-3"></i>
                                    <p class="mb-1 fw-semibold">{{ __('No invoices found') }}</p>
                                    <p class="small mb-0">{{ __('Try adjusting your filters or search term') }}</p>
                                </div>
                            </td>
                        </tr>
                    `;
                    return;
                }

                tableBody.innerHTML = data.map(invoice => {
                    let statusBadgeClass = '';
                    let statusIcon = '';
                    let statusLabel = '';

                    switch (invoice.status) {
                        case 'draft':
                            statusBadgeClass = 'bg-secondary';
                            statusIcon = 'bi-file-earmark';
                            statusLabel = 'Draft';
                            break;
                        case 'sent':
                            statusBadgeClass = 'bg-info';
                            statusIcon = 'bi-send';
                            statusLabel = 'Sent';
                            break;
                        case 'partially_paid':
                            statusBadgeClass = 'bg-warning text-dark';
                            statusIcon = 'bi-cash-stack';
                            statusLabel = 'Partially Paid';
                            break;
                        case 'paid':
                            statusBadgeClass = 'bg-success';
                            statusIcon = 'bi-check-circle';
                            statusLabel = 'Paid';
                            break;
                        case 'overdue':
                            statusBadgeClass = 'bg-danger';
                            statusIcon = 'bi-exclamation-triangle';
                            statusLabel = 'Overdue';
                            break;
                        case 'cancelled':
                            statusBadgeClass = 'bg-dark';
                            statusIcon = 'bi-x-circle';
                            statusLabel = 'Cancelled';
                            break;
                        default:
                            statusBadgeClass = 'bg-secondary';
                            statusIcon = 'bi-question-circle';
                            statusLabel = 'Unknown';
                    }

                    const formatDate = (dateStr) => {
                        if (!dateStr) return '-';

                        const date = new Date(dateStr);

                        const day = String(date.getDate()).padStart(2, '0');
                        const month = String(date.getMonth() + 1).padStart(2, '0');
                        const year = String(date.getFullYear()).slice(-2);

                        return `${day}/${month}/${year}`;
                    };

                    const formatPeriod = (start, end) => {
                        const startDate = new Date(start);
                        const endDate = new Date(end);

                        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
                        ];

                        return `${monthNames[startDate.getMonth()]} ${startDate.getFullYear()}`;
                    };

                    // Check if overdue
                    const today = new Date();
                    const dueDate = new Date(invoice.due_date);
                    const isOverdue = dueDate < today && invoice.outstanding > 0;
                    const dueDateClass = isOverdue ? 'text-danger fw-bold' : '';

                    return `
                        <tr class="invoice-row-clickable" data-invoice-id="${invoice.id}" data-cnoinvoice="${invoice.invoice_number}" data-uuid="${invoice.uuid}">
                            <td class="px-3 py-2 col-invoice_number">
                                <span class="fw-semibold text-success">${invoice.invoice_number}</span>
                            </td>
                            <td class="px-3 py-2 col-customer">
                                <span class="fw-medium">${invoice.customer}</span>
                            </td>
                            <td class="px-3 py-2 text-center col-issue_date">
                                ${formatDate(invoice.issue_date)}
                            </td>
                            <td class="px-3 py-2 text-center col-due_date ${dueDateClass}">
                                ${formatDate(invoice.due_date)}
                                ${isOverdue ? '<i class="bi bi-exclamation-circle ms-1"></i>' : ''}
                            </td>
                            <td class="px-3 py-2 text-center col-period">
                                <span class="badge bg-light text-dark">${formatPeriod(invoice.billing_period_start, invoice.billing_period_end)}</span>
                            </td>
                            <td class="px-3 py-2 text-end col-total_amount">
                                <span class="fw-medium">${formatCurrency(invoice.total_amount)}</span>
                            </td>
                            <td class="px-3 py-2 text-end col-paid_amount">
                                <span class="text-success">${formatCurrency(invoice.paid_amount)}</span>
                            </td>
                            <td class="px-3 py-2 text-end col-outstanding">
                                <span class="${invoice.outstanding > 0 ? 'text-danger fw-medium' : 'text-muted'}">
                                    ${formatCurrency(invoice.outstanding)}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-center col-status">
                                <span class="badge ${statusBadgeClass} rounded-pill">
                                    <i class="${statusIcon} me-1"></i>${statusLabel}
                                </span>
                            </td>
                        </tr>
                    `;
                }).join('');

                updateColumnVisibility();

                // Add click handlers to rows
                document.querySelectorAll('.invoice-row-clickable').forEach(row => {
                    row.addEventListener('click', function() {
                        const invoiceId = this.getAttribute('data-invoice-id');
                        let cnoinvoice = this.getAttribute('data-cnoinvoice');
                        let uuid = this.getAttribute('data-uuid');
                        cnoinvoice = cnoinvoice.replaceAll("/", "_");
                        window.location.href = `/invoice/${uuid}`;
                    });
                });
            }

            // Render pagination
            function renderPagination(current, last) {
                if (last <= 1) {
                    paginationControls.innerHTML = '';
                    return;
                }

                let start = Math.max(1, current - 2);
                let end = Math.min(last, current + 2);

                if (current <= 3) end = Math.min(5, last);
                if (current > last - 3) start = Math.max(1, last - 4);

                let html = `
                    <li class="page-item ${current === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="${current - 1}">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                `;

                if (start > 1) {
                    html += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`;
                    if (start > 2) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }

                for (let i = start; i <= end; i++) {
                    html += `<li class="page-item ${i === current ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>`;
                }

                if (end < last) {
                    if (end < last - 1) html +=
                        `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                    html +=
                        `<li class="page-item"><a class="page-link" href="#" data-page="${last}">${last}</a></li>`;
                }

                html += `
                    <li class="page-item ${current === last ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="${current + 1}">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                `;

                paginationControls.innerHTML = html;

                paginationControls.querySelectorAll('.page-link').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (this.closest('.page-item').classList.contains('disabled') ||
                            this.closest('.page-item').classList.contains('active')) return;
                        const page = parseInt(this.getAttribute('data-page'));
                        if (page) {
                            currentPage = page;
                            filterAndRender();
                        }
                    });
                });
            }

            // Status filter buttons
            statusFilterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    statusFilterButtons.forEach(btn => {
                        btn.classList.remove('btn-brand', 'active-filter');
                        btn.classList.add('btn-outline-secondary');
                    });
                    this.classList.remove('btn-outline-secondary');
                    this.classList.add('btn-brand', 'active-filter');
                    currentStatus = this.getAttribute('data-status');
                    currentPage = 1;
                    filterAndRender();
                });
            });

            // Search input
            searchInput.addEventListener('input', function() {
                currentSearch = this.value.trim();
                currentPage = 1;
                filterAndRender();
                clearSearchBtn.classList.toggle('d-none', !currentSearch);
            });

            // Clear search
            clearSearchBtn.addEventListener('click', function() {
                searchInput.value = '';
                currentSearch = '';
                currentPage = 1;
                this.classList.add('d-none');
                filterAndRender();
            });

            clearMonthBtn.addEventListener('click', function() {
                monthPicker.clear();
                selectedMonth = null;
                currentPage = 1;
                filterAndRender();
            });

            //getData
            async function getData() {
                try {
                    let ckdcust = '{{ Auth::user()->ckdcust ?? "" }}';
                    const response = await fetch('/api/invoice/getData/' + (ckdcust ?? ''));
                    const data = await response.json();
                    console.log(data);
                    let ctr=1;
                    data.forEach(invoice => {
                        allInvoicesData.push(
                            {
                                id: ctr,
                                invoice_number: invoice.cnofakturpnj,
                                customer: invoice.cnmcust,
                                issue_date: invoice.dtglfakturpnj,
                                due_date: invoice.dtglfakturpnj,
                                billing_period_start: invoice.dtglawaltagih,
                                billing_period_end: invoice.dtglakhirtagih,
                                total_amount: invoice.njmlhrg==null?0:invoice.njmlhrg,
                                paid_amount: invoice.njmlbyr==null?0:invoice.njmlbyr,
                                outstanding: ((invoice.njmlhrg==null?0:invoice.njmlhrg)-(invoice.njmlbyr==null?0:invoice.njmlbyr)),
                                status: invoice.cstatus,
                                uuid: invoice.uuid
                            }
                        );
                        ctr++;
                    });
                    filterAndRender();
                    return data;
                } catch (error) {
                    console.error('Error fetching data:', error);
                    return [];
                }
            }

            getData();
            // Initial render
            // filterAndRender();
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // =========================================================================
            // INVOICE MANAGEMENT TOUR (ID-SAFE)
            // =========================================================================
            window.startInvoiceManagementTour = function() {
                const driver = window.driver.js.driver;

                const driverObj = driver({
                    showProgress: true,
                    showButtons: ['next', 'previous', 'close'],
                    steps: [
                            {
                                popover: {
                                    title: "🧾 {{ __('Invoice Management') }}",
                                    description: "{{ __('This page allows you to track and manage customer invoices generated by the system.') }}"
                                }
                            },
                            {
                                popover: {
                                    title: "⚙️ {{ __('Auto-Generated Invoices') }}",
                                    description: "{{ __('Invoices are generated automatically every month based on pallet usage. You do not need to create them manually.') }}"
                                }
                            },
                            {
                                element: '#monthYearFilter',
                                popover: {
                                    title: "📅 {{ __('Billing Month Filter') }}",
                                    description: "{{ __('Filter invoices by billing month and year.') }}",
                                    side: 'bottom',
                                    align: 'start'
                                }
                            },
                            {
                                element: '#searchInput',
                                popover: {
                                    title: "🔍 {{ __('Search Invoices') }}",
                                    description: "{{ __('Search invoices by invoice number or customer name.') }}",
                                    side: 'bottom',
                                    align: 'start'
                                }
                            },
                            {
                                element: '#invoiceStatusFilters',
                                popover: {
                                    title: "📌 {{ __('Invoice Status Filter') }}",
                                    description: "{{ __('Filter invoices by status such as Draft, Sent, Paid, Partially Paid, Overdue, or Cancelled.') }}",
                                    side: 'top',
                                    align: 'start'
                                }
                            },
                            {
                                element: '#invoiceSummaryCards',
                                popover: {
                                    title: "📊 {{ __('Invoice Summary') }}",
                                    description: "{{ __('These summary cards show total invoices, outstanding amounts, payments received, and overdue balances.') }}",
                                    side: 'bottom',
                                    align: 'start'
                                }
                            },
                            {
                                element: '#columnToggle',
                                popover: {
                                    title: "⚙️ {{ __('Column Settings') }}",
                                    description: "{{ __('Customize which columns are visible in the invoice table.') }}",
                                    side: 'left',
                                    align: 'start'
                                }
                            },
                            {
                                element: '#invoiceTable',
                                popover: {
                                    title: "📋 {{ __('Invoice List') }}",
                                    description: "{{ __('Each row represents a customer invoice for a specific billing period.') }}",
                                    side: 'top',
                                    align: 'start'
                                }
                            },
                            {
                                popover: {
                                    title: "🖱️ {{ __('View Invoice Details') }}",
                                    description: "{{ __('Click any invoice row to view detailed billing information and payment history.') }}"
                                }
                            },
                            {
                                popover: {
                                    title: "🎉 {{ __('You’re All Set!') }}",
                                    description:
                                        "{{ __('You now know how to monitor and manage customer invoices.') }}" +
                                        "<br><br><strong>{{ __('Tip:') }}</strong> " +
                                        "{{ __('Use status filters to quickly identify overdue or unpaid invoices.') }}"
                                }
                            }
                        ]
                });

                driverObj.drive();
                return driverObj;
            };

            // =========================================================================
            // REGISTER THIS PAGE TOUR
            // =========================================================================
            window.startProductTour = window.startInvoiceManagementTour;

        });
    </script>
@endpush
