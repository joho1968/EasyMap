<?php
/**
 * EasyMap Location List Admin Class
 *
 * @since      1.0.0
 * @package    EasyMap
 * @subpackage easymap/includes
 * @author     Joaquim Homrighausen <joho@webbplatsen.se>
 *
 * class_location_admin.inc.php
 * Copyright (C) 2021 Joaquim Homrighausen where applicable
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
if ( ! defined( 'EASYMAP_WORDPRESS_PLUGIN' ) ) {
    die( '-1' );
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
if ( ! defined( 'EASYMAP_LOCATION_LIST_PAGE_NAME' ) ) {
    define( 'EASYMAP_LOCATION_LIST_PAGE_NAME', 'easymap-locations'   );
}

class EasyMap_Location_List extends \WP_List_Table {
    public static $instance = null;
    protected $Utility;                                         // @since 1.0.1
    protected array $easymap_notify;                            // @since 1.0.0
    protected int $easymap_icon_style;                          // @since 1.0.0
    protected $easymap_location_list;                           // @since 1.0.0
    protected $collator;                                        // @since 1.0.0
    protected $locale;                                          // @since 1.0.0
    protected $single_item_edit_nonce;                          // @since 1.0.0

    public static function getInstance( int $icon_style, array $location_list, string $locale, $Utility ) {
        null === self::$instance AND self::$instance = new self( $icon_style, $location_list, $locale, $Utility );
        return( self::$instance );
    }
    /**
    * Start me up ...
    */
    public function __construct( int $icon_style, array $location_list, string $locale, $Utility ) {
        parent::__construct( array(
                                'singular' => 'easymap-location',
                                'plural'   => 'easymap-locations',
                                'ajax'     => false,
                                )
                            );
        $this->Utility = $Utility;
        $this->easymap_icon_style = $icon_style;
        $this->easymap_location_list = $location_list;
        $this->easymap_notify = array();
        if ( empty( $locale ) ) {
            $locale = 'en_US.UTF-8';
        }
        $this->locale = $locale;
        if ( function_exists( 'collator_create' ) ) {
            $this->collator = collator_create( $this->locale );
            if ( ! is_object( $this->collator ) ) {
                error_log( basename(__FILE__) . ' (' . __FUNCTION__ . '): Unable to create collator ("' . intl_error_message() . '")');
                $this->collator = false;
            }
        } else {
            $this->collator = false;
        }
        $this->single_item_edit_nonce = wp_create_nonce( 'easymap_location_single_item' );
    }

    /**
     * Add notification.
     *
     * @since 1.0.0
     */
    protected function add_notice( string $notice_type, string $notice_message ) {
        $this->easymap_notify[] = array( 'type' => $notice_type, 'message' => $notice_message );
    }

    /**
     * Display admin notifications.
     *
     * @since 1.0.0
     */
    public function show_notifications( bool $return_html = false ) {
        $html = '';
        if ( ! empty( $this->easymap_notify ) ) {
            foreach( $this->easymap_notify as $k => $v ) {
                switch( $v['type'] ) {
                    default:
                        $css_class = ' notice-info';
                        break;
                    case 'success':
                        $css_class = ' notice-success';
                        break;
                    case 'warning':
                        $css_class = ' notice-warning';
                        break;
                    case 'error':
                        $css_class = ' notice-error';
                        break;
                }// switch
                $html .= '<div class="notice' . $css_class . ' is-dismissible"><p>' .
                         esc_html( $v['message'] ) .
                         '</p></div>';
            }
        }
        if ( ! $return_html ) {
            echo $html;
            return;
        }
        return( $html );
    }

    /**
     * Attempt to keep search query for table links.
     *
     * This will attempt to "restore" the search query in a WP_Table_List, even
     * when the various column headers and next/prev buttons are used.
     *
     * @since 1.0.0
     */
    public function mangle_url_scheme( string $url, string $scheme, $orig_scheme ) : string {
        if ( ! empty( $url ) && $this->Utility->x_strpos( $url, '?page=' . EASYMAP_LOCATION_LIST_PAGE_NAME ) !== false && isset( $_REQUEST['s'] ) ) {
            $url = add_query_arg( 's', rawurlencode( $_REQUEST['s'] ), $url );
            if ( ! empty( $_POST['_wpnonce'] ) && empty( $_GET['_wpnonce'] ) ) {
                $url = add_query_arg( '_wpnonce', urlencode( $_POST['_wpnonce'] ), $url );
            }
        }
        return( $url );
    }

    /**
     * Text displayed when no items are available
     *
     * @since 1.0.0
     */
    public function no_items() {
        echo esc_html__( 'No locations', 'easymap' );
    }
    /**
     * Define our table columns.
     *
     * @since 1.0.0
     * @return array $columns, array of columns in table
     */
    public function get_columns() {
        $table_columns = array(
            //'cb'           =>   '<input type="checkbox" />',
            'id'   => __( 'ID', 'easymap' ),
            'al'   => __( 'Alias', 'easymap' ),
            'na'   => __( 'Name', 'easymap' ),
            'ci'   => __( 'City', 'easymap' ),
            'ac'   => __( 'Active', 'easymap' ),
            );
        return( $table_columns );
    }
     /**
     * Defined sortable columns.
     *
     * @since 1.0.0
     * @return array $sortable, the columns that can be sorted by.
     */
    protected function get_sortable_columns() {
        $sortable = array(
                'id' => array( 'id', true  ),
                'al' => array( 'al', true  ),
                'na' => array( 'na', true  ),
                'ci' => array( 'ci', true  ),
                'ac' => array( 'ac', true  ),
        );
        return( $sortable );
    }
    /**
     * REQUIRED if displaying checkboxes or using bulk actions!
     *
     * @since 1.0.0
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     */
    /*
    protected function column_cb( $item ){
        return( sprintf( '<input type="checkbox" name="%s[]" id="id_%s" value="%s" />',
                         $this->_args['singular'], $item['id'], $item['id'] ) );
    }
    */

    /**
     * Retain search and pagination.
     *
     * @since 1.0.0
     */
    protected function retain_search_pagination() {
        $addon_str = '';
        if ( ! empty( $_REQUEST['orderby'] ) ) {
            $addon_str .= '&orderby=' . sanitize_key( trim( $_REQUEST['orderby'] ) );
        }
        if ( ! empty( $_REQUEST['order'] ) ) {
            $addon_str .= '&order=' . sanitize_key( trim( $_REQUEST['order'] ) );
        }
        if ( ! empty( $_REQUEST['s'] ) ) {
            $addon_str .= '&s=' . urlencode( sanitize_text_field( trim( $_REQUEST['s'] ) ) );
            if ( ! empty( $_REQUEST['snonce'] ) ) {
                $addon_str .= '&snonce=' . sanitize_key ( trim( $_REQUEST['snonce'] ) );
            } elseif ( ! empty( $_REQUEST['_wpnonce'] ) ) {
                $addon_str .= '&snonce=' . sanitize_key ( trim( $_REQUEST['_wpnonce'] ) );
            }
        }
        if ( ! empty( $_REQUEST['paged'] ) ) {
            $addon_str .= '&paged=' . (int)sanitize_key( $_REQUEST['paged'] );
        }
        return( $addon_str );
    }
    /*
     * Add custom button links to create new locations.
     *
     * @since 1.0.0
     */
    public function extra_tablenav( $which ) {
        if ( $which == 'top' ) {
            $addon_str = $this->retain_search_pagination();
            echo '<a href="?page=' . EASYMAP_LOCATION_LIST_PAGE_NAME .
                 '&action=edit' .
                 '&location=new' .
                 '&_wpnonce=' . esc_attr( $this->single_item_edit_nonce ) .
                 $addon_str .
                 '" class="button">' .
                 esc_html__( 'Add new location', 'easymap' ) . '</a>';
        }
    }
    /**
     * Table columns.
     *
     * @since 1.0.0
     */
    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'id':
                if ( ! empty( $item[$column_name] ) ) {
                    $addon_str = $this->retain_search_pagination();
                    $xs = sprintf( '<a href="?page=%s&action=edit&location=%d&_wpnonce=%s%s">' .
                                   esc_html( $item[$column_name] ) . '</a>',
                                   EASYMAP_LOCATION_LIST_PAGE_NAME,
                                   $item['id'],
                                   $this->single_item_edit_nonce,
                                   $addon_str );
                } else {
                    $xs = '';
                }
                return( $xs );
            case 'al':// Alias
            case 'na':// Name
            case 'ci':// City
                if ( ! empty( $item[$column_name] ) ) {
                    $addon_str = $this->retain_search_pagination();
                    $xs = sprintf( '<a href="?page=%s&action=edit&location=%d&_wpnonce=%s%s">' .
                                   esc_html( $item[$column_name] ) . '</a>',
                                   EASYMAP_LOCATION_LIST_PAGE_NAME,
                                   $item['id'],
                                   $this->single_item_edit_nonce,
                                   $addon_str );
                } else {
                    $xs = '';
                }
                return( $xs );
            case 'ac'://Active
                if ( empty( $item[$column_name] ) ) {
                    $xs = __( 'No' );
                } else {
                    $xs = __( 'Yes' );
                }
                return( $xs );
        }// switch
        if ( ! empty( $column_name ) ) {
            return( $column_name . ' * ' . print_r( $item, true ) );// to catch errors
        }
        return( '' );
    }
    /**
     * Table column provider_id with actions.
     *
     * @since 1.0.0
     */
    /*
    public function column_id( $item ) {
        $actions = array(
                    'edit'  => sprintf( '<a href="?page=%s&action=edit&category=%d&_wpnonce=%s">' .
                                        esc_html__( 'Edit', 'easymap' ) . '</a>',
                                        EASYMAP_LOCATION_LIST_PAGE_NAME,
                                        $item['id'],
                                        $this->single_item_edit_nonce ),
                    );
        return( sprintf( '%1$s %2$s', $item['id'], $this->row_actions( $actions ) ) );
    }
    */
   /**
     * Bulk actions
     *
     * @since 1.0.0
     */
    public function get_bulk_actions() {
        $actions = array();
        // $actions['delete'] = __( 'Delete', 'easymap' );
        return( $actions );
    }
    /**
     * Setup data
     *
     * @since 1.0.0
     */
    public function prepare_items() {
        $this->_column_headers = array(
            $this->get_columns(),
            array(),
            $this->get_sortable_columns(),
        );
        // $this->_column_headers = $this->get_column_info();
        // Handle list actions
        // $this->handle_location_actions();
        // Are we searching?
        $location_search = '';
        if ( ! empty( $_REQUEST['s'] ) ) {
            if ( ! empty( $_REQUEST['snonce'] ) ) {
                $nonce = sanitize_key( $_REQUEST['snonce'] );
            } elseif ( ! empty( $_REQUEST['_wpnonce'] ) ) {
                $nonce = sanitize_key( $_REQUEST['_wpnonce'] );
            } else {
                $nonce = '';
            }
            if ( ! empty( $nonce ) ) {
                if ( ! wp_verify_nonce( $nonce, 'bulk-easymap-locations' ) ) {
                    error_log( basename(__FILE__) . ' (' . __FUNCTION__ . '): Invalid nonce for search');
                } else {
                    $location_search = sanitize_text_field( wp_unslash( trim( $_REQUEST['s'] ) ) );
                }
            } else {
                error_log( basename(__FILE__) . ' (' . __FUNCTION__ . '): Missing nonce for search');
            }
        }
        // Fetch data
        $locations = $this->get_locations( $location_search );
        // Handle pagination
        $locations_per_page = $this->get_items_per_page( 'locations_per_page' );
        $list_page = $this->get_pagenum();
        // Hand it over to WP
        // We need to manually slice the data based on the current pagination
        $this->items = array_slice( $locations, ( ( $list_page - 1 ) * $locations_per_page ), $locations_per_page );
        // set the pagination arguments
        $total_locations = count( $locations );
        $this->set_pagination_args( array (
            'total_items' => $total_locations,
            'per_page'    => $locations_per_page,
            'total_pages' => ceil( $total_locations / $locations_per_page )
        ) );
    }

    /**
     * Handle table list actions.
     *
     * This is just a stub. The work for this is actually done in easymap.php
     * since we don't have "bulk actions" per se in this case.
     *
     * @since 1.0.0
     */
    /*
    public function handle_location_actions() {
        error_log( basename(__FILE__) . ' (' . __FUNCTION__ . ')' );
        if ( ! is_admin( ) || ! is_user_logged_in() || ! current_user_can( 'administrator' ) )  {
            return;
        }
        return;
    }
    */

    /**
     * Fetch locations from "DB".
     *
     * Since we don't store locations in the database, we fetch them from memory.
     *
     * @since 1.0.0
     */
    protected function get_locations( string $search_string ) {
        if ( ! empty( $search_string ) ) {
            $db_results = array();
            $is_numeric = is_numeric( $search_string );
            foreach( $this->easymap_location_list as $k => $v ) {
                if ( empty( $v['na'] ) ) {
                    continue;
                }
                if ( $is_numeric ) {
                    // This will match '11' with '11', '110', '115', etc.
                    $match = $this->Utility->x_strpos( (string)$v['id'], $search_string ) === 0;
                } else {
                    // This will match anywhere in the string, insensitively
                    $match = ( $this->Utility->x_stripos( $v['na'], $search_string ) !== false
                               ||
                               $this->Utility->x_stripos( $v['al'], $search_string ) !== false
                               ||
                               $this->Utility->x_stripos( $v['ci'], $search_string ) !== false );
                }
                if ( $match ) {
                    $db_results[] = $v;
                }
            }
        } else {
            $db_results = array();
            foreach( $this->easymap_location_list as $k => $v ) {
                if ( empty( $v['na'] ) ) {
                    continue;
                }
            $db_results[] = $v;
            }
        }
        //error_log(print_r($db_results, true));
        // Possibly execute sorting
        if ( count( $db_results ) > 0 ) {
            uasort( $db_results, array( $this, 'handle_sorting' ) );
        }
        return( $db_results );
    }

    /**
     * Handle sorting
     */
    public function handle_sorting( $a, $b ) {
        $order_by = ( isset( $_REQUEST['orderby'] ) ) ? sanitize_key( $_REQUEST['orderby'] ) : 'id';
        $order_how = ( ! empty( $_REQUEST['order'] ) ) ? sanitize_key( $_REQUEST['order'] ) : 'asc';

        switch( $order_by ) {
            case 'na':// Name
            case 'al':// Alias
            case 'ci':// City
                // Do actual comparison
                if ( is_object( $this->collator ) ) {
                    $result = collator_compare ( $this->collator, $a['na'], $b['na'] );
                    if ( $result === false ) {
                        error_log( basename( __FILE__ ) . ' (' . __FUNCTION__ .'): ' . collator_get_error_message( $this->collator ) );
                        $this->collator = false;
                        $result = strcoll( $a[$order_by], $b[$order_by] );
                    }
                } else {
                    $result = strcoll( $a[$order_by], $b[$order_by] );
                }
                break;
            case 'id':
                $result = (int)$a[$order_by] - (int)$b[$order_by];
                break;
            case 'ac':// Active, @since 1.1.0
                $result = (int)$a[$order_by] - (int)$b[$order_by];
                if ( $result === 0 ) {
                    // Make search result less jumpy on equal status
                    $result = (int)$a['id'] - (int)$b['id'];
                }
                break;
            default:
                break;
        }
        return( ( $order_how === 'asc' ) ? $result : -$result );
    }

}// EasyMap_Location_List
