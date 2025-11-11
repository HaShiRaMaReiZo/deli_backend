@extends('layouts.office')

@section('title', 'Picked Up Packages')
@section('page-title', 'Picked Up Packages')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Picked Up Packages</h2>
                <p class="text-gray-600 mt-1">Packages picked up by riders, grouped by merchant shop</p>
            </div>
            <a href="{{ route('office.packages') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300">
                <i class="fas fa-arrow-left mr-1"></i> Back to Packages
            </a>
        </div>
    </div>

    <!-- Merchants List -->
    @forelse($merchants as $merchant)
        @php
            $merchantPackages = $packagesByMerchant->get($merchant->id, collect());
            $packageCount = $merchantPackages->count();
        @endphp
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <!-- Merchant Header -->
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 p-6 text-white">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <h3 class="text-xl font-bold mb-2">{{ $merchant->business_name }}</h3>
                        <div class="space-y-1 text-purple-100">
                            <div class="flex items-center">
                                <i class="fas fa-map-marker-alt mr-2"></i>
                                <span>{{ $merchant->business_address }}</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-phone mr-2"></i>
                                <span>{{ $merchant->business_phone }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="bg-white bg-opacity-20 rounded-lg px-4 py-2">
                            <div class="text-sm text-purple-100">Total Packages</div>
                            <div class="text-3xl font-bold">{{ $packageCount }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Packages List -->
            <div class="p-6">
                <div class="mb-4 flex justify-between items-center">
                    <h4 class="text-lg font-semibold text-gray-800">Packages ({{ $packageCount }})</h4>
                    @php
                        $pickedUpCount = $merchantPackages->where('status', 'picked_up')->count();
                    @endphp
                    @if($pickedUpCount > 0)
                        <button 
                            onclick="markAllArrived({{ $merchant->id }})" 
                            class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 text-sm">
                            <i class="fas fa-check-circle mr-1"></i> Mark All Picked Up as Arrived ({{ $pickedUpCount }})
                        </button>
                    @endif
                </div>

                @if($packageCount > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <input type="checkbox" class="merchant-checkbox-{{ $merchant->id }}" onchange="toggleMerchantPackages({{ $merchant->id }})">
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tracking Code</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($merchantPackages as $package)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <input type="checkbox" class="package-checkbox-{{ $merchant->id }}" value="{{ $package->id }}" data-merchant="{{ $merchant->id }}">
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="font-mono text-blue-600">{{ $package->tracking_code }}</span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                        <div>{{ $package->customer_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $package->customer_phone }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 max-w-xs truncate">
                                        {{ $package->delivery_address }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($package->status == 'arrived_at_office') bg-green-100 text-green-800
                                            @elseif($package->status == 'picked_up') bg-purple-100 text-purple-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ ucfirst(str_replace('_', ' ', $package->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($package->amount, 2) }} {{ $package->payment_type == 'cod' ? '(COD)' : '' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                        {{ $package->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                                        @if($package->status == 'picked_up')
                                            <button 
                                                onclick="markArrived({{ $package->id }})" 
                                                class="text-purple-600 hover:text-purple-900">
                                                <i class="fas fa-check-circle mr-1"></i> Mark Arrived
                                            </button>
                                        @else
                                            <span class="text-green-600">
                                                <i class="fas fa-check-circle mr-1"></i> Arrived
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @php
                        $pickedUpCount = $merchantPackages->where('status', 'picked_up')->count();
                    @endphp
                    @if($pickedUpCount > 0)
                        <div class="mt-4 flex justify-end">
                            <button 
                                onclick="markSelectedArrived({{ $merchant->id }})" 
                                class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 text-sm">
                                <i class="fas fa-check-circle mr-1"></i> Mark Selected as Arrived
                            </button>
                        </div>
                    @endif
                @else
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-2"></i>
                        <p>No picked up packages for this merchant</p>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="bg-white rounded-lg shadow p-12 text-center">
            <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">No Picked Up Packages</h3>
            <p class="text-gray-500">There are no picked up packages to display.</p>
        </div>
    @endforelse
</div>

@push('scripts')
<script>
    function toggleMerchantPackages(merchantId) {
        const selectAll = document.querySelector(`.merchant-checkbox-${merchantId}`);
        const checkboxes = document.querySelectorAll(`.package-checkbox-${merchantId}`);
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
    }

    function markArrived(packageId) {
        if (!confirm('Mark this package as arrived at office?')) {
            return;
        }

        fetch(`/api/office/packages/${packageId}/status`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer {{ $apiToken }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                status: 'arrived_at_office',
                notes: 'Marked as arrived at office'
            })
        })
        .then(res => {
            if (!res.ok) {
                return res.json().then(err => {
                    throw new Error(err.message || 'Failed to update status');
                });
            }
            return res.json();
        })
        .then(data => {
            alert('Package marked as arrived at office successfully!');
            location.reload();
        })
        .catch(err => {
            alert('Error updating status: ' + err.message);
            console.error(err);
        });
    }

    function markSelectedArrived(merchantId) {
        const selected = Array.from(document.querySelectorAll(`.package-checkbox-${merchantId}:checked`)).map(cb => cb.value);
        if (selected.length === 0) {
            alert('Please select at least one package');
            return;
        }

        // Filter only picked_up packages
        const pickedUpPackages = selected.filter(id => {
            const row = document.querySelector(`input[value="${id}"][data-merchant="${merchantId}"]`).closest('tr');
            const statusBadge = row.querySelector('td:nth-child(5) span');
            return statusBadge && statusBadge.textContent.trim().includes('Picked Up');
        });

        if (pickedUpPackages.length === 0) {
            alert('Please select packages with status "Picked Up"');
            return;
        }

        if (!confirm(`Mark ${pickedUpPackages.length} picked up package(s) as arrived at office?`)) {
            return;
        }

        // Mark each package
        let completed = 0;
        let failed = 0;

        pickedUpPackages.forEach(packageId => {
            fetch(`/api/office/packages/${packageId}/status`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer {{ $apiToken }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    status: 'arrived_at_office',
                    notes: 'Marked as arrived at office'
                })
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error('Failed');
                }
                completed++;
                if (completed + failed === pickedUpPackages.length) {
                    if (failed > 0) {
                        alert(`${completed} package(s) marked successfully, ${failed} failed`);
                    } else {
                        alert(`${completed} package(s) marked as arrived at office successfully!`);
                    }
                    location.reload();
                }
            })
            .catch(err => {
                failed++;
                if (completed + failed === pickedUpPackages.length) {
                    if (failed > 0) {
                        alert(`${completed} package(s) marked successfully, ${failed} failed`);
                    } else {
                        alert('All packages marked as arrived at office successfully!');
                    }
                    location.reload();
                }
            });
        });
    }

    function markAllArrived(merchantId) {
        // Get only picked_up packages
        const rows = document.querySelectorAll(`.package-checkbox-${merchantId}[data-merchant="${merchantId}"]`);
        const pickedUpPackageIds = [];
        
        rows.forEach(checkbox => {
            const row = checkbox.closest('tr');
            const statusBadge = row.querySelector('td:nth-child(5) span');
            if (statusBadge && statusBadge.textContent.trim().includes('Picked Up')) {
                pickedUpPackageIds.push(checkbox.value);
            }
        });
        
        if (pickedUpPackageIds.length === 0) {
            alert('No picked up packages to mark as arrived');
            return;
        }

        if (!confirm(`Mark all ${pickedUpPackageIds.length} picked up package(s) from this merchant as arrived at office?`)) {
            return;
        }

        // Mark each package
        let completed = 0;
        let failed = 0;

        pickedUpPackageIds.forEach(packageId => {
            fetch(`/api/office/packages/${packageId}/status`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer {{ $apiToken }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    status: 'arrived_at_office',
                    notes: 'Marked as arrived at office'
                })
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error('Failed');
                }
                completed++;
                if (completed + failed === pickedUpPackageIds.length) {
                    if (failed > 0) {
                        alert(`${completed} package(s) marked successfully, ${failed} failed`);
                    } else {
                        alert(`All ${completed} package(s) marked as arrived at office successfully!`);
                    }
                    location.reload();
                }
            })
            .catch(err => {
                failed++;
                if (completed + failed === pickedUpPackageIds.length) {
                    if (failed > 0) {
                        alert(`${completed} package(s) marked successfully, ${failed} failed`);
                    } else {
                        alert('All packages marked as arrived at office successfully!');
                    }
                    location.reload();
                }
            });
        });
    }
</script>
@endpush

@endsection

