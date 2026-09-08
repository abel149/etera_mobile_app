@extends('layouts.insurance')
@section('content')
<div class="margin-top-15 margin-bottom-45n"></div>
@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@elseif ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<style type="text/css">
    .table td:last-child { white-space: nowrap; width: 1%; }
    .pagination-wrap { display:flex; justify-content:center; align-items:center; gap:6px; flex-wrap:wrap; margin-top:1.25rem; }
    .pagination-wrap .page-btn {
        display:inline-flex; align-items:center; justify-content:center;
        min-width:36px; height:36px; padding:0 10px;
        border-radius:50px; border:1px solid #dee2e6;
        background:#fff; color:#495057; font-size:0.85rem;
        text-decoration:none; transition:all .2s;
    }
    .pagination-wrap .page-btn:hover { background:#f0fdf4; border-color:#28a745; color:#28a745; }
    .pagination-wrap .page-btn.active { background:#28a745; border-color:#28a745; color:#fff; font-weight:700; }
    .pagination-wrap .page-btn.disabled { opacity:.45; pointer-events:none; }
    .pagination-wrap .page-ellipsis { padding:0 4px; color:#adb5bd; font-size:0.85rem; }
    #searchSpinner { display:none; }
</style>

<h3 class="">Received Proforma</h3>

<div class="row row-cols-12 row-cols-lg-12 row-cols-xl-12">
    <div class="col mx-auto">
    <div class="my-5 my-lg-0 shadow-none">
        <div class="card radius-10">
            <div class="card-body">

                {{-- Search Form --}}
                <div class="row align-items-center mb-3">
                    <div class="col-lg-9 col-xl-8">
                        <form id="searchForm" method="GET" action="/insurance/received-proformas">
                            <div class="row row-cols-lg-2 row-cols-xl-auto g-2 align-items-center">
                                <div class="col">
                                    <div class="position-relative">
                                        <input id="searchInput" name="search" type="text"
                                            class="form-control ps-5 radius-30"
                                            placeholder="Search File #, Name, License Plate or Phone..."
                                            value="{{ request('search') }}"
                                            autocomplete="off">
                                        <span class="position-absolute top-50 product-show translate-middle-y">
                                            <i class="bx bx-search"></i>
                                        </span>
                                        <span id="searchSpinner" class="position-absolute top-50 translate-middle-y" style="right:14px;">
                                            <span class="spinner-border spinner-border-sm text-secondary" role="status"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-lg-3 col-xl-4 text-end">
                        <span class="text-muted small">
                            {{ $proformas->total() }} proforma{{ $proformas->total() !== 1 ? 's' : '' }} found
                        </span>
                    </div>
                </div>

                {{-- Searchable Table + Pagination --}}
                <div id="searchableTable">
                    <div class="table-responsive lead-table">
                        <table class="table mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Claim #</th>
                                    <th>Customer Name</th>
                                    <th>Car Brand</th>
                                    <th>Model</th>
                                    <th>Year</th>
                                    <th>License Plate</th>
                                    <th>Phone #</th>
                                    <th>Parts Progress</th>
                                    <th>Show</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($proformas as $proforma)
                                <tr>
                                    <td>#{{ $proforma->file_number }}</td>
                                    <td>{{ $proforma->customer_name }}</td>
                                    <td>{{ $proforma->brand->name ?? 'N/A' }}</td>
                                    <td>{{ $proforma->model }}</td>
                                    <td>{{ $proforma->year }}</td>
                                    <td>{{ $proforma->license_plate_number }}</td>
                                    <td>{{ $proforma->customer_phone_number }}</td>
                                    <td>
                                        @php $progress = $proforma->partsPricingProgress(); @endphp
                                        @if($progress['total'] > 0)
                                            <span class="badge {{ $progress['filled'] >= $progress['total'] ? 'bg-success' : 'bg-warning' }}">
                                                {{ $progress['filled'] }}/{{ $progress['total'] }}
                                            </span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a class="btn" href="/insurance/proforma-details?proforma_id={{ $proforma->id }}">
                                            <i class="bx bx-show me-0"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        <i class="bx bx-search-alt fs-4 d-block mb-1"></i>
                                        No proformas found{{ request('search') ? ' for "' . request('search') . '"' : '' }}.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination UI --}}
                    @if($proformas->lastPage() > 1)
                    <div class="pagination-wrap">
                        {{-- Previous --}}
                        <a href="{{ $proformas->previousPageUrl() ?? '#' }}"
                           class="page-btn {{ $proformas->onFirstPage() ? 'disabled' : '' }}"
                           data-page="{{ $proformas->currentPage() - 1 }}">
                            <i class="bx bx-chevron-left"></i>
                        </a>

                        @php
                            $current = $proformas->currentPage();
                            $last    = $proformas->lastPage();
                            $pages   = [];
                            if ($last <= 7) {
                                $pages = range(1, $last);
                            } else {
                                $pages[] = 1;
                                if ($current > 3) $pages[] = '...';
                                for ($i = max(2, $current - 1); $i <= min($last - 1, $current + 1); $i++) $pages[] = $i;
                                if ($current < $last - 2) $pages[] = '...';
                                $pages[] = $last;
                            }
                        @endphp

                        @foreach($pages as $page)
                            @if($page === '...')
                                <span class="page-ellipsis">…</span>
                            @else
                                <a href="{{ $proformas->url($page) }}"
                                   class="page-btn {{ $page == $current ? 'active' : '' }}"
                                   data-page="{{ $page }}">{{ $page }}</a>
                            @endif
                        @endforeach

                        {{-- Next --}}
                        <a href="{{ $proformas->nextPageUrl() ?? '#' }}"
                           class="page-btn {{ !$proformas->hasMorePages() ? 'disabled' : '' }}"
                           data-page="{{ $proformas->currentPage() + 1 }}">
                            <i class="bx bx-chevron-right"></i>
                        </a>
                    </div>
                    @endif

                </div>{{-- #searchableTable --}}

            </div>
        </div>
    </div>
    </div>
</div>

<script>
(function () {
    const form        = document.getElementById('searchForm');
    const input       = document.getElementById('searchInput');
    const wrapper     = document.getElementById('searchableTable');
    const spinner     = document.getElementById('searchSpinner');
    const baseUrl     = '/insurance/received-proformas';
    let debounceTimer = null;

    function fetchPage(url) {
        spinner.style.display = 'inline-block';

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (res) { return res.text(); })
            .then(function (html) {
                const parser  = new DOMParser();
                const doc     = parser.parseFromString(html, 'text/html');
                const fresh   = doc.getElementById('searchableTable');
                if (fresh) {
                    wrapper.innerHTML = fresh.innerHTML;
                    attachPaginationClicks();
                }
                history.pushState(null, '', url);
                spinner.style.display = 'none';
            })
            .catch(function () { spinner.style.display = 'none'; });
    }

    function buildUrl(page) {
        const params = new URLSearchParams();
        const q = input.value.trim();
        if (q) params.set('search', q);
        if (page && page > 1) params.set('page', page);
        return baseUrl + (params.toString() ? '?' + params.toString() : '');
    }

    function attachPaginationClicks() {
        wrapper.querySelectorAll('.page-btn:not(.disabled):not(.active)').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                fetchPage(this.getAttribute('href'));
            });
        });
    }

    input.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () {
            fetchPage(buildUrl(1));
        }, 350);
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        fetchPage(buildUrl(1));
    });

    attachPaginationClicks();

    window.addEventListener('popstate', function () {
        fetchPage(location.href);
    });
})();
</script>

@endsection
