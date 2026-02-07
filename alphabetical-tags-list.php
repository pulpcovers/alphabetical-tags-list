<?php
/**
* Plugin Name: Alphabetical Tags List
* Plugin URI: https://github.com/pulpcovers/alphabetical-tags-list
* Description: Display all tags alphabetically grouped by first letter using shortcode [alphabetical_tags]
* Version: 1.3
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
    
    // Enqueue plugin styles
    public function enqueue_styles() {
        if (has_shortcode(get_post()->post_content ?? '', 'alphabetical_tags')) {
            wp_add_inline_style('wp-block-library', $this->get_inline_css());
        }
    }
    
    // Get inline CSS (base styles only)
    private function get_inline_css() {
        // Adjust top position based on admin bar (desktop only)
        $admin_bar_height = is_admin_bar_showing() ? '32px' : '0px';
        $scroll_margin = is_admin_bar_showing() ? '140px' : '100px';
        
        return "
            .atl-container {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                line-height: 1.5;
            }
            .atl-jump-nav {
                position: sticky;
                top: {$admin_bar_height};
                background: #fff;
                padding: 15px 0;
                margin-bottom: 30px;
                border-bottom: 2px solid #0073aa;
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
                color: #333;
                font-weight: bold;
                font-size: 14px;
                border-radius: 4px;
                transition: all 0.2s ease;
                min-width: 32px;
                text-align: center;
            }
            .atl-jump-link:hover {
                background: #0073aa;
                color: #fff;
                transform: translateY(-1px);
            }
            .atl-jump-link.active {
                background: #0073aa;
                color: #fff;
            }
            .atl-jump-link:focus {
                outline: none;
            }
            .atl-jump-link.disabled {
                color: #ccc;
                cursor: not-allowed;
                pointer-events: none;
                font-weight: bold;
            }
            .atl-letter-section {
                margin-bottom: 25px;
                scroll-margin-top: {$scroll_margin};
            }
            .atl-letter-heading {
                font-weight: bold;
                color: #333;
                margin-bottom: 10px;
                padding-bottom: 5px;
                border-bottom: 2px solid #0073aa;
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
                color: #666;
                font-size: 0.9em;
                margin-left: 4px;
            }
            .atl-no-tags {
                padding: 20px;
                background: #fff3cd;
                border-left: 4px solid #ffc107;
                color: #856404;
            }
            @media (max-width: 782px) {
                .atl-jump-nav {
                    top: 0;
                }
                .atl-letter-section {
                    scroll-margin-top: 100px;
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
        // Parse shortcode attributes
        $atts = shortcode_atts(array(
            'min_count' => 1,
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => true,
            'heading_size' => '24px',
            'tag_size' => '14px',
            'show_jump_nav' => true,
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
        
        // Sanitize size values
        $heading_size = esc_attr($atts['heading_size']);
        $tag_size = esc_attr($atts['tag_size']);
        $show_jump_nav = filter_var($atts['show_jump_nav'], FILTER_VALIDATE_BOOLEAN);
        
        // Build output using array
        $output = array();
        
        // Add jump navigation if enabled
        if ($show_jump_nav) {
            $output[] = $this->render_jump_navigation($grouped_tags);
        }
        
        $output[] = '<div class="atl-container" style="font-size: ' . $tag_size . ';">';
        
        foreach ($grouped_tags as $letter => $letter_tags) {
            $letter_id = $this->get_letter_id($letter);
            $output[] = '<div class="atl-letter-section" id="' . esc_attr($letter_id) . '">';
            $output[] = '<h2 class="atl-letter-heading" style="font-size: ' . $heading_size . ';">' . esc_html($letter) . '</h2>';
            $output[] = '<div class="atl-tags-grid">';
            
            foreach ($letter_tags as $tag) {
                $output[] = '<div class="atl-tag-item">';
                $output[] = '<a href="' . esc_url(get_tag_link($tag->term_id)) . '" class="atl-tag-link">';
                $output[] = esc_html($tag->name);
                $output[] = '</a>';
                $output[] = '<span class="atl-tag-count">(' . esc_html($tag->count) . ')</span>';
                $output[] = '</div>';
            }
            
            $output[] = '</div>';
            $output[] = '</div>';
        }
        
        $output[] = '</div>';
        
        // Add smooth scroll JavaScript
        if ($show_jump_nav) {
            $output[] = $this->render_jump_navigation_script();
        }
        
        return implode('', $output);
    }
    
    // Render jump navigation
    private function render_jump_navigation($grouped_tags) {
        $all_letters = array_merge(
            array('0-9', '#'),
            range('A', 'Z')
        );
        
        $available_letters = array_keys($grouped_tags);
        
        $nav = array();
        $nav[] = '<nav class="atl-jump-nav">';
        $nav[] = '<div class="atl-jump-nav-inner">';
        
        foreach ($all_letters as $letter) {
            $letter_id = $this->get_letter_id($letter);
            $is_available = in_array($letter, $available_letters);
            $class = $is_available ? 'atl-jump-link' : 'atl-jump-link disabled';
            
            if ($is_available) {
                $nav[] = '<a href="#' . esc_attr($letter_id) . '" class="' . $class . '" data-letter="' . esc_attr($letter) . '">' . esc_html($letter) . '</a>';
            } else {
                $nav[] = '<span class="' . $class . '">' . esc_html($letter) . '</span>';
            }
        }
        
        $nav[] = '</div>';
        $nav[] = '</nav>';
        
        return implode('', $nav);
    }
    
    // Render smooth scroll JavaScript
    private function render_jump_navigation_script() {
        return "
        <script>
        (function() {
            var isScrolling = false;
            var scrollTimeout;
            
            // Smooth scroll for jump links
            document.querySelectorAll('.atl-jump-link:not(.disabled)').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    var targetId = this.getAttribute('href').substring(1);
                    var targetElement = document.getElementById(targetId);
                    if (targetElement) {
                        isScrolling = true;
                        targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        
                        // Update active state immediately
                        document.querySelectorAll('.atl-jump-link').forEach(function(l) {
                            l.classList.remove('active');
                        });
                        this.classList.add('active');
                        
                        // Remove focus to prevent outline persistence
                        this.blur();
                        
                        // Reset isScrolling flag after scroll completes
                        setTimeout(function() {
                            isScrolling = false;
                        }, 1000);
                    }
                });
            });
            
            // Update active link based on scroll position
            function updateActiveLink() {
                if (isScrolling) return;
                
                var sections = document.querySelectorAll('.atl-letter-section');
                var navLinks = document.querySelectorAll('.atl-jump-link:not(.disabled)');
                
                var navHeight = document.querySelector('.atl-jump-nav')?.offsetHeight || 0;
                var adminBarHeight = 0;
                
                if (window.innerWidth > 782) {
                    adminBarHeight = document.querySelector('#wpadminbar')?.offsetHeight || 0;
                }
                
                var offset = navHeight + adminBarHeight + 10;
                var currentSection = null;
                
                sections.forEach(function(section) {
                    var rect = section.getBoundingClientRect();
                    if (rect.top <= offset && rect.bottom > offset) {
                        currentSection = section;
                    }
                });
                
                if (!currentSection) {
                    var closestSection = null;
                    var closestDistance = Infinity;
                    
                    sections.forEach(function(section) {
                        var rect = section.getBoundingClientRect();
                        if (rect.top < offset) {
                            var distance = offset - rect.top;
                            if (distance < closestDistance) {
                                closestDistance = distance;
                                closestSection = section;
                            }
                        }
                    });
                    
                    currentSection = closestSection;
                }
                
                if (currentSection) {
                    var id = currentSection.getAttribute('id');
                    navLinks.forEach(function(link) {
                        if (link.getAttribute('href') === '#' + id) {
                            if (!link.classList.contains('active')) {
                                navLinks.forEach(function(l) {
                                    l.classList.remove('active');
                                    l.blur();
                                });
                                link.classList.add('active');
                            }
                        }
                    });
                }
            }
            
            window.addEventListener('scroll', function() {
                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(updateActiveLink, 50);
            }, { passive: true });
            
            var resizeTimeout;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(updateActiveLink, 100);
            });
            
            updateActiveLink();
        })();
        </script>
        ";
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
            $first_char = mb_substr($tag->name, 0, 1, 'UTF-8');
            $normalized_char = $this->normalize_character($first_char);
            $first_letter = mb_strtoupper($normalized_char, 'UTF-8');
            
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
        
        // Sort by key with numbers and symbols first
        uksort($grouped, function($a, $b) {
            if ($a === '0-9' && $b !== '0-9') return -1;
            if ($b === '0-9' && $a !== '0-9') return 1;
            if ($a === '#' && $b !== '0-9') return -1;
            if ($b === '#' && $a !== '0-9') return 1;
            return strcmp($a, $b);
        });
        
        return $grouped;
    }
    
    /**
     * Normalize accented characters to ASCII equivalents
     * Multi-strategy approach for consistent cross-platform behavior
     */
    private function normalize_character($char) {
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
        $char_map = array(
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
}

// Initialize the plugin
new Alphabetical_Tags_List();
