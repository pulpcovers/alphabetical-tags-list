<?php
/**
* Plugin Name: Alphabetical Tags List
* Plugin URI: https://github.com/pulpcovers/alphabetical-tags-list
* Description: Display all tags alphabetically grouped by first letter using shortcode [alphabetical_tags]
* Version: 1.2.0
* Tested up to: 7.1
* Author: PulpCovers
* Author URI: https://pulpcovers.com
* License: GPLv2 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain: alphabetical-tags-list
*/
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
// Single source of truth: the Version header above
$atl_plugin_data = get_file_data(__FILE__, array('Version' => 'Version'));
define('ALPHABETICAL_TAGS_VERSION', !empty($atl_plugin_data['Version']) ? $atl_plugin_data['Version'] : '1.2.0');
unset($atl_plugin_data);
define('ALPHABETICAL_TAGS_PLUGIN_URL', plugin_dir_url(__FILE__));
class Alphabetical_Tags_List {
public function __construct() {
    add_shortcode('alphabetical_tags', array($this, 'render_tags_list'));
    add_action('wp_enqueue_scripts', array($this, 'register_assets'));
}



    // Register (but don't enqueue) assets; they are enqueued only when the shortcode renders
public function register_assets() {
    wp_register_style('atl-styles', false, array(), ALPHABETICAL_TAGS_VERSION);
    wp_register_script(
        'atl-jump-nav',
        ALPHABETICAL_TAGS_PLUGIN_URL . 'assets/js/atl-jump-nav.js',
        array(),
        ALPHABETICAL_TAGS_VERSION,
        true
    );
}

    // Enqueue assets once per request, from wherever the shortcode is rendered
private function enqueue_assets($with_jump_nav) {
    static $style_added = false;

    if (!wp_style_is('atl-styles', 'registered')) {
        $this->register_assets();
    }

    if (!$style_added) {
        wp_enqueue_style('atl-styles');
        wp_add_inline_style('atl-styles', $this->get_inline_css());
        $style_added = true;
    }

    if ($with_jump_nav) {
        wp_enqueue_script('atl-jump-nav');
    }
}

    // Per-size-combination CSS variables, added once each (no inline style attributes)
    private function add_size_rules($heading_size, $tag_size) {
        static $added = array();

        $class = 'atl-sz-' . substr(md5($heading_size . '|' . $tag_size), 0, 8);

        if (!isset($added[$class])) {
            wp_add_inline_style(
                'atl-styles',
                '.' . $class . '{--atl-heading-size:' . $heading_size . ';--atl-tag-size:' . $tag_size . ';}'
            );
            $added[$class] = true;
        }

        return $class;
    }

