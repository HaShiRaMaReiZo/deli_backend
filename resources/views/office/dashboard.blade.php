@extends('layouts.office')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
@if(isset($error))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline">{{ $error }}</span>
    </div>
@endif
<div class="space-y-6">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Total Packages</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stats['total_packages'] }}</p>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <i class="fas fa-box text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Pending Packages</p>
                    <p class="text-3xl font-bold text-orange-600">{{ $stats['pending_packages'] }}</p>
                </div>
                <div class="bg-orange-100 rounded-full p-3">
                    <i class="fas fa-clock text-orange-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">In Transit</p>
                    <p class="text-3xl font-bold text-blue-600">{{ $stats['in_transit'] }}</p>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <i class="fas fa-truck text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Delivered Today</p>
                    <p class="text-3xl font-bold text-green-600">{{ $stats['delivered_today'] }}</p>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Active Riders</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['active_riders'] }} / {{ $stats['total_riders'] }}</p>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <i class="fas fa-motorcycle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Total Riders</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['total_riders'] }}</p>
                </div>
                <div class="bg-gray-100 rounded-full p-3">
                    <i class="fas fa-users text-gray-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Total Merchants</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['total_merchants'] }}</p>
                </div>
                <div class="bg-purple-100 rounded-full p-3">
                    <i class="fas fa-store text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Packages -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Recent Packages</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tracking Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Merchant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rider</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentPackages as $package)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ route('office.packages') }}?tracking_code={{ $package->tracking_code }}" class="text-blue-600 hover:underline font-mono">
                                {{ $package->tracking_code }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $package->merchant ? $package->merchant->business_name : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $package->customer_name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($package->status == 'delivered') bg-green-100 text-green-800
                                @elseif($package->status == 'on_the_way') bg-blue-100 text-blue-800
                                @elseif($package->status == 'registered') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst(str_replace('_', ' ', $package->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $package->currentRider ? $package->currentRider->name : 'Unassigned' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $package->created_at->format('M d, Y') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No packages found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t">
            <a href="{{ route('office.packages') }}" class="text-blue-600 hover:underline">View all packages →</a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // WebSocket: Listen for package status changes
    if (window.WebSocketHelper && window.WebSocketHelper.isConnected()) {
        const packageChannel = window.WebSocketHelper.connect('office.packages', 'package.status.changed', function(data) {
            // Show notification
            showNotification('Package status updated: ' + data.status, 'info');
            // Reload page after 2 seconds to show updated data
            setTimeout(() => {
                location.reload();
            }, 2000);
        });
        
        // Clean up on page unload
        window.addEventListener('beforeunload', () => {
            if (packageChannel) {
                window.WebSocketHelper.disconnect(packageChannel);
            }
        });
    }
    
    function showNotification(message, type) {
        const bgColor = type === 'info' ? 'bg-blue-100 border-blue-400 text-blue-700' : 'bg-green-100 border-green-400 text-green-700';
        const notification = document.createElement('div');
        notification.className = `mb-4 border px-4 py-3 rounded relative ${bgColor}`;
        notification.innerHTML = `<span>${message}</span>`;
        document.querySelector('main').insertBefore(notification, document.querySelector('main').firstChild);
        setTimeout(() => notification.remove(), 5000);
    }
</script>
@endpush
@endsection
