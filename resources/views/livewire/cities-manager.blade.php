<div>
    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-6">
        <h2 class="text-xl font-semibold mb-4">Manage City Subscriptions</h2>

        @if (session()->has('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <div class="mb-6">
            <form wire:submit.prevent="searchCity">
                <div class="flex mb-2">
                    <input wire:model="cityName" type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mr-2" placeholder="Enter city name">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Search
                    </button>
                </div>
                @error('cityName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </form>
        </div>

        @if(count($cities) > 0 && !$selectedCity)
            <div class="mb-6">
                <h3 class="font-semibold mb-2">Select a City</h3>
                <div class="bg-gray-100 p-4 rounded">
                    @foreach($cities as $city)
                        <div class="mb-2">
                            <button wire:click="selectCity({{ $city->id }})" class="bg-gray-200 hover:bg-gray-300 py-2 px-4 rounded w-full text-left">
                                {{ $city->name }}{{ $city->country ? ', ' . $city->country : '' }}
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($selectedCity)
            <div class="mb-6 bg-gray-100 p-4 rounded">
                <h3 class="font-semibold mb-2">Alert Settings for {{ $selectedCity->name }}</h3>

                <div class="mb-4">
                    <label class="flex items-center">
                        <input wire:model="precipitation_enabled" type="checkbox" class="form-checkbox">
                        <span class="ml-2">Receive precipitation alerts</span>
                    </label>
                </div>

                @if($precipitation_enabled)
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="precipitation_threshold">
                            Precipitation Threshold (mm)
                        </label>
                        <input wire:model="precipitation_threshold" type="number" step="0.1" min="0.1" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        @error('precipitation_threshold') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                @endif

                <div class="mb-4">
                    <label class="flex items-center">
                        <input wire:model="uv_enabled" type="checkbox" class="form-checkbox">
                        <span class="ml-2">Receive UV index alerts</span>
                    </label>
                </div>

                @if($uv_enabled)
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="uv_threshold">
                            UV Index Threshold
                        </label>
                        <input wire:model="uv_threshold" type="number" step="0.1" min="1" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        @error('uv_threshold') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                @endif

                <div class="flex justify-between">
                    <button wire:click="subscribeToCity" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Subscribe
                    </button>
                    <button wire:click="$set('selectedCity', null)" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Cancel
                    </button>
                </div>
            </div>
        @endif

        @if(count($userCities) > 0)
            <div>
                <h3 class="font-semibold mb-2">Your City Subscriptions</h3>

                <div class="space-y-4">
                    @foreach($userCities as $city)
                        <div class="bg-gray-100 p-4 rounded">
                            <div class="flex justify-between mb-2">
                                <h4 class="font-bold">{{ $city->name }}</h4>
                                <button wire:click="unsubscribeFromCity({{ $city->id }})" class="text-red-600 hover:text-red-800">
                                    Unsubscribe
                                </button>
                            </div>

                            <div class="text-sm mb-2">
                                <div>
                                    <span class="font-medium">Precipitation alerts:</span>
                                    @if(isset($city->pivot) && $city->pivot)
                                        {{ $city->pivot->precipitation_enabled ? 'Enabled' : 'Disabled' }}
                                        @if($city->pivot->precipitation_enabled)
                                            (Threshold: {{ $city->pivot->precipitation_threshold }}mm)
                                        @endif
                                    @else
                                        <span class="italic text-gray-500">Not configured</span>
                                    @endif
                                </div>
                                <div>
                                    <span class="font-medium">UV index alerts:</span>
                                    @if(isset($city->pivot) && $city->pivot)
                                        {{ $city->pivot->uv_enabled ? 'Enabled' : 'Disabled' }}
                                        @if($city->pivot->uv_enabled)
                                            (Threshold: {{ $city->pivot->uv_threshold }})
                                        @endif
                                    @else
                                        <span class="italic text-gray-500">Not configured</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
