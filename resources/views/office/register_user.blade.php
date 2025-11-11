@extends('layouts.office')

@section('title', 'Register User')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Register New User</h1>
        <p class="text-gray-600 mt-1">Create a new merchant or rider account</p>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="POST" action="{{ route('office.register_user.post') }}" id="registerForm">
            @csrf

            <!-- Role Selection -->
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="role">
                    User Type <span class="text-red-500">*</span>
                </label>
                <select name="role" id="role" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    <option value="">Select User Type</option>
                    <option value="merchant" {{ old('role') == 'merchant' ? 'selected' : '' }}>Merchant</option>
                    <option value="rider" {{ old('role') == 'rider' ? 'selected' : '' }}>Rider</option>
                </select>
                @error('role')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Basic Information -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Basic Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        @error('name')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        @error('email')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">
                            Phone
                        </label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        @error('phone')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="password" id="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        @error('password')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="password_confirmation">
                            Confirm Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                </div>
            </div>

            <!-- Merchant Information (shown when role is merchant) -->
            <div id="merchantFields" class="mb-6 hidden">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Business Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="business_name">
                            Business Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="business_name" id="business_name" value="{{ old('business_name') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        @error('business_name')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="business_email">
                            Business Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="business_email" id="business_email" value="{{ old('business_email') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        @error('business_email')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="business_phone">
                            Business Phone <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="business_phone" id="business_phone" value="{{ old('business_phone') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        @error('business_phone')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="business_address">
                            Business Address <span class="text-red-500">*</span>
                        </label>
                        <textarea name="business_address" id="business_address" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">{{ old('business_address') }}</textarea>
                        @error('business_address')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Rider Information (shown when role is rider) -->
            <div id="riderFields" class="mb-6 hidden">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Vehicle Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="vehicle_type">
                            Vehicle Type <span class="text-red-500">*</span>
                        </label>
                        <select name="vehicle_type" id="vehicle_type" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <option value="">Select Vehicle Type</option>
                            <option value="bike" {{ old('vehicle_type') == 'bike' ? 'selected' : '' }}>Bike</option>
                            <option value="motorcycle" {{ old('vehicle_type') == 'motorcycle' ? 'selected' : '' }}>Motorcycle</option>
                            <option value="car" {{ old('vehicle_type') == 'car' ? 'selected' : '' }}>Car</option>
                            <option value="van" {{ old('vehicle_type') == 'van' ? 'selected' : '' }}>Van</option>
                        </select>
                        @error('vehicle_type')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="vehicle_number">
                            Vehicle Number
                        </label>
                        <input type="text" name="vehicle_number" id="vehicle_number" value="{{ old('vehicle_number') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        @error('vehicle_number')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="license_number">
                            License Number
                        </label>
                        <input type="text" name="license_number" id="license_number" value="{{ old('license_number') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        @error('license_number')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end mt-6">
                <button type="submit" form="registerForm" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    <i class="fas fa-user-plus mr-2"></i>Create Account
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('role');
        const merchantFields = document.getElementById('merchantFields');
        const riderFields = document.getElementById('riderFields');

        function toggleFields() {
            const role = roleSelect.value;
            
            if (role === 'merchant') {
                merchantFields.classList.remove('hidden');
                riderFields.classList.add('hidden');
                // Make merchant fields required
                document.getElementById('business_name').required = true;
                document.getElementById('business_email').required = true;
                document.getElementById('business_phone').required = true;
                document.getElementById('business_address').required = true;
                // Make rider fields not required
                document.getElementById('vehicle_type').required = false;
            } else if (role === 'rider') {
                merchantFields.classList.add('hidden');
                riderFields.classList.remove('hidden');
                // Make rider fields required
                document.getElementById('vehicle_type').required = true;
                // Make merchant fields not required
                document.getElementById('business_name').required = false;
                document.getElementById('business_email').required = false;
                document.getElementById('business_phone').required = false;
                document.getElementById('business_address').required = false;
            } else {
                merchantFields.classList.add('hidden');
                riderFields.classList.add('hidden');
            }
        }

        roleSelect.addEventListener('change', toggleFields);
        
        // Initialize on page load if old role is set
        if (roleSelect.value) {
            toggleFields();
        }
    });
</script>
@endpush
@endsection

