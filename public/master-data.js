// Master Data Management JavaScript
class MasterDataManager {
    constructor() {
        this.baseUrl = '/api/master-data';
        this.currentImportType = null;
        this.importModal = null;
        
        // Initialize when DOM is loaded
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.init());
        } else {
            this.init();
        }
    }

    init() {
        this.importModal = new bootstrap.Modal(document.getElementById('importModal'));
        this.loadStatistics();
        this.setupEventListeners();
    }

    setupEventListeners() {
        // File input change handler
        const fileInput = document.getElementById('importFile');
        if (fileInput) {
            fileInput.addEventListener('change', this.handleFileSelect.bind(this));
        }
    }

    // Load master data statistics
    async loadStatistics() {
        try {
            const response = await fetch('/api/system/info');
            if (!response.ok) throw new Error('Failed to load statistics');
            
            // For now, we'll use placeholder numbers
            // In a real implementation, you'd get these from the API
            document.getElementById('salesmenCount').textContent = '150';
            document.getElementById('suppliersCount').textContent = '75';
            document.getElementById('categoriesCount').textContent = '200';
            document.getElementById('regionsCount').textContent = '8';
            document.getElementById('channelsCount').textContent = '5';
            
        } catch (error) {
            console.error('Error loading statistics:', error);
            this.showAlert('Failed to load statistics', 'warning');
        }
    }

    // Export functions
    async exportSalesmen() {
        try {
            this.showAlert('Preparing salesmen export...', 'info');
            
            const response = await fetch(`${this.baseUrl}/salesmen/export?format=xlsx`);
            if (!response.ok) throw new Error('Export failed');
            
            const blob = await response.blob();
            this.downloadFile(blob, `salesmen_export_${this.getDateString()}.xlsx`);
            
            this.showAlert('Salesmen data exported successfully!', 'success');
        } catch (error) {
            console.error('Export error:', error);
            this.showAlert('Failed to export salesmen data', 'danger');
        }
    }

    async exportSuppliers() {
        try {
            this.showAlert('Preparing suppliers export...', 'info');
            
            const response = await fetch(`${this.baseUrl}/suppliers/export?format=xlsx`);
            if (!response.ok) throw new Error('Export failed');
            
            const blob = await response.blob();
            this.downloadFile(blob, `suppliers_export_${this.getDateString()}.xlsx`);
            
            this.showAlert('Suppliers data exported successfully!', 'success');
        } catch (error) {
            console.error('Export error:', error);
            this.showAlert('Failed to export suppliers data', 'danger');
        }
    }

    // Template download functions
    async downloadTemplate(type) {
        try {
            const endpoint = type === 'salesmen' ? 'salesmen/template' : 'suppliers/template';
            const response = await fetch(`${this.baseUrl}/${endpoint}`);
            
            if (!response.ok) throw new Error('Template download failed');
            
            const blob = await response.blob();
            this.downloadFile(blob, `${type}_template.xlsx`);
            
            this.showAlert(`${type} template downloaded successfully!`, 'success');
        } catch (error) {
            console.error('Template download error:', error);
            this.showAlert(`Failed to download ${type} template`, 'danger');
        }
    }

    // Import functions
    showImportModal(type) {
        this.currentImportType = type;
        document.getElementById('importType').textContent = type.charAt(0).toUpperCase() + type.slice(1);
        
        // Reset form
        document.getElementById('importForm').reset();
        document.getElementById('importProgress').classList.add('d-none');
        
        this.importModal.show();
    }

    handleFileSelect(event) {
        const file = event.target.files[0];
        if (!file) return;

        // Validate file
        const validTypes = ['.xlsx', '.xls', '.csv'];
        const fileExt = '.' + file.name.split('.').pop().toLowerCase();
        
        if (!validTypes.includes(fileExt)) {
            this.showAlert('Invalid file format. Please select an Excel or CSV file.', 'danger');
            event.target.value = '';
            return;
        }

        if (file.size > 10 * 1024 * 1024) { // 10MB
            this.showAlert('File too large. Maximum size is 10MB.', 'danger');
            event.target.value = '';
            return;
        }
    }

    async performImport() {
        const fileInput = document.getElementById('importFile');
        const updateExisting = document.getElementById('updateExisting').checked;
        
        if (!fileInput.files[0]) {
            this.showAlert('Please select a file to import', 'warning');
            return;
        }

        const formData = new FormData();
        formData.append('file', fileInput.files[0]);
        formData.append('update_existing', updateExisting);

        try {
            // Show progress
            document.getElementById('importProgress').classList.remove('d-none');
            
            const endpoint = `${this.baseUrl}/${this.currentImportType}/import`;
            const response = await fetch(endpoint, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Import failed');
            }

            // Hide progress
            document.getElementById('importProgress').classList.add('d-none');
            
            // Show results
            this.showImportResults(result.data);
            this.importModal.hide();
            
            // Reload statistics
            this.loadStatistics();
            
        } catch (error) {
            document.getElementById('importProgress').classList.add('d-none');
            console.error('Import error:', error);
            this.showAlert(`Import failed: ${error.message}`, 'danger');
        }
    }

    showImportResults(data) {
        const message = `
            Import completed successfully!
            <br><strong>Total:</strong> ${data.total_rows} rows
            <br><strong>Imported:</strong> ${data.imported_rows} new records
            <br><strong>Updated:</strong> ${data.updated_rows} existing records
            <br><strong>Failed:</strong> ${data.failed_rows} errors
            ${data.errors && data.errors.length > 0 ? '<br><small>Check console for error details</small>' : ''}
        `;
        
        this.showAlert(message, data.failed_rows > 0 ? 'warning' : 'success', 10000);
        
        if (data.errors && data.errors.length > 0) {
            console.error('Import errors:', data.errors);
        }
    }

    // View functions
    async viewSalesmen() {
        try {
            const response = await fetch(`${this.baseUrl}/salesmen`);
            if (!response.ok) throw new Error('Failed to load salesmen');
            
            const result = await response.json();
            this.showDataModal('Salesmen', result.data, [
                'employee_code', 'name', 'region_name', 'channel_name', 'classification'
            ]);
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('Failed to load salesmen data', 'danger');
        }
    }

    async viewSuppliers() {
        try {
            const response = await fetch(`${this.baseUrl}/suppliers`);
            if (!response.ok) throw new Error('Failed to load suppliers');
            
            const result = await response.json();
            this.showDataModal('Suppliers', result.data, [
                'supplier_code', 'supplier_name', 'classification', 'category_name'
            ]);
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('Failed to load suppliers data', 'danger');
        }
    }

    async viewRegions() {
        try {
            const response = await fetch(`${this.baseUrl}/regions`);
            if (!response.ok) throw new Error('Failed to load regions');
            
            const result = await response.json();
            this.showDataModal('Regions', result.data, ['name', 'region_code']);
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('Failed to load regions data', 'danger');
        }
    }

    async viewChannels() {
        try {
            const response = await fetch(`${this.baseUrl}/channels`);
            if (!response.ok) throw new Error('Failed to load channels');
            
            const result = await response.json();
            this.showDataModal('Channels', result.data, ['name', 'channel_code']);
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('Failed to load channels data', 'danger');
        }
    }

    // Utility functions
    showDataModal(title, data, columns) {
        let tableHtml = `
            <div class="modal fade" id="dataModal" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${title} Data (${data.length} records)</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
        `;
        
        columns.forEach(col => {
            tableHtml += `<th>${col.replace('_', ' ').toUpperCase()}</th>`;
        });
        
        tableHtml += '</tr></thead><tbody>';
        
        data.slice(0, 100).forEach(row => { // Limit to first 100 rows
            tableHtml += '<tr>';
            columns.forEach(col => {
                tableHtml += `<td>${row[col] || '-'}</td>`;
            });
            tableHtml += '</tr>';
        });
        
        tableHtml += `
                                </tbody>
                            </table>
                        </div>
                        ${data.length > 100 ? '<div class="alert alert-info">Showing first 100 records</div>' : ''}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        const existingModal = document.getElementById('dataModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Add new modal to body
        document.body.insertAdjacentHTML('beforeend', tableHtml);
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('dataModal'));
        modal.show();
        
        // Clean up when modal is hidden
        document.getElementById('dataModal').addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
    }

    downloadFile(blob, filename) {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    }

    getDateString() {
        return new Date().toISOString().slice(0, 19).replace(/[:-]/g, '').replace('T', '_');
    }

    showAlert(message, type = 'info', duration = 5000) {
        const alertContainer = document.getElementById('alertContainer');
        const alertId = 'alert_' + Date.now();
        
        const alertHtml = `
            <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        alertContainer.insertAdjacentHTML('beforeend', alertHtml);
        
        // Auto-dismiss after duration
        setTimeout(() => {
            const alert = document.getElementById(alertId);
            if (alert) {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                bsAlert.close();
            }
        }, duration);
    }
}

// Global functions for HTML onclick handlers
let masterDataManager = new MasterDataManager();

function exportSalesmen() {
    masterDataManager.exportSalesmen();
}

function exportSuppliers() {
    masterDataManager.exportSuppliers();
}

function showImportModal(type) {
    masterDataManager.showImportModal(type);
}

function downloadTemplate(type) {
    masterDataManager.downloadTemplate(type);
}

function viewSalesmen() {
    masterDataManager.viewSalesmen();
}

function viewSuppliers() {
    masterDataManager.viewSuppliers();
}

function viewRegions() {
    masterDataManager.viewRegions();
}

function viewChannels() {
    masterDataManager.viewChannels();
}

function performImport() {
    masterDataManager.performImport();
}
