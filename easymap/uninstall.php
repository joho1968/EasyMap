<?php
/**
 * EasyMap is uninstalled.
 *
 * @link              https://code.webbplatsen.net/wordpress/easymap/
 * @since             1.0.0
 * @package           EasyMap
 * @author            Joaquim Homrighausen <joho@webbplatsen.se>
 *
 * uninstall.php
 * Copyright 2021-2025 Joaquim Homrighausen; all rights reserved.
 * Development sponsored by WebbPlatsen i Sverige AB, www.webbplatsen.se
 *
 * This file is part of EasyMap. EasyMap is free software.
 *
 * You may redistribute it and/or modify it under the terms of the
 * GNU General Public License version 2, as published by the Free Software
 * Foundation.
 *
 * EasyMap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with the EasyMap package. If not, write to:
 *  The Free Software Foundation, Inc.,
 *  51 Franklin Street, Fifth Floor
 *  Boston, MA  02110-1301, USA.
 */

//  define( 'EASYMAP_UNINSTALL_TRACE', true );

defined( 'ABSPATH' ) || die( '-1' );
// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    if ( defined( 'EASYMAP_UNINSTALL_TRACE' ) ) {
        error_log( 'easymap-uninstall: init, WP_UNINSTALL_PLUGIN not defined' );
    }
    exit;
}

/**
 * We don't check these anymore.
 * https://developer.wordpress.org/plugins/plugin-basics/uninstall-methods/
 */
/*
// If action is not to uninstall, then exit
if ( empty( $_REQUEST['action'] ) || $_REQUEST['action'] !== 'delete-plugin' ) {
    if ( defined( 'EASYMAP_UNINSTALL_TRACE' ) ) {
        error_log( 'easymap-uninstall: REQUEST["action"] is not delete-plugin' );
    }
    exit;
}
// If it's not us, then exit
if ( empty( $_REQUEST['slug'] ) || $_REQUEST['slug'] !== 'easymap' ) {
    if ( defined( 'EASYMAP_UNINSTALL_TRACE' ) ) {
        error_log( 'easymap-uninstall: REQUEST["slug"] is not easymap' );
    }
    exit;
}
// If we shouldn't do this, then exit
if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'delete_plugins' ) ) {
    if ( defined( 'EASYMAP_UNINSTALL_TRACE' ) ) {
        error_log( 'easymap-uninstall: User is not allowed to manage/uninstall plugins' );
    }
    exit;
}
*/

// Figure out if an uninstall should remove plugin settings
$remove_settings = get_option( 'easymap-settings-remove', '0' );

if ( $remove_settings == '1' ) {
    if ( defined( 'EASYMAP_UNINSTALL_TRACE' ) ) {
        error_log( 'easymap-uninstall: uninstalling' );
    }
    delete_option( 'easymap-google-disable-notifications'   );
    delete_option( 'easymap-google-geodata-api-key'         );
    /*delete_option( 'easymap-google-default-marker-color'  );*/
    delete_option( 'easymap-google-language'                );
    delete_option( 'easymap-google-region'                  );
    delete_option( 'easymap-google-country'                 );
    delete_option( 'easymap-google-start-lat'               );
    delete_option( 'easymap-google-start-lng'               );
    delete_option( 'easymap-google-start-zoom'              );
    delete_option( 'easymap-google-show-poi'                );
    delete_option( 'easymap-google-show-transit'            );
    delete_option( 'easymap-google-show-landscape'          );
    delete_option( 'easymap-google-show-typeselector'       );
    delete_option( 'easymap-google-marker-animation'        );
    delete_option( 'easymap-google-marker-hovereffect'      );
    delete_option( 'easymap-google-marker-address-template' );
    delete_option( 'easymap-google-marker-address-data'     );
    delete_option( 'easymap-settings-remove'                );
    if ( defined( 'EASYMAP_UNINSTALL_TRACE' ) ) {
        error_log( 'easymap-uninstall: ' . __FUNCTION__ . ' end' );
    }
} else {
    if ( defined( 'EASYMAP_UNINSTALL_TRACE' ) ) {
        error_log( 'easymap-uninstall: $remove_settings = ' . var_export( $remove_settings, true ) );
    }
}
