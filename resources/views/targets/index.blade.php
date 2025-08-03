@extends('layouts.app')

@section('title', __('Sales Targets'))

@section('content')
<div id="alert-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;"></div>
<!-- Compact Page Header -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1 fw-bold d-flex align-items-center">
            <div class="p-1 rounded-circle bg-primary bg-opacity-10 me-2">
                <i class="bi bi-bullseye text-primary small"></i>
            </div>
            {{ __('Sales Targets') }}
        </h1>
        <p class="text-muted mb-0 ms-4 small">{{ __('Set and manage sales targets for your team members') }}</p>
    </div>
    <div class="d-flex gap-1 flex-wrap">
        <button type="button" class="btn btn-success btn-sm" onclick="saveAllTargets()" id="saveAllBtn">
            <i class="bi bi-check-circle me-1"></i>{{ __('Save All') }}
        </button>
        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-primary" onclick="exportTargets()">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i>{{ __('Export') }}
            </button>
            <button type="button" class="btn btn-outline-primary" onclick="showUploadModal()">
                <i class="bi bi-upload me-1"></i>{{ __('Upload') }}
            </button>
            <button type="button" class="btn btn-outline-primary" onclick="downloadTemplate()">
                <i class="bi bi-download me-1"></i>{{ __('Template') }}
            </button>
        </div>
    </div>
</div>

