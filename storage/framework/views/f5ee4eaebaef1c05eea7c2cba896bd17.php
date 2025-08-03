<?php $__env->startSection('title', __('Dashboard')); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="h2 mb-1"><?php echo e(__('Dashboard')); ?></h1>
        <p class="text-muted mb-0"><?php echo e(__('Welcome back,')); ?> <strong><?php echo e(auth()->user()->username); ?></strong> - <?php echo e(__('View and analyze your sales target data')); ?></p>
    </div>
    <div class="d-flex gap-2" style="margin-top: 0.5rem;">
        <div class="text-end me-3">
            <div class="text-muted small"><?php echo e(__('Last Login')); ?></div>
            <div class="fw-medium"><?php echo e(now()->format('M d, Y H:i')); ?></div>
        </div>
        <span class="badge bg-info-subtle text-info px-3 py-2">
            <i class="bi bi-graph-up me-1"></i><?php echo e(__('Analytics Dashboard')); ?>

        </span>
    </div>
</div>

<!-- Dashboard Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-sliders me-2"></i><?php echo e(__('Dashboard Filters')); ?>

        </h5>
        <small class="text-muted"><?php echo e(__('Filter your dashboard data by period, location, and classification')); ?></small>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <!-- Period Filters -->
            <div class="col-md-2">
                <label for="yearFilter" class="form-label small fw-medium">
                    <i class="bi bi-calendar-date me-1"></i><?php echo e(__('Year')); ?>

                </label>
                <select class="form-select form-select-sm" id="yearFilter">
                    <option value=""><?php echo e(__('All Years')); ?></option>
                    <?php for($y = date('Y'); $y >= date('Y') - 2; $y--): ?>
                        <option value="<?php echo e($y); ?>" <?php echo e($y == date('Y') ? 'selected' : ''); ?>><?php echo e($y); ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="monthFilter" class="form-label small fw-medium">
                    <i class="bi bi-calendar-month me-1"></i><?php echo e(__('Month')); ?>

                </label>
                <select class="form-select form-select-sm" id="monthFilter">
                    <option value=""><?php echo e(__('All Months')); ?></option>
                    <?php for($m = 1; $m <= 12; $m++): ?>
                        <option value="<?php echo e($m); ?>" <?php echo e($m == date('n') ? 'selected' : ''); ?>>
                            <?php echo e(date('M', mktime(0, 0, 0, $m, 1))); ?>

                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <!-- Location Filters -->
            <?php if(auth()->user()->isAdmin()): ?>
            <div class="col-md-2">
                <label for="regionFilter" class="form-label small fw-medium">
                    <i class="bi bi-geo-alt me-1"></i><?php echo e(__('Region')); ?>

                </label>
                <select class="form-select form-select-sm" id="regionFilter">
                    <option value=""><?php echo e(__('All Regions')); ?></option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="channelFilter" class="form-label small fw-medium">
                    <i class="bi bi-diagram-3 me-1"></i><?php echo e(__('Channel')); ?>

                </label>
                <select class="form-select form-select-sm" id="channelFilter">
                    <option value=""><?php echo e(__('All Channels')); ?></option>
                </select>
            </div>
            <?php else: ?>
            <!-- Manager sees their assigned region/channel only -->
            <div class="col-md-2">
                <label for="regionFilter" class="form-label small fw-medium">
                    <i class="bi bi-geo-alt me-1"></i><?php echo e(__('Region')); ?>

                </label>
                <select class="form-select form-select-sm" id="regionFilter" 
                        <?php if(auth()->user()->isManager() && auth()->user()->regions->count() <= 1): ?> disabled <?php endif; ?>>
                    <?php if(auth()->user()->isAdmin()): ?>
                        <option value=""><?php echo e(__('All Regions')); ?></option>
                    <?php elseif(auth()->user()->regions->count() == 1): ?>
                        <option value="<?php echo e(auth()->user()->regions->first()->id); ?>" selected>
                            <?php echo e(auth()->user()->regions->first()->name); ?>

                        </option>
                    <?php else: ?>
                        <option value=""><?php echo e(__('Select Region')); ?></option>
                        <?php $__currentLoopData = auth()->user()->regions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $region): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($region->id); ?>"><?php echo e($region->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="channelFilter" class="form-label small fw-medium">
                    <i class="bi bi-diagram-3 me-1"></i><?php echo e(__('Channel')); ?>

                </label>
                <select class="form-select form-select-sm" id="channelFilter" 
                        <?php if(auth()->user()->isManager() && auth()->user()->channels->count() <= 1): ?> disabled <?php endif; ?>>
                    <?php if(auth()->user()->isAdmin()): ?>
                        <option value=""><?php echo e(__('All Channels')); ?></option>
                    <?php elseif(auth()->user()->channels->count() == 1): ?>
                        <option value="<?php echo e(auth()->user()->channels->first()->id); ?>" selected>
                            <?php echo e(auth()->user()->channels->first()->name); ?>

                        </option>
                    <?php else: ?>
                        <option value=""><?php echo e(__('Select Channel')); ?></option>
                        <?php $__currentLoopData = auth()->user()->channels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $channel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($channel->id); ?>"><?php echo e($channel->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                </select>
            </div>
            <?php endif; ?>
            
            <!-- Product Filters -->
            <div class="col-md-2">
                <label for="supplierFilter" class="form-label small fw-medium">
                    <i class="bi bi-building me-1"></i><?php echo e(__('Supplier')); ?>

                </label>
                <select class="form-select form-select-sm" id="supplierFilter">
                    <option value=""><?php echo e(__('All Suppliers')); ?></option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="categoryFilter" class="form-label small fw-medium">
                    <i class="bi bi-tags me-1"></i><?php echo e(__('Category')); ?>

                </label>
                <select class="form-select form-select-sm" id="categoryFilter">
                    <option value=""><?php echo e(__('All Categories')); ?></option>
                </select>
            </div>
        </div>
        
        <div class="row g-3 mt-2">
            <!-- Classification Filter -->
            <div class="col-md-2">
                <label for="classificationFilter" class="form-label small fw-medium">
                    <i class="bi bi-collection me-1"></i><?php echo e(__('Classification')); ?>

                </label>
                <select class="form-select form-select-sm" id="classificationFilter">
                    <!-- Will be populated dynamically based on user permissions -->
                </select>
            </div>
            
            <!-- Salesman Filter -->
            <div class="col-md-3">
                <label for="salesmanFilter" class="form-label small fw-medium">
                    <i class="bi bi-people me-1"></i><?php echo e(__('Salesman')); ?>

                </label>
                <select class="form-select form-select-sm" id="salesmanFilter">
                    <option value=""><?php echo e(__('All Salesmen')); ?></option>
                </select>
            </div>
            
            <!-- Action Buttons -->
            <div class="col-md-7 d-flex align-items-end gap-2">
                <button class="btn btn-primary btn-sm" onclick="loadReports()">
                    <i class="bi bi-arrow-clockwise me-1"></i><?php echo e(__('Refresh Dashboard')); ?>

                </button>
                <button class="btn btn-outline-secondary btn-sm" onclick="clearFilters()">
                    <i class="bi bi-x-circle me-1"></i><?php echo e(__('Clear Filters')); ?>

                </button>
                <button class="btn btn-success btn-sm" onclick="exportReport()">
                    <i class="bi bi-download me-1"></i><?php echo e(__('Export Data')); ?>

                </button>
            </div>
        </div>
    </div>
