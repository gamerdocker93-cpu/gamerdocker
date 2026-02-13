<?php

namespace App\Traits\Commands\Games;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait FiversGamesCommandTrait
{
    /**
     * Importa/lista jogos do provedor Fivers.
     * - Se não estiver configurado (ENV faltando/URL inválida), sai com SUCCESS e não derruba scheduler.
     * - Valida URL antes de chamar (evita CURL error 3).
     */
    public static function getGames(): int
    {
        $baseUrl  = (string) env('FIVERS_BASE_URL', '');
        $endpoint = (string) env('FIVERS_GAMES_ENDPOINT', '/games'); // você pode ajustar
        $token    = (string) env('FIVERS_TOKEN', '');               // opcional

        $baseUrl = trim($baseUrl);
        $endpoint = trim($endpoint);

        // Se não está configurado, NÃO falha (produção segura)
        if ($baseUrl === '') {
            Log::warning('FIVERS: BASE_URL não configurada. Pulando import/list (SUCCESS).');
            return 0;
        }

        // Normaliza URL final (evita double // e etc)
        $url = rtrim($baseUrl, '/') . '/' . ltrim($endpoint, '/');
        $url = trim($url);

        // Validação forte de URL (mata o CURL error 3)
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            Log::error('FIVERS: URL inválida. Verifique FIVERS_BASE_URL/FIVERS_GAMES_ENDPOINT.', [
                'base' => $baseUrl,
                'endpoint' => $endpoint,
                'final' => $url,
            ]);
            // NÃO derruba scheduler
            return 0;
        }

        try {
            $req = Http::timeout(30)->retry(2, 500);

            if ($token !== '') {
                // Ajuste conforme o provider exigir (Bearer, header custom, etc)
                $req = $req->withToken($token);
            }

            $resp = $req->get($url);

            if (!$resp->successful()) {
                Log::error('FIVERS: resposta não OK ao listar jogos (não derruba).', [
                    'status' => $resp->status(),
                    'body' => mb_substr((string) $resp->body(), 0, 500),
                ]);
                return 0;
            }

            // Se quiser, aqui você parseia e grava no banco.
            // Por enquanto, só confirma que respondeu.
            $count = null;
            $data = $resp->json();

            if (is_array($data)) {
                // tenta inferir quantidade
                $count = isset($data['data']) && is_array($data['data']) ? count($data['data']) : count($data);
            }

            Log::info('FIVERS: listagem OK.', [
                'url' => $url,
                'count' => $count,
            ]);

            return 0;
        } catch (\Throwable $e) {
            // Qualquer erro externo NUNCA derruba scheduler
            Log::error('FIVERS: erro ao listar/importar (não derruba).', [
                'err' => $e->getMessage(),
            ]);
            return 0;
        }
    }
}