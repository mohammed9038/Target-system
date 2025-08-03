<!DOCTYPE html>
<html>
<head>
    <title>Logout Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h1>Logout Test</h1>
    
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit">Test Logout</button>
    </form>
    
    <p>CSRF Token: {{ csrf_token() }}</p>
    <p>Session ID: {{ session()->getId() }}</p>
    <p>Auth Check: {{ Auth::check() ? 'Logged In' : 'Not Logged In' }}</p>
    
    @if(Auth::check())
        <p>User: {{ Auth::user()->username }}</p>
    @endif
</body>
</html>
