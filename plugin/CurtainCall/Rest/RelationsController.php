<?php

declare(strict_types=1);

namespace CurtainCall\Rest;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class RelationsController
{
    public const NAMESPACE = 'ccwp/v1';

    public static function registerRoutes(): void
    {
        register_rest_route(static::NAMESPACE, '/relations', [
            [
                'methods' => 'GET',
                'callback' => [static::class, 'getRelations'],
                'permission_callback' => static fn() => current_user_can('edit_posts'),
                'args' => [
                    'production_id' => ['type' => 'integer', 'required' => false],
                    'cast_and_crew_id' => ['type' => 'integer', 'required' => false],
                    'type' => ['type' => 'string', 'required' => false, 'enum' => ['cast', 'crew']],
                ],
            ],
            [
                'methods' => 'POST',
                'callback' => [static::class, 'attach'],
                'permission_callback' => static fn() => current_user_can('edit_posts'),
                'args' => [
                    'production_id' => ['type' => 'integer', 'required' => true],
                    'cast_and_crew_id' => ['type' => 'integer', 'required' => true],
                    'type' => ['type' => 'string', 'required' => true, 'enum' => ['cast', 'crew']],
                    'role' => ['type' => 'string', 'required' => false],
                    'custom_order' => ['type' => 'integer', 'required' => false],
                ],
            ],
            [
                'methods' => 'DELETE',
                'callback' => [static::class, 'detach'],
                'permission_callback' => static fn() => current_user_can('edit_posts'),
                'args' => [
                    'production_id' => ['type' => 'integer', 'required' => true],
                    'cast_and_crew_id' => ['type' => 'integer', 'required' => true],
                ],
            ],
        ]);
    }

    public static function getRelations(WP_REST_Request $request): WP_REST_Response
    {
        global $wpdb;

        $table = $wpdb->prefix . 'ccwp_castandcrew_production';
        $where = [];
        $params = [];

        $pid = (int) $request->get_param('production_id');

        if ($pid) {
            $where[] = 'production_id = %d';
            $params[] = $pid;
        }

        $ccid = (int) $request->get_param('cast_and_crew_id');

        if ($ccid) {
            $where[] = 'cast_and_crew_id = %d';
            $params[] = $ccid;
        }

        $type = $request->get_param('type');

        if ($type) {
            $where[] = 'type = %s';
            $params[] = $type;
        }

        $sql = "SELECT * FROM {$table}" . (empty($where) ? '' : ' WHERE ' . implode(' AND ', $where));
        $rows = $wpdb->get_results($wpdb->prepare($sql, $params));

        return new WP_REST_Response($rows ?: [], 200);
    }

    public static function attach(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ccwp_castandcrew_production';

        $pid = (int) $request->get_param('production_id');
        $ccid = (int) $request->get_param('cast_and_crew_id');
        $type = (string) $request->get_param('type');
        $role = (string) ($request->get_param('role') ?? '');
        $order = $request->get_param('custom_order');

        if (!$pid || !$ccid || !in_array($type, ['cast', 'crew'], true)) {
            return new WP_Error('ccwp_invalid_params', 'Invalid parameters', ['status' => 400]);
        }

        // Upsert semantics
        $data = [
            'production_id' => $pid,
            'cast_and_crew_id' => $ccid,
            'type' => $type,
            'role' => sanitize_text_field($role),
            'custom_order' => is_null($order) ? null : (int) $order,
        ];

        // Check existing
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE production_id=%d AND cast_and_crew_id=%d",
            $pid,
            $ccid,
        ));

        if ((int) $existing > 0) {
            $wpdb->update($table, $data, [
                'production_id' => $pid,
                'cast_and_crew_id' => $ccid,
            ]);
        } else {
            $wpdb->insert($table, $data);
        }

        return new WP_REST_Response(['ok' => true], 200);
    }

    public static function detach(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ccwp_castandcrew_production';

        $pid = (int) $request->get_param('production_id');
        $ccid = (int) $request->get_param('cast_and_crew_id');

        if (!$pid || !$ccid) {
            return new WP_Error('ccwp_invalid_params', 'Invalid parameters', ['status' => 400]);
        }

        $wpdb->delete($table, [
            'production_id' => $pid,
            'cast_and_crew_id' => $ccid,
        ]);

        return new WP_REST_Response(['ok' => true], 200);
    }
}
