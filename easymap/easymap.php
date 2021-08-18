<?php
/**
 * EasyMap
 *
 * @link              https://code.webbplatsen.net/wordpress/easymap/
 * @since             1.0.0
 * @package           EasyMap
 * @author            Joaquim Homrighausen <joho@webbplatsen.se>
 *
 * @wordpress-plugin
 * Plugin Name:       EasyMap
 * Plugin URI:        https://code.webbplatsen.net/wordpress/easymap/
 * Description:       Uncomplicated map functionality for WordPress
 * Version:           1.1.0
 * Author:            WebbPlatsen, Joaquim Homrighausen <joho@webbplatsen.se>
 * Author URI:        https://webbplatsen.se/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       easymap
 * Domain Path:       /languages
 *
 * easymap.php
 * Copyright (C) 2021 Joaquim Homrighausen; all rights reserved.
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
namespace easymap;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
if ( ! defined( 'ABSPATH' ) ) {
    die( '-1' );
}

define( 'EASYMAP_WORDPRESS_PLUGIN',        true                    );
define( 'EASYMAP_VERSION',                 '1.1.0'                 );
define( 'EASYMAP_REV',                     1                       );
define( 'EASYMAP_PLUGINNAME_HUMAN',        'EasyMap'               );
define( 'EASYMAP_PLUGINNAME_SLUG',         'easymap'               );
define( 'EASYMAP_DEFAULT_PREFIX',          'easymap'               );
define( 'EASYMAP_DB_VERSION',              1                       );
define( 'EASYMAP_ICONSTYLE_DASHICONS',     0 /* default */         );
define( 'EASYMAP_ICONSTYLE_FA',            1                       );
define( 'EASYMAP_DEBUG',                   false                   );
define( 'EASYMAP_DEBUG_OPTIONS',           false                   );
define( 'EASYMAP_LOCATION_LIST_PAGE_NAME', 'easymap-locations'     );
define( 'EASYMAP_DEFAULT_MARKER_COLOR',    '#ffa31a'               );
define( 'EASYMAP_ADDRESS_TEMPLATE_EU',     1                       );
define( 'EASYMAP_ADDRESS_TEMPLATE_UK',     2                       );
define( 'EASYMAP_ADDRESS_TEMPLATE_US',     3                       );
define( 'EASYMAP_ADDRESS_TEMPLATE_CUSTOM', 4                       );

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
require_once( plugin_dir_path( __FILE__ ) . 'include/class_easymap_util.inc.php' );

class EasyMap {
    public static $instance = null;
    protected $Utility;                                        // @since 1.0.1
    protected $easymap_plugin_version;                         // @since 1.0.0
    protected $easymap_have_scfa;                              // @since 1.0.0
    protected int $easymap_icon_style;                         // @since 1.0.0
    protected $easymap_locale;                                 // @since 1.0.0
    protected $easymap_tz_string;                              // @since 1.0.0
    protected $easymap_nonce;                                  // @since 1.0.0
    protected $easymap_ajax_url;                               // @since 1.0.0
    protected $easymap_did_map_shortcode;                      // @since 1.0.0
    protected $easymap_templates;                              // @since 1.0.0
    protected $easymap_form_tab;                               // @since 1.0.0
    protected $EasyMap_Templates_ID;                           // @since 1.0.1

    protected $easymap_location_list;                          // @since 1.0.0
    protected $easymap_google_disable_notifications;           // @since 1.0.0
    protected $easymap_google_geodata_api_key;                 // @since 1.0.0
    protected $easymap_google_default_marker_color;            // @since 1.0.0

    protected $easymap_google_language;                        // @since 1.0.0
    protected $easymap_google_region;                          // @since 1.0.0
    protected $easymap_google_country;                         // @since 1.0.0
    protected $easymap_google_start_lng;                       // @since 1.0.0
    protected $easymap_google_start_lat;                       // @since 1.0.0
    protected $easymap_google_start_zoom;                      // @since 1.0.0
    protected $easymap_google_show_poi;                        // @since 1.0.0
    protected $easymap_google_show_transit;                    // @since 1.0.0
    protected $easymap_google_show_landscape;                  // @since 1.0.0
    protected $easymap_google_show_typeselector;               // @since 1.0.0
    protected $easymap_google_show_fullscreen;                 // @since 1.1.0
    protected $easymap_google_show_streetview;                 // @since 1.1.0
    protected $easymap_google_greedy_control;                  // @since 1.1.0
    protected $easymap_google_marker_animation;                // @since 1.0.0
    protected $easymap_google_marker_hovereffect;              // @since 1.0.0
    protected $easymap_settings_remove;                        // @since 1.0.0
    protected $easymap_google_marker_address_template;         // @since 1.0.0
    protected $easymap_google_marker_address_data;             // @since 1.0.0

