<?php
/**
 * Seeds demo data matching the Figma dashboard design.
 *
 * @package Cnw_Social_Bridge
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Cnw_Social_Bridge_Seed_Data {

    /**
     * Seed threads, replies, and votes if the threads table is empty.
     */
    public static function seed() {
        global $wpdb;

        $threads_table = $wpdb->prefix . 'cnw_social_worker_threads';

        // Guard: only seed when threads table is empty.
        $count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$threads_table}" );
        if ( $count > 0 ) {
            return;
        }

        // ── Create demo users ────────────────────────────────────────────
        $users = array(
            'jordan_m'       => array( 'display_name' => 'Jordan M.',    'email' => 'jordan_m@cnw-demo.local' ),
            'marcus_l'       => array( 'display_name' => 'Marcus L.',    'email' => 'marcus_l@cnw-demo.local' ),
            'cornelia_k'     => array( 'display_name' => 'Cornelia K.',  'email' => 'cornelia_k@cnw-demo.local' ),
            'anonymous_user' => array( 'display_name' => 'Anonymous',    'email' => 'anonymous@cnw-demo.local' ),
        );

        $user_ids = array();
        foreach ( $users as $login => $meta ) {
            $existing = get_user_by( 'login', $login );
            if ( $existing ) {
                $user_ids[ $login ] = $existing->ID;
                continue;
            }

            $id = wp_insert_user( array(
                'user_login'   => $login,
                'user_email'   => $meta['email'],
                'user_pass'    => wp_generate_password( 24 ),
                'display_name' => $meta['display_name'],
                'role'         => 'cnw_forum_member',
            ) );

            if ( ! is_wp_error( $id ) ) {
                $user_ids[ $login ] = $id;
            }
        }

        // ── Insert threads ───────────────────────────────────────────────
        $replies_table = $wpdb->prefix . 'cnw_social_worker_replies';
        $votes_table   = $wpdb->prefix . 'cnw_social_worker_votes';

        $threads = array(
            array(
                'author'     => 'jordan_m',
                'title'      => 'Best referral options for adults needing low-cost trauma-informed therapy?',
                'content'    => 'Best referral options for adults needing low-cost trauma-informed therapy?',
                'views'      => 3402,
                'created_at' => '2026-02-03 15:06:00',
                'upvotes'    => 14,
            ),
            array(
                'author'     => 'anonymous_user',
                'title'      => 'How are others securing emergency housing for single adults when shelters are full?',
                'content'    => 'How are others securing emergency housing for single adults when shelters are full?',
                'views'      => 1250,
                'created_at' => '2026-02-14 08:02:00',
                'upvotes'    => 0,
            ),
            array(
                'author'     => 'anonymous_user',
                'title'      => 'Supporting teens experiencing housing instability while staying enrolled in school',
                'content'    => 'Supporting teens experiencing housing instability while staying enrolled in school',
                'views'      => 3402,
                'created_at' => '2026-02-03 08:19:00',
                'upvotes'    => 15,
            ),
        );

        $thread_ids = array();
        foreach ( $threads as $t ) {
            $wpdb->insert( $threads_table, array(
                'author_id'  => $user_ids[ $t['author'] ],
                'title'      => $t['title'],
                'content'    => $t['content'],
                'status'     => 'published',
                'views'      => $t['views'],
                'created_at' => $t['created_at'],
                'updated_at' => $t['created_at'],
            ) );
            $thread_ids[] = $wpdb->insert_id;

            // Insert upvotes for this thread.
            for ( $i = 0; $i < $t['upvotes']; $i++ ) {
                $wpdb->insert( $votes_table, array(
                    'user_id'     => $user_ids['jordan_m'] + $i, // spread across user IDs
                    'target_type' => 'thread',
                    'target_id'   => $wpdb->insert_id ?: end( $thread_ids ),
                    'vote_type'   => 1,
                ) );
            }
        }

        // Fix: re-insert thread votes properly (the loop above had an insert_id issue).
        // Clear votes and redo cleanly.
        $wpdb->query( "DELETE FROM {$votes_table}" );
        self::insert_thread_votes( $votes_table, $thread_ids[0], 14, $user_ids );
        self::insert_thread_votes( $votes_table, $thread_ids[2], 15, $user_ids );

        // ── Insert replies for Thread 2 ──────────────────────────────────
        $t2 = $thread_ids[1];

        // Reply 1: Cornelia K. (top-level)
        $wpdb->insert( $replies_table, array(
            'thread_id'  => $t2,
            'author_id'  => $user_ids['cornelia_k'],
            'parent_id'  => null,
            'content'    => "Seconding this. We've had success when we loop in outreach teams early — especially street outreach or coordinated entry staff. If your area has a shared-availability tracker (even a simple shared spreadsheet), it speeds things up. Also, some housing authorities have emergency vouchers that aren't widely advertised.",
            'status'     => 'approved',
            'created_at' => '2026-02-14 09:30:00',
        ) );
        $reply1_id = $wpdb->insert_id;

        // Reply 2: Marcus L. (top-level)
        $wpdb->insert( $replies_table, array(
            'thread_id'  => $t2,
            'author_id'  => $user_ids['marcus_l'],
            'parent_id'  => null,
            'content'    => "Thank you for sharing this. The shared-availability tracking is a great idea — we've been doing something similar with a Google Sheet that local providers update weekly. It's not perfect, but it's helped us avoid sending clients to places that are already full.",
            'status'     => 'approved',
            'created_at' => '2026-02-14 10:15:00',
        ) );
        $reply2_id = $wpdb->insert_id;

        // Reply 3: Anonymous (top-level)
        $wpdb->insert( $replies_table, array(
            'thread_id'  => $t2,
            'author_id'  => $user_ids['anonymous_user'],
            'parent_id'  => null,
            'content'    => 'Same experience here. Early coordination makes a huge difference.',
            'status'     => 'approved',
            'created_at' => '2026-02-14 11:00:00',
        ) );
        $reply3_id = $wpdb->insert_id;

        // Reply 4: Marcus L. (nested under reply 1)
        $wpdb->insert( $replies_table, array(
            'thread_id'  => $t2,
            'author_id'  => $user_ids['marcus_l'],
            'parent_id'  => $reply1_id,
            'content'    => "Thank you for sharing this. The shared-availability tracking is a great idea — we've been doing something similar with a Google Sheet that local providers update weekly. It's not perfect, but it's helped us avoid sending clients to places that are already full.",
            'status'     => 'approved',
            'created_at' => '2026-02-14 11:30:00',
        ) );
        $reply4_id = $wpdb->insert_id;

        // Reply 5: Anonymous (nested under reply 1)
        $wpdb->insert( $replies_table, array(
            'thread_id'  => $t2,
            'author_id'  => $user_ids['anonymous_user'],
            'parent_id'  => $reply1_id,
            'content'    => 'Same experience here. Early coordination makes a huge difference.',
            'status'     => 'approved',
            'created_at' => '2026-02-14 12:00:00',
        ) );
        $reply5_id = $wpdb->insert_id;

        // Reply 6: Marcus L. (top-level)
        $wpdb->insert( $replies_table, array(
            'thread_id'  => $t2,
            'author_id'  => $user_ids['marcus_l'],
            'parent_id'  => null,
            'content'    => "In cases where shelters are full, we've focused on diversion strategies — short-term financial assistance for rent deposits, connecting with faith-based organizations that offer temporary stays, and working with landlords who accept rapid re-housing vouchers. It's not a perfect solution, but it buys time.",
            'status'     => 'approved',
            'created_at' => '2026-02-14 13:00:00',
        ) );
        $reply6_id = $wpdb->insert_id;

        // ── Insert reply votes ───────────────────────────────────────────
        $reply_likes = array(
            $reply1_id => 7,
            $reply2_id => 3,
            $reply3_id => 2,
            $reply4_id => 3,
            $reply5_id => 2,
            $reply6_id => 5,
        );

        $voter_base = $user_ids['jordan_m'];
        foreach ( $reply_likes as $reply_id => $num_likes ) {
            for ( $i = 0; $i < $num_likes; $i++ ) {
                $wpdb->insert( $votes_table, array(
                    'user_id'     => $voter_base + $i,
                    'target_type' => 'reply',
                    'target_id'   => $reply_id,
                    'vote_type'   => 1,
                ) );
            }
        }
    }

    /**
     * Helper to insert thread upvotes, spreading user_ids to avoid unique constraint.
     */
    private static function insert_thread_votes( $votes_table, $thread_id, $count, $user_ids ) {
        global $wpdb;
        $base = min( $user_ids );
        for ( $i = 0; $i < $count; $i++ ) {
            $wpdb->insert( $votes_table, array(
                'user_id'     => $base + $i,
                'target_type' => 'thread',
                'target_id'   => $thread_id,
                'vote_type'   => 1,
            ) );
        }
    }
}
