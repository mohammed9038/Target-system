@echo off
setlocal EnableDelayedExpansion

REM Target Management System - Comprehensive Test Suite
REM This script runs all tests for the application

echo ==========================================
echo TARGET MANAGEMENT SYSTEM - TEST SUITE
echo ==========================================
echo.

REM Check if we're in the right directory
if not exist "artisan" (
    echo [ERROR] Please run this script from the Laravel project root directory
    exit /b 1
)

echo [INFO] Starting comprehensive test suite...
echo.

REM Set up test environment
echo [INFO] Setting up test environment...
if exist ".env" copy /Y ".env" ".env.backup" >nul 2>&1
if not exist ".env.testing" echo DB_CONNECTION=sqlite > .env.testing

REM Create test database
echo [INFO] Creating test database...
if not exist "database\testing.sqlite" type nul > "database\testing.sqlite"

echo.
echo ==========================================
echo 1. BASIC FUNCTIONALITY TESTS
echo ==========================================

echo [INFO] Running Master Data CRUD Tests...
php artisan test --filter=MasterDataTest
if !errorlevel! equ 0 (
    echo [SUCCESS] Master Data CRUD Tests - PASSED
) else (
    echo [ERROR] Master Data CRUD Tests - FAILED
)

echo.
echo ==========================================
echo 2. IMPORT/EXPORT FUNCTIONALITY TESTS
echo ==========================================

echo [INFO] Running Import/Export Tests...
php artisan test --filter=ImportExportTest
if !errorlevel! equ 0 (
    echo [SUCCESS] Import/Export Tests - PASSED
) else (
    echo [ERROR] Import/Export Tests - FAILED
)

echo.
echo ==========================================
echo 3. FILTERING AND SEARCH TESTS
echo ==========================================

echo [INFO] Running Filtering Tests...
php artisan test --filter=FilteringTest
if !errorlevel! equ 0 (
    echo [SUCCESS] Filtering Tests - PASSED
) else (
    echo [ERROR] Filtering Tests - FAILED
)

echo.
echo ==========================================
echo 4. VALIDATION TESTS
echo ==========================================

echo [INFO] Running Validation Tests...
php artisan test --filter=ValidationTest
if !errorlevel! equ 0 (
    echo [SUCCESS] Validation Tests - PASSED
) else (
    echo [ERROR] Validation Tests - FAILED
)

echo.
echo ==========================================
echo 5. PERMISSIONS AND SECURITY TESTS
echo ==========================================

echo [INFO] Running Permissions Tests...
php artisan test --filter=PermissionsTest
if !errorlevel! equ 0 (
    echo [SUCCESS] Permissions Tests - PASSED
) else (
    echo [ERROR] Permissions Tests - FAILED
)

echo.
echo ==========================================
echo 6. COMPREHENSIVE TEST SUITE
echo ==========================================

echo [INFO] Running all tests together...
php artisan test
if !errorlevel! equ 0 (
    echo [SUCCESS] All Tests - PASSED
) else (
    echo [ERROR] Some tests failed - Check output above
)

echo.
echo ==========================================
echo 7. MANUAL TESTING CHECKLIST
echo ==========================================

echo.
echo [INFO] Manual testing checklist:
echo.
echo CRUD Operations:
echo    □ Create new records in all master data pages
echo    □ Edit existing records
echo    □ Delete records
echo    □ View individual records
echo.
echo Import/Export:
echo    □ Export data from each master data page
echo    □ Download templates
echo    □ Import valid Excel files
echo    □ Test import with 'Update existing' option
echo    □ Test import validation errors
echo.
echo Filtering:
echo    □ Search functionality on each page
echo    □ Status filtering (active/inactive)
echo    □ Record count display
echo.
echo Permissions:
echo    □ Admin access to user management
echo    □ Regular user restrictions
echo    □ Authentication requirements
echo.
echo UI/UX:
echo    □ Responsive design on mobile/tablet
echo    □ Success/error message display
echo    □ Modal functionality
echo    □ Loading states
echo.

echo.
echo ==========================================
echo 8. QUICK FUNCTIONAL TEST
echo ==========================================

echo [INFO] Running quick functional verification...

REM Test if Laravel can boot
php artisan --version >nul 2>&1
if !errorlevel! equ 0 (
    echo [SUCCESS] Laravel application boots correctly
) else (
    echo [ERROR] Laravel application failed to boot
)

REM Test database connection
php artisan migrate:status >nul 2>&1
if !errorlevel! equ 0 (
    echo [SUCCESS] Database connection working
) else (
    echo [WARNING] Database connection issues detected
)

REM Test route compilation
php artisan route:list >nul 2>&1
if !errorlevel! equ 0 (
    echo [SUCCESS] Routes compiled successfully
) else (
    echo [ERROR] Route compilation failed
)

echo.
echo ==========================================
echo TEST SUMMARY
echo ==========================================

echo [INFO] Test suite completed!
echo.
echo Tests Coverage:
echo    ✓ Master Data CRUD Operations
echo    ✓ Import/Export Functionality
echo    ✓ Data Filtering and Search
echo    ✓ Form Validation
echo    ✓ User Permissions and Security
echo.
echo Next Steps:
echo    1. Review any failed tests above
echo    2. Complete manual testing checklist
echo    3. Test on different browsers/devices
echo    4. Perform user acceptance testing
echo.

REM Restore original environment
if exist ".env.backup" (
    move ".env.backup" ".env" >nul 2>&1
)

echo [SUCCESS] Test suite execution completed!
echo.
echo To run individual test suites:
echo    php artisan test --filter=MasterDataTest
echo    php artisan test --filter=ImportExportTest
echo    php artisan test --filter=FilteringTest
echo    php artisan test --filter=ValidationTest
echo    php artisan test --filter=PermissionsTest
echo.
pause
