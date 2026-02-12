<?php

namespace App\Http\Controllers\Api\Games;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameFavorite;
use App\Models\GameLike;
use App\Models\GamesKey;
use App\Models\Gateway;
use App\Models\Provider;
use App\Models\Wallet;
use App\Traits\Providers\EvergameTrait;
use App\Traits\Providers\FiversTrait;
use App\Traits\Providers\Games2ApiTrait;
use App\Traits\Providers\PlayGamingTrait;
use App\Traits\Providers\SalsaGamesTrait;
use App\Traits\Providers\VenixCGTrait;
use App\Traits\Providers\VeniXTrait;
use App\Traits\Providers\VibraTrait;
use App\Traits\Providers\WorldSlotTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class GameController extends Controller
{
    use FiversTrait,
        VibraTrait,
        SalsaGamesTrait,
        WorldSlotTrait,
        Games2ApiTrait,
        VeniXTrait,
        EvergameTrait,
        PlayGamingTrait,
        VenixCGTrait;

    /**
     * CACHE TTL (segundos)
     * Seguro: 60s (pode aumentar depois)
     */
    private int $cacheTtl = 60;

    /**
     * Cache com tags (se suportar). Se não suportar, fallback sem tags.
     */
    private function cacheRemember(string $key, int $ttl, \Closure $callback)
    {
        try {
            return Cache::tags(['games'])->remember($key, $ttl, $callback);
        } catch (\Throwable $e) {
            return Cache::remember($key, $ttl, $callback);
        }
    }

    /**
     * Monta chave de cache estável e curta.
     */
    private function makeCacheKey(string $prefix, array $parts): string
    {
        $normalized = [];
        foreach ($parts as $k => $v) {
            if (is_string($v)) {
                $v = trim(mb_strtolower($v));
            }
            $normalized[] = $k . '=' . (string) $v;
        }
        return $prefix . ':' . md5(implode('|', $normalized));
    }

    /**
     * @dev venixplataformas
     * Display a listing of the resource.
     */
    public function index()
    {
        $key = 'api:games:index:v2';

        return $this->cacheRemember($key, $this->cacheTtl, function () {
            $providers = Provider::with(['games', 'games.provider'])
                ->whereHas('games')
                ->where('status', 1)
                ->orderBy('name', 'desc')
                ->get();

            return response()->json(['providers' => $providers]);
        });
    }

    /**
     * @dev venixplataformas
     * @return \Illuminate\Http\JsonResponse
     */
    public function featured()
    {
        $key = 'api:games:featured:v2';

        return $this->cacheRemember($key, $this->cacheTtl, function () {
            $featured_games = Game::with(['provider'])
                ->where('is_featured', 1)
                ->where('status', 1)
                ->get();

            return response()->json(['featured_games' => $featured_games]);
        });
    }

    /**
     * Source Provider
     */
    public function sourceProvider(Request $request, $token, $action)
    {
        $tokenOpen = \Helper::DecToken($token);
        $validEndpoints = ['session', 'icons', 'spin', 'freenum'];

        if (!in_array($action, $validEndpoints)) {
            return response()->json([], 500);
        }

        if (isset($tokenOpen['status']) && $tokenOpen['status']) {
            $game = Game::whereStatus(1)->where('game_code', $tokenOpen['game'])->first();
            if (!empty($game)) {
                $controller = \Helper::createController($game->game_code);

                switch ($action) {
                    case 'session':
                        return $controller->session($token);
                    case 'spin':
                        return $controller->spin($request, $token);
                    case 'freenum':
                        return $controller->freenum($request, $token);
                    case 'icons':
                        return $controller->icons();
                }
            }
        }

        return response()->json([], 500);
    }

    /**
     * Favorite
     */
    public function toggleFavorite($id)
    {
        if (auth('api')->check()) {
            $checkExist = GameFavorite::where('user_id', auth('api')->id())->where('game_id', $id)->first();
            if (!empty($checkExist)) {
                if ($checkExist->delete()) {
                    return response()->json(['status' => true, 'message' => 'Removido com sucesso']);
                }
            } else {
                $gameFavoriteCreate = GameFavorite::create([
                    'user_id' => auth('api')->id(),
                    'game_id' => $id
                ]);

                if ($gameFavoriteCreate) {
                    return response()->json(['status' => true, 'message' => 'Criado com sucesso']);
                }
            }
        }
    }

    /**
     * Like
     */
    public function toggleLike($id)
    {
        if (auth('api')->check()) {
            $checkExist = GameLike::where('user_id', auth('api')->id())->where('game_id', $id)->first();
            if (!empty($checkExist)) {
                if ($checkExist->delete()) {
                    return response()->json(['status' => true, 'message' => 'Removido com sucesso']);
                }
            } else {
                $gameLikeCreate = GameLike::create([
                    'user_id' => auth('api')->id(),
                    'game_id' => $id
                ]);

                if ($gameLikeCreate) {
                    return response()->json(['status' => true, 'message' => 'Criado com sucesso']);
                }
            }
        }
    }

    /**
     * Show game
     * NÃO cacheia (views, saldo, token)
     */
    public function show(string $id)
    {
        $game = Game::with(['categories', 'provider'])->whereStatus(1)->find($id);

        if (empty($game)) {
            return response()->json(['error' => '', 'status' => false], 400);
        }

        if (!auth('api')->check()) {
            return response()->json(['error' => 'Você precisa tá autenticado para jogar', 'status' => false], 400);
        }

        $wallet = Wallet::where('user_id', auth('api')->id())->first();
        if (!$wallet || $wallet->total_balance <= 0) {
            return response()->json(['error' => 'Você precisa ter saldo para jogar', 'status' => false, 'action' => 'deposit'], 200);
        }

        $game->increment('views');

        $token = \Helper::MakeToken([
            'id' => auth('api')->id(),
            'game' => $game->game_code
        ]);

        switch ($game->distribution) {
            case 'source':
                return response()->json([
                    'game' => $game,
                    'gameUrl' => url('/originals/' . $game->game_code . '/index.html?token=' . $token),
                    'token' => $token
                ]);

            case 'venixcg':
                $gameLauncher = self::GameLaunchVenixCG($game);
                if ($gameLauncher) {
                    return response()->json([
                        'game' => $game,
                        'gameUrl' => $gameLauncher,
                        'token' => $token
                    ]);
                }
                return response()->json();

            case 'playgaming':
                $gameLauncher = self::LaunchGamePlayGaming($game->game_id);
                if ($gameLauncher) {
                    return response()->json([
                        'game' => $game,
                        'gameUrl' => $gameLauncher,
                        'token' => $token
                    ]);
                }
                return response()->json();

            case 'salsa':
                return response()->json([
                    'game' => $game,
                    'gameUrl' => self::playGameSalsa('CHARGED', 'BRL', 'pt', $game->game_id),
                    'token' => $token
                ]);

            case 'evergame':
                $evergameLaunch = self::GameLaunchEvergame($game->provider->code, $game->game_id, 'pt', auth('api')->id());
                if (isset($evergameLaunch['launchUrl'])) {
                    return response()->json([
                        'game' => $game,
                        'gameUrl' => $evergameLaunch['launchUrl'],
                    ]);
                }
                return response()->json($evergameLaunch);

            case 'vibra_gaming':
                return response()->json([
                    'game' => $game,
                    'gameUrl' => self::GenerateGameLaunch($game),
                    'token' => $token
                ]);

            case 'fivers':
                $fiversLaunch = self::GameLaunchFivers($game->provider->code, $game->game_id, 'pt', auth('api')->id());
                if (isset($fiversLaunch['launch_url'])) {
                    return response()->json([
                        'game' => $game,
                        'gameUrl' => $fiversLaunch['launch_url'],
                        'token' => $token
                    ]);
                }
                return response()->json(['error' => $fiversLaunch, 'status' => false], 400);

            case 'games2_api':
                $games2ApiLaunch = self::GameLaunchGames2($game->provider->code, $game->game_id, 'pt', auth('api')->id());
                if (isset($games2ApiLaunch['launch_url'])) {
                    return response()->json([
                        'game' => $game,
                        'gameUrl' => $games2ApiLaunch['launch_url'],
                        'token' => $token
                    ]);
                }
                return response()->json(['error' => $games2ApiLaunch, 'status' => false], 400);

            case 'worldslot':
                $worldslotLaunch = self::GameLaunchWorldSlot($game->provider->code, $game->game_id, 'pt', auth('api')->id());
                if (isset($worldslotLaunch['launch_url'])) {
                    return response()->json([
                        'game' => $game,
                        'gameUrl' => $worldslotLaunch['launch_url'],
                        'token' => $token
                    ]);
                }
                return response()->json(['error' => $worldslotLaunch, 'status' => false], 400);
        }

        return response()->json(['error' => '', 'status' => false], 400);
    }

    /**
     * Listagem (cache por page, provider, category, search)
     * Seguro e robusto: funciona com cache driver sem tags.
     */
    public function allGames(Request $request)
    {
        $provider = (string) $request->query('provider', 'all');
        $category = (string) $request->query('category', 'all');
        $search   = (string) $request->query('searchTerm', '');
        $page     = (int) $request->query('page', 1);

        $key = $this->makeCacheKey('api:games:allGames:v3', [
            'provider' => $provider,
            'category' => $category,
            'search' => $search,
            'page' => $page,
        ]);

        return $this->cacheRemember($key, $this->cacheTtl, function () use ($request) {

            $query = Game::query()
                ->with(['provider', 'categories'])
                ->where('status', 1);

            if (!empty($request->provider) && $request->provider != 'all') {
                $query->where('provider_id', $request->provider);
            }

            if (!empty($request->category) && $request->category != 'all') {
                $query->whereHas('categories', function ($q) use ($request) {
                    $q->where('slug', $request->category);
                });
            }

            if (isset($request->searchTerm) && !empty($request->searchTerm) && strlen($request->searchTerm) > 2) {
                $query->whereLike(
                    ['game_code', 'game_name', 'description', 'distribution', 'provider.name'],
                    $request->searchTerm
                );
            } else {
                $query->orderBy('views', 'desc');
            }

            $games = $query
                ->paginate(12)
                ->appends(request()->query());

            return response()->json(['games' => $games]);
        });
    }

    public function webhookGoldApiMethod(Request $request)
    {
        return self::WebhooksFivers($request);
    }

    public function webhookUserBalanceMethod(Request $request)
    {
        return self::GetUserBalanceWorldSlot($request);
    }

    public function webhookGameCallbackMethod(Request $request)
    {
        return self::GameCallbackWorldSlot($request);
    }

    public function webhookMoneyCallbackMethod(Request $request)
    {
        return self::MoneyCallbackWorldSlot($request);
    }

    public function webhookVibraMethod(Request $request, $parameters)
    {
        return self::WebhookVibra($request, $parameters);
    }

    public function webhookKaGamingMethod(Request $request)
    {
        return self::WebhookKaGaming($request);
    }

    public function webhookSalsaMethod(Request $request)
    {
        return self::webhookSalsa($request);
    }

    public function webhookVeniXMethod(Request $request)
    {
        return self::WebhookVenixCG($request);
    }

    public function webhookEvergameMethod(Request $request)
    {
        return self::WebhooksEvergame($request);
    }

    public function webhookPlayGamingMethod(Request $request)
    {
        return self::WebhooksPlayGaming($request);
    }
}