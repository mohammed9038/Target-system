#!/bin/bash

# Target Management System - Comprehensive Test Suite
# This script runs all tests for the application

echo "=========================================="
echo "TARGET MANAGEMENT SYSTEM - TEST SUITE"
echo "=========================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    print_error "Please run this script from the Laravel project root directory"
    exit 1
fi

print_status "Starting comprehensive test suite..."
echo ""

# Set up test environment
print_status "Setting up test environment..."
cp .env .env.backup 2>/dev/null || true
cp .env.testing .env 2>/dev/null || echo "DB_CONNECTION=sqlite" > .env.testing

# Create test database
print_status "Creating test database..."
touch database/testing.sqlite 2>/dev/null || true

echo ""
echo "=========================================="
echo "1. BASIC FUNCTIONALITY TESTS"
echo "=========================================="

print_status "Running Master Data CRUD Tests..."
php artisan test --filter=MasterDataTest
if [ $? -eq 0 ]; then
    print_success "Master Data CRUD Tests - PASSED"
else
    print_error "Master Data CRUD Tests - FAILED"
fi

echo ""
echo "=========================================="
echo "2. IMPORT/EXPORT FUNCTIONALITY TESTS"
echo "=========================================="

print_status "Running Import/Export Tests..."
php artisan test --filter=ImportExportTest
if [ $? -eq 0 ]; then
    print_success "Import/Export Tests - PASSED"
else
    print_error "Import/Export Tests - FAILED"
fi

echo ""
echo "=========================================="
echo "3. FILTERING AND SEARCH TESTS"
echo "=========================================="

print_status "Running Filtering Tests..."
php artisan test --filter=FilteringTest
if [ $? -eq 0 ]; then
    print_success "Filtering Tests - PASSED"
else
    print_error "Filtering Tests - FAILED"
fi

echo ""
echo "=========================================="
echo "4. VALIDATION TESTS"
echo "=========================================="

print_status "Running Validation Tests..."
php artisan test --filter=ValidationTest
if [ $? -eq 0 ]; then
    print_success "Validation Tests - PASSED"
else
    print_error "Validation Tests - FAILED"
fi

echo ""
echo "=========================================="
echo "5. PERMISSIONS AND SECURITY TESTS"
echo "=========================================="

print_status "Running Permissions Tests..."
php artisan test --filter=PermissionsTest
if [ $? -eq 0 ]; then
    print_success "Permissions Tests - PASSED"
else
    print_error "Permissions Tests - FAILED"
fi

echo ""
echo "=========================================="
echo "6. COMPREHENSIVE TEST SUITE"
echo "=========================================="

print_status "Running all tests together..."
php artisan test
if [ $? -eq 0 ]; then
    print_success "All Tests - PASSED"
else
    print_error "Some tests failed - Check output above"
fi

echo ""
echo "=========================================="
echo "7. MANUAL TESTING CHECKLIST"
echo "=========================================="

echo ""
print_status "Manual testing checklist:"
echo ""
echo "🔍 CRUD Operations:"
echo "   □ Create new records in all master data pages"
echo "   □ Edit existing records"
echo "   □ Delete records"
echo "   □ View individual records"
echo ""
echo "📊 Import/Export:"
echo "   □ Export data from each master data page"
echo "   □ Download templates"
echo "   □ Import valid Excel files"
echo "   □ Test import with 'Update existing' option"
echo "   □ Test import validation errors"
echo ""
echo "🔍 Filtering:"
echo "   □ Search functionality on each page"
echo "   □ Status filtering (active/inactive)"
echo "   □ Record count display"
echo ""
echo "🔐 Permissions:"
echo "   □ Admin access to user management"
echo "   □ Regular user restrictions"
echo "   □ Authentication requirements"
echo ""
echo "✅ UI/UX:"
echo "   □ Responsive design on mobile/tablet"
echo "   □ Success/error message display"
echo "   □ Modal functionality"
echo "   □ Loading states"
echo ""

echo ""
echo "=========================================="
echo "8. PERFORMANCE TESTING"
echo "=========================================="

print_status "Testing performance with sample data..."

# Generate sample data for performance testing
print_status "Generating sample data..."
php artisan tinker --execute="
\App\Models\Region::factory(100)->create();
\App\Models\Channel::factory(50)->create();
\App\Models\Supplier::factory(200)->create();
\App\Models\Category::factory(75)->create();
\App\Models\Salesman::factory(150)->create();
echo 'Sample data generated successfully';
"

print_status "Testing page load times..."
curl -w "@-" -o /dev/null -s "http://127.0.0.1:8000/regions" <<< "
     time_namelookup:  %{time_namelookup}\n
        time_connect:  %{time_connect}\n
     time_appconnect:  %{time_appconnect}\n
    time_pretransfer:  %{time_pretransfer}\n
       time_redirect:  %{time_redirect}\n
  time_starttransfer:  %{time_starttransfer}\n
                     ----------\n
          time_total:  %{time_total}\n
" 2>/dev/null || print_warning "Performance test requires running server"

echo ""
echo "=========================================="
echo "TEST SUMMARY"
echo "=========================================="

print_status "Test suite completed!"
echo ""
echo "📋 Tests Coverage:"
echo "   ✅ Master Data CRUD Operations"
echo "   ✅ Import/Export Functionality"
echo "   ✅ Data Filtering and Search"
echo "   ✅ Form Validation"
echo "   ✅ User Permissions and Security"
echo ""
echo "🚀 Next Steps:"
echo "   1. Review any failed tests above"
echo "   2. Complete manual testing checklist"
echo "   3. Test on different browsers/devices"
echo "   4. Perform user acceptance testing"
echo ""

# Restore original environment
if [ -f ".env.backup" ]; then
    mv .env.backup .env
fi

print_success "Test suite execution completed!"
