<?php
/**
 * Asset Orchestrator for Core Web Vitals (CWV)
 * Optimizes the critical rendering path by managing script loading strategies.
 * * @author Upesh Vishwakarma
 */

namespace UpeshV\Performance;

class AssetOrchestrator {
    
    /**
     * De-queue unnecessary assets on high-conversion pages (Home, Landing Pages)
     * Reduces the total CSS/JS payload size.
     */
    public static function cleanup_excessive_assets() {
        if ( is_front_page() || is_page_template( 'landing-page.php' ) ) {
            wp_dequeue_style( 'contact-form-7' ); // Example: CF7 is heavy and only needed on /contact
            wp_dequeue_style( 'wc-block-style' ); // WooCommerce blocks on non-shop pages
            wp_dequeue_script( 'wp-embed' );
        }
    }

    /**
     * Transform blocking scripts into non-blocking 'defer' or 'async' 
     * based on their impact on First Contentful Paint (FCP).
     */
    public static function optimize_script_attributes( $tag, $handle, $src ) {
        // List of scripts to DEFER (Wait until DOM is ready)
        $defer_scripts = [ 'jquery-core', 'google-tag-manager', 'hubspot-analytics' ];
        
        // List of scripts to ASYNC (Download in background)
        $async_scripts = [ 'adsense-js', 'external-widget' ];

        if ( in_array( $handle, $defer_scripts ) ) {
            return str_replace( ' src', ' defer="defer" src', $tag );
        }

        if ( in_array( $handle, $async_scripts ) ) {
            return str_replace( ' src', ' async="async" src', $tag );
        }

        return $tag;
    }
}

// Hooks to trigger the Orchestrator
add_action( 'wp_enqueue_scripts', [ 'UpeshV\Performance\AssetOrchestrator', 'cleanup_excessive_assets' ], 99 );
add_filter( 'script_loader_tag', [ 'UpeshV\Performance\AssetOrchestrator', 'optimize_script_attributes' ], 10, 3 );