</div>



<!-- Dashboard Data Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-table me-2"></i><?php echo e(__('Targets Overview')); ?>

        </h5>
        <div class="d-flex gap-2">
            <a href="<?php echo e(route('targets.index')); ?>" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-plus-circle me-1"></i><?php echo e(__('Manage Targets')); ?>

            </a>
        </div>
    </div>
    <div class="card-body">
        <div id="reportsContent">
            <div class="text-center py-5">
                <i class="bi bi-speedometer2 text-muted" style="font-size: 3rem;"></i>
                <h5 class="mt-3"><?php echo e(__('Dashboard Ready')); ?></h5>
                <p class="text-muted"><?php echo e(__('Apply filters and click "Refresh Dashboard" to view your data')); ?></p>
            </div>
        </div>
    </div>
</div>

<script>
let userInfo = null;

document.addEventListener('DOMContentLoaded', function() {
    console.log('🎯 DASHBOARD PAGE LOADED');
    loadUserInfo();
    loadFilters();
});

async function loadUserInfo() {
    const fetchOptions = {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        credentials: 'same-origin'
    };

    try {
        console.log('📡 Fetching user info from /api/v1/user/info');
        const response = await fetch('/api/v1/user/info', fetchOptions);
        console.log('📡 User info response status:', response.status);
        
        if (response.ok) {
            const result = await response.json();
            console.log('👤 User info received:', result);
            userInfo = result.data;
            setUserClassificationFilter();
        } else {
            console.error('❌ User info failed:', response.status, await response.text());
        }
    } catch (error) {
        console.error('❌ Error loading user info:', error);
    }
}

