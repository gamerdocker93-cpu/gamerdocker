<x-filament-panels::page>
    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-bold tracking-tight">Auto Saque</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Ative ou desative o saque automático (jogadores e afiliados).
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 dark:border-gray-800 p-6 bg-white/50 dark:bg-gray-900/30">
            {{ $this->form }}
        </div>
    </div>
</x-filament-panels::page>