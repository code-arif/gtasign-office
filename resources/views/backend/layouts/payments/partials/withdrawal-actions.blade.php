<div style="display:flex;gap:6px">
    @if($w->stripe_transfer_id)
        <span style="font-size:11px;color:var(--clr-muted)">Auto-processed</span>
    @else
        <span style="font-size:11px;color:var(--clr-muted)">—</span>
    @endif
</div>
