# Ultimate Map Plugin for WordPress

![License](https://img.shields.io/badge/license-GPLv2%20or%20later-blue.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.0%2B-blue.svg)
![Version](https://img.shields.io/badge/version-1.0.5-green.svg)

The **Ultimate Map Plugin** is a versatile WordPress plugin that enables the creation of interactive maps with customizable layers, user submissions, and real-time weather data integration. Supporting Leaflet, Google Maps, and MapBox, it is designed for businesses, travel blogs, event organizers, and any site needing dynamic map visualizations.

- **Repository**: [github.com/ildrm/wp-ultimate-map-plugin](https://github.com/ildrm/wp-ultimate-map-plugin)
- **Author**: Shahin Ilderemi ([@ildrm](https://github.com/ildrm))
- **Plugin Directory**: `wp-ultimate-map-plugin`
- **Main File**: `wp-ultimate-map-plugin.php`
- **License**: [GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html)

## Overview

The Ultimate Map Plugin empowers WordPress users to build and manage interactive maps with ease. Key features include support for multiple map libraries, custom geometries (points, lines, polygons), marker clustering, heatmaps, user-submitted points, and weather layers (clouds and temperature) via OpenWeatherMap. The plugin is highly configurable through the WordPress admin panel, making it suitable for a wide range of applications.

For detailed plugin metadata and WordPress-specific documentation, see the [readme.txt](readme.txt) file, formatted for the WordPress Plugin Directory.

## Features

- **Multiple Map Libraries**: Switch between Leaflet, Google Maps, and MapBox with customizable base and overlay layers.
- **Custom Layers & Geometries**: Manage points, lines, and polygons using WordPress custom post types.
- **Marker Clustering & Heatmaps**: Optimize large datasets with clustering and visualize data density (Leaflet only).
- **User Submissions**: Allow front-end submissions with descriptions, images, and working hours via a shortcode.
- **Weather Integration**: Display real-time cloud and temperature layers using OpenWeatherMap (Leaflet only).
- **Search & Filtering**: Enable searchable maps with distance-based filtering.
- **GeoJSON Support**: Import and export map data in GeoJSON format.
- **Configurable Settings**: Customize API keys, coordinates, zoom, marker colors, and more via the admin panel.

## Use Cases

- Display business locations with detailed popups and working hours.
- Create interactive travel maps with user-contributed points.
- Visualize event locations with real-time weather overlays.
- Build data-driven maps with clustering and heatmap visualizations.

## Installation

1. **Download the Plugin**:
   - Clone the repository:
     ```bash
     git clone https://github.com/ildrm/wp-ultimate-map-plugin.git
     ```
   - Or download the ZIP file from the [releases page](https://github.com/ildrm/wp-ultimate-map-plugin/releases).

2. **Upload to WordPress**:
   - Copy the `wp-ultimate-map-plugin` folder to the `/wp-content/plugins/` directory.
   - Alternatively, upload the ZIP file via the WordPress admin panel (**Plugins > Add New > Upload Plugin**).

3. **Activate the Plugin**:
   - Navigate to **Plugins** in the WordPress admin and activate **Ultimate Map Plugin**.

4. **Configure Settings**:
   - Go to **Ultimate Map Plugin > Settings** in the WordPress admin.
   - Select a map library (Leaflet, Google Maps, or MapBox).
   - Enter API keys for Google Maps, MapBox, and/or OpenWeatherMap.
   - Set default coordinates, zoom level, marker colors, and other options.

5. **Create Layers and Geometries**:
   - Use the **Layers** and **Geometries** menus to define map layers and add geometries.
   - Assign geometries to layers for organized rendering.

6. **Display Maps**:
   - Embed maps using the `[umap]` shortcode (e.g., `[umap layer_id="1"]`).
   - Enable user submissions with the `[umap_submission_form]` shortcode.

## Configuration

Configure the plugin via **Ultimate Map Plugin > Settings** in the WordPress admin. Key options include:

- **Map Library**: Leaflet (default), Google Maps, or MapBox.
- **API Keys**:
  - [Google Maps API](https://console.cloud.google.com/)
  - [MapBox Access Token](https://www.mapbox.com/studio)
  - [OpenWeatherMap API](https://openweathermap.org/)
- **Map Defaults**: Latitude, longitude, and zoom level.
- **Visual Options**: Marker clustering, heatmaps, and marker color.
- **User Submissions**: Enable front-end submissions with customizable fields.

## Shortcode Examples

- Display a map with a specific layer:
  ```shortcode
  [umap layer_id="1"]
  ```
- Show a map with multiple layers:
  ```shortcode
  [umap layer_ids="1,2,3"]
  ```
- Add a single point:
  ```shortcode
  [umap point="35.6895,51.3890"]
  ```
- Enable user submission form:
  ```shortcode
  [umap_submission_form]
  ```

## Development

### Prerequisites

- WordPress 5.0 or higher
- PHP 7.0 or higher
- MySQL 5.6 or higher (or MariaDB equivalent)

### Project Structure

```
wp-ultimate-map-plugin/
├── wp-ultimate-map-plugin.php  # Main plugin file
├── readme.txt                 # WordPress Plugin Directory documentation
└── README.md                  # GitHub documentation
```

> **Note**: Additional directories (e.g., `assets/`, `includes/`, `languages/`) may be added in future releases to support styles, scripts, and translations.

### Setup for Development

1. Clone the repository:
   ```bash
   git clone https://github.com/ildrm/wp-ultimate-map-plugin.git
   ```

2. Set up a local WordPress environment (e.g., [LocalWP](https://localwp.com/) or Docker).
3. Symlink or copy the `wp-ultimate-map-plugin` folder to `/wp-content/plugins/`.
4. Activate the plugin and start development.

### Contributing

Contributions are welcome! To contribute:

1. Fork the repository.
2. Create a feature branch: `git checkout -b feature/your-feature-name`.
3. Commit changes: `git commit -m "Add your feature description"`.
4. Push to the branch: `git push origin feature/your-feature-name`.
5. Open a pull request with a detailed description.

Please follow the [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/) and include unit tests where possible. See [CONTRIBUTING.md](CONTRIBUTING.md) for detailed guidelines (to be added).

## Troubleshooting

- **Weather Layers Not Displaying**:
  - Verify the OpenWeatherMap API key in settings.
  - Ensure Leaflet is the selected map library.
  - Check the browser console for errors (e.g., 401 for invalid API key).

- **Map Not Rendering**:
  - Confirm valid API keys for Google Maps or MapBox.
  - Ensure layers and geometries are correctly assigned.
  - Inspect the browser console for JavaScript errors.

- **User Submissions Not Working**:
  - Enable **Allow User Submissions** in settings.
  - Verify the `[umap_submission_form]` shortcode is on a page.

For support, open an issue on the [GitHub repository](https://github.com/ildrm/wp-ultimate-map-plugin/issues).

## Frequently Asked Questions

### Which map libraries are supported?
Leaflet (default), Google Maps, and MapBox, selectable in the settings.

### Are API keys required?
Yes, for Google Maps, MapBox, and OpenWeatherMap features. Enter keys in the plugin settings.

### How do I enable weather layers?
Provide a valid OpenWeatherMap API key. Clouds and temperature layers appear in Leaflet's layer control.

### Can users submit points?
Enable **Allow User Submissions** and use the `[umap_submission_form]` shortcode.

### What geometries are supported?
Points, Lines, and Polygons, managed via the WordPress admin.

### How do I import/export data?
Use the **Import/Export** section in settings to handle GeoJSON files.

## Changelog

### 1.0.5 (2025-05-08)
- Added OpenWeatherMap integration for clouds and temperature layers.
- Fixed duplicate Map Library field in settings.
- Improved error handling for weather layers.

### 1.0.4 (2025-03-15)
- Added Leaflet Panel Layers for better layer control.
- Enhanced debug messages.
- Fixed minor admin CSS issues.

### 1.0.3 (2025-01-20)
- Introduced marker clustering and heatmaps.
- Added GeoJSON import/export.
- Optimized geometry search.

### 1.0.2 (2024-11-10)
- Added user submission form with shortcode.
- Supported working hours and image uploads.
- Fixed WordPress 6.0+ compatibility.

### 1.0.1 (2024-09-05)
- Added Google Maps and MapBox support.
- Improved admin UI for layers and geometries.
- Fixed shortcode bugs.

### 1.0.0 (2024-07-01)
- Initial release with Leaflet, custom layers, and geometry management.

## License

Licensed under the [GNU General Public License v2 or later](https://www.gnu.org/licenses/gpl-2.0.html). You may use, modify, and distribute this plugin under the terms of this license.

## Credits

Developed by Shahin Ilderemi ([@ildrm](https://github.com/ildrm)). Thanks to the open-source community and the following libraries:

- [Leaflet](https://leafletjs.com/)
- [Google Maps JavaScript API](https://developers.google.com/maps)
- [MapBox](https://www.mapbox.com/)
- [OpenWeatherMap](https://openweathermap.org/)

## Support

For issues or feature requests:
- Open an issue on [GitHub](https://github.com/ildrm/wp-ultimate-map-plugin/issues).
- Contact the author via [GitHub](https://github.com/ildrm).

Contributions and feedback are appreciated! Star the repository if you find it useful.