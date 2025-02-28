<x-guest-layout>
    <div class="pt-4 bg-gray-100">
        <div class="min-h-screen flex flex-col items-center pt-6 sm:pt-0">
            <div>
                <x-authentication-card-logo />
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                <div class="text-center">
                    <h1 class="text-3xl font-bold mb-4">Weather Alert Service</h1>

                    <p class="mb-6">Get notified about harmful weather conditions including high precipitation and dangerous UV levels in your cities of interest.</p>

                    <div class="flex justify-center space-x-4">
                        <a href="{{ route('login') }}" class="px-6 py-3 bg-blue-600 text-white font-bold rounded text-lg">
                            Login
                        </a>

                        <a href="{{ route('register') }}" class="px-6 py-3 bg-gray-800 text-white font-bold rounded text-lg">
                            Register
                        </a>
                    </div>
                </div>

                <div class="mt-8">
                    <h2 class="text-xl font-semibold mb-4">Features:</h2>

                    <ul class="list-disc list-inside space-y-2">
                        <li>Monitor multiple cities</li>
                        <li>Customize alert thresholds for each city</li>
                        <li>Real-time weather data</li>
                        <li>Email notifications for dangerous conditions</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
