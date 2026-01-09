<?php

declare(strict_types=1);

namespace CurtainCall\Controllers;

use CurtainCall\Data\CastCrewData;
use CurtainCall\Data\ProductionData;
use CurtainCall\Models\CastAndCrew;
use CurtainCall\Models\CurtainCallPivot;
use CurtainCall\Models\Production;
use Illuminate\Support\Collection;
use WP_Error;
use WP_Post;
use WP_REST_Request;
use WP_REST_Response;

final class RelationsRestController extends RestController
{
    public static function registerRoutes(): void
    {
        register_rest_route(self::NAMESPACE, '/relations', [
            [
                'methods' => 'POST',
                'callback' => [self::class, 'attach'],
                'permission_callback' => static fn() => current_user_can('edit_posts'),
                'args' => [
                    'cast_and_crew_id' => ['type' => 'integer', 'required' => true],
                    'production_id' => ['type' => 'integer', 'required' => true],
                    'type' => ['type' => 'string', 'required' => true, 'enum' => ['cast', 'crew']],
                    'role' => ['type' => 'string', 'required' => false],
                    'custom_order' => ['type' => 'integer', 'required' => false],
                ],
            ],
            [
                'methods' => 'DELETE',
                'callback' => [self::class, 'detach'],
                'permission_callback' => static fn() => current_user_can('edit_posts'),
                'args' => [
                    'production_id' => ['type' => 'integer', 'required' => true],
                    'cast_and_crew_id' => ['type' => 'integer', 'required' => true],
                ],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/relations/cast-crew', [
            [
                'methods' => 'GET',
                'callback' => [self::class, 'getCastCrew'],
                'permission_callback' => static fn() => current_user_can('edit_posts'),
                'args' => [
                    'production_id' => ['type' => 'integer', 'required' => false],
                    'type' => ['type' => 'string', 'required' => false, 'enum' => ['cast', 'crew']],
                ],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/relations/productions', [
            [
                'methods' => 'GET',
                'callback' => [self::class, 'getProductions'],
                'permission_callback' => static fn() => current_user_can('edit_posts'),
                'args' => [
                    'cast_crew_id' => ['type' => 'integer', 'required' => false],
                ],
            ],
        ]);
    }

    public static function getCastCrew(WP_REST_Request $request): WP_REST_Response
    {
        $wpdb = ccwp_get_wpdb();
        $table = $wpdb->prefix . CurtainCallPivot::TABLE_NAME;
        $castCrewPostType = CastAndCrew::POST_TYPE;
        $where = [];
        $params = [];

        $productionId = $request->get_param('production_id');
        if (!$productionId) {
            return new WP_REST_Response([], 200);
        }

        $where[] = 'production_id = %d';
        $params[] = (int) $productionId;

        $type = $request->get_param('type');
        if ($type) {
            $where[] = 'type = %s';
            $params[] = $type;
        }

        // Get the pivot rows
        $pivotSql = "SELECT * FROM {$table}" . (empty($where) ? '' : ' WHERE ' . implode(' AND ', $where));
        /** @var array{production_id: int, cast_and_crew_id: int, type: string, role: string, order: int} $pivotRows */
        $pivotRows = $wpdb->get_results($wpdb->prepare($pivotSql, $params), ARRAY_A) ?: [];
        /** @var Collection<CurtainCallPivot> $pivots */
        $pivots = collect($pivotRows)->map(static fn($row) => new CurtainCallPivot($row));

        // Then get the cast/crew members
        /** @var list<int> $memberIds */
        $memberIds = $pivots->pluck('cast_and_crew_id')->unique()->values()->toArray();
        /** @var Collection<int, WP_Post> $memberPosts */
        $memberPosts = collect();

        if (count($memberIds) > 0) {
            $postSql = "
                SELECT *
                FROM {$wpdb->posts}
                WHERE `ID` IN (" . implode(',', $memberIds) . ")
                AND `post_type` = '{$castCrewPostType}'
            ";
            /** @var list<array<string, mixed>> $postRows */
            $postRows = $wpdb->get_results($wpdb->prepare($postSql, $params)) ?: [];
            /** @var Collection<int, WP_Post> $memberPosts */
            $memberPosts = collect($postRows)->map(static fn($row) => new WP_Post($row))->keyBy('ID');
        }

        // glue them together
        $castCrew = $pivots
            ->map(static function (CurtainCallPivot $pivot) use ($memberPosts) {
                /** @var WP_Post|null $memberPost */
                $memberPost = $memberPosts->get($pivot->cast_and_crew_id);

                if (!$memberPost) {
                    return null;
                }

                $member = CastAndCrew::make($memberPost)->setCurtainCallPostJoin($pivot);

                return CastCrewData::fromCastCrew($member);
            })
            ->filter()
            ->values()
            ->toArray();

        return new WP_REST_Response($castCrew, 200);
    }

    public static function getProductions(WP_REST_Request $request): WP_REST_Response
    {
        $wpdb = ccwp_get_wpdb();
        $table = $wpdb->prefix . CurtainCallPivot::TABLE_NAME;
        $productionPostType = Production::POST_TYPE;
        $where = [];
        $params = [];

        $castcrewId = $request->get_param('cast_and_crew_id');
        if (!$castcrewId) {
            return new WP_REST_Response([], 200);
        }

        $where[] = 'cast_and_crew_id = %d';
        $params[] = (int) $castcrewId;

        // Get the pivot rows
        $pivotSql = "SELECT * FROM {$table}" . (empty($where) ? '' : ' WHERE ' . implode(' AND ', $where));
        /** @var array{production_id: int, cast_and_crew_id: int, type: string, role: string, order: int} $rows */
        $pivotRows = $wpdb->get_results($wpdb->prepare($pivotSql, $params), ARRAY_A) ?: [];
        /** @var Collection<int, CurtainCallPivot> $pivots */
        $pivots = collect($pivotRows)->map(static fn(array $row) => new CurtainCallPivot($row));

        // Then get the productions
        /** @var list<int> $productionIds */
        $productionIds = $pivots->pluck('production_id')->values()->toArray();
        /** @var Collection<int, WP_Post> $productionPosts */
        $productionPosts = collect();

        if (count($productionIds) > 0) {
            $postSql = "
                SELECT *
                FROM {$wpdb->posts}
                WHERE `ID` IN (" . implode(',', $productionIds) . ")
                AND `post_type` = '{$productionPostType}'
            ";
            /** @var list<array<string, mixed>> $rows */
            $postRows = $wpdb->get_results($wpdb->prepare($postSql, $params)) ?: [];
            /** @var Collection<int, WP_Post> $productionPosts */
            $productionPosts = collect($postRows)->map(static fn($row) => new WP_Post($row))->keyBy('ID');
        }

        // glue them together
        $productions = $pivots
            ->map(static function (CurtainCallPivot $pivot) use ($productionPosts) {
                /** @var WP_Post|null $production */
                $productionPost = $productionPosts->get($pivot->production_id);

                if (!$productionPost) {
                    return null;
                }

                $production = Production::make($productionPost)->setCurtainCallPostJoin($pivot);

                return ProductionData::fromProduction($production);
            })
            ->filter()
            ->values()
            ->toArray();

        return new WP_REST_Response($productions, 200);
    }

    public static function attach(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $wpdb = ccwp_get_wpdb();
        $table = $wpdb->prefix . CurtainCallPivot::TABLE_NAME;
        $productionId = (int) $request->get_param('production_id');
        $castcrewId = (int) $request->get_param('cast_and_crew_id');
        $type = (string) $request->get_param('type');
        $role = (string) ($request->get_param('role') ?? '');
        $order = $request->get_param('custom_order');

        if (!$productionId || !$castcrewId || !in_array($type, ['cast', 'crew'], true)) {
            return new WP_Error('ccwp_invalid_params', 'Invalid parameters', ['status' => 400]);
        }

        // Upsert semantics
        $data = [
            'production_id' => $productionId,
            'cast_and_crew_id' => $castcrewId,
            'type' => $type,
            'role' => sanitize_text_field($role),
            'custom_order' => is_null($order) ? null : (int) $order,
        ];

        // Check existing
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE production_id=%d AND cast_and_crew_id=%d AND type=%s",
            $productionId,
            $castcrewId,
            $type,
        ));

        if ((int) $existing > 0) {
            $wpdb->update($table, $data, [
                'production_id' => $productionId,
                'cast_and_crew_id' => $castcrewId,
                'type' => $type,
            ]);
        } else {
            $wpdb->insert($table, $data);
        }

        return new WP_REST_Response(['ok' => true], 200);
    }

    public static function detach(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $wpdb = ccwp_get_wpdb();
        $table = $wpdb->prefix . CurtainCallPivot::TABLE_NAME;
        $productionId = (int) $request->get_param('production_id');
        $castcrewId = (int) $request->get_param('cast_and_crew_id');

        if (!$productionId || !$castcrewId) {
            return new WP_Error('ccwp_invalid_params', 'Invalid parameters', ['status' => 400]);
        }

        $params = ['production_id' => $productionId, 'cast_and_crew_id' => $castcrewId];

        if ($request->has_param('type')) {
            $type = (string) $request->get_param('type');

            if (!in_array($type, ['cast', 'crew'], true)) {
                return new WP_Error('ccwp_invalid_params', 'Invalid parameters', ['status' => 400]);
            }

            $params['type'] = $type;
        }

        $wpdb->delete($table, $params);

        return new WP_REST_Response(['ok' => true], 200);
    }
}
