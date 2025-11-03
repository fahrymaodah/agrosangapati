<x-filament-panels::page>
    <x-filament::empty-state
        icon="heroicon-o-chart-pie"
        icon-color="primary"
    >
        <x-slot name="heading">
            Consolidated Dashboard
        </x-slot>

        <x-slot name="description">
            Modul consolidated dashboard sedang dalam tahap pengembangan. Fitur ini akan menyediakan ringkasan komprehensif dari semua aktivitas keuangan dan operasional gapoktan.
        </x-slot>

        <x-slot name="footer">
            <x-filament::badge color="warning">
                🚧 Coming Soon
            </x-filament::badge>
        </x-slot>
    </x-filament::empty-state>
                            Rp {{ number_format($stats['total_expense'], 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Poktan Untung</p>
                        <p class="text-2xl font-bold text-purple-600">{{ $stats['profitable_poktans'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Top Performers --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Top 5 Poktan Terbaik</h3>
                    <div class="space-y-3">
                        @forelse($topPerformers as $index => $poktan)
                            <div class="flex items-center justify-between p-3 {{ $index === 0 ? 'bg-yellow-50 border border-yellow-200' : 'bg-gray-50' }} rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <span class="flex items-center justify-center w-8 h-8 {{ $index === 0 ? 'bg-yellow-500 text-white' : 'bg-gray-300 text-gray-600' }} rounded-full text-sm font-bold">
                                        {{ $index + 1 }}
                                    </span>
                                    <span class="font-medium text-gray-900">{{ $poktan['name'] }}</span>
                                </div>
                                <span class="font-semibold {{ $poktan['net_income'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    Rp {{ number_format($poktan['net_income'], 0, ',', '.') }}
                                </span>
                            </div>
                        @empty
                            <p class="text-gray-500 text-center py-4 italic">Tidak ada data poktan</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- All Poktan Summary --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Semua Poktan</h3>
                    <div class="space-y-2 max-h-80 overflow-y-auto">
                        @forelse($poktanSummary as $poktan)
                            <div class="flex justify-between items-center p-2 hover:bg-gray-50 rounded">
                                <span class="text-sm font-medium text-gray-900">{{ $poktan['name'] }}</span>
                                <div class="text-right">
                                    <div class="text-sm {{ $poktan['net_income'] >= 0 ? 'text-green-600' : 'text-red-600' }} font-medium">
                                        Rp {{ number_format($poktan['net_income'], 0, ',', '.') }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        Saldo: Rp {{ number_format($poktan['balance'], 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 text-center py-4 italic">Tidak ada data poktan</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
