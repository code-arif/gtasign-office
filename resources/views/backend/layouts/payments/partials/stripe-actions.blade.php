<div style="display:flex;gap:6px;flex-wrap:wrap">
    <a href="{{ route('admin.payments.stripe.dashboard', $u->id) }}"
       class="pcc-btn pcc-btn-blue" target="_blank" title="Open Stripe Dashboard">
        <i class="fe fe-external-link"></i> Stripe Dashboard
    </a>
    <button class="pcc-btn pcc-btn-ghost"
            onclick="loadExpertWallet({{ $u->id }}, '{{ $u->profile?->first_name }} {{ $u->profile?->last_name }}')"
            title="View Wallet">
        <i class="fe fe-dollar-sign"></i> Wallet
    </button>
</div>
