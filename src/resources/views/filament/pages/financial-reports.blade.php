<x-filament-panels::page>
    <div class="space-y-6" x-data="{ activeTab: 'transactions' }">
        {{-- Page Header --}}
        <x-filament::section>
            <x-slot name="heading">
                📊 Financial Reports
            </x-slot>
            <x-slot name="description">
                Sistem pelaporan keuangan komprehensif untuk Gapoktan
            </x-slot>
        </x-filament::section>

        {{-- Tabs Navigation --}}
        <x-filament::tabs>
            <x-filament::tabs.item
                alpine-active="activeTab === 'transactions'"
                x-on:click="activeTab = 'transactions'"
                icon="heroicon-m-list-bullet"
            >
                Daftar Transaksi
            </x-filament::tabs.item>

            <x-filament::tabs.item
                alpine-active="activeTab === 'income-statement'"
                x-on:click="activeTab = 'income-statement'"
                icon="heroicon-m-chart-bar"
            >
                Laporan Laba Rugi
            </x-filament::tabs.item>

            <x-filament::tabs.item
                alpine-active="activeTab === 'cash-flow'"
                x-on:click="activeTab = 'cash-flow'"
                icon="heroicon-m-banknotes"
            >
                Arus Kas
            </x-filament::tabs.item>

            <x-filament::tabs.item
                alpine-active="activeTab === 'category-summary'"
                x-on:click="activeTab = 'category-summary'"
                icon="heroicon-m-chart-pie"
            >
                Ringkasan Kategori
            </x-filament::tabs.item>
        </x-filament::tabs>

        {{-- Tab Contents --}}
        <div x-show="activeTab === 'transactions'" x-transition>
            <x-filament::section>
                <x-slot name="heading">
                    � Daftar Transaksi
                </x-slot>
                
                <x-filament::empty-state
                    icon="heroicon-o-list-bullet"
                    icon-color="info"
                >
                    <x-slot name="heading">
                        Fitur Transaksi
                    </x-slot>
                    <x-slot name="description">
                        Daftar lengkap semua transaksi dengan filter advanced, pencarian, dan export data. Sedang dalam pengembangan.
                    </x-slot>
                    <x-slot name="footer">
                        <x-filament::badge color="warning">🚧 Coming Soon</x-filament::badge>
                    </x-slot>
                </x-filament::empty-state>
            </x-filament::section>
        </div>

        <div x-show="activeTab === 'income-statement'" x-transition>
            <x-filament::section>
                <x-slot name="heading">
                    📈 Laporan Laba Rugi
                </x-slot>
                
                <x-filament::empty-state
                    icon="heroicon-o-chart-bar"
                    icon-color="success"
                >
                    <x-slot name="heading">
                        Analisis Laba Rugi
                    </x-slot>
                    <x-slot name="description">
                        Laporan komprehensif pendapatan dan pengeluaran dengan perbandingan periode, grafik trend, dan export PDF/Excel.
                    </x-slot>
                    <x-slot name="footer">
                        <x-filament::badge color="warning">🚧 Coming Soon</x-filament::badge>
                    </x-slot>
                </x-filament::empty-state>
            </x-filament::section>
        </div>

        <div x-show="activeTab === 'cash-flow'" x-transition>
            <x-filament::section>
                <x-slot name="heading">
                    💰 Analisis Arus Kas
                </x-slot>
                
                <x-filament::empty-state
                    icon="heroicon-o-banknotes"
                    icon-color="warning"
                >
                    <x-slot name="heading">
                        Cash Flow Analysis
                    </x-slot>
                    <x-slot name="description">
                        Monitoring arus kas harian/bulanan, prediksi cash flow masa depan, dan visualisasi grafik yang interaktif.
                    </x-slot>
                    <x-slot name="footer">
                        <x-filament::badge color="warning">🚧 Coming Soon</x-filament::badge>
                    </x-slot>
                </x-filament::empty-state>
            </x-filament::section>
        </div>

        <div x-show="activeTab === 'category-summary'" x-transition>
            <x-filament::section>
                <x-slot name="heading">
                    🎯 Ringkasan Kategori
                </x-slot>
                
                <x-filament::empty-state
                    icon="heroicon-o-chart-pie"
                    icon-color="primary"
                >
                    <x-slot name="heading">
                        Category Breakdown
                    </x-slot>
                    <x-slot name="description">
                        Analisis mendalam per kategori transaksi, persentase kontribusi, dan trend analysis untuk optimasi keuangan.
                    </x-slot>
                    <x-slot name="footer">
                        <x-filament::badge color="warning">🚧 Coming Soon</x-filament::badge>
                    </x-slot>
                </x-filament::empty-state>
            </x-filament::section>
        </div>
    </div>
</x-filament-panels::page>