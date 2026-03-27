@if ($paginator->hasPages())
    <div class="d-flex justify-content-between align-items-center">
        <div class="text-muted small">
            Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results
        </div>
        <nav aria-label="Page navigation">
            {{ $paginator->links() }}
        </nav>
    </div>
@endif
