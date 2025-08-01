<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Region;
use App\Models\Channel;
use App\Models\Supplier;
use App\Models\Category;
use App\Models\Salesman;
use App\Models\ActiveMonthYear;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create regions first
        $region1 = Region::updateOrCreate(
            ['region_code' => 'R001'],
            [
                'name' => 'North Region',
                'is_active' => true,
            ]
        );

        $region2 = Region::updateOrCreate(
            ['region_code' => 'R002'],
            [
                'name' => 'South Region',
                'is_active' => true,
            ]
        );

        // Create channels
        $channel1 = Channel::updateOrCreate(
            ['channel_code' => 'C001'],
            [
                'name' => 'Direct Sales',
                'is_active' => true,
            ]
        );

        $channel2 = Channel::updateOrCreate(
            ['channel_code' => 'C002'],
            [
                'name' => 'Retail',
                'is_active' => true,
            ]
        );

        // Create suppliers
        $supplier1 = Supplier::updateOrCreate(
            ['supplier_code' => 'S001'],
            [
                'name' => 'Food Supplier A',
                'classification' => 'food',
            ]
        );

        $supplier2 = Supplier::updateOrCreate(
            ['supplier_code' => 'S002'],
            [
                'name' => 'Non-Food Supplier B',
                'classification' => 'non_food',
            ]
        );

        // Remove Universal Supplier C since suppliers should only be food or non_food

        // Create categories
        Category::updateOrCreate(
            ['category_code' => 'CAT001'],
            [
                'name' => 'Beverages',
                'supplier_id' => $supplier1->id,
            ]
        );

        Category::updateOrCreate(
            ['category_code' => 'CAT002'],
            [
                'name' => 'Snacks',
                'supplier_id' => $supplier1->id,
            ]
        );

        Category::updateOrCreate(
            ['category_code' => 'CAT003'],
            [
                'name' => 'Electronics',
                'supplier_id' => $supplier2->id,
            ]
        );

        // Removed categories for Universal Supplier C since it was removed

        // Create salesmen (without classification field - will use pivot table)
        $salesman1 = Salesman::updateOrCreate(
            ['salesman_code' => 'SAL001'],
            [
                'employee_code' => 'EMP001',
                'name' => 'John Doe',
                'region_id' => $region1->id,
                'channel_id' => $channel1->id,
            ]
        );

        $salesman2 = Salesman::updateOrCreate(
            ['salesman_code' => 'SAL002'],
            [
                'employee_code' => 'EMP002',
                'name' => 'Jane Smith',
                'region_id' => $region2->id,
                'channel_id' => $channel2->id,
            ]
        );

        $salesman3 = Salesman::updateOrCreate(
            ['salesman_code' => 'SAL003'],
            [
                'employee_code' => 'EMP003',
                'name' => 'ahmed',
                'region_id' => $region2->id,
                'channel_id' => $channel2->id,
            ]
        );

        // Assign classifications to salesmen (many-to-many)
        // John Doe can handle both food and non-food
        \App\Models\SalesmanClassification::updateOrCreate([
            'salesman_id' => $salesman1->id,
            'classification' => 'food'
        ]);
        \App\Models\SalesmanClassification::updateOrCreate([
            'salesman_id' => $salesman1->id,
            'classification' => 'non_food'
        ]);

        // Jane Smith handles only food
        \App\Models\SalesmanClassification::updateOrCreate([
            'salesman_id' => $salesman2->id,
            'classification' => 'food'
        ]);

        // ahmed handles only non-food
        \App\Models\SalesmanClassification::updateOrCreate([
            'salesman_id' => $salesman3->id,
            'classification' => 'non_food'
        ]);

        // Create active periods
        ActiveMonthYear::updateOrCreate(
            ['year' => 2025, 'month' => 8],
            ['is_open' => true]
        );

        ActiveMonthYear::updateOrCreate(
            ['year' => 2025, 'month' => 9],
            ['is_open' => false]
        );
        
        // Create current month period if different
        $currentYear = (int)date('Y');
        $currentMonth = (int)date('n');
        if ($currentYear != 2025 || $currentMonth != 8) {
            ActiveMonthYear::updateOrCreate(
                ['year' => $currentYear, 'month' => $currentMonth],
                ['is_open' => true]
            );
        }

        // Create admin user
        $admin = User::updateOrCreate(
            ['username' => 'admin'],
            [
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // Create manager user (no classification field - will use pivot table)
        $manager = User::updateOrCreate(
            ['username' => 'manager'],
            [
                'password' => Hash::make('password'),
                'role' => 'manager',
            ]
        );

        // Associate manager with regions and channels using pivot tables
        $manager->regions()->detach(); // Clear existing associations first
        $manager->channels()->detach();
        $manager->regions()->attach($region1->id);
        $manager->channels()->attach($channel1->id);

        // Assign food classification to manager
        \App\Models\UserClassification::updateOrCreate([
            'user_id' => $manager->id,
            'classification' => 'food'
        ]);

        // Create manager user for non-food classification
        $manager2 = User::updateOrCreate(
            ['username' => 'manager2'],
            [
                'password' => Hash::make('password'),
                'role' => 'manager',
            ]
        );

        // Associate manager2 with different regions and channels
        $manager2->regions()->detach(); // Clear existing associations first
        $manager2->channels()->detach();
        $manager2->regions()->attach($region2->id);
        $manager2->channels()->attach($channel2->id);

        // Assign non-food classification to manager2
        \App\Models\UserClassification::updateOrCreate([
            'user_id' => $manager2->id,
            'classification' => 'non_food'
        ]);

        // Create a manager with access to both classifications
        $manager3 = User::updateOrCreate(
            ['username' => 'manager3'],
            [
                'password' => Hash::make('password'),
                'role' => 'manager',
            ]
        );

        // Associate manager3 with multiple regions and channels
        $manager3->regions()->detach(); // Clear existing associations first
        $manager3->channels()->detach();
        $manager3->regions()->attach([$region1->id, $region2->id]);
        $manager3->channels()->attach([$channel1->id, $channel2->id]);

        // Assign both classifications to manager3
        \App\Models\UserClassification::updateOrCreate([
            'user_id' => $manager3->id,
            'classification' => 'food'
        ]);
        \App\Models\UserClassification::updateOrCreate([
            'user_id' => $manager3->id,
            'classification' => 'non_food'
        ]);
    }
} 