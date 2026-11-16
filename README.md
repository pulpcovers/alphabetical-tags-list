# Alphabetical Tags List
A lightweight WordPress plugin that displays all your blog tags in an organized, alphabetical list grouped by first letter. Perfect for blogs with hundreds or thousands of tags.

## Features

 **Alphabetical Organization** - Tags grouped by first letter (A-Z)  
 **International Character Support** - Handles accented characters (Á, É, Ñ, Ü, etc.) and transliterates Cyrillic and Greek for grouping  
 **UTF-8 Compatible** - Tag names display correctly in any language; grouping covers Latin, Cyrillic and Greek (other scripts go under "#")  
 **Smart Grouping** - Numbers grouped as "0-9", symbols as "#"  
 **Responsive Grid Layout** - Adapts to any screen size  
 **Theme Integration** - Tag links use your theme's styling  
 **Post Count Display** - Shows number of posts for each tag (can be turned off with `show_count="false"`)  
 **No Settings Page** - Controlled entirely by shortcode attributes  
 **Lightweight** - No external dependencies — minimal JavaScript and CSS 
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
| `min_count` | `1` | Minimum number of posts required for a tag to display (`0` = no minimum) | `0`, `1`, `5`, `10` |
| `orderby` | `name` | How to order tags within each letter group | `name`, `count`, `slug`, `term_id` |
| `order` | `ASC` | Sort order (ascending or descending) | `ASC`, `DESC` |
| `hide_empty` | `true` | Whether to hide tags with no posts | `true`, `false` |
| `heading_size` | `24px` | Font size for letter headings (A, B, C, etc.) | `20px`, `1.5em`, `2rem` |
| `tag_size` | `14px` | Font size for tag names | `12px`, `0.875rem`, `16px` |
| `show_jump_nav` | `true` | Whether to display the sticky jump-to-letter navigation | `true`, `false` |
| `heading_level` | `2` | HTML heading level for letter headings (1–6 or `h1`–`h6`) | `2`, `3`, `h4` |
| `show_count` | `true` | Whether to show the post count after each tag | `true`, `false` |

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

### Hide Post Counts
Show tags without the number of posts:
[alphabetical_tags show_count="false"]

### Hide Jump Navigation
Remove the A-Z sticky navigation at the top:
[alphabetical_tags show_jump_nav="false"]

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
2. **#** (all tags starting with symbols like `.`, `#`, `@`, etc.)
3. **A-Z** (alphabetically)

## International Character Support

The plugin properly handles accented and special characters by normalizing them to their ASCII equivalents for grouping:

- **Á**rbol, **Á**guila → Grouped under **A**
- **É**ducation, **È**cole → Grouped under **E**
- **Ñ**oño → Grouped under **N**
- **Ü**ber → Grouped under **U**

The original tag names are preserved in the display; only the grouping is normalized.

Transliterations that produce several letters are grouped under the first one (for example, **Ж** → `ZH` is filed under **Z**, and **Æ** → `AE` under **A**). Tags starting with other scripts (Chinese, Japanese, Korean, Arabic, Hebrew, etc.) are grouped under **#**.

### Supported Characters

- **Spanish**: Á, É, Í, Ó, Ú, Ñ, Ü
- **French**: À, É, È, Ê, Ç, Œ
- **German**: Ä, Ö, Ü, ß
- **Portuguese**: Ã, Õ, Ç
- **Polish**: Ł, Ź, Ż, Ą, Ę
- **Cyrillic**: А, Б, В, Г, Д
- **Greek**: Α, Β, Γ, Δ, Ε
- **Czech**: Č, Ř, Ž, Ů
- And 100+ more extended Latin characters

## Styling

The plugin uses minimal CSS and allows your theme's link styles to apply to tag links. The default styling includes:

- Responsive grid layout (adjusts to screen width)
- Letter headings with bottom border
- Tag count shown in a lighter shade of the text color
- No background boxes (clean, simple look)

All styles can be overridden using custom CSS in your theme, or by setting these CSS custom properties (for example on `:root`):

| Property | Default | Purpose |
|----------|---------|---------|
| `--atl-accent` | `#0073aa` | Accent color (borders, active/hover letters) |
| `--atl-accent-text` | `#fff` | Text color on accented items |
| `--atl-text` | inherited | Text color for letter links and headings |
| `--atl-nav-bg` | theme base color or `#fff` | Background of the sticky navigation |

When no tags match, visitors see nothing; logged-in users who can edit posts see a "No tags found." notice.

## Browser Compatibility

- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Requirements

- WordPress 5.9 or higher (the sticky navigation uses the admin-bar height variable that WordPress added in 5.9)
- PHP 7.4 or higher
- PHP mbstring extension (recommended; without it, tags starting with non-ASCII characters are grouped under `#`)

## Performance

The plugin is designed to stay light on large sites:

- A single tag query per shortcode, with per-character caching of the normalization work
- Assets load only on pages that render the shortcode
- Active-letter tracking uses an `IntersectionObserver` instead of scroll handlers
- Minimal CSS/JavaScript
- No external API calls

The rendered list is not cached between requests; use a page-cache plugin if you have very large tag sets and heavy traffic.

## Changelog

### 1.2.0
- **Performance:** assets load only where the shortcode renders; cached character normalization; `IntersectionObserver`-based active-letter tracking
- **Accessibility:** visible focus styles, ARIA labels and states, reduced-motion support, focus moves to the section heading
- **Theming:** CSS custom properties (`--atl-accent`, `--atl-accent-text`, `--atl-text`, `--atl-nav-bg`); sticky offsets follow the WordPress admin bar
- New `heading_level` and `show_count` attributes; `min_count="0"` is now allowed
- Empty-state notice is shown only to users who can edit posts
- **Fix:** correct group order (0-9, #, A-Z); multi-letter transliterations (e.g. Ж, Æ) now group under their first letter
- **Fix:** plugin no longer errors when the mbstring extension is missing
- Version constant is now read from the plugin header

### 1.0.0
- Initial release
  
## Support

For bug reports, feature requests, or questions:

## License

GPLv2 or later — https://www.gnu.org/licenses/gpl-2.0.html

- **GitHub Issues**: [https://github.com/pulpcovers/alphabetical-tags-list/issues](https://github.com/pulpcovers/alphabetical-tags-list/issues)


