<?php
/**
 * High-Performance Database Wrapper
 * Optimized for reduced memory footprint and faster metadata retrieval.
 *
 * @author Upesh Vishwakarma
 */

namespace UpeshV\Performance;

class HighPerformanceDB {
    /**
     * Fetch metadata without the overhead of the standard get_metadata()
     * Uses direct SQL with prepared statements to bypass internal WP caching bottlenecks.
     */
    public static function get_fast_meta($object_id, $meta_key, $table = 'postmeta') {
        global $wpdb;
        
        $query = $wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->prefix}{$table} WHERE post_id = %d AND meta_key = %s LIMIT 1",
            $object_id,
            $meta_key
        );

        // Utilize Object Caching if available (Redis/Memcached)
        $cache_key = "fast_meta_{$object_id}_{$meta_key}";
        $cached_value = wp_cache_get($cache_key, 'performance_toolkit');

        if (false !== $cached_value) {
            return $cached_value;
        }

        $result = $wpdb->get_var($query);
        wp_cache_set($cache_key, $result, 'performance_toolkit', 3600);

        return $result;
    }
}