<!-- Compact Filters Panel -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body p-3">
        <!-- Period and Filters in One Row -->
        <div class="row g-2 align-items-end">
            <div class="col-lg-1 col-md-2">
                <label for="target_year" class="form-label small text-muted mb-1">{{ __('Year') }}</label>
                <select class="form-select form-select-sm" id="target_year" name="year">
                    <option value="">{{ __('Year') }}</option>
                    @for($y = date('Y'); $y <= date('Y') + 2; $y++)
                        <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-lg-1 col-md-2">
                <label for="target_month" class="form-label small text-muted mb-1">{{ __('Month') }}</label>
                <select class="form-select form-select-sm" id="target_month" name="month">
                    <option value="">{{ __('Month') }}</option>
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>
                            {{ date('M', mktime(0, 0, 0, $m, 1)) }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="col-lg-1 col-md-2">
                <button class="btn btn-primary btn-sm w-100" id="loadMatrixBtn" onclick="loadTargetMatrix()">
                    <i class="bi bi-table me-1"></i>{{ __('Load') }}
                </button>
            </div>
            <div class="col-lg-1 col-md-2">
                <label for="filter_classification" class="form-label small text-muted mb-1">{{ __('Type') }}</label>
                <select class="form-select form-select-sm" id="filter_classification">
                    <option value="">{{ __('All') }}</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-2">
                <label for="filter_region" class="form-label small text-muted mb-1">{{ __('Region') }}</label>
                <select class="form-select form-select-sm" id="filter_region">
                    <option value="">{{ __('All Regions') }}</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-2">
                <label for="filter_channel" class="form-label small text-muted mb-1">{{ __('Channel') }}</label>
                <select class="form-select form-select-sm" id="filter_channel">
                    <option value="">{{ __('All Channels') }}</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-2">
                <label for="filter_supplier" class="form-label small text-muted mb-1">{{ __('Supplier') }}</label>
                <select class="form-select form-select-sm" id="filter_supplier">
                    <option value="">{{ __('All Suppliers') }}</option>
                </select>
            </div>
            <div class="col-lg-1 col-md-2">
                <label for="filter_category" class="form-label small text-muted mb-1">{{ __('Category') }}</label>
                <select class="form-select form-select-sm" id="filter_category">
                    <option value="">{{ __('All') }}</option>
                </select>
            </div>
            <div class="col-lg-1 col-md-2">
                <label for="filter_salesman" class="form-label small text-muted mb-1">{{ __('Salesman') }}</label>
                <select class="form-select form-select-sm" id="filter_salesman">
                    <option value="">{{ __('All') }}</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Compact Target Matrix -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
        <h6 class="card-title mb-0 fw-semibold d-flex align-items-center">
            <i class="bi bi-grid-3x3-gap text-success me-2"></i>
            {{ __('Target Matrix') }}
        </h6>
        <small class="text-muted">
            <i class="bi bi-info-circle me-1"></i>{{ __('Enter target amounts') }}
        </small>
    </div>
    <div class="card-body p-0">
        <!-- Loading State -->
        <div id="matrix-loading" class="text-center py-3" style="display: none;">
            <div class="spinner-border text-primary mb-2" role="status">
                <span class="visually-hidden">{{ __('Loading...') }}</span>
            </div>
            <small class="text-muted">{{ __('Loading target matrix...') }}</small>
        </div>
        
        <!-- Matrix Container -->
        <div id="matrix-container" style="display: none;">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0" id="target-matrix" style="font-size: 0.90rem;">
                    <thead class="table-dark">
                        <tr>
                            <th class="py-2 px-3 small">
                                <i class="bi bi-person-badge me-1"></i>{{ __('Salesman') }}
                            </th>
                            <th class="py-2 px-3 small">
                                <i class="bi bi-geo-alt me-1"></i>{{ __('Region') }}
                            </th>
                            <th class="py-2 px-3 small">
                                <i class="bi bi-diagram-3 me-1"></i>{{ __('Channel') }}
                            </th>
                            <th class="py-2 px-3 small">
                                <i class="bi bi-building me-1"></i>{{ __('Supplier') }}
                            </th>
                            <th class="py-2 px-3 small">
                                <i class="bi bi-tags me-1"></i>{{ __('Category') }}
                            </th>
                            <th class="py-2 px-3 small">
                                <i class="bi bi-diagram-2 me-1"></i>{{ __('Type') }}
                            </th>
                            <th class="py-2 px-3 small text-center">
                                <i class="bi bi-currency-dollar me-1"></i>{{ __('Amount') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot class="table-light border-top">
                        <tr class="fw-bold">
                            <td colspan="6" class="py-3 px-4 text-end">
                                <i class="bi bi-calculator me-2 text-primary"></i>{{ __('Total Target Amount:') }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span id="matrix-total" class="fw-bold text-success fs-6">$0.00</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        
        <!-- Empty State -->
        <div id="matrix-empty" class="text-center py-4">
            <div class="mb-3">
                <i class="bi bi-table text-muted" style="font-size: 2rem;"></i>
            </div>
            <h6 class="text-dark mb-2">{{ __('No Data Available') }}</h6>
            <p class="text-muted small mb-3">{{ __('Please select year, month and click "Load Matrix" to view targets.') }}</p>
            <div class="d-flex justify-content-center gap-1 flex-wrap">
                <small class="badge bg-primary bg-opacity-10 text-primary px-2 py-1">
                    <i class="bi bi-1-circle me-1"></i>{{ __('Select Period') }}
                </small>
                <small class="badge bg-success bg-opacity-10 text-success px-2 py-1">
                    <i class="bi bi-2-circle me-1"></i>{{ __('Apply Filters') }}
                </small>
                <small class="badge bg-info bg-opacity-10 text-info px-2 py-1">
                    <i class="bi bi-3-circle me-1"></i>{{ __('Load Matrix') }}
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Upload Targets') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="uploadForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="upload_file" class="form-label">{{ __('Select CSV File') }}</label>
                        <input type="file" class="form-control" id="upload_file" name="csv_file" accept=".csv" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary" onclick="uploadTargets()">{{ __('Upload') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    console.log("🎯 TARGET PAGE SCRIPT LOADED - V3.1 FINAL");

    let isPeriodOpen = false;
    let isMatrixLoaded = false;

    // Helper functions for classification display
    function getClassificationLabel(classification) {
        switch(classification) {
            case 'food': return 'Food';
            case 'non_food': return 'Non-Food';
            case 'both': return 'Both';
            default: return classification || 'N/A';
        }
    }

    function getClassificationBadgeClass(classification) {
        switch(classification) {
            case 'food': return 'bg-success bg-opacity-10 text-success';
            case 'non_food': return 'bg-info bg-opacity-10 text-info';
            case 'both': return 'bg-secondary bg-opacity-10 text-secondary';
            default: return 'bg-light text-muted';
        }
    }

    const apiOptions = {
        headers: {
            "Accept": "application/json",
            "Content-Type": "application/json",
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    };

    function showAlert(message, type = "info") {
        const alertContainer = document.getElementById('alert-container');
        const alertDiv = document.createElement("div");
        alertDiv.className = `alert alert-${type === "error" ? "danger" : type} alert-dismissible fade show`;
        alertDiv.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        alertContainer.appendChild(alertDiv);
        setTimeout(() => alertDiv.remove(), 5000);
    }

    function populateSelect(elementId, data, valueField, textField) {
        const select = document.getElementById(elementId);
        if (select) {
            const firstOption = select.options[0];
            select.innerHTML = "";
            select.appendChild(firstOption);
            if (data) data.forEach(item => {
                const option = document.createElement("option");
                option.value = item[valueField];
                option.textContent = item[textField];
                select.appendChild(option);
            });
        }
    }

    async function loadMasterData() {
        try {
            const [regions, channels, suppliers, categories, salesmen] = await Promise.all([
                fetch(`/api/v1/deps/regions`).then(res => res.json()),
                fetch(`/api/v1/deps/channels`).then(res => res.json()),
                fetch(`/api/v1/deps/suppliers`).then(res => res.json()),
                fetch(`/api/v1/deps/categories`).then(res => res.json()),
                fetch(`/api/v1/deps/salesmen`).then(res => res.json())
            ]);
            
            populateSelect("filter_region", regions.data, "id", "name");
            populateSelect("filter_channel", channels.data, "id", "name");
            populateSelect("filter_supplier", suppliers.data, "id", "name");
            populateSelect("filter_category", categories.data, "id", "name");
            populateSelect("filter_salesman", salesmen.data, "id", "name");
            
            // Auto-set user's classification filter based on their permissions
            await setUserClassificationFilter(regions.data, channels.data, suppliers.data);
            
            // Set up cascading filter event listeners
            setupCascadingFilters();
            
        } catch (error) {
            showAlert("Failed to load filter data.", "error");
        }
    }

    /**
     * Set up cascading filter event listeners
     */
    function setupCascadingFilters() {
        // Classification change affects suppliers and salesmen
        document.getElementById('filter_classification').addEventListener('change', function() {
            updateSuppliersFilter();
            updateSalesmenFilter();
        });

        // Supplier change affects categories
        document.getElementById('filter_supplier').addEventListener('change', function() {
            updateCategoriesFilter();
        });

        // Region change affects salesmen
        document.getElementById('filter_region').addEventListener('change', function() {
            updateSalesmenFilter();
        });

        // Channel change affects salesmen
        document.getElementById('filter_channel').addEventListener('change', function() {
            updateSalesmenFilter();
        });
    }

    /**
     * Update suppliers dropdown based on classification filter
     */
    async function updateSuppliersFilter() {
        try {
            const classification = document.getElementById('filter_classification').value;
            const url = `/api/v1/deps/filtered/suppliers${classification ? `?classification=${classification}` : ''}`;
            
            const response = await fetch(url);
            const data = await response.json();
            
            populateSelect("filter_supplier", data.data, "id", "name");
            
            // Reset dependent filters
            updateCategoriesFilter();
        } catch (error) {
            console.error('Failed to update suppliers filter:', error);
        }
    }

    /**
     * Update categories dropdown based on supplier and classification filters
     */
    async function updateCategoriesFilter() {
        try {
            const classification = document.getElementById('filter_classification').value;
            const supplierId = document.getElementById('filter_supplier').value;
            
            const params = new URLSearchParams();
            if (classification) params.append('classification', classification);
            if (supplierId) params.append('supplier_id', supplierId);
            
            const url = `/api/v1/deps/filtered/categories${params.toString() ? `?${params.toString()}` : ''}`;
            
            const response = await fetch(url);
            const data = await response.json();
            
            populateSelect("filter_category", data.data, "id", "name");
        } catch (error) {
            console.error('Failed to update categories filter:', error);
        }
    }

    /**
     * Update salesmen dropdown based on region, channel, and classification filters
     */
    async function updateSalesmenFilter() {
        try {
            const classification = document.getElementById('filter_classification').value;
            const regionId = document.getElementById('filter_region').value;
            const channelId = document.getElementById('filter_channel').value;
            
            const params = new URLSearchParams();
            if (classification) params.append('classification', classification);
            if (regionId) params.append('region_id', regionId);
            if (channelId) params.append('channel_id', channelId);
            
            const url = `/api/v1/deps/filtered/salesmen${params.toString() ? `?${params.toString()}` : ''}`;
            
            const response = await fetch(url);
            const data = await response.json();
            
            populateSelect("filter_salesman", data.data, "id", "name");
        } catch (error) {
            console.error('Failed to update salesmen filter:', error);
        }
    }

    async function setUserClassificationFilter(regions, channels, suppliers) {
        try {
            // Get user info from API
            const userResponse = await fetch('/api/v1/user/info');
            if (!userResponse.ok) return;
            
            const userData = await userResponse.json();
            const user = userData.data;
            
            // Populate classification dropdown based on user permissions
            const classificationSelect = document.getElementById('filter_classification');
            
            if (user.is_admin) {
                // Admin can see all classifications
                classificationSelect.innerHTML = `
                    <option value="">All Types</option>
                    <option value="food">Food</option>
                    <option value="non_food">Non-Food</option>
                `;
            } else if (user.classifications && user.classifications.length > 0) {
                // Manager can only see their assigned classifications
                let optionsHtml = '<option value="">All Types</option>';
                
                user.classifications.forEach(classification => {
                    const label = classification === 'food' ? 'Food' : 'Non-Food';
                    optionsHtml += `<option value="${classification}">${label}</option>`;
                });
                
                classificationSelect.innerHTML = optionsHtml;
                
                // If user has only one classification, auto-select it
                if (user.classifications.length === 1) {
                    classificationSelect.value = user.classifications[0];
                }
            }
            
            // Auto-select region and channel if user has only one option
            if (!user.is_admin) {
                if (regions.length === 1) {
                    document.getElementById('filter_region').value = regions[0].id;
                }
                if (channels.length === 1) {
                    document.getElementById('filter_channel').value = channels[0].id;
                }
                
                console.log(`Auto-applied user filters - Classifications: ${user.classifications?.join(', ')}, Regions: ${regions.length}, Channels: ${channels.length}`);
            }
        } catch (error) {
            console.log('Could not auto-set user filters:', error);
        }
    }

    function getCurrentFilters() {
        const filters = {
            year: document.getElementById('target_year').value,
            month: document.getElementById('target_month').value,
            region_id: document.getElementById('filter_region').value,
            channel_id: document.getElementById('filter_channel').value,
            supplier_id: document.getElementById('filter_supplier').value,
            category_id: document.getElementById('filter_category').value,
            salesman_id: document.getElementById('filter_salesman').value,
            classification: document.getElementById('filter_classification').value
        };
        // Remove empty filters
        Object.keys(filters).forEach(key => (filters[key] === '') && delete filters[key]);
        return filters;
    }

    async function loadTargetMatrix() {
        const year = document.getElementById("target_year").value;
        const month = document.getElementById("target_month").value;
        if (!year || !month) {
            showAlert("Please select both Year and Month.", "warning");
            return;
        }

        // Reset matrix loaded state
        isMatrixLoaded = false;

        const loadBtn = document.getElementById("loadMatrixBtn");
        loadBtn.disabled = true;
        loadBtn.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Loading...`;
        
        document.getElementById("matrix-loading").style.display = "block";
        document.getElementById("matrix-container").style.display = "none";
        document.getElementById("matrix-empty").style.display = "none";

        const params = new URLSearchParams(getCurrentFilters());
        try {
            console.log('Loading matrix with params:', params.toString());
            const response = await fetch(`/api/v1/targets/matrix?${params}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('Error response:', errorText);
                throw new Error('Failed to load data: ' + response.status);
            }
            
            const result = await response.json();
            console.log('Matrix data received:', result);
            
            if (!result.data) {
                throw new Error('Invalid response format - missing data field');
            }
            
            isPeriodOpen = result.data.is_period_open;
            document.getElementById("saveAllBtn").style.display = isPeriodOpen ? 'flex' : 'none';
            renderMatrix(result.data);

        } catch (error) {
            showAlert(error.message, "error");
            document.getElementById("matrix-empty").style.display = "block";
        } finally {
            loadBtn.disabled = false;
            loadBtn.innerHTML = `<i class="bi bi-table me-2"></i>Load Matrix`;
            document.getElementById("matrix-loading").style.display = "none";
        }
    }

    function renderMatrix({ salesmen, suppliers, targets }) {
        console.log('Rendering matrix with:', { 
            salesmenCount: salesmen?.length || 0, 
            suppliersCount: suppliers?.length || 0, 
            targetsCount: targets?.length || 0 
        });
        

        
        const tbody = document.querySelector("#target-matrix tbody");
        tbody.innerHTML = "";
        
        if (!salesmen || !suppliers || salesmen.length === 0 || suppliers.length === 0) {
            console.log('No data to display - showing empty state');
            document.getElementById("matrix-empty").style.display = "block";
            return;
        }



        const targetsMap = targets.reduce((map, t) => {
            map[`${t.salesman_id}-${t.supplier_id}-${t.category_id}`] = t.target_amount;
            return map;
        }, {});

        let rowCount = 0;
        salesmen.forEach(s => {
            suppliers.forEach(sup => {
                const compatible = isClassificationCompatible(s.salesman_classifications, sup.supplier_classification);
                if (compatible) {
                    rowCount++;
                    const key = `${s.salesman_id}-${sup.supplier_id}-${sup.category_id}`;
                    tbody.innerHTML += `
                        <tr class="border-0" style="border-bottom: 1px solid #e9ecef !important;">
                            <td class="py-3 px-4 border-0">
                                <div class="d-flex align-items-center">
                                    <div class="p-1 rounded-circle bg-primary bg-opacity-10 me-2">
                                        <i class="bi bi-person text-primary" style="font-size: 0.8rem;"></i>
                                    </div>
                                    <span class="fw-medium">${s.salesman_name}</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 border-0">
                                <span class="text-dark fw-medium">
                                    <i class="bi bi-geo-alt me-1"></i>${s.region_name || 'N/A'}
                                </span>
                            </td>
                            <td class="py-3 px-4 border-0">
                                <span class="text-dark fw-medium">
                                    <i class="bi bi-diagram-3 me-1"></i>${s.channel_name || 'N/A'}
                                </span>
                            </td>
                            <td class="py-3 px-4 border-0">
                                <span class="text-dark fw-medium">${sup.supplier_name}</span>
                            </td>
                            <td class="py-3 px-4 border-0">
                                <span class="text-dark fw-medium">
                                    ${sup.category_name}
                                </span>
                            </td>
                            <td class="py-3 px-4 border-0">
                                <span class="badge ${getClassificationBadgeClass(sup.supplier_classification)} px-2 py-1">
                                    <i class="bi bi-diagram-2 me-1"></i>${getClassificationLabel(sup.supplier_classification)}
                                </span>
                            </td>
                            <td class="py-3 px-4 border-0 text-center">
                                <input type="number" 
                                       class="form-control form-control-sm border shadow-sm text-center fw-bold" 
                                       style="border-radius: 8px; background-color: ${!isPeriodOpen ? '#f3f4f6' : '#ffffff'}; color: ${!isPeriodOpen ? '#6b7280' : '#111827'}; border-color: ${!isPeriodOpen ? '#d1d5db' : '#059669'};"
                                       value="${targetsMap[key] || ''}" 
                                       ${!isPeriodOpen ? 'disabled' : ''}
                                       placeholder="${!isPeriodOpen ? 'Closed' : 'Enter amount'}"
                                       data-salesman-id="${s.salesman_id}" 
                                       data-supplier-id="${sup.supplier_id}" 
                                       data-category-id="${sup.category_id}">
                            </td>
                        </tr>`;
                }
            });
        });
        

        
        // Hide empty state and show matrix
        document.getElementById("matrix-empty").style.display = "none";
        document.getElementById("matrix-container").style.display = "block";
        isMatrixLoaded = true;
        
        // Calculate and display initial total
        updateMatrixTotal();
        
        // Add event listeners to all input fields for real-time total calculation
        document.querySelectorAll("#target-matrix input[type='number']").forEach(input => {
            input.addEventListener('input', updateMatrixTotal);
        });
    }

    /**
     * Calculate and update the matrix total
     */
    function updateMatrixTotal() {
        let total = 0;
        document.querySelectorAll("#target-matrix input[type='number']").forEach(input => {
            const value = parseFloat(input.value) || 0;
            total += value;
        });
        
        const totalElement = document.getElementById('matrix-total');
        if (totalElement) {
            totalElement.textContent = `$${total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        }
    }

    function isClassificationCompatible(salesmanClassifications, supplierClass) {
        // salesmanClassifications is now an array, supplierClass is a single value
        if (!Array.isArray(salesmanClassifications)) {
            return false;
        }
        // Check if any of the salesman's classifications match the supplier's classification
        return salesmanClassifications.includes(supplierClass);
    }

    async function saveAllTargets() {
        if (!isPeriodOpen) return;
        const targetsToSave = [];
        document.querySelectorAll("#target-matrix input").forEach(input => {
            if (input.value.trim() !== '') {
                targetsToSave.push({
                    salesman_id: input.dataset.salesmanId,
                    supplier_id: input.dataset.supplierId,
                    category_id: input.dataset.categoryId,
                    target_amount: parseFloat(input.value)
                });
            }
        });

        if (targetsToSave.length === 0) {
            showAlert("No targets to save.", "info");
            return;
        }

        const saveBtn = document.getElementById("saveAllBtn");
        saveBtn.disabled = true;
        saveBtn.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Saving...`;

        try {
            const response = await fetch(`/api/v1/targets/bulk-save`, {
                method: 'POST',
                headers: apiOptions.headers,
                body: JSON.stringify({
                    year: document.getElementById("target_year").value,
                    month: document.getElementById("target_month").value,
                    targets: targetsToSave
                })
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message);
            showAlert(`${result.saved_count} targets saved.`, "success");
        } catch (error) {
            showAlert(error.message, "error");
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = `<i class="bi bi-check-circle me-2"></i>Save All Targets`;
        }
    }
    
    async function exportTargets() {
        const year = document.getElementById("target_year").value;
        const month = document.getElementById("target_month").value;
        
        if (!year || !month) {
            showAlert("Please select Year and Month before exporting.", "warning");
            return;
        }

        if (!isMatrixLoaded) {
            showAlert("Please load the matrix first before exporting.", "warning");
            return;
        }

        const params = new URLSearchParams(getCurrentFilters());
        const exportBtn = document.querySelector('button[onclick="exportTargets()"]');
        const originalText = exportBtn.innerHTML;
        
        try {
            exportBtn.disabled = true;
            exportBtn.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Exporting...`;
            
            const response = await fetch(`/api/v1/export/targets?${params}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const contentType = response.headers.get('content-type');
            
            if (!response.ok) {
                if (contentType && contentType.includes('application/json')) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Export failed');
                } else {
                    const textResponse = await response.text();
                    if (textResponse.includes('<')) {
                        throw new Error('Authentication required. Please refresh the page and try again.');
                    }
                    throw new Error('Export failed: ' + response.statusText);
                }
            }

            if (contentType && contentType.includes('text/html')) {
                throw new Error('Received HTML instead of CSV. Please try logging in again.');
            }

            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `targets_${year}_${month}.csv`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            
            showAlert("Export completed successfully", "success");
        } catch (error) {
            console.error('Export error:', error);
            showAlert("Failed to export targets: " + error.message, "error");
        } finally {
            exportBtn.disabled = false;
            exportBtn.innerHTML = originalText;
        }
    }

    function showUploadModal() {
        new bootstrap.Modal(document.getElementById('uploadModal')).show();
    }

    async function downloadTemplate() {
        const templateBtn = document.querySelector('button[onclick="downloadTemplate()"]');
        const originalText = templateBtn.innerHTML;
        
        try {
            templateBtn.disabled = true;
            templateBtn.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Downloading...`;
            
            const response = await fetch('/api/v1/export/template', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Template download failed');
            }

            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('text/html')) {
                throw new Error('Received HTML instead of CSV. Please try logging in again.');
            }

            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `targets_template.csv`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            
            showAlert("Template downloaded successfully", "success");
        } catch (error) {
            console.error('Template download error:', error);
            showAlert("Failed to download template: " + error.message, "error");
        } finally {
            templateBtn.disabled = false;
            templateBtn.innerHTML = originalText;
        }
    }

    async function uploadTargets() {
        const form = document.getElementById('uploadForm');
        const fileInput = document.getElementById('upload_file');
        const year = document.getElementById("target_year").value;
        const month = document.getElementById("target_month").value;

        if (!year || !month) {
            showAlert("Please select Year and Month before uploading.", "warning");
            return;
        }

        if (!fileInput.files.length) {
            showAlert("Please select a file to upload.", "warning");
            return;
        }

        const formData = new FormData();
        formData.append('file', fileInput.files[0]);
        formData.append('year', year);
        formData.append('month', month);

        try {
            const response = await fetch('/api/v1/targets/upload', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            });

            const result = await response.json();
            
            if (!response.ok) throw new Error(result.message || 'Upload failed');
            
            showAlert(`Upload completed: ${result.created} created, ${result.updated} updated`, "success");
            if (result.errors > 0) {
                console.error('Upload errors:', result.error_details);
                showAlert(`Warning: ${result.errors} errors occurred. Check console for details.`, "warning");
            }
            
            // Reset the file input
            fileInput.value = '';
            
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('uploadModal')).hide();
            
            // Wait a bit for the backend to finish processing
            await new Promise(resolve => setTimeout(resolve, 500));
            
            // Refresh the matrix with current filters
            await loadTargetMatrix();
            
            // Force a complete refresh of the data
            await loadMasterData();
            
        } catch (error) {
            showAlert(error.message, "error");
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        // Test authentication on page load
        console.log('Page loaded. Testing authentication...');
        fetch('/api/v1/test-auth', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            console.log('Auth test result:', data);
        })
        .catch(error => {
            console.error('Auth test failed:', error);
        });
        
        loadMasterData();
        
        // Add event listeners to reset matrix loaded state when filters change
        const filterElements = ['target_year', 'target_month', 'filter_region', 'filter_channel', 'filter_supplier', 'filter_category', 'filter_salesman', 'filter_classification'];
        
        filterElements.forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.addEventListener('change', function() {
                    isMatrixLoaded = false;
                });
            }
        });
    });
</script>
@endpush
