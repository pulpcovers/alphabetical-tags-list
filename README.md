# Alphabetical Tags List - WordPress Plugin

A lightweight WordPress plugin that displays all your blog tags in an organized, alphabetical list grouped by first letter. Perfect for blogs with hundreds or thousands of tags.

## Features

 **Alphabetical Organization** - Tags grouped by first letter (A-Z)  
 **International Character Support** - Handles accented characters (Á, É, Ñ, Ü, etc.)  
 **UTF-8 Compatible** - Properly sorts tags in any language  
 **Smart Grouping** - Numbers grouped as "0-9", symbols as "# Symbols"  
 **Responsive Grid Layout** - Adapts to any screen size  
 **Theme Integration** - Tag links use your theme's styling  
 **Post Count Display** - Shows number of posts for each tag  
 **No Settings Page** - Controlled entirely by shortcode attributes  
 **Lightweight** - No external dependencies  
 **Customizable** - Multiple shortcode options for fine-tuning

## Installation

### Manual Installation

1. Download the plugin files
2. Upload the `alphabetical-tags-list` folder to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Add the shortcode `[alphabetical_tags]` to any page or post

## Usage
### Basic Usage
Simply add the shortcode to any page or post:

[alphabetical_tags]

### Shortcode Options
| Attribute | Default | Description | Example Values |
|-----------|---------|-------------|----------------|
| `min_count` | `1` | Minimum number of posts required for a tag to display | `1`, `5`, `10` |
| `orderby` | `name` | How to order tags within each letter group | `name`, `count`, `slug` |
| `order` | `ASC` | Sort order (ascending or descending) | `ASC`, `DESC` |
| `hide_empty` | `true` | Whether to hide tags with no posts | `true`, `false` |
| `heading_size` | `24px` | Font size for letter headings (A, B, C, etc.) | `20px`, `1.5em`, `2rem` |
| `tag_size` | `14px` | Font size for tag names | `12px`, `0.875rem`, `16px` |
| `show_jump_nav` | `true` | Whether to display the sticky jump-to-letter navigation | `true`, `false` |

## Examples

### Show Only Popular Tags

Display only tags with at least 5 posts:

[alphabetical_tags min_count="5"]

### Sort by Popularity

Order tags by post count (most popular first) within each letter:

[alphabetical_tags orderby="count" order="DESC"]


### Show All Tags (Including Empty)

Display tags even if they have no posts:

[alphabetical_tags hide_empty="false"]


### Customize Font Sizes

Make headings larger and tags smaller:

[alphabetical_tags heading_size="32px" tag_size="12px"]


### Use Relative Font Sizes

Use em or rem units for better scaling:

[alphabetical_tags heading_size="1.5em" tag_size="0.875rem"]


### Combine Multiple Options

Show only tags with 10+ posts, sorted by count, with custom sizes:

[alphabetical_tags min_count="10" orderby="count" order="DESC" heading_size="28px" tag_size="13px"]

## Display Order

Tags are displayed in the following order:

1. **0-9** (all tags starting with numbers)
2. **# Symbols** (all tags starting with symbols like `.`, `#`, `@`, etc.)
3. **A-Z** (alphabetically)

## International Character Support

The plugin properly handles accented and special characters by normalizing them to their ASCII equivalents for grouping:

- **Á**rbol, **Á**guila → Grouped under **A**
- **É**ducation, **È**cole → Grouped under **E**
- **Ñ**oño → Grouped under **N**
- **Ü**ber → Grouped under **U**

The original tag names are preserved in the display; only the grouping is normalized.

### Supported Characters

- **Spanish**: Á, É, Í, Ó, Ú, Ñ, Ü
- **French**: À, É, È, Ê, Ç, Œ
- **German**: Ä, Ö, Ü, ß
- **Portuguese**: Ã, Õ, Ç
- **Polish**: Ł, Ź, Ż, Ą, Ę
- **Czech**: Č, Ř, Ž, Ů
- And 100+ more extended Latin characters

## Styling

The plugin uses minimal CSS and allows your theme's link styles to apply to tag links. The default styling includes:

- Responsive grid layout (adjusts to screen width)
- Letter headings with bottom border
- Tag count in gray
- No background boxes (clean, simple look)

All styles can be overridden using custom CSS in your theme.

## Browser Compatibility

- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher
- PHP mbstring extension (usually enabled by default)

## Performance

The plugin is optimized for sites with thousands of tags:

- Efficient database queries
- Minimal CSS/JavaScript
- No external API calls
- Server-side rendering (no client-side delays)

## License

This plugin is released under the [CC0 1.0 Universal](https://creativecommons.org/publicdomain/zero/1.0/) license (Public Domain). You are free to use, modify, and distribute it without restriction.

## Support

For bug reports, feature requests, or questions:

- **GitHub Issues**: [https://github.com/pulpcovers/alphabetical-tags-list/issues](https://github.com/pulpcovers/alphabetical-tags-list/issues)
- **Website**: [https://pulpcovers.com](https://pulpcovers.com)

## Changelog

### Version 1.1
- Added `heading_size` shortcode attribute
- Added `tag_size` shortcode attribute
- Removed theme-overriding link styles
- Improved compact display for large tag lists

### Version 1.0
- Initial release
- Alphabetical grouping with letter headers
- International character support
- UTF-8 compatibility
- Responsive grid layout
- Shortcode-based configuration

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Credits

Created by [PulpCovers](https://pulpcovers.com)
