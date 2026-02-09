<x-filament-panels::page>
    <div class="space-y-6">
        <div>
            <h2 class="text-xl font-bold">Auto Saque</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Ative ou desative o saque automático (jogadores e afiliados).
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 dark:border-gray-800 p-6 space-y-4">
            <label class="flex items-center justify-between gap-4">
                <div>
                    <div class="font-semibold">Auto Saque Ativo</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Quando ativo, o sistema tenta processar saques automaticamente.
                    </div>
                </div>

                <input
                    type="checkbox"
                    class="h-6 w-6"
                    wire:model="enabled"
                />
            </label>

            <div>
                <x-filament::button wire:click="save">
                    Salvar
                </x-filament::button>
            </div>
        </div>
    </div>
</x-filament-panels::page>