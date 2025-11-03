<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header Section --}}
        <x-filament::section>
            <x-slot name="heading">
                📊 Financial Reports
            </x-slot>
            <x-slot name="description">
                Sistem pelaporan keuangan komprehensif untuk Gapoktan
            </x-slot>
            
            <div class="text-center py-8">
                <div class="mx-auto w-24 h-24 bg-gradient-to-br from-green-100 to-blue-100 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                
                <x-filament::badge color="warning" size="lg">
                    🚧 Coming Soon - Sedang Dikembangkan
                </x-filament::badge>
                
                <p class="text-gray-600 mt-4 max-w-2xl mx-auto">
                    Modul pelaporan keuangan canggih sedang dalam tahap pengembangan. 
                    Sistem ini akan menyediakan analisis mendalam untuk semua aspek keuangan Gapoktan.
                </p>
            </div>
        </x-filament::section>

        {{-- Features Preview --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-filament::section>
                <x-slot name="heading">
                    📈 Laporan Laba Rugi
                </x-slot>
                
                <div class="space-y-3">
                    <div class="flex items-center text-sm text-gray-600">
                        <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Analisis pendapatan dan pengeluaran
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Perbandingan periode
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Export ke PDF/Excel
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">
                    💰 Analisis Arus Kas
                </x-slot>
                
                <div class="space-y-3">
                    <div class="flex items-center text-sm text-gray-600">
                        <svg class="w-4 h-4 mr-2 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Cash flow harian/bulanan
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <svg class="w-4 h-4 mr-2 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Prediksi arus kas
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <svg class="w-4 h-4 mr-2 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Visualisasi grafik
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">
                    📋 Daftar Transaksi
                </x-slot>
                
                <div class="space-y-3">
                    <div class="flex items-center text-sm text-gray-600">
                        <svg class="w-4 h-4 mr-2 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Filter advanced & search
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <svg class="w-4 h-4 mr-2 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Detail per poktan
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <svg class="w-4 h-4 mr-2 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Audit trail lengkap
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">
                    🎯 Ringkasan Kategori
                </x-slot>
                
                <div class="space-y-3">
                    <div class="flex items-center text-sm text-gray-600">
                        <svg class="w-4 h-4 mr-2 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Breakdown per kategori
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <svg class="w-4 h-4 mr-2 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Persentase kontribusi
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <svg class="w-4 h-4 mr-2 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Trend analysis
                    </div>
                </div>
            </x-filament::section>
        </div>

        {{-- CTA Section --}}
        <x-filament::section>
            <div class="text-center py-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                    🚀 Fitur Canggih Segera Hadir
                </h3>
                <p class="text-gray-600 mb-4">
                    Tim development sedang bekerja keras untuk menghadirkan sistem pelaporan keuangan terdepan
                </p>
                <div class="flex justify-center space-x-2">
                    <x-filament::badge color="info">Real-time Updates</x-filament::badge>
                    <x-filament::badge color="success">Multi-format Export</x-filament::badge>
                    <x-filament::badge color="warning">Advanced Analytics</x-filament::badge>
                </div>
            </div>
        </x-filament::section>
    </div>
            </div>

            <div class="p-6">
                @if($activeTab === 'transactions')
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900">Daftar Transaksi</h3>
                        <p class="text-sm text-gray-600">Data lengkap semua transaksi dengan filter dan pencarian</p>
                        
                        {{ $this->table }}
                    </div>
                
                @elseif($activeTab === 'income-statement')
                    @php $incomeStatement = $this->getIncomeStatement(); @endphp
                    <div class="space-y-6">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-medium text-gray-900">Laporan Laba Rugi</h3>
                            <span class="text-sm text-gray-600">{{ $incomeStatement['period'] }}</span>
                        </div>
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            {{-- Income Section --}}
                            <div class="bg-green-50 rounded-lg p-4">
                                <h4 class="font-medium text-green-900 mb-4">PEMASUKAN</h4>
                                <div class="space-y-2">
                                    @forelse($incomeStatement['income'] as $item)
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-green-800">{{ $item['category'] }}</span>
                                            <span class="text-sm font-semibold text-green-900">
                                                Rp {{ number_format($item['amount'], 0, ',', '.') }}
                                            </span>
                                        </div>
                                    @empty
                                        <p class="text-sm text-green-700 italic">Tidak ada pemasukan periode ini</p>
                                    @endforelse
                                </div>
                                <div class="border-t border-green-200 mt-3 pt-3">
                                    <div class="flex justify-between items-center font-semibold">
                                        <span class="text-green-900">Total Pemasukan</span>
                                        <span class="text-green-900">
                                            Rp {{ number_format($incomeStatement['total_income'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Expense Section --}}
                            <div class="bg-red-50 rounded-lg p-4">
                                <h4 class="font-medium text-red-900 mb-4">PENGELUARAN</h4>
                                <div class="space-y-2">
                                    @forelse($incomeStatement['expense'] as $item)
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-red-800">{{ $item['category'] }}</span>
                                            <span class="text-sm font-semibold text-red-900">
                                                Rp {{ number_format($item['amount'], 0, ',', '.') }}
                                            </span>
                                        </div>
                                    @empty
                                        <p class="text-sm text-red-700 italic">Tidak ada pengeluaran periode ini</p>
                                    @endforelse
                                </div>
                                <div class="border-t border-red-200 mt-3 pt-3">
                                    <div class="flex justify-between items-center font-semibold">
                                        <span class="text-red-900">Total Pengeluaran</span>
                                        <span class="text-red-900">
                                            Rp {{ number_format($incomeStatement['total_expense'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Net Income --}}
                        <div class="bg-gray-100 rounded-lg p-4">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-semibold text-gray-900">LABA/RUGI BERSIH</span>
                                <span class="text-xl font-bold {{ $incomeStatement['net_income'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    Rp {{ number_format($incomeStatement['net_income'], 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                
                @elseif($activeTab === 'category-summary')
                    @php $categorySummary = $this->getCategorySummary(); @endphp
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900">Ringkasan per Kategori</h3>
                        <div class="space-y-3">
                            @forelse($categorySummary as $category)
                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <span class="font-medium text-gray-900">{{ $category['name'] }}</span>
                                        <span class="px-2 py-1 text-xs rounded-full {{ $category['type'] === 'income' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $category['type'] === 'income' ? 'Pemasukan' : 'Pengeluaran' }}
                                        </span>
                                    </div>
                                    <span class="font-semibold {{ $category['type'] === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                        Rp {{ number_format($category['transactions_sum_amount'], 0, ',', '.') }}
                                    </span>
                                </div>
                            @empty
                                <p class="text-gray-500 text-center py-8 italic">Tidak ada data kategori bulan ini</p>
                            @endforelse
                        </div>
                    </div>
                
                @else
                    <div class="text-center py-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Arus Kas</h3>
                        <p class="text-gray-500">Fitur arus kas akan segera hadir</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
