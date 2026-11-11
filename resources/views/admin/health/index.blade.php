@extends('admin.layout')

@section('title', 'System Health')
@section('header', 'System Health')

@section('content')
<div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
    <h3 class="text-xl font-semibold text-white mb-4">Environment</h3>
    <div class="space-y-2 text-gray-300">
        <div>App Env: {{ $health['app'] }}</div>
        <div>Queue Driver: {{ $health['queue'] }}</div>
        <div>Cache Driver: {{ $health['cache'] }}</div>
        <div>Database: {{ $health['db'] }}</div>
    </div>
</div>
@endsection

