<?php
/**
 * Pusher helper — creates a shared Pusher instance and provides trigger/auth helpers.
 * Supports both Pusher.com and self-hosted Soketi / Laravel WebSockets.
 *
 * @package Cnw_Social_Bridge
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Cnw_Social_Bridge_Pusher {

    private static $instance = null;

    /**
     * Get a configured Pusher instance, or null if credentials are missing.
     */
    public static function get_instance() {
        if ( self::$instance !== null ) {
            return self::$instance;
        }

        $app_id  = get_option( 'cnw_pusher_app_id', '' );
        $key     = get_option( 'cnw_pusher_key', '' );
        $secret  = get_option( 'cnw_pusher_secret', '' );
        $cluster = get_option( 'cnw_pusher_cluster', 'mt1' );
        $host    = get_option( 'cnw_pusher_host', '' );
        $port    = get_option( 'cnw_pusher_port', '443' );

        if ( ! $app_id || ! $key || ! $secret ) {
            return null;
        }

        $options = array(
            'cluster' => $cluster,
            'useTLS'  => true,
        );

        // Self-hosted (Soketi / Laravel WebSockets)
        if ( $host ) {
            $options['host']   = $host;
            $options['port']   = (int) $port;
            $use_tls = ( (int) $port === 443 );
            $options['scheme'] = $use_tls ? 'https' : 'http';
            $options['useTLS'] = $use_tls;
        }

        if ( ! class_exists( '\\Pusher\\Pusher' ) ) {
            return null;
        }

        self::$instance = new \Pusher\Pusher( $key, $secret, $app_id, $options );

        return self::$instance;
    }

    /**
     * Trigger an event. Silently fails if Pusher is not configured.
     */
    public static function trigger( $channel, $event, $data ) {
        $pusher = self::get_instance();
        if ( ! $pusher ) {
            return;
        }
        try {
            $pusher->trigger( $channel, $event, $data );
        } catch ( \Exception $e ) {
            // Silently ignore — messages still work via REST fallback.
        }
    }

    /**
     * Authenticate a private channel subscription.
     */
    public static function auth( $channel_name, $socket_id ) {
        $pusher = self::get_instance();
        if ( ! $pusher ) {
            return new WP_Error( 'pusher_not_configured', 'Pusher credentials not set', array( 'status' => 500 ) );
        }
        return $pusher->authorizeChannel( $channel_name, $socket_id );
    }

    /**
     * Verify a Pusher webhook signature.
     * Pusher signs webhooks with HMAC SHA-256 using the app secret.
     *
     * @param string $body      Raw request body.
     * @param string $signature The X-Pusher-Signature header value.
     * @return bool
     */
    public static function verify_webhook( $body, $signature ) {
        if ( ! $signature || ! $body ) {
            return false;
        }
        $secret = get_option( 'cnw_pusher_secret', '' );
        if ( ! $secret ) {
            return false;
        }
        $expected = hash_hmac( 'sha256', $body, $secret );
        return hash_equals( $expected, $signature );
    }
}