    // Get inline CSS (base styles only)
    private function get_inline_css() {
        return "
            .atl-jump-nav,
            .atl-letter-section {
                --atl-bar-offset: var(--wp-admin--admin-bar--height, 0px);
            }
            .atl-container {
                font-size: var(--atl-tag-size, 14px);
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                line-height: 1.5;
            }
            .atl-jump-nav {
                position: sticky;
                top: var(--atl-bar-offset);
                background: var(--atl-nav-bg, var(--wp--preset--color--base, #fff));
                padding: 15px 0;
                margin-bottom: 30px;
                border-bottom: 2px solid var(--atl-accent, #0073aa);
                z-index: 100;
            }
            .atl-jump-nav-inner {
                display: flex;
                flex-wrap: wrap;
                gap: 8px 12px;
                justify-content: center;
                align-items: center;
            }
            .atl-jump-link {
                display: inline-block;
                padding: 6px 10px;
                text-decoration: none;
                color: var(--atl-text, inherit);
                font-weight: bold;
                font-size: 14px;
                border-radius: 4px;
                transition: all 0.2s ease;
                min-width: 32px;
                text-align: center;
            }
            .atl-jump-link:hover {
                background: var(--atl-accent, #0073aa);
                color: var(--atl-accent-text, #fff);
                transform: translateY(-1px);
            }
            .atl-jump-link.active {
                background: var(--atl-accent, #0073aa);
                color: var(--atl-accent-text, #fff);
            }
            .atl-jump-link:focus-visible {
                outline: 2px solid var(--atl-accent, #0073aa);
                outline-offset: 2px;
            }
            .atl-letter-heading:focus {
                outline: none;
            }
            @media (prefers-reduced-motion: reduce) {
                .atl-jump-link {
                    transition: none;
                }
                .atl-jump-link:hover {
                    transform: none;
                }
            }
            .atl-jump-link.disabled {
                color: inherit;
                opacity: 0.35;
                cursor: not-allowed;
                pointer-events: none;
                font-weight: bold;
            }
            .atl-letter-section {
                margin-bottom: 25px;
                scroll-margin-top: calc(var(--atl-bar-offset) + 100px);
            }
            .atl-container .atl-letter-heading {
                font-size: var(--atl-heading-size, 24px);
                font-weight: bold;
                color: var(--atl-text, inherit);
                margin-bottom: 10px;
                padding-bottom: 5px;
                border-bottom: 2px solid var(--atl-accent, #0073aa);
            }
            .atl-tags-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 8px 15px;
                margin-bottom: 15px;
            }
            .atl-tag-item {
                line-height: 1.4;
            }
            .atl-tag-count {
                color: inherit;
                opacity: 0.7;
                font-size: 0.9em;
                margin-left: 4px;
            }
            .atl-no-tags {
                padding: 20px;
                background: #fff3cd;
                border-left: 4px solid #ffc107;
                color: #856404;
            }
            @media (max-width: 600px) {
                .atl-jump-nav,
                .atl-letter-section {
                    --atl-bar-offset: 0px;
                }
            }
            @media (max-width: 768px) {
                .atl-jump-nav {
                    padding: 10px 0;
                }
                .atl-jump-nav-inner {
                    gap: 6px 8px;
                }
                .atl-jump-link {
                    padding: 4px 8px;
                    font-size: 13px;
                    min-width: 28px;
                }
            }
        ";
    }

    // Render the tags list
    public function render_tags_list($atts) {
        $atts = shortcode_atts(array(
            'min_count' => 1,
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => true,
            'heading_size' => '24px',
            'tag_size' => '14px',
            'show_jump_nav' => true,
            'heading_level' => 2,
            'show_count' => true,
        ), $atts);

        $atts['min_count'] = absint($atts['min_count']);

        $heading_level = (int) ltrim(strtolower((string) $atts['heading_level']), 'h');
        if ($heading_level < 1 || $heading_level > 6) {
            $heading_level = 2;
        }
        $heading_tag = 'h' . $heading_level;

        $allowed_orderby = array('name', 'count', 'slug', 'term_id');
        if (!in_array($atts['orderby'], $allowed_orderby, true)) {
            $atts['orderby'] = 'name';
        }

        $atts['order'] = strtoupper($atts['order']);
        if (!in_array($atts['order'], array('ASC', 'DESC'), true)) {
            $atts['order'] = 'ASC';
        }

        $atts['heading_size'] = $this->sanitize_css_size($atts['heading_size'], '24px');
        $atts['tag_size'] = $this->sanitize_css_size($atts['tag_size'], '14px');

        $atts['hide_empty'] = filter_var($atts['hide_empty'], FILTER_VALIDATE_BOOLEAN);
        $atts['show_jump_nav'] = filter_var($atts['show_jump_nav'], FILTER_VALIDATE_BOOLEAN);
        $atts['show_count'] = filter_var($atts['show_count'], FILTER_VALIDATE_BOOLEAN);

        $tags = get_tags(array(
            'orderby' => $atts['orderby'],
            'order' => $atts['order'],
            'hide_empty' => $atts['hide_empty'],
        ));

        if (is_wp_error($tags) || !is_array($tags)) {
            $tags = array();
        }

        if ($atts['min_count'] > 1) {
            $tags = array_filter($tags, function($tag) use ($atts) {
                return $tag->count >= $atts['min_count'];
            });
        }

        if (empty($tags)) {
            // Visitors see nothing; editors get a hint
            if (!current_user_can('edit_posts')) {
                return '';
            }
            return '<div class="atl-no-tags">' . esc_html__('No tags found.', 'alphabetical-tags-list') . '</div>';
        }

        $grouped_tags = $this->group_tags_by_letter($tags);

        $show_jump_nav = $atts['show_jump_nav'];

        $this->enqueue_assets($show_jump_nav);
        $size_class = $this->add_size_rules($atts['heading_size'], $atts['tag_size']);

        $output = array();

        if ($show_jump_nav) {
            $output[] = $this->render_jump_navigation($grouped_tags);
        }

        $output[] = '<div class="atl-container ' . esc_attr($size_class) . '">';

        foreach ($grouped_tags as $letter => $letter_tags) {
            $letter_id = $this->get_letter_id($letter);
            $output[] = '<div class="atl-letter-section" id="' . esc_attr($letter_id) . '">';
            $output[] = '<' . $heading_tag . ' class="atl-letter-heading" tabindex="-1">' . esc_html($letter) . '</' . $heading_tag . '>';
            $output[] = '<div class="atl-tags-grid">';

            foreach ($letter_tags as $tag) {
                $output[] = '<div class="atl-tag-item">';
                $output[] = '<a href="' . esc_url(get_tag_link($tag->term_id)) . '" class="atl-tag-link">';
                $output[] = esc_html($tag->name);
                $output[] = '</a>';
                if ($atts['show_count']) {
                    $output[] = '<span class="atl-tag-count">(' . esc_html($tag->count) . ')</span>';
                }
                $output[] = '</div>';
            }

            $output[] = '</div>';
            $output[] = '</div>';
        }

        $output[] = '</div>';

        return implode('', $output);
    }

    // Render jump navigation
    private function render_jump_navigation($grouped_tags) {
        $all_letters = array_merge(
            array('0-9', '#'),
            range('A', 'Z')
        );

        $nav = array();
        $nav[] = '<nav class="atl-jump-nav" aria-label="' . esc_attr__('Jump to letter', 'alphabetical-tags-list') . '">';
        $nav[] = '<div class="atl-jump-nav-inner">';

        foreach ($all_letters as $letter) {
            $letter_id = $this->get_letter_id($letter);
            $is_available = isset($grouped_tags[$letter]);
            $class = $is_available ? 'atl-jump-link' : 'atl-jump-link disabled';

            if ($is_available) {
                $nav[] = '<a href="#' . esc_attr($letter_id) . '" class="' . $class . '" data-letter="' . esc_attr($letter) . '">' . esc_html($letter) . '</a>';
            } else {
                $nav[] = '<span class="' . $class . '" aria-disabled="true">' . esc_html($letter) . '</span>';
            }
        }

        $nav[] = '</div>';
        $nav[] = '</nav>';

        return implode('', $nav);
    }

    // Get sanitized ID for letter
    private function get_letter_id($letter) {
        if ($letter === '0-9') {
            return 'atl-numbers';
        } elseif ($letter === '#') {
            return 'atl-symbols';
        } else {
            return 'atl-letter-' . strtolower($letter);
        }
    }

    // Group tags by first letter
    private function group_tags_by_letter($tags) {
        $grouped = array();

        foreach ($tags as $tag) {
            $first_char = function_exists('mb_substr')
                ? mb_substr($tag->name, 0, 1, 'UTF-8')
                : substr($tag->name, 0, 1);
            $normalized_char = $this->normalize_character($first_char);
            $first_letter = function_exists('mb_substr')
                ? mb_strtoupper(mb_substr($normalized_char, 0, 1, 'UTF-8'), 'UTF-8')
                : strtoupper(substr($normalized_char, 0, 1));

            if (preg_match('/[A-Z]/i', $first_letter)) {
                $group_key = $first_letter;
            } elseif (is_numeric($first_letter)) {
                $group_key = '0-9';
            } else {
                $group_key = '#';
            }

            if (!isset($grouped[$group_key])) {
                $grouped[$group_key] = array();
            }

            $grouped[$group_key][] = $tag;
        }

        // Order: 0-9, then #, then A-Z (matches the jump navigation)
        $rank = function($key) {
            if ($key === '0-9') return 0;
            if ($key === '#') return 1;
            return 2;
        };
        uksort($grouped, function($a, $b) use ($rank) {
            $diff = $rank($a) - $rank($b);
            return $diff !== 0 ? $diff : strcmp($a, $b);
        });

        return $grouped;
    }

    /**
     * Normalize accented characters to ASCII equivalents
     * Multi-strategy approach for consistent cross-platform behavior
     */
	
    private function normalize_character($char) {
        static $cache = array();

        if (!isset($cache[$char])) {
            $cache[$char] = $this->compute_normalized_character($char);
        }

        return $cache[$char];
    }

    private function compute_normalized_character($char) {
        // Strategy 1: Try Normalizer class (most reliable, requires intl extension)
        if (class_exists('Normalizer')) {
            // Decompose character (é → e + ́)
            $normalized = Normalizer::normalize($char, Normalizer::FORM_D);
            // Remove combining diacritical marks
            $normalized = preg_replace('/\p{Mn}/u', '', $normalized);
            // If we got a clean ASCII result, use it
            if ($normalized && preg_match('/^[A-Za-z0-9]$/', $normalized)) {
                return $normalized;
            }
        }
        
        // Strategy 2: Manual character map (guaranteed consistent)
        static $char_map = array(
            // Uppercase - Basic Latin with diacritics
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Ā' => 'A', 'Ă' => 'A', 'Ą' => 'A',
            'Æ' => 'AE',
            'Ç' => 'C', 'Ć' => 'C', 'Ĉ' => 'C', 'Ċ' => 'C', 'Č' => 'C',
            'Ď' => 'D', 'Đ' => 'D',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ē' => 'E', 'Ĕ' => 'E', 'Ė' => 'E', 'Ę' => 'E', 'Ě' => 'E',
            'Ĝ' => 'G', 'Ğ' => 'G', 'Ġ' => 'G', 'Ģ' => 'G',
            'Ĥ' => 'H', 'Ħ' => 'H',
            'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ĩ' => 'I', 'Ī' => 'I', 'Ĭ' => 'I', 'Į' => 'I', 'İ' => 'I',
            'Ĵ' => 'J',
            'Ķ' => 'K',
            'Ĺ' => 'L', 'Ļ' => 'L', 'Ľ' => 'L', 'Ŀ' => 'L', 'Ł' => 'L',
            'Ñ' => 'N', 'Ń' => 'N', 'Ņ' => 'N', 'Ň' => 'N',
            'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ō' => 'O', 'Ŏ' => 'O', 'Ő' => 'O',
            'Œ' => 'OE',
            'Ŕ' => 'R', 'Ŗ' => 'R', 'Ř' => 'R',
            'Ś' => 'S', 'Ŝ' => 'S', 'Ş' => 'S', 'Š' => 'S',
            'ẞ' => 'SS', 'ß' => 'SS',
            'Ţ' => 'T', 'Ť' => 'T', 'Ŧ' => 'T',
            'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ũ' => 'U', 'Ū' => 'U', 'Ŭ' => 'U', 'Ů' => 'U', 'Ű' => 'U', 'Ų' => 'U',
            'Ŵ' => 'W',
            'Ý' => 'Y', 'Ÿ' => 'Y', 'Ŷ' => 'Y',
            'Ź' => 'Z', 'Ż' => 'Z', 'Ž' => 'Z',
            
            // Lowercase - Basic Latin with diacritics
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'ā' => 'a', 'ă' => 'a', 'ą' => 'a',
            'æ' => 'ae',
            'ç' => 'c', 'ć' => 'c', 'ĉ' => 'c', 'ċ' => 'c', 'č' => 'c',
            'ď' => 'd', 'đ' => 'd',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ē' => 'e', 'ĕ' => 'e', 'ė' => 'e', 'ę' => 'e', 'ě' => 'e',
            'ĝ' => 'g', 'ğ' => 'g', 'ġ' => 'g', 'ģ' => 'g',
            'ĥ' => 'h', 'ħ' => 'h',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ĩ' => 'i', 'ī' => 'i', 'ĭ' => 'i', 'į' => 'i', 'ı' => 'i',
            'ĵ' => 'j',
            'ķ' => 'k',
            'ĺ' => 'l', 'ļ' => 'l', 'ľ' => 'l', 'ŀ' => 'l', 'ł' => 'l',
            'ñ' => 'n', 'ń' => 'n', 'ņ' => 'n', 'ň' => 'n',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ō' => 'o', 'ŏ' => 'o', 'ő' => 'o',
            'œ' => 'oe',
            'ŕ' => 'r', 'ŗ' => 'r', 'ř' => 'r',
            'ś' => 's', 'ŝ' => 's', 'ş' => 's', 'š' => 's',
            'ţ' => 't', 'ť' => 't', 'ŧ' => 't',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ũ' => 'u', 'ū' => 'u', 'ŭ' => 'u', 'ů' => 'u', 'ű' => 'u', 'ų' => 'u',
            'ŵ' => 'w',
            'ý' => 'y', 'ÿ' => 'y', 'ŷ' => 'y',
            'ź' => 'z', 'ż' => 'z', 'ž' => 'z',
            
            // Cyrillic (basic)
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'E',
            'Ж' => 'ZH', 'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M',
            'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U',
            'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C', 'Ч' => 'CH', 'Ш' => 'SH', 'Щ' => 'SCH',
            'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'YU', 'Я' => 'YA',
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e',
            'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm',
            'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
            'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
            'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
            
            // Greek (basic)
            'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H',
            'Θ' => 'TH', 'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => 'X',
            'Ο' => 'O', 'Π' => 'P', 'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'PH',
            'Χ' => 'CH', 'Ψ' => 'PS', 'Ω' => 'O',
            'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h',
            'θ' => 'th', 'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => 'x',
            'ο' => 'o', 'π' => 'p', 'ρ' => 'r', 'σ' => 's', 'ς' => 's', 'τ' => 't', 'υ' => 'y',
            'φ' => 'ph', 'χ' => 'ch', 'ψ' => 'ps', 'ω' => 'o',
        );
        
        if (isset($char_map[$char])) {
            return $char_map[$char];
        }
        
        // Strategy 3: iconv as last resort (may be inconsistent)
        if (function_exists('iconv')) {
            $normalized = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $char);
            if ($normalized !== false && $normalized !== '' && $normalized !== '?') {
                // Clean up iconv artifacts
                $normalized = str_replace(array("'", '"', '`', '^', '~'), '', $normalized);
                if ($normalized && preg_match('/^[A-Za-z0-9]+$/', $normalized)) {
                    return $normalized;
                }
            }
        }
        
        // If all else fails, return original character
        return $char;
    }
	/**
	 * Sanitize CSS size value to prevent XSS
	 * Only allows numbers followed by px, em, rem, or %
	 * 
	 * @param string $value CSS size value to sanitize
	 * @param string $default Default value if validation fails
	 * @return string Sanitized CSS value
	 */
	private function sanitize_css_size( $value, $default ) {
		// Allow only safe CSS units: px, em, rem, %
		// Format: number (with optional decimal) + unit
		if ( preg_match( '/^[0-9]+(\.[0-9]+)?(px|em|rem|%)$/i', $value ) ) {
			return esc_attr( $value );
		}
		return $default;
	}
}

// Initialize the plugin
new Alphabetical_Tags_List();
