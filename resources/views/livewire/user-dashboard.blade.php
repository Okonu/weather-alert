<div>
    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Your Cities Weather</h2>
            <button wire:click="refreshWeather" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline -mt-1 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                </svg>
                Refresh
            </button>
        </div>

        @if($loading)
            <div class="text-center py-10">
                <svg class="animate-spin h-10 w-10 text-blue-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="mt-3 text-gray-600">Loading weather data...</p>
            </div>
        @elseif(count($citiesWeather) === 0)
            <div class="text-center py-10 bg-gray-100 rounded">
                <p class="text-gray-700 mb-4">You haven't subscribed to any cities yet.</p>
                <a href="{{ route('cities.manage') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Add Cities
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($citiesWeather as $weather)
                    <div class="bg-gray-100 p-4 rounded">
                        <h3 class="font-bold text-lg mb-2">{{ $weather['name'] }}</h3>

                        @if(isset($weather['temperature']))
                            <p class="mb-2">Temperature: {{ $weather['temperature'] }}°C</p>
                        @endif

                        @if(isset($weather['description']))
                            <p class="mb-2">Conditions: {{ ucfirst($weather['description']) }}</p>
                        @endif

                        <div class="flex flex-col space-y-2 mt-4">
                            <div class="flex items-center">
                                <span class="w-32">Precipitation:</span>
                                <span class="{{ $weather['precipitationWarning'] ? 'text-red-600 font-bold' : '' }}">
                                    {{ $weather['precipitation'] }}mm
                                    @if($weather['precipitationWarning'])
                                        ⚠️ High
                                    @endif
                                </span>
                            </div>

                            <div class="flex items-center">
                                <span class="w-32">UV Index:</span>
                                <span class="{{ $weather['uvWarning'] ? 'text-red-600 font-bold' : '' }}">
                                    {{ $weather['uvIndex'] }}
                                    @if($weather['uvWarning'])
                                        ⚠️ Harmful
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
