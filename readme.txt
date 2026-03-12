=== Alphabetical Tags List ===
Contributors: pulpcovers
Requires at least: 5.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
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

Attribute | Default | Description
--------- | ------- | -----------
show_count | false | Show the number of posts for each tag
show_link | true | Make each tag clickable
class | (none) | Add a custom CSS class to the wrapper

Example:

    [alphabetical_tags_list show_count="true" show_link="false" class="my-tag-list"]

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
