=== Ultimate Map Plugin ===
Contributors: [your-wordpress-username]
Tags: map, leaflet, google maps, mapbox, interactive map, geolocation, weather, user submission, geojson
Requires at least: 5.0
Tested up to: 6.6
Stable tag: 1.0.5
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create interactive maps with customizable layers, user submissions, and weather data using Leaflet, Google Maps, or MapBox.

== Description ==

**Ultimate Map Plugin** is a powerful WordPress plugin designed to create and manage interactive maps with ease. It supports multiple map libraries (Leaflet, Google Maps, MapBox) and offers advanced features like custom layers, user-submitted points, and real-time weather data (clouds and temperature) via OpenWeatherMap integration. Whether you're showcasing locations, visualizing data, or engaging users, this plugin is perfect for businesses, travel blogs, event organizers, or any site needing dynamic maps.

= Key Features =
* **Multiple Map Libraries**: Choose between Leaflet, Google Maps, or MapBox with customizable base and overlay layers.
* **Custom Layers & Geometries**: Manage points, lines, and polygons via custom post types.
* **Marker Clustering & Heatmaps**: Optimize performance and visualize data density.
* **User Submissions**: Allow front-end users to submit points with descriptions, images, and working hours.
* **Weather Integration**: Display real-time cloud and temperature layers using OpenWeatherMap API.
* **Search & Filter**: Enable searchable maps with distance-based filtering.
* **GeoJSON Support**: Import and export map data in GeoJSON format.
* **Customizable Settings**: Configure API keys, default coordinates, zoom levels, and marker colors via the admin panel.

= Use Cases =
* Display business locations with detailed popups and working hours.
* Create interactive travel maps with user-contributed points.
* Visualize event locations with weather overlays.
* Build data-driven maps with heatmap and clustering capabilities.

== Installation ==

1. Upload the `ultimate-map-plugin` folder to the `/wp-content/plugins/` directory, or install directly via the WordPress plugins screen.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Go to **Ultimate Map Plugin > Settings** to configure the map library, API keys (Google Maps, MapBox, OpenWeatherMap), and other preferences.
4. Create layers and geometries via the **Layers** and **Geometries** menus in the WordPress admin.
5. Use the `[umap]` shortcode to display maps on your pages or posts (e.g., `[umap layer_id="1"]`).
6. Optionally enable user submissions with the `[umap_submission_form]` shortcode.

== Frequently Asked Questions ==

= Which map libraries are supported? =
The plugin supports Leaflet (default), Google Maps, and MapBox. You can select your preferred library in the settings.

= Do I need API keys? =
Yes, for Google Maps, MapBox, and OpenWeatherMap features, you need to provide valid API keys in the plugin settings.

= Can users submit points from the front-end? =
Yes, enable the **Allow User Submissions** option in settings and use the `[umap_submission_form]` shortcode to display a submission form.

= How do I display weather data? =
Enter a valid OpenWeatherMap API key in the settings. Clouds and temperature layers will appear in the map's layer control (Leaflet only).

= What types of geometries are supported? =
The plugin supports Points, Lines, and Polygons, manageable via the WordPress admin.

= How can I import/export map data? =
Use the **Import/Export** section in the settings to upload GeoJSON files or download existing geometries.

= Why don't I see weather layers? =
Ensure you have a valid OpenWeatherMap API key and that Leaflet is selected as the map library. Check the browser console for errors.

== Screenshots ==

1. **Admin Settings**: Configure map library, API keys, and other options.
2. **Layer Management**: Create and manage custom layers for your maps.
3. **Geometry Editor**: Add points, lines, or polygons with descriptions and images.
4. **Interactive Map**: Display maps with layer controls, clustering, and weather data.
5. **User Submission Form**: Allow users to submit points from the front-end.

== Changelog ==

= 1.0.5 =
* Added OpenWeatherMap integration for clouds and temperature layers.
* Fixed duplicate Map Library field in settings.
* Improved error handling for weather layer loading.

= 1.0.4 =
* Added Leaflet Panel Layers for better layer control.
* Enhanced debug messages for easier troubleshooting.
* Fixed minor CSS issues in the admin interface.

= 1.0.3 =
* Introduced marker clustering and heatmap options.
* Added support for GeoJSON import/export.
* Improved search functionality for geometries.

= 1.0.2 =
* Added user submission form with shortcode `[umap_submission_form]`.
* Included working hours and image uploads for geometries.
* Fixed compatibility issues with WordPress 6.0+.

= 1.0.1 =
* Added support for Google Maps and MapBox alongside Leaflet.
* Improved admin UI for layer and geometry management.
* Fixed minor bugs in shortcode handling.

= 1.0.0 =
* Initial release with Leaflet support, custom layers, and geometry management.

== Upgrade Notice ==

= 1.0.5 =
This update adds weather layers (clouds and temperature) via OpenWeatherMap and fixes the duplicate Map Library field issue. Enter a valid OpenWeatherMap API key to enable weather features.

== Arbitrary section ==

= Getting API Keys =
* **Google Maps**: Obtain an API key from the [Google Cloud Console](https://console.cloud.google.com/).
* **MapBox**: Get an access token from [MapBox Studio](https://www.mapbox.com/studio).
* **OpenWeatherMap**: Sign up at [OpenWeatherMap](https://openweathermap.org/) to receive an API key.

= Shortcode Examples =
* Display a map with a specific layer: `[umap layer_id="1"]`
* Show a map with multiple layers: `[umap layer_ids="1,2,3"]`
* Add a single point: `[umap point="35.6895,51.3890"]`
* Enable user submissions: `[umap_submission_form]`

= Support =
For issues or feature requests, visit the [plugin support forum](https://wordpress.org/support/plugin/ultimate-map-plugin) or contact the developer via [your contact link].