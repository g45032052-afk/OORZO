<?php

namespace Modules\AI\app\Agents\Tools;

use Modules\AI\app\Agents\AiResponseContext;
use App\Models\Cuisine;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

/**
 * List the platform's CUISINES (Bengali, Chinese, Italian, …). Cuisines are a
 * distinct entity from food categories — a cuisine groups RESTAURANTS by their
 * style of cooking, whereas a category groups FOODS. Use this whenever the
 * customer asks about cuisines/food styles/nationalities of food; never answer
 * a cuisine question with GetCategoriesTool.
 */
class GetCuisinesTool implements Tool
{
    public function __construct(private readonly AiResponseContext $context) {}

    public function description(): string
    {
        return 'Get the cuisines available on the platform (e.g. Bengali, Chinese, Italian) with their IDs. Cuisines describe a restaurant\'s style of cooking and are DIFFERENT from food categories. Pass a keyword to look up a specific cuisine, or leave it null to list all cuisines.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'keyword' => $schema->string()->description('Optional cuisine-name keyword (e.g. "Italian"). Null = list all cuisines.')->required()->nullable(),
            'limit'   => $schema->number()->description('Number of cuisines to return, default 15, max 30')->required()->nullable(),
        ];
    }

    public function handle(Request $request): string
    {
        $args    = $request->all();
        $limit   = min((int) ($args['limit'] ?? 15), 30);
        $keyword = isset($args['keyword']) && $args['keyword'] !== null ? trim((string) $args['keyword']) : null;

        $cuisines = Cuisine::where('status', 1)
            ->when($keyword !== null && $keyword !== '', function ($q) use ($keyword) {
                $keys = array_filter(array_map('trim', explode(' ', $keyword)));
                $q->where(function ($qq) use ($keys, $keyword) {
                    $qq->where('name', 'like', "%{$keyword}%");
                    foreach ($keys as $kw) {
                        if (mb_strlen($kw) >= 3) {
                            $qq->orWhere('name', 'like', "%{$kw}%");
                        }
                    }
                });
            })
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name', 'image']);

        $this->context->recordTool('GetCuisinesTool');

        if ($cuisines->isEmpty()) {
            return $keyword !== null && $keyword !== ''
                ? "No cuisines matched \"{$keyword}\"."
                : 'No cuisines found.';
        }

        $cuisineList = $cuisines->map(fn (Cuisine $c) => [
            'id'             => $c->getKey(),
            'name'           => $c->getAttribute('name'),
            'image'          => $c->getAttribute('image'),
            'image_full_url' => $c->image_full_url,
        ])->values()->all();

        $this->context->addCuisines($cuisineList);

        $lines = implode('; ', array_map(
            fn (array $c) => $c['name'] . ' [ID:' . $c['id'] . ']',
            $cuisineList
        ));

        return count($cuisineList) . ' cuisines: ' . $lines;
    }
}
