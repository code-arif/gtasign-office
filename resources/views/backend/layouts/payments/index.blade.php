@extends('backend.app')

@section('title', 'Payment Control Center')

@push('styles')
    <link href="{{ asset('default/datatable.css') }}" rel="stylesheet" />
    <style>
        :root {
            --clr-bg: #0d0f14;
            --clr-surface: #13161e;
            --clr-surface-2: #1a1e28;
            --clr-border: #252a38;
            --clr-accent: #00c896;
            --clr-accent-dim: rgba(0,200,150,0.12);
            --clr-warn: #f5a623;
            --clr-warn-dim: rgba(245,166,35,0.12);
            --clr-danger: #ff4d4f;
            --clr-danger-dim: rgba(255,77,79,0.12);
            --clr-blue: #4a9eff;
            --clr-blue-dim: rgba(74,158,255,0.12);
            --clr-text: #e2e8f0;
            --clr-muted: #6b7a99;
            --radius: 12px;
        }

        body { background: var(--clr-bg); }

        /* ─── STAT CARDS ─── */
        .pcc-stats { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; margin-bottom: 28px; }

        .pcc-stat {
            background: var(--clr-surface);
            border: 1px solid var(--clr-border);
            border-radius: var(--radius);
            padding: 20px 22px;
            position: relative;
            overflow: hidden;
            transition: transform .2s, box-shadow .2s;
        }
        .pcc-stat:hover { transform: translateY(-2px); box-shadow: 0 8px 32px rgba(0,0,0,.4); }
        .pcc-stat::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: var(--accent-color, var(--clr-accent));
        }
        .pcc-stat .stat-icon {
            width: 40px; height: 40px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            margin-bottom: 12px;
            background: var(--icon-bg, var(--clr-accent-dim));
            color: var(--accent-color, var(--clr-accent));
        }
        .pcc-stat .stat-label { font-size: 11px; text-transform: uppercase; letter-spacing: .08em; color: var(--clr-muted); margin-bottom: 4px; }
        .pcc-stat .stat-value { font-size: 24px; font-weight: 700; color: var(--clr-text); line-height: 1; }
        .pcc-stat .stat-sub { font-size: 11px; color: var(--clr-muted); margin-top: 6px; }

        /* ─── TABS ─── */
        .pcc-tabs {
            display: flex; gap: 4px;
            background: var(--clr-surface);
            border: 1px solid var(--clr-border);
            border-radius: var(--radius);
            padding: 6px;
            margin-bottom: 24px;
        }
        .pcc-tab {
            flex: 1; padding: 10px 16px;
            border-radius: 8px;
            border: none; background: transparent;
            color: var(--clr-muted);
            font-size: 13px; font-weight: 500;
            cursor: pointer; transition: all .2s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .pcc-tab:hover { color: var(--clr-text); background: var(--clr-surface-2); }
        .pcc-tab.active { background: var(--clr-accent-dim); color: var(--clr-accent); }
        .pcc-tab .tab-badge {
            background: var(--clr-accent); color: #000;
            border-radius: 99px; font-size: 10px; font-weight: 700;
            padding: 1px 7px; min-width: 18px; text-align: center;
        }

        /* ─── PANELS ─── */
        .pcc-panel { display: none; }
        .pcc-panel.active { display: block; }

        /* ─── CARD ─── */
        .pcc-card {
            background: var(--clr-surface);
            border: 1px solid var(--clr-border);
            border-radius: var(--radius);
            overflow: hidden;
        }
        .pcc-card-header {
            padding: 16px 22px;
            border-bottom: 1px solid var(--clr-border);
            display: flex; align-items: center; justify-content: space-between;
        }
        .pcc-card-header h3 { margin: 0; font-size: 14px; font-weight: 600; color: var(--clr-text); }
        .pcc-card-body { padding: 20px 22px; }

        /* ─── TABLE ─── */
        .pcc-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .pcc-table th {
            padding: 10px 14px; text-align: left;
            font-size: 11px; font-weight: 600;
            text-transform: uppercase; letter-spacing: .06em;
            color: var(--clr-muted);
            border-bottom: 1px solid var(--clr-border);
        }
        .pcc-table td {
            padding: 12px 14px;
            border-bottom: 1px solid rgba(37,42,56,.5);
            color: var(--clr-text);
            vertical-align: middle;
        }
        .pcc-table tr:last-child td { border-bottom: none; }
        .pcc-table tr:hover td { background: var(--clr-surface-2); }

        /* ─── BADGES ─── */
        .pcc-badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 10px; border-radius: 99px;
            font-size: 11px; font-weight: 600;
        }
        .pcc-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
        .pcc-badge.green { background: var(--clr-accent-dim); color: var(--clr-accent); }
        .pcc-badge.yellow { background: var(--clr-warn-dim); color: var(--clr-warn); }
        .pcc-badge.red { background: var(--clr-danger-dim); color: var(--clr-danger); }
        .pcc-badge.blue { background: var(--clr-blue-dim); color: var(--clr-blue); }
        .pcc-badge.gray { background: rgba(107,122,153,.12); color: var(--clr-muted); }

        /* ─── BUTTONS ─── */
        .pcc-btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 14px; border-radius: 8px;
            font-size: 12px; font-weight: 600;
            border: none; cursor: pointer; transition: all .15s;
            text-decoration: none;
        }
        .pcc-btn-green { background: var(--clr-accent-dim); color: var(--clr-accent); }
        .pcc-btn-green:hover { background: var(--clr-accent); color: #000; }
        .pcc-btn-yellow { background: var(--clr-warn-dim); color: var(--clr-warn); }
        .pcc-btn-yellow:hover { background: var(--clr-warn); color: #000; }
        .pcc-btn-red { background: var(--clr-danger-dim); color: var(--clr-danger); }
        .pcc-btn-red:hover { background: var(--clr-danger); color: #fff; }
        .pcc-btn-blue { background: var(--clr-blue-dim); color: var(--clr-blue); }
        .pcc-btn-blue:hover { background: var(--clr-blue); color: #fff; }
        .pcc-btn-ghost { background: var(--clr-surface-2); color: var(--clr-muted); border: 1px solid var(--clr-border); }
        .pcc-btn-ghost:hover { color: var(--clr-text); border-color: var(--clr-muted); }

        /* ─── MODAL OVERRIDES ─── */
        .modal-content {
            background: var(--clr-surface) !important;
            border: 1px solid var(--clr-border) !important;
            border-radius: var(--radius) !important;
            color: var(--clr-text) !important;
        }
        .modal-header { border-bottom-color: var(--clr-border) !important; }
        .modal-footer { border-top-color: var(--clr-border) !important; }
        .form-control, .form-select {
            background: var(--clr-surface-2) !important;
            border-color: var(--clr-border) !important;
            color: var(--clr-text) !important;
            border-radius: 8px !important;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--clr-accent) !important;
            box-shadow: 0 0 0 3px var(--clr-accent-dim) !important;
        }
        .form-label { color: var(--clr-muted) !important; font-size: 12px !important; font-weight: 600 !important; text-transform: uppercase !important; letter-spacing: .05em !important; }
        .btn-close { filter: invert(1); }

        /* ─── ORDER DETAIL PANEL ─── */
        .detail-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--clr-border); }
        .detail-row:last-child { border-bottom: none; }
        .detail-key { color: var(--clr-muted); font-size: 12px; }
        .detail-val { color: var(--clr-text); font-size: 13px; font-weight: 500; }
        .detail-money { color: var(--clr-accent); font-size: 15px; font-weight: 700; font-family: monospace; }

        /* ─── ALERT ─── */
        .pcc-alert {
            padding: 12px 16px; border-radius: 8px; margin-bottom: 16px;
            font-size: 13px; display: flex; align-items: flex-start; gap: 10px;
        }
        .pcc-alert.success { background: var(--clr-accent-dim); color: var(--clr-accent); border: 1px solid rgba(0,200,150,.25); }
        .pcc-alert.error { background: var(--clr-danger-dim); color: var(--clr-danger); border: 1px solid rgba(255,77,79,.25); }

        /* ─── DATATABLE DARK ─── */
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            background: var(--clr-surface-2) !important;
            border: 1px solid var(--clr-border) !important;
            color: var(--clr-text) !important;
            border-radius: 6px !important;
        }
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_length label,
        .dataTables_wrapper .dataTables_filter label { color: var(--clr-muted) !important; font-size: 12px !important; }
        .dataTables_wrapper .dataTables_paginate .paginate_button { color: var(--clr-muted) !important; border-radius: 6px !important; }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--clr-accent-dim) !important;
            color: var(--clr-accent) !important; border: none !important;
        }

        /* ─── PAGE HEADER ─── */
        .pcc-page-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 28px;
        }
        .pcc-page-header h1 { font-size: 22px; font-weight: 700; color: var(--clr-text); margin: 0; }
        .pcc-page-header p { color: var(--clr-muted); font-size: 13px; margin: 4px 0 0; }

        /* ─── RADIOS for refund type ─── */
        .refund-options { display: flex; gap: 10px; flex-wrap: wrap; }
        .refund-option {
            flex: 1; min-width: 100px;
            border: 1px solid var(--clr-border);
            border-radius: 8px; padding: 12px;
            cursor: pointer; text-align: center;
            transition: all .15s;
        }
        .refund-option input { display: none; }
        .refund-option:has(input:checked) { border-color: var(--clr-accent); background: var(--clr-accent-dim); }
        .refund-option .rf-icon { font-size: 20px; margin-bottom: 4px; }
        .refund-option .rf-label { font-size: 12px; font-weight: 600; color: var(--clr-text); }
        .refund-option .rf-sub { font-size: 11px; color: var(--clr-muted); }

        .section-divider { height: 1px; background: var(--clr-border); margin: 20px 0; }

        /* scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: var(--clr-bg); }
        ::-webkit-scrollbar-thumb { background: var(--clr-border); border-radius: 3px; }
    </style>
@endpush

@section('content')
<div class="app-content main-content mt-0">
    <div class="side-app" style="margin-bottom: 60px">
        <div class="main-container container-fluid">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="pcc-alert success mt-3"><i class="fe fe-check-circle"></i> {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="pcc-alert error mt-3"><i class="fe fe-alert-circle"></i> {{ session('error') }}</div>
            @endif

            {{-- Page Header --}}
            <div class="pcc-page-header mt-3">
                <div>
                    <h1>💳 Payment Control Center</h1>
                    <p>Full control over orders, escrow, Stripe accounts, and payouts</p>
                </div>
                <div style="color: var(--clr-muted); font-size: 12px;">
                    Last updated: {{ now()->format('d M Y, H:i') }}
                </div>
            </div>

            {{-- Stats Grid --}}
            <div class="pcc-stats">
                <div class="pcc-stat" style="--accent-color: var(--clr-accent); --icon-bg: var(--clr-accent-dim)">
                    <div class="stat-icon"><i class="fe fe-shopping-bag"></i></div>
                    <div class="stat-label">Total Revenue</div>
                    <div class="stat-value">${{ number_format($stats['total_revenue'], 0) }}</div>
                    <div class="stat-sub">{{ $stats['completed_orders'] }} completed orders</div>
                </div>
                <div class="pcc-stat" style="--accent-color: var(--clr-blue); --icon-bg: var(--clr-blue-dim)">
                    <div class="stat-icon"><i class="fe fe-percent"></i></div>
                    <div class="stat-label">Platform Fees</div>
                    <div class="stat-value">${{ number_format($stats['platform_fees'], 0) }}</div>
                    <div class="stat-sub">10% of completed orders</div>
                </div>
                <div class="pcc-stat" style="--accent-color: var(--clr-warn); --icon-bg: var(--clr-warn-dim)">
                    <div class="stat-icon"><i class="fe fe-lock"></i></div>
                    <div class="stat-label">In Escrow</div>
                    <div class="stat-value">${{ number_format($stats['in_escrow'], 0) }}</div>
                    <div class="stat-sub">Pending + clearing</div>
                </div>
                <div class="pcc-stat" style="--accent-color: var(--clr-accent); --icon-bg: var(--clr-accent-dim)">
                    <div class="stat-icon"><i class="fe fe-dollar-sign"></i></div>
                    <div class="stat-label">Available to Experts</div>
                    <div class="stat-value">${{ number_format($stats['available_to_experts'], 0) }}</div>
                    <div class="stat-sub">Ready to withdraw</div>
                </div>
                <div class="pcc-stat" style="--accent-color: #a78bfa; --icon-bg: rgba(167,139,250,.12)">
                    <div class="stat-icon"><i class="fe fe-send"></i></div>
                    <div class="stat-label">Total Withdrawn</div>
                    <div class="stat-value">${{ number_format($stats['withdrawn'], 0) }}</div>
                    <div class="stat-sub">Paid out to experts</div>
                </div>
                <div class="pcc-stat" style="--accent-color: var(--clr-warn); --icon-bg: var(--clr-warn-dim)">
                    <div class="stat-icon"><i class="fe fe-clock"></i></div>
                    <div class="stat-label">Pending Withdrawals</div>
                    <div class="stat-value">{{ $stats['pending_withdrawals'] }}</div>
                    <div class="stat-sub">Awaiting processing</div>
                </div>
                <div class="pcc-stat" style="--accent-color: var(--clr-blue); --icon-bg: var(--clr-blue-dim)">
                    <div class="stat-icon"><i class="fe fe-zap"></i></div>
                    <div class="stat-label">Active Orders</div>
                    <div class="stat-value">{{ $stats['active_orders'] }}</div>
                    <div class="stat-sub">In progress right now</div>
                </div>
                <div class="pcc-stat" style="--accent-color: var(--clr-accent); --icon-bg: var(--clr-accent-dim)">
                    <div class="stat-icon"><i class="fe fe-link"></i></div>
                    <div class="stat-label">Connected Experts</div>
                    {{-- <div class="stat-value">{{ $stats['connected_experts'] }}</div> --}}
                    <div class="stat-sub">Stripe accounts linked</div>
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
                    {{-- <span class="tab-badge" style="background:#4a9eff">{{ $stats['connected_experts'] }}</span> --}}
                </button>
                <button class="pcc-tab" onclick="switchTab('withdrawals', this)">
                    <i class="fe fe-arrow-up-right"></i> Withdrawals
                    @if($stats['pending_withdrawals'] > 0)
                        <span class="tab-badge" style="background:var(--clr-warn); color:#000">{{ $stats['pending_withdrawals'] }}</span>
                    @endif
                </button>
            </div>

            {{-- ═══════════════════════════════════════════════ --}}
            {{-- TAB: ORDERS                                       --}}
            {{-- ═══════════════════════════════════════════════ --}}
            <div id="tab-orders" class="pcc-panel active">
                <div class="pcc-card">
                    <div class="pcc-card-header">
                        <h3><i class="fe fe-list me-2"></i>All Orders — Payment Control</h3>
                        <div style="font-size:12px; color:var(--clr-muted)">Click <strong style="color:var(--clr-text)">Details</strong> for full control</div>
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

            {{-- ═══════════════════════════════════════════════ --}}
            {{-- TAB: STRIPE ACCOUNTS                             --}}
            {{-- ═══════════════════════════════════════════════ --}}
            <div id="tab-stripe" class="pcc-panel">
                <div class="pcc-card">
                    <div class="pcc-card-header">
                        <h3><i class="fe fe-credit-card me-2"></i>Connected Stripe Accounts</h3>
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

            {{-- ═══════════════════════════════════════════════ --}}
            {{-- TAB: WITHDRAWALS                                  --}}
            {{-- ═══════════════════════════════════════════════ --}}
            <div id="tab-withdrawals" class="pcc-panel">
                <div class="pcc-card">
                    <div class="pcc-card-header">
                        <h3><i class="fe fe-arrow-up-right me-2"></i>Withdrawal Requests</h3>
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

{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- ORDER DETAIL MODAL                                                    --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="orderDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="color:var(--clr-text)">📦 Order Details & Controls</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="orderDetailContent">
                <div class="text-center py-5" style="color:var(--clr-muted)">
                    <div class="spinner-border text-success mb-3"></div>
                    <div>Loading order details...</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- RELEASE ESCROW MODAL                                                  --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="releaseEscrowModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="color:var(--clr-accent)">🔓 Release Escrow Early</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="releaseEscrowForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="pcc-alert success mb-3">
                        <i class="fe fe-info"></i>
                        <div>This will immediately move the earning to "available" status, bypassing the 14-day clearing period.</div>
                    </div>
                    <div id="releaseOrderSummary" class="mb-3"></div>
                    <div class="mb-3">
                        <label class="form-label">Reason for Early Release <span style="color:var(--clr-danger)">*</span></label>
                        <textarea class="form-control" name="reason" rows="3" placeholder="e.g., Client requested early release, exceptional performance..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="pcc-btn pcc-btn-green">
                        <i class="fe fe-unlock"></i> Release Escrow
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- CANCEL ORDER MODAL                                                    --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="cancelOrderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="color:var(--clr-danger)">⚠️ Cancel Order & Handle Refund</h5>
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
                            <div class="rf-sub">Client gets 100% back. Expert gets nothing.</div>
                        </label>
                        <label class="refund-option">
                            <input type="radio" name="refund_type" value="partial">
                            <div class="rf-icon">⚖️</div>
                            <div class="rf-label">Partial / Negotiated</div>
                            <div class="rf-sub">Split between client & expert.</div>
                        </label>
                        <label class="refund-option">
                            <input type="radio" name="refund_type" value="none">
                            <div class="rf-icon">🚫</div>
                            <div class="rf-label">No Refund</div>
                            <div class="rf-sub">Expert keeps full earnings.</div>
                        </label>
                    </div>

                    <div id="partialAmountGroup" style="display:none;" class="mb-3">
                        <label class="form-label">Expert Compensation Amount ($)</label>
                        <input type="number" class="form-control" name="expert_compensation" min="0" step="0.01" placeholder="0.00">
                        <small style="color:var(--clr-muted); font-size:11px">Amount expert will receive. Remainder is refunded to client.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Cancellation Reason <span style="color:var(--clr-danger)">*</span></label>
                        <textarea class="form-control" name="reason" rows="3" placeholder="Explain why this order is being cancelled..." required></textarea>
                    </div>

                    <div class="pcc-alert error">
                        <i class="fe fe-alert-triangle"></i>
                        <div>Note: Actual Stripe refund to client must be processed separately from the Stripe dashboard. This action updates the platform records only.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Go Back</button>
                    <button type="submit" class="pcc-btn pcc-btn-red">
                        <i class="fe fe-x-circle"></i> Confirm Cancellation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- FORCE TRANSFER MODAL                                                  --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="forceTransferModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="color:var(--clr-blue)">⚡ Force Stripe Transfer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="forceTransferForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="pcc-alert" style="background:var(--clr-blue-dim);color:var(--clr-blue);border:1px solid rgba(74,158,255,.25)" class="mb-3">
                        <i class="fe fe-zap"></i>
                        <div>This will immediately trigger a Stripe Transfer to the expert's connected account, bypassing the normal withdrawal flow.</div>
                    </div>
                    <div id="forceTransferSummary" class="mb-3"></div>
                    <div class="mb-3">
                        <label class="form-label">Reason <span style="color:var(--clr-danger)">*</span></label>
                        <textarea class="form-control" name="reason" rows="3" placeholder="e.g., Admin override, exceptional case..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
// ─── Tab System ─────────────────────────────────────────────────────
function switchTab(name, btn) {
    document.querySelectorAll('.pcc-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.pcc-tab').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    btn.classList.add('active');
}

// ─── Status Badge Maps ───────────────────────────────────────────────
const orderStatusMap = {
    pending_payment: ['yellow', 'Pending Payment'],
    active:          ['blue',   'Active'],
    qa_pending:      ['yellow', 'QA Review'],
    qa_approved:     ['green',  'QA Approved'],
    qa_rejected:     ['red',    'QA Rejected'],
    delivered:       ['blue',   'Delivered'],
    completed:       ['green',  'Completed'],
    cancelled:       ['red',    'Cancelled'],
    disputed:        ['red',    'Disputed'],
};

const earningStatusMap = {
    pending:    ['yellow', 'Pending'],
    clearing:   ['blue',   'Clearing (14d)'],
    available:  ['green',  'Available'],
    withdrawn:  ['gray',   'Withdrawn'],
    refunded:   ['red',    'Refunded'],
    no_record:  ['red',    'No Record'],
};

function badge(map, key) {
    const [cls, label] = map[key] || ['gray', key];
    return `<span class="pcc-badge ${cls}">${label}</span>`;
}

// ─── Orders DataTable ────────────────────────────────────────────────
$(document).ready(function () {
    var ordersTable = $('#ordersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.payments.orders') }}",
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false, width: '5%' },
            {
                data: 'order_number', render: (d, t, row) =>
                    `<div style="font-family:monospace;color:var(--clr-accent);font-weight:600">#${d}</div>
                     <div style="font-size:11px;color:var(--clr-muted)">${row.gig?.title?.substring(0,30) || 'Custom Order'}...</div>`
            },
            { data: 'buyer_name', render: d => `<span style="color:var(--clr-text)">${d}</span>` },
            { data: 'seller_name', render: d => `<span style="color:var(--clr-text)">${d}</span>` },
            {
                data: 'price', render: (d, t, row) =>
                    `<div style="font-family:monospace;color:var(--clr-accent);font-weight:700">$${parseFloat(d).toFixed(2)}</div>
                     <div style="font-size:11px;color:var(--clr-muted)">Fee: $${parseFloat(row.platform_fee).toFixed(2)}</div>`
            },
            { data: 'status', render: d => badge(orderStatusMap, d) },
            { data: 'earning_status', render: d => badge(earningStatusMap, d) },
            {
                data: 'stripe_connected',
                orderable: false,
                render: d => d
                    ? '<span class="pcc-badge green">Connected</span>'
                    : '<span class="pcc-badge red">Not Connected</span>'
            },
            { data: 'action', orderable: false, searchable: false },
        ],
        order: [[0, 'desc']],
        pageLength: 15,
    });

    // ─── Stripe Accounts DataTable ────────────────────────────────
    var stripeTable = $('#stripeTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.payments.stripe-accounts') }}",
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false, width: '5%' },
            { data: 'full_name' },
            { data: 'username', render: d => `<code style="color:var(--clr-muted)">${d}</code>` },
            { data: 'stripe_account', render: d => `<code style="font-size:11px;color:var(--clr-blue)">${d}</code>` },
            { data: 'stripe_status', orderable: false },
            { data: 'onboarded_at' },
            { data: 'available_balance', render: d => `<span style="color:var(--clr-accent);font-weight:700">${d}</span>` },
            { data: 'pending_clearance', render: d => `<span style="color:var(--clr-warn)">${d}</span>` },
            { data: 'action', orderable: false, searchable: false },
        ],
        order: [[0, 'desc']],
        pageLength: 15,
    });

    // ─── Withdrawals DataTable ────────────────────────────────────
    var withdrawStatusMap = {
        pending:    ['yellow', 'Pending'],
        processing: ['blue',   'Processing'],
        completed:  ['green',  'Completed'],
        failed:     ['red',    'Failed'],
        cancelled:  ['gray',   'Cancelled'],
    };

    var withdrawalsTable = $('#withdrawalsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.payments.withdrawals') }}",
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'withdrawal_number', render: d => `<code style="color:var(--clr-blue)">${d}</code>` },
            { data: 'seller_name' },
            { data: 'amount', render: d => `<span style="font-family:monospace">$${parseFloat(d).toFixed(2)}</span>` },
            { data: 'fee', render: d => `<span style="color:var(--clr-muted)">$${parseFloat(d).toFixed(2)}</span>` },
            { data: 'net_amount', render: d => `<span style="color:var(--clr-accent);font-weight:700">$${parseFloat(d).toFixed(2)}</span>` },
            { data: 'status', render: d => badge(withdrawStatusMap, d) },
            {
                data: 'stripe_transfer_id',
                render: d => d
                    ? `<code style="font-size:10px;color:var(--clr-blue)">${d.substring(0,20)}...</code>`
                    : '<span style="color:var(--clr-muted)">—</span>'
            },
            { data: 'requested_at', render: d => new Date(d).toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' }) },
            { data: 'action', orderable: false, searchable: false },
        ],
        order: [[0, 'desc']],
        pageLength: 15,
    });

    // ─── Refund Type Toggle ────────────────────────────────────────
    $('input[name="refund_type"]').on('change', function () {
        $('#partialAmountGroup').toggle($(this).val() === 'partial');
    });
});

// ─── Open Order Details Modal ────────────────────────────────────────
function openOrderDetails(orderId) {
    $('#orderDetailModal').modal('show');
    $('#orderDetailContent').html('<div class="text-center py-5" style="color:var(--clr-muted)"><div class="spinner-border text-success mb-3"></div><div>Loading...</div></div>');

    $.get(`{{ url('admin/payments/orders') }}/${orderId}/details`, function (res) {
        if (!res.success) { $('#orderDetailContent').html(`<div class="pcc-alert error">${res.message}</div>`); return; }

        const o = res.order;
        const s = res.stripe;
        const e = o.earning;

        const statusMap = {
            pending_payment: ['yellow', 'Pending Payment'], active: ['blue', 'Active'],
            qa_pending: ['yellow', 'QA Pending'], qa_approved: ['green', 'QA Approved'],
            delivered: ['blue', 'Delivered'], completed: ['green', 'Completed'],
            cancelled: ['red', 'Cancelled'],
        };
        const [sc, sl] = statusMap[o.status] || ['gray', o.status];

        const earningStatusColors = { pending: '#f5a623', clearing: '#4a9eff', available: '#00c896', withdrawn: '#6b7a99', refunded: '#ff4d4f', };
        const ec = earningStatusColors[e?.status] || '#6b7a99';

        let actionsHtml = '';
        if (['pending', 'clearing'].includes(e?.status) && o.status === 'completed') {
            actionsHtml += `<button class="pcc-btn pcc-btn-green me-2" onclick="openReleaseEscrow(${o.id}, '${o.order_number}', ${e?.net_amount || 0})"><i class="fe fe-unlock"></i> Release Escrow Early</button>`;
        }
        if (s.is_ready && ['pending', 'clearing', 'available'].includes(e?.status)) {
            actionsHtml += `<button class="pcc-btn pcc-btn-blue me-2" onclick="openForceTransfer(${o.id}, '${o.order_number}', ${e?.net_amount || 0}, '${s.account_id}')"><i class="fe fe-zap"></i> Force Stripe Transfer</button>`;
        }
        if (!['cancelled', 'completed'].includes(o.status)) {
            actionsHtml += `<button class="pcc-btn pcc-btn-red" onclick="openCancelOrder(${o.id}, '${o.order_number}', ${o.price})"><i class="fe fe-x-circle"></i> Cancel Order</button>`;
        }

        $('#orderDetailContent').html(`
            <div class="row g-4">
                <div class="col-md-6">
                    <div style="background:var(--clr-surface-2);border-radius:10px;padding:20px">
                        <div style="font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:var(--clr-muted);margin-bottom:12px">Order Info</div>
                        <div class="detail-row"><span class="detail-key">Order Number</span><span style="font-family:monospace;color:var(--clr-accent);font-weight:700">#${o.order_number}</span></div>
                        <div class="detail-row"><span class="detail-key">Status</span><span class="pcc-badge ${sc}">${sl}</span></div>
                        <div class="detail-row"><span class="detail-key">Total Price</span><span class="detail-money">$${parseFloat(o.price).toFixed(2)}</span></div>
                        <div class="detail-row"><span class="detail-key">Platform Fee</span><span class="detail-val" style="color:var(--clr-warn)">$${parseFloat(o.platform_fee).toFixed(2)}</span></div>
                        <div class="detail-row"><span class="detail-key">Expert Earns</span><span class="detail-money">$${parseFloat(o.seller_earnings).toFixed(2)}</span></div>
                        <div class="detail-row"><span class="detail-key">Created</span><span class="detail-val">${new Date(o.created_at).toLocaleDateString('en-US',{day:'numeric',month:'short',year:'numeric'})}</span></div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div style="background:var(--clr-surface-2);border-radius:10px;padding:20px">
                        <div style="font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:var(--clr-muted);margin-bottom:12px">Payment & Escrow</div>
                        <div class="detail-row">
                            <span class="detail-key">Earning Status</span>
                            <span style="color:${ec};font-weight:700;font-size:13px">${e?.status?.toUpperCase() || 'NO RECORD'}</span>
                        </div>
                        <div class="detail-row"><span class="detail-key">Net to Expert</span><span class="detail-money">$${parseFloat(e?.net_amount || 0).toFixed(2)}</span></div>
                        <div class="detail-row"><span class="detail-key">Available At</span><span class="detail-val">${e?.available_at ? new Date(e.available_at).toLocaleDateString('en-US',{day:'numeric',month:'short',year:'numeric'}) : '—'}</span></div>
                        <div class="detail-row">
                            <span class="detail-key">Stripe Account</span>
                            ${s.account_id
                                ? `<code style="font-size:11px;color:var(--clr-blue)">${s.account_id}</code>`
                                : '<span style="color:var(--clr-danger);font-size:12px">Not Connected</span>'}
                        </div>
                        <div class="detail-row">
                            <span class="detail-key">Stripe Ready</span>
                            ${s.is_ready
                                ? '<span class="pcc-badge green">Active</span>'
                                : '<span class="pcc-badge red">Not Ready</span>'}
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div style="background:var(--clr-surface-2);border-radius:10px;padding:20px">
                        <div style="font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:var(--clr-muted);margin-bottom:12px">Client</div>
                        <div class="detail-row"><span class="detail-key">Name</span><span class="detail-val">${o.buyer?.profile?.first_name} ${o.buyer?.profile?.last_name}</span></div>
                        <div class="detail-row"><span class="detail-key">Email</span><span class="detail-val">${o.buyer?.email}</span></div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div style="background:var(--clr-surface-2);border-radius:10px;padding:20px">
                        <div style="font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:var(--clr-muted);margin-bottom:12px">Expert</div>
                        <div class="detail-row"><span class="detail-key">Name</span><span class="detail-val">${o.seller?.profile?.first_name} ${o.seller?.profile?.last_name}</span></div>
                        <div class="detail-row"><span class="detail-key">Email</span><span class="detail-val">${o.seller?.email}</span></div>
                    </div>
                </div>

                ${actionsHtml ? `
                <div class="col-12">
                    <div style="background:var(--clr-surface-2);border-radius:10px;padding:20px">
                        <div style="font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:var(--clr-muted);margin-bottom:14px">⚡ Admin Actions</div>
                        <div>${actionsHtml}</div>
                    </div>
                </div>` : ''}
            </div>
        `);
    }).fail(function () {
        $('#orderDetailContent').html('<div class="pcc-alert error">Failed to load order details.</div>');
    });
}

// ─── Release Escrow Modal ────────────────────────────────────────────
function openReleaseEscrow(orderId, orderNum, amount) {
    $('#orderDetailModal').modal('hide');
    setTimeout(() => {
        $('#releaseOrderSummary').html(`
            <div style="background:var(--clr-surface-2);border-radius:8px;padding:14px;display:flex;justify-content:space-between;align-items:center">
                <div>
                    <div style="font-size:12px;color:var(--clr-muted)">Order</div>
                    <div style="font-weight:700;color:var(--clr-text)">#${orderNum}</div>
                </div>
                <div>
                    <div style="font-size:12px;color:var(--clr-muted)">Amount</div>
                    <div style="font-size:20px;font-weight:700;color:var(--clr-accent);font-family:monospace">$${parseFloat(amount).toFixed(2)}</div>
                </div>
            </div>
        `);
        $('#releaseEscrowForm').attr('action', `{{ url('admin/payments/orders') }}/${orderId}/release-escrow`);
        $('#releaseEscrowModal').modal('show');
    }, 300);
}

// ─── Cancel Order Modal ──────────────────────────────────────────────
function openCancelOrder(orderId, orderNum, price) {
    $('#orderDetailModal').modal('hide');
    setTimeout(() => {
        $('#cancelOrderSummary').html(`
            <div style="background:var(--clr-surface-2);border-radius:8px;padding:14px;display:flex;justify-content:space-between;align-items:center">
                <div>
                    <div style="font-size:12px;color:var(--clr-muted)">Order</div>
                    <div style="font-weight:700;color:var(--clr-text)">#${orderNum}</div>
                </div>
                <div>
                    <div style="font-size:12px;color:var(--clr-muted)">Total Amount</div>
                    <div style="font-size:20px;font-weight:700;color:var(--clr-danger);font-family:monospace">$${parseFloat(price).toFixed(2)}</div>
                </div>
            </div>
        `);
        $('#cancelOrderForm').attr('action', `{{ url('admin/payments/orders') }}/${orderId}/cancel`);
        $('#cancelOrderModal').modal('show');
    }, 300);
}

// ─── Force Transfer Modal ────────────────────────────────────────────
function openForceTransfer(orderId, orderNum, amount, stripeAccount) {
    $('#orderDetailModal').modal('hide');
    setTimeout(() => {
        $('#forceTransferSummary').html(`
            <div style="background:var(--clr-surface-2);border-radius:8px;padding:14px">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
                    <div>
                        <div style="font-size:12px;color:var(--clr-muted)">Order</div>
                        <div style="font-weight:700;color:var(--clr-text)">#${orderNum}</div>
                    </div>
                    <div>
                        <div style="font-size:12px;color:var(--clr-muted)">Transfer Amount</div>
                        <div style="font-size:20px;font-weight:700;color:var(--clr-blue);font-family:monospace">$${parseFloat(amount).toFixed(2)}</div>
                    </div>
                </div>
                <div style="font-size:11px;color:var(--clr-muted)">Destination: <code style="color:var(--clr-blue)">${stripeAccount}</code></div>
            </div>
        `);
        $('#forceTransferForm').attr('action', `{{ url('admin/payments/orders') }}/${orderId}/force-transfer`);
        $('#forceTransferModal').modal('show');
    }, 300);
}
</script>
@endpush