function setUserClassificationFilter() {
    console.log('🎯 Setting classification filter, userInfo:', userInfo);
    const classificationSelect = document.getElementById('classificationFilter');
    classificationSelect.innerHTML = ''; // Clear existing options
    
    if (userInfo.is_admin) {
        // Admin sees all options
        const allOption = document.createElement('option');
        allOption.value = '';
        allOption.textContent = 'All Classifications';
        classificationSelect.appendChild(allOption);
        
        const foodOption = document.createElement('option');
        foodOption.value = 'food';
        foodOption.textContent = 'Food';
        classificationSelect.appendChild(foodOption);
        
        const nonFoodOption = document.createElement('option');
        nonFoodOption.value = 'non_food';
        nonFoodOption.textContent = 'Non-Food';
        classificationSelect.appendChild(nonFoodOption);
    } else {
        // Manager sees based on their assigned classifications
        if (userInfo.classifications && userInfo.classifications.length > 0) {
            if (userInfo.classifications.length > 1) {
                // Multiple classifications - show "All" option
                const allOption = document.createElement('option');
                allOption.value = '';
                allOption.textContent = 'All Classifications';
                classificationSelect.appendChild(allOption);
            }
            
            userInfo.classifications.forEach(classification => {
                const option = document.createElement('option');
                option.value = classification;
                option.textContent = classification === 'food' ? 'Food' : 'Non-Food';
                if (userInfo.classifications.length === 1) {
                    option.selected = true;
                    classificationSelect.disabled = true;
                }
                classificationSelect.appendChild(option);
            });
        } else {
            // No classifications assigned
            const noOption = document.createElement('option');
            noOption.value = '';
            noOption.textContent = 'No Classifications';
            noOption.disabled = true;
            classificationSelect.appendChild(noOption);
            classificationSelect.disabled = true;
        }
    }
}

