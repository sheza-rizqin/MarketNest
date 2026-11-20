@props(['paginator'])

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination navigation" class="mt-8">
        {{ $paginator->onEachSide(1)->links() }}
    </nav>
@endif
