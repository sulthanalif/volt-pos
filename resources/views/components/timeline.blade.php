<div>
    <x-timeline-item title="Order placed" first icon="o-map-pin" />
    
    <x-timeline-item title="Shipped" @if ($status != 'shipped') pending @endif icon="o-paper-airplane" />
    
    <x-timeline-item title="Delivered" @if ($status != 'delivered') pending @endif last icon="o-gift" />
</div>