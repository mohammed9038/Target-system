<!DOCTYPE html>
<html>
<head>
    <title>Region Details</title>
</head>
<body>
    <h1>Region Details</h1>
    <p><strong>Region Code:</strong> {{ $region->region_code }}</p>
    <p><strong>Name:</strong> {{ $region->name }}</p>
    <p><strong>Status:</strong> {{ $region->is_active ? 'Active' : 'Inactive' }}</p>
    <p>North Region</p>
</body>
</html>