async function loadFilters() {
    const fetchOptions = {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        credentials: 'same-origin'
    };

    try {
        // Load regions
        console.log('📡 Loading regions from /api/v1/deps/regions');
        const regionsResponse = await fetch('/api/v1/deps/regions', fetchOptions);
        console.log('📡 Regions response status:', regionsResponse.status);
        
        if (regionsResponse.ok) {
            const regions = await regionsResponse.json();
            console.log('🏘️ Regions loaded:', regions);
            populateSelect('regionFilter', regions.data, 'id', 'name');
        } else {
            console.error('❌ Regions failed:', regionsResponse.status, await regionsResponse.text());
        }

        // Load channels
        console.log('📡 Loading channels from /api/v1/deps/channels');
        const channelsResponse = await fetch('/api/v1/deps/channels', fetchOptions);
        console.log('📡 Channels response status:', channelsResponse.status);
        
        if (channelsResponse.ok) {
            const channels = await channelsResponse.json();
            console.log('🏢 Channels loaded:', channels);
            populateSelect('channelFilter', channels.data, 'id', 'name');
        } else {
            console.error('❌ Channels failed:', channelsResponse.status, await channelsResponse.text());
        }

        // Load suppliers
        console.log('📡 Loading suppliers from /api/v1/deps/suppliers');
        const suppliersResponse = await fetch('/api/v1/deps/suppliers', fetchOptions);
        console.log('📡 Suppliers response status:', suppliersResponse.status);
        
        if (suppliersResponse.ok) {
            const suppliers = await suppliersResponse.json();
            console.log('🏪 Suppliers loaded:', suppliers);
            populateSelect('supplierFilter', suppliers.data, 'id', 'name');
        } else {
            console.error('❌ Suppliers failed:', suppliersResponse.status, await suppliersResponse.text());
        }

        // Load categories
        console.log('📡 Loading categories from /api/v1/deps/categories');
        const categoriesResponse = await fetch('/api/v1/deps/categories', fetchOptions);
        console.log('📡 Categories response status:', categoriesResponse.status);
        
        if (categoriesResponse.ok) {
            const categories = await categoriesResponse.json();
            console.log('📦 Categories loaded:', categories);
            populateSelect('categoryFilter', categories.data, 'id', 'name');
        } else {
            console.error('❌ Categories failed:', categoriesResponse.status, await categoriesResponse.text());
        }

        // Load salesmen
        console.log('📡 Loading salesmen from /api/v1/deps/salesmen');
        const salesmenResponse = await fetch('/api/v1/deps/salesmen', fetchOptions);
        console.log('📡 Salesmen response status:', salesmenResponse.status);
        
        if (salesmenResponse.ok) {
            const salesmen = await salesmenResponse.json();
            console.log('👥 Salesmen loaded:', salesmen);
            populateSelect('salesmanFilter', salesmen.data, 'id', 'name');
        } else {
            console.error('❌ Salesmen failed:', salesmenResponse.status, await salesmenResponse.text());
        }

        // Set up cascading filters
        setupCascadingFilters();
        
    } catch (error) {
        console.error('Error loading filters:', error);
    }
}

/**
 * Helper function to populate select elements
 */
function populateSelect(elementId, data, valueField, textField) {
    const select = document.getElementById(elementId);
    if (select && data) {
        const firstOption = select.options[0]; // Preserve the "All" option
        select.innerHTML = "";
        if (firstOption) {
            select.appendChild(firstOption);
        }
        data.forEach(item => {
            const option = document.createElement("option");
            option.value = item[valueField];
            option.textContent = item[textField];
            select.appendChild(option);
        });
    }
}

/**
 * Set up cascading filter event listeners
 */
function setupCascadingFilters() {
    // Classification change affects suppliers and salesmen
    const classificationSelect = document.getElementById('classificationFilter');
    if (classificationSelect) {
        classificationSelect.addEventListener('change', function() {
            updateSuppliersFilter();
            updateSalesmenFilter();
        });
    }

    // Supplier change affects categories
    const supplierSelect = document.getElementById('supplierFilter');
    if (supplierSelect) {
        supplierSelect.addEventListener('change', function() {
            updateCategoriesFilter();
        });
    }

    // Region change affects salesmen
    const regionSelect = document.getElementById('regionFilter');
    if (regionSelect) {
        regionSelect.addEventListener('change', function() {
            updateSalesmenFilter();
        });
    }

    // Channel change affects salesmen
    const channelSelect = document.getElementById('channelFilter');
    if (channelSelect) {
        channelSelect.addEventListener('change', function() {
            updateSalesmenFilter();
        });
    }
}

/**
 * Update suppliers dropdown based on classification filter
 */
