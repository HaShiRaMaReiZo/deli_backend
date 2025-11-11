@extends('layouts.office')

@section('title', 'Riders')
@section('page-title', 'Riders Management')

@section('content')
<div class="space-y-6">
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Filters</h3>
        <form method="GET" action="{{ route('office.riders') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Statuses</option>
                    <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                    <option value="busy" {{ request('status') == 'busy' ? 'selected' : '' }}>Busy</option>
                    <option value="offline" {{ request('status') == 'offline' ? 'selected' : '' }}>Offline</option>
                </select>
            </div>
            <div class="flex items-end space-x-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                    <i class="fas fa-search mr-1"></i> Filter
                </button>
                <a href="{{ route('office.riders') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300">
                    <i class="fas fa-times mr-1"></i> Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Riders Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($riders as $rider)
        <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-motorcycle text-blue-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">{{ $rider->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $rider->phone }}</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                        @if($rider->status == 'available') bg-green-100 text-green-800
                        @elseif($rider->status == 'busy') bg-yellow-100 text-yellow-800
                        @else bg-gray-100 text-gray-800
                        @endif">
                        {{ ucfirst($rider->status) }}
                    </span>
                </div>

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Vehicle:</span>
                        <span class="text-gray-900 font-medium">{{ ucfirst($rider->vehicle_type) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Vehicle Number:</span>
                        <span class="text-gray-900 font-medium">{{ $rider->vehicle_number ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Active Packages:</span>
                        <span class="text-gray-900 font-medium">{{ $rider->package_count ?? 0 }}</span>
                    </div>
                    @if($rider->current_latitude && $rider->current_longitude)
                    <div class="flex justify-between">
                        <span class="text-gray-600">Last Location:</span>
                        <span class="text-gray-900 font-medium text-xs">
                            {{ $rider->last_location_update ? \Carbon\Carbon::parse($rider->last_location_update)->diffForHumans() : 'N/A' }}
                        </span>
                    </div>
                    @endif
                </div>

                <div class="mt-4 pt-4 border-t flex space-x-2">
                    <button onclick="viewRider({{ $rider->id }})" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm">
                        <i class="fas fa-eye mr-1"></i> View Details
                    </button>
                    @if($rider->current_latitude && $rider->current_longitude)
                    <a href="{{ route('office.map') }}?rider_id={{ $rider->id }}" class="flex-1 bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm text-center">
                        <i class="fas fa-map-marker-alt mr-1"></i> Track
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12 text-gray-500">
            <i class="fas fa-motorcycle text-4xl mb-4"></i>
            <p>No riders found</p>
        </div>
        @endforelse
    </div>
</div>

<!-- Rider Details Modal -->
<div id="riderModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Rider Details</h3>
            <button onclick="closeRiderModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="riderDetails" class="space-y-4">
            <!-- Content will be loaded via AJAX -->
        </div>
    </div>
</div>

@push('scripts')
<script>
    function viewRider(id) {
        fetch(`/api/office/riders/${id}`, {
            headers: {
                'Authorization': 'Bearer {{ $apiToken }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            const details = `
                <div class="grid grid-cols-2 gap-4">
                    <div><strong>Name:</strong> ${data.name}</div>
                    <div><strong>Phone:</strong> ${data.phone}</div>
                    <div><strong>Email:</strong> ${data.user ? data.user.email : 'N/A'}</div>
                    <div><strong>Status:</strong> ${data.status}</div>
                    <div><strong>Vehicle Type:</strong> ${data.vehicle_type}</div>
                    <div><strong>Vehicle Number:</strong> ${data.vehicle_number || 'N/A'}</div>
                    <div><strong>License Number:</strong> ${data.license_number || 'N/A'}</div>
                    <div><strong>Active Packages:</strong> ${data.packages ? data.packages.length : 0}</div>
                    ${data.current_latitude ? `
                        <div class="col-span-2"><strong>Current Location:</strong> ${data.current_latitude}, ${data.current_longitude}</div>
                        <div class="col-span-2"><strong>Last Update:</strong> ${data.last_location_update || 'N/A'}</div>
                    ` : ''}
                </div>
                ${data.packages && data.packages.length > 0 ? `
                    <div class="mt-4">
                        <h4 class="font-semibold mb-2">Active Packages:</h4>
                        <ul class="list-disc list-inside space-y-1">
                            ${data.packages.map(p => `<li>${p.tracking_code} - ${p.status}</li>`).join('')}
                        </ul>
                    </div>
                ` : ''}
            `;
            document.getElementById('riderDetails').innerHTML = details;
            document.getElementById('riderModal').classList.remove('hidden');
        })
        .catch(err => {
            alert('Error loading rider details');
            console.error(err);
        });
    }

    function closeRiderModal() {
        document.getElementById('riderModal').classList.add('hidden');
    }
</script>
@endpush
@endsection
