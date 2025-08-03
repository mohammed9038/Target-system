@props([
    'title' => '',
    'icon' => 'bi-table',
    'searchPlaceholder' => 'Search...',
    'searchValue' => '',
    'searchRoute' => '',
    'totalRecords' => 0,
    'columns' => [],
    'rows' => [],
    'actions' => null
])

<x-card :title="$title" :icon="$icon" class="mb-4">
    <x-slot name="actions">
        <div class="d-flex align-items-center gap-3">
            <small class="text-muted">
                {{ $totalRecords }} records
            </small>
            
            @if($searchRoute)
                <form method="GET" action="{{ $searchRoute }}" class="d-flex gap-2">
                    <div class="input-group" style="width: 250px;">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" 
                               name="search" 
                               class="form-control border-start-0" 
                               placeholder="{{ $searchPlaceholder }}" 
                               value="{{ $searchValue }}"
                               id="searchInput">
                    </div>
                    @if($searchValue)
                        <a href="{{ $searchRoute }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x"></i>
                        </a>
                    @endif
                </form>
            @else
                <div class="input-group" style="width: 250px;">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" 
                           class="form-control border-start-0" 
                           placeholder="{{ $searchPlaceholder }}"
                           id="searchInput">
                </div>
            @endif
        </div>
    </x-slot>
    
    <div class="table-responsive">
        <table class="table table-hover mb-0" id="dataTable">
            <thead class="table-light">
                <tr>
                    @foreach($columns as $column)
                        <th class="border-0 {{ $column['class'] ?? '' }}">
                            <div class="d-flex align-items-center {{ $column['align'] ?? '' }}">
                                @if(isset($column['icon']))
                                    <i class="{{ $column['icon'] }} me-2 text-muted"></i>
                                @endif
                                {{ $column['label'] }}
                                @if(isset($column['sortable']) && $column['sortable'])
                                    <i class="bi bi-chevron-expand ms-1 text-muted small"></i>
                                @endif
                            </div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                {{ $slot }}
            </tbody>
        </table>
    </div>
    
    @if($actions)
        <div class="card-footer bg-light">
            {{ $actions }}
        </div>
    @endif
</x-card>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const table = document.getElementById('dataTable');
    
    if (searchInput && table) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});
</script>
@endpush
