@extends('layouts.office')

@section('title', 'Packages')
@section('page-title', 'Packages Management')

@section('content')
<div class="space-y-6">
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Filters</h3>
        <form method="GET" action="{{ route('office.packages') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tracking Code</label>
                <input type="text" name="tracking_code" value="{{ request('tracking_code') }}" 
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Merchant</label>
                <select name="merchant_id" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Merchants</option>
                    @foreach($merchants as $merchant)
                        <option value="{{ $merchant->id }}" {{ request('merchant_id') == $merchant->id ? 'selected' : '' }}>{{ $merchant->business_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end space-x-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                    <i class="fas fa-search mr-1"></i> Filter
                </button>
                <a href="{{ route('office.packages') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300">
                    <i class="fas fa-times mr-1"></i> Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Packages Table -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">Packages ({{ $packages->total() }})</h3>
            <div class="flex space-x-2">
                <a href="{{ route('office.registered_packages_by_merchant') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm">
                    <i class="fas fa-store mr-1"></i> View by Merchant
                </a>
                <a href="{{ route('office.picked_up_packages') }}" class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 text-sm">
                    <i class="fas fa-box-open mr-1"></i> Picked Up Packages
                </a>
                <button onclick="openBulkAssignModal()" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm">
                    <i class="fas fa-user-plus mr-1"></i> Bulk Assign
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tracking Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Merchant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rider</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($packages as $package)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" class="package-checkbox" value="{{ $package->id }}">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-mono text-blue-600">{{ $package->tracking_code }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $package->merchant ? $package->merchant->business_name : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div>{{ $package->customer_name }}</div>
                            <div class="text-xs text-gray-500">{{ $package->customer_phone }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">
                            {{ $package->delivery_address }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($package->status == 'delivered') bg-green-100 text-green-800
                                @elseif($package->status == 'on_the_way') bg-blue-100 text-blue-800
                                @elseif($package->status == 'ready_for_delivery') bg-teal-100 text-teal-800
                                @elseif($package->status == 'arrived_at_office') bg-blue-100 text-blue-800
                                @elseif($package->status == 'return_to_office') bg-red-100 text-red-800
                                @elseif($package->status == 'returned_to_merchant') bg-purple-100 text-purple-800
                                @elseif($package->status == 'cancelled') bg-gray-100 text-gray-800
                                @elseif($package->status == 'registered') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst(str_replace('_', ' ', $package->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $package->currentRider->name ?? 'Unassigned' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($package->amount, 2) }} {{ $package->payment_type == 'cod' ? '(COD)' : '' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $package->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <button onclick="viewPackage({{ $package->id }})" class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @if($package->status == 'arrived_at_office')
                                    <button onclick="assignPackage({{ $package->id }})" class="text-green-600 hover:text-green-900">
                                        <i class="fas fa-user-plus"></i>
                                    </button>
                                @elseif($package->status == 'return_to_office')
                                    @php
                                        // Get the status before return_to_office from status history
                                        // Sort by created_at descending to get most recent first
                                        $statusHistory = $package->statusHistory->sortByDesc('created_at')->values();
                                        $previousStatus = null;
                                        
                                        // Find the return_to_office entry
                                        $returnToOfficeIndex = null;
                                        foreach ($statusHistory as $index => $history) {
                                            if ($history->status == 'return_to_office') {
                                                $returnToOfficeIndex = $index;
                                                break;
                                            }
                                        }
                                        
                                        // Get the status right before return_to_office
                                        if ($returnToOfficeIndex !== null && isset($statusHistory[$returnToOfficeIndex + 1])) {
                                            $previousStatus = $statusHistory[$returnToOfficeIndex + 1]->status;
                                        }
                                    @endphp
                                    @if($previousStatus == 'cancelled')
                                        <button onclick="returnToMerchant({{ $package->id }})" class="text-purple-600 hover:text-purple-900" title="Return to Merchant">
                                            <i class="fas fa-store"></i>
                                        </button>
                                    @endif
                                    {{-- contact_failed packages are automatically reassigned (status becomes arrived_at_office) --}}
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-6 py-4 text-center text-gray-500">No packages found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t">
            {{ $packages->links() }}
        </div>
    </div>
</div>

<!-- Package Details Modal -->
<div id="packageModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Package Details</h3>
            <button onclick="closePackageModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="packageDetails" class="space-y-4">
            <!-- Content will be loaded via AJAX -->
        </div>
    </div>
</div>

<!-- Assign Package Modal -->
<div id="assignModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Assign Package to Rider</h3>
            <button onclick="closeAssignModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="assignForm" onsubmit="submitAssign(event)">
            <input type="hidden" id="assignPackageId" name="package_id">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Rider</label>
                <select id="assignRiderId" name="rider_id" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <option value="">Choose a rider...</option>
                    @foreach($riders as $rider)
                        <option value="{{ $rider->id }}">{{ $rider->name }} ({{ $rider->status }})</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeAssignModal()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300">
                    Cancel
                </button>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                    Assign
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Assign Modal -->
<div id="bulkAssignModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Bulk Assign Packages</h3>
            <button onclick="closeBulkAssignModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="bulkAssignForm" onsubmit="submitBulkAssign(event)">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Rider</label>
                <select id="bulkAssignRiderId" name="rider_id" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <option value="">Choose a rider...</option>
                    @foreach($riders as $rider)
                        <option value="{{ $rider->id }}">{{ $rider->name }} ({{ $rider->status }})</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <p class="text-sm text-gray-600">Selected packages: <span id="selectedCount">0</span></p>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeBulkAssignModal()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300">
                    Cancel
                </button>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                    Assign Selected
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.package-checkbox');
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const selected = document.querySelectorAll('.package-checkbox:checked');
        document.getElementById('selectedCount').textContent = selected.length;
    }

    document.querySelectorAll('.package-checkbox').forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });

    function viewPackage(id) {
        fetch(`/api/office/packages/${id}`, {
            headers: {
                'Authorization': 'Bearer {{ $apiToken }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            const details = `
                <div class="grid grid-cols-2 gap-4">
                    <div><strong>Tracking Code:</strong> ${data.tracking_code}</div>
                    <div><strong>Status:</strong> ${data.status}</div>
                    <div><strong>Customer:</strong> ${data.customer_name}</div>
                    <div><strong>Phone:</strong> ${data.customer_phone}</div>
                    <div class="col-span-2"><strong>Address:</strong> ${data.delivery_address}</div>
                    <div><strong>Amount:</strong> ${data.amount} (${data.payment_type})</div>
                    <div><strong>Rider:</strong> ${data.current_rider ? data.current_rider.name : 'Unassigned'}</div>
                </div>
            `;
            document.getElementById('packageDetails').innerHTML = details;
            document.getElementById('packageModal').classList.remove('hidden');
        })
        .catch(err => {
            alert('Error loading package details');
            console.error(err);
        });
    }

    function closePackageModal() {
        document.getElementById('packageModal').classList.add('hidden');
    }

    function assignPackage(id) {
        document.getElementById('assignPackageId').value = id;
        document.getElementById('assignModal').classList.remove('hidden');
    }

    function closeAssignModal() {
        document.getElementById('assignModal').classList.add('hidden');
        document.getElementById('assignForm').reset();
    }

    function submitAssign(e) {
        e.preventDefault();
        const packageId = document.getElementById('assignPackageId').value;
        const riderId = document.getElementById('assignRiderId').value;

        fetch(`/api/office/packages/${packageId}/assign`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer {{ $apiToken }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ rider_id: riderId })
        })
        .then(res => {
            if (!res.ok) {
                return res.json().then(err => {
                    throw new Error(err.message || 'Failed to assign package');
                });
            }
            return res.json();
        })
        .then(data => {
            alert('Package assigned successfully');
            location.reload();
        })
        .catch(err => {
            alert('Error assigning package: ' + err.message);
            console.error(err);
        });
    }

    function openBulkAssignModal() {
        const selected = Array.from(document.querySelectorAll('.package-checkbox:checked')).map(cb => cb.value);
        if (selected.length === 0) {
            alert('Please select at least one package');
            return;
        }
        document.getElementById('bulkAssignModal').classList.remove('hidden');
    }

    function closeBulkAssignModal() {
        document.getElementById('bulkAssignModal').classList.add('hidden');
    }

    function submitBulkAssign(e) {
        e.preventDefault();
        const selected = Array.from(document.querySelectorAll('.package-checkbox:checked')).map(cb => cb.value);
        const riderId = document.getElementById('bulkAssignRiderId').value;

        fetch('/api/office/packages/bulk-assign', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer {{ $apiToken }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                package_ids: selected,
                rider_id: riderId
            })
        })
        .then(res => {
            if (!res.ok) {
                return res.json().then(err => {
                    throw new Error(err.message || 'Failed to assign packages');
                });
            }
            return res.json();
        })
        .then(data => {
            alert(`Successfully assigned ${data.assigned_count} packages`);
            location.reload();
        })
        .catch(err => {
            alert('Error assigning packages: ' + err.message);
            console.error(err);
        });
    }
    
    function returnToMerchant(id) {
        if (!confirm('Mark this package as returned to merchant?')) {
            return;
        }

        fetch(`/api/office/packages/${id}/status`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer {{ $apiToken }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                status: 'returned_to_merchant',
                notes: 'Package returned to merchant shop'
            })
        })
        .then(res => {
            if (!res.ok) {
                return res.json().then(err => {
                    throw new Error(err.message || 'Failed to update package status');
                });
            }
            return res.json();
        })
        .then(data => {
            alert('Package marked as returned to merchant successfully!');
            location.reload();
        })
        .catch(err => {
            alert('Error updating status: ' + err.message);
            console.error(err);
        });
    }

    {{-- reassignForNextDay function removed - contact_failed packages are automatically reassigned --}}

    // WebSocket: Listen for package status changes
    if (window.WebSocketHelper && window.WebSocketHelper.isConnected()) {
        const packageChannel = window.WebSocketHelper.connect('office.packages', 'package.status.changed', function(data) {
            // Show notification
            showNotification('Package #' + data.package_id + ' status updated: ' + data.status, 'info');
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
        const main = document.querySelector('main');
        if (main) {
            main.insertBefore(notification, main.firstChild);
            setTimeout(() => notification.remove(), 5000);
        }
    }
</script>
@endpush
@endsection
