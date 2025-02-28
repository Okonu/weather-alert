<div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-6">
    <h2 class="text-xl font-semibold mb-4">Current Weather</h2>

    <div class="mb-4">
        <label class="block text-gray-700 text-sm font-bold mb-2" for="city">
            Check City
        </label>
        <div class="flex">
            <input wire:model.debounce.500ms="city" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mr-2" id="city" type="text" placeholder="Enter city name">
            <button wire:click="checkWeather" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Check
            </button>
        </div>
    </div>

    @if($loading)
        <div class="text-center py-4">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-500 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Loading...
        </div>
    @elseif($weatherData)
        <div class="bg-gray-100 p-4 rounded">
            <h3 class="font-bold text-lg mb-2">{{ $weatherData['city'] }}</h3>

            @if(isset($weatherData['temperature']))
                <p class="mb-2">Temperature: {{ $weatherData['temperature'] }}°C</p>
            @endif

            @if(isset($weatherData['description']))
                <p class="mb-2">Conditions: {{ ucfirst($weatherData['description']) }}</p>
            @endif

            <div class="flex flex-col space-y-2 mt-4">
                <div class="flex items-center">
                    <span class="w-32">Precipitation:</span>
                    <span class="{{ $weatherData['precipitationWarning'] ? 'text-red-600 font-bold' : '' }}">
                        {{ $weatherData['precipitation'] }}mm
                        @if($weatherData['precipitationWarning'])
                            ⚠️ High
                        @endif
                    </span>
                </div>

                <div class="flex items-center">
                    <span class="w-32">UV Index:</span>
                    <span class="{{ $weatherData['uvWarning'] ? 'text-red-600 font-bold' : '' }}">
                        {{ $weatherData['uvIndex'] }}
                        @if($weatherData['uvWarning'])
                            ⚠️ Harmful
                        @endif
                    </span>
                </div>
            </div>
        </div>
    @else
        <p class="text-gray-600">No weather data available. Try checking a different city.</p>
    @endif
</div>
