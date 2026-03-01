@extends('backend.app')

@section('title', 'Payment Control Center')

@push('styles')
    <link href="{{ asset('default/datatable.css') }}" rel="stylesheet" />
    <style>
        :root {
            --clr-bg: #f4f6fb;
            --clr-surface: #ffffff;
            --clr-surface-2: #f8fafc;
            --clr-border: #e8edf5;
            --clr-accent: #0ab87a;
            --clr-accent-dim: rgba(10, 184, 122, 0.08);
            --clr-warn: #f59f00;
            --clr-warn-dim: rgba(245, 159, 0, 0.10);
            --clr-danger: #e03131;
            --clr-danger-dim: rgba(224, 49, 49, 0.08);
            --clr-blue: #1971c2;
            --clr-blue-dim: rgba(25, 113, 194, 0.08);
            --clr-text: #1a1f2e;
            --clr-muted: #7c8db5;
            --clr-muted-2: #a8b4cf;
            --radius: 10px;
            --shadow: 0 1px 4px rgba(0, 0, 0, .06), 0 4px 16px rgba(0, 0, 0, .04);
        }

        /* ─── STAT CARDS ─── */
        .pcc-stats {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
            gap: 14px;
            margin-bottom: 24px;
        }

        .pcc-stat {
            background: var(--clr-surface);
            border: 1px solid var(--clr-border);
            border-radius: var(--radius);
            padding: 18px 20px;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: transform .15s, box-shadow .15s;
        }

        .pcc-stat:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, .09);
        }

        .pcc-stat::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--accent-color, var(--clr-accent));
            border-radius: var(--radius) var(--radius) 0 0;
        }

        .pcc-stat .stat-icon {
            width: 38px;
            height: 38px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            margin-bottom: 10px;
            background: var(--icon-bg, var(--clr-accent-dim));
            color: var(--accent-color, var(--clr-accent));
        }

        .pcc-stat .stat-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--clr-muted);
            margin-bottom: 3px;
            font-weight: 600;
        }

        .pcc-stat .stat-value {
            font-size: 22px;
            font-weight: 700;
            color: var(--clr-text);
            line-height: 1;
        }

        .pcc-stat .stat-sub {
            font-size: 11px;
            color: var(--clr-muted-2);
            margin-top: 5px;
        }

        /* ─── TABS ─── */
        .pcc-tabs {
            display: flex;
            gap: 4px;
            background: var(--clr-surface);
            border: 1px solid var(--clr-border);
            border-radius: var(--radius);
            padding: 5px;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
        }

        .pcc-tab {
            flex: 1;
            padding: 9px 14px;
            border-radius: 7px;
            border: none;
            background: transparent;
            color: var(--clr-muted);
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all .15s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
        }

        .pcc-tab:hover {
            color: var(--clr-text);
            background: var(--clr-surface-2);
        }

        .pcc-tab.active {
            background: var(--clr-accent-dim);
            color: var(--clr-accent);
            font-weight: 600;
        }

        .tab-badge {
            background: var(--clr-accent);
            color: #fff;
            border-radius: 99px;
            font-size: 10px;
            font-weight: 700;
            padding: 1px 7px;
            min-width: 18px;
            text-align: center;
        }

        /* ─── PANELS ─── */
        .pcc-panel {
            display: none;
        }

        .pcc-panel.active {
            display: block;
        }

        /* ─── CARD ─── */
        .pcc-card {
            background: var(--clr-surface);
            border: 1px solid var(--clr-border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .pcc-card-header {
            padding: 14px 20px;
            border-bottom: 1px solid var(--clr-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--clr-surface-2);
        }

        .pcc-card-header h3 {
            margin: 0;
            font-size: 13px;
            font-weight: 600;
            color: var(--clr-text);
        }

        .pcc-card-body {
            padding: 18px 20px;
        }

        /* ─── BADGES ─── */
        .pcc-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 9px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 600;
        }

        .pcc-badge::before {
            content: '';
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: currentColor;
        }

        .pcc-badge.green {
            background: var(--clr-accent-dim);
            color: var(--clr-accent);
        }

        .pcc-badge.yellow {
            background: var(--clr-warn-dim);
            color: var(--clr-warn);
        }

        .pcc-badge.red {
            background: var(--clr-danger-dim);
            color: var(--clr-danger);
        }

        .pcc-badge.blue {
            background: var(--clr-blue-dim);
            color: var(--clr-blue);
        }

        .pcc-badge.gray {
            background: #f1f3f8;
            color: var(--clr-muted);
        }

        /* ─── BUTTONS ─── */
        .pcc-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 13px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all .15s;
            text-decoration: none;
        }

        .pcc-btn-green {
            background: var(--clr-accent-dim);
            color: var(--clr-accent);
        }

        .pcc-btn-green:hover {
            background: var(--clr-accent);
            color: #fff;
        }

        .pcc-btn-yellow {
            background: var(--clr-warn-dim);
            color: var(--clr-warn);
        }

        .pcc-btn-yellow:hover {
            background: var(--clr-warn);
            color: #fff;
        }

        .pcc-btn-red {
            background: var(--clr-danger-dim);
            color: var(--clr-danger);
        }

        .pcc-btn-red:hover {
            background: var(--clr-danger);
            color: #fff;
        }

        .pcc-btn-blue {
            background: var(--clr-blue-dim);
            color: var(--clr-blue);
        }

        .pcc-btn-blue:hover {
            background: var(--clr-blue);
            color: #fff;
        }

        .pcc-btn-ghost {
            background: #f1f3f8;
            color: var(--clr-muted);
            border: 1px solid var(--clr-border);
        }

        .pcc-btn-ghost:hover {
            color: var(--clr-text);
            background: var(--clr-border);
        }

        /* ─── ORDER DETAIL ─── */
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 9px 0;
            border-bottom: 1px solid var(--clr-border);
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-key {
            color: var(--clr-muted);
            font-size: 12px;
        }

        .detail-val {
            color: var(--clr-text);
            font-size: 13px;
            font-weight: 500;
        }

        .detail-money {
            color: var(--clr-accent);
            font-size: 15px;
            font-weight: 700;
            font-family: monospace;
        }

        /* ─── ALERTS ─── */
        .pcc-alert {
            padding: 11px 15px;
            border-radius: 8px;
            margin-bottom: 14px;
            font-size: 13px;
            display: flex;
            align-items: flex-start;
            gap: 9px;
        }

        .pcc-alert.success {
            background: var(--clr-accent-dim);
            color: #087a52;
            border: 1px solid rgba(10, 184, 122, .2);
        }

        .pcc-alert.error {
            background: var(--clr-danger-dim);
            color: var(--clr-danger);
            border: 1px solid rgba(224, 49, 49, .2);
        }

        .pcc-alert.info {
            background: var(--clr-blue-dim);
            color: var(--clr-blue);
            border: 1px solid rgba(25, 113, 194, .2);
        }

        /* ─── PAGE HEADER ─── */
        .pcc-page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .pcc-page-header h1 {
            font-size: 20px;
            font-weight: 700;
            color: var(--clr-text);
            margin: 0;
        }

        .pcc-page-header p {
            color: var(--clr-muted);
            font-size: 12px;
            margin: 3px 0 0;
        }

        /* ─── REFUND OPTIONS ─── */
        .refund-options {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .refund-option {
            flex: 1;
            min-width: 110px;
            border: 1.5px solid var(--clr-border);
            border-radius: 8px;
            padding: 12px 10px;
            cursor: pointer;
            text-align: center;
            transition: all .15s;
            background: var(--clr-surface-2);
        }

        .refund-option:has(input:checked) {
            border-color: var(--clr-accent);
            background: var(--clr-accent-dim);
        }

        .refund-option input {
            display: none;
        }

        .refund-option .rf-icon {
            font-size: 18px;
            margin-bottom: 4px;
        }

        .refund-option .rf-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--clr-text);
        }

        .refund-option .rf-sub {
            font-size: 10px;
            color: var(--clr-muted);
            margin-top: 2px;
        }

        /* ─── MODAL ─── */
        .modal-content {
            border: 1px solid var(--clr-border) !important;
            border-radius: var(--radius) !important;
            box-shadow: 0 8px 32px rgba(0, 0, 0, .12) !important;
        }

        .modal-header {
            border-bottom-color: var(--clr-border) !important;
            background: var(--clr-surface-2);
            border-radius: var(--radius) var(--radius) 0 0 !important;
            padding: 14px 20px !important;
        }

        .modal-footer {
            border-top-color: var(--clr-border) !important;
            background: var(--clr-surface-2);
            border-radius: 0 0 var(--radius) var(--radius) !important;
        }

        .modal-title {
            font-size: 15px !important;
            font-weight: 600 !important;
            color: var(--clr-text) !important;
        }

        .form-label {
            font-size: 11px !important;
            font-weight: 600 !important;
            text-transform: uppercase !important;
            letter-spacing: .05em !important;
            color: var(--clr-muted) !important;
        }

        .form-control,
        .form-select {
            border-color: var(--clr-border) !important;
            border-radius: 7px !important;
            font-size: 13px !important;
            color: var(--clr-text) !important;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--clr-accent) !important;
            box-shadow: 0 0 0 3px var(--clr-accent-dim) !important;
        }

        /* ─── DATATABLE ─── */
        #ordersTable thead th,
        #stripeTable thead th,
        #withdrawalsTable thead th {
            background: var(--clr-surface-2);
            color: var(--clr-muted);
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .05em;
            border-bottom: 1px solid var(--clr-border) !important;
            white-space: nowrap;
        }

        .table> :not(caption)>*>* {
            border-bottom-color: var(--clr-border) !important;
        }

        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid var(--clr-border) !important;
            border-radius: 6px !important;
            font-size: 12px !important;
            padding: 4px 8px !important;
            color: var(--clr-text) !important;
        }

        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_length label,
        .dataTables_wrapper .dataTables_filter label {
            font-size: 12px !important;
            color: var(--clr-muted) !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 6px !important;
            font-size: 12px !important;
            color: var(--clr-muted) !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--clr-accent-dim) !important;
            color: var(--clr-accent) !important;
            border: 1px solid rgba(10, 184, 122, .2) !important;
            font-weight: 600 !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: var(--clr-surface-2) !important;
            color: var(--clr-text) !important;
            border: 1px solid var(--clr-border) !important;
        }

        /* ─── DETAIL PANEL inside modal ─── */
        .detail-panel {
            background: var(--clr-surface-2);
            border: 1px solid var(--clr-border);
            border-radius: 9px;
            padding: 16px 18px;
        }

        .detail-panel-title {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--clr-muted);
            margin-bottom: 10px;
            font-weight: 700;
        }
    </style>
