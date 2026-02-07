<?php
/**
* Plugin Name: Alphabetical Tags List
* Plugin URI: https://github.com/pulpcovers/alphabetical-tags-list
* Description: Display all tags alphabetically grouped by first letter using shortcode [alphabetical_tags]
* Version: 1.2
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
            array('0-9', '#'), // Numbers and symbols first
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
                if (isScrolling) return; // Don't update during programmatic scroll
                
                var sections = document.querySelectorAll('.atl-letter-section');
                var navLinks = document.querySelectorAll('.atl-jump-link:not(.disabled)');
                
                // Get the offset for the sticky nav
                var navHeight = document.querySelector('.atl-jump-nav')?.offsetHeight || 0;
                var adminBarHeight = 0;
                
                // Only include admin bar height on desktop (>782px)
                if (window.innerWidth > 782) {
                    adminBarHeight = document.querySelector('#wpadminbar')?.offsetHeight || 0;
                }
                
                var offset = navHeight + adminBarHeight + 10;
                
                var currentSection = null;
                
                // Find which section is currently at the top of the viewport
                sections.forEach(function(section) {
                    var rect = section.getBoundingClientRect();
                    if (rect.top <= offset && rect.bottom > offset) {
                        currentSection = section;
                    }
                });
                
                // If no section at top, find the closest one above
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
                
                // Update active state and remove focus from all links
                if (currentSection) {
                    var id = currentSection.getAttribute('id');
                    navLinks.forEach(function(link) {
                        if (link.getAttribute('href') === '#' + id) {
                            if (!link.classList.contains('active')) {
                                navLinks.forEach(function(l) {
                                    l.classList.remove('active');
                                    l.blur(); // Remove focus from all links
                                });
                                link.classList.add('active');
                            }
                        }
                    });
                }
            }
            
            // Debounced scroll handler
            window.addEventListener('scroll', function() {
                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(updateActiveLink, 50);
            }, { passive: true });
            
            // Update on resize (in case crossing 782px breakpoint)
            var resizeTimeout;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(updateActiveLink, 100);
            });
            
            // Initial update
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
    
    // Normalize accented characters to ASCII equivalents
    private function normalize_character($char) {
        // Try using iconv for transliteration
        if (function_exists('iconv')) {
            $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $char);
            if ($normalized !== false && $normalized !== '') {
                return $normalized;
            }
        }
        
        // Fallback: Manual character map
        $char_map = array(
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A',
            'Ç' => 'C',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ñ' => 'N',
            'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O',
            'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ý' => 'Y',
            'ß' => 'S',
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a',
            'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ñ' => 'n',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ý' => 'y', 'ÿ' => 'y',
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