    public static function getInstance( string $version = '' )
    {
        null === self::$instance AND self::$instance = new self( $version );
        return( self::$instance );
    }
    /**
     * Start me up ...
     */
    public function __construct( string $version = '' ) {
        if ( defined( 'EASYMAP_DEBUG' ) && EASYMAP_DEBUG ) {
            if ( ! empty( $_POST)) {
                error_log( basename(__FILE__) . ' (' . __FUNCTION__ . '): POST' . "\n" . var_export( $_POST, true ) );
            }
            if ( ! empty( $_REQUEST)) {
                error_log( basename(__FILE__) . ' (' . __FUNCTION__ . '): REQUEST' . "\n" . var_export( $_REQUEST, true ) );
            }
        }
        if ( empty( $version ) ) {
            if ( defined( 'EASYMAP_VERSION' ) ) {
                $this->easymap_plugin_version = EASYMAP_VERSION;
            } else {
                $this->easymap_plugin_version = '1.0.0';
            }
        } else {
            $this->easymap_plugin_version = $version;
        }
        // Our templates
        $this->EasyMap_Templates_ID = array(
            EASYMAP_ADDRESS_TEMPLATE_EU,
            EASYMAP_ADDRESS_TEMPLATE_UK,
            EASYMAP_ADDRESS_TEMPLATE_US,
            EASYMAP_ADDRESS_TEMPLATE_CUSTOM
            );
        // Utilities
        $this->Utility = EasyMap_Utility::getInstance();
        if ( ! is_object( $this->Utility ) ) {
            error_log( basename(__FILE__) . ' (' . __FUNCTION__ . '): Unable to create $Utility instance (?)' );
        }
        // Try to retain our last used tab. This is lost since WordPress
        // actually calls the constructor twice on an options.php form page
        if ( ! empty( $_POST['easymap-form-tab'] ) ) {
            $this->easymap_form_tab = sanitize_key( $_POST['easymap-form-tab'] );
            update_option( 'easymap-form-tab', $this->easymap_form_tab );
        } else {
            $this->easymap_form_tab = get_option( 'easymap-form-tab', null );
            if ( $this->easymap_form_tab === null ) {
                $this->easymap_form_tab = '';
            }
        }
        // No shortcode handled at this point
        $easymap_did_map_shortcode = false;
        // Figure out our locale
        $this->easymap_tz_string = get_option( 'timezone_string', '!*!' );
        $wp_charset = get_bloginfo( 'charset' );
        if ( empty( $wp_charset ) ) {
            $wp_charset = 'UTF-8';
        }
        $wp_lang = get_bloginfo( 'language' );
        if ( empty( $wp_lang ) ) {
            $wp_lang = 'en_US';
        }
        $this->easymap_locale = $wp_lang . '.' . $wp_charset;

        // Make sure we notify about missing mbstring
        if ( ! $this->Utility->x_have_mbstring() ) {
            add_action( 'admin_notices', [$this, 'easymap_admin_alert_missing_mbstring'] );
        }
        // See if Shortcodes for Font Awesome (SCFA) is present
        if ( is_plugin_active( 'shortcodes-for-font-awesome/scfa.php' ) ) {
            if ( defined( 'EASYMAP_DEBUG' ) && EASYMAP_DEBUG ) {
                error_log( basename(__FILE__) . ' (' . __FUNCTION__ . '): Using FontAwesome (SCFA)' );
            }
            $this->easymap_have_scfa = true;
            $this->easymap_icon_style = (int)EASYMAP_ICONSTYLE_FA;
        } else {
            if ( defined( 'EASYMAP_DEBUG' ) && EASYMAP_DEBUG ) {
                error_log( basename(__FILE__) . ' (' . __FUNCTION__ . '): Using DashIcons' );
            }
            $this->easymap_have_scfa = false;
            $this->easymap_icon_style = (int)EASYMAP_ICONSTYLE_DASHICONS;
        }
        /*
        $this->easymap_have_scfa = false;
        $this->easymap_icon_style = (int)EASYMAP_ICONSTYLE_DASHICONS;
        */
        // Configuration
        // Fetch options and setup defaults
        // ..Location list (stored as WordPress option)
        $this->easymap_location_list = @ json_decode( get_option( 'easymap-location-list', null ), true, 3 );
        if ( ! is_array( $this->easymap_location_list ) || empty( $this->easymap_location_list ) ) {
            if ( defined( 'EASYMAP_DEBUG' ) && EASYMAP_DEBUG ) {
                error_log( basename(__FILE__) . ' (' . __FUNCTION__ . '): Re-initializing location list' );
            }
            $ainit = array();
            $brec = $this->easymap_init_location( false );
            for ( $i = 1; $i < 201; $i++ ) {
                $brec['id'] = $i;
                $ainit[$i] = $brec;
            }
            $this->easymap_location_list = $ainit;
            update_option( 'easymap-location-list', json_encode( $this->easymap_location_list ) );
        }
        // ..Google Maps
        $this->easymap_google_disable_notifications = get_option( 'easymap-google-disable-notifications', null );
        if ( $this->easymap_google_disable_notifications === null || ! $this->easymap_google_disable_notifications ) {
            if ( $this->easymap_google_disable_notifications === null ) {
                update_option( 'easymap-google-disable-notifications', 0 );
            }
            $this->easymap_google_disable_notifications = false;
        } else {
            $this->easymap_google_disable_notifications = true;
        }
        $this->easymap_google_geodata_api_key = get_option( 'easymap-google-geodata-api-key', null );
        if ( $this->easymap_google_geodata_api_key === null ) {
            $this->easymap_google_geodata_api_key = '';
        } else {
            $this->easymap_google_geodata_api_key = sanitize_text_field( $this->easymap_google_geodata_api_key );
        }
        if ( empty( $this->easymap_google_geodata_api_key ) && ! $this->easymap_google_disable_notifications ) {
            add_action( 'admin_notices', [$this, 'easymap_admin_alert_missing_google_geodata_apikey'] );
        }
        $this->easymap_google_default_marker_color = get_option( 'easymap-google-default-marker-color', null );
        if ( $this->easymap_google_default_marker_color === null || empty( $this->easymap_google_default_marker_color ) ) {
            $this->easymap_google_default_marker_color = EASYMAP_DEFAULT_MARKER_COLOR;
        }
        $this->easymap_google_language = get_option( 'easymap-google-language', null );
        if ( $this->easymap_google_language === null || empty( $this->easymap_google_language ) ) {
            $this->easymap_google_language = $wp_lang;
            update_option( 'easymap-google-language', $this->easymap_google_language );
        }
        $this->easymap_google_region = get_option( 'easymap-google-region', null );
        if ( $this->easymap_google_region === null || empty( $this->easymap_google_region ) ) {
            $this->easymap_google_region = 'EU';
            update_option( 'easymap-google-region', $this->easymap_google_region );
        }
        $this->easymap_google_country = get_option( 'easymap-google-country', null );
        if ( $this->easymap_google_region === null || empty( $this->easymap_google_region ) ) {
            $this->easymap_google_region = '';
        }
        $this->easymap_google_start_lng = get_option( 'easymap-google-start-lng', null );
        if ( $this->easymap_google_start_lng === null || empty( $this->easymap_google_start_lng ) ) {
            $this->easymap_google_start_lng = '';
        }
        $this->easymap_google_start_lat = get_option( 'easymap-google-start-lat', null );
        if ( $this->easymap_google_start_lat === null || empty( $this->easymap_google_start_lat ) ) {
            $this->easymap_google_start_lat = '';
        }
        $this->easymap_google_start_zoom = get_option( 'easymap-google-start-zoom', null );
        $initial_zoom = $this->easymap_google_start_zoom;
        if ( $this->easymap_google_start_zoom === null ) {
            $this->easymap_google_start_zoom = '';
        } elseif ( (int)$this->easymap_google_start_zoom > 18 ) {
            $this->easymap_google_start_zoom = 18;
        } elseif ( (int)$this->easymap_google_start_zoom < 1 ) {
            $this->easymap_google_start_zoom = '';
        }
        if ( $this->easymap_google_start_zoom != $initial_zoom ) {
            update_option( 'easymap-start-zoom', $this->easymap_google_start_zoom );
        }
        $this->easymap_google_show_poi = get_option( 'easymap-google-show-poi', null );
        if ( $this->easymap_google_show_poi === null || ! $this->easymap_google_show_poi ) {
            $this->easymap_google_show_poi = false;
        } else {
            $this->easymap_google_show_poi = true;
        }
        $this->easymap_google_show_transit = get_option( 'easymap-google-show-transit', null );
        if ( $this->easymap_google_show_transit === null || ! $this->easymap_google_show_transit ) {
            $this->easymap_google_show_transit = false;
        } else {
            $this->easymap_google_show_transit = true;
        }
        $this->easymap_google_show_landscape = get_option( 'easymap-google-show-landscape', null );
        if ( $this->easymap_google_show_landscape === null || ! $this->easymap_google_show_landscape ) {
            $this->easymap_google_show_landscape = false;
        } else {
            $this->easymap_google_show_landscape = true;
        }
        $this->easymap_google_show_typeselector = get_option( 'easymap-google-show-typeselector', null );
        if ( $this->easymap_google_show_typeselector === null || ! $this->easymap_google_show_typeselector ) {
            $this->easymap_google_show_typeselector = false;
        } else {
            $this->easymap_google_show_typeselector = true;
        }
        $this->easymap_google_show_fullscreen = get_option( 'easymap-google-show-fullscreen', null );
        if ( $this->easymap_google_show_fullscreen === null || ! $this->easymap_google_show_fullscreen ) {
            $this->easymap_google_show_fullscreen = false;
        } else {
            $this->easymap_google_show_fullscreen = true;
        }
        $this->easymap_google_show_streetview = get_option( 'easymap-google-show-streetview', null );
        if ( $this->easymap_google_show_streetview === null || ! $this->easymap_google_show_streetview ) {
            $this->easymap_google_show_streetview = false;
        } else {
            $this->easymap_google_show_streetview = true;
        }
        $this->easymap_google_greedy_control = get_option( 'easymap-google-greedy-control', null );
        if ( $this->easymap_google_greedy_control === null || ! $this->easymap_google_greedy_control ) {
            $this->easymap_google_greedy_control = false;
        } else {
            $this->easymap_google_greedy_control = true;
        }
        $this->easymap_google_marker_animation = get_option( 'easymap-google-marker-animation', null );
        if ( $this->easymap_google_marker_animation === null || ! $this->easymap_google_marker_animation ) {
            $this->easymap_google_marker_animation = false;
        } else {
            $this->easymap_google_marker_animation = true;
        }
        $this->easymap_google_marker_hovereffect = get_option( 'easymap-google-marker-hovereffect', null );
        if ( $this->easymap_google_marker_hovereffect === null || ! $this->easymap_google_marker_hovereffect ) {
            $this->easymap_google_marker_hovereffect = false;
        } else {
            $this->easymap_google_marker_hovereffect = true;
        }
        $this->easymap_settings_remove = get_option( 'easymap-settings-remove', null );
        if ( $this->easymap_settings_remove === null || ! $this->easymap_settings_remove ) {
            $this->easymap_settings_remove = false;
        } else {
            $this->easymap_settings_remove = true;
        }
        $this->easymap_google_marker_address_template = get_option( 'easymap-google-marker-address-template', null );
        if ( $this->easymap_google_marker_address_template === null || ! $this->easymap_google_marker_address_template ) {
            if ( defined( 'EASYMAP_DEBUG' ) && EASYMAP_DEBUG ) {
                error_log( basename(__FILE__) . ' (' . __FUNCTION__ . '): No template set, using EU as default' );
            }
            $this->easymap_google_marker_address_template = EASYMAP_ADDRESS_TEMPLATE_EU;
            update_option( 'easymap-google-marker-address-template', $this->easymap_google_marker_address_template );
        }
        $this->easymap_google_marker_address_data = get_option( 'easymap-google-marker-address-data', null );
        if ( $this->easymap_google_marker_address_data === null || empty( $this->easymap_google_marker_address_data ) ) {
            if ( defined( 'EASYMAP_DEBUG' ) && EASYMAP_DEBUG ) {
                error_log( basename(__FILE__) . ' (' . __FUNCTION__ . '): Empty custom template, copying from current' );
            }
            $this->easymap_google_marker_address_data = $this->easymap_template_to_data( (int)$this->easymap_google_marker_address_template );
            update_option( 'easymap-google-marker-address-data', $this->easymap_google_marker_address_data );
        }
        // Setup useful array with template IDs and names
        $this->easymap_templates = array();
        foreach( $this->EasyMap_Templates_ID as $k => $v) {
            $template_data = $this->easymap_template_to_data( $v );
            switch( $v ) {
                case EASYMAP_ADDRESS_TEMPLATE_EU:
                case EASYMAP_ADDRESS_TEMPLATE_UK:
                case EASYMAP_ADDRESS_TEMPLATE_US:
                    $template_name = __( 'Address template', 'easymap' ) . ' #' . $v;
                    break;
                case EASYMAP_ADDRESS_TEMPLATE_CUSTOM:
                    $template_name = __( 'Custom template', 'easymap' );
                    break;
            }// switch
            $this->easymap_templates[$v] = array(
                'id' => $v,
                'name' => $template_name,
                'data' => $template_data,
            );
        }
        // Add 'Settings' link in plugin list
        add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [$this, 'easymap_settings_link']);
    }
    /**
     * Create empty location record.
     *
     * Create empty location record with reasonable value, possibly apply defaults.
     *
     * @since 1.0.0
     */
    protected function easymap_init_location( $add_defaults ) : array {
        $arec = array( 'id'  => -1,
                       'al'  => '',   // Alias
                       'na'  => '',   // Name
                       'co'  => '',   // Color (#hhhhhh)
                       'sa'  => '',   // Street address
                       'sn'  => '',   // Street number
                       'ci'  => '',   // City
                       'st'  => '',   // State
                       'pc'  => '',   // Postal code
                       'ph'  => '',   // Phone
                       'em'  => '',   // E-mail
                       'ws'  => '',   // Website
                       'no'  => '',   // Notes
                       'la'  => '',   // Latitude
                       'lo'  => '',   // Longitude
                       'ma'  => '',   // Marker (alias/id)
                       'ac'  => false,// Active
                       );
        if ( $add_defaults ) {
            $arec['co'] = $this->easymap_google_default_marker_color;
            $arec['ac'] = true;
        }
        return( $arec );
    }
    /**
     * Setup template data based on template type.
     *
     * @since 1.0.0
     */
    protected function easymap_template_to_data( int $template_type ) : string {
        switch( $template_type ) {
            case EASYMAP_ADDRESS_TEMPLATE_EU:
                $rs = '{streetname} {streetnumber}{br}' .
                      '{postalcode} {city}{br}' .
                      '{state}';
                break;
            case EASYMAP_ADDRESS_TEMPLATE_UK:
                $rs = '{streetnumber} {streetname}{br}' .
                      '{city}{br}' .
                      '{state}{br}' .
                      '{postalcode}';
                break;
            case EASYMAP_ADDRESS_TEMPLATE_US:
                $rs = '{streetnumber} {streetname}{br}' .
                      '{city}, {state} {postalcode}';
                break;
            case EASYMAP_ADDRESS_TEMPLATE_CUSTOM:
                if ( empty( $this->easymap_google_marker_address_data ) ) {
                    $rs = '';
                } else {
                    $rs = $this->easymap_google_marker_address_data;
                }
                if ( is_array( $rs ) ) {
                    $as = $rs;
                    $rs = '';
                    foreach( $as as $k => $v ) {
                        $rs .= $v;
                    }
                }
                break;
        }// switch
        return( $rs );

    }
    /**
     * Add link to EasyMap settings in plugin list.
     *
     * @since 1.0.0
     */
    public function easymap_settings_link( array $links ) {
        $our_link = '<a href ="' . esc_url( admin_url('admin.php') . '?page=' . 'easymap' ) . '">' .
                                   esc_html__( 'Settings ', 'easymap' ) . '</a>';
        array_unshift( $links, $our_link );
        return ( $links );
    }
    /**
     * Display admin alerts.
     *
     * Display various admin alerts like missing configuration options, etc.
     *
     * @since 1.0.0
     */
    public function easymap_admin_alert_missing_mbstring() {
        echo '<div class="notice notice-error"><br/>'.
             '<p>' . $this->easymap_make_icon_html( 'errornotice' ) . '&nbsp;' .
             EASYMAP_PLUGINNAME_HUMAN . ': ' .
             esc_html__( 'mbstring-extensions are missing, contact server administrator to enable them', 'easymap' ) .
             '!' .
             '<br/><br/></p>';
        echo '</div>';
    }
    public function easymap_admin_alert_missing_google_geodata_apikey() {
        echo '<div class="notice notice-error"><br/>'.
             '<p>' . $this->easymap_make_icon_html( 'errornotice' ) . '&nbsp;' .
             EASYMAP_PLUGINNAME_HUMAN . ': ' .
             esc_html__( 'Please configure an API key to be used for Google Geodata services', 'easymap' ) .
             '!' .
             '<br/><br/></p>';
        echo '</div>';
    }
    /**
     * Fetch filemtime() of file and return it.
     *
     * Fetch filemtime() of $filename and return it, upon error, plugin_version
     * is returned instead. This could possibly simply return plugin_version in
     * production.
     *
     * @since  1.0.0
     * @param  string $filename The file for which we want filemtime()
     * @return string
     */
    protected function easymap_resource_mtime( $filename ) {
        $filetime = @ filemtime( $filename );
        if ( $filetime === false ) {
            if ( defined( 'EASYMAP_DEBUG' ) && EASYMAP_DEBUG ) {
                error_log( basename(__FILE__) . ' (' . __FUNCTION__ . '): Failed to get mtime for "' . $filename . '"' );
            }
            $filetime = $this->easymap_plugin_version;
        }
        return ( $filetime );
    }
    /**
     * Setup admin CSS
     *
     * @since 1.0.0
     */
    public function easymap_setup_admin_css() {
        wp_enqueue_style( EASYMAP_PLUGINNAME_SLUG, plugin_dir_url( __FILE__ ) . 'css/easymap-admin.css',
                          array(),
                          $this->easymap_resource_mtime( dirname(__FILE__) . '/css/easymap-admin.css' ), 'all' );
        wp_enqueue_script( EASYMAP_PLUGINNAME_SLUG,
                           plugin_dir_url( __FILE__ ) . 'js/easymap-admin.js',
                           array(),
                           $this->easymap_resource_mtime( dirname( __FILE__ ) . '/js/easymap-admin.js' ),
                           false );
    }
    /**
     * Setup public CSS
     *
     * @since 1.0.0
     */
    public function easymap_setup_public_css() {
        wp_enqueue_style( EASYMAP_PLUGINNAME_SLUG, plugin_dir_url( __FILE__ ) . 'css/easymap-public.css',
                          array(),
                          $this->easymap_resource_mtime( dirname(__FILE__).'/css/easymap-public.css' ),
                          'all' );
        if ( @ filesize( dirname( __FILE__ ) . '/css/easymap-public-custom.css' ) !== false ) {
            // Enqueue custom CSS for front-end if it exists and size >0
            wp_enqueue_style( EASYMAP_PLUGINNAME_SLUG . '-Custom', plugin_dir_url( __FILE__ ) . 'css/easymap-public-custom.css',
                              array( EASYMAP_PLUGINNAME_SLUG ),
                              $this->easymap_resource_mtime( dirname( __FILE__ ) . '/css/easymap-public-custom.css' ),
                              'all' );
        } elseif ( defined( 'EASYMAP_DEBUG' ) && EASYMAP_DEBUG ) {
            error_log( basename(__FILE__) . ' (' . __FUNCTION__ . '): Not loading custom CSS, not found or zero size' );
        }
    }
    /**
     * Generate HTML for inline icons
     *
     * Generate HTML for inline icons. We give them "human aliases" here, and
     * generate the appropriate output based on settings such as if Font Awesome
     * is to be used, etc.
     *
     * @since    1.0.0
     *
     * @param    string $icon_name The icon's "human" name
     * @param    string $add_class Additional class="" we want to add
     * @param    string $add_title title="" we want to add
     *
     * @return   string
     */
    protected function easymap_make_icon_html( string $icon_name, string $add_class = '', string $add_title = '' ) {
        if ( ! empty( $add_class ) ) {
            $add_class = ' ' . trim( $add_class ) . '"';
        } else {
            $add_class = '"';
        }
        if ( ! empty( $add_title ) ) {
            $add_title = ' title="' . esc_html( trim( $add_title ) ) . '"';
        }
        switch( $icon_name ) {
            case 'itemchecked':// Not technically an icon here
                switch( $this->easymap_icon_style ) {
                    case EASYMAP_ICONSTYLE_FA: $html = 'fas fa-check-square'; break;
                    default: $html = 'dashicons dashicons-cloud-saved'; break;
                }
                break;
            case 'itemunchecked':// Not technically an icon here
                switch( $this->easymap_icon_style ) {
                    case EASYMAP_ICONSTYLE_FA: $html = 'far fa-square'; break;
                    default: $html = 'dashicons dashicons-cloud'; break;
                }
                break;
            case 'errornotice':
                switch( $this->easymap_icon_style ) {
                    case EASYMAP_ICONSTYLE_FA: $html = '<span class="fas fa-exclamation-triangle' . $add_class . $add_title . '></span>'; break;
                    default: $html = '<span class="dashicons dashicons-flag' . $add_class . $add_title . ' style="font-size:24px;"></span>'; break;
                }
                break;
            case 'appicon':
                switch( $this->easymap_icon_style ) {
                    case EASYMAP_ICONSTYLE_FA: $html = '<span class="fas fa-map-marked-alt' . $add_class . $add_title . '></span>'; break;
                    default: $html = '<span class="dashicons dashicons-location-alt' . $add_class . $add_title . ' style="font-size:30px;"></span>'; break;
                }
                break;
            case 'mapmarker':
                switch( $this->easymap_icon_style ) {
                    case EASYMAP_ICONSTYLE_FA: $html = '<span class="fas fa-map-marker' . $add_class . $add_title . '></span>'; break;
                    default: $html = '<span class="dashicons dashicons-location' . $add_class . $add_title . ' style="font-size:30px;"></span>'; break;
                }
                break;
            case 'homefolder':
                switch( $this->easymap_icon_style ) {
                    case EASYMAP_ICONSTYLE_FA: $html = '<span class="fas fa-home' . $add_class . $add_title . '></span>'; break;
                    default: $html = '<span class="dashicons dashicons-admin-home' . $add_class . $add_title . '></span>'; break;
                }
                break;
            case 'upfolder':
                switch( $this->easymap_icon_style ) {
                    case EASYMAP_ICONSTYLE_FA: $html = '<span class="fas fa-arrow-up' . $add_class . $add_title . '></span>'; break;
                    default: $html = '<span class="dashicons dashicons-arrow-up-alt2' . $add_class . $add_title . '></span>'; break;
                }
                break;
            case 'folder':
                switch( $this->easymap_icon_style ) {
                    case EASYMAP_ICONSTYLE_FA: $html = '<span class="fas fa-folder' . $add_class . $add_title . '></span>'; break;
                    default: $html = '<span class="dashicons dashicons-open-folder' . $add_class . $add_title . '></span>'; break;
                }
                break;
            case 'trashfile':
                switch( $this->easymap_icon_style ) {
                    case EASYMAP_ICONSTYLE_FA: $html = '<span class="fas fa-trash-alt fa-xs' . $add_class . $add_title . '></span>'; break;
                    default: $html = '<span class="dashicons dashicons-trash' . $add_class . $add_title . '></span>'; break;
                }
                break;
            case 'copy':
                switch( $this->easymap_icon_style ) {
                    case EASYMAP_ICONSTYLE_FA: $html = '<span class="far fa-copy' . $add_class . $add_title . ' style="vertical-align:middle;"></span>'; break;
                    default: $html = '<span class="dashicons dashicons-admin-page' . $add_class . $add_title . ' style="vertical-align:middle;"></span>'; break;
                }
                break;
            case 'greencheck':
                switch( $this->easymap_icon_style ) {
                    case EASYMAP_ICONSTYLE_FA: $html = '<span class="fas fa-check' . $add_class . $add_title . ' style="font-size:14px;margin-left:4px;vertical-align:middle;color:green;"></span>'; break;
                    default: $html = '<span class="dashicons dashicons-yes' . $add_class . $add_title . ' style="font-size:20px;vertical-align:middle;color:green;"></span>'; break;
                }
                break;
             default:
                $html = '';
                break;
        }
        return( $html );
    }
    /**
     * Output plugin title, with possible add-on
     *
     * @since    1.0.0
     */
    public function easymap_page_header( string $add_on_string = '', bool $return_html = false) {
        $html = '<h2>' . $this->easymap_make_icon_html( 'appicon' ). '&nbsp;' . esc_html( EASYMAP_PLUGINNAME_HUMAN );
        if ( strlen( $add_on_string ) > 0 ) {
            $html .= ':&nbsp;' . $add_on_string;
        }
        $html .= '</h2>';
        if ( $return_html ) {
            return( $html );
        }
        echo $html;
    }
    /**
     * Setup WordPress admin menu.
     *
     * Create menu entry for WordPress, only if 'administrator' role.
     *
     * @since  1.0.0
     */
    public function easymap_menu() {
        if ( ! is_admin( ) || ! is_user_logged_in() || ! current_user_can( 'administrator' ) )  {
            return;
        }
        // Add our menu entry (stand-alone menu)
        add_menu_page( esc_html__( EASYMAP_PLUGINNAME_HUMAN, 'easymap' ),
                       esc_html__( EASYMAP_PLUGINNAME_HUMAN, 'easymap' ),
                       'manage_options',
                       EASYMAP_PLUGINNAME_SLUG,
                       [ $this, 'easymap_admin_page' ],
                       'dashicons-location-alt'
                       // $position
                       //
                     );
        // The first sub-menu page is a "duplicate" of the parent, because ...
        add_submenu_page ( EASYMAP_PLUGINNAME_SLUG,
                           esc_html__( EASYMAP_PLUGINNAME_HUMAN, 'easymap' ),
                           esc_html__( 'Settings', 'easymap' ),
                           'manage_options',
                           EASYMAP_PLUGINNAME_SLUG,
                           [ $this, 'easymap_admin_page'] );
        // Add actual sub-menu items
        add_submenu_page ( EASYMAP_PLUGINNAME_SLUG,
                           esc_html__( EASYMAP_PLUGINNAME_HUMAN, 'easymap' ) . ' - '.esc_html__( 'Locations', 'easymap' ),
                           esc_html__( 'Locations', 'easymap' ),
                           'manage_options',
                           EASYMAP_LOCATION_LIST_PAGE_NAME,
                           [ $this, 'easymap_admin_locations'] );
        add_submenu_page ( EASYMAP_PLUGINNAME_SLUG,
                           esc_html__( EASYMAP_PLUGINNAME_HUMAN, 'easymap' ) . ' - '.esc_html__( 'Export various data', 'easymap' ),
                           esc_html__( 'Export', 'easymap' ),
                           'manage_options',
                           EASYMAP_PLUGINNAME_SLUG. '-export',
                           [ $this, 'easymap_admin_export'] );
        add_submenu_page ( EASYMAP_PLUGINNAME_SLUG,
                           esc_html__( EASYMAP_PLUGINNAME_HUMAN, 'easymap' ) . ' - '.esc_html__( 'Import external data', 'easymap' ),
                           esc_html__( 'Import', 'easymap' ),
                           'manage_options',
                           EASYMAP_PLUGINNAME_SLUG. '-import',
                           [ $this, 'easymap_admin_import'] );
    }
    /**
     * Setup WordPress admin options page.
     *
     * Create menu entry for WordPress, only if 'administrator' role.
     *
     * @since  1.0.0
     */
    public function easymap_admin_page() {
        if ( ! is_admin( ) || ! is_user_logged_in() || ! current_user_can( 'administrator' ) )  {
            return;
        }
        //
        $html = '
            <script type="text/javascript">
            var easymapAddressSelect;
            const easymapSampleAddress = {sa:"Some street",sn:"68",ci:"Some city",st:"Some state",pc:"12345"};
            const easymapAddressTemplates = [';

        foreach( $this->easymap_templates as $k => $v ) {
            $html .= '{id:' .$v['id'] . ',name:"' .esc_html( $v['name'] ) . '",data:"' . $v['data'] . '"},';
        }
        $html .= '];

            function easymapTemplateDisplay(templateString) {
                let easymap_info_regex = /{[\w+]+}/g ;
                templateLines = templateString.split("{br}");
                let htmlOut = templateLines.join("<br/>");
                document.getElementById("easymap-template-display").innerHTML = htmlOut;
                htmlOut = "";
                templateLines.forEach(function(keyword,index,a) {
                    let matches;
                    let match;
                    let replacement;
                    do {
                        matches = keyword.matchAll(easymap_info_regex);
                        let matchCount = 0;
                        for (match of matches) {
                            matchCount++;
                            switch(match[0]) {
                                case "{streetname}":   replacement = easymapSampleAddress.sa; break;
                                case "{streetnumber}": replacement = easymapSampleAddress.sn; break;
                                case "{city}":         replacement = easymapSampleAddress.ci; break;
                                case "{state}":        replacement = easymapSampleAddress.st; break;
                                case "{postalcode}":   replacement = easymapSampleAddress.pc; break;
                                default:               replacement = ""; break;
                            }
                            let new_keyword = keyword.substring(0, match.index) + replacement + keyword.substring(match.index+match[0].length);
                            keyword = new_keyword;
                            break;
                        }
                        if (matchCount == 0) {
                            break;
                        }
                    } while (matches);
                    htmlOut += keyword + "<br/>";
                });
                document.getElementById("easymap-template-address").innerHTML = htmlOut;
            }
            function easymapTemplateSelect() {
              let selectedTemplate = false;
              for (let template of easymapAddressTemplates) {
                if (template.id == easymapAddressSelect.value) {
                  selectedTemplate = template;
                  break;
                }
              }
              if (selectedTemplate) {
                if (selectedTemplate.id == ' . EASYMAP_ADDRESS_TEMPLATE_CUSTOM . ') {
                  /*Show custom template*/
                  let customTemplate = document.getElementById("easymap-google-marker-address-data");
                  customTemplate.classList.remove("easymap-is-hidden");
                  document.getElementById("easymap-google-marker-address-data-label").classList.remove("easymap-is-hidden");
                  /*Copy current content of custom template*/
                  let template_content = customTemplate.value.split("\n");
                  selectedTemplate.data = template_content.join("{br}");
                } else {
                  /*Hide custom template*/
                  document.getElementById("easymap-google-marker-address-data").classList.add("easymap-is-hidden");
                  document.getElementById("easymap-google-marker-address-data-label").classList.add("easymap-is-hidden");
                }
                easymapTemplateDisplay(selectedTemplate.data);
              }
            }
            function easymapSettingsSetup() {
              easymapAddressSelect = document.getElementById("easymap-google-marker-address-template");
              easymapAddressSelect.addEventListener("change", easymapTemplateSelect);
              easymapTemplateSelect();
              document.getElementById("easymap-google-marker-address-data").addEventListener("keyup", easymapTemplateSelect);
            }
            if (document.readyState === "complete" ||
                  (document.readyState !== "loading" && !document.documentElement.doScroll)) {
              easymapSettingsSetup();
            } else {
              document.addEventListener("DOMContentLoaded", easymapSettingsSetup);
            }
            </script>';

        if ( empty( $this->easymap_form_tab ) ) {
            $this->easymap_form_tab = 'googlemaps';
        }
        // Output configuration options$action
        $tab_header = '<div class="wrap">';
            $tab_header .= '<h1>' . $this->easymap_make_icon_html( 'appicon' ) . '&nbsp;&nbsp;' . esc_html( EASYMAP_PLUGINNAME_HUMAN ) . '</h1>';
            $tab_header .= '<p>' . esc_html__( 'These settings allow general configuration of EasyMap', 'easymap' ) . '</p>';
            $tab_header .= '<nav class="nav-tab-wrapper">';
            $tab_header .= '<a data-toggle="easymap-googlemaps" href="#googlemaps" class="easymap-tab nav-tab">' . esc_html__( 'Google Maps', 'easymap' ) . '</a>';
            $tab_header .= '<a data-toggle="easymap-templates" href="#templates" class="easymap-tab nav-tab">' . esc_html__( 'Templates', 'easymap' ) . '</a>';
            $tab_header .= '<a data-toggle="easymap-shortcode" href="#shortcode" class="easymap-tab nav-tab">[' . esc_html__( 'shortcode', 'easymap' ) . ']</a>';
            $tab_header .= '<a data-toggle="easymap-about" href="#about" class="easymap-tab nav-tab">' . esc_html__( 'About', 'easymap' ) . '</a>';
            $tab_header .= '</nav>';

            $html .= '<form method="post" action="options.php">';
            $html .= '<input type="hidden" name="easymap-form-tab" id="easymap-form-tab" value="' . esc_attr( $this->easymap_form_tab ) . '" />';
            $html .= '<div class="tab-content">';
            $html .= '<div class="easymap-config-header">';
            $html .= '<div id="easymap-googlemaps" class="easymap-tab-content easymap-is-hidden">';
            ob_start();
            settings_fields( 'easymap' );
            echo '<table class="form-table" role="presentation">';
                 do_settings_fields( 'easymap', 'easymap-settings' );
            echo '</table>';
            $html .= ob_get_contents();
            ob_end_clean();
            $html .= '</div>';//easymap-googlemaps
            $html .= '<div id="easymap-templates" class="easymap-tab-content easymap-is-hidden">';
            ob_start();
            settings_fields( 'easymap' );
            echo '<table class="form-table" role="presentation">';
                 do_settings_fields( 'easymap', 'easymap-templates' );
            echo '</table>';
            echo '<div id="easymap-template-sample">';
            echo '<h3>' . esc_html__( 'Sample address output', 'easymap' ) . '</h3>';
            echo '<div id="easymap-template-sample-container">';
            echo '<div id="easymap-template-address">';
            echo 'Sample address';
            echo '</div>';
            echo '<div id="easymap-template-display">';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            $html .= ob_get_contents();
            ob_end_clean();
            $html .= '</div>';//easymap-templates
            $html .= '<div id="easymap-shortcode" class="easymap-tab-content easymap-is-hidden">'.
                     '<p>'  .
                     esc_html__( 'To use the plugin, you create a page or a post in WordPress and add the plugin shortcode', 'easymap' ) . '. ' .
                     esc_html__( 'The shortcode will take its deftault values from the plugin configuration', 'easymap' ) .
                     '<strong> ' . esc_html__( 'The shortcode can be used once on any given page load', 'easymap' ) . '.</strong>' .
                     '.</p>';
            $html .= '<p>' . esc_html__( 'The simplest usage form is', 'easymap' ) . ':' .
                     '<pre>   [easymap_map /]</pre></p>';
            $html .= '<p>' . esc_html__( 'You can also use parameters to override the default behavior', 'easymap' ) . ':' .
                     '<pre>   [easymap_map markers="mydojo,5,8,9" poi="1" transit="0" /]</pre></p>';
            $html .= '<p>' . esc_html__( 'The parameters available are', 'easymap' ) . ':' .
                     '<pre>   markers=alias1,alias4,5,8,12</pre>' .
                     '<pre>   poi=0|1</pre>' .
                     '<pre>   transit=0|1</pre>' .
                     '<pre>   landscape=0|1</pre>' .
                     '<pre>   fullscreen=0|1</pre>' .
                     '<pre>   streetview=0|1</pre>' .
                     '<pre>   greedy=0|1</pre>' .
                     '<pre>   zoom=1-18</pre>';
            $html .= '</div>';// easymap-shortcode
            $html .= '<div id="easymap-about" class="easymap-tab-content easymap-is-hidden">'.
                     '<p>'  . esc_html__( 'Thank you for installing', 'easymap' ) .' ' . EASYMAP_PLUGINNAME_HUMAN . '!' . ' '.
                     esc_html__( 'This WordPress plugin provides geolocation services for WordPress and certain types of service providers', 'easymap' ) .
                     '</p>';
            $html .= '<div class="easymap-config-section">'.
                      '<p>'  . '<img class="easymap-wps-logo" alt="" src="' . plugin_dir_url( __FILE__ ) . 'img/webbplatsen_logo.png" />' .
                               esc_html__( 'Commercial support and customizations for this plugin is available from', 'easymap' ) .
                               ' <a class="easymap-ext-link" href="https://webbplatsen.se" target="_blank">WebbPlatsen i Sverige AB</a> '.
                               esc_html__('in Stockholm, Sweden. We speak Swedish and English', 'easymap' ) . ' :-)' .
                               '<br/><br/>' .
                               esc_html__( 'The plugin is written by Joaquim Homrighausen and sponsored by WebbPlatsen i Sverige AB.', 'easymap' ) .
                      '<br/><br/>' .
                               esc_html__( 'If you find this plugin useful, the author is happy to receive a donation, good review, or just a kind word.', 'easymap' ) . ' ' .
                               esc_html__( 'If there is something you feel to be missing from this plugin, or if you have found a problem with the code or a feature, please do not hesitate to reach out to', 'easymap' ) .
                                           ' <a class="easymap-ext-link" href="mailto:support@webbplatsen.se">support@webbplatsen.se</a>' . ' '.
                               esc_html__( 'There is more documentation available at', 'easymap' ) . ' ' .
                                           '<a class="easymap-ext-link" target="_blank" href="https://code.webbplatsen.net/documentation/easymap/">'.
                                           'code.webbplatsen.net/documentation/easymap/</a>' .
                      '</p>'.
                      '</div>';
            $html .= '</div>';// easymap-about
            ob_start();
            submit_button();
            $html .= ob_get_contents();
            ob_end_clean();
            $html .= '</div>';
            $html .= '</div>'; // tab-content
            $html .= '</form>';
        $html .= '</div>'; // wrap
        //
        echo $tab_header . $html;
    }
    /**
     * Display settings.
     *
     * @since  1.0.0
     */
    public function easymap_settings() {
        if ( defined( 'EASYMAP_DEBUG' ) && EASYMAP_DEBUG ) {
            error_log( basename(__FILE__) . ' (' . __FUNCTION__ . ')' );
        }
        if ( ! is_admin( ) || ! is_user_logged_in() || ! current_user_can( 'administrator' ) )  {
            return;
        }
        add_settings_section( 'easymap-settings', '', false, 'easymap' );
          add_settings_field( 'easymap-google-disable-notifications', esc_html__( 'Disable notifications', 'easymap' ), [$this, 'easymap_setting_google_disable_notifications'], 'easymap', 'easymap-settings', ['label_for' => 'easymap-google-disable-notifications'] );
          add_settings_field( 'easymap-google-geodata-api-key', esc_html__( 'Google Geodata API key', 'easymap' ), [$this, 'easymap_setting_google_geodata_api_key'], 'easymap', 'easymap-settings', ['label_for' => 'easymap-google-geodata-api-key'] );
          add_settings_field( 'easymap-google-language', esc_html__( 'Localization', 'easymap' ), [$this, 'easymap_setting_google_language'], 'easymap', 'easymap-settings', ['label_for' => 'easymap-google-language'] );
          add_settings_field( 'easymap-google-region', esc_html__( 'Region', 'easymap' ), [$this, 'easymap_setting_google_region'], 'easymap', 'easymap-settings', ['label_for' => 'easymap-google-region'] );
          add_settings_field( 'easymap-google-country', esc_html__( 'Country', 'easymap' ), [$this, 'easymap_setting_google_country'], 'easymap', 'easymap-settings', ['label_for' => 'easymap-google-country'] );
          add_settings_field( 'easymap-google-start-lat', esc_html__( 'Starting position', 'easymap' ), [$this, 'easymap_setting_google_start_pos'], 'easymap', 'easymap-settings', ['label_for' => 'easymap-google-start-lat'] );
          add_settings_field( 'easymap-google-start-lng', '', [$this, 'easymap_setting_no_output'], 'easymap', 'easymap-settings', array( 'class' => 'easymap-is-hidden' ) );
          add_settings_field( 'easymap-google-start-zoom', esc_html__( 'Initial zoom', 'easymap' ), [$this, 'easymap_setting_google_start_zoom'], 'easymap', 'easymap-settings', ['label_for' => 'easymap-google-start-zoom'] );
          add_settings_field( 'easymap-google-show-poi', esc_html__( 'Map features', 'easymap' ), [$this, 'easymap_setting_google_features'], 'easymap', 'easymap-settings', ['label_for' => 'easymap-google-show-poi'] );
          add_settings_field( 'easymap-google-show-transit', '', [$this, 'easymap_setting_no_output'], 'easymap', 'easymap-settings', array( 'class' => 'easymap-is-hidden' ) );
          add_settings_field( 'easymap-google-show-landscape', '', [$this, 'easymap_setting_no_output'], 'easymap', 'easymap-settings', array( 'class' => 'easymap-is-hidden' ) );
          add_settings_field( 'easymap-google-show-typeselector', '', [$this, 'easymap_setting_no_output'], 'easymap', 'easymap-settings', array( 'class' => 'easymap-is-hidden' ) );
          add_settings_field( 'easymap-google-show-fullscreen', '', [$this, 'easymap_setting_no_output'], 'easymap', 'easymap-settings', array( 'class' => 'easymap-is-hidden' ) );
          add_settings_field( 'easymap-google-show-streetview', '', [$this, 'easymap_setting_no_output'], 'easymap', 'easymap-settings', array( 'class' => 'easymap-is-hidden' ) );
          add_settings_field( 'easymap-google-greedy-control', '', [$this, 'easymap_setting_no_output'], 'easymap', 'easymap-settings', array( 'class' => 'easymap-is-hidden' ) );
          add_settings_field( 'easymap-google-marker-animation', esc_html__( 'Bouncing markers', 'easymap' ), [$this, 'easymap_setting_google_marker_animation'], 'easymap', 'easymap-settings', ['label_for' => 'easymap-google-marker-animation'] );
          add_settings_field( 'easymap-google-marker-hovereffect', esc_html__( 'Hover color switch', 'easymap' ), [$this, 'easymap_setting_google_marker_hover'], 'easymap', 'easymap-settings', ['label_for' => 'easymap-google-marker-hovereffect'] );
          add_settings_field( 'easymap-settings-remove', esc_html__( 'Remove settings', 'easymap' ), [$this, 'easymap_setting_remove'], 'easymap', 'easymap-settings', ['label_for' => 'easymap-settings-remove'] );
        add_settings_section( 'easymap-templates', '', false, 'easymap' );
          add_settings_field( 'easymap-google-marker-address-template', esc_html__( 'Address template', 'easymap' ), [$this, 'easymap_setting_google_marker_address_template'], 'easymap', 'easymap-templates', ['label_for' => 'easymap-google-marker-address-template'] );
          add_settings_field( 'easymap-google-marker-address-data', esc_html__( 'Custom template data', 'easymap' ), [$this, 'easymap_setting_google_marker_address_data'], 'easymap', 'easymap-templates', ['label_for' => 'easymap-google-marker-address-data'] );
          /*add_settings_field( 'easymap-google-default-marker-color', esc_html__( 'Default Marker color', 'easymap' ), [$this, 'easymap_setting_google_marker_color'], 'easymap', 'easymap-settings', ['label_for' => 'easymap-google-default-marker-color'] );*/
          // add Enable geolocation checkbox
          // add Map containter width,min-width,max-width, three fields, can be empty
          // add Map container height,min-height,max-height, three fields, can be empty
        register_setting( 'easymap', 'easymap-google-disable-notifications' );
        register_setting( 'easymap', 'easymap-google-geodata-api-key', ['type' => 'string', 'sanitize_callback' => [$this, 'easymap_setting_sanitize_google_geodata_api_key']] );
        /*register_setting( 'easymap', 'easymap-google-default-marker-color', ['type' => 'string', 'sanitize_callback' => [$this, 'easymap_setting_sanitize_google_marker_color']] );*/
        register_setting( 'easymap', 'easymap-google-language', ['type' => 'string', 'sanitize_callback' => [$this, 'easymap_setting_sanitize_google_language']] );
        register_setting( 'easymap', 'easymap-google-region', ['type' => 'string', 'sanitize_callback' => [$this, 'easymap_setting_sanitize_google_region']] );
        register_setting( 'easymap', 'easymap-google-country', ['type' => 'string', 'sanitize_callback' => [$this, 'easymap_setting_sanitize_google_country']] );
        register_setting( 'easymap', 'easymap-google-start-lat', ['type' => 'string', 'sanitize_callback' => [$this, 'easymap_setting_sanitize_google_pos']] );
        register_setting( 'easymap', 'easymap-google-start-lng', ['type' => 'string', 'sanitize_callback' => [$this, 'easymap_setting_sanitize_google_pos']] );
        register_setting( 'easymap', 'easymap-google-start-zoom', ['type' => 'string', 'sanitize_callback' => [$this, 'easymap_setting_sanitize_google_zoom']] );
        register_setting( 'easymap', 'easymap-google-show-poi' );
        register_setting( 'easymap', 'easymap-google-show-transit' );
        register_setting( 'easymap', 'easymap-google-show-landscape' );
        register_setting( 'easymap', 'easymap-google-show-typeselector' );
        register_setting( 'easymap', 'easymap-google-show-fullscreen' );
        register_setting( 'easymap', 'easymap-google-show-streetview' );
        register_setting( 'easymap', 'easymap-google-greedy-control' );
        register_setting( 'easymap', 'easymap-google-marker-animation' );
        register_setting( 'easymap', 'easymap-google-marker-hovereffect' );
        register_setting( 'easymap', 'easymap-google-marker-address-template' );
        register_setting( 'easymap', 'easymap-google-marker-address-data', ['type' => 'string', 'sanitize_callback' => [$this, 'easymap_setting_sanitize_custom_template']] );
        register_setting( 'easymap', 'easymap-settings-remove' );
    }
    /**
     * Sanitize input.
     *
     * Basic cleaning/checking of user input. Not much to do really.
     *
     * @since  1.0.0
     */
    public function easymap_setting_sanitize_google_geodata_api_key( $input ) {
        if ( ! is_admin( ) || ! is_user_logged_in() || ! current_user_can( 'administrator' ) )  {
            return;
        }
        if ( defined( 'EASYMAP_DEBUG' ) && EASYMAP_DEBUG ) {
            error_log( basename(__FILE__) . ' (' . __FUNCTION__ . ')' );
        }
        return( substr( sanitize_text_field( trim( $input ) ), 0, 200 ) );
    }
    public function easymap_setting_sanitize_google_language( $input ) {
        if ( ! is_admin( ) || ! is_user_logged_in() || ! current_user_can( 'administrator' ) )  {
            return;
        }
        if ( defined( 'EASYMAP_DEBUG' ) && EASYMAP_DEBUG ) {
            error_log( basename(__FILE__) . ' (' . __FUNCTION__ . ')' );
        }
        return( $this->Utility->x_substr( sanitize_text_field( trim( $input ) ), 0, 16 ) );
    }
    public function easymap_setting_sanitize_google_region( $input ) {
        if ( ! is_admin( ) || ! is_user_logged_in() || ! current_user_can( 'administrator' ) )  {
            return;
        }
        if ( defined( 'EASYMAP_DEBUG' ) && EASYMAP_DEBUG ) {
            error_log( basename(__FILE__) . ' (' . __FUNCTION__ . ')' );
        }
        return( $this->Utility->x_substr( sanitize_text_field( trim( $input ) ), 0, 16 ) );
    }
    public function easymap_setting_sanitize_google_country( $input ) {
        if ( ! is_admin( ) || ! is_user_logged_in() || ! current_user_can( 'administrator' ) )  {
            return;
        }
        if ( defined( 'EASYMAP_DEBUG' ) && EASYMAP_DEBUG ) {
            error_log( basename(__FILE__) . ' (' . __FUNCTION__ . ')' );
        }
        return( $this->Utility->x_substr( sanitize_text_field( trim( $input ) ), 0, 64 ) );
    }
    /*
    public function easymap_setting_sanitize_google_marker_color( $input ) {
        if ( ! is_admin( ) || ! is_user_logged_in() || ! current_user_can( 'administrator' ) )  {
            return;
        }
        if ( defined( 'EASYMAP_DEBUG' ) && EASYMAP_DEBUG ) {
            error_log( basename(__FILE__) . ' (' . __FUNCTION__ . ')' );
        }
        return( sanitize_hex_color( substr( trim( $input ), 0, 7 ) ) );
    }
    */
    public function easymap_setting_sanitize_textarea_setting( $input ) {
        if ( ! is_admin( ) || ! is_user_logged_in() || ! current_user_can( 'administrator' ) )  {
            return;
        }
        $input = explode( "\n", sanitize_textarea_field( $input ) );
        $output = array();
        foreach( $input as $one_line ) {
            $one_line = trim( $this->Utility->x_substr( $one_line, 0, 80 ) );
            if ( $this->Utility->x_strlen( $one_line ) > 0 ) {
                $output[] = $one_line;
            }
        }
        $input = @ json_encode( $output );
        return( $input );
    }
    public function easymap_setting_sanitize_google_pos( $input ) {
        if ( ! is_admin( ) || ! is_user_logged_in() || ! current_user_can( 'administrator' ) )  {
            return;
        }
        return( substr( sanitize_text_field( trim( $input ) ), 0, 32 ) );
    }
    public function easymap_setting_sanitize_google_zoom( $input ) {
        if ( ! is_admin( ) || ! is_user_logged_in() || ! current_user_can( 'administrator' ) )  {
            return;
        }
        $xs = substr( sanitize_text_field( trim( $input ) ), 0, 5 ) ;
        if ( ! empty( $xs ) ) {
            if ( (int)$xs > 18 || (int)$xs < 1 || ! is_numeric( $xs ) ) {
                $xs = $this->easymap_google_start_zoom;
            }
        }
        return( $xs );
    }
    public function easymap_setting_sanitize_custom_template( $input ) {
        if ( ! is_admin( ) || ! is_user_logged_in() || ! current_user_can( 'administrator' ) )  {
            return;
        }
        if ( defined( 'EASYMAP_DEBUG' ) && EASYMAP_DEBUG ) {
            error_log( basename(__FILE__) . ' (' . __FUNCTION__ . ')' );
        }
        if ( defined( 'EASYMAP_DEBUG' ) && EASYMAP_DEBUG && defined( 'EASYMAP_DEBUG_OPTIONS' ) && EASYMAP_DEBUG_OPTIONS ) {
            error_log( 'Input: "' . print_r( $input, true ) . '"' );
        }
        // Accept {br}, just in case
        $input = str_replace( '{br}', "\n", wp_kses_post( $input ) );
        // Split on newline \n
        $input = explode( "\n", $input );
        // Put it all back together
        $output = '';
        foreach( $input as $one_line ) {
            $output .= trim( $this->Utility->x_substr( $one_line, 0, 80 ) ) . '{br}';
        }
        if ( defined( 'EASYMAP_DEBUG' ) && EASYMAP_DEBUG && defined( 'EASYMAP_DEBUG_OPTIONS' ) && EASYMAP_DEBUG_OPTIONS ) {
            error_log( 'Output: "' . print_r( $output, true ) . '"' );
        }
        return( $output );
    }
    /**
     * Output input fields.
     *
     * @since 1.0.0
     */
    public function easymap_setting_no_output() {
        return( '' );
    }
    public function easymap_setting_google_disable_notifications() {
        echo '<div class="easymap-role-option">';
        echo '<label for="easymap-google-disable-notifications">';
        echo '<input type="checkbox" name="easymap-google-disable-notifications" id="easymap-google-disable-notifications" value="1" ' . ( checked( $this->easymap_google_disable_notifications, 1, false ) ) . '/>';
        echo esc_html__( 'Disable notifications about missing Google API key', 'easymap' ) . '</label> ';
        echo '</div>';
    }
    public function easymap_setting_google_geodata_api_key() {
        echo '<input type="text" size="60" maxlength="200" id="easymap-google-geodata-api-key" name="easymap-google-geodata-api-key" value="' . esc_attr( $this->easymap_google_geodata_api_key ). '"';
        echo ' />';
        echo '<p class="description">' . esc_html__( 'The Google Geodata API key as provided by Google', 'easymap' ) . '</p>';
    }
    public function easymap_setting_google_language() {
        echo '<input type="text" size="5" maxlength="16" id="easymap-google-language" name="easymap-google-language" value="' . esc_attr( $this->easymap_google_language ). '"';
        echo ' />';
        echo '<p class="description">' . esc_html__( 'Language code for Google Maps', 'easymap' ) .
             '. <a class="easymap-extlink" href="https://developers.google.com/maps/documentation/javascript/localization">developers.google.com/maps/documentation/javascript/localization</a>' .
             '</p>';
    }
    public function easymap_setting_google_region() {
        echo '<input type="text" size="5" maxlength="16" id="easymap-google-region" name="easymap-google-region" value="' . esc_attr( $this->easymap_google_region ). '"';
        echo ' />';
        echo '<p class="description">' . esc_html__( 'Region code for Google Maps', 'easymap' ) .
             '. <a class="easymap-extlink" href="https://developers.google.com/maps/documentation/javascript/geocoding#GeocodingRegionCodes">developers.google.com/maps/documentation/javascript/geocoding#GeocodingRegionCodes</a>' .
             '</p>';
    }
    public function easymap_setting_google_country() {
        echo '<input type="text" size="32" maxlength="64" id="easymap-google-country" name="easymap-google-country" value="' . esc_attr( $this->easymap_google_country ). '"';
        echo ' />';
        echo '<p class="description">' . esc_html__( 'Country (name) for lookups in Locations', 'easymap' ) .
             '</p>';
    }
    public function easymap_setting_google_start_pos() {
        echo '<label for="easymap-google-start-lat">';
        echo '<strong>' . esc_html__( 'lat', 'easymap' ) . '</strong> ';
        echo '<input type="text" size="16" maxlength="32" id="easymap-google-start-lat" name="easymap-google-start-lat" value="' . esc_attr( $this->easymap_google_start_lat ). '" />';
        echo '</label>';
        echo '<label for="easymap-google-start-lng">';
        echo ' <strong>' . esc_html__( 'long', 'easymap' ) . '</strong> ';
        echo '<input type="text" size="16" maxlength="32" id="easymap-google-start-lng" name="easymap-google-start-lng" value="' . esc_attr( $this->easymap_google_start_lng ). '" />';
        echo '</label>';
        echo '<p class="description">' . esc_html__( 'Starting position for Google Maps, defaults to auto position', 'easymap' ) .
             '</p>';
    }
    public function easymap_setting_google_start_zoom() {
        echo '<input type="text" size="2" maxlength="2" id="easymap-google-start-zoom" name="easymap-google-start-zoom" value="' . esc_attr( $this->easymap_google_start_zoom ). '"';
        echo ' />';
        echo '<p class="description">' . esc_html__( 'Starting map zoom level 1-18, leave empty for auto zoom', 'easymap' ) .
             '</p>';
    }
    public function easymap_setting_google_features() {
        echo '<div class="easymap-settings-checkboxes">';
        echo '<div><label for="easymap-google-show-poi">';
        echo '<input type="checkbox" name="easymap-google-show-poi" id="easymap-google-show-poi" value="1" ' . ( checked( $this->easymap_google_show_poi, 1, false ) ) . '/>';
        echo '<strong>' . esc_html__( 'POI', 'easymap' ) . '</strong> ';
        echo '</label></div>';
        echo '<div><label for="easymap-google-show-transit">';
        echo '<input type="checkbox" name="easymap-google-show-transit" id="easymap-google-show-transit" value="1" ' . ( checked( $this->easymap_google_show_transit, 1, false ) ) . '/>';
        echo '<strong>' . esc_html__( 'Transit', 'easymap' ) . '</strong> ';
        echo '</label></div>';
        echo '<div><label for="easymap-google-show-landscape">';
        echo '<input type="checkbox" name="easymap-google-show-landscape" id="easymap-google-show-landscape" value="1" ' . ( checked( $this->easymap_google_show_landscape, 1, false ) ) . '/>';
        echo '<strong>' . esc_html__( 'Landscape', 'easymap' ) . '</strong> ';
        echo '</label></div>';
        echo '<div><label for="easymap-google-show-typeselector">';
        echo '<input type="checkbox" name="easymap-google-show-typeselector" id="easymap-google-show-typeselector" value="1" ' . ( checked( $this->easymap_google_show_typeselector, 1, false ) ) . '/>';
        echo '<strong>' . esc_html__( 'Type selector', 'easymap' ) . '</strong> ';
        echo '</label></div>';
        echo '<div><label for="easymap-google-show-fullscreen">';
        echo '<input type="checkbox" name="easymap-google-show-fullscreen" id="easymap-google-show-fullscreen" value="1" ' . ( checked( $this->easymap_google_show_fullscreen, 1, false ) ) . '/>';
        echo '<strong>' . esc_html__( 'Fullscreen', 'easymap' ) . '</strong> ';
        echo '</label></div>';
        echo '<div><label for="easymap-google-show-streetview">';
        echo '<input type="checkbox" name="easymap-google-show-streetview" id="easymap-google-show-streetview" value="1" ' . ( checked( $this->easymap_google_show_streetview, 1, false ) ) . '/>';
        echo '<strong>' . esc_html__( 'Streetview', 'easymap' ) . '</strong> ';
        echo '</label></div>';
        echo '<div><label for="easymap-google-greedy-control">';
        echo '<input type="checkbox" name="easymap-google-greedy-control" id="easymap-google-greedy-control" value="1" ' . ( checked( $this->easymap_google_greedy_control, 1, false ) ) . '/>';
        echo '<strong>' . esc_html__( 'Greedy control', 'easymap' ) . '</strong> ';
        echo '</label></div>';
        echo '</div>';// easymap-settings-checkboxes
        echo '<p class="description">' . esc_html__( 'Configure various Google Maps features', 'easymap' ) .
             '</p>';
    }
    public function easymap_setting_google_marker_animation() {
        echo '<div class="easymap-role-option">';
        echo '<label for="easymap-google-marker-animation">';
        echo '<input type="checkbox" name="easymap-google-marker-animation" id="easymap-google-marker-animation" value="1" ' . ( checked( $this->easymap_google_marker_animation, 1, false ) ) . '/>';
        echo esc_html__( 'Bouncing effect when mouse pointer is over marker', 'easymap' ) . '</label> ';
        echo '</div>';
    }
    public function easymap_setting_google_marker_hover() {
        echo '<div class="easymap-role-option">';
        echo '<label for="easymap-google-marker-hovereffect">';
        echo '<input type="checkbox" name="easymap-google-marker-hovereffect" id="easymap-google-marker-hovereffect" value="1" ' . ( checked( $this->easymap_google_marker_hovereffect, 1, false ) ) . '/>';
        echo esc_html__( 'Switch color when mouse pointer is over marker', 'easymap' ) . '</label> ';
        echo '</div>';
    }
    public function easymap_setting_remove() {
        echo '<div class="easymap-role-option">';
        echo '<label for="easymap-settings-remove">';
        echo '<input type="checkbox" name="easymap-settings-remove" id="easymap-settings-remove" value="1" ' . ( checked( $this->easymap_settings_remove, 1, false ) ) . '/>';
        echo esc_html__( 'Remove all plugin settings and data when plugin is uninstalled', 'easymap' ) . '</label> ';
        echo '</div>';
    }
    /*
    public function easymap_setting_google_marker_color() {
        echo '<input type="text" size="8" maxlength="7" id="easymap-google-default-marker-color" name="easymap-google-default-marker-color" value="' . esc_attr( $this->easymap_google_default_marker_color ). '" placeholder="#hhhhhh" />';
        if ( ! empty( $this->easymap_google_default_marker_color ) ) {
            echo '&nbsp;<span style="color:' .
                 esc_attr( $this->easymap_google_default_marker_color ) . '" title="' . esc_html__( 'Sample map Marker', 'easymap' ) . '">' .
                 $this->easymap_make_icon_html( 'mapmarker' ) .
                 '</span>';
        }
        echo '<p class="description">' .
             esc_html__( 'Default map Marker color in #hhhhhh format', 'easymap' ) .
             '. ' .
             '<a href="https://www.w3schools.com/colors/colors_picker.asp" class="easymap-ext-link" target="_blank">' .
             esc_html__( 'Color picker', 'easymap' ) .
             '</a>'.
             '</p>';
    }
    */
    public function easymap_setting_google_marker_address_template() {
        echo '<label for="easymap-google-marker-address-template"></label>';
        echo '<select name="easymap-google-marker-address-template" id="easymap-google-marker-address-template" value="' . (int)$this->easymap_google_marker_address_template . '">';
        $found_selected = false;
        foreach( $this->easymap_templates as $k => $v ) {
            echo '<option value="' . (int)$v['id'] . '"';
            if ( ! $found_selected && $v['id'] == $this->easymap_google_marker_address_template ) {
                $found_selected = true;
                echo ' selected';
            }
            echo '>' . esc_html( $v['name'] ) . '</option>';
        }
        echo '</select>';
    }
    public function easymap_setting_google_marker_address_data() {
        echo '<label for="easymap-google-marker-address-data" id="easymap-google-marker-address-data-label"></label>';
        $template_str = str_replace( '{br}', "\n", esc_html( $this->easymap_google_marker_address_data ) );
        echo '<textarea name="easymap-google-marker-address-data" id="easymap-google-marker-address-data" cols="60" rows="5">' .
             $template_str .
             '</textarea>';
    }
    /**
     * Export
     *
     * Display locations as CSV and as JSON for easy copy&paste. Also display configuration as JSON.
     *
     * @since  1.0.0
     */
    protected function easymap_admin_export_esc_csv( string $input ) : string {
        return( $input );
    }
    public function easymap_admin_export() {
        global $wpdb;

        if ( ! is_admin( ) || ! is_user_logged_in() || ! current_user_can( 'administrator' ) )  {
            return;
        }
        // Get ourselves a proper URL
        $action = admin_url( 'admin.php' ) . '?page=' . EASYMAP_PLUGINNAME_SLUG;
        //
        $locations = array();
        foreach( $this->easymap_location_list as $k => $v ) {
            if ( ! empty( $v ) && ! empty( $v['na'] ) ) {
                $locations[$k] = $v;
            }
        }
        //
        $html = '';
        $tab_header = '<div class="wrap">';
            $tab_header .= '<h1>' . $this->easymap_make_icon_html( 'appicon' ) . '&nbsp;&nbsp;' . esc_html( EASYMAP_PLUGINNAME_HUMAN ) .
                           ': <small>' . esc_html__( 'Export', 'easymap' ) . '</small></h1>';
            $tab_header .= '<p>' . esc_html__( 'Export various data', 'easymap' ) . '</p>';
            $tab_header .= '<nav class="nav-tab-wrapper">';
            $tab_header .= '<a data-toggle="easymap-locations-export-json" href="#easymap-locations-export-json" class="easymap-tab nav-tab">' . esc_html__( 'Locations, JSON', 'easymap' ) . '</a>';
            $tab_header .= '<a data-toggle="easymap-locations-export-csv" href="#easymap-locations-export-csv" class="easymap-tab nav-tab">' . esc_html__( 'Locations, CSV', 'easymap' ) . '</a>';
            $tab_header .= '<a data-toggle="easymap-export-config" href="#easymap-export-config" class="easymap-tab nav-tab">' . esc_html__( 'Configuration, JSON', 'easymap' ) . '</a>';
            $tab_header .= '</nav>';

            $html .= '<div class="tab-content">';
            $html .= '<div class="easymap-config-header">';
            $html .= '<div id="easymap-locations-export-json" class="easymap-tab-content easymap-is-hidden">';
            $html .= esc_html__( 'Only locations with a non-empty "name" will be exported', 'easymap' );
            $html .= '<p>' . esc_html__( 'Copy and paste this Base64 data into another EasyMap installation', 'easymap' ) . '.</p>';
            if ( ! empty( $locations ) ) {
                $html .= '<textarea rows="10" cols="60" class="easymap-textarea-importexport" readonly>';
                $html .= @ base64_encode( json_encode( $locations ) );
                $html .= '</textarea>';
            } else {
                // Nothing to export
                $html .= '<div class="easymap-error">' .
                         esc_html__( 'There are no locations to export', 'easymap' ) .
                         '</div>';
            }
            $html .= '</div>';//easymap-locations-export-json
            $html .= '<div id="easymap-locations-export-csv" class="easymap-tab-content easymap-is-hidden">';
            $html .= esc_html__( 'Only locations with a non-empty "name" will be exported', 'easymap' );
            $html .= '<p>' . esc_html__( 'Copy and paste this CSV data into another application', 'easymap' ) . '. ' .
                     esc_html__( 'You can also save it to a .csv file and open it in your spreadsheet application', 'easymap' ) . '.</p>';
            if ( ! empty( $locations ) ) {
                $html .= '<div class="easymap-copytext-export"><pre>';
                ob_start();
                $fp = @ fopen('php://output', 'w');
                @ fputcsv( $fp, array( 'ID',
                                     'Alias',
                                     'Name',
                                     'Street address',
                                     'Street number',
                                     'City',
                                     'State',
                                     'Zip',
                                     'Phone',
                                     'E-mail',
                                     'Website',
                                     'Pos LAT',
                                     'Pos LONG',
                                     'Active',
                                     'Description' ), ',', '"' );
                foreach( $locations as $k => $v ) {
                    $html_csv =
                        array( $v['id'],
                               $v['al'],
                               $this->easymap_admin_export_esc_csv( $v['na'] ),
                               $this->easymap_admin_export_esc_csv( $v['sa'] ),
                               $v['sn'],
                               $this->easymap_admin_export_esc_csv( $v['ci'] ),
                               $this->easymap_admin_export_esc_csv( $v['st'] ),
                               $v['pc'],
                               $v['ph'],
                               $v['em'],
                               $v['ws'],
                               $v['la'],
                               $v['lo'],
                               (int) $v['ac'],
                               esc_html( $this->easymap_admin_export_esc_csv( $v['no'] ) ),
                             );
                    @ fputcsv( $fp, $html_csv, ',', '"' );
                }
                @ fclose( $fp );
                $html .= ob_get_contents();
                ob_end_clean();
                $html .= '</pre></div>';
            } else {
                // Nothing to export
                $html .= '<div class="easymap-error">' .
                         esc_html__( 'There are no locations to export', 'easymap' ) .
                         '</div>';
            }
            $html .= '</div>';//easymap-locations-export-csv
            $html .= '<div id="easymap-export-config" class="easymap-tab-content easymap-is-hidden">';
            $query = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->get_blog_prefix() .
                                         'options WHERE option_name LIKE "' . EASYMAP_DEFAULT_PREFIX . '%" ' .
                                         ' AND option_name <> "' . EASYMAP_DEFAULT_PREFIX . '-location-list" '.
                                         'ORDER BY option_id', ARRAY_A );
            if ( ! is_array( $query ) || ! is_array( $query[0] ) || empty( $query[0] ) ) {
                $html .= '<div class="easymap-error">' .
                         esc_html__( 'Unable to fetch plugin configuration from the WordPress database', 'easymap' ) .
                         '</div>';
            } else {
                $html .= esc_html__( 'Copy and paste this Base64 data into another EasyMap installation', 'easymap' );
                // Show notice about locations not being included
                $html .= '<p>' . esc_html__( 'Please note that locations are not included in this export', 'easymap' ) . '.</p>';
                // Add our "signature", just for basic import validation
                $query[] = array( 'easymap' => $this->easymap_plugin_version );
                $html .= '<textarea rows="10" cols="60" class="easymap-textarea-importexport" readonly>';
                $html .= @ base64_encode( json_encode( $query ) );
                $html .= '</textarea>';
            }
            //EASYMAP_DEFAULT_PREFIX
            $html .= '</div>';//easymap-export-config
            $html .= '</div>';//easymap-config-header
            $html .= '</div>';//tab-content
        $html .= '</div>'; // wrap
        //
        echo wp_kses_post( $tab_header . $html );
    }
    /**
     * Import data.
     *
     * Allows import of locations from CSV or JSON, and import of configuration from JSON.
     *
     * @since  1.0.0
     */
    public function easymap_admin_import() {
        if ( ! is_admin( ) || ! is_user_logged_in() || ! current_user_can( 'administrator' ) )  {
            return;
        }
        // Get ourselves a proper URL
        if ( empty( $_POST['easymap-form-tab'] ) ) {
            $easymap_form_tab = '';
        } else {
            $easymap_form_tab = sanitize_key( $_POST['easymap-form-tab'] );
        }
        switch( $easymap_form_tab ) {
            case 'easymap-locations-import-csv':
            case 'easymap-import-config':
                $url_addon = $_POST['easymap-form-tab'];
                break;
            default:
                $url_addon = 'easymap-locations-import-json';
                break;
        } // switch
        // Handle submit
        $skipped_options = '';
        $form_error = false;
        $form_error_message = '';
        $partial_form_message = '';
        $import_count = 0;
        switch( $url_addon ) {
            case 'easymap-locations-import-json':
                if ( ! empty( $_POST['easymapjsondoimport'] ) ) {
                    if ( ! empty( $_POST['easymap-jsonimportdata'] ) ) {
                        $easymap_json_importdata = sanitize_text_field( trim( $_POST['easymap-jsonimportdata'] ) );
                    } else {
                        $easymap_json_importdata = '';
                    }
                    // Simple Base64 validation
                    if ( empty( $easymap_json_importdata ) || base64_encode( base64_decode( $easymap_json_importdata ) ) != $easymap_json_importdata ) {
                        $form_error_message = __( 'Please enter a valid Base64 encoded string', 'easymap' );
                    } else {
                        // Try json_decode() and validation
                        $json_error = false;
                        $json_data = @ json_decode( base64_decode( $easymap_json_importdata ), true, 10 );
                        if ( ! is_array( $json_data ) || empty( $json_data ) || json_last_error() != 0 ) {
                            if ( defined( 'EASYMAP_DEBUG' ) && EASYMAP_DEBUG ) {
                                error_log( basename(__FILE__) . ' (' . __FUNCTION__ . '): json_last_error=' .json_last_error.', json_last_error_msg "' . json_last_error_msg() .'"' );
                            }
                            $form_error_message = __( 'The specified import data does not seem to contain any locations', 'easymap' );
                        } elseif ( count( $json_data ) > 200 ) {
                            $form_error_message = __( 'The specified import data contains more than 200 locations', 'easymap' );
                        } else {
                            $json_error = true;
                            foreach( $json_data as $k => $v ) {
                                if ( ! empty( $v['na'] ) ) {
                                    $json_error = false;
                                    break;
                                }
                            }// foreach
                            if ( $json_error ) {
                                $form_error_message = __( 'No locations with "Name" found in import data', 'easymap' );
                            } else {
                                if ( defined( 'EASYMAP_DEBUG' ) && EASYMAP_DEBUG ) {
                                    error_log( basename(__FILE__) . ' (' . __FUNCTION__ . '): IMPORT' . "\n" . var_export( $json_data, true ) );
                                }
                                // Do import
                            }
                        }
                        // Do import
                        if ( empty( $form_error_message ) ) {
                            // Replace existing
                            if ( ! empty( $_POST['easymap-locations-import-json-replace'] ) ) {
                                $is_replace = true;
                                $ainit = array();
                                $brec = $this->easymap_init_location( false );
                                for ( $i = 1; $i < 201; $i++ ) {
                                    $brec['id'] = $i;
                                    $ainit[$i] = $brec;
                                }
                                $this->easymap_location_list = $ainit;
                            } else {
                                $is_replace = false;
                            }
                            // Find first empty slot
                            $empty_slot = -1;
                            foreach ( $this->easymap_location_list as $k => $v ) {
                                if ( empty( $v['na'] ) ) {
                                    $empty_slot = $k;
                                    break;
                                }
                            }
                            if ( $empty_slot == -1 ) {
                                // No empty slots (can only happen if we don't choose to replace)
                                $form_error_message = __( 'No empty location slots to use for import', 'easymap' );
                            } else {
                                // Go on ..
                                foreach( $json_data as $k => $v ) {
                                    if ( ! empty( $v['na'] ) ) {
                                        $brec = $this->easymap_init_location( false );
                                        if ( $is_replace && ! empty( $v['id'] ) && is_numeric( $v['id'] ) && $v['id'] < 201 ) {
                                            $used_slot = $brec['id'] = $v['id'];
                                        } else {
                                            $used_slot = $brec['id'] = $empty_slot;
                                        }
                                        $brec['na'] = $this->Utility->x_substr( sanitize_text_field( trim( $v['na'] ) ), 0, 100 );
                                        if ( ! empty( $v['al'] ) ) $brec['al'] = $this->Utility->x_substr( sanitize_text_field( trim( $v['al'] ) ), 0,  64 );
                                        if ( ! empty( $v['co'] ) ) $brec['co'] = $this->Utility->x_substr( sanitize_text_field( trim( $v['co'] ) ), 0,   7 );
                                        if ( ! empty( $v['sa'] ) ) $brec['sa'] = $this->Utility->x_substr( sanitize_text_field( trim( $v['sa'] ) ), 0, 100 );
                                        if ( ! empty( $v['sn'] ) ) $brec['sn'] = $this->Utility->x_substr( sanitize_text_field( trim( $v['sn'] ) ), 0,  20 );
                                        if ( ! empty( $v['ci'] ) ) $brec['ci'] = $this->Utility->x_substr( sanitize_text_field( trim( $v['ci'] ) ), 0, 100 );
                                        if ( ! empty( $v['st'] ) ) $brec['st'] = $this->Utility->x_substr( sanitize_text_field( trim( $v['st'] ) ), 0, 100 );
                                        if ( ! empty( $v['pc'] ) ) $brec['pc'] = $this->Utility->x_substr( sanitize_text_field( trim( $v['pc'] ) ), 0, 100 );
                                        if ( ! empty( $v['ph'] ) ) $brec['ph'] = $this->Utility->x_substr( sanitize_text_field( trim( $v['ph'] ) ), 0, 100 );
                                        if ( ! empty( $v['em'] ) ) $brec['em'] = $this->Utility->x_substr( sanitize_text_field( trim( $v['em'] ) ), 0, 100 );
                                        if ( ! empty( $v['ws'] ) ) $brec['ws'] = $this->Utility->x_substr( sanitize_text_field( trim( $v['ws'] ) ), 0, 100 );
                                        if ( ! empty( $v['no'] ) ) $brec['no'] = $this->Utility->x_substr( wp_kses_post( trim( $v['no'] ) ), 0, 1024 );
                                        if ( ! empty( $v['la'] ) ) $brec['la'] = $this->Utility->x_substr( sanitize_text_field( trim( $v['la'] ) ), 0,  32 );
                                        if ( ! empty( $v['lo'] ) ) $brec['lo'] = $this->Utility->x_substr( sanitize_text_field( trim( $v['lo'] ) ), 0,  32 );
                                        if ( ! empty( $v['ma'] ) ) $brec['ma'] = $v['ma'];
                                        if ( ! empty( $v['ac'] ) ) $brec['ac'] = $v['ac'];
                                        //Copy to actual list
                                        $this->easymap_location_list[$used_slot] = $brec;
                                        $import_count++;
                                        //Find next available slot
                                        $empty_slot = -1;
                                        foreach ( $this->easymap_location_list as $ok => $ov ) {
                                            if ( empty( $ov['na'] ) ) {
                                                $empty_slot = $ok;
                                                break;
                                            }
                                        }
                                        if ( $empty_slot < 1 ) {
                                            // Not a fatal error, but we abort here
                                            $partial_form_message = __( 'No more empty location slots, all locations were not imported', 'easymap' );
                                            break;
                                        }
                                    }
                                }// REMOVE foreach
                            }
                        }// import
                    }
                }
                break;
            case 'easymap-locations-import-csv':
                if ( ! empty( $_POST['easymapcsvdoimport'] ) ) {
                    $csv_data_error = true;
                    if ( ! isset( $_POST['easymap-csvimportdata'] ) ) {
                        $easymap_csv_importdata = '';
                    } else {
                        $easymap_csv_importdata = $this->Utility->x_stripslashes( $_POST['easymap-csvimportdata'] );
                    }
                    $csv_data = array();
                    // Make sure we have data
                    if ( ! empty( $easymap_csv_importdata ) ) {
                        // Read CSV, this is not reliable IMHO
                        $fp = @ fopen( 'php://memory', 'rb+' );
                        if ( $fp !== false ) {
                            if ( @ fwrite( $fp, $easymap_csv_importdata ) !== false ) {
                                if ( @ fseek( $fp, 0, SEEK_SET ) === 0 ) {
                                    do {
                                        $data = @ fgetcsv( $fp, 2048, ',', '"' );
                                        if ( is_array( $data ) && count( $data ) == 15 ) {
                                            // We're quite strict with the format
                                            if ( isset( $data[0] ) && is_numeric( $data[0] ) ) {
                                                $csv_data[] = $data;
                                            }
                                        }
                                    } while ( $data !== null && $data !== false );
                                }
                            }
                            @ fclose( $fp );
                        }
                    }
                    // Process final result
                    if ( count( $csv_data ) == 0 ) {
                        $form_error_message = __( 'Please enter valid CSV encoded data', 'easymap' );
                    } else {
                        // Replace existing
                        if ( ! empty( $_POST['easymap-locations-import-csv-replace'] ) ) {
                            $is_replace = true;
                            $ainit = array();
                            $brec = $this->easymap_init_location( false );
                            for ( $i = 1; $i < 201; $i++ ) {
                                $brec['id'] = $i;
                                $ainit[$i] = $brec;
                            }
                            $this->easymap_location_list = $ainit;
                        } else {
                            $is_replace = false;
                        }
                        // Find first empty slot
                        $empty_slot = -1;
                        foreach ( $this->easymap_location_list as $k => $v ) {
                            if ( empty( $v['na'] ) ) {
                                $empty_slot = $k;
                                break;
                            }
                        }
                        if ( $empty_slot == -1 ) {
                            // No empty slots (can only happen if we don't choose to replace)
                            $form_error_message = __( 'No empty location slots to use for import', 'easymap' );
                        } else {
                            // Go on ..
                            foreach( $csv_data as $k => $v ) {
                                if ( ! empty( $v[0] ) ) {
                                    $brec = $this->easymap_init_location( false );
                                    if ( $is_replace && $v[0] < 201 ) {
                                        // Use ID "as is"
                                        $used_slot = $brec['id'] = $v[0];
                                    } else {
                                        $used_slot = $brec['id'] = $empty_slot;
                                    }
                                    $brec['al'] = $this->Utility->x_substr( sanitize_text_field( trim( $v[1] ) ), 0,  64 );
                                    $brec['na'] = $this->Utility->x_substr( sanitize_text_field( trim( $v[2] ) ), 0, 100 );
                                    $brec['sa'] = $this->Utility->x_substr( sanitize_text_field( trim( $v[3] ) ), 0, 100 );
                                    $brec['sn'] = $this->Utility->x_substr( sanitize_text_field( trim( $v[4] ) ), 0,  20 );
                                    $brec['ci'] = $this->Utility->x_substr( sanitize_text_field( trim( $v[5] ) ), 0, 100 );
                                    $brec['st'] = $this->Utility->x_substr( sanitize_text_field( trim( $v[6] ) ), 0, 100 );
                                    $brec['pc'] = $this->Utility->x_substr( sanitize_text_field( trim( $v[7] ) ), 0, 100 );
                                    $brec['ph'] = $this->Utility->x_substr( sanitize_text_field( trim( $v[8] ) ), 0, 100 );
                                    $brec['em'] = $this->Utility->x_substr( sanitize_text_field( trim( $v[9] ) ), 0, 100 );
                                    $brec['ws'] = $this->Utility->x_substr( sanitize_text_field( trim( $v[10] ) ), 0, 100 );
                                    $brec['la'] = $this->Utility->x_substr( sanitize_text_field( trim( $v[11] ) ), 0,  32 );
                                    $brec['lo'] = $this->Utility->x_substr( sanitize_text_field( trim( $v[12] ) ), 0,  32 );
                                    $brec['ac'] = (int)$v[13];
                                    $brec['no'] = $this->Utility->x_substr( wp_kses_post( trim( $v[14] ) ), 0, 1024 );
                                    //Copy to actual list
                                    $this->easymap_location_list[$used_slot] = $brec;
                                    $import_count++;
                                    //Find next available slot
                                    $empty_slot = -1;
                                    foreach ( $this->easymap_location_list as $ok => $ov ) {
                                        if ( empty( $ov['na'] ) ) {
                                            $empty_slot = $ok;
                                            break;
                                        }
                                    }
                                    if ( $empty_slot < 1 ) {
                                        // Not a fatal error, but we abort here
                                        $partial_form_message = __( 'No more empty location slots, all locations were not imported', 'easymap' );
                                        break;
                                    }
                                }
                            }// foreach
                        }
                    }
                }
                break;
            case 'easymap-import-config':
                if ( ! empty( $_POST['easymapcfgdoimport'] ) ) {
                    if ( ! empty( $_POST['easymap-jsonimportconfig'] ) ) {
                        $easymap_json_importconfig = sanitize_text_field( $_POST['easymap-jsonimportconfig'] );
                    } else {
                        $easymap_json_importconfig = '';
                    }
                    // Simple Base64 validation
                    if ( empty( $easymap_json_importconfig ) || base64_encode( base64_decode( $easymap_json_importconfig ) ) != $easymap_json_importconfig ) {
                        $form_error_message = __( 'Please enter a valid Base64 encoded string', 'easymap' );
                    } else {
                        // Try json_decode() and validation
                        $json_error = false;
                        $json_data = @ json_decode( base64_decode( $easymap_json_importconfig ), true, 10 );
                        if ( ! is_array( $json_data ) || empty( $json_data ) || json_last_error() != 0 ) {
                            if ( defined( 'EASYMAP_DEBUG' ) && EASYMAP_DEBUG ) {
                                error_log( basename(__FILE__) . ' (' . __FUNCTION__ . '): json_last_error=' .json_last_error.', json_last_error_msg "' . json_last_error_msg() .'"' );
                            }
                            $form_error_message = __( 'The specified import data does not seem to contain an exported configuration', 'easymap' );
                        } else {
                            $found_signature = false;
                            foreach( $json_data as $k => $v ) {
                                if ( is_array( $v ) && ! empty( $v[EASYMAP_PLUGINNAME_SLUG] ) ) {
                                    // We don't do any more validation than this at this point
                                    $found_signature = true;
                                }
                            }
                            if ( ! $found_signature ) {
                                if ( defined( 'EASYMAP_DEBUG' ) && EASYMAP_DEBUG ) {
                                    error_log( basename(__FILE__) . ' (' . __FUNCTION__ . '): json data doest not contain "' . EASYMAP_PLUGINNAME_SLUG . '"' );
                                    error_log( print_r( $json_data, true ) );
                                }
                                $form_error_message = __( 'The specified import data does not seem to contain an exported configuration', 'easymap' );
                            }
                        }
                        // Do import
                        if ( empty( $form_error_message ) ) {
                            foreach( $json_data as $k => $v ) {
                                if ( is_array( $v ) && empty( $v[EASYMAP_PLUGINNAME_SLUG] ) ) {
                                    $option = array();
                                    foreach( $v as $cfg_option => $cfg_value ) {
                                        $option[$cfg_option] = $cfg_value;
                                    }
                                    if ( empty( $option['option_name'] ) || ! isset( $option['option_value'] ) || $this->Utility->x_strpos( $option['option_name'], EASYMAP_PLUGINNAME_SLUG . '-' ) === false ) {
                                        if ( defined( 'EASYMAP_DEBUG' ) && EASYMAP_DEBUG ) {
                                            error_log( basename(__FILE__) . ' (' . __FUNCTION__ . '): Skipped unknown option "' . $option['option_name'] . '"' );
                                            error_log( print_r( $json_data, true ) );
                                        }
                                        if ( empty( $form_error_message ) ) {
                                            $form_error_message = __( 'One or more unrecognized options were ignored', 'easymap' );
                                        }
                                        $skipped_options .= esc_html( $option['option_name'] ) . '<br/>';
                                    } elseif ( $option['option_name'] != EASYMAP_PLUGINNAME_SLUG . '-form-tab' ) {
                                        update_option( $option['option_name'], $option['option_value'] );
                                        $import_count++;
                                    }
                                }
                            }

                        }// import
                    }
                }
                break;
        }// switch

        // Possibly format error message
        if ( ! empty( $form_error_message )  ) {
            $form_error_message = '<div class="notice notice-error is-dismissible"><p><strong>'.
                                  esc_html( $form_error_message ) .
                                  ( ! empty( $skipped_options ) ? '<p>' . $skipped_options . '</p>' : '' ) .
                                  '</strong></p></div>';
        } elseif ( $import_count > 0 ) {
            // Finalize import
            switch( $url_addon ) {
                case 'easymap-locations-import-json':
                    $form_error_message = '<div class="notice notice-info is-dismissible"><p><strong>'.
                                          (int)$import_count . ' ' . esc_html__( 'location(s) imported', 'easymap' ) .
                                          '</strong></p></div>';
                    update_option( 'easymap-location-list', json_encode( $this->easymap_location_list ) );
                    break;
                case 'easymap-locations-import-csv':
                    $form_error_message = '<div class="notice notice-info is-dismissible"><p><strong>'.
                                          (int)$import_count . ' ' . esc_html__( 'location(s) imported', 'easymap' ) .
                                          '</strong></p></div>';
                    update_option( 'easymap-location-list', json_encode( $this->easymap_location_list ) );
                    break;
                case 'easymap-import-config':
                    $form_error_message = '<div class="notice notice-info is-dismissible"><p><strong>'.
                                          (int)$import_count . ' ' . esc_html__( 'configuration setting(s) imported', 'easymap' ) .
                                          '</strong></p></div>';
                    break;
            }// switch
        }
        if ( ! empty( $partial_form_message ) ) {
            $form_error_message = '<div class="notice notice-info is-dismissible"><p><strong>'.
                                  esc_html( $partial_form_message ) .
                                  '</strong></p></div>' .
                                  $form_error_message;
        }
        //
        echo '<div class="wrap">' . wp_kses_post( $form_error_message );
        echo '<h1>' . $this->easymap_make_icon_html( 'appicon' ) . '&nbsp;&nbsp;' . esc_html( EASYMAP_PLUGINNAME_HUMAN ) .
             ': <small>' . esc_html__( 'Import', 'easymap' ) . '</small></h1>';
        echo '<p>' . esc_html__( 'Import data', 'easymap' ) . '</p>';
        echo '<nav class="nav-tab-wrapper">';
        echo '<a data-toggle="easymap-locations-import-json" href="#easymap-locations-import-json" class="easymap-tab nav-tab">' . esc_html__( 'Locations', 'easymap' ) . ', JSON</a>';
        echo '<a data-toggle="easymap-locations-import-csv" href="#easymap-locations-import-csv" class="easymap-tab nav-tab">' . esc_html__( 'Locations', 'easymap' ) . ', CSV</a>';
        echo '<a data-toggle="easymap-import-config" href="#easymap-import-config" class="easymap-tab nav-tab">' . esc_html__( 'Configuration', 'easymap' ) . ', JSON</a>';
        echo '</nav>';

        echo '<form method="post" action="' . admin_url( 'admin.php' ) . '?page=' . EASYMAP_PLUGINNAME_SLUG . '-import" id="easymap-tab-form">';
        echo '<input type="hidden" name="easymap-form-tab" id="easymap-form-tab" value="' . esc_attr( $url_addon ) . '" />';
        echo '<div class="tab-content">';
        echo '<div class="easymap-config-header">';
        echo '<div id="easymap-locations-import-json" class="easymap-tab-content easymap-is-hidden">';
        echo esc_html__( 'Only locations with a non-empty "name" will be imported. Identical IDs will be re-located unless "Replace locations" is selected.', 'easymap' );
        echo '<textarea rows="10" cols="60" style="margin-top:25px;" class="easymap-textarea-importexport" name="easymap-jsonimportdata"></textarea>';
        echo '<p class="description">' . esc_html__( 'Paste previously exported Base64 location data into this field', 'easymap' ) . '.</p>';
        echo '<p style="margin-top:25px;"><label for="easymap-locations-import-json-replace">' .
             '<input type="checkbox" name="easymap-locations-import-json-replace" id="easymap-locations-import-json-replace" value="replace" />' .
             esc_html__( 'Replace locations', 'easymap' ) .
             '</label>' .
             '<p class="description">' .
             esc_html__( 'This will REMOVE ALL PREVIOUS locations before doing the import', 'easymap' ) .
             '</p></p>';
        submit_button( esc_html__( 'Import', 'easymap' ), 'primary', 'easymapjsondoimport' );
        echo '</div>';//easymap-locations-import-json
        echo '<div id="easymap-locations-import-csv" class="easymap-tab-content easymap-is-hidden">';
        echo esc_html__( 'Only locations with a non-empty "name" will be imported. Identical IDs will be re-located unless "Replace locations" is selected.', 'easymap' );
        echo '<p>' .
             esc_html__( 'The column format must exactly match this syntax', 'easymap' ) . ':<br/><strong>' .
             esc_html__( 'ID,Alias,Name,Street address,Street number,City,State,Zip,Phone,E-mail,Website,Pos LAT,Pos LONG,Active,Description', 'easymap' ) .
             '</strong></p>';
        echo '<textarea rows="10" cols="60" style="margin-top:25px;" class="easymap-textarea-importexport" name="easymap-csvimportdata"></textarea>';
        echo '<p class="description">' . esc_html__( 'Paste previously exported CSV location data into this field', 'easymap' ) . '.</p>';
        echo '<p style="margin-top:25px;"><label for="easymap-locations-import-csv-replace">' .
             '<input type="checkbox" name="easymap-locations-import-csv-replace" id="easymap-locations-import-csv-replace" value="replace" />' .
             esc_html__( 'Replace locations', 'easymap' ) .
             '</label>' .
             '<p class="description">' .
             esc_html__( 'This will REMOVE ALL PREVIOUS locations before doing the import', 'easymap' ) .
             '</p></p>';
        submit_button( esc_html__( 'Import', 'easymap'), 'primary', 'easymapcsvdoimport' );
        echo '</div>';//easymap-locations-export-csv
        echo '<div id="easymap-import-config" class="easymap-tab-content easymap-is-hidden">';
        echo esc_html__( 'This will replace plugin configuration data except for locations', 'easymap' ) . '.';
        echo '<textarea rows="10" cols="60" style="margin-top:25px;" class="easymap-textarea-importexport" name="easymap-jsonimportconfig"></textarea>';
        echo '<p class="description">' . esc_html__( 'Paste previously exported Base64 configuration data into this field', 'easymap' ) . '.</p>';
        submit_button( esc_html__( 'Import', 'easymap' ), 'primary', 'easymapcfgdoimport' );
        echo '</div>';//easymap-export-config
        echo '</div>';//easymap-config-header
        echo '</div>';//tab-content
        echo '</form>';
        echo '</div>'; // wrap
    }
    /**
     * Generate error for required field
     *
     * @since 1.0.0
     */
    protected function easymap_admin_required_field( $field_text ) {
        return( '<div class="notice notice-error is-dismissible"><p>'.
                $field_text . ' ' . esc_html__( 'is a required field', 'easymap' ) .
                '</p></div>' );
    }
    /**
     * Location list.
     *
     * Display list of locations using WordPress tables.
     *
     * @since  1.0.0
     */
    public function easymap_admin_locations_css() {
        echo '<style type="text/css">';
        echo '.wp-list-table .check-column { width:24px; align:center; }';
        echo '.wp-list-table .column-cb { width:24px; align:center; }';
        echo '.wp-list-table .column-id { width:60px; padding-right:10px; text-align:center; }';
        echo '.wp-list-table .column-name { width:200px; }';
        echo '.wp-list-table .column-color { width:100px; }';
        echo '.wp-list-table .column-active { width:100px; }';
        echo '</style>';
    }
    public function easymap_admin_locations() {
        if ( ! is_admin( ) || ! is_user_logged_in() || ! current_user_can( 'administrator' ) )  {
            return;
        }
        $show_locations = true;
        // Check for request other than provider list
        if ( empty ( $_REQUEST['cancel_location'] ) && empty( $_REQUEST['delete_location'] ) ) {
            if ( ! empty( $_REQUEST['action'] ) && $_REQUEST['action'] == 'edit' && ! empty( $_REQUEST['location'] ) ) {
                if ( is_numeric( $_REQUEST['location'] ) || $_REQUEST['location'] == 'new' ) {
                    if ( is_numeric( $_REQUEST['location'] ) ) {
                        $_REQUEST['location'] = (int)$_REQUEST['location'];
                        if ( $_REQUEST['location'] > 0 && $_REQUEST['location'] < 201 ) {
                            $show_locations = false;
                        } else {
                            echo '<div class="notice notice-error is-dismissible"><p>'.
                                 esc_html__( 'Invalid location ID, must be 1-200', 'easymap' ) .
                                 '</p>'.
                                 '</div>';
                            error_log( basename(__FILE__) . ' (' . __FUNCTION__ . '): Invalid location value ("' . $_REQUEST['location'] . '")' );
                        }
                    } else {
                        // new or newsub
                        $show_locations = false;
                    }
                    if ( ! $show_locations ) {
                        $save_location = false;
                        if ( ! empty( $_POST['submit_location'] ) ) {
                            if ( ! empty( $_POST['easymap_nonce'] ) ) {
                                if ( wp_verify_nonce( $_POST['easymap_nonce'], 'easymap-location-edit') ) {
                                    $form_validation = true;
                                    $post_alias = ( empty( $_POST['alias'] ) ? '':$this->Utility->x_substr( sanitize_text_field( trim( $_POST['alias'] ) ), 0, 64 ) );
                                    $post_name = ( empty( $_POST['name'] ) ? '':$this->Utility->x_stripslashes( $this->Utility->x_substr( sanitize_text_field( trim( $_POST['name'] ) ), 0, 100 ) ) );
                                    $post_color = ( empty( $_POST['color'] ) ? '':$this->Utility->x_substr( sanitize_text_field( trim( $_POST['color'] ) ), 0, 7 ) );
                                    //$post_description = ( empty( $_POST['description'] ) ? '':$this->Utility->x_stripslashes( $this->Utility->x_substr( sanitize_textarea_field( trim( $_POST['description'] ) ), 0, 1024 ) ) );
                                    $post_description = ( empty( $_POST['description'] ) ? '':$this->Utility->x_stripslashes( $this->Utility->x_substr( wp_kses_post( trim( $_POST['description'] ) ), 0, 1024 ) ) );
                                    $post_address = ( empty( $_POST['address'] ) ? '':$this->Utility->x_stripslashes( $this->Utility->x_substr( sanitize_text_field( trim( $_POST['address'] ) ), 0, 100 ) ) );
                                    $post_number = ( empty( $_POST['number'] ) ? '':$this->Utility->x_stripslashes( $this->Utility->x_substr( sanitize_text_field( trim( $_POST['number'] ) ), 0, 20 ) ) );
                                    $post_city = ( empty( $_POST['city'] ) ? '':$this->Utility->x_stripslashes( $this->Utility->x_substr( sanitize_text_field( trim( $_POST['city'] ) ), 0, 100 ) ) );
                                    $post_state = ( empty( $_POST['state'] ) ? '':$this->Utility->x_stripslashes( $this->Utility->x_substr( sanitize_text_field( trim( $_POST['state'] ) ), 0, 100 ) ) );
                                    $post_zip = ( empty( $_POST['zip'] ) ? '':$this->Utility->x_stripslashes( $this->Utility->x_substr( sanitize_text_field( trim( $_POST['zip'] ) ), 0, 100 ) ) );
                                    $post_phone = ( empty( $_POST['phone'] ) ? '':$this->Utility->x_stripslashes( $this->Utility->x_substr( sanitize_text_field( trim( $_POST['phone'] ) ), 0, 100 ) ) );
                                    $post_email = ( empty( $_POST['email'] ) ? '':$this->Utility->x_substr( sanitize_text_field( trim( $_POST['email'] ) ), 0, 100 ) );
                                    $post_website = ( empty( $_POST['website'] ) ? '':$this->Utility->x_substr( sanitize_text_field( trim( $_POST['website'] ) ), 0, 100 ) );
                                    $post_pos_lat = ( empty( $_POST['pos_lat'] ) ? '':$this->Utility->x_substr( sanitize_text_field( trim( $_POST['pos_lat'] ) ), 0, 32 ) );
                                    $post_pos_long = ( empty( $_POST['pos_long'] ) ? '':$this->Utility->x_substr( sanitize_text_field( trim( $_POST['pos_long'] ) ), 0, 32 ) );
                                    if ( $this->Utility->x_strlen( $post_name ) < 2 ) {
                                        $form_validation = false;
                                        echo '<div class="notice notice-error is-dismissible"><p>'.
                                             esc_html__( 'Location name must be two characters or more', 'easymap' ) .
                                             '</p>'.
                                             '</div>';
                                    }
                                    if ( $form_validation ) {
                                        if ( ! empty( $post_color ) && $this->Utility->x_strlen( $post_color ) !== 7 && $this->Utility->x_strlen( $post_color ) !== 3 ) {
                                            $form_validation = false;
                                            echo '<div class="notice notice-error is-dismissible"><p>'.
                                                 esc_html__( 'Location color must be # followed by three or six hexadecimal digits (0-9a-f)', 'easymap' ) .
                                                 '</p>'.
                                                 '</div>';
                                        }
                                    }
                                    if ( $form_validation ) {
                                        if ( ! empty( $post_alias ) && ctype_digit( $post_alias[0] ) ) {
                                            $form_validation = false;
                                            echo '<div class="notice notice-error is-dismissible"><p>'.
                                                 esc_html__( 'Location alias must not begin with a number (0-9)', 'easymap' ) .
                                                 '</p>'.
                                                 '</div>';
                                        }
                                    }
                                    $location_override = 0;
                                    if ( $form_validation ) {
                                        if ( ! empty( $_POST['location-override'] ) ) {
                                            $location_override = (int)sanitize_text_field( $_POST['location-override'] );
                                            if ( $_REQUEST['location'] == 'new' ) {
                                                if ( $location_override < 1 || $location_override > 200 ) {
                                                    $form_validation = false;
                                                    echo '<div class="notice notice-error is-dismissible"><p>'.
                                                         esc_html__( 'Location ID must be 1-200', 'easymap' ) .
                                                         '</p>'.
                                                         '</div>';
                                                }
                                            }
                                        }
                                    }
                                    if ( $form_validation ) {
                                        $pr = array();
                                        if ( $location_override > 0 && $location_override < 201 ) {
                                            $pr['id'] = $location_override;
                                        } else {
                                            $pr['id'] = (int)sanitize_key( $_REQUEST['location'] );
                                        }
                                        $pr['al'] = $post_alias;
                                        $pr['na'] = $post_name;
                                        //$pr['co'] = $post_color;
                                        $pr['co'] = '';
                                        $pr['ma'] = '';
                                        $pr['ac'] = ( ! empty( $_POST['active'] ) );
                                        $pr['no'] = $post_description;
                                        $pr['sa'] = $post_address;
                                        $pr['sn'] = $post_number;
                                        $pr['ci'] = $post_city;
                                        $pr['st'] = $post_state;
                                        $pr['pc'] = $post_zip;
                                        $pr['ph'] = $post_phone;
                                        $pr['em'] = $post_email;
                                        $pr['ws'] = $post_website;
                                        $pr['la'] = $post_pos_lat;
                                        $pr['lo'] = $post_pos_long;
                                        if ( defined( 'EASYMAP_DEBUG' ) && EASYMAP_DEBUG ) {
                                            error_log( basename(__FILE__) . ' (' . __FUNCTION__ . '): Saving record:' . "\n" . print_r( $pr, true ) );
                                        }
                                        $this->easymap_location_list[ $pr['id'] ] = $pr;
                                        unset( $this->easymap_location_list[ 'new' ] );
                                        update_option( 'easymap-location-list', json_encode( $this->easymap_location_list ) );
                                        $show_locations = true;
                                        echo '<div class="notice notice-success is-dismissible"><p>'.
                                             esc_html__( 'Location saved', 'easymap' ) .
                                             '</p>'.
                                             '</div>';
                                        unset( $_REQUEST['action'] );
                                    }
                                } else {
                                    echo '<div class="notice notice-error is-dismissible"><p>'.
                                         esc_html__( 'Unable to process location form data', 'easymap' ) .
                                         '</p>'.
                                         '</div>';
                                }
                            }
                        }
                        if ( ! $show_locations ) {
                            // error_log( 'Not showing categories, action=edit' );
                            $this->easymap_admin_location_edit( $_REQUEST['location'] );
                        }
                    }
                }
            }
        } // ! cancel & ! delete

        // Show location list, we are not editing
        if ( $show_locations ) {
            // Our table handler
            require_once plugin_dir_path( __FILE__ ) . 'include/class_location_admin.inc.php';
            // Get ourselves a proper URL
            $action = admin_url( 'admin.php' ) . '?page=' . EASYMAP_LOCATION_LIST_PAGE_NAME;
            echo '<div class="wrap">';
            echo '<h1>' . $this->easymap_make_icon_html( 'appicon' ) . '&nbsp;&nbsp;' . esc_html( EASYMAP_PLUGINNAME_HUMAN ) .
                 ': <small>' . esc_html__( 'Locations', 'easymap' ) . '</small></h1>';
            // Possibly handle delete before display of list
            if ( ! empty( $_REQUEST['delete_location'] ) ) {
                $delete_location = '';
                $form_validation = true;
                if ( ! wp_verify_nonce( $_REQUEST['easymap_nonce'], 'easymap-category-edit' ) ) {
                    // Bad nonce
                    error_log( basename(__FILE__) . ' (' . __FUNCTION__ . '): Invalid nonce for location deletion' );
                    echo '<div class="notice notice-error is-dismissible"><p>'.
                         esc_html__( 'Unable to process location form data', 'easymap' ) .
                         '</p>'.
                         '</div>';
                    $form_validation = false;
                }
                if ( $form_validation ) {
                    $delete_location = sanitize_key( trim( $_REQUEST['delete_location'] ) );
                    if ( ! is_numeric( $delete_location ) || (int)$delete_location < 1 || (int)$delete_location > 200 ) {
                        // Bad location
                        error_log( basename(__FILE__) . ' (' . __FUNCTION__ . '): Invalid location value to delete ("' . $delete_location . '")' );
                        echo '<div class="notice notice-error is-dismissible"><p>'.
                             esc_html__( 'Unable to process location form data', 'easymap' ) .
                             '</p>'.
                             '</div>';
                        $form_validation = false;
                    }
                }
                if ( $form_validation ) {
                    // Successful, remove category and update WP option
                    if ( defined( 'EASYMAP_DEBUG' ) && EASYMAP_DEBUG ) {
                        error_log( basename(__FILE__) . ' (' . __FUNCTION__ . '): Deleting location ("' . $delete_location . '")' );
                    }
                    $pr = $this->easymap_init_location( false );
                    $pr['id'] = (int)$delete_location;
                    $this->easymap_location_list[ $pr['id'] ] = $pr;
                    update_option( 'easymap-location-list', json_encode( $this->easymap_location_list ) );
                    echo '<div class="notice notice-success is-dismissible"><p>'.
                         esc_html__( 'Location deleted', 'easymap' ) .
                         '</p>'.
                         '</div>';
                    unset( $_REQUEST['action'] );
                }
            }

            $location_list = Easymap_Location_List::getInstance( $this->easymap_icon_style,
                                                                 $this->easymap_location_list,
                                                                 $this->easymap_locale,
                                                                 $this->Utility );
            $location_list->prepare_items();// This must go before the search_box() call
            echo $location_list->show_notifications(true);

            echo '<form name="easymap-location-form" method="post" action="' . esc_url_raw( $action ) . '">';
            if ( ! empty( $_REQUEST['orderby'] ) ) {
                echo '<input type="hidden" name="orderby" value="' . esc_attr( sanitize_key( trim( $_REQUEST['orderby'] ) ) ) . '" />';
            }
            if ( ! empty( $_REQUEST['order'] ) ) {
                echo '<input type="hidden" name="order" value="' . esc_attr( sanitize_key( trim( $_REQUEST['order'] ) ) ) . '" />';
            }
            if ( ! empty( $_REQUEST['s'] ) ) {
                echo '<input type="hidden" name="s" value="' . esc_attr( trim( $_REQUEST['s'] ) ) . '" />';
            }
            if ( ! empty( $_REQUEST['paged'] ) ) {
                echo '<input type="hidden" name="paged" value="' . (int)$_REQUEST['paged'] . '" />';
            }
            add_filter( 'set_url_scheme', [$location_list, 'mangle_url_scheme'], 10, 3 );
            $location_list->search_box( __( 'Search', 'easymap' ), 'easymap-location-search' );
            $location_list->display();
            remove_filter( 'set_url_scheme', [$location_list, 'mangle_url_scheme'], 10 );
            echo '</form>';
            echo '</div>';
        }
    }

    /**
     * Edit location record.
     *
     * @since 1.0.0
     */
    public function easymap_admin_location_edit( $location_id ) {
        if ( ! is_admin( ) || ! is_user_logged_in() || ! current_user_can( 'administrator' ) )  {
            return;
        }
        $cancel_url = admin_url( 'admin.php' ) . '?page=' . EASYMAP_LOCATION_LIST_PAGE_NAME;
        $action = $cancel_url . '&action=edit';
        echo '<div class="wrap">';
        echo '<h1>' . $this->easymap_make_icon_html( 'appicon' ) . '&nbsp;&nbsp;' . esc_html( EASYMAP_PLUGINNAME_HUMAN ) .
             ': <small>' . esc_html__( 'Location', 'easymap' ) . '</small></h1>';
        if ( empty( $_POST['submit_location'] ) || empty( $_POST['location'] ) ) {
            if ( $location_id == 'new' ) {
                // New location
                $pr = $this->easymap_init_location( true );
                $pr['id'] = $location_id;
            } elseif ( (int)$location_id < 201 && (int)$location_id > 0 ) {
                $pr = $this->easymap_location_list[$location_id];
                // error_log('Existing record:'.print_r($pr, true));
            } else {
                error_log( basename(__FILE__) . ' (' . __FUNCTION__ . '): Unexpected location ID ("' . $location_id . '")' );
                echo '<div class="notice notice-error is-dismissible"><p>'.
                     esc_html__( 'Unable to fetch location', 'easymap' ) . ':<br/><br/>' .
                     esc_html__( 'Invalid location ID', 'easymap' ) . ' (' . esc_html( $location_id ) . ')' .
                     '</p>';
                echo '<p>' .
                     esc_html__( 'Click the back button in your browser and try again', 'easymap' ) .
                     ', ' .
                     esc_html__( 'or', 'easymap' ) . ' ' .
                     '<a href="' . esc_attr( admin_url( 'admin.php' ) . '?page=' . EASYMAP_LOCATION_LIST_PAGE_NAME ) . '">' .
                     esc_html__( 'go to list of locations', 'easymap' ) .
                     '</a></p></div>';
                return;
            }
        } else {
            // Retain form fields
            // error_log( basename(__FILE__) . ' (' . __FUNCTION__ . '): Retaining fields' );
            $pr = $this->easymap_init_location( false );
            $pr['id'] = $_POST['location'];
            $pr['al'] = $this->Utility->x_strtolower( $Utility->x_substr( sanitize_text_field( trim( $_POST['alias'] ) ), 0, 64 ) );
            $pr['na'] = $this->Utility->x_substr( sanitize_text_field( trim( $_POST['name'] ) ), 0, 100 );
            //$pr['co'] = sanitize_hex_color( $this->Utility->x_substr( trim( $_POST['color'] ), 0, 7 ) );
            $pr['sa'] = $this->Utility->x_substr( sanitize_text_field( trim( $_POST['address'] ) ), 0, 100 );
            $pr['sn'] = $this->Utility->x_substr( sanitize_text_field( trim( $_POST['number'] ) ), 0, 20 );
            $pr['ci'] = $this->Utility->x_substr( sanitize_text_field( trim( $_POST['city'] ) ), 0, 100 );
            $pr['st'] = $this->Utility->x_substr( sanitize_text_field( trim( $_POST['state'] ) ), 0, 100 );
            $pr['pc'] = $this->Utility->x_substr( sanitize_text_field( trim( $_POST['zip'] ) ), 0, 100 );
            $pr['ph'] = $this->Utility->x_substr( sanitize_text_field( trim( $_POST['phone'] ) ), 0, 100 );
            $pr['em'] = $this->Utility->x_substr( sanitize_text_field( trim( $_POST['email'] ) ), 0, 100 );
            $pr['ws'] = $this->Utility->x_substr( sanitize_text_field( trim( $_POST['website'] ) ), 0, 100 );
            $pr['la'] = $this->Utility->x_substr( sanitize_text_field( trim( $_POST['pos_lat'] ) ), 0, 32 );
            $pr['lo'] = $this->Utility->x_substr( sanitize_text_field( trim( $_POST['pos_long'] ) ), 0, 32 );
            //$pr['no'] = $this->Utility->x_substr( sanitize_textarea_field( wp_kses_post( trim( $_POST['description'] ) ) ), 0, 1024 );
            $pr['no'] = $this->Utility->x_substr( wp_kses_post( trim( $_POST['description'] ) ) , 0, 1024 );
            if ( ! empty( $_POST['active'] ) ) {
                $pr['ac']   = true;
            } else {
                $pr['ac']   = false;
            }
            if ( ! empty( $_POST['location-override'] ) ) {
                $pr['location_override'] = (int)sanitize_text_field( $_POST['location-override'] );
            }
        }
        ob_start();
        echo '<div id="poststuff">';
        echo '<form method="post" name="easymap-location-edit-form" id="easymap-location-edit-form" action="' . $action . '">';
        $edit_nonce = wp_create_nonce( 'easymap-location-edit' );
        echo wp_nonce_field( 'easymap-location-edit', 'easymap_nonce', true, false );
        // Table navigation retention
        if ( ! empty( $_REQUEST['orderby'] ) ) {
            $field_val = sanitize_key( trim( $_REQUEST['orderby'] ) );
            $cancel_url = add_query_arg( 'orderby', rawurlencode( $field_val ), $cancel_url );
            echo '<input type="hidden" name="orderby" value="' . esc_attr( $field_val ). '" />';
        }
        if ( ! empty( $_REQUEST['order'] ) ) {
            $field_val = sanitize_key( trim( $_REQUEST['order'] ) );
            $cancel_url = add_query_arg( 'order', rawurlencode( $field_val ), $cancel_url );
            echo '<input type="hidden" name="order" value="' . esc_attr( $field_val ). '" />';
        }
        if ( ! empty( $_REQUEST['s'] ) ) {
            $field_val = sanitize_text_field( trim( $_REQUEST['s'] ) );
            $cancel_url = add_query_arg( 's', $field_val, $cancel_url );
            //$cancel_url = add_query_arg( 's', rawurlencode( $field_val ), $cancel_url );
            if ( ! empty( $_REQUEST['snonce'] ) ) {
                $snonce = sanitize_key( $_REQUEST['snonce'] );
                echo '<input type="hidden" name="snonce" value="' . esc_attr( $snonce ). '" />';
                $cancel_url = add_query_arg( 'snonce', $snonce, $cancel_url );
            }
            echo '<input type="hidden" name="s" value="' . esc_attr( $field_val ). '" />';
        }
        if ( ! empty( $_REQUEST['paged'] ) ) {
            $field_val = sanitize_key( trim( $_REQUEST['paged'] ) );
            $cancel_url = add_query_arg( 'paged', (int)$field_val, $cancel_url );
            echo '<input type="hidden" name="paged" value="' . esc_attr( $field_val ). '" />';
        }
        // Editing related
        if ( $location_id !== 'new' ) {
            echo '<input type="hidden" name="location" value="' . esc_attr( (int)$location_id ). '" />';
        } else {
            echo '<input type="hidden" name="location" value="' . esc_attr( $location_id ) . '" />';
        }
        $delete_url = add_query_arg( 'easymap_nonce', $edit_nonce, $cancel_url );
        $delete_url = add_query_arg( 'delete_location', esc_attr( (int)$location_id ), $delete_url );
        echo '<div class="easymap-form-edit-twocol">';
        echo '<div class="easymap-form-edit-pricol" id="easymap-location-primary">';
        // Location name
        $addon_str = ' <small>(';
        if ( $location_id == 'new' ) {
            $addon_str .= esc_html__( 'new location', 'easymap' );
        } else {
            $addon_str .= esc_html__( 'location ID', 'easymap' ) . ' ' . (int)$location_id;
        }
        $addon_str .= ')</small>';
        echo '<h3>' . esc_html__( 'Location details', 'easymap' ) . $addon_str . '</h3>';
        // Row #1
        // Location name
        echo '<div class="easymap-form-row" style="padding-bottom:5px;">';
        echo '<div>';
        echo '<label for="name">' . esc_html__( 'Location name', 'easymap' ) . '</label><br/>';
        echo '<input type="text" name="name" id="name" size="58" maxlength="100" style="width:500px;" value="' . esc_attr( $pr['na'] ) . '" spellcheck="true" autocomplete="off" required />';
        echo '</div>';
        // Location ID
        if ( $location_id == 'new' ) {
            echo '<div>';
            $is_new_location = true;
            $available_loc = array();
            for( $i = 1; $i < 201; $i++ ) {
                if ( empty( $this->easymap_location_list[$i]['na'] ) ) {
                    $available_loc[] = $this->easymap_location_list[$i]['id'];
                }
            }
            if ( ! empty( $available_loc ) ) {
                echo '<label for="location-override" title="' . esc_html__( 'Location ID', 'easymap' ) . '">' .
                     esc_html__( 'ID', 'easymap' ) . '</label><br/>';
                echo '<select name="location-override" id="location-override" style="width:70px;" value="' . esc_attr( $available_loc[0] ) . '">';
                $have_selected = false;
                foreach( $available_loc as $c ) {
                    echo '<option value="' . esc_attr( $c ) . '"';
                    if ( ! $have_selected && ! empty( $pr['location_override'] ) && $pr['location_override'] == $c ) {
                        echo ' selected';
                        $have_selected = true;
                    }
                    echo '>' . esc_html( $c ). '</option>';
                }
                echo '</select></label>';
            } else {
                ob_end_clean();
                echo '<div class="notice notice-error is-dismissible"><p>'.
                     esc_html__( 'No available location IDs', 'easymap' ) .
                     '</p>';
                echo '<p>' .
                     esc_html__( 'Click the back button in your browser and try again', 'easymap' ) .
                     ', ' .
                     esc_html__( 'or', 'easymap' ) . ' ' .
                     '<a href="' . $cancel_url . '">' .
                     esc_html__( 'go to list of location', 'easymap' ) .
                     '</a></p></div>';
                return;
            }
            echo '</div>';
        } else {
            $is_new_location = false;
        }
        // Location color
        /*
        echo '<div>';
        echo '<label for="color" title="' . esc_html__( 'Location color', 'easymap' ) . '">' . esc_html__( 'Color', 'easymap' ) . '</label><br/>';
        echo '<input type="text" name="color" id="color" size="8" maxlength="7" pattern="[#][a-fA-F\d]+" style="width:80px;" value="' . esc_attr( $pr['co'] ) . '" spellcheck="true" autocomplete="off" placeholder="#hhhhhh" required />';
        echo '&nbsp;<a href="https://www.w3schools.com/colors/colors_picker.asp" class="easymap-ext-link" target="_blank">' .
             esc_html__( 'Color picker', 'easymap' ) .
             '</a>';
        echo '</div>';
        */
        echo '</div>';
        // Row #2
        echo '<div class="easymap-form-row" style="padding-bottom:5px;">';
        echo '<div>';
        echo '<label for="address">' . esc_html__( 'Street address', 'easymap' ) . '</label><br/>';
        echo '<input type="text" name="address" id="address" size="58" maxlength="100" style="width:500px;" value="' . esc_attr( $pr['sa'] ) . '" spellcheck="true" autocomplete="off" required />';
        echo '</div>';
        echo '<div>';
        echo '<label for="number" title="' . esc_html__( 'Street number', 'easymap' ) . '">&nbsp;#</label><br/>';
        echo '<input type="text" name="number" id="number" size="5" maxlength="20" style="width:72px;" value="' . esc_attr( $pr['sn'] ) . '" spellcheck="false" autocomplete="off" />';
        echo '</div>';
        echo '<div>';
        echo '<div style="padding-top:3px;">' . esc_html__( 'Active', 'easymap' ) . '</div>';
        echo '<fieldset style="padding-top:10px;display:flex;flex-direction:row;flex-wrap:wrap;">';
        echo '&nbsp;<input type="radio" name="active" id="active_yes" value="1" ' . ( $pr['ac'] ? 'checked="checked" ':'' ) . '/> ';
        echo '<label for="active_yes" style="padding-left:0px;padding-right:15px;">' . esc_html__( 'Yes' ) . '</label>';
        echo '<input type="radio" name="active" id="active_no" value="0" ' . ( empty( $pr['ac'] ) ? 'checked="checked" ':'' ) . '/> ';
        echo '<label for="active_no" style="padding-left:0px;">' . esc_html__( 'No' ) . '</label>';
        echo '</fieldset>';
        echo '</div>';
        echo '</div>';
        // Row #3
        echo '<div class="easymap-form-row" style="padding-bottom:5px;">';
        echo '<div>';
        echo '<label for="zip">' . esc_html__( 'Postal code', 'easymap' ) . '</label><br/>';
        echo '<input type="text" name="zip" id="zip" size="10" maxlength="100" style="width:114px;" value="' . esc_attr( $pr['pc'] ) . '" spellcheck="false" autocomplete="off" />';
        echo '</div>';
        echo '<div>';
        echo '<label for="city">' . esc_html__( 'City', 'easymap' ) . '</label><br/>';
        echo '<input type="text" name="city" id="city" size="31" maxlength="100" style="width:286px;" value="' . esc_attr( $pr['ci'] ) . '" spellcheck="false" autocomplete="off" />';
        echo '</div>';
        echo '<div>';
        echo '<label for="state">' . esc_html__( 'State', 'easymap' ) . '</label><br/>';
        echo '<input type="text" name="state" id="state" size="17" maxlength="100" style="width:170px;" value="' . esc_attr( $pr['st'] ) . '" spellcheck="false" autocomplete="off" />';
        echo '</div>';
        echo '</div>';
        // Row #4
        echo '<div class="easymap-form-row" style="padding-bottom:5px;">';
        echo '<div>';
        echo '<label for="email">' . esc_html__( 'E-mail', 'easymap' ) . '</label><br/>';
        echo '<input type="text" name="email" id="email" size="20" maxlength="100" style="width:200px;" value="' . esc_attr( $pr['em'] ) . '" spellcheck="false" autocomplete="off" />';
        echo '</div>';
        echo '<div>';
        echo '<label for="phone">' . esc_html__( 'Phone', 'easymap' ) . '</label><br/>';
        echo '<input type="text" name="phone" id="phone" size="20" maxlength="100" style="width:200px;" value="' . esc_attr( $pr['ph'] ) . '" spellcheck="false" autocomplete="off" />';
        echo '</div>';
        echo '<div>';
        echo '<label for="website">' . esc_html__( 'Website', 'easymap' ) . '</label><br/>';
        echo '<input type="text" name="website" id="website" size="28" maxlength="100" style="width:336px;" value="' . esc_attr( $pr['ws'] ) . '" spellcheck="false" autocomplete="off" />';
        echo '</div>';
        echo '</div>';
        // Row #5
        echo '<div class="easymap-form-row" style="padding-bottom:5px;">';
        echo '<div>';
        echo '<label for="pos_lat">' . esc_html__( 'Latitude', 'easymap' ) . '</label><br/>';
        echo '<input type="text" name="pos_lat" id="pos_lat" size="20" maxlength="32" style="width:200px;" value="' . esc_attr( $pr['la'] ) . '" spellcheck="false" autocomplete="off" />';
        echo '</div>';
        echo '<div>';
        echo '<label for="pos_long">' . esc_html__( 'Longitude', 'easymap' ) . '</label><br/>';
        echo '<input type="text" name="pos_long" id="pos_long" size="20" maxlength="32" style="width:200px;" value="' . esc_attr( $pr['lo'] ) . '" spellcheck="false" autocomplete="off" />';
        echo '</div>';
        echo '<div>';
        echo '<label for="alias">' . esc_html__( 'Location alias', 'easymap' ) . '</label><br/>';
        echo '<input type="text" name="alias" id="alias" size="28" maxlength="64" style="width:336px;" value="' . esc_attr( $pr['al'] ) . '" spellcheck="false" autocomplete="off" />';
        echo '</div>';
        echo '</div>';
        // Description
        echo '<div class="easymap-form-row" style="padding-bottom:5px;">';
        echo '<div style="flex: 1 !important;">';
        echo '<label for="description">' . esc_html__( 'Description (HTML allowed)', 'easymap' ) . '</label><br/>';
        echo '<textarea name="description" id="description" rows="6" style="width:100% !important;min-width:450px;">' .
             esc_html( $pr['no'] ) .
             '</textarea>';
        echo '</div>';
        echo '</div>';
        echo '<div class="easymap-form-row" style="padding-bottom:5px;">';
        echo '<div>';
        submit_button( __( 'Save', 'easymap' ), 'primary', 'submit_location', false );
        echo '&nbsp;';
        echo '<a href="' . esc_url_raw( $cancel_url ) . '" class="button cancel">' . esc_html__( 'Cancel', 'easymap' ) . '</a>';
        echo '</div>';
        echo '<div style="margin-left:auto;">';
        echo '<input type="reset" name="reset_location" id="reset_location" class="button button-secondary" value="' . esc_html__( 'Reset form data', 'easymap' ) . '" />';
        echo '</div>';
        echo '</div>';
        echo '</div>';//easymap-form-edit-pricol

        $output_copy_JS = false;

        // 50%
        echo '<div class="easymap-form-edit-seccol" id="easymap-location-secondary">';
        echo '<h2 style="padding-left:0;padding-bottom:0;">' . esc_html__( 'Google Maps lookup', 'easymap' ) . '</h2>';
        if ( empty( $this->easymap_google_geodata_api_key ) ) {
            echo '<p>' . esc_html__( 'Please configure your', 'easymap' ) .
                 ' <a href="' . esc_url( admin_url('admin.php') . '?page=' . 'easymap' ) . '">' .
                 esc_html__( 'Google Geodata API key', 'easymap' ) . '</a> ' .
                 esc_html__( 'to perform lookups', 'easymap' ) . '</p>';
        } elseif ( empty ( $pr['sa'] ) ) {
            echo '<p>' . esc_html__( 'Please specify, as a minimum, the location street address to perform a Google Maps lookup', 'easymap' ) . '</p>';
        } else {
            // Google Maps lookup
            //https://developer.wordpress.org/reference/classes/wp_http/request/
            $url = 'https://maps.googleapis.com/maps/api/geocode/json' .
                   '?address=';
            $address = $pr['sa'];
            if ( ! empty( $pr['sn'] ) ) {
                // Append street number
                $address .= ' ' . $pr['sn'];
            }
            if ( ! empty( $pr['pc'] ) && ! empty( $pr['ci'] ) ) {
                // Append postal/zipcode and city
                $address .= ',' . $pr['pc'] . ' ' . $pr['ci'];
            } elseif ( ! empty( $pr['ci'] ) ) {
                // Append city
                $address .= ',' . $pr['ci'];
            }
            if ( ! empty( $pr['st'] ) ) {
                // Append state
                $address .= ',' . $pr['st'];
            }
            if ( ! empty( $this->easymap_google_country ) ) {
                // Append default country, if any
                $address .= ',' . $this->easymap_google_country;
            }
            $url .= urlencode( $address ) . '&key=' . $this->easymap_google_geodata_api_key . '&region=' . urlencode( $this->easymap_google_region );
            $google_lookup = wp_remote_get( $url, array( 'method' => 'GET',
                                            // '
                                            ), );
            if ( is_array( $google_lookup ) && ! is_wp_error( $google_lookup ) ) {
                if ( empty( $google_lookup['response'] ) || empty( $google_lookup['response']['code'] ) || $google_lookup['response']['code'] != 200 ) {
                    echo '<div class="easymap-error">'.
                         esc_html__( 'Unable to retrieve information from Google Maps', 'easymap' ) .
                         '</div>';
                    if ( ! empty( $google_lookup['response'] ) ) {
                        error_log( basename( __FILE__ ) . ' (' . __FUNCTION__ . '): Google lookup returned error: ' . "\n" .
                                   print_r( $google_lookup['response'], true ) );
                    } else {
                        error_log( basename( __FILE__ ) . ' (' . __FUNCTION__ . '): Google lookup returned unkown error' );
                    }
                } else {
                    $json = @ json_decode( wp_remote_retrieve_body( $google_lookup ), true );
                    /*echo "<pre>JSON: " . esc_html(print_r($json, true)) . '</pre>';*/
                    if ( empty( $json['results'][0] ) ) {
                        echo '<div class="easymap-error">'.
                             esc_html__( 'Unable to retrieve information from Google Maps', 'easymap' ) .
                             '</div>';
                    } else {
                        $json = $json['results'][0];
                        if ( ! empty( $json['formatted_address'] ) ) {
                            echo '<p><strong>' .
                                    esc_html__( 'Address', 'easymap' ) .
                                    '</strong><br/>' .
                                    esc_html( $json['formatted_address'] ) . '</p>';
                            }
                        echo '<p><strong>' .
                             esc_html__( 'Geolocation', 'easymap' ) .
                             '</strong><br/>';
                        if ( ! empty( $json['geometry']['location'] ) ) {
                            echo esc_html__( 'Lat') . ': ' . esc_html( $json['geometry']['location']['lat'] ) . '&nbsp;&nbsp;&nbsp;' .
                                 esc_html__( 'Long', 'easymap' ) . ': ' . esc_html( $json['geometry']['location']['lng'] );
                            if ( (string)$json['geometry']['location']['lat'] == (string)$pr['la'] && (string)$json['geometry']['location']['lng'] == (string)$pr['lo'] ) {
                                // Show green checkmark to indicate it matches stored information
                                echo '&nbsp;' . $this->easymap_make_icon_html( 'greencheck' );
                            } else {
                                // Allow geolocation information to be copied
                                echo '<div>';
                                echo '<button type="button" class="button" name="easymap_copygeo" id="easymap_copygeo" ' .
                                     'data-coord="' . esc_attr( $json['geometry']['location']['lat'] . ',' . $json['geometry']['location']['lng'] ) . '">' .
                                     esc_html__( 'Copy', 'easymap' ) . '&nbsp;' . $this->easymap_make_icon_html( 'copy' ) .
                                     '</button>';
                                echo '</div>';
                                $output_copy_JS = true;
                            }
                        } else {
                            esc_html__( 'No information', 'easymap' );
                        }
                        echo '</p>';
                        /*
                        echo '<pre>';
                        print_r($json);
                        echo '</pre>';
                        */
                    }
                }
            } else {
                echo '<div class="easymap-error">'.
                     esc_html__( 'Unable to retrieve information from Google Maps', 'easymap' ) .
                     '</div>';
            }
        }

        if ( defined( 'EASYMAP_DEBUG' ) && EASYMAP_DEBUG ) {
            echo '<div style="background-color:greenyellow;border:3px solid green;padding:3px;">{debug}';
            echo '<p>';
            echo esc_url( $url );
            echo '</p>';
            echo '<p><pre>';
            echo print_r($_REQUEST, true);
            echo '</pre>';
            echo '</div>';
        }

        echo '</div>';//easymap-form-edit-seccol

        echo '</div>';// easymap-form-edit-twocol

        echo '</form>';
        echo '</div>';
        echo '</div>';

        // Possibly output Javascript to handle geolocation copy button
        if ( $output_copy_JS ) {
            echo "\n" . '<script type="text/javascript">';
            echo 'let easymapCopyGeoButton;' . "\n";
            echo 'function easymapDoCopyGeo(e) {
                      var googleCoord = easymapCopyGeoButton.getAttribute("data-coord").split(",");
                      document.getElementById("pos_lat").setAttribute("value", googleCoord[0]);
                      document.getElementById("pos_long").setAttribute("value", googleCoord[1]);
                  }' . "\n";
            echo 'function easymapCopyGeoSetup() {
                    easymapCopyGeoButton = document.getElementById("easymap_copygeo");
                    if (easymapCopyGeoButton) {
                      easymapCopyGeoButton.addEventListener("click", easymapDoCopyGeo);
                    }
                  }' . "\n";
            echo 'if (document.readyState === "complete" ||
                    (document.readyState !== "loading" && !document.documentElement.doScroll)) {
                        easymapCopyGeoSetup();
                    } else {
                        document.addEventListener("DOMContentLoaded", easymapCopyGeoSetup);
                  }' . "\n";
            echo '</script>' . "\n";
        }
        ob_end_flush();
    }

    /**
     * Shortcode handler for [easymap_map].
     *
     * Handler for [easymap_map] shortcode, public facing side only.
     *
     * [easymap_map markers="alias1,id1,id2,alias2,..."][/easymap_map]
     * [easymap_map poi=true][/easymap_map]
     *
     * @since 1.0.0
     */
    public function easymap_shortcode_map( $atts, $content, $tag ) {
        if ( defined( 'EASYMAP_DEBUG' ) && EASYMAP_DEBUG ) {
            error_log( basename(__FILE__) . ' (' . __FUNCTION__ . ')' );
            error_log( '$atts: ' . var_export( $atts, true ) );
            error_log( '$content: "' . $content . '"' );
            error_log( '$tag: "' . $tag . '"' );
        }
        // Make sure there's a key
        if ( empty( $this->easymap_google_geodata_api_key ) ) {
            error_log( basename(__FILE__) . ' (' . __FUNCTION__ . '): No Google Geodata services API key configured' );
            return( '' );
        }
        // Make sure we don't do more than one shortcode per run
        if ( $this->easymap_did_map_shortcode ) {
            error_log( basename(__FILE__) . ' (' . __FUNCTION__ . '): Shortcode for EasyMap has already been used once' );
            return( '' );
        } else {
            $this->easymap_did_map_shortcode = true;
        }

        // Figure out selected markers (we default to all)
        $markers = array();
        if ( empty( $atts['markers'] ) ) {
            // No markers specified
            foreach( $this->easymap_location_list as $k => $v ) {
                if ( $v['ac'] && ! empty( $v['la'] ) && ! empty( $v['lo'] ) ) {
                    $markers[] = array( 'na' => $v['na'],
                                        'sa' => $v['sa'],
                                        'sn' => $v['sn'],
                                        'ci' => $v['ci'],
                                        'st' => $v['st'],
                                        'pc' => $v['pc'],
                                        'ph' => $v['ph'],
                                        'em' => $v['em'],
                                        'ws' => $v['ws'],
                                        'no' => nl2br( $v['no'] ),
                                        'la' => $v['la'],
                                        'lo' => $v['lo'] );
                }
            }
        } else {
            // Specific markers specified
            $atts['markers'] = $this->Utility->x_strtolower( $atts['markers'] );
            $selected_markers = explode( ',', $atts['markers'], 255 );
            foreach( $selected_markers as $s_k => $s_v ) {
                $s_v = trim( $s_v );
                $got_match = false;
                if ( is_numeric( $s_v ) ) {
                    foreach( $this->easymap_location_list as $k => $v ) {
                        if ( $v['id'] == $s_v ) {
                            $got_match = true;
                            $marker = $v;
                        }
                    } // foreach (locations)
                } else {
                    foreach( $this->easymap_location_list as $k => $v ) {
                        if ( $v['al'] == $s_v ) {
                            $got_match = true;
                            $marker = $v;
                        }
                    } // foreach (locations)
                }
                if ( $got_match ) {
                    if ( ! empty( $marker['ac'] ) ) {
                        if ( ! empty( $marker['la'] ) && ! empty( $marker['lo'] ) ) {
                            $markers[] = array( 'na' => $marker['na'],
                                                'sa' => $marker['sa'],
                                                'sn' => $marker['sn'],
                                                'ci' => $marker['ci'],
                                                'st' => $marker['st'],
                                                'pc' => $marker['pc'],
                                                'ph' => $marker['ph'],
                                                'em' => $marker['em'],
                                                'ws' => $marker['ws'],
                                                'no' => nl2br( $marker['no'] ),
                                                'la' => $marker['la'],
                                                'lo' => $marker['lo'] );
                        } else {
                            error_log( basename(__FILE__) . ' (' . __FUNCTION__ . '): Specified marker "' . $s_v . '" is missing geo coordinates, skipped' );
                        }
                    } else {
                        error_log( basename(__FILE__) . ' (' . __FUNCTION__ . '): Specified marker "' . $s_v . '" is not set to active, skipped' );
                    }
                }
            }
        }
        // Other arguments
        $greedy_control = $this->easymap_google_greedy_control;
        if ( ! empty( $atts['greedy'] ) ) {
            if  ( $atts['greedy'] == '1' || $this->Utility->x_strtolower( $atts['greedy'] ) == 'true' ) {
                $greedy_control = true;
            } else {
                $greedy_control = false;
            }
        }
        $show_fullscreen = $this->easymap_google_show_fullscreen;
        if ( ! empty( $atts['fullscreen'] ) ) {
            if  ( $atts['fullscreen'] == '1' || $this->Utility->x_strtolower( $atts['fullscreen'] ) == 'true' ) {
                $show_fullscreen = true;
            } else {
                $show_fullscreen = false;
            }
        }
        $show_streetview = $this->easymap_google_show_streetview;
        if ( ! empty( $atts['streetview'] ) ) {
            if  ( $atts['streetview'] == '1' || $this->Utility->x_strtolower( $atts['streetview'] ) == 'true' ) {
                $show_streetview = true;
            } else {
                $show_streetview = false;
            }
        }
        $show_poi = $this->easymap_google_show_poi;
        if ( ! empty( $atts['poi'] ) ) {
            if ( $atts['poi'] == '1' || $this->Utility->x_strtolower( $atts['poi'] ) == 'true' ) {
                $show_poi = true;
            } else {
                $show_poi = true;
            }
        }
        $show_transit = $this->easymap_google_show_transit;
        if ( ! empty( $atts['transit'] ) ) {
            if ( $atts['transit'] == '1' || $this->Utility->x_strtolower( $atts['transit'] ) == 'true' ) {
                $show_transit = true;
            } else {
                $show_transit = false;
            }
        }
        $show_landscape = $this->easymap_google_show_landscape;
        if ( ! empty( $atts['landscape'] ) ) {
            if ( $atts['landscape'] == '1' || $this->Utility->x_strtolower( $atts['landscape'] ) == 'true' ) {
                $show_landscape = true;
            } else {
                $show_landscape = false;
            }
        }
        $show_zoom = $this->easymap_google_start_zoom;
        if ( ! empty( $atts['zoom'] ) && is_numeric( $atts['zoom'] ) ) {
            $show_zoom = (int)$atts['zoom'];
        }

        // Google Maps options
        if ( empty( $this->easymap_google_start_lat ) || empty( $this->easymap_google_start_lat ) ) {
            $easymap_posStart = '';
        } else {
            $easymap_posStart = 'center: {lat:' . $this->easymap_google_start_lat . ',lng:' . $this->easymap_google_start_lng . '},';
        }
        if ( empty( $show_zoom ) || (int)$show_zoom > 18 || (int)$show_zoom < 1 ) {
            $easymap_setZoom = '';
        } else {
            $easymap_setZoom = 'var easymap_listener = google.maps.event.addListener(easymap_map, "idle", function(){
                                  easymap_map.setZoom(' . (int)$show_zoom . ');
                                  google.maps.event.removeListener(easymap_listener);
                                  });';
        }
        $easymap_styles = '';
        if ( empty( $show_poi ) ) {
            $easymap_styles .= '{featureType: "poi", elementType: "labels", stylers: [{ visibility: "off" }]},';
        } else {
            $easymap_styles .= '{featureType: "poi", elementType: "labels", stylers: [{ visibility: "on" }]},';
        }
        if ( empty( $show_transit ) ) {
            $easymap_styles .= '{featureType: "transit", elementType: "labels", stylers: [{ visibility: "off" }]},';
        } else {
            $easymap_styles .= '{featureType: "transit", elementType: "labels", stylers: [{ visibility: "on" }]},';
        }
        if ( empty( $show_landscape ) ) {
            $easymap_styles .= '{featureType: "landscape", elementType: "labels", stylers: [{ visibility: "off" }]},';
        } else {
            $easymap_styles .= '{featureType: "landscape", elementType: "labels", stylers: [{ visibility: "on" }]},';
        }
        if ( ! empty( $easymap_styles ) ) {
            $easymap_styles = 'styles:[' . $easymap_styles . '],';
        }
        if ( $this->easymap_google_marker_animation ) {
            $easymap_marker_animation = '
                       pin.addListener("mouseover", () => {
                         if (pin.getAnimation() == null || typeof pin.getAnimation() === "undefined") {
                           clearTimeout(easymap_bounceTimer);
                           var thePin = pin;
                           easymap_bounceTimer = setTimeout(function(){
                             thePin.setAnimation(google.maps.Animation.BOUNCE);
                           }, 250);
                        }
                       });
                       pin.addListener("mouseout", () => {
                         if (pin.getAnimation() !== null) {
                            pin.setAnimation(null);
                         }
                         clearTimeout(easymap_bounceTimer);
                       });
                       ';
        } else {
            $easymap_marker_animation = '';
        }
        if ( $this->easymap_google_marker_hovereffect ) {
            $easymap_marker_hover = '
                       pin.addListener("mouseover", () => {
                         pin.setIcon(easymap_marker_yellow);
                       });
                       pin.addListener("mouseout", () => {
                         pin.setIcon(easymap_marker_std);
                       });
                       ';
        } else {
            $easymap_marker_hover = '';
        }
        // Google Maps javascript
        $html = '<script type="text/javascript">
                    const easymap_info_format = "' . $this->easymap_google_marker_address_data . '";
                    const easymap_info_regex = /{[\w+]+}/g ;
                    var easymap_info_array = null;
                    const easymap_marker_std = {url:"https://maps.google.com/mapfiles/ms/icons/red-dot.png",};
                    const easymap_marker_yellow = {url:"https://maps.google.com/mapfiles/ms/icons/yellow-dot.png",};
                    var easymap_mapOptions;
                    let easymap_markers = ' . @ json_encode( $markers /*, JSON_NUMERIC_CHECK */ ) . ';
                    let easymap_map;
                    let easymap_mapInfo;
                    var easymap_bounds = null;
                    var easymap_bounceTimer = null;
                    var easymap_setup = function() {
                        easymap_info_array = easymap_info_format.split("{br}");
                    }
                    function easymap_makepin(marker) {
                        const pin = new google.maps.Marker({
                            map: easymap_map,
                            position: {lat: parseFloat(marker.la), lng: parseFloat(marker.lo)},
                            title: `${marker.na}`,
                            icon: easymap_marker_std,
                            /*label: "",*/
                            visible: true,
                            animation: null,
                        });
                        easymap_bounds.extend(pin.position);
                        ' . $easymap_marker_hover .
                        $easymap_marker_animation . '
                        pin.addListener("click", () => {
                            var infoContent = "<div id=\"easymap-info-window\">" +
                                              "<div class=\"easymap-info-title\">" + `${marker.na}` + "</div>";
                            /*
                            easymap_info_array.forEach(function(keyword,index,a) {
                                const matches = keyword.matchAll(easymap_info_regex);
                                let alert_text = keyword + "\n";
                                for (const match of matches) {
                                    alert_text += `Found ${match[0]} start=${match.index} end=${match.index + match[0].length}` + "\n";
                                }
                                alert(alert_text);
                            });
                            */
                            /*
                            const matches = info_text.matchAll(keywords);
                            let alert_text = "";
                            for (const match of matches) {
                                alert_text += `Found ${match[0]} start=${match.index} end=${match.index + match[0].length}` + "\n";
                            }
                            alert_text += info_text;
                            alert(alert_text);
                            */
                            if (marker.ws.length) {
                                var infoWebsite = "<div class=\"easymap-info-website\">" +
                                    "<a class=\"easymap-info-link\" target=\"_blank\" href=\"" + `${marker.ws}` + "\">" + `${marker.ws}` + "</a>" +
                                    "</div>";
                            } else {
                                var infoWebsite = "";
                            }
                            if (marker.em.length) {
                                var infoEmail = "<div class=\"easymap-info-email\">" +
                                    "<a class=\"easymap-info-link\" href=\"mailto:" + `${marker.em}` + "\">" + `${marker.em}` + "</a>" +
                                    "</div>";
                            } else {
                                var infoEmail = "";
                            }
                            if (marker.ph.length) {
                                var infoPhone  = "<div class=\"easymap-info-phone\">" +
                                    "<a class=\"easymap-info-link\" href=\"tel:" + `${marker.ph}` + "\">" + `${marker.ph}` + "</a>" +
                                    "</div>";
                            } else {
                                var infoPhone = "";
                            }
                            if (infoWebsite.length || infoEmail.length || infoPhone.length) {
                                infoContent += "<div class=\"easymap-info-contact\">" +
                                               infoWebsite +
                                               infoEmail +
                                               infoPhone +
                                               "</div>";
                            }
                            if (marker.sa.length || marker.ci.length || marker.st.length) {
                                infoContent += "<div class=\"easymap-info-address\">";
                                easymap_info_array.forEach(function(keyword,index,a) {
                                    let matches;
                                    let match;
                                    let replacement;
                                    do {
                                        matches = keyword.matchAll(easymap_info_regex);
                                        let matchCount = 0;
                                        for (match of matches) {
                                            matchCount++;
                                            switch(match[0]) {
                                                case "{streetname}":   replacement = marker.sa; break;
                                                case "{streetnumber}": replacement = marker.sn; break;
                                                case "{city}":         replacement = marker.ci; break;
                                                case "{state}":        replacement = marker.st; break;
                                                case "{postalcode}":   replacement = marker.pc; break;
                                                default:               replacement = "";        break;
                                            }
                                            let new_keyword = keyword.substring(0, match.index) + replacement + keyword.substring(match.index+match[0].length);
                                            keyword = new_keyword;
                                            break;
                                        }
                                        if (matchCount == 0) {
                                            break;
                                        }
                                    } while (matches);
                                    infoContent += keyword + "<br/>";
                                });
                                infoContent += "</div>";
                            }
                            if (marker.no.length) {
                                infoContent += "<div class=\"easymap-info-description\">" +
                                "<div class=\"easymap-info-description-text\">" +
                                `${marker.no}` +
                                "</div>" +
                                "</div>";
                            }
                            infoContent += "</div>";
                            easymap_mapInfo.setContent(infoContent);
                            easymap_mapInfo.open(easymap_map, pin);
                        });
                        return(pin);
                    }
                    function easymap_initmap() {
                        easymap_bounds = new google.maps.LatLngBounds();
                        easymap_mapOptions = {
                          ' . $easymap_posStart .
                          ( ! empty( $show_fullscreen ) ?
                            '' : 'zoomControlOptions: {
                                    position: google.maps.ControlPosition.TOP_RIGHT,
                                  },' ) .
                          ( ! empty( $greedy_control ) ?
                            'gestureHandling: "greedy",' : '' ) . '
                          zoomControl: true,
                          mapTypeControl: ' . ( ! empty( $this->easymap_google_show_typeselector ) ? 'true':'false' ) . ',
                          streetViewControl: ' . ( ! empty( $show_streetview ) ? 'true':'false' ) . ',
                          fullscreenControl: ' . ( ! empty( $show_fullscreen ) ? 'true':'false' ) . ',
                          ' . $easymap_styles . '
                        };
                        easymap_map = new google.maps.Map(document.getElementById("easymap-map"), easymap_mapOptions);
                        easymap_mapInfo = new google.maps.InfoWindow({content:"<h1>EasyMap</h1>"});
                        easymap_map.addListener("click", () => {
                            easymap_mapInfo.close();
                        });
                        easymap_markers.forEach(function(easymap_markers,index,a) {
                            a[index]["marker"] = easymap_makepin(easymap_markers);
                        });
                        easymap_map.fitBounds(easymap_bounds);
                        easymap_map.panToBounds(easymap_bounds);
                        ' . $easymap_setZoom . '
                    }
                    /* Make sure we are ready */
                    if (document.readyState === "complete" ||
                            (document.readyState !== "loading" && !document.documentElement.doScroll)) {
                        easymap_setup();
                    } else {
                        document.addEventListener("DOMContentLoaded", easymap_setup);
                    }
                </script>
                ';
        // Where the map is displayed
        $html .= '<div id="easymap-map" class="easymap-map"></div>';
        // Let's go ...
        $html .= '<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?'.
                 'key=' . $this->easymap_google_geodata_api_key .
                 '&callback=easymap_initmap&libraries=&v=weekly'.
                 '&region=' . rawurlencode( $this->easymap_google_region ) .
                 '&language=' . rawurlencode( $this->easymap_google_language ) . '"></script>';
        return( $html );
    }
    /**
     * Init public facing side.
     *
     * @since 1.0.0
     */
    public function easymap_setup_public() {
        if ( is_admin() ) {
            return;
        }
        add_shortcode( 'easymap_map', [ $this, 'easymap_shortcode_map' ] );
    }
    /**
     * Do other init that needs to be delayed.
     *
     * @since 1.0.0
     */
    public function easymap_setup_other() {
        if ( defined( 'EASYMAP_DEBUG' ) && EASYMAP_DEBUG ) {
            error_log( basename(__FILE__) . ' (' . __FUNCTION__ . ')' );
        }
        // Setup "global" nonce for ajax, etc.
        $this->easymap_nonce = wp_create_nonce( EASYMAP_PLUGINNAME_SLUG . EASYMAP_VERSION );
        // Setup URL for ajax
        $this->easymap_ajax_url = admin_url( 'admin-ajax.php' );
    }
    /**
     * Debugging
     */
    public function easymap_admin_debug_update_option( $option, $old_value, $value ) {
        if ( defined( 'EASYMAP_DEBUG' ) && EASYMAP_DEBUG ) {
            if ( defined( 'EASYMAP_DEBUG_OPTIONS' && EASYMAP_DEBUG_OPTIONS ) ) {
                error_log( basename(__FILE__) . ' (' . __FUNCTION__ . ')' );
                error_log( 'option="' . $option . '" value="' . var_export( $value, true) . '" old_value="' . var_export( $old_value, true) );
            }
        }
    }
    public function easymap_admin_debug_updated_option( $option, $old_value, $value ) {
        if ( defined( 'EASYMAP_DEBUG' ) && EASYMAP_DEBUG ) {
            if ( defined( 'EASYMAP_DEBUG_OPTIONS' && EASYMAP_DEBUG_OPTIONS ) ) {
                error_log( basename(__FILE__) . ' (' . __FUNCTION__ . ')' );
                error_log( 'option="' . $option . '" value="' . var_export( $value, true) . '" old_value="' . var_export( $old_value, true) );
            }
        }
    }
    /**
     * Setup language support.
     *
     * @since 1.0.0
     */
    public function setup_locale() {
		if ( ! load_plugin_textdomain( EASYMAP_PLUGINNAME_SLUG,
                                       false,
                                       dirname( plugin_basename( __FILE__ ) ) . '/languages' ) ) {
            /**
             * We don't consider this to be a "real" error since 1.1.0
             */
            // error_log( 'Unable to load language file (' . dirname( plugin_basename( __FILE__ ) ) . '/languages' . ')' );
        }
    }
    /**
     * Run plugin.
     *
     * Basically "enqueues" WordPress actions and lets WordPress do its thing.
     *
     * @since 1.0.0
     */
    public function run() {
        // Plugin activation, not needed for this plugin atm :-)
        // register_activation_hook( __FILE__, [$this, 'easymap_activate_plugin'] );

        // Setup i18n. We use the 'init' action rather than 'plugins_loaded' as per
        // https://developer.wordpress.org/reference/functions/load_plugin_textdomain/#user-contributed-notes
        add_action( 'init',                      [$this, 'setup_locale']              );

        // Admin setup
        if ( is_admin() ) {
            add_action( 'admin_enqueue_scripts', [$this, 'easymap_setup_admin_css']   );
            add_action( 'admin_menu',            [$this, 'easymap_menu']              );
            add_action( 'admin_init',            [$this, 'easymap_settings']          );

            if ( defined( 'EASYMAP_DEBUG' ) && EASYMAP_DEBUG ) {
                add_action( 'update_option', [$this, 'easymap_admin_debug_update_option'], 10, 3 );
                add_action( 'updated_option', [$this, 'easymap_admin_debug_updated_option'], 10, 3 );
            }

            // Add CSS for tables
            if ( ! empty( $_GET['page'] ) ) {
                if ( $_GET['page'] == EASYMAP_LOCATION_LIST_PAGE_NAME ) {
                    add_action( 'admin_head', [$this, 'easymap_admin_locations_css'] );
                }
            }
        } else {
        // Public setup
            add_action( 'wp_enqueue_scripts',    [$this, 'easymap_setup_public_css']  );
        }

        // Other setup
        add_action( 'wp_loaded',                 [$this, 'easymap_setup_public']      );
        add_action( 'init',                      [$this, 'easymap_setup_other']       );
        /*
        add_action( 'wp_loaded',                 [$this, 'easymap_wp_loaded'] );
        add_action( 'parse_request',             [$this, 'easymap_parse_request'] );
        */

        // add_action( 'wp',                  [$this, 'easymap_wp_main']              );
        // add_action( 'pre_get_posts',       [$this, 'easymap_pgp']                  );
        // Plugin deactivation, not needed atm :-)
        // register_deactivation_hook( __FILE__, [$this, 'easymap_deactivate_plugin'] );
    }

}// EasyMap


/**
 * Run plugin
 *
 * @since 1.0.0
 */
function run_easymaps() {
    $plugin = EasyMap::getInstance( EASYMAP_VERSION );
    $plugin->run();
}

run_easymaps();