@endpush

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app" style="margin-bottom: 80px; margin-top: 30px;">
            <div class="main-container container-fluid">

                {{-- Flash Messages --}}
                @if (session('success'))
                    <div class="pcc-alert success mt-3">
                        <i class="fe fe-check-circle" style="margin-top:1px;flex-shrink:0"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif
                @if (session('error'))
                    <div class="pcc-alert error mt-3">
                        <i class="fe fe-alert-circle" style="margin-top:1px;flex-shrink:0"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                {{-- Page Header --}}
                <div class="pcc-page-header mt-3">
                    <div>
                        <h1>Payment Control Center</h1>
                        <p>Manage orders, escrow releases, Stripe accounts, and payouts</p>
                    </div>
                    <div
                        style="font-size:11px;color:var(--clr-muted-2);background:var(--clr-surface);border:1px solid var(--clr-border);padding:6px 12px;border-radius:6px">
                        <i class="fe fe-clock me-1"></i> {{ now()->format('d M Y, H:i') }}
                    </div>
                </div>

                {{-- Stats Grid --}}
                <div class="pcc-stats">
                    <div class="pcc-stat" style="--accent-color:var(--clr-accent);--icon-bg:var(--clr-accent-dim)">
                        <div class="stat-icon"><i class="fe fe-trending-up"></i></div>
                        <div class="stat-label">Total Revenue</div>
                        <div class="stat-value">${{ number_format($stats['total_revenue'], 0) }}</div>
                        <div class="stat-sub">{{ $stats['completed_orders'] }} completed orders</div>
                    </div>
                    <div class="pcc-stat" style="--accent-color:var(--clr-blue);--icon-bg:var(--clr-blue-dim)">
                        <div class="stat-icon"><i class="fe fe-percent"></i></div>
                        <div class="stat-label">Platform Fees</div>
                        <div class="stat-value">${{ number_format($stats['platform_fees'], 0) }}</div>
                        <div class="stat-sub">10% of completed orders</div>
                    </div>
                    <div class="pcc-stat" style="--accent-color:var(--clr-warn);--icon-bg:var(--clr-warn-dim)">
                        <div class="stat-icon"><i class="fe fe-lock"></i></div>
                        <div class="stat-label">In Escrow</div>
                        <div class="stat-value">${{ number_format($stats['in_escrow'], 0) }}</div>
                        <div class="stat-sub">Pending + clearing</div>
                    </div>
                    <div class="pcc-stat" style="--accent-color:var(--clr-accent);--icon-bg:var(--clr-accent-dim)">
                        <div class="stat-icon"><i class="fe fe-dollar-sign"></i></div>
                        <div class="stat-label">Available to Experts</div>
                        <div class="stat-value">${{ number_format($stats['available_to_experts'], 0) }}</div>
                        <div class="stat-sub">Ready to withdraw</div>
                    </div>
                    <div class="pcc-stat" style="--accent-color:#7048e8;--icon-bg:rgba(112,72,232,.08)">
                        <div class="stat-icon"><i class="fe fe-send"></i></div>
                        <div class="stat-label">Total Withdrawn</div>
                        <div class="stat-value">${{ number_format($stats['withdrawn'], 0) }}</div>
                        <div class="stat-sub">Paid out to experts</div>
                    </div>
                    <div class="pcc-stat" style="--accent-color:var(--clr-warn);--icon-bg:var(--clr-warn-dim)">
                        <div class="stat-icon"><i class="fe fe-clock"></i></div>
                        <div class="stat-label">Pending Withdrawals</div>
                        <div class="stat-value">{{ $stats['pending_withdrawals'] }}</div>
                        <div class="stat-sub">Awaiting processing</div>
                    </div>
                    <div class="pcc-stat" style="--accent-color:var(--clr-blue);--icon-bg:var(--clr-blue-dim)">
                        <div class="stat-icon"><i class="fe fe-activity"></i></div>
                        <div class="stat-label">Active Orders</div>
                        <div class="stat-value">{{ $stats['active_orders'] }}</div>
                        <div class="stat-sub">In progress right now</div>
                    </div>
                    <div class="pcc-stat" style="--accent-color:var(--clr-accent);--icon-bg:var(--clr-accent-dim)">
                        <div class="stat-icon"><i class="fe fe-check-square"></i></div>
                        <div class="stat-label">Completed Orders</div>
                        <div class="stat-value">{{ $stats['completed_orders'] }}</div>
                        <div class="stat-sub">All time</div>
                    </div>
                </div>

                {{-- Tabs --}}
                <div class="pcc-tabs">
                    <button class="pcc-tab active" onclick="switchTab('orders', this)">
                        <i class="fe fe-list"></i> Orders & Escrow
                        <span class="tab-badge">{{ $stats['active_orders'] }}</span>
                    </button>
                    <button class="pcc-tab" onclick="switchTab('stripe', this)">
                        <i class="fe fe-credit-card"></i> Stripe Accounts
                    </button>
                    <button class="pcc-tab" onclick="switchTab('withdrawals', this)">
                        <i class="fe fe-arrow-up-right"></i> Withdrawals
                        @if ($stats['pending_withdrawals'] > 0)
                            <span class="tab-badge"
                                style="background:var(--clr-warn)">{{ $stats['pending_withdrawals'] }}</span>
                        @endif
                    </button>
                </div>

                {{-- TAB: ORDERS --}}
                <div id="tab-orders" class="pcc-panel active">
                    <div class="pcc-card">
                        <div class="pcc-card-header">
                            <h3><i class="fe fe-list me-2" style="color:var(--clr-accent)"></i>All Orders — Payment Control
                            </h3>
                            <small style="color:var(--clr-muted)">Click <strong>Details</strong> on any row for full
                                controls</small>
                        </div>
                        <div class="pcc-card-body">
                            <table class="table table-bordered w-100" id="ordersTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Order</th>
                                        <th>Client</th>
                                        <th>Expert</th>
                                        <th>Amount</th>
                                        <th>Order Status</th>
                                        <th>Earning Status</th>
                                        <th>Stripe</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- TAB: STRIPE ACCOUNTS --}}
                <div id="tab-stripe" class="pcc-panel">
                    <div class="pcc-card">
                        <div class="pcc-card-header">
                            <h3><i class="fe fe-credit-card me-2" style="color:var(--clr-blue)"></i>Connected Stripe
                                Accounts</h3>
                        </div>
                        <div class="pcc-card-body">
                            <table class="table table-bordered w-100" id="stripeTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Expert</th>
                                        <th>Username</th>
                                        <th>Stripe Account</th>
                                        <th>Status</th>
                                        <th>Onboarded</th>
                                        <th>Available</th>
                                        <th>Clearing</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- TAB: WITHDRAWALS --}}
                <div id="tab-withdrawals" class="pcc-panel">
                    <div class="pcc-card">
                        <div class="pcc-card-header">
                            <h3><i class="fe fe-arrow-up-right me-2" style="color:var(--clr-blue)"></i>Withdrawal Requests
                            </h3>
                        </div>
                        <div class="pcc-card-body">
                            <table class="table table-bordered w-100" id="withdrawalsTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Withdrawal #</th>
                                        <th>Expert</th>
                                        <th>Amount</th>
                                        <th>Fee</th>
                                        <th>Net</th>
                                        <th>Status</th>
                                        <th>Stripe Transfer</th>
                                        <th>Requested</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ORDER DETAIL MODAL --}}
    <div class="modal fade" id="orderDetailModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order Details & Controls</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="orderDetailContent">
                    <div class="text-center py-5">
                        <div class="spinner-border mb-3" style="color:var(--clr-accent)"></div>
                        <div style="color:var(--clr-muted);font-size:13px">Loading order details...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- RELEASE ESCROW MODAL --}}
    <div class="modal fade" id="releaseEscrowModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" style="color:var(--clr-accent)">
                        <i class="fe fe-unlock me-2"></i>Release Escrow Early
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="releaseEscrowForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="pcc-alert success">
                            <i class="fe fe-info" style="flex-shrink:0;margin-top:1px"></i>
                            <span>This will immediately move the earning to "available", bypassing the 14-day clearing
                                period.</span>
                        </div>
                        <div id="releaseOrderSummary" class="mb-3"></div>
                        <div class="mb-3">
                            <label class="form-label">Reason for Early Release <span
                                    style="color:var(--clr-danger)">*</span></label>
                            <textarea class="form-control" name="reason" rows="3"
                                placeholder="e.g., Client requested early release, exceptional performance..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="pcc-btn pcc-btn-green">
                            <i class="fe fe-unlock"></i> Release Escrow
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- CANCEL ORDER MODAL --}}
    <div class="modal fade" id="cancelOrderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" style="color:var(--clr-danger)">
                        <i class="fe fe-alert-triangle me-2"></i>Cancel Order & Handle Refund
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="cancelOrderForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div id="cancelOrderSummary" class="mb-4"></div>

                        <label class="form-label mb-2">Refund Type</label>
                        <div class="refund-options mb-3">
                            <label class="refund-option">
                                <input type="radio" name="refund_type" value="full" checked>
                                <div class="rf-icon">💯</div>
                                <div class="rf-label">Full Refund</div>
                                <div class="rf-sub">Client gets 100% back</div>
                            </label>
                            <label class="refund-option">
                                <input type="radio" name="refund_type" value="partial">
                                <div class="rf-icon">⚖️</div>
                                <div class="rf-label">Partial / Negotiated</div>
                                <div class="rf-sub">Split between both parties</div>
                            </label>
                            <label class="refund-option">
                                <input type="radio" name="refund_type" value="none">
                                <div class="rf-icon">🚫</div>
                                <div class="rf-label">No Refund</div>
                                <div class="rf-sub">Expert keeps earnings</div>
                            </label>
                        </div>

                        <div id="partialAmountGroup" style="display:none;" class="mb-3">
                            <label class="form-label">Expert Compensation Amount ($)</label>
                            <input type="number" class="form-control" name="expert_compensation" min="0"
                                step="0.01" placeholder="0.00">
                            <small style="color:var(--clr-muted);font-size:11px;margin-top:4px;display:block">
                                Amount expert will receive. Remainder refunded to client.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Cancellation Reason <span
                                    style="color:var(--clr-danger)">*</span></label>
                            <textarea class="form-control" name="reason" rows="3" placeholder="Explain the reason for cancellation..."
                                required></textarea>
                        </div>

                        <div class="pcc-alert error">
                            <i class="fe fe-alert-triangle" style="flex-shrink:0;margin-top:1px"></i>
                            <span>Actual Stripe refund to client must be processed separately from your Stripe Dashboard.
                                This updates platform records only.</span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Go Back</button>
                        <button type="submit" class="pcc-btn pcc-btn-red">
                            <i class="fe fe-x-circle"></i> Confirm Cancellation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- FORCE TRANSFER MODAL --}}
    <div class="modal fade" id="forceTransferModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" style="color:var(--clr-blue)">
                        <i class="fe fe-zap me-2"></i>Force Stripe Transfer
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="forceTransferForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="pcc-alert info">
                            <i class="fe fe-zap" style="flex-shrink:0;margin-top:1px"></i>
                            <span>This will immediately trigger a Stripe Transfer to the expert's connected account,
                                bypassing the normal withdrawal flow.</span>
                        </div>
                        <div id="forceTransferSummary" class="mb-3"></div>
                        <div class="mb-3">
                            <label class="form-label">Reason <span style="color:var(--clr-danger)">*</span></label>
                            <textarea class="form-control" name="reason" rows="3" placeholder="e.g., Admin override, exceptional case..."
                                required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="pcc-btn pcc-btn-blue">
                            <i class="fe fe-send"></i> Execute Transfer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        // ─── Tab System ──────────────────────────────────────────────────────
        function switchTab(name, btn) {
            document.querySelectorAll('.pcc-panel').forEach(p => p.classList.remove('active'));
            document.querySelectorAll('.pcc-tab').forEach(b => b.classList.remove('active'));
            document.getElementById('tab-' + name).classList.add('active');
            btn.classList.add('active');
        }

        // ─── Badge Helpers ───────────────────────────────────────────────────
        const orderStatusMap = {
            pending_payment: ['yellow', 'Pending Payment'],
            active: ['blue', 'Active'],
            qa_pending: ['yellow', 'QA Review'],
            qa_approved: ['green', 'QA Approved'],
            qa_rejected: ['red', 'QA Rejected'],
            delivered: ['blue', 'Delivered'],
            completed: ['green', 'Completed'],
            cancelled: ['red', 'Cancelled'],
            disputed: ['red', 'Disputed'],
        };
        const earningStatusMap = {
            pending: ['yellow', 'Pending'],
            clearing: ['blue', 'Clearing (14d)'],
            available: ['green', 'Available'],
            withdrawn: ['gray', 'Withdrawn'],
            refunded: ['red', 'Refunded'],
            no_record: ['red', 'No Record'],
        };

        function badge(map, key) {
            const [cls, label] = map[key] || ['gray', key];
            return `<span class="pcc-badge ${cls}">${label}</span>`;
        }

        $(document).ready(function() {

            // ─── Orders Table ────────────────────────────────────────────
            $('#ordersTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.payments.orders') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        width: '5%'
                    },
                    {
                        data: 'order_number',
                        render: (d, t, row) =>
                            `<div style="font-family:monospace;color:var(--clr-accent);font-weight:600;font-size:13px">#${d}</div>
                     <div style="font-size:11px;color:var(--clr-muted)">${(row.gig?.title || 'Custom Order').substring(0,28)}...</div>`
                    },
                    {
                        data: 'buyer_name'
                    },
                    {
                        data: 'seller_name'
                    },
                    {
                        data: 'price',
                        render: (d, t, row) =>
                            `<div style="font-family:monospace;font-weight:700;color:var(--clr-text)">$${parseFloat(d).toFixed(2)}</div>
                     <div style="font-size:11px;color:var(--clr-muted)">Fee: $${parseFloat(row.platform_fee).toFixed(2)}</div>`
                    },
                    {
                        data: 'status',
                        render: d => badge(orderStatusMap, d)
                    },
                    {
                        data: 'earning_status',
                        render: d => badge(earningStatusMap, d)
                    },
                    {
                        data: 'stripe_connected',
                        orderable: false,
                        render: d => d ?
                            '<span class="pcc-badge green">Connected</span>' :
                            '<span class="pcc-badge red">Not Connected</span>'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                order: [
                    [0, 'desc']
                ],
                pageLength: 15,
            });

            // ─── Stripe Accounts Table ───────────────────────────────────
            $('#stripeTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.payments.stripe-accounts') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        width: '5%'
                    },
                    {
                        data: 'full_name'
                    },
                    {
                        data: 'username',
                        render: d => `<code style="color:var(--clr-muted);font-size:12px">${d}</code>`
                    },
                    {
                        data: 'stripe_account',
                        render: d => `<code style="font-size:11px;color:var(--clr-blue)">${d}</code>`
                    },
                    {
                        data: 'stripe_status',
                        orderable: false
                    },
                    {
                        data: 'onboarded_at'
                    },
                    {
                        data: 'available_balance',
                        render: d => `<span style="color:var(--clr-accent);font-weight:600">${d}</span>`
                    },
                    {
                        data: 'pending_clearance',
                        render: d => `<span style="color:var(--clr-warn);font-weight:600">${d}</span>`
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                order: [
                    [0, 'desc']
                ],
                pageLength: 15,
            });

            // ─── Withdrawals Table ───────────────────────────────────────
            const withdrawStatusMap = {
                pending: ['yellow', 'Pending'],
                processing: ['blue', 'Processing'],
                completed: ['green', 'Completed'],
                failed: ['red', 'Failed'],
                cancelled: ['gray', 'Cancelled'],
            };

            $('#withdrawalsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.payments.withdrawals') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'withdrawal_number',
                        render: d => `<code style="color:var(--clr-blue);font-size:12px">${d}</code>`
                    },
                    {
                        data: 'seller_name'
                    },
                    {
                        data: 'amount',
                        render: d =>
                            `<span style="font-family:monospace">$${parseFloat(d).toFixed(2)}</span>`
                    },
                    {
                        data: 'fee',
                        render: d =>
                            `<span style="color:var(--clr-muted)">$${parseFloat(d).toFixed(2)}</span>`
                    },
                    {
                        data: 'net_amount',
                        render: d =>
                            `<span style="color:var(--clr-accent);font-weight:600">$${parseFloat(d).toFixed(2)}</span>`
                    },
                    {
                        data: 'status',
                        render: d => badge(withdrawStatusMap, d)
                    },
                    {
                        data: 'stripe_transfer_id',
                        render: d => d ?
                            `<code style="font-size:10px;color:var(--clr-blue)">${d.substring(0,22)}...</code>` :
                            '<span style="color:var(--clr-muted-2)">—</span>'
                    },
                    {
                        data: 'requested_at',
                        render: d =>
                            `<span style="font-size:12px">${new Date(d).toLocaleDateString('en-US',{day:'numeric',month:'short',year:'numeric'})}</span>`
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                order: [
                    [0, 'desc']
                ],
                pageLength: 15,
            });

            // ─── Refund type toggle ──────────────────────────────────────
            $('input[name="refund_type"]').on('change', function() {
                $('#partialAmountGroup').toggle($(this).val() === 'partial');
            });
        });

        // ─── Order Details Modal ─────────────────────────────────────────────
        function openOrderDetails(orderId) {
            $('#orderDetailModal').modal('show');
            $('#orderDetailContent').html(`
        <div class="text-center py-5">
            <div class="spinner-border mb-3" style="color:var(--clr-accent)"></div>
            <div style="color:var(--clr-muted);font-size:13px">Loading...</div>
        </div>
    `);

            $.get(`{{ url('admin/payments/orders') }}/${orderId}/details`, function(res) {
                if (!res.success) {
                    $('#orderDetailContent').html(`<div class="pcc-alert error">${res.message}</div>`);
                    return;
                }

                const o = res.order;
                const s = res.stripe;
                const e = o.earning;

                const [sc, sl] = ({
                    pending_payment: ['yellow', 'Pending Payment'],
                    active: ['blue', 'Active'],
                    qa_pending: ['yellow', 'QA Pending'],
                    qa_approved: ['green', 'QA Approved'],
                    delivered: ['blue', 'Delivered'],
                    completed: ['green', 'Completed'],
                    cancelled: ['red', 'Cancelled'],
                })[o.status] || ['gray', o.status];

                const earningColors = {
                    pending: '#f59f00',
                    clearing: '#1971c2',
                    available: '#0ab87a',
                    withdrawn: '#7c8db5',
                    refunded: '#e03131'
                };
                const ec = earningColors[e?.status] || '#7c8db5';

                let actionsHtml = '';
                if (['pending', 'clearing'].includes(e?.status) && o.status === 'completed') {
                    actionsHtml +=
                        `<button class="pcc-btn pcc-btn-green me-2" onclick="openReleaseEscrow(${o.id},'${o.order_number}',${e?.net_amount||0})"><i class="fe fe-unlock"></i> Release Escrow Early</button>`;
                }
                if (s.is_ready && ['pending', 'clearing', 'available'].includes(e?.status)) {
                    actionsHtml +=
                        `<button class="pcc-btn pcc-btn-blue me-2" onclick="openForceTransfer(${o.id},'${o.order_number}',${e?.net_amount||0},'${s.account_id}')"><i class="fe fe-zap"></i> Force Stripe Transfer</button>`;
                }
                if (!['cancelled', 'completed'].includes(o.status)) {
                    actionsHtml +=
                        `<button class="pcc-btn pcc-btn-red" onclick="openCancelOrder(${o.id},'${o.order_number}',${o.price})"><i class="fe fe-x-circle"></i> Cancel Order</button>`;
                }

                $('#orderDetailContent').html(`
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="detail-panel">
                        <div class="detail-panel-title">Order Info</div>
                        <div class="detail-row"><span class="detail-key">Order Number</span><span style="font-family:monospace;color:var(--clr-accent);font-weight:700">#${o.order_number}</span></div>
                        <div class="detail-row"><span class="detail-key">Status</span><span class="pcc-badge ${sc}">${sl}</span></div>
                        <div class="detail-row"><span class="detail-key">Total Price</span><span class="detail-money">$${parseFloat(o.price).toFixed(2)}</span></div>
                        <div class="detail-row"><span class="detail-key">Platform Fee</span><span class="detail-val" style="color:var(--clr-warn)">$${parseFloat(o.platform_fee).toFixed(2)}</span></div>
                        <div class="detail-row"><span class="detail-key">Expert Earns</span><span class="detail-money">$${parseFloat(o.seller_earnings).toFixed(2)}</span></div>
                        <div class="detail-row"><span class="detail-key">Created</span><span class="detail-val">${new Date(o.created_at).toLocaleDateString('en-US',{day:'numeric',month:'short',year:'numeric'})}</span></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-panel">
                        <div class="detail-panel-title">Payment & Escrow</div>
                        <div class="detail-row">
                            <span class="detail-key">Earning Status</span>
                            <span style="color:${ec};font-weight:700;font-size:12px;text-transform:uppercase">${e?.status || 'No Record'}</span>
                        </div>
                        <div class="detail-row"><span class="detail-key">Net to Expert</span><span class="detail-money">$${parseFloat(e?.net_amount||0).toFixed(2)}</span></div>
                        <div class="detail-row"><span class="detail-key">Available At</span><span class="detail-val">${e?.available_at ? new Date(e.available_at).toLocaleDateString('en-US',{day:'numeric',month:'short',year:'numeric'}) : '—'}</span></div>
                        <div class="detail-row">
                            <span class="detail-key">Stripe Account</span>
                            ${s.account_id ? `<code style="font-size:11px;color:var(--clr-blue)">${s.account_id}</code>` : '<span style="color:var(--clr-danger);font-size:12px">Not Connected</span>'}
                        </div>
                        <div class="detail-row">
                            <span class="detail-key">Stripe Ready</span>
                            ${s.is_ready ? '<span class="pcc-badge green">Active</span>' : '<span class="pcc-badge red">Not Ready</span>'}
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-panel">
                        <div class="detail-panel-title">Client</div>
                        <div class="detail-row"><span class="detail-key">Name</span><span class="detail-val">${o.buyer?.profile?.first_name||''} ${o.buyer?.profile?.last_name||''}</span></div>
                        <div class="detail-row"><span class="detail-key">Email</span><span class="detail-val">${o.buyer?.email||'—'}</span></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-panel">
                        <div class="detail-panel-title">Expert</div>
                        <div class="detail-row"><span class="detail-key">Name</span><span class="detail-val">${o.seller?.profile?.first_name||''} ${o.seller?.profile?.last_name||''}</span></div>
                        <div class="detail-row"><span class="detail-key">Email</span><span class="detail-val">${o.seller?.email||'—'}</span></div>
                    </div>
                </div>
                ${actionsHtml ? `
                    <div class="col-12">
                        <div class="detail-panel">
                            <div class="detail-panel-title">Admin Actions</div>
                            <div class="d-flex flex-wrap gap-2">${actionsHtml}</div>
                        </div>
                    </div>` : ''}
            </div>
        `);
            }).fail(() => {
                $('#orderDetailContent').html(
                    '<div class="pcc-alert error"><i class="fe fe-alert-circle"></i> Failed to load order details.</div>'
                    );
            });
        }

        // ─── Release Escrow ──────────────────────────────────────────────────
        function openReleaseEscrow(orderId, orderNum, amount) {
            $('#orderDetailModal').modal('hide');
            setTimeout(() => {
                $('#releaseOrderSummary').html(`
            <div class="detail-panel d-flex justify-content-between align-items-center">
                <div><div style="font-size:11px;color:var(--clr-muted)">Order</div><div style="font-weight:700;color:var(--clr-text)">#${orderNum}</div></div>
                <div style="text-align:right"><div style="font-size:11px;color:var(--clr-muted)">Amount</div><div style="font-size:20px;font-weight:700;color:var(--clr-accent);font-family:monospace">$${parseFloat(amount).toFixed(2)}</div></div>
            </div>
        `);
                $('#releaseEscrowForm').attr('action',
                    `{{ url('admin/payments/orders') }}/${orderId}/release-escrow`);
                $('#releaseEscrowModal').modal('show');
            }, 300);
        }

        // ─── Cancel Order ────────────────────────────────────────────────────
        function openCancelOrder(orderId, orderNum, price) {
            $('#orderDetailModal').modal('hide');
            setTimeout(() => {
                $('#cancelOrderSummary').html(`
            <div class="detail-panel d-flex justify-content-between align-items-center">
                <div><div style="font-size:11px;color:var(--clr-muted)">Order</div><div style="font-weight:700;color:var(--clr-text)">#${orderNum}</div></div>
                <div style="text-align:right"><div style="font-size:11px;color:var(--clr-muted)">Total Amount</div><div style="font-size:20px;font-weight:700;color:var(--clr-danger);font-family:monospace">$${parseFloat(price).toFixed(2)}</div></div>
            </div>
        `);
                $('#cancelOrderForm').attr('action', `{{ url('admin/payments/orders') }}/${orderId}/cancel`);
                $('#cancelOrderModal').modal('show');
            }, 300);
        }

        // ─── Force Transfer ──────────────────────────────────────────────────
        function openForceTransfer(orderId, orderNum, amount, stripeAccount) {
            $('#orderDetailModal').modal('hide');
            setTimeout(() => {
                $('#forceTransferSummary').html(`
            <div class="detail-panel">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div><div style="font-size:11px;color:var(--clr-muted)">Order</div><div style="font-weight:700;color:var(--clr-text)">#${orderNum}</div></div>
                    <div style="text-align:right"><div style="font-size:11px;color:var(--clr-muted)">Transfer Amount</div><div style="font-size:20px;font-weight:700;color:var(--clr-blue);font-family:monospace">$${parseFloat(amount).toFixed(2)}</div></div>
                </div>
                <div style="font-size:11px;color:var(--clr-muted)">Destination: <code style="color:var(--clr-blue)">${stripeAccount}</code></div>
            </div>
        `);
                $('#forceTransferForm').attr('action',
                    `{{ url('admin/payments/orders') }}/${orderId}/force-transfer`);
                $('#forceTransferModal').modal('show');
            }, 300);
        }
    </script>
@endpush
