@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Test Page</h1>
        <p>This is a test to check if basic components work.</p>
        
        <x-alert type="success">
            Test alert component
        </x-alert>
        
        <x-card title="Test Card">
            Test card content
        </x-card>
    </div>
@endsection
