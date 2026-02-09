<x-filament::page>

    <div class="space-y-6">

        {{-- Título --}}
        <div>
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">
                Configuração de Saque Automático
            </h2>

            <p class="text-sm text-gray-500 dark:text-gray-400">
                Gerencie os pagamentos automáticos para jogadores e afiliados.
            </p>
        </div>

        {{-- Form --}}
        <x-filament::form wire:submit="submit">

            {{ $this->form }}

            {{-- Botão --}}
            <div class="pt-4">
                <x-filament::button
                    type="submit"
                    color="primary"
                >
                    Salvar Configurações
                </x-filament::button>
            </div>

        </x-filament::form>

        {{-- Aviso --}}
        <div class="rounded-lg border border-yellow-300 bg-yellow-50 p-4 text-yellow-800 dark:border-yellow-600 dark:bg-yellow-900 dark:text-yellow-200">

            <strong>Atenção:</strong>

            <ul class="mt-2 list-disc list-inside text-sm">
                <li>O saque automático só funciona se o gateway estiver configurado corretamente.</li>
                <li>Tenha saldo disponível nos provedores.</li>
                <li>Erros são registrados no log do sistema.</li>
                <li>Recomendado testar primeiro em ambiente de teste.</li>
            </ul>

        </div>

    </div>

</x-filament::page>