async function updateSuppliersFilter() {
    const fetchOptions = {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        credentials: 'same-origin'
    };

    try {
        const classification = document.getElementById('classificationFilter').value;
        const url = `/api/v1/deps/filtered/suppliers${classification ? `?classification=${classification}` : ''}`;
        
        const response = await fetch(url, fetchOptions);
        const data = await response.json();
        
        populateSelect('supplierFilter', data.data, 'id', 'name');
        
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
    const fetchOptions = {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        credentials: 'same-origin'
    };

    try {
        const classification = document.getElementById('classificationFilter').value;
        const supplierId = document.getElementById('supplierFilter').value;
        
        const params = new URLSearchParams();
        if (classification) params.append('classification', classification);
        if (supplierId) params.append('supplier_id', supplierId);
        
        const url = `/api/v1/deps/filtered/categories${params.toString() ? `?${params.toString()}` : ''}`;
        
        const response = await fetch(url, fetchOptions);
        const data = await response.json();
        
        populateSelect('categoryFilter', data.data, 'id', 'name');
    } catch (error) {
        console.error('Failed to update categories filter:', error);
    }
}

/**
 * Update salesmen dropdown based on region, channel, and classification filters
 */
async function updateSalesmenFilter() {
    const fetchOptions = {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        credentials: 'same-origin'
    };

    try {
        const classification = document.getElementById('classificationFilter').value;
        const regionId = document.getElementById('regionFilter').value;
        const channelId = document.getElementById('channelFilter').value;
        
        const params = new URLSearchParams();
        if (classification) params.append('classification', classification);
        if (regionId) params.append('region_id', regionId);
        if (channelId) params.append('channel_id', channelId);
        
        const url = `/api/v1/deps/filtered/salesmen${params.toString() ? `?${params.toString()}` : ''}`;
        
        const response = await fetch(url, fetchOptions);
        const data = await response.json();
        
        populateSelect('salesmanFilter', data.data, 'id', 'name');
    } catch (error) {
        console.error('Failed to update salesmen filter:', error);
    }
}



async function loadReports() {
    console.log('📊 Loading reports...');
    const reportsContent = document.getElementById('reportsContent');
    
    // Get all filter values
    const filters = {
        year: document.getElementById('yearFilter').value,
        month: document.getElementById('monthFilter').value,
        region_id: document.getElementById('regionFilter').value,
        channel_id: document.getElementById('channelFilter').value,
        supplier_id: document.getElementById('supplierFilter').value,
        category_id: document.getElementById('categoryFilter').value,
        classification: document.getElementById('classificationFilter').value,
        salesman_id: document.getElementById('salesmanFilter').value,
    };
    
    console.log('🎯 Raw filters:', filters);
    
    // Remove empty filters
    Object.keys(filters).forEach(key => {
        if (!filters[key]) {
            delete filters[key];
        }
    });
    
    console.log('🎯 Clean filters:', filters);
    
    // Show loading state
    reportsContent.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden"><?php echo e(__('Loading...')); ?></span>
            </div>
            <p class="text-muted mt-2"><?php echo e(__('Generating report with selected filters...')); ?></p>
        </div>
    `;
    
    try {
        // Build query string
        const queryParams = new URLSearchParams(filters);
        
        // Fetch targets data from API
        const apiUrl = `/api/v1/targets?${queryParams}`;
        console.log('📡 Fetching targets from:', apiUrl);
        
        const response = await fetch(apiUrl, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        });
        
        console.log('📡 Targets response status:', response.status);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('❌ Targets API error:', response.status, errorText);
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const result = await response.json();
        console.log('📊 Targets result:', result);
        const targets = result.data.data || []; // Handle pagination
        console.log('📊 Targets array:', targets);
        
        // Build active filters display
        const activeFilters = Object.entries(filters)
            .map(([key, value]) => {
                const filterNames = {
                    year: 'Year',
                    month: 'Month',
                    region_id: 'Region',
                    channel_id: 'Channel',
                    supplier_id: 'Supplier',
                    category_id: 'Category',
                    classification: 'Classification',
                    salesman_id: 'Salesman'
                };
                return `${filterNames[key]}: ${value}`;
            });
        
        if (targets.length === 0) {
            // No data found
            reportsContent.innerHTML = `
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0">
                                    <i class="bi bi-person me-1 text-muted"></i><?php echo e(__('Salesman')); ?>

                                </th>
                                <th class="border-0">
                                    <i class="bi bi-geo-alt me-1 text-muted"></i><?php echo e(__('Region')); ?>

                                </th>
                                <th class="border-0">
                                    <i class="bi bi-diagram-3 me-1 text-muted"></i><?php echo e(__('Channel')); ?>

                                </th>
                                <th class="border-0">
                                    <i class="bi bi-building me-1 text-muted"></i><?php echo e(__('Supplier')); ?>

                                </th>
                                <th class="border-0">
                                    <i class="bi bi-tags me-1 text-muted"></i><?php echo e(__('Category')); ?>

                                </th>
                                <th class="border-0">
                                    <i class="bi bi-calendar me-1 text-muted"></i><?php echo e(__('Period')); ?>

                                </th>
                                <th class="border-0">
                                    <i class="bi bi-currency-dollar me-1 text-muted"></i><?php echo e(__('Target Amount')); ?>

                                </th>
                                <th class="border-0">
                                    <i class="bi bi-activity me-1 text-muted"></i><?php echo e(__('Status')); ?>

                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-graph-up" style="font-size: 2rem;"></i>
                                        <p class="mt-2 mb-1"><?php echo e(__('No data available for the selected filters')); ?></p>
                                        ${activeFilters.length > 0 ? 
                                            `<small class="text-muted"><?php echo e(__('Active filters')); ?>: ${activeFilters.join(', ')}</small>` : 
                                            `<small class="text-muted"><?php echo e(__('Try selecting specific filters to narrow down the results')); ?></small>`
                                        }
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            `;
        } else {
            // Display the data
            const tableRows = targets.map(target => {
                const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                                 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                const monthName = monthNames[target.month - 1] || target.month;
                const period = `${monthName} ${target.year}`;
                
                return `
                    <tr>
                        <td>
                            <div class="fw-medium">${target.salesman?.name || 'N/A'}</div>
                            <small class="text-muted">${target.salesman?.salesman_code || ''}</small>
                        </td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary">${target.region?.name || 'N/A'}</span>
                        </td>
                        <td>
                            <span class="badge bg-info-subtle text-info">${target.channel?.name || 'N/A'}</span>
                        </td>
                        <td>
                            <div class="fw-medium">${target.supplier?.name || 'N/A'}</div>
                            <small class="text-muted">${target.supplier?.supplier_code || ''}</small>
                        </td>
                        <td>
                            <div class="fw-medium">${target.category?.name || 'N/A'}</div>
                            <small class="text-muted">${target.category?.category_code || ''}</small>
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary">${period}</span>
                        </td>
                        <td class="text-end">
                            <span class="fw-bold text-success">$${parseFloat(target.target_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
                        </td>
                        <td>
                            <span class="badge bg-success-subtle text-success">
                                <i class="bi bi-check-circle me-1"></i><?php echo e(__('Active')); ?>

                            </span>
                        </td>
                    </tr>
                `;
            }).join('');
            
            reportsContent.innerHTML = `
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0">
                                    <i class="bi bi-person me-1 text-muted"></i><?php echo e(__('Salesman')); ?>

                                </th>
                                <th class="border-0">
                                    <i class="bi bi-geo-alt me-1 text-muted"></i><?php echo e(__('Region')); ?>

                                </th>
                                <th class="border-0">
                                    <i class="bi bi-diagram-3 me-1 text-muted"></i><?php echo e(__('Channel')); ?>

                                </th>
                                <th class="border-0">
                                    <i class="bi bi-building me-1 text-muted"></i><?php echo e(__('Supplier')); ?>

                                </th>
                                <th class="border-0">
                                    <i class="bi bi-tags me-1 text-muted"></i><?php echo e(__('Category')); ?>

                                </th>
                                <th class="border-0">
                                    <i class="bi bi-calendar me-1 text-muted"></i><?php echo e(__('Period')); ?>

                                </th>
                                <th class="border-0 text-end">
                                    <i class="bi bi-currency-dollar me-1 text-muted"></i><?php echo e(__('Target Amount')); ?>

                                </th>
                                <th class="border-0">
                                    <i class="bi bi-activity me-1 text-muted"></i><?php echo e(__('Status')); ?>

                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            ${tableRows}
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td colspan="6" class="text-end border-0">
                                    <i class="bi bi-calculator me-1 text-muted"></i><?php echo e(__('Total')); ?>:
                                </td>
                                <td class="text-end border-0" id="reportsTotal">
                                    $0.00
                                </td>
                                <td class="border-0"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                ${result.data.total ? `
                    <div class="card-footer bg-light">
                        <small class="text-muted">
                            <?php echo e(__('Showing')); ?> ${targets.length} <?php echo e(__('of')); ?> ${result.data.total} <?php echo e(__('targets')); ?>

                            ${activeFilters.length > 0 ? ` | <?php echo e(__('Filtered by')); ?>: ${activeFilters.join(', ')}` : ''}
                        </small>
                    </div>
                ` : ''}
            `;
        }

        // Calculate and update total
        updateReportsTotal();
        
    } catch (error) {
        console.error('Error loading reports:', error);
        reportsContent.innerHTML = `
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?php echo e(__('Error loading report data')); ?>: ${error.message}
                <br><small class="text-muted"><?php echo e(__('Please try again or contact support if the problem persists.')); ?></small>
            </div>
        `;
    }
}

function exportReport() {
    // Get all filter values
    const filters = {
        year: document.getElementById('yearFilter').value,
        month: document.getElementById('monthFilter').value,
        region_id: document.getElementById('regionFilter').value,
        channel_id: document.getElementById('channelFilter').value,
        supplier_id: document.getElementById('supplierFilter').value,
        category_id: document.getElementById('categoryFilter').value,
        classification: document.getElementById('classificationFilter').value,
        salesman_id: document.getElementById('salesmanFilter').value,
    };
    
    // Remove empty filters
    Object.keys(filters).forEach(key => {
        if (!filters[key]) {
            delete filters[key];
        }
    });
    
    // Build query string
    const queryParams = new URLSearchParams(filters);
    
    // Open the export URL in a new window
    window.open(`/api/v1/reports/export.xlsx?${queryParams}`, '_blank');
}

function clearFilters() {
    // Reset all filter dropdowns to their default values
    document.getElementById('yearFilter').value = '<?php echo e(date("Y")); ?>';
    document.getElementById('monthFilter').value = '<?php echo e(date("n")); ?>';
    
    // Reset filters based on user permissions
    if (userInfo) {
        if (userInfo.is_admin) {
            document.getElementById('regionFilter').value = '';
            document.getElementById('channelFilter').value = '';
            document.getElementById('classificationFilter').value = '';
        } else {
            // Managers keep their assigned values or reset appropriately
            const regionFilter = document.getElementById('regionFilter');
            const channelFilter = document.getElementById('channelFilter');
            const classificationFilter = document.getElementById('classificationFilter');
            
            if (!regionFilter.disabled) {
                regionFilter.value = '';
            }
            if (!channelFilter.disabled) {
                channelFilter.value = '';
            }
            if (!classificationFilter.disabled) {
                classificationFilter.value = '';
            }
        }
    }
    
    document.getElementById('supplierFilter').value = '';
    document.getElementById('categoryFilter').value = '';
    document.getElementById('salesmanFilter').value = '';
    
    // Reload all filters to reset cascading dependencies
    loadFilters();
}

// Function to update reports total
function updateReportsTotal() {
    const reportsTotal = document.getElementById('reportsTotal');
    if (!reportsTotal) return;
    
    // Find all target amount cells in the reports table
    const targetCells = document.querySelectorAll('#reportsContent tbody tr td:nth-child(7)');
    let total = 0;
    
    targetCells.forEach(cell => {
        const text = cell.textContent.trim();
        // Extract numeric value from currency format (e.g., "$1,234.56" -> 1234.56)
        const value = parseFloat(text.replace(/[$,]/g, ''));
        if (!isNaN(value)) {
            total += value;
        }
    });
    
    // Format and display the total
    reportsTotal.textContent = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(total);
}
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Target\resources\views/reports/index.blade.php ENDPATH**/ ?>