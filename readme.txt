=== Alphabetical Tags List ===
Contributors: pulpcovers
Requires at least: 5.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

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
* Tag counts
* Tag links
* Works via shortcode
* No settings page — simple and fast

== Shortcode ==

Use the shortcode anywhere:

`[alphabetical_tags]`

The list of tags will display

=== Shortcode Attributes ===

* `min_count` (default: `1`) — Minimum number of posts required for a tag to display. Example: `5`, `10`
* `orderby` (default: `name`) — How to order tags within each letter group. Example: `count`, `slug`, `term_id`
* `order` (default: `ASC`) — Sort order, ascending or descending. Example: `DESC`
* `hide_empty` (default: `true`) — Whether to hide tags with no posts. Example: `false`
* `heading_size` (default: `24px`) — Font size for letter headings (A, B, C, etc.). Example: `20px`, `1.5em`, `2rem`
* `tag_size` (default: `14px`) — Font size for tag names. Example: `12px`, `0.875rem`, `16px`
* `show_jump_nav` (default: `true`) — Whether to display the sticky jump-to-letter navigation. Example: `false`

=== Example ===

Show only tags with 10 or more posts, sorted by popularity, with custom font sizes:

    [alphabetical_tags min_count="10" orderby="count" order="DESC" heading_size="28px" tag_size="13px"]

== Installation ==

1. Upload the plugin folder to /wp-content/plugins/
2. Activate the plugin through “Plugins → Installed Plugins”
3. Add the shortcode [alphabetical_tags] to any page or post — or call the function in your theme.

== Frequently Asked Questions ==

= Can I style the output? =
Yes. Use the shortcode attributes for basic styling:

* `heading_size` — font size for letter headings (default: 24px)
* `tag_size` — font size for tag names (default: 14px)

For more advanced styling, the plugin outputs HTML with
consistent CSS classes you can target in your theme's
stylesheet:

* `.atl-container` — outer wrapper
* `.atl-jump-nav` — sticky letter navigation bar
* `.atl-jump-link` — individual letter links in the navigation
* `.atl-letter-section` — wrapper for each letter group
* `.atl-letter-heading` — the letter heading (A, B, C, etc.)
* `.atl-tags-grid` — the grid of tags within each letter
* `.atl-tag-item` — individual tag wrapper
* `.atl-tag-link` — the tag link
* `.atl-tag-count` — the post count in parentheses

= Why isn't my tag showing? =
There are two default behaviours that can cause a tag to be hidden:

* `hide_empty` is set to `true` by default, meaning tags with no 
  posts assigned to them will not appear. To show all tags regardless 
  of post count, use:

    [alphabetical_tags hide_empty="false"]

* `min_count` is set to `1` by default, meaning a tag must have at 
  least one post to display. If you have raised this value, tags below 
  that threshold will be hidden. For example, if you have set 
  `min_count="5"`, any tag with fewer than 5 posts will not appear.

To display all tags with no restrictions:

    [alphabetical_tags hide_empty="false" min_count="1"]

= Does it support accented and international characters? =
Yes. The plugin normalizes accented and special characters when 
grouping tags by letter, so tags are always filed under their base 
letter regardless of diacritics. For example:

* Árbol, Águila → grouped under A
* Éclair, Ñoño → grouped under E and N respectively
* Über → grouped under U

The following scripts are supported:

* Latin extended — Spanish, French, German, Portuguese, Polish, 
  Czech, and 100+ more (Á, É, Ñ, Ü, Ç, Ø, Ł, etc.)
* Cyrillic — transliterated to their ASCII equivalents for grouping
* Greek — transliterated to their ASCII equivalents for grouping

The original tag name is always preserved in the display — only the 
grouping logic uses the normalized form.

For best results, ensure your server has the PHP `intl` extension 
enabled. The plugin will fall back to a built-in character map if 
`intl` is unavailable, so international characters will still be 
handled correctly in either case.

= Does this plugin add any settings pages? =
No. It's intentionally lightweight — everything is controlled via 
shortcode attributes.

= Does it support custom taxonomies? =
Not yet. The plugin currently supports the built-in post_tag taxonomy.

= Does it work with block themes? =
Yes. You can place the shortcode in any block that supports shortcodes.

== Changelog ==

= 1.0.0 =
* Initial release
