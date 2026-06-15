<x-filament-widgets::widget>
    <div class="content-card">
        <div class="content-card-header">
            <h2 class="content-card-title">Welcome, {{ auth()->user()->name }}</h2>
            <span class="badge badge-gray">Admin Panel</span>
        </div>
        <div class="content-card-body">
            <p style="margin: 0 0 1.5rem; font-size: 0.875rem; color: #6b7280; line-height: 1.6;">
                Manage your B2B plywood marketplace — distributors, customers, products, and orders.
            </p>
            <div class="info-grid">
                <div class="info-tile">
                    <p class="info-tile-label">Workflow</p>
                    <p class="info-tile-value">Customer cart → Order → Fulfillment</p>
                </div>
                <div class="info-tile">
                    <p class="info-tile-label">Pricing</p>
                    <p class="info-tile-value">Distributor-specific pricing on checkout</p>
                </div>
                <div class="info-tile">
                    <p class="info-tile-label">Database</p>
                    <p class="info-tile-value">MySQL · plywood</p>
                </div>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
