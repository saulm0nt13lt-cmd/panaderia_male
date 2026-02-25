@if ($paginator->hasPages())
<nav class="pagination-wrap">
  <ul class="pagination">

    {{-- ◀ PREVIOUS --}}
    @if ($paginator->onFirstPage())
      <li class="page-item disabled">
        <span class="page-link">
          <i class="fi fi-rr-angle-left"></i>
        </span>
      </li>
    @else
      <li class="page-item">
        <a class="page-link" href="{{ $paginator->previousPageUrl() }}">
          <i class="fi fi-rr-angle-left"></i>
        </a>
      </li>
    @endif

    {{-- ▶ NEXT --}}
    @if ($paginator->hasMorePages())
      <li class="page-item">
        <a class="page-link" href="{{ $paginator->nextPageUrl() }}">
          <i class="fi fi-rr-angle-right"></i>
        </a>
      </li>
    @else
      <li class="page-item disabled">
        <span class="page-link">
          <i class="fi fi-rr-angle-right"></i>
        </span>
      </li>
    @endif

  </ul>
</nav>
@endif