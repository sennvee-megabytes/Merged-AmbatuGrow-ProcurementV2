@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex flex-col sm:flex-row items-center justify-between gap-4 w-full">
        {{-- Showing Results Info --}}
        <div class="text-xs font-medium text-slate-500">
            Showing
            @if ($paginator->firstItem())
                <span class="font-bold text-slate-800">{{ $paginator->firstItem() }}</span>
                to
                <span class="font-bold text-slate-800">{{ $paginator->lastItem() }}</span>
            @else
                {{ $paginator->count() }}
            @endif
            of
            <span class="font-bold text-slate-800">{{ $paginator->total() }}</span>
            results
        </div>

        {{-- Dark Pagination Bar Container --}}
        <div class="inline-flex items-center rounded-lg border border-slate-800 bg-slate-900 shadow-sm overflow-hidden divide-x divide-slate-800">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}" class="w-9 h-9 min-w-[36px] flex items-center justify-center text-xs font-semibold text-slate-500 bg-slate-900 opacity-40 cursor-not-allowed select-none">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="w-9 h-9 min-w-[36px] flex items-center justify-center text-xs font-semibold text-slate-300 bg-slate-900 hover:bg-slate-800 hover:text-white transition-colors" aria-label="{{ __('pagination.previous') }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span aria-disabled="true" class="w-9 h-9 min-w-[36px] flex items-center justify-center text-xs font-semibold text-slate-500 bg-slate-900 select-none">
                        {{ $element }}
                    </span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="w-9 h-9 min-w-[36px] flex items-center justify-center text-xs font-bold text-white bg-emerald-600 shadow-inner select-none">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" class="w-9 h-9 min-w-[36px] flex items-center justify-center text-xs font-semibold text-slate-300 bg-slate-900 hover:bg-slate-800 hover:text-white transition-colors" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="w-9 h-9 min-w-[36px] flex items-center justify-center text-xs font-semibold text-slate-300 bg-slate-900 hover:bg-slate-800 hover:text-white transition-colors" aria-label="{{ __('pagination.next') }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            @else
                <span aria-disabled="true" aria-label="{{ __('pagination.next') }}" class="w-9 h-9 min-w-[36px] flex items-center justify-center text-xs font-semibold text-slate-500 bg-slate-900 opacity-40 cursor-not-allowed select-none">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </span>
            @endif
        </div>
    </nav>
@endif
