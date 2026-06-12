@props(['paginator'])

@if ($paginator->hasPages())
    <div class="table-pagination">
        <p class="pagination-info">
            Showing <strong>{{ $paginator->firstItem() ?? 0 }}</strong> to
            <strong>{{ $paginator->lastItem() ?? 0 }}</strong> of
            <strong>{{ $paginator->total() }}</strong> results
        </p>
        <div class="pagination-controls">
            @if ($paginator->onFirstPage())
                <button type="button" class="btn-pagination" disabled aria-label="Previous">
                    <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-chevron-left"></use></svg>
                </button>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="btn-pagination" aria-label="Previous">
                    <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-chevron-left"></use></svg>
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="btn-pagination" aria-label="Next">
                    <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-chevron-right"></use></svg>
                </a>
            @else
                <button type="button" class="btn-pagination" disabled aria-label="Next">
                    <svg class="btn-icon-svg" aria-hidden="true"><use href="#icon-chevron-right"></use></svg>
                </button>
            @endif
        </div>
    </div>
@endif
