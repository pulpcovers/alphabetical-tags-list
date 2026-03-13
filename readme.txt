=== Alphabetical Tags List ===
Contributors: pulpcovers
Requires at least: 5.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.3
License: CC0-1.0
License URI: https://creativecommons.org/publicdomain/zero/1.0/

Display your WordPress tags in a clean, alphabetical list — with optional counts, links, and formatting controls.

== Description ==

Alphabetical Tags List provides a simple, flexible way to display all your WordPress tags in alphabetical order.
It works anywhere you can place a shortcode — posts, pages, widgets, or block editors.

This plugin is ideal for:

* Sites with large tag collections
* Readers who need a browsable tag index
* SEO‑friendly tag navigation
* Magazine, blog, and archive‑heavy sites

The output is lightweight, accessible, and easy to style.

=== Features ===

* Display all tags alphabetically
* Optional tag counts
* Optional tag links
* Optional CSS class for custom styling
* Works via shortcode or template function
* No JavaScript required
* No settings page — simple and fast

== Shortcode ==

Use the shortcode anywhere:

    [alphabetical_tags_list]

== Shortcode Attributes ==

| Attribute | Default | Description | Example Values |
|-----------|---------|-------------|----------------|
| `min_count` | `1` | Minimum number of posts required for a tag to display | `1`, `5`, `10` |
| `orderby` | `name` | How to order tags within each letter group | `name`, `count`, `slug`, `term_id` |
| `order` | `ASC` | Sort order (ascending or descending) | `ASC`, `DESC` |
| `hide_empty` | `true` | Whether to hide tags with no posts | `true`, `false` |
| `heading_size` | `24px` | Font size for letter headings (A, B, C, etc.) | `20px`, `1.5em`, `2rem` |
| `tag_size` | `14px` | Font size for tag names | `12px`, `0.875rem`, `16px` |
| `show_jump_nav` | `true` | Whether to display the sticky jump-to-letter navigation | `true`, `false` |

Example:

    [alphabetical_tags min_count="10" orderby="count" order="DESC" heading_size="28px" tag_size="13px"]

== Template Function ==

You can also output the list directly in your theme:

    if ( function_exists( 'alphabetical_tags_list' ) ) {
        echo alphabetical_tags_list( array(
            'show_count' => true,
            'show_link'  => true,
            'class'      => 'tag-list',
        ) );
    }

== Installation ==

1. Upload the plugin folder to /wp-content/plugins/
2. Activate the plugin through “Plugins → Installed Plugins”
3. Add the shortcode [alphabetical_tags_list] to any page or post — or call the function in your theme.

== Frequently Asked Questions ==

= Can I style the output? =
Yes. Add a custom class using the “class” attribute, then style it in your theme’s CSS.

= Does this plugin add any settings pages? =
No. It’s intentionally lightweight — everything is controlled via shortcode attributes.

= Does it support custom taxonomies? =
Not yet. The plugin currently supports the built‑in post_tag taxonomy.

= Does it work with block themes? =
Yes. You can place the shortcode in any block that supports shortcodes.

== Changelog ==

= 1.0.0 =
* Initial release
