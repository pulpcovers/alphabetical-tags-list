<?php
/**
 * Plugin Name: Alphabetical Tags List
 * Plugin URI: https://github.com/pulpcovers/alphabetical-tags-list
 * Description: Display all tags alphabetically grouped by first letter using shortcode [alphabetical_tags]
 * Version: 1.0
 * Author: PulpCovers
 * Author URI: https://pulpcovers.com
 * License: CC0 1.0 Universal
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Alphabetical_Tags_List {
    
    public function __construct() {
        add_shortcode('alphabetical_tags', array($this, 'render_tags_list'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
    }
    
    /**
     * Enqueue plugin styles
     */
    public function enqueue_styles() {
        if (has_shortcode(get_post()->post_content ?? '', 'alphabetical_tags')) {
            wp_add_inline_style('wp-block-library', $this->get_inline_css());
        }
    }
    
    /**
     * Get inline CSS
     */
    private function get_inline_css() {
        return "
            .atl-container {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                line-height: 1.6;
            }
            .atl-letter-section {
                margin-bottom: 30px;
            }
            .atl-letter-heading {
                font-size: 28px;
                font-weight: bold;
                color: #333;
                margin-bottom: 15px;
                padding-bottom: 8px;
                border-bottom: 3px solid #0073aa;
            }
            .atl-tags-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 10px;
                margin-bottom: 20px;
            }
            .atl-tag-item {
                padding: 8px 12px;
                background: #f7f7f7;
                border-radius: 4px;
                transition: background 0.2s;
            }
            .atl-tag-item:hover {
                background: #e8e8e8;
            }
            .atl-tag-link {
                text-decoration: none;
                color: #0073aa;
                font-weight: 500;
            }
            .atl-tag-link:hover {
                text-decoration: underline;
            }
            .atl-tag-count {
                color: #666;
                font-size: 0.9em;
                margin-left: 5px;
            }
            .atl-no-tags {
                padding: 20px;
                background: #fff3cd;
                border-left: 4px solid #ffc107;
                color: #856404;
            }
        ";
    }
    
    /**
     * Render the tags list
     */
    public function render_tags_list($atts) {
        // Parse shortcode attributes
        $atts = shortcode_atts(array(
            'min_count' => 1,
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => true,
        ), $atts);
        
        // Get all tags
        $tags = get_tags(array(
            'orderby' => $atts['orderby'],
            'order' => $atts['order'],
            'hide_empty' => filter_var($atts['hide_empty'], FILTER_VALIDATE_BOOLEAN),
        ));
        
        // Filter by minimum count if specified
        if ($atts['min_count'] > 1) {
            $tags = array_filter($tags, function($tag) use ($atts) {
                return $tag->count >= $atts['min_count'];
            });
        }
        
        if (empty($tags)) {
            return '<div class="atl-no-tags">No tags found.</div>';
        }
        
        // Group tags by first letter
        $grouped_tags = $this->group_tags_by_letter($tags);
        
        // Build output
        ob_start();
        ?>
        <div class="atl-container">
            <?php foreach ($grouped_tags as $letter => $letter_tags): ?>
                <div class="atl-letter-section">
                    <h2 class="atl-letter-heading"><?php echo esc_html($letter); ?></h2>
                    <div class="atl-tags-grid">
                        <?php foreach ($letter_tags as $tag): ?>
                            <div class="atl-tag-item">
                                <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>" 
                                   class="atl-tag-link">
                                    <?php echo esc_html($tag->name); ?>
                                </a>
                                <span class="atl-tag-count">(<?php echo esc_html($tag->count); ?>)</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Group tags by first letter
     */
    private function group_tags_by_letter($tags) {
        $grouped = array();
        
        foreach ($tags as $tag) {
            // Use mb_substr for proper UTF-8 character handling
            $first_char = mb_substr($tag->name, 0, 1, 'UTF-8');
            
            // Normalize accented characters to ASCII equivalents
            $normalized_char = $this->normalize_character($first_char);
            $first_letter = mb_strtoupper($normalized_char, 'UTF-8');
            
            // Check if it's a letter (A-Z)
            if (preg_match('/[A-Z]/i', $first_letter)) {
                $group_key = $first_letter;
            }
            // Check if it's a number (0-9)
            elseif (is_numeric($first_letter)) {
                $group_key = '0-9';
            }
            // Everything else (symbols)
            else {
                $group_key = '# Symbols';
            }
            
            if (!isset($grouped[$group_key])) {
                $grouped[$group_key] = array();
            }
            
            $grouped[$group_key][] = $tag;
        }
        
        // Sort by key with numbers and symbols first
        uksort($grouped, function($a, $b) {
            // Numbers first
            if ($a === '0-9' && $b !== '0-9') return -1;
            if ($b === '0-9' && $a !== '0-9') return 1;
            
            // Symbols second
            if ($a === '# Symbols' && $b !== '0-9') return -1;
            if ($b === '# Symbols' && $a !== '0-9') return 1;
            
            // Regular alphabetical sort for letters
            return strcmp($a, $b);
        });
        
        return $grouped;
    }
    
    /**
     * Normalize accented characters to ASCII equivalents
     */
    private function normalize_character($char) {
        // Try using iconv for transliteration (most comprehensive)
        if (function_exists('iconv')) {
            $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $char);
            if ($normalized !== false && $normalized !== '') {
                return $normalized;
            }
        }
        
        // Fallback: Manual character map for common accents
        $char_map = array(
            // Uppercase
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A',
            'Ç' => 'C',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ñ' => 'N',
            'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O',
            'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ý' => 'Y',
            'ß' => 'S',
            // Lowercase
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a',
            'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ñ' => 'n',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ý' => 'y', 'ÿ' => 'y',
            // Extended Latin
            'Ā' => 'A', 'ā' => 'a', 'Ă' => 'A', 'ă' => 'a', 'Ą' => 'A', 'ą' => 'a',
            'Ć' => 'C', 'ć' => 'c', 'Ĉ' => 'C', 'ĉ' => 'c', 'Ċ' => 'C', 'ċ' => 'c', 'Č' => 'C', 'č' => 'c',
            'Ď' => 'D', 'ď' => 'd', 'Đ' => 'D', 'đ' => 'd',
            'Ē' => 'E', 'ē' => 'e', 'Ĕ' => 'E', 'ĕ' => 'e', 'Ė' => 'E', 'ė' => 'e', 'Ę' => 'E', 'ę' => 'e', 'Ě' => 'E', 'ě' => 'e',
            'Ĝ' => 'G', 'ĝ' => 'g', 'Ğ' => 'G', 'ğ' => 'g', 'Ġ' => 'G', 'ġ' => 'g', 'Ģ' => 'G', 'ģ' => 'g',
            'Ĥ' => 'H', 'ĥ' => 'h', 'Ħ' => 'H', 'ħ' => 'h',
            'Ĩ' => 'I', 'ĩ' => 'i', 'Ī' => 'I', 'ī' => 'i', 'Ĭ' => 'I', 'ĭ' => 'i', 'Į' => 'I', 'į' => 'i', 'İ' => 'I', 'ı' => 'i',
            'Ĵ' => 'J', 'ĵ' => 'j',
            'Ķ' => 'K', 'ķ' => 'k',
            'Ĺ' => 'L', 'ĺ' => 'l', 'Ļ' => 'L', 'ļ' => 'l', 'Ľ' => 'L', 'ľ' => 'l', 'Ŀ' => 'L', 'ŀ' => 'l', 'Ł' => 'L', 'ł' => 'l',
            'Ń' => 'N', 'ń' => 'n', 'Ņ' => 'N', 'ņ' => 'n', 'Ň' => 'N', 'ň' => 'n',
            'Ō' => 'O', 'ō' => 'o', 'Ŏ' => 'O', 'ŏ' => 'o', 'Ő' => 'O', 'ő' => 'o', 'Œ' => 'O', 'œ' => 'o',
            'Ŕ' => 'R', 'ŕ' => 'r', 'Ŗ' => 'R', 'ŗ' => 'r', 'Ř' => 'R', 'ř' => 'r',
            'Ś' => 'S', 'ś' => 's', 'Ŝ' => 'S', 'ŝ' => 's', 'Ş' => 'S', 'ş' => 's', 'Š' => 'S', 'š' => 's',
            'Ţ' => 'T', 'ţ' => 't', 'Ť' => 'T', 'ť' => 't', 'Ŧ' => 'T', 'ŧ' => 't',
            'Ũ' => 'U', 'ũ' => 'u', 'Ū' => 'U', 'ū' => 'u', 'Ŭ' => 'U', 'ŭ' => 'u', 'Ů' => 'U', 'ů' => 'u', 'Ű' => 'U', 'ű' => 'u', 'Ų' => 'U', 'ų' => 'u',
            'Ŵ' => 'W', 'ŵ' => 'w',
            'Ŷ' => 'Y', 'ŷ' => 'y', 'Ÿ' => 'Y',
            'Ź' => 'Z', 'ź' => 'z', 'Ż' => 'Z', 'ż' => 'z', 'Ž' => 'Z', 'ž' => 'z',
        );
        
        return isset($char_map[$char]) ? $char_map[$char] : $char;
    }
}

// Initialize the plugin
new Alphabetical_Tags_List();
