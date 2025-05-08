<?php
/*
Plugin Name: Ultimate Map Plugin
Description: A comprehensive WordPress plugin for displaying interactive maps with layers, geometries, and user-submitted points, supporting Leaflet Panel Layers.
Version: 1.0.5
Author: Shahin Ilderemi
Author URI: https://ildrm.com
License: GPL2
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('UMAP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('UMAP_PLUGIN_URL', plugin_dir_url(__FILE__));

// Enqueue scripts and styles
function umap_plugin_enqueue_scripts() {
    global $post;
    if (is_a($post, 'WP_Post') && (has_shortcode($post->post_content, 'umap') || has_shortcode($post->post_content, 'umap_submission_form'))) {
        $library = get_option('umap_library', 'leaflet');
        
        // Enqueue based on selected library
        if ($library === 'leaflet') {
            wp_enqueue_style('leaflet', 'https://unpkg.com/leaflet@1.9.3/dist/leaflet.css', [], '1.9.3');
            wp_enqueue_script('leaflet', 'https://unpkg.com/leaflet@1.9.3/dist/leaflet.js', [], '1.9.3', true);
            wp_enqueue_style('leaflet-markercluster', 'https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css', [], '1.5.3');
            wp_enqueue_style('leaflet-markercluster-default', 'https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css', [], '1.5.3');
            wp_enqueue_script('leaflet-markercluster', 'https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js', ['leaflet'], '1.5.3', true);
            wp_enqueue_script('leaflet-draw', 'https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js', ['leaflet'], '1.0.4', true);
            wp_enqueue_style('leaflet-draw', 'https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css', [], '1.0.4');
            // Add Leaflet Panel Layers
            wp_enqueue_style('leaflet-panel-layers', 'https://unpkg.com/leaflet-panel-layers@1.2.8/dist/leaflet-panel-layers.min.css', [], '1.2.8');
            wp_enqueue_script('leaflet-panel-layers', 'https://unpkg.com/leaflet-panel-layers@1.2.8/dist/leaflet-panel-layers.min.js', ['leaflet'], '1.2.8', true);
            // Add Leaflet.Heat if enabled
            if (get_option('umap_enable_heatmap', 0)) {
                wp_enqueue_script('leaflet-heat', 'https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js', ['leaflet'], '0.2.0', true);
            }
        } elseif ($library === 'google_maps') {
            $api_key = get_option('umap_google_maps_api_key', '');
            wp_enqueue_script('google-maps', "https://maps.googleapis.com/maps/api/js?key=$api_key&libraries=places", [], null, true);
        } elseif ($library === 'mapbox') {
            wp_enqueue_style('mapbox-gl', 'https://api.mapbox.com/mapbox-gl-js/v2.9.2/mapbox-gl.css', [], '2.9.2');
            wp_enqueue_script('mapbox-gl', 'https://api.mapbox.com/mapbox-gl-js/v2.9.2/mapbox-gl.js', [], '2.9.2', true);
        }

        // Inline CSS
        $css = '
            .umap-container { position: relative; }
            .umap-search { width: 100%; padding: 8px; margin-bottom: 10px; }
            .umap-marker div { width: 30px; height: 30px; border-radius: 50%; }
            .umap-images img { margin: 5px; }
            .umap-submission-form p { margin: 10px 0; }
            .umap-submission-form label { display: block; margin-bottom: 5px; }
            .umap-submission-form input, .umap-submission-form textarea, .umap-submission-form select { width: 100%; padding: 8px; }
            .tooltip { cursor: help; color: #0073aa; margin-left: 5px; }
            .working-hours-toggle { cursor: pointer; }
            .umap-images .umap-image { display: inline-block; margin: 10px; }
            .umap-debug { background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 10px; }
            .leaflet-panel-layers { width: 200px; }
            .icon-cloud::before { content: "☁"; }
            .icon-thermometer::before { content: "🌡️"; }
        ';
        wp_add_inline_style($library === 'leaflet' ? 'leaflet' : 'wp-admin', $css);

        // Localize script
        wp_localize_script('leaflet', 'umap', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('umap_nonce'),
            'library' => $library,
            'mapboxToken' => get_option('umap_mapbox_api_key', ''),
            'enableHeatmap' => get_option('umap_enable_heatmap', 0),
        ]);
    }
}
add_action('wp_enqueue_scripts', 'umap_plugin_enqueue_scripts');

// Enqueue admin scripts
function umap_plugin_enqueue_admin_scripts($hook) {
    if (in_array($hook, ['post.php', 'post-new.php']) && get_post_type() === 'umap_geometry') {
        wp_enqueue_style('leaflet', 'https://unpkg.com/leaflet@1.9.3/dist/leaflet.css', [], '1.9.3');
        wp_enqueue_script('leaflet', 'https://unpkg.com/leaflet@1.9.3/dist/leaflet.js', [], '1.9.3', true);
        wp_enqueue_script('leaflet-draw', 'https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js', ['leaflet'], '1.0.4', true);
        wp_enqueue_style('leaflet-draw', 'https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css', [], '1.0.4');
        
        // Inline admin CSS
        $admin_css = '
            .tooltip { cursor: help; color: #0073aa; margin-left: 5px; }
            .working-hours-toggle { cursor: pointer; }
            .umap-images .umap-image { display: inline-block; margin: 10px; }
            #geometry-map { margin-top: 10px; }
        ';
        wp_add_inline_style('leaflet', $admin_css);
    }
}
add_action('admin_enqueue_scripts', 'umap_plugin_enqueue_admin_scripts');

// Register Custom Post Types
function umap_plugin_register_cpts() {
    register_post_type('umap_layer', [
        'labels' => [
            'name' => __('Layers', 'umap'),
            'singular_name' => __('Layer', 'umap'),
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => 'umap',
        'supports' => ['title'],
        'capability_type' => 'post',
        'capabilities' => [
            'create_posts' => 'edit_others_posts',
        ],
        'map_meta_cap' => true,
    ]);

    register_post_type('umap_geometry', [
        'labels' => [
            'name' => __('Geometries', 'umap'),
            'singular_name' => __('Geometry', 'umap'),
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => 'umap',
        'supports' => ['title', 'thumbnail'],
        'capability_type' => 'post',
        'capabilities' => [
            'create_posts' => 'edit_others_posts',
        ],
        'map_meta_cap' => true,
    ]);
}
add_action('init', 'umap_plugin_register_cpts');

// Add Admin Menu
function umap_plugin_admin_menu() {
    add_menu_page(
        __('Ultimate Map Plugin', 'umap'),
        __('Ultimate Map Plugin', 'umap'),
        'manage_options',
        'umap',
        'umap_plugin_settings_page',
        'dashicons-location',
        20
    );
    add_submenu_page(
        'umap',
        __('Settings', 'umap'),
        __('Settings', 'umap'),
        'manage_options',
        'umap',
        'umap_plugin_settings_page'
    );
}
add_action('admin_menu', 'umap_plugin_admin_menu');

// Register Settings
function umap_plugin_register_settings() {
    register_setting('umap_settings', 'umap_library', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('umap_settings', 'umap_google_maps_api_key', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('umap_settings', 'umap_mapbox_api_key', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('umap_settings', 'umap_openweather_api_key', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('umap_settings', 'umap_default_lat', ['sanitize_callback' => 'floatval']);
    register_setting('umap_settings', 'umap_default_lon', ['sanitize_callback' => 'floatval']);
    register_setting('umap_settings', 'umap_default_zoom', ['sanitize_callback' => 'intval']);
    register_setting('umap_settings', 'umap_enable_clustering', ['sanitize_callback' => 'intval']);
    register_setting('umap_settings', 'umap_enable_heatmap', ['sanitize_callback' => 'intval']);
    register_setting('umap_settings', 'umap_marker_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('umap_settings', 'umap_map_style', ['sanitize_callback' => 'wp_kses_post']);
    register_setting('umap_settings', 'umap_allow_user_submission', ['sanitize_callback' => 'intval']);

    add_settings_section('umap_main', __('Map Settings', 'umap'), null, 'umap');
    add_settings_field('map_library', __('Map Library', 'umap'), 'umap_library_field', 'umap', 'umap_main');
    add_settings_field('google_maps_api_key', __('Google Maps API Key', 'umap'), 'umap_google_maps_api_key_field', 'umap', 'umap_main');
    add_settings_field('mapbox_api_key', __('MapBox API Key', 'umap'), 'umap_mapbox_api_key_field', 'umap', 'umap_main');
    add_settings_field('openweather_api_key', __('OpenWeatherMap API Key', 'umap'), 'umap_openweather_api_key_field', 'umap', 'umap_main');
    add_settings_field('default_lat', __('Default Latitude', 'umap'), 'umap_default_lat_field', 'umap', 'umap_main');
    add_settings_field('default_lon', __('Default Longitude', 'umap'), 'umap_default_lon_field', 'umap', 'umap_main');
    add_settings_field('default_zoom', __('Default Zoom', 'umap'), 'umap_default_zoom_field', 'umap', 'umap_main');
    add_settings_field('enable_clustering', __('Enable Marker Clustering', 'umap'), 'umap_enable_clustering_field', 'umap', 'umap_main');
    add_settings_field('enable_heatmap', __('Enable Heatmap', 'umap'), 'umap_enable_heatmap_field', 'umap', 'umap_main');
    add_settings_field('marker_color', __('Marker Color', 'umap'), 'umap_marker_color_field', 'umap', 'umap_main');
    add_settings_field('map_style', __('Map Style (JSON)', 'umap'), 'umap_map_style_field', 'umap', 'umap_main');
    add_settings_field('allow_user_submission', __('Allow User Submissions', 'umap'), 'umap_allow_user_submission_field', 'umap', 'umap_main');
}
add_action('admin_init', 'umap_plugin_register_settings');

// Settings Fields
function umap_library_field() {
    $library = get_option('umap_library', 'leaflet');
    ?>
    <select name="umap_library" id="umap_library">
        <option value="leaflet" <?php selected($library, 'leaflet'); ?>>Leaflet</option>
        <option value="google_maps" <?php selected($library, 'google_maps'); ?>>Google Maps</option>
        <option value="mapbox" <?php selected($library, 'mapbox'); ?>>MapBox</option>
    </select>
    <p class="description"><?php _e('Select the map library to use.', 'umap'); ?></p>
    <?php
}

function umap_google_maps_api_key_field() {
    $key = get_option('umap_google_maps_api_key', '');
    echo '<input type="text" name="umap_google_maps_api_key" value="' . esc_attr($key) . '" />';
    echo '<p class="description">' . __('Enter your Google Maps API Key.', 'umap') . '</p>';
}

function umap_mapbox_api_key_field() {
    $key = get_option('umap_mapbox_api_key', '');
    echo '<input type="text" name="umap_mapbox_api_key" value="' . esc_attr($key) . '" />';
    echo '<p class="description">' . __('Enter your MapBox Access Token.', 'umap') . '</p>';
}

function umap_openweather_api_key_field() {
    $key = get_option('umap_openweather_api_key', '');
    echo '<input type="text" name="umap_openweather_api_key" value="' . esc_attr($key) . '" />';
    echo '<p class="description">' . __('Enter your OpenWeatherMap API Key for weather layers.', 'umap') . '</p>';
}

function umap_default_lat_field() {
    $lat = get_option('umap_default_lat', 35.6895);
    echo '<input type="number" step="any" name="umap_default_lat" value="' . esc_attr($lat) . '" />';
    echo '<p class="description">' . __('Default latitude for map center.', 'umap') . '</p>';
}

function umap_default_lon_field() {
    $lon = get_option('umap_default_lon', 51.3890);
    echo '<input type="number" step="any" name="umap_default_lon" value="' . esc_attr($lon) . '" />';
    echo '<p class="description">' . __('Default longitude for map center.', 'umap') . '</p>';
}

function umap_default_zoom_field() {
    $zoom = get_option('umap_default_zoom', 10);
    echo '<input type="number" name="umap_default_zoom" value="' . esc_attr($zoom) . '" />';
    echo '<p class="description">' . __('Default zoom level for the map.', 'umap') . '</p>';
}

function umap_enable_clustering_field() {
    $clustering = get_option('umap_enable_clustering', 1);
    echo '<input type="checkbox" name="umap_enable_clustering" value="1" ' . checked(1, $clustering, false) . ' />';
    echo '<p class="description">' . __('Enable marker clustering for better performance.', 'umap') . '</p>';
}

function umap_enable_heatmap_field() {
    $heatmap = get_option('umap_enable_heatmap', 0);
    echo '<input type="checkbox" name="umap_enable_heatmap" value="1" ' . checked(1, $heatmap, false) . ' />';
    echo '<p class="description">' . __('Enable heatmap visualization for points (Leaflet only).', 'umap') . '</p>';
}

function umap_marker_color_field() {
    $color = get_option('umap_marker_color', '#3388ff');
    echo '<input type="color" name="umap_marker_color" value="' . esc_attr($color) . '" />';
    echo '<p class="description">' . __('Select the default marker color.', 'umap') . '</p>';
}

function umap_map_style_field() {
    $style = get_option('umap_map_style', '');
    echo '<textarea name="umap_map_style" rows="5" style="width:100%;">' . esc_textarea($style) . '</textarea>';
    echo '<p class="description">' . __('Enter JSON for custom map styles (Google Maps or MapBox).', 'umap') . '</p>';
}

function umap_allow_user_submission_field() {
    $allow = get_option('umap_allow_user_submission', 0);
    echo '<input type="checkbox" name="umap_allow_user_submission" value="1" ' . checked(1, $allow, false) . ' />';
    echo '<p class="description">' . __('Allow users to submit geometries from the front-end.', 'umap') . '</p>';
}

// Settings Page
function umap_plugin_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('Ultimate Map Plugin Settings', 'umap'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('umap_settings');
            do_settings_sections('umap');
            submit_button();
            ?>
        </form>
        <h2><?php _e('Layers', 'umap'); ?></h2>
        <input type="text" id="layer-search" placeholder="<?php _e('Search Layers...', 'umap'); ?>" />
        <a href="<?php echo admin_url('post-new.php?post_type=umap_layer'); ?>" class="button"><?php _e('Add New Layer', 'umap'); ?></a>
        <table class="wp-list-table widefat fixed striped" id="layers-table">
            <thead>
                <tr>
                    <th><?php _e('Layer Name', 'umap'); ?></th>
                    <th><?php _e('Actions', 'umap'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $layers = get_posts(['post_type' => 'umap_layer', 'numberposts' => -1]);
                foreach ($layers as $layer) {
                    echo '<tr>';
                    echo '<td>' . esc_html($layer->post_title) . '</td>';
                    echo '<td>';
                    echo '<a href="' . admin_url('edit.php?post_type=umap_geometry&layer_id=' . $layer->ID) . '">' . __('View Geometries', 'umap') . '</a> | ';
                    echo '<a href="' . admin_url('post.php?post=' . $layer->ID . '&action=edit') . '">' . __('Edit', 'umap') . '</a> | ';
                    echo '<a href="#" class="preview-map" data-layer-id="' . esc_attr($layer->ID) . '">' . __('Preview', 'umap') . '</a>';
                    echo '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
        <div id="map-preview" style="display:none; height:400px; margin-top:20px;"></div>
        <h2><?php _e('Import/Export', 'umap'); ?></h2>
        <form method="post" enctype="multipart/form-data">
            <p>
                <label><?php _e('Import GeoJSON', 'umap'); ?></label>
                <input type="file" name="geojson_import" accept=".json,.geojson" />
            </p>
            <?php wp_nonce_field('umap_import', 'umap_import_nonce'); ?>
            <input type="submit" class="button" value="<?php _e('Import', 'umap'); ?>" />
        </form>
        <a href="<?php echo admin_url('admin-ajax.php?action=umap_export_geojson&nonce=' . wp_create_nonce('umap_export')); ?>" class="button"><?php _e('Export GeoJSON', 'umap'); ?></a>
    </div>
    <script>
        jQuery(document).ready(function($) {
            $('#layer-search').on('input', function() {
                var search = $(this).val().toLowerCase();
                $('#layers-table tbody tr').each(function() {
                    var name = $(this).find('td:first').text().toLowerCase();
                    $(this).toggle(name.includes(search));
                });
            });
            $('.preview-map').click(function(e) {
                e.preventDefault();
                var layerId = $(this).data('layer-id');
                $('#map-preview').show();
                $.post(umap.ajaxurl, {
                    action: 'umap_preview_map',
                    nonce: umap.nonce,
                    layer_id: layerId
                }, function(response) {
                    $('#map-preview').html(response);
                });
            });
        });
    </script>
    <?php
}

// Handle GeoJSON Import
function umap_plugin_handle_import() {
    if (!isset($_POST['umap_import_nonce']) || !wp_verify_nonce($_POST['umap_import_nonce'], 'umap_import')) {
        return;
    }
    if (!current_user_can('manage_options')) {
        return;
    }
    if (isset($_FILES['geojson_import']) && $_FILES['geojson_import']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['geojson_import']['tmp_name'];
        $content = file_get_contents($file);
        $geojson = json_decode($content, true);
        if ($geojson && isset($geojson['features'])) {
            foreach ($geojson['features'] as $feature) {
                $post_id = wp_insert_post([
                    'post_type' => 'umap_geometry',
                    'post_title' => isset($feature['properties']['title']) ? sanitize_text_field($feature['properties']['title']) : __('Unnamed', 'umap'),
                    'post_status' => 'publish',
                ]);
                if ($post_id) {
                    update_post_meta($post_id, '_umap_geometry_type', sanitize_text_field($feature['geometry']['type']));
                    update_post_meta($post_id, '_umap_geometry_coordinates', wp_json_encode($feature['geometry']['coordinates']));
                    update_post_meta($post_id, '_umap_geometry_description', sanitize_textarea_field($feature['properties']['description'] ?? ''));
                    update_post_meta($post_id, '_umap_geometry_working_hours', wp_json_encode($feature['properties']['working_hours'] ?? []));
                    update_post_meta($post_id, '_umap_geometry_layer_id', intval($feature['properties']['layer_id'] ?? 0));
                    update_post_meta($post_id, '_umap_geometry_images', wp_json_encode($feature['properties']['images'] ?? []));
                }
            }
        }
    }
}
add_action('admin_init', 'umap_plugin_handle_import');

// Handle GeoJSON Export
function umap_plugin_export_geojson() {
    check_ajax_referer('umap_export', 'nonce');
    $geometries = get_posts(['post_type' => 'umap_geometry', 'numberposts' => -1]);
    $geojson = [
        'type' => 'FeatureCollection',
        'features' => [],
    ];
    foreach ($geometries as $geometry) {
        $geojson['features'][] = [
            'type' => 'Feature',
            'geometry' => [
                'type' => get_post_meta($geometry->ID, '_umap_geometry_type', true),
                'coordinates' => json_decode(get_post_meta($geometry->ID, '_umap_geometry_coordinates', true), true),
            ],
            'properties' => [
                'title' => $geometry->post_title,
                'description' => get_post_meta($geometry->ID, '_umap_geometry_description', true),
                'working_hours' => json_decode(get_post_meta($geometry->ID, '_umap_geometry_working_hours', true), true),
                'layer_id' => get_post_meta($geometry->ID, '_umap_geometry_layer_id', true),
                'images' => json_decode(get_post_meta($geometry->ID, '_umap_geometry_images', true), true),
            ],
        ];
    }
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="umap_geometries.geojson"');
    echo wp_json_encode($geojson);
    exit;
}
add_action('wp_ajax_umap_export_geojson', 'umap_plugin_export_geojson');

// Add Meta Boxes for Geometries
function umap_plugin_geometry_meta_boxes() {
    add_meta_box('umap_geometry_details', __('Geometry Details', 'umap'), 'umap_plugin_geometry_details_meta_box', 'umap_geometry', 'normal', 'high');
}
add_action('add_meta_boxes', 'umap_plugin_geometry_meta_boxes');

function umap_plugin_geometry_details_meta_box($post) {
    wp_nonce_field('umap_geometry_details', 'umap_geometry_details_nonce');
    $type = get_post_meta($post->ID, '_umap_geometry_type', true);
    $coordinates = get_post_meta($post->ID, '_umap_geometry_coordinates', true);
    $description = get_post_meta($post->ID, '_umap_geometry_description', true);
    $working_hours = get_post_meta($post->ID, '_umap_geometry_working_hours', true);
    $working_hours = $working_hours ? json_decode($working_hours, true) : [];
    $layer_id = get_post_meta($post->ID, '_umap_geometry_layer_id', true);
    $images = get_post_meta($post->ID, '_umap_geometry_images', true);
    $images = $images ? json_decode($images, true) : [];
    ?>
    <p>
        <label><?php _e('Geometry Type', 'umap'); ?> <span class="tooltip" title="<?php _e('Select the type of geometry.', 'umap'); ?>">?</span></label>
        <select name="umap_geometry_type">
            <option value="Point" <?php selected($type, 'Point'); ?>>Point</option>
            <option value="LineString" <?php selected($type, 'LineString'); ?>>Line</option>
            <option value="Polygon" <?php selected($type, 'Polygon'); ?>>Polygon</option>
        </select>
    </p>
    <p>
        <label><?php _e('Coordinates (GeoJSON)', 'umap'); ?> <span class="tooltip" title="<?php _e('Enter coordinates or use the map below.', 'umap'); ?>">?</span></label>
        <textarea name="umap_geometry_coordinates" rows="4" style="width:100%;" placeholder='[51.3890, 35.6895] for Point, [[51.3890, 35.6895], [51.3900, 35.6900]] for Line/Polygon'><?php echo esc_textarea($coordinates); ?></textarea>
        <div id="geometry-map" style="height:300px;"></div>
    </p>
    <p>
        <label><?php _e('Description', 'umap'); ?> <span class="tooltip" title="<?php _e('Enter a description for the geometry.', 'umap'); ?>">?</span></label>
        <textarea name="umap_geometry_description" rows="4" style="width:100%;"><?php echo esc_textarea($description); ?></textarea>
    </p>
    <p>
        <label><?php _e('Layer', 'umap'); ?> <span class="tooltip" title="<?php _e('Select the layer for this geometry.', 'umap'); ?>">?</span></label>
        <select name="umap_geometry_layer_id">
            <option value=""><?php _e('Select Layer', 'umap'); ?></option>
            <?php
            $layers = get_posts(['post_type' => 'umap_layer', 'numberposts' => -1]);
            foreach ($layers as $layer) {
                echo '<option value="' . esc_attr($layer->ID) . '" ' . selected($layer_id, $layer->ID, false) . '>' . esc_html($layer->post_title) . '</option>';
            }
            ?>
        </select>
    </p>
    <p>
        <label><?php _e('Images', 'umap'); ?> <span class="tooltip" title="<?php _e('Upload images for the geometry.', 'umap'); ?>">?</span></label>
        <div class="umap-images">
            <?php foreach ($images as $image_id) : ?>
                <div class="umap-image">
                    <?php echo wp_get_attachment_image($image_id, 'thumbnail'); ?>
                    <input type="hidden" name="umap_geometry_images[]" value="<?php echo esc_attr($image_id); ?>" />
                    <button type="button" class="button remove-image"><?php _e('Remove', 'umap'); ?></button>
                </div>
            <?php endforeach; ?>
            <button type="button" class="button upload-image"><?php _e('Add Image', 'umap'); ?></button>
        </div>
    </p>
    <h3 class="working-hours-toggle"><?php _e('Working Hours', 'umap'); ?> <span>(<?php _e('Click to expand', 'umap'); ?>)</span></h3>
    <div class="working-hours" style="display:none;">
        <?php
        $days = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        foreach ($days as $day) {
            $open = isset($working_hours[$day]['open']) ? $working_hours[$day]['open'] : '';
            $close = isset($working_hours[$day]['close']) ? $working_hours[$day]['close'] : '';
            $closed = isset($working_hours[$day]['closed']) ? $working_hours[$day]['closed'] : false;
            ?>
            <p>
                <label><?php echo esc_html(ucfirst($day)); ?>:</label>
                <input type="time" name="umap_geometry_working_hours[<?php echo $day; ?>][open]" value="<?php echo esc_attr($open); ?>" />
                <input type="time" name="umap_geometry_working_hours[<?php echo $day; ?>][close]" value="<?php echo esc_attr($close); ?>" />
                <label><input type="checkbox" name="umap_geometry_working_hours[<?php echo $day; ?>][closed]" value="1" <?php checked($closed, 1); ?> /> <?php _e('Closed', 'umap'); ?></label>
            </p>
            <?php
        }
        ?>
        <button type="button" class="button copy-working-hours"><?php _e('Copy to All Days', 'umap'); ?></button>
    </div>
    <script>
        jQuery(document).ready(function($) {
            try {
                var map = L.map('geometry-map').setView([35.6895, 51.3890], 10);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
                var drawnItems = new L.FeatureGroup();
                map.addLayer(drawnItems);
                var drawControl = new L.Control.Draw({
                    edit: { featureGroup: drawnItems },
                    draw: { circle: false, rectangle: false, marker: true, polyline: true, polygon: true }
                });
                map.addControl(drawControl);
                map.on('draw:created', function(e) {
                    drawnItems.clearLayers();
                    drawnItems.addLayer(e.layer);
                    var geojson = e.layer.toGeoJSON();
                    $('textarea[name="umap_geometry_coordinates"]').val(JSON.stringify(geojson.geometry.coordinates));
                    $('select[name="umap_geometry_type"]').val(geojson.geometry.type);
                });
                <?php if ($coordinates && $type) : ?>
                try {
                    var layer = L.geoJSON({
                        type: 'Feature',
                        geometry: { type: '<?php echo esc_js($type); ?>', coordinates: <?php echo $coordinates; ?> }
                    });
                    drawnItems.addLayer(layer);
                    map.fitBounds(layer.getBounds());
                } catch (e) {
                    console.error('Error loading geometry:', e);
                }
                <?php endif; ?>
                $('.working-hours-toggle').click(function() {
                    $('.working-hours').slideToggle();
                });
                $('.copy-working-hours').click(function() {
                    var firstDay = $('.working-hours p:first input[type="time"]');
                    var open = firstDay.eq(0).val();
                    var close = firstDay.eq(1).val();
                    var closed = $('.working-hours p:first input[type="checkbox"]').is(':checked');
                    $('.working-hours p').each(function() {
                        $(this).find('input[type="time"]').eq(0).val(open);
                        $(this).find('input[type="time"]').eq(1).val(close);
                        $(this).find('input[type="checkbox"]').prop('checked', closed);
                    });
                });
                $('.upload-image').click(function() {
                    var frame = wp.media({
                        title: '<?php _e('Select Images', 'umap'); ?>',
                        multiple: true,
                        library: { type: 'image' }
                    });
                    frame.on('select', function() {
                        var attachments = frame.state().get('selection').toJSON();
                        attachments.forEach(function(attachment) {
                            $('.umap-images').append(
                                '<div class="umap-image">' +
                                '<img src="' + attachment.url + '" style="max-width:100px;" />' +
                                '<input type="hidden" name="umap_geometry_images[]" value="' + attachment.id + '" />' +
                                '<button type="button" class="button remove-image"><?php _e('Remove', 'umap'); ?></button>' +
                                '</div>'
                            );
                        });
                    });
                    frame.open();
                });
                $('.umap-images').on('click', '.remove-image', function() {
                    $(this).parent('.umap-image').remove();
                });
            } catch (e) {
                console.error('Geometry meta box error:', e);
            }
        });
    </script>
    <?php
}

// Save Geometry Meta
function umap_plugin_save_geometry_meta($post_id) {
    if (!isset($_POST['umap_geometry_details_nonce']) || !wp_verify_nonce($_POST['umap_geometry_details_nonce'], 'umap_geometry_details')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $fields = [
        'umap_geometry_type' => 'sanitize_text_field',
        'umap_geometry_coordinates' => 'wp_kses_post',
        'umap_geometry_description' => 'sanitize_textarea_field',
        'umap_geometry_layer_id' => 'intval',
    ];

    foreach ($fields as $field => $sanitize) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, '_' . $field, $sanitize($_POST[$field]));
        }
    }

    if (isset($_POST['umap_geometry_working_hours'])) {
        $working_hours = [];
        foreach ($_POST['umap_geometry_working_hours'] as $day => $hours) {
            $working_hours[$day] = [
                'open' => sanitize_text_field($hours['open']),
                'close' => sanitize_text_field($hours['close']),
                'closed' => isset($hours['closed']) ? 1 : 0,
            ];
        }
        update_post_meta($post_id, '_umap_geometry_working_hours', wp_json_encode($working_hours));
    }

    if (isset($_POST['umap_geometry_images'])) {
        $images = array_map('intval', $_POST['umap_geometry_images']);
        update_post_meta($post_id, '_umap_geometry_images', wp_json_encode($images));
    }
}
add_action('save_post_umap_geometry', 'umap_plugin_save_geometry_meta');

// Map Shortcode
function umap_plugin_map_shortcode($atts) {
    $atts = shortcode_atts([
        'layer_id' => 0,
        'layer_ids' => '',
        'point' => '',
        'geometry_type' => 'Point',
        'height' => '400px',
        'distance_filter' => 0,
    ], $atts, 'umap');

    $layer_id = intval($atts['layer_id']);
    $layer_ids = array_filter(array_map('intval', explode(',', $atts['layer_ids'])));
    $height = esc_attr($atts['height']);
    $point = sanitize_text_field($atts['point']);
    $geometry_type = sanitize_text_field($atts['geometry_type']);
    $distance_filter = floatval($atts['distance_filter']);

    $library = get_option('umap_library', 'leaflet');
    $default_lat = floatval(get_option('umap_default_lat', 35.6895));
    $default_lon = floatval(get_option('umap_default_lon', 51.3890));
    $default_zoom = intval(get_option('umap_default_zoom', 10));
    $enable_clustering = intval(get_option('umap_enable_clustering', 1));
    $enable_heatmap = intval(get_option('umap_enable_heatmap', 0));
    $marker_color = get_option('umap_marker_color', '#3388ff');
    $map_style = get_option('umap_map_style', '');
    $mapbox_api_key = get_option('umap_mapbox_api_key', '');
    $openweather_api_key = get_option('umap_openweather_api_key', '');

    // Get layers
    $layer_args = [
        'post_type' => 'umap_layer',
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ];
    if (!empty($layer_ids)) {
        $layer_args['post__in'] = $layer_ids;
    } elseif ($layer_id) {
        $layer_args['p'] = $layer_id;
    }
    $layers = get_posts($layer_args);

    // Get geometries
    $args = [
        'post_type' => 'umap_geometry',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => [
            [
                'key' => '_umap_geometry_type',
                'value' => 'Point',
                'compare' => '=',
            ],
        ],
    ];
    if (!empty($layer_ids)) {
        $args['meta_query'][] = [
            'key' => '_umap_geometry_layer_id',
            'value' => $layer_ids,
            'compare' => 'IN',
        ];
    } elseif ($layer_id) {
        $args['meta_query'][] = [
            'key' => '_umap_geometry_layer_id',
            'value' => $layer_id,
            'compare' => '=',
        ];
    }
    $query = new WP_Query($args);
    $geometries = [];
    while ($query->have_posts()) {
        $query->the_post();
        $coordinates = json_decode(get_post_meta(get_the_ID(), '_umap_geometry_coordinates', true), true);
        if (!$coordinates || !is_array($coordinates) || !isset($coordinates[0], $coordinates[1])) {
            continue;
        }
        $images = json_decode(get_post_meta(get_the_ID(), '_umap_geometry_images', true), true) ?: [];
        $image_urls = array_map('wp_get_attachment_url', $images);
        $geometries[] = [
            'id' => get_the_ID(),
            'title' => get_the_title(),
            'type' => get_post_meta(get_the_ID(), '_umap_geometry_type', true),
            'coordinates' => $coordinates,
            'description' => get_post_meta(get_the_ID(), '_umap_geometry_description', true),
            'working_hours' => json_decode(get_post_meta(get_the_ID(), '_umap_geometry_working_hours', true), true),
            'images' => $image_urls,
            'layer_id' => intval(get_post_meta(get_the_ID(), '_umap_geometry_layer_id', true)),
        ];
    }
    wp_reset_postdata();

    // Group geometries by layer
    $layer_geometries = [];
    foreach ($layers as $layer) {
        $layer_geometries[$layer->ID] = array_filter($geometries, function($geometry) use ($layer) {
            return $geometry['layer_id'] == $layer->ID;
        });
    }

    // Apply distance filter
    if ($distance_filter && $point) {
        $coords = explode(',', $point);
        if (count($coords) === 2 && is_numeric($coords[0]) && is_numeric($coords[1])) {
            $geometries = array_filter($geometries, function($geometry) use ($coords, $distance_filter) {
                if ($geometry['type'] === 'Point' && isset($geometry['coordinates'][0], $geometry['coordinates'][1])) {
                    $lat1 = floatval($coords[0]);
                    $lon1 = floatval($coords[1]);
                    $lat2 = floatval($geometry['coordinates'][1]);
                    $lon2 = floatval($geometry['coordinates'][0]);
                    $distance = 6371 * acos(cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($lon2) - deg2rad($lon1)) + sin(deg2rad($lat1)) * sin(deg2rad($lat2)));
                    return $distance <= $distance_filter;
                }
                return true;
            });
            foreach ($layer_geometries as $lid => $geoms) {
                $layer_geometries[$lid] = array_filter($geoms, function($geometry) use ($coords, $distance_filter) {
                    if ($geometry['type'] === 'Point' && isset($geometry['coordinates'][0], $geometry['coordinates'][1])) {
                        $lat1 = floatval($coords[0]);
                        $lon1 = floatval($coords[1]);
                        $lat2 = floatval($geometry['coordinates'][1]);
                        $lon2 = floatval($geometry['coordinates'][0]);
                        $distance = 6371 * acos(cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($lon2) - deg2rad($lon1)) + sin(deg2rad($lat1)) * sin(deg2rad($lat2)));
                        return $distance <= $distance_filter;
                    }
                    return true;
                });
            }
        }
    }

    // Single point
    $single_geometry = [];
    if ($point && $geometry_type === 'Point') {
        $coords = explode(',', $point);
        if (count($coords) === 2 && is_numeric($coords[0]) && is_numeric($coords[1])) {
            $single_geometry = [
                'type' => 'Point',
                'coordinates' => [floatval($coords[1]), floatval($coords[0])],
                'title' => __('Custom Point', 'umap'),
                'description' => '',
                'images' => [],
                'layer_id' => 0,
            ];
        }
    }

    // Debug messages
    $debug = '';
    if (empty($layers)) {
        $debug = '<div class="umap-debug">' . __('No layers found. Please add layers in the admin panel.', 'umap') . '</div>';
    } elseif (empty($geometries) && empty($single_geometry)) {
        $debug = '<div class="umap-debug">' . __('No points found for the specified layers.', 'umap') . '</div>';
    } elseif (!empty($layer_ids) && count($layer_ids) > count($layers)) {
        $debug = '<div class="umap-debug">' . __('Some specified layers were not found.', 'umap') . '</div>';
    } elseif ($layer_id && empty($layer_geometries[$layer_id])) {
        $debug = '<div class="umap-debug">' . sprintf(__('No points found for layer ID %s.', 'umap'), $layer_id) . '</div>';
    }
    // if (empty($openweather_api_key)) {
    //     $debug .= '<div class="umap-debug">' . __('OpenWeatherMap API Key is missing. Weather layers (Clouds and Temperature) cannot be loaded.', 'umap') . '</div>';
    // }

    $map_id = 'umap_' . uniqid();

    ob_start();
    ?>
    <div class="umap-container">
        <?php echo $debug; ?>
        <input type="text" class="umap-search" placeholder="<?php _e('Search Geometries...', 'umap'); ?>" />
        <div id="<?php echo esc_attr($map_id); ?>" style="height: <?php echo $height; ?>;"></div>
    </div>
    <script>
        jQuery(document).ready(function($) {
            try {
                var mapPlugin = window.umap || {};
                var map, panelLayers, layerGroups = {};
                <?php if ($library === 'leaflet') : ?>
                map = L.map('<?php echo esc_attr($map_id); ?>').setView([<?php echo $default_lat; ?>, <?php echo $default_lon; ?>], <?php echo $default_zoom; ?>);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                }).addTo(map);

                // Define base layers
                var baseLayers = [
                    {
                        name: 'OpenStreetMap',
                        layer: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                        })
                    },
                    {
                        name: 'CartoDB Positron',
                        layer: L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png', {
                            attribution: '© <a href="https://carto.com/attributions">CARTO</a>'
                        })
                    },
                    {
                        name: 'Stamen Terrain',
                        layer: L.tileLayer('https://stamen-tiles-{s}.a.ssl.fastly.net/terrain/{z}/{x}/{y}.jpg', {
                            attribution: 'Map tiles by <a href="http://stamen.com">Stamen Design</a>, under <a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a>. Data by <a href="http://openstreetmap.org">OpenStreetMap</a>, under <a href="http://www.openstreetmap.org/copyright">ODbL</a>.'
                        })
                    },
                    {
                        name: 'Esri WorldStreetMap',
                        layer: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
                            attribution: 'Tiles © Esri — Source: Esri, DeLorme, NAVTEQ, USGS, Intermap, iPC, NRCAN, Esri Japan, METI, Esri China (Hong Kong), Esri (Thailand), TomTom, 2012'
                        })
                    }
                ];

                // Add MapBox Streets if API key is available
                <?php if (!empty($mapbox_api_key)) : ?>
                baseLayers.push({
                    name: 'MapBox Streets',
                    layer: L.tileLayer('https://api.mapbox.com/styles/v1/mapbox/streets-v11/tiles/{z}/{x}/{y}?access_token=<?php echo esc_js($mapbox_api_key); ?>', {
                        attribution: '© <a href="https://www.mapbox.com/about/maps/">MapBox</a> © <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                    })
                });
                <?php endif; ?>

                // Define overlay layers
                var overlayLayers = [];
                var markerIcon = L.divIcon({
                    className: 'umap-marker',
                    html: '<div style="background-color: <?php echo esc_js($marker_color); ?>;"></div>',
                    iconSize: [30, 30]
                });

                <?php foreach ($layers as $layer) : ?>
                layerGroups[<?php echo $layer->ID; ?>] = <?php echo $enable_clustering && !$enable_heatmap ? 'L.markerClusterGroup()' : 'L.layerGroup()'; ?>;
                <?php foreach ($layer_geometries[$layer->ID] as $geometry) : ?>
                <?php if ($geometry['type'] === 'Point' && isset($geometry['coordinates'][0], $geometry['coordinates'][1])) : ?>
                <?php if ($enable_heatmap) : ?>
                // Heatmap points are handled separately
                <?php else : ?>
                var marker = L.marker([<?php echo floatval($geometry['coordinates'][1]); ?>, <?php echo floatval($geometry['coordinates'][0]); ?>], { icon: markerIcon })
                    .bindPopup(
                        '<h3><?php echo esc_js($geometry['title']); ?></h3>' +
                        '<p><?php echo esc_js($geometry['description']); ?></p>' +
                        '<?php if ($geometry['working_hours']) : ?>' +
                        '<ul>' +
                        '<?php foreach ($geometry['working_hours'] as $day => $hours) : ?>' +
                        '<li><?php echo esc_js(ucfirst($day)); ?>: ' +
                        '<?php echo $hours['closed'] ? __('Closed', 'umap') : esc_js($hours['open'] . ' - ' . $hours['close']); ?>' +
                        '</li>' +
                        '<?php endforeach; ?>' +
                        '</ul>' +
                        '<?php endif; ?>' +
                        '<?php if ($geometry['images']) : ?>' +
                        '<div class="umap-images">' +
                        '<?php foreach ($geometry['images'] as $image) : ?>' +
                        '<img src="<?php echo esc_js($image); ?>" style="max-width:100px;" />' +
                        '<?php endforeach; ?>' +
                        '</div>' +
                        '<?php endif; ?>'
                    );
                layerGroups[<?php echo $layer->ID; ?>].addLayer(marker);
                <?php endif; ?>
                <?php endif; ?>
                <?php endforeach; ?>
                overlayLayers.push({
                    name: '<?php echo esc_js($layer->post_title); ?>',
                    icon: '<i class="icon icon-data"></i>',
                    layer: layerGroups[<?php echo $layer->ID; ?>]
                });
                <?php endforeach; ?>

                // Handle heatmap
                <?php if ($enable_heatmap) : ?>
                var heatPoints = [];
                <?php foreach ($geometries as $geometry) : ?>
                <?php if ($geometry['type'] === 'Point' && isset($geometry['coordinates'][0], $geometry['coordinates'][1])) : ?>
                heatPoints.push([<?php echo floatval($geometry['coordinates'][1]); ?>, <?php echo floatval($geometry['coordinates'][0]); ?>, 1]);
                <?php endif; ?>
                <?php endforeach; ?>
                <?php if ($single_geometry && isset($single_geometry['coordinates'][0], $single_geometry['coordinates'][1])) : ?>
                heatPoints.push([<?php echo floatval($single_geometry['coordinates'][1]); ?>, <?php echo floatval($single_geometry['coordinates'][0]); ?>, 1]);
                <?php endif; ?>
                if (heatPoints.length > 0) {
                    var heatmapLayer = L.heatLayer(heatPoints, { radius: 25 });
                    overlayLayers.push({
                        name: 'Heatmap',
                        icon: '<i class="icon icon-heatmap"></i>',
                        layer: heatmapLayer
                    });
                }
                <?php endif; ?>

                // Handle single point
                <?php if ($single_geometry && isset($single_geometry['coordinates'][0], $single_geometry['coordinates'][1]) && !$enable_heatmap) : ?>
                var singleLayer = L.layerGroup();
                var singleMarker = L.marker([<?php echo floatval($single_geometry['coordinates'][1]); ?>, <?php echo floatval($single_geometry['coordinates'][0]); ?>], { icon: markerIcon })
                    .bindPopup('<h3><?php echo esc_js($single_geometry['title']); ?></h3><p><?php echo esc_js($single_geometry['description']); ?></p>');
                singleLayer.addLayer(singleMarker);
                overlayLayers.push({
                    name: 'Custom Point',
                    icon: '<i class="icon icon-marker"></i>',
                    layer: singleLayer
                });
                map.setView([<?php echo floatval($single_geometry['coordinates'][1]); ?>, <?php echo floatval($single_geometry['coordinates'][0]); ?>], <?php echo $default_zoom; ?>);
                <?php endif; ?>

                // Add weather layers if OpenWeatherMap API key is available
                <?php if (!empty($openweather_api_key)) : ?>
                try {
                    var cloudsLayer = L.tileLayer('https://tile.openweathermap.org/map/clouds_new/{z}/{x}/{y}.png?appid=<?php echo esc_js($openweather_api_key); ?>', {
                        attribution: '© <a href="https://openweathermap.org/">OpenWeatherMap</a>',
                        opacity: 0.7,
                        errorTileUrl: ''
                    }).on('tileerror', function(error, tile) {
                        console.error('Clouds layer tile error:', error, tile);
                    });
                    overlayLayers.push({
                        name: 'Clouds',
                        icon: '<i class="icon icon-cloud"></i>',
                        layer: cloudsLayer
                    });
                    var tempLayer = L.tileLayer('https://tile.openweathermap.org/map/temp_new/{z}/{x}/{y}.png?appid=<?php echo esc_js($openweather_api_key); ?>', {
                        attribution: '© <a href="https://openweathermap.org/">OpenWeatherMap</a>',
                        opacity: 0.7,
                        errorTileUrl: ''
                    }).on('tileerror', function(error, tile) {
                        console.error('Temperature layer tile error:', error, tile);
                    });
                    overlayLayers.push({
                        name: 'Temperature',
                        icon: '<i class="icon icon-thermometer"></i>',
                        layer: tempLayer
                    });
                } catch (e) {
                    console.error('Error adding weather layers:', e);
                }
                <?php else : ?>
                console.warn('OpenWeatherMap API key is missing. Weather layers are not loaded.');
                <?php endif; ?>

                // Initialize Panel Layers
                if (overlayLayers.length > 0 || baseLayers.length > 0) {
                    try {
                        panelLayers = new L.Control.PanelLayers(baseLayers, overlayLayers, {
                            collapsibleGroups: true,
                            position: 'topright'
                        });
                        map.addControl(panelLayers);
                        // Add all overlay layers to map by default
                        overlayLayers.forEach(function(overlay) {
                            map.addLayer(overlay.layer);
                        });
                        // Set default base layer
                        map.addLayer(baseLayers[0].layer);
                    } catch (e) {
                        console.error('Error initializing panel layers:', e);
                    }
                } else {
                    console.warn('No layers available to initialize panel.');
                }

                // Fit map to bounds if geometries exist
                var allMarkers = [];
                <?php foreach ($layers as $layer) : ?>
                allMarkers = allMarkers.concat(layerGroups[<?php echo $layer->ID; ?>].getLayers());
                <?php endforeach; ?>
                <?php if ($single_geometry && !$enable_heatmap) : ?>
                allMarkers.push(singleMarker);
                <?php endif; ?>
                if (allMarkers.length > 0) {
                    var group = new L.featureGroup(allMarkers);
                    map.fitBounds(group.getBounds());
                }

                <?php elseif ($library === 'google_maps') : ?>
                map = new google.maps.Map(document.getElementById('<?php echo esc_attr($map_id); ?>'), {
                    center: { lat: <?php echo $default_lat; ?>, lng: <?php echo $default_lon; ?> },
                    zoom: <?php echo $default_zoom; ?>,
                    styles: <?php echo $map_style ?: '[]'; ?>
                });
                markers = [];
                <?php foreach ($geometries as $geometry) : ?>
                <?php if ($geometry['type'] === 'Point' && isset($geometry['coordinates'][0], $geometry['coordinates'][1])) : ?>
                var marker = new google.maps.Marker({
                    position: { lat: <?php echo floatval($geometry['coordinates'][1]); ?>, lng: <?php echo floatval($geometry['coordinates'][0]); ?> },
                    map: map,
                    icon: { url: 'data:image/svg+xml;charset=UTF-8,<svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 30 30"><circle cx="15" cy="15" r="15" fill="<?php echo esc_js($marker_color); ?>"/></svg>' }
                });
                marker.addListener('click', function() {
                    new google.maps.InfoWindow({
                        content: '<h3><?php echo esc_js($geometry['title']); ?></h3><p><?php echo esc_js($geometry['description']); ?></p>' +
                                 '<?php if ($geometry['images']) : ?><div><?php foreach ($geometry['images'] as $image) : ?><img src="<?php echo esc_js($image); ?>" style="max-width:100px;" /><?php endforeach; ?></div><?php endif; ?>'
                    }).open(map, marker);
                });
                markers.push(marker);
                <?php endif; ?>
                <?php endforeach; ?>
                <?php if ($single_geometry && isset($single_geometry['coordinates'][0], $single_geometry['coordinates'][1])) : ?>
                var singleMarker = new google.maps.Marker({
                    position: { lat: <?php echo floatval($single_geometry['coordinates'][1]); ?>, lng: <?php echo floatval($single_geometry['coordinates'][0]); ?> },
                    map: map,
                    icon: { url: 'data:image/svg+xml;charset=UTF-8,<svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 30 30"><circle cx="15" cy="15" r="15" fill="<?php echo esc_js($marker_color); ?>"/></svg>' }
                });
                singleMarker.addListener('click', function() {
                    new google.maps.InfoWindow({
                        content: '<h3><?php echo esc_js($single_geometry['title']); ?></h3><p><?php echo esc_js($single_geometry['description']); ?></p>'
                    }).open(map, singleMarker);
                });
                map.setCenter({ lat: <?php echo floatval($single_geometry['coordinates'][1]); ?>, lng: <?php echo floatval($single_geometry['coordinates'][0]); ?> });
                <?php endif; ?>
                <?php elseif ($library === 'mapbox') : ?>
                mapboxgl.accessToken = mapPlugin.mapboxToken;
                map = new mapboxgl.Map({
                    container: '<?php echo esc_attr($map_id); ?>',
                    style: <?php echo $map_style ?: "'mapbox://styles/mapbox/streets-v11'"; ?>,
                    center: [<?php echo $default_lon; ?>, <?php echo $default_lat; ?>],
                    zoom: <?php echo $default_zoom; ?>
                });
                map.on('load', function() {
                    <?php foreach ($geometries as $geometry) : ?>
                    <?php if ($geometry['type'] === 'Point' && isset($geometry['coordinates'][0], $geometry['coordinates'][1])) : ?>
                    new mapboxgl.Marker({ color: '<?php echo esc_js($marker_color); ?>' })
                        .setLngLat([<?php echo floatval($geometry['coordinates'][0]); ?>, <?php echo floatval($geometry['coordinates'][1]); ?>])
                        .setPopup(new mapboxgl.Popup().setHTML(
                            '<h3><?php echo esc_js($geometry['title']); ?></h3><p><?php echo esc_js($geometry['description']); ?></p>' +
                            '<?php if ($geometry['images']) : ?><div><?php foreach ($geometry['images'] as $image) : ?><img src="<?php echo esc_js($image); ?>" style="max-width:100px;" /><?php endforeach; ?></div><?php endif; ?>'
                        ))
                        .addTo(map);
                    <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if ($single_geometry && isset($single_geometry['coordinates'][0], $single_geometry['coordinates'][1])) : ?>
                    new mapboxgl.Marker({ color: '<?php echo esc_js($marker_color); ?>' })
                        .setLngLat([<?php echo floatval($single_geometry['coordinates'][0]); ?>, <?php echo floatval($single_geometry['coordinates'][1]); ?>])
                        .setPopup(new mapboxgl.Popup().setHTML('<h3><?php echo esc_js($single_geometry['title']); ?></h3><p><?php echo esc_js($single_geometry['description']); ?></p>'))
                        .addTo(map);
                    map.setCenter([<?php echo floatval($single_geometry['coordinates'][0]); ?>, <?php echo floatval($single_geometry['coordinates'][1]); ?>]);
                    <?php endif; ?>
                });
                <?php endif; ?>
                $('.umap-search').on('input', function() {
                    var search = $(this).val().toLowerCase();
                    $.post(mapPlugin.ajaxurl, {
                        action: 'umap_search_geometries',
                        nonce: mapPlugin.nonce,
                        search: search,
                        layer_id: <?php echo $layer_id; ?>,
                        layer_ids: '<?php echo esc_js($atts['layer_ids']); ?>'
                    }, function(response) {
                        if (response.success) {
                            console.log('Search results:', response.data.geometries);
                        } else {
                            console.error('Search failed:', response.data);
                        }
                    }).fail(function(jqXHR, textStatus, errorThrown) {
                        console.error('Search AJAX failed:', textStatus, errorThrown);
                    });
                });
            } catch (e) {
                console.error('Map initialization error:', e);
            }
        });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('umap', 'umap_plugin_map_shortcode');

// Submission Form Shortcode
function umap_plugin_submission_form_shortcode() {
    if (!get_option('umap_allow_user_submission', 0)) {
        return '';
    }
    ob_start();
    ?>
    <form class="umap-submission-form" method="post" enctype="multipart/form-data">
        <p>
            <label><?php _e('Title', 'umap'); ?></label>
            <input type="text" name="umap_title" required />
        </p>
        <p>
            <label><?php _e('Description', 'umap'); ?></label>
            <textarea name="umap_description" rows="4" style="width:100%;"></textarea>
        </p>
        <p>
            <label><?php _e('Layer', 'umap'); ?></label>
            <select name="umap_layer_id">
                <option value=""><?php _e('Select Layer', 'umap'); ?></option>
                <?php
                $layers = get_posts(['post_type' => 'umap_layer', 'numberposts' => -1]);
                foreach ($layers as $layer) {
                    echo '<option value="' . esc_attr($layer->ID) . '">' . esc_html($layer->post_title) . '</option>';
                }
                ?>
            </select>
        </p>
        <p>
            <label><?php _e('Coordinates', 'umap'); ?></label>
            <div id="submission-map" style="height:300px;"></div>
            <input type="hidden" name="umap_coordinates" />
        </p>
        <p>
            <label><?php _e('Images', 'umap'); ?></label>
            <input type="file" name="umap_images[]" multiple accept="image/*" />
        </p>
        <h3><?php _e('Working Hours', 'umap'); ?></h3>
        <?php
        $days = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        foreach ($days as $day) {
            ?>
            <p>
                <label><?php echo esc_html(ucfirst($day)); ?>:</label>
                <input type="time" name="umap_working_hours[<?php echo $day; ?>][open]" />
                <input type="time" name="umap_working_hours[<?php echo $day; ?>][close]" />
                <label><input type="checkbox" name="umap_working_hours[<?php echo $day; ?>][closed]" value="1" /> <?php _e('Closed', 'umap'); ?></label>
            </p>
            <?php
        }
        ?>
        <?php wp_nonce_field('umap_submission', 'umap_submission_nonce'); ?>
        <input type="submit" class="button" value="<?php _e('Submit', 'umap'); ?>" />
    </form>
    <script>
        jQuery(document).ready(function($) {
            try {
                var map = L.map('submission-map').setView([35.6895, 51.3890], 10);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
                var marker;
                map.on('click', function(e) {
                    if (marker) map.removeLayer(marker);
                    marker = L.marker(e.latlng).addTo(map);
                    $('input[name="umap_coordinates"]').val(JSON.stringify([e.latlng.lng, e.latlng.lat]));
                });
            } catch (e) {
                console.error('Submission map error:', e);
            }
        });
    </script>
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['umap_submission_nonce']) && wp_verify_nonce($_POST['umap_submission_nonce'], 'umap_submission')) {
        $title = sanitize_text_field($_POST['umap_title']);
        $description = sanitize_textarea_field($_POST['umap_description']);
        $layer_id = intval($_POST['umap_layer_id']);
        $coordinates = wp_kses_post($_POST['umap_coordinates']);
        $working_hours = [];
        if (isset($_POST['umap_working_hours'])) {
            foreach ($_POST['umap_working_hours'] as $day => $hours) {
                $working_hours[$day] = [
                    'open' => sanitize_text_field($hours['open']),
                    'close' => sanitize_text_field($hours['close']),
                    'closed' => isset($hours['closed']) ? 1 : 0,
                ];
            }
        }
        $images = [];
        if (!empty($_FILES['umap_images']['name'][0])) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            foreach ($_FILES['umap_images']['name'] as $key => $value) {
                if ($_FILES['umap_images']['name'][$key]) {
                    $file = [
                        'name' => $_FILES['umap_images']['name'][$key],
                        'type' => $_FILES['umap_images']['type'][$key],
                        'tmp_name' => $_FILES['umap_images']['tmp_name'][$key],
                        'error' => $_FILES['umap_images']['error'][$key],
                        'size' => $_FILES['umap_images']['size'][$key],
                    ];
                    $_FILES['umap_image_temp'] = $file;
                    $attachment_id = media_handle_upload('umap_image_temp', 0);
                    if (!is_wp_error($attachment_id)) {
                        $images[] = $attachment_id;
                    }
                }
            }
        }
        $post_id = wp_insert_post([
            'post_type' => 'umap_geometry',
            'post_title' => $title,
            'post_status' => 'pending',
        ]);
        if ($post_id) {
            update_post_meta($post_id, '_umap_geometry_type', 'Point');
            update_post_meta($post_id, '_umap_geometry_coordinates', $coordinates);
            update_post_meta($post_id, '_umap_geometry_description', $description);
            update_post_meta($post_id, '_umap_geometry_working_hours', wp_json_encode($working_hours));
            update_post_meta($post_id, '_umap_geometry_layer_id', $layer_id);
            update_post_meta($post_id, '_umap_geometry_images', wp_json_encode($images));
            echo '<p>' . __('Your submission is pending review.', 'umap') . '</p>';
        }
    }
    return ob_get_clean();
}
add_shortcode('umap_submission_form', 'umap_plugin_submission_form_shortcode');

// AJAX Handler for Loading Geometries
function umap_plugin_load_geometries() {
    check_ajax_referer('umap_nonce', 'nonce');
    $layer_id = isset($_POST['layer_id']) ? intval($_POST['layer_id']) : 0;
    $args = [
        'post_type' => 'umap_geometry',
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ];
    if ($layer_id) {
        $args['meta_query'] = [
            [
                'key' => '_umap_geometry_layer_id',
                'value' => $layer_id,
                'compare' => '=',
            ],
        ];
    }
    $query = new WP_Query($args);
    $geometries = [];
    while ($query->have_posts()) {
        $query->the_post();
        $coordinates = json_decode(get_post_meta(get_the_ID(), '_umap_geometry_coordinates', true), true);
        if (!$coordinates || !is_array($coordinates)) {
            continue;
        }
        $images = json_decode(get_post_meta(get_the_ID(), '_umap_geometry_images', true), true) ?: [];
        $image_urls = array_map('wp_get_attachment_url', $images);
        $geometries[] = [
            'id' => get_the_ID(),
            'title' => get_the_title(),
            'type' => get_post_meta(get_the_ID(), '_umap_geometry_type', true),
            'coordinates' => $coordinates,
            'description' => get_post_meta(get_the_ID(), '_umap_geometry_description', true),
            'working_hours' => json_decode(get_post_meta(get_the_ID(), '_umap_geometry_working_hours', true), true),
            'images' => $image_urls,
        ];
    }
    wp_reset_postdata();
    wp_send_json_success(['geometries' => $geometries]);
}
add_action('wp_ajax_umap_load_geometries', 'umap_plugin_load_geometries');
add_action('wp_ajax_nopriv_umap_load_geometries', 'umap_plugin_load_geometries');

// AJAX Handler for Search
function umap_plugin_search_geometries() {
    check_ajax_referer('umap_nonce', 'nonce');
    $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    $layer_id = isset($_POST['layer_id']) ? intval($_POST['layer_id']) : 0;
    $args = [
        'post_type' => 'umap_geometry',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        's' => $search,
    ];
    if ($layer_id) {
        $args['meta_query'] = [
            [
                'key' => '_umap_geometry_layer_id',
                'value' => $layer_id,
                'compare' => '=',
            ],
        ];
    }
    $query = new WP_Query($args);
    $geometries = [];
    while ($query->have_posts()) {
        $query->the_post();
        $coordinates = json_decode(get_post_meta(get_the_ID(), '_umap_geometry_coordinates', true), true);
        if (!$coordinates || !is_array($coordinates)) {
            continue;
        }
        $images = json_decode(get_post_meta(get_the_ID(), '_umap_geometry_images', true), true) ?: [];
        $image_urls = array_map('wp_get_attachment_url', $images);
        $geometries[] = [
            'id' => get_the_ID(),
            'title' => get_the_title(),
            'type' => get_post_meta(get_the_ID(), '_umap_geometry_type', true),
            'coordinates' => $coordinates,
            'description' => get_post_meta(get_the_ID(), '_umap_geometry_description', true),
            'working_hours' => json_decode(get_post_meta(get_the_ID(), '_umap_geometry_working_hours', true), true),
            'images' => $image_urls,
        ];
    }
    wp_reset_postdata();
    wp_send_json_success(['geometries' => $geometries]);
}
add_action('wp_ajax_umap_search_geometries', 'umap_plugin_search_geometries');
add_action('wp_ajax_nopriv_umap_search_geometries', 'umap_plugin_search_geometries');

// AJAX Handler for Preview
function umap_plugin_preview_map() {
    check_ajax_referer('umap_nonce', 'nonce');
    $layer_id = isset($_POST['layer_id']) ? intval($_POST['layer_id']) : 0;
    echo do_shortcode('[umap layer_id="' . $layer_id . '"]');
    wp_die();
}
add_action('wp_ajax_umap_preview_map', 'umap_plugin_preview_map');
?>