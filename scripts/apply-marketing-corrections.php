<?php
/**
 * Apply the verified July 2026 marketing corrections to the local SABER site.
 *
 * Safety: requires SABER_APPLY_MARKETING_CORRECTIONS=1 and creates revisions.
 * Run inside the WordPress container with WP-CLI.
 */

if (!defined('ABSPATH')) {
    exit(1);
}

if (getenv('SABER_APPLY_MARKETING_CORRECTIONS') !== '1') {
    WP_CLI::error('Set SABER_APPLY_MARKETING_CORRECTIONS=1 to apply these changes.');
}

$actions = [];
$notes = [];
$modified_pages = [];

function saber_expected_page($id, $slug) {
    $page = get_post($id);
    $valid_slug = $page && (
        $page->post_name === $slug
        || ($page->post_status === 'trash' && $page->post_name === $slug . '__trashed')
    );
    if (!$page || $page->post_type !== 'page' || !$valid_slug) {
        WP_CLI::error("Expected page {$id} ({$slug}) was not found. Nothing was changed.");
    }
    return $page;
}

$expected_pages = [
    36 => 'general',
    859 => 'saber_college_leadership',
    861 => 'campus-location',
    863 => 'explore-saber-college-gallery',
    877 => 'nursing-admissions',
    887 => 'saber-college-accreditation',
    896 => 'academic-affairs',
    900 => 'career-services',
    911 => 'net-price-calculator',
    915 => 'graduation-requirements',
    917 => 'gainful-employment-programs',
    919 => 'saber-college-faculty',
    921 => 'saber-college-distance-education',
    1238 => 'contact',
    1639 => 'campus-location',
    1643 => 'vesl',
    1649 => 'medical-assistant-program',
];

foreach ($expected_pages as $id => $slug) {
    saber_expected_page($id, $slug);
}

$snapshot = [
    'created_utc' => gmdate('c'),
    'pages' => [],
    'posts' => [],
    'terms' => [],
    'redirections' => [],
];

foreach (array_keys($expected_pages) as $id) {
    $snapshot['pages'][$id] = [
        'post' => (array) get_post($id),
        'meta' => get_post_meta($id),
    ];
}

foreach ([989, 1034, 1037, 1040, 1087, 1108, 1111, 1141, 1146, 1155, 1232] as $id) {
    $post = get_post($id);
    if ($post) {
        $snapshot['posts'][$id] = [
            'post' => (array) $post,
            'meta' => get_post_meta($id),
            'terms' => wp_get_object_terms($id, ['category', 'post_tag'], ['fields' => 'all_with_object_id']),
        ];
    }
}

foreach ([15, 16, 25, 26, 29, 49, 64, 67, 70, 71, 72, 108, 128, 129] as $term_id) {
    foreach (['category', 'post_tag'] as $taxonomy) {
        $term = get_term($term_id, $taxonomy);
        if ($term && !is_wp_error($term)) {
            $snapshot['terms'][] = (array) $term;
        }
    }
}

global $wpdb;
$redirect_table = $wpdb->prefix . 'rank_math_redirections';
if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $redirect_table)) === $redirect_table) {
    $snapshot['redirections'] = $wpdb->get_results(
        "SELECT * FROM {$redirect_table} WHERE LOWER(url_to) LIKE '%/lp/vesl/%' OR LOWER(url_to) LIKE '%/lp/medical-assistant-program/%' OR LOWER(url_to) LIKE '%/category/vesl/%' OR LOWER(url_to) LIKE '%/category/medical-assisting/%'",
        ARRAY_A
    );
}

$stamp = gmdate('Ymd-His');
$backup = '/tmp/saber-marketing-corrections-before-' . $stamp . '.json';
$backup_json = wp_json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if (false === file_put_contents($backup, $backup_json)) {
    WP_CLI::error('Could not create the application snapshot. Nothing was changed.');
}
file_put_contents('/tmp/saber-marketing-corrections-before-latest.json', $backup_json);
WP_CLI::line('BACKUP=' . $backup);

function saber_find_element(&$nodes, $id, &$found = null) {
    foreach ($nodes as &$node) {
        if (($node['id'] ?? '') === $id) {
            $found =& $node;
            return true;
        }
        if (!empty($node['elements']) && saber_find_element($node['elements'], $id, $found)) {
            return true;
        }
    }
    return false;
}

function saber_set_all_widget_settings(&$data, $id, $setting, $value) {
    $updated = 0;
    foreach ($data as &$node) {
        if (($node['id'] ?? '') === $id) {
            $node['settings'][$setting] = $value;
            $updated++;
        }
        if (!empty($node['elements'])) {
            $updated += saber_set_all_widget_settings($node['elements'], $id, $setting, $value);
        }
    }
    return $updated;
}

function saber_set_widget_setting(&$data, $id, $setting, $value) {
    if (saber_set_all_widget_settings($data, $id, $setting, $value) > 0) {
        return true;
    }
    WP_CLI::error("Elementor element {$id} was not found.");
}

function saber_replace_widget_setting(&$nodes, $id, $setting, $search, $replace) {
    foreach ($nodes as &$node) {
        if (($node['id'] ?? '') === $id) {
            if (!isset($node['settings'][$setting]) || !is_string($node['settings'][$setting])) {
                WP_CLI::error("Elementor setting {$id}:{$setting} is unavailable.");
            }
            $node['settings'][$setting] = str_replace($search, $replace, $node['settings'][$setting]);
            return true;
        }
        if (!empty($node['elements']) && saber_replace_widget_setting($node['elements'], $id, $setting, $search, $replace)) {
            return true;
        }
    }
    return false;
}

function saber_remove_element(&$nodes, $id) {
    $removed = 0;
    foreach ($nodes as $index => &$node) {
        if (($node['id'] ?? '') === $id) {
            unset($nodes[$index]);
            $removed++;
            continue;
        }
        if (!empty($node['elements'])) {
            $removed += saber_remove_element($node['elements'], $id);
        }
    }
    $nodes = array_values($nodes);
    return $removed;
}

function saber_set_atomic_style_prop(&$nodes, $id, $breakpoint, $prop, $value) {
    foreach ($nodes as &$node) {
        if (($node['id'] ?? '') === $id) {
            if (empty($node['styles'])) {
                return false;
            }
            foreach ($node['styles'] as &$style) {
                if (empty($style['variants'])) {
                    continue;
                }
                foreach ($style['variants'] as &$variant) {
                    if (($variant['meta']['breakpoint'] ?? '') === $breakpoint) {
                        $variant['props'][$prop] = $value;
                        return true;
                    }
                }
            }
            return false;
        }
        if (!empty($node['elements']) && saber_set_atomic_style_prop($node['elements'], $id, $breakpoint, $prop, $value)) {
            return true;
        }
    }
    return false;
}

function saber_prepend_child(&$nodes, $parent_id, $child) {
    foreach ($nodes as &$node) {
        if (($node['id'] ?? '') === $parent_id) {
            foreach (($node['elements'] ?? []) as $existing) {
                if (($existing['id'] ?? '') === ($child['id'] ?? '')) {
                    return true;
                }
            }
            array_unshift($node['elements'], $child);
            return true;
        }
        if (!empty($node['elements']) && saber_prepend_child($node['elements'], $parent_id, $child)) {
            return true;
        }
    }
    return false;
}

function saber_insert_child_after(&$nodes, $parent_id, $after_id, $child) {
    foreach ($nodes as &$node) {
        if (($node['id'] ?? '') === $parent_id) {
            foreach (($node['elements'] ?? []) as $existing) {
                if (($existing['id'] ?? '') === ($child['id'] ?? '')) {
                    return true;
                }
            }
            foreach ($node['elements'] as $index => $existing) {
                if (($existing['id'] ?? '') === $after_id) {
                    array_splice($node['elements'], $index + 1, 0, [$child]);
                    return true;
                }
            }
            return false;
        }
        if (!empty($node['elements']) && saber_insert_child_after($node['elements'], $parent_id, $after_id, $child)) {
            return true;
        }
    }
    return false;
}

function saber_has_element($data, $id) {
    $found = null;
    return saber_find_element($data, $id, $found);
}

function saber_icon_items($texts, $prefix) {
    $items = [];
    foreach ($texts as $index => $text) {
        $items[] = [
            'text' => $text,
            'selected_icon' => ['value' => 'fas fa-angle-right', 'library' => 'fa-solid'],
            '_id' => substr(md5($prefix . '-' . $index . '-' . $text), 0, 7),
        ];
    }
    return $items;
}

function saber_typed($type, $value) {
    return ['$$type' => $type, 'value' => $value];
}

function saber_size($value, $unit = 'px') {
    return saber_typed('size', ['size' => $value, 'unit' => $unit]);
}

function saber_pad($top, $right, $bottom, $left) {
    return saber_typed('dimensions', [
        'block-start' => saber_size($top),
        'inline-end' => saber_size($right),
        'block-end' => saber_size($bottom),
        'inline-start' => saber_size($left),
    ]);
}

function saber_background($color) {
    return saber_typed('background', ['color' => saber_typed('color', $color)]);
}

function saber_atomic_container($id, $class, $children, $props = [], $mobile = []) {
    $base = [
        'display' => saber_typed('string', 'flex'),
        'flex-direction' => saber_typed('string', 'column'),
        'min-width' => saber_size(0),
    ];
    $variants = [[
        'meta' => ['breakpoint' => 'desktop', 'state' => null],
        'props' => array_merge($base, $props),
        'custom_css' => null,
    ]];
    if ($mobile) {
        $variants[] = [
            'meta' => ['breakpoint' => 'mobile', 'state' => null],
            'props' => $mobile,
            'custom_css' => null,
        ];
    }
    return [
        'id' => $id,
        'elType' => 'e-flexbox',
        'settings' => ['classes' => saber_typed('classes', [$class])],
        'elements' => $children,
        'isInner' => false,
        'styles' => [$class => [
            'id' => $class,
            'label' => $class,
            'type' => 'class',
            'variants' => $variants,
        ]],
        'interactions' => [],
        'editor_settings' => [],
        'version' => '0.0',
    ];
}

function saber_heading_widget($id, $title, $level = 'h2', $color = '#001a39') {
    return [
        'id' => $id,
        'elType' => 'widget',
        'widgetType' => 'heading',
        'elements' => [],
        'settings' => [
            'title' => $title,
            'header_size' => $level,
            'title_color' => $color,
            'typography_typography' => 'custom',
            'typography_font_family' => 'Nunito',
            'typography_font_weight' => '800',
            'typography_font_size' => ['unit' => 'px', 'size' => 38, 'sizes' => []],
            'typography_font_size_mobile' => ['unit' => 'px', 'size' => 30, 'sizes' => []],
            'typography_line_height' => ['unit' => 'em', 'size' => 1.1, 'sizes' => []],
        ],
    ];
}

function saber_text_widget($id, $html, $color = '#29435f') {
    return [
        'id' => $id,
        'elType' => 'widget',
        'widgetType' => 'text-editor',
        'elements' => [],
        'settings' => [
            'editor' => $html,
            'text_color' => $color,
            'link_color' => '#005fbd',
            'link_hover_color' => '#003f7d',
            'typography_typography' => 'custom',
            'typography_font_family' => 'Nunito',
            'typography_font_weight' => '500',
            'typography_font_size' => ['unit' => 'px', 'size' => 17, 'sizes' => []],
            'typography_font_size_mobile' => ['unit' => 'px', 'size' => 16, 'sizes' => []],
            'typography_line_height' => ['unit' => 'em', 'size' => 1.68, 'sizes' => []],
        ],
    ];
}

function saber_icon_list_widget($id, $texts, $prefix, $color = '#29435f') {
    return [
        'id' => $id,
        'elType' => 'widget',
        'widgetType' => 'icon-list',
        'elements' => [],
        'settings' => [
            'icon_list' => saber_icon_items($texts, $prefix),
            'icon_color' => '#25377b',
            'text_color' => $color,
            'icon_size' => ['unit' => 'px', 'size' => 18, 'sizes' => []],
            'text_indent' => ['unit' => 'px', 'size' => 10, 'sizes' => []],
            'space_between' => ['unit' => 'px', 'size' => 10, 'sizes' => []],
            'text_typography_typography' => 'custom',
            'text_typography_font_family' => 'Nunito',
            'text_typography_font_weight' => '600',
            'text_typography_font_size' => ['unit' => 'px', 'size' => 17, 'sizes' => []],
            'text_typography_line_height' => ['unit' => 'em', 'size' => 1.5, 'sizes' => []],
        ],
    ];
}

function saber_image_widget($id, $attachment_id, $alt, $url = '') {
    $settings = [
        'image' => [
            'id' => $attachment_id,
            'url' => wp_get_attachment_url($attachment_id),
            'alt' => $alt,
            'source' => 'library',
        ],
        'image_size' => 'full',
        'width' => ['unit' => '%', 'size' => 100, 'sizes' => []],
    ];
    if ($url !== '') {
        $settings['link_to'] = 'custom';
        $settings['link'] = ['url' => $url, 'is_external' => 'on', 'nofollow' => '', 'custom_attributes' => ''];
    }
    return [
        'id' => $id,
        'elType' => 'widget',
        'widgetType' => 'image',
        'elements' => [],
        'settings' => $settings,
    ];
}

function saber_plain_post_content($data) {
    $parts = [];
    $walk = function ($nodes) use (&$walk, &$parts) {
        foreach ($nodes as $node) {
            if (($node['elType'] ?? '') === 'widget') {
                $settings = $node['settings'] ?? [];
                $type = $node['widgetType'] ?? '';
                if ($type === 'shortcode' && !empty($settings['shortcode'])) {
                    $parts[] = $settings['shortcode'];
                } elseif ($type === 'heading' && isset($settings['title'])) {
                    $tag = strtolower($settings['header_size'] ?? 'h2');
                    if (!preg_match('/^h[1-6]$/', $tag)) {
                        $tag = 'h2';
                    }
                    $parts[] = '<' . $tag . '>' . esc_html(wp_strip_all_tags($settings['title'])) . '</' . $tag . '>';
                } elseif ($type === 'text-editor' && isset($settings['editor'])) {
                    $parts[] = wp_kses_post($settings['editor']);
                } elseif ($type === 'icon-list' && !empty($settings['icon_list'])) {
                    $items = [];
                    foreach ($settings['icon_list'] as $item) {
                        if (!empty($item['text'])) {
                            $items[] = '<li>' . wp_kses_post($item['text']) . '</li>';
                        }
                    }
                    if ($items) {
                        $parts[] = '<ul>' . implode('', $items) . '</ul>';
                    }
                }
            }
            if (!empty($node['elements'])) {
                $walk($node['elements']);
            }
        }
    };
    $walk($data);
    return implode("\n", $parts);
}

function saber_save_elementor_page($post_id, $data) {
    wp_save_post_revision($post_id);
    update_post_meta($post_id, '_elementor_data', wp_slash(wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)));
    update_post_meta($post_id, '_elementor_edit_mode', 'builder');
    if (defined('ELEMENTOR_VERSION')) {
        update_post_meta($post_id, '_elementor_version', ELEMENTOR_VERSION);
    }
    delete_post_meta($post_id, '_elementor_element_cache');
    wp_update_post([
        'ID' => $post_id,
        'post_content' => saber_plain_post_content($data),
    ]);
    if (class_exists('Elementor\\Core\\Files\\CSS\\Post')) {
        do_action('elementor/atomic-widgets/styles/clear', ['local', $post_id]);
        Elementor\Core\Files\CSS\Post::create($post_id)->update();
    }
    clean_post_cache($post_id);
}

function saber_edit_elementor_page($post_id, $callback) {
    $raw = get_post_meta($post_id, '_elementor_data', true);
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        WP_CLI::error("Page {$post_id} has invalid Elementor data.");
    }
    $callback($data);
    saber_save_elementor_page($post_id, $data);
}

function saber_update_post_content($post_id, $callback) {
    $post = get_post($post_id);
    if (!$post) {
        WP_CLI::error("Post {$post_id} does not exist.");
    }
    $updated = $callback($post->post_content);
    if ($updated === $post->post_content) {
        return false;
    }
    wp_save_post_revision($post_id);
    wp_update_post(['ID' => $post_id, 'post_content' => $updated]);
    clean_post_cache($post_id);
    return true;
}

// General Overview: retain the current copy while removing retired-program references.
saber_edit_elementor_page(36, function (&$data) {
    saber_set_widget_setting($data, '6674787', 'editor', "The SABER College General Information page offers students a clear overview of academic resources, tuition support, campus policies, and student success tools designed to support every step of their educational journey. Our programs—including the Professional Nursing Program A.S. and Physical Therapist Assistant Program A.S.—are designed to give you the skills employers seek. With guidance from experienced faculty, you'll gain the knowledge and hands-on experience to enter the job market confidently. Look around and see how SABER College can help you reach your goals!");
    saber_set_widget_setting($data, '5a9b428', 'editor', "Get a glimpse of general student life at SABER College! Our gallery highlights our vibrant campus in Miami, FL, including fully equipped classrooms and labs in the Professional Nursing and Physical Therapist Assistant programs. You'll also see snapshots of campus life. Please tour our photos to get a feel for the SABER College community and our state-of-the-art facilities.");
    saber_set_widget_setting($data, '0a406ff', 'editor', "SABER College’s distance education format is available for the first semester of the Nursing and Physical Therapist Assistant programs. This instructor-led online modality mirrors the academic rigor of our in-person courses, offering scheduled lessons, structured assignments, and direct faculty support.");
});
$modified_pages[] = 36;
$actions[] = 'General Overview: retired-program references removed.';

$nln_logo_ids = get_posts([
    'post_type' => 'attachment',
    'post_status' => 'inherit',
    'posts_per_page' => 1,
    'fields' => 'ids',
    'meta_key' => '_saber_marketing_asset',
    'meta_value' => 'nln-cnea-seal-400',
]);
if (!$nln_logo_ids || !wp_get_attachment_url($nln_logo_ids[0])) {
    WP_CLI::error('The official NLN CNEA seal is not available in the Media Library.');
}
$nln_logo_id = (int) $nln_logo_ids[0];

// Leadership: replace the staff lists exactly as supplied and add Board, accreditation and FAQ sections.
saber_edit_elementor_page(859, function (&$data) use ($nln_logo_id) {
    saber_set_widget_setting($data, '399fbed', 'icon_list', saber_icon_items([
        'Josefina Bonet, MPA — Chief Executive Officer',
        'Alex Montorro, MBA, CPA — Chief Financial Officer',
        'Maria Palacios — Chief Financial Assistance Officer',
    ], 'leadership-executive'));
    saber_set_widget_setting($data, '20ad238', 'icon_list', saber_icon_items([
        'Amarilis Somoza — Dean of Academic Affairs',
        'Karen Flynn, PT, MS — Director of the Physical Therapist Assistant Program',
        'Pavel S. Pugh Alvarez, DNP, APRN, PMHNP-C, FNP-C, CNE, FEP — Director of Nursing and Health Sciences',
        'Gaspara Bardritch, PT, MSPT, DPT — Academic Coordinator of Clinical Education (ACCE)',
        'Alexandra Lis — Director of Distance Education',
        'Lucy Alvarez — Senior Director of Admissions',
        'Claudio Bettinelli — Registrar',
        'Dulce Estevez — Assistant CEO / Personnel Manager / Accreditation Coordinator',
        'Marivi Rodriguez — Career Services',
        'Carmen Ruiz — Librarian',
    ], 'leadership-education'));
    saber_set_widget_setting($data, '1410108', 'editor', 'In addition to academic and operational leadership, SABER College proudly supports students from diverse cultural and linguistic backgrounds. While all healthcare programs are taught in English, the college offers bilingual support services across admissions, financial aid, and academic advising to ensure that students and their families have full access to the resources they need. Information is available in both English and Spanish.');
    saber_set_widget_setting($data, 'dcc10d1', 'editor', 'To learn more about SABER College or to explore our accredited programs in Professional Nursing and Physical Therapist Assistant, we invite you to contact us or call our admissions team at (305) 443-9170 to begin your journey.');

    if (!saber_has_element($data, 'mktbrdsec')) {
        $board = saber_atomic_container('mktbrdsec', 'e-leadership-board', [
            saber_atomic_container('mktbrdin', 'e-leadership-board-inner', [
                saber_heading_widget('mktbrdh', 'Board of Directors'),
                saber_icon_list_widget('mktbrdl', [
                    'Raul Rodriguez — Chairman',
                    'Robert Bonds — Treasurer',
                    'Hilda Portilla — Secretary',
                    'Maurice Habif — Director',
                ], 'board'),
            ], [
                'width' => saber_size(100, '%'),
                'max-width' => saber_size(1200),
                'padding' => saber_pad(72, 24, 76, 24),
                'gap' => saber_size(26),
            ], ['padding' => saber_pad(48, 20, 52, 20)]),
        ], [
            'width' => saber_size(100, '%'),
            'align-items' => saber_typed('string', 'center'),
            'background' => saber_background('#ffffff'),
        ]);

        $logo_cards = [
            saber_atomic_container('mktnlnlc', 'e-leadership-accreditation-nln', [
                saber_image_widget('mktnlnli', $nln_logo_id, 'NLN CNEA accreditation seal', 'https://cnea.nln.org/'),
            ], ['width' => saber_size(23, '%'), 'padding' => saber_pad(24, 24, 24, 24), 'background' => saber_background('#ffffff'), 'border-radius' => saber_size(18)], ['width' => saber_size(100, '%')]),
            saber_atomic_container('mktcoec', 'e-leadership-accreditation-coe', [
                saber_image_widget('mktcoei', 458, 'Council on Occupational Education', 'https://www.council.org/'),
            ], ['width' => saber_size(23, '%'), 'padding' => saber_pad(24, 24, 24, 24), 'background' => saber_background('#ffffff'), 'border-radius' => saber_size(18)], ['width' => saber_size(100, '%')]),
            saber_atomic_container('mktciec', 'e-leadership-accreditation-cie', [
                saber_image_widget('mktciei', 691, 'Florida Commission for Independent Education', 'https://www.fldoe.org/policy/cie/'),
            ], ['width' => saber_size(23, '%'), 'padding' => saber_pad(24, 24, 24, 24), 'background' => saber_background('#ffffff'), 'border-radius' => saber_size(18)], ['width' => saber_size(100, '%')]),
            saber_atomic_container('mktcaptc', 'e-leadership-accreditation-capte', [
                saber_image_widget('mktcapti', 84, 'Commission on Accreditation in Physical Therapy Education', 'https://www.capteonline.org/'),
            ], ['width' => saber_size(23, '%'), 'padding' => saber_pad(24, 24, 24, 24), 'background' => saber_background('#ffffff'), 'border-radius' => saber_size(18)], ['width' => saber_size(100, '%')]),
        ];

        $accreditation = saber_atomic_container('mktaccsec', 'e-leadership-accreditation', [
            saber_atomic_container('mktaccin', 'e-leadership-accreditation-inner', [
                saber_heading_widget('mktacch', 'Accreditation', 'h2', '#ffffff'),
                saber_atomic_container('mktacclg', 'e-leadership-accreditation-logos', $logo_cards, [
                    'width' => saber_size(100, '%'),
                    'flex-direction' => saber_typed('string', 'row'),
                    'justify-content' => saber_typed('string', 'space-between'),
                    'gap' => saber_size(22),
                ], ['flex-direction' => saber_typed('string', 'column')]),
            ], [
                'width' => saber_size(100, '%'),
                'max-width' => saber_size(1200),
                'padding' => saber_pad(72, 24, 76, 24),
                'gap' => saber_size(30),
            ], ['padding' => saber_pad(48, 20, 52, 20)]),
        ], [
            'width' => saber_size(100, '%'),
            'align-items' => saber_typed('string', 'center'),
            'background' => saber_background('#082b55'),
        ]);

        $faq_html = '<p><strong>Q: Is SABER College accredited?</strong></p><p>A: Yes. SABER College is accredited by the Council on Occupational Education (COE) and has been licensed by the Florida Department of Education’s Commission for Independent Education (CIE) since 1992 (License #1400). SABER College is a private, non-profit 501(c)(3) institution.</p>'
            . '<p><strong>Q: Is the Professional Nursing Program accredited?</strong></p><p>A: Yes. The SABER College Associate Degree in the Nursing Program holds accreditation from the National League for Nursing Commission for Nursing Education Accreditation (NLN CNEA), 2600 Virginia Avenue, NW, Washington, DC 20037; (202) 909-2487. Graduates are eligible to sit for the NCLEX-RN.</p>'
            . '<p><strong>Q: Is the Physical Therapist Assistant (PTA) Program accredited?</strong></p><p>A: Yes. The PTA Program is accredited by the Commission on Accreditation in Physical Therapy Education (CAPTE), 3030 Potomac Ave., Suite 100, Alexandria, VA 22305-3085; (703) 706-3245; accreditation@apta.org; www.capteonline.org. Graduates are eligible to sit for the National Physical Therapy Examination (NPTE) for PTAs.</p>'
            . '<p><strong>Q: Why does accreditation matter?</strong></p><p>A: Accreditation means SABER College meets recognized national and state standards for quality education. It supports eligibility for federal financial aid (for those who qualify), the ability to sit for licensure examinations, and the value of your degree with employers.</p>'
            . '<p><strong>Q: How can I verify SABER College’s accreditation?</strong></p><p>A: You can confirm our accreditation directly with each agency: COE (www.council.org), CIE (www.fldoe.org/policy/cie), CAPTE (www.capteonline.org), and NLN CNEA (cnea.nln.org).</p>';
        $faq = saber_atomic_container('mktfaqsec', 'e-leadership-faq', [
            saber_atomic_container('mktfaqin', 'e-leadership-faq-inner', [
                saber_heading_widget('mktfaqh', 'Accreditation Frequently Asked Questions'),
                saber_text_widget('mktfaqt', $faq_html),
            ], [
                'width' => saber_size(100, '%'),
                'max-width' => saber_size(1000),
                'padding' => saber_pad(72, 24, 76, 24),
                'gap' => saber_size(28),
            ], ['padding' => saber_pad(48, 20, 52, 20)]),
        ], [
            'width' => saber_size(100, '%'),
            'align-items' => saber_typed('string', 'center'),
            'background' => saber_background('#f3f9ff'),
        ]);

        $data[] = $board;
        $data[] = $accreditation;
        $data[] = $faq;
    }

    if (!saber_has_element($data, 'mktnlnlc')) {
        $nln_card = saber_atomic_container('mktnlnlc', 'e-leadership-accreditation-nln', [
            saber_image_widget('mktnlnli', $nln_logo_id, 'NLN CNEA accreditation seal', 'https://cnea.nln.org/'),
        ], ['width' => saber_size(23, '%'), 'padding' => saber_pad(24, 24, 24, 24), 'background' => saber_background('#ffffff'), 'border-radius' => saber_size(18)], ['width' => saber_size(100, '%')]);
        if (!saber_prepend_child($data, 'mktacclg', $nln_card)) {
            WP_CLI::error('Leadership accreditation logo container was not found.');
        }
    }
    foreach (['mktnlnlc', 'mktcoec', 'mktciec', 'mktcaptc'] as $card_id) {
        if (!saber_set_atomic_style_prop($data, $card_id, 'desktop', 'width', saber_size(23, '%'))) {
            WP_CLI::error("Leadership accreditation card {$card_id} style was not found.");
        }
    }
});
$modified_pages[] = 859;
$actions[] = 'Leadership: staff, Board of Directors, four accreditation marks and supplied FAQ added.';

// Campus Location: update Admissions and remove the retired VESL card.
saber_edit_elementor_page(861, function (&$data) {
    saber_set_widget_setting($data, '4a46000', 'icon_list', saber_icon_items([
        'Senior Director of Admissions: Lucy Alvarez',
        'Email: lalvarez@sabercollege.edu',
        'Phone: (305) 443-7030',
    ], 'campus-admissions'));
    saber_remove_element($data, '4c7d0cf');
});
$modified_pages[] = 861;
$actions[] = 'Campus Location: Lucy Alvarez updated and VESL card removed.';

// Gallery: retain only the current academic programs.
saber_edit_elementor_page(863, function (&$data) {
    saber_set_widget_setting($data, '3b53306', 'editor', 'Located in Miami, Florida, SABER College has been preparing students for rewarding healthcare careers since 1972. Our programs include the Professional Nursing Program (PNP) and the Physical Therapist Assistant (PTA) Program. Each program is designed to provide students with the skills, knowledge, and professional readiness needed to succeed in the healthcare industry.');
    saber_set_widget_setting($data, '0f57cca', 'editor', 'Our healthcare programs are delivered entirely in English, ensuring that graduates meet the language and communication standards required in medical environments across the United States.');
    saber_set_widget_setting($data, '6381b51', 'editor', 'Whether you’re interested in learning more about our Nursing labs or PTA clinical training, our gallery offers a real glimpse into day-to-day campus life. It’s not just about posed photos or promotional images. Our Instagram posts reflect real moments—students participating in lab simulations, collaborating in group projects, engaging in community service, and celebrating their academic milestones.');
    saber_set_widget_setting($data, 'c82c99e', 'icon_list', saber_icon_items([
        'Hands-on training sessions in our Health Sciences labs',
        'Faculty-led workshops and classroom discussions',
        'Behind-the-scenes content from clinical practice simulations',
        'Inspiring student success stories and testimonials',
        'Highlights from school-wide events and celebrations',
    ], 'gallery-highlights'));
});
$modified_pages[] = 863;
$actions[] = 'Gallery: retired-program references removed.';

// Correct two accidental word corruptions caused by the VESL letter sequence.
saber_edit_elementor_page(877, function (&$data) {
    if (!saber_replace_widget_setting($data, 'caa7f81', 'editor', 'rVESLving', 'resolving')) {
        WP_CLI::error('Nursing Admissions counseling widget was not found.');
    }
});
$modified_pages[] = 877;

// Accreditation: remove VESL and add the supplied NLN CNEA statement and official seal.
saber_edit_elementor_page(887, function (&$data) use ($nln_logo_id) {
    saber_set_widget_setting($data, '1ed484a', 'editor', 'SABER College Accreditation plays a central role in building confidence among students, families, employers, and regulators. It reflects the college’s dedication to educational quality, student support, and continuous improvement. Students enrolled in Nursing or PTA programs can expect a learning environment that meets or exceeds the standards set by accrediting agencies and professional organizations.');
    if (!saber_has_element($data, 'mktnlnsec')) {
        $nln = saber_atomic_container('mktnlnsec', 'e-accred-nln', [
            saber_atomic_container('mktnlnin', 'e-accred-nln-inner', [
                saber_heading_widget('mktnlnh', 'NLN CNEA Accreditation'),
                saber_image_widget('mktnlni', $nln_logo_id, 'NLN CNEA accreditation seal', 'https://cnea.nln.org/'),
                saber_text_widget('mktnlnt', '<p>“The SABER College Associate Degree in Nursing Program holds accreditation from the National League for Nursing Commission for Nursing Education Accreditation (NLN CNEA), located at 2600 Virginia Avenue, NW, Washington, DC 20037; (202) 909-2487.”</p>'),
            ], [
                'width' => saber_size(100, '%'),
                'max-width' => saber_size(1000),
                'padding' => saber_pad(72, 24, 76, 24),
                'gap' => saber_size(24),
            ], ['padding' => saber_pad(48, 20, 52, 20)]),
        ], [
            'width' => saber_size(100, '%'),
            'align-items' => saber_typed('string', 'center'),
            'background' => saber_background('#fff8e4'),
        ]);
        $data[] = $nln;
    }
    if (!saber_has_element($data, 'mktnlni')) {
        $nln_image = saber_image_widget('mktnlni', $nln_logo_id, 'NLN CNEA accreditation seal', 'https://cnea.nln.org/');
        if (!saber_insert_child_after($data, 'mktnlnin', 'mktnlnh', $nln_image)) {
            WP_CLI::error('Accreditation NLN CNEA content container was not found.');
        }
    }
    saber_set_widget_setting($data, 'mktnlni', 'width', ['unit' => 'px', 'size' => 220, 'sizes' => []]);
    saber_set_widget_setting($data, 'mktnlni', 'align', 'center');
});
$modified_pages[] = 887;
$actions[] = 'Accreditation: NLN CNEA statement and official seal added; existing COE, CIE and CAPTE marks retained.';

// Academic Affairs: replace the existing body copy with the supplied four paragraphs.
$academic_paragraphs = [
    'At SABER College, the Office of Academic Affairs is committed to guiding every student toward academic success from enrollment through graduation. Led by the Dean of Academic Affairs, this department oversees the quality, integrity, and consistency of instruction across all of our programs, including the Professional Nursing Program (A.S.) and the Physical Therapist Assistant Program (A.S.).',
    'Our Academic Affairs team supports students with personalized academic advisement—helping them understand program requirements, plan their course sequence, monitor their progress, and stay on track to meet graduation requirements. Students who need extra help have access to tutoring and remediation, along with mid-semester advising designed to identify challenges early and keep learning on course.',
    'Academic Affairs also administers key academic policies, including Satisfactory Academic Progress (SAP), attendance, transfer of credit, and academic governance. Faculty work closely with this office to review and update curricula each year, ensuring that what students learn stays current with the demands of today’s healthcare workforce.',
    'Our goal is simple: to create a supportive, student-centered environment where learners feel respected, motivated, and equipped to reach their educational and career goals. To learn more about academic support at SABER College, contact us at (305) 443-9170 or saber@sabercollege.edu.',
];
saber_edit_elementor_page(896, function (&$data) use ($academic_paragraphs) {
    saber_set_widget_setting($data, '43363de', 'editor', '<p>' . implode('</p><p>', array_slice($academic_paragraphs, 0, 3)) . '</p>');
    saber_remove_element($data, 'ae8d9b7');
    saber_set_widget_setting($data, '025db21', 'editor', '<p>' . $academic_paragraphs[3] . '</p>');
});
$modified_pages[] = 896;
$actions[] = 'Academic Affairs: supplied SEO copy applied verbatim.';

// Career Services: distribute the supplied four paragraphs across the existing design.
$career_paragraphs = [
    'At SABER College, Career Services is dedicated to helping students and graduates turn their education into meaningful employment. From the moment you enroll to the day you land your first position in healthcare, our team supports you at every step of your professional journey.',
    'Our career coaches provide one-on-one guidance tailored to your goals. We help you build a professional resume and cover letter, prepare for interviews with confidence, and develop the job-search skills employers value. For students with little or no prior experience, we offer practical strategies to help you stand out and enter the workforce successfully.',
    'SABER College maintains strong relationships with healthcare employers throughout South Florida, connecting graduates with real opportunities in hospitals, clinics, rehabilitation centers, long-term care facilities, and community health organizations. We also coordinate externship and clinical placements that give students hands-on experience and valuable professional connections before they graduate.',
    'Placement services are available to all graduates at no additional charge. While no school can guarantee employment, SABER College is committed to making every reasonable effort to help each graduate find a position suited to their training, skills, and goals. To learn more, contact us at (305) 443-9170 or saber@sabercollege.edu.',
];
saber_edit_elementor_page(900, function (&$data) use ($career_paragraphs) {
    saber_set_widget_setting($data, 'fd31a55', 'editor', '<p>' . $career_paragraphs[0] . '</p>');
    saber_set_widget_setting($data, 'dc9c485', 'editor', '<p>' . $career_paragraphs[1] . '</p><p>' . $career_paragraphs[2] . '</p>');
    saber_set_widget_setting($data, 'f9d4334', 'editor', '<p>' . $career_paragraphs[3] . '</p>');
});
$modified_pages[] = 900;
$actions[] = 'Career Services: supplied SEO copy applied verbatim.';

// Net Price Calculator: remove the retired program from the program examples.
saber_edit_elementor_page(911, function (&$data) {
    saber_set_widget_setting($data, '43629b9', 'editor', 'This tool is particularly useful for those pursuing Gainful Employment programs at SABER College, including our Nursing and Physical Therapist Assistant programs.');
});
$modified_pages[] = 911;
$actions[] = 'Net Price Calculator: retired-program reference removed.';

// Graduation Requirements: repair corrupted word and remove retired-program wording.
saber_edit_elementor_page(915, function (&$data) {
    saber_set_widget_setting($data, '5efea74', 'editor', 'At SABER College, graduation requirements are carefully structured to ensure that every student who completes a degree in Professional Nursing or Physical Therapist Assistant is fully prepared to enter the healthcare workforce with the clinical skills, academic knowledge, and ethical standards required in today’s patient care environments.');
    if (!saber_replace_widget_setting($data, '56ad851', 'editor', 'unrVESLved', 'unresolved')) {
        WP_CLI::error('Graduation Requirements obligations widget was not found.');
    }
});
$modified_pages[] = 915;

// Gainful Employment: update the supplied fiscal year.
saber_edit_elementor_page(917, function (&$data) {
    saber_set_widget_setting($data, 'b3cd81d', 'editor', 'Fiscal Year: 2026-2027');
});
$modified_pages[] = 917;
$actions[] = 'Gainful Employment: fiscal year updated to 2026-2027.';

// Faculty: replace all three current-program lists and remove the VESL section.
saber_edit_elementor_page(919, function (&$data) {
    saber_set_widget_setting($data, 'a8ba405', 'editor', 'Our SABER College faculty exemplify the dedication, knowledge, and professionalism that define truly effective educators. Our faculty are the strength of our institution; bringing expertise, passion, and real-world experience into every classroom. With decades of combined teaching and clinical experience, our faculty hold advanced academic credentials and share a deep commitment to student success. Whether guiding future nurses or physical therapist assistants, each member of our team plays a vital role in fulfilling SABER College’s mission to prepare graduates for meaningful careers.');
    saber_set_widget_setting($data, '859c2c0', 'icon_list', saber_icon_items([
        'Yalmar Acosta — MSN, APRN',
        'Yailet Cervera — MSN, RN',
        'Hugo Cos-Salmon — MSN, APRN',
        'Solmaire Cueto — MSN, APRN',
        'Lorna Cuxart Falcon — MSN, APRN',
        'Yaimara Diaz — DNP, APRN',
        'Irelys Di Renzo — BSN, RN',
        'Patricia Fernandes — MSN, APRN',
        'Frank Garcia — MSN, RN',
        'Yulia Garcia — MSN, RN',
        'Yovany Gongora — MSN, APRN',
        'Ana Grillo — BSN, RN',
        'Rainel Leon Leon — MSN, APRN',
        'Juan Manotas — MSN, RN',
        'Osvaldo Medina — MSN, RN',
        'Yosvani Pena — MSN, APRN',
        'Mercedes Perez — DNP, RN',
        'Dixania Soto — MSN, RN',
    ], 'faculty-nursing'));
    saber_set_widget_setting($data, '96c6c6f', 'icon_list', saber_icon_items([
        'Karen Flynn — BS / MS Physical Therapy',
        'Gaspara Barditch — MS / Doctorate Physical Therapy',
        'Lorena Castillo — BS Physical Therapy',
        'Ayleen Prieto — A.S. Physical Therapy',
        'Aura Sanchez — MS Physical Therapy',
    ], 'faculty-pta'));
    saber_set_widget_setting($data, 'a20a532', 'icon_list', saber_icon_items([
        'Victoria Mahler — M.A. in English',
        'Freddy Suarez — M.S. Math Education, Ed.D.',
        'Alexandra J. Lis — M.A. in Psychology, Ed.D.',
        'Araceli Rodríguez — Doctor of Medicine (Anatomy & Physiology)',
        'Jaqueline Mayorga — M.S. in Microbiology',
    ], 'faculty-general'));
    saber_remove_element($data, 'facvesls');
});
foreach (get_post_meta(919, '_elementor_used_global_class', false) as $class) {
    if (stripos((string) $class, 'vesl') !== false) {
        delete_post_meta(919, '_elementor_used_global_class', $class);
    }
}
$modified_pages[] = 919;
$actions[] = 'Faculty: supplied Nursing, PTA and General Education lists applied; VESL faculty removed.';
$notes[] = 'The source contradicts itself on Gaspara’s surname (Bardritch/Barditch). Faculty retains “Gaspara Barditch” from the supplied faculty table; Leadership uses “Gaspara Bardritch” from the supplied leadership list. Marketing must confirm the correct spelling.';

// Distance Education: remove VESL and the explicitly identified access paragraph.
saber_edit_elementor_page(921, function (&$data) {
    saber_set_widget_setting($data, 'a36f8ea', 'editor', 'SABER College Distance Education offers students the flexibility to begin or complete coursework online while maintaining the quality, structure, and support that define our academic programs. Distance education is not a separate program or credential—it is a delivery modality, meaning a method of instructional delivery. At SABER College, distance education is used strategically to expand access and provide flexible learning options for students pursuing healthcare careers.');
    saber_set_widget_setting($data, '9ec9779', 'editor', 'As part of our commitment to accessible, career-driven education, SABER College offers select courses and program components via distance education. This includes the first semester of our accredited healthcare programs, including Professional Nursing and Physical Therapist Assistant (PTA). After this initial online phase, healthcare students transition to on-campus instruction for the hands-on and clinical components of their training.');
    saber_set_widget_setting($data, '860ae9c', 'editor', 'Whether you are preparing for licensure or beginning your healthcare education, SABER College Distance Education offers flexible entry points into high-quality academic pathways designed for real-world outcomes.');
    saber_remove_element($data, 'deveslc');
    if (!saber_set_atomic_style_prop($data, 'dehealth', 'desktop', 'width', saber_size(100, '%'))) {
        WP_CLI::error('Distance Education healthcare card style was not found.');
    }
    saber_set_widget_setting($data, '805783c', 'editor', 'As part of our commitment to flexibility and student access, SABER College offers the first semester of the Nursing and PTA programs through distance education. These online courses focus on theoretical and foundational content, ensuring students are well prepared for the hands-on, in-person components that begin in the second semester. Students can begin their healthcare education with the convenience of remote learning—while staying connected to instructors and academic support every step of the way.');
    saber_set_widget_setting($data, '6ec9c9a', 'editor', 'Unlike self-paced platforms, our distance education model is guided by credentialed faculty who actively monitor student progress, respond to questions, and provide individualized support. Students benefit from a learning experience that feels interactive, personal, and aligned with the expectations of modern healthcare instruction.');
    saber_remove_element($data, 'cc8ee6e');
    saber_set_widget_setting($data, 'c649688', 'editor', 'From your first inquiry to program completion, you’ll have access to responsive staff and qualified faculty who are committed to helping you stay informed, confident, and connected. This structured support ensures that every student participating in a short-term distance education component of a healthcare track has the tools and resources needed to succeed.');
    saber_remove_element($data, '5d67d18');
    saber_remove_element($data, 'deimpc1');
    if (!saber_set_atomic_style_prop($data, 'deimpc2', 'desktop', 'width', saber_size(100, '%'))) {
        WP_CLI::error('Distance Education impact card style was not found.');
    }
});
foreach (get_post_meta(921, '_elementor_used_global_class', false) as $class) {
    if (stripos((string) $class, 'vesl') !== false) {
        delete_post_meta(921, '_elementor_used_global_class', $class);
    }
}
$modified_pages[] = 921;
$actions[] = 'Distance Education: VESL and retired healthcare-training references removed.';

// Contact page: update Admissions and remove the VESL contact block.
saber_edit_elementor_page(1238, function (&$data) {
    saber_set_widget_setting($data, 'f96ae6c', 'icon_list', saber_icon_items([
        'Senior Director of Admissions: Lucy Alvarez',
        'Email: lalvarez@sabercollege.edu',
        'Phone: (305) 443-7030',
    ], 'contact-admissions'));
    saber_remove_element($data, '33bb1b1');
    saber_remove_element($data, '54edc89');
});
$modified_pages[] = 1238;
$actions[] = 'Contact Us: Lucy Alvarez updated and VESL contact block removed.';

// LP Campus Location: update Admissions and remove both VESL widgets.
saber_edit_elementor_page(1639, function (&$data) {
    foreach (['c895f31', '00781ca'] as $id) {
        saber_set_widget_setting($data, $id, 'editor', "Senior Director of Admissions: Lucy Alvarez\nEmail: lalvarez@sabercollege.edu\nPhone: (305) 443-7030");
    }
    saber_remove_element($data, '1b41055');
    saber_remove_element($data, '4d7f77e');
    saber_remove_element($data, '79f3e3a');
});
$modified_pages[] = 1639;
$actions[] = 'LP Campus Location: Lucy Alvarez updated and VESL block removed.';

// Standard WordPress content corrections.
if (saber_update_post_content(989, fn($content) => str_replace('rVESLving', 'resolving', $content))) {
    $actions[] = 'RN article: accidental “rVESLving” corruption repaired.';
}
if (saber_update_post_content(1037, fn($content) => str_replace('rVESLving', 'resolving', $content))) {
    $actions[] = 'Professional Nursing Values article: accidental “rVESLving” corruption repaired.';
}
if (saber_update_post_content(1034, function ($content) {
    $content = str_replace('Contact Admissions Specialist Kirssys Fabre:', 'Contact Senior Director of Admissions Lucy Alvarez:', $content);
    $content = str_replace('kfabre@sabercollege.edu', 'lalvarez@sabercollege.edu', $content);
    return $content;
})) {
    $actions[] = 'PTA transfer article: Admissions contact updated to Lucy Alvarez.';
}
if (saber_update_post_content(1087, function ($content) {
    return preg_replace('~\s*<!-- wp:paragraph -->\s*<p>[^<]*online VESL Program.*?</p>\s*<!-- /wp:paragraph -->~s', '', $content, 1);
})) {
    $actions[] = 'Dual Commencement article: VESL promotional paragraph removed.';
}
if (saber_update_post_content(1232, function ($content) {
    return str_replace(
        'providing accredited career education in Nursing, Physical Therapist Assistant, healthcare training, and VESL.',
        'providing accredited career education in Nursing and Physical Therapist Assistant programs.',
        $content
    );
})) {
    $actions[] = 'Privacy Policy: retired-program references removed.';
}

// Retire the two landing pages and five articles dedicated to discontinued programs.
foreach ([1643, 1649, 1040, 1108, 1111, 1141, 1155] as $post_id) {
    $post = get_post($post_id);
    if ($post && $post->post_status !== 'trash') {
        wp_trash_post($post_id);
        $actions[] = "Moved {$post->post_type} {$post_id} ({$post->post_title}) to Trash.";
    }
}

// Remove only program-specific categories/tags. Shared generic taxonomies remain intact.
foreach ([15, 16] as $term_id) {
    $term = get_term($term_id, 'category');
    if ($term && !is_wp_error($term)) {
        wp_delete_term($term_id, 'category');
        $actions[] = "Deleted retired category {$term->name}.";
    }
}
foreach ([25, 26, 29, 49, 64, 67, 70, 71, 72, 108, 128, 129] as $term_id) {
    $term = get_term($term_id, 'post_tag');
    if ($term && !is_wp_error($term)) {
        wp_delete_term($term_id, 'post_tag');
        $actions[] = "Deleted retired tag {$term->name}.";
    }
}

// Existing redirects pointing to content now removed are disabled rather than given invented destinations.
$disabled_redirects = [];
if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $redirect_table)) === $redirect_table) {
    $disabled_redirects = $wpdb->get_col(
        "SELECT id FROM {$redirect_table} WHERE status='active' AND (LOWER(url_to) LIKE '%/lp/vesl/%' OR LOWER(url_to) LIKE '%/lp/medical-assistant-program/%' OR LOWER(url_to) LIKE '%/category/vesl/%' OR LOWER(url_to) LIKE '%/category/medical-assisting/%')"
    );
    if ($disabled_redirects) {
        $ids = implode(',', array_map('intval', $disabled_redirects));
        $wpdb->query("UPDATE {$redirect_table} SET status='inactive', updated=UTC_TIMESTAMP() WHERE id IN ({$ids})");
        $cache_table = $wpdb->prefix . 'rank_math_redirections_cache';
        if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $cache_table)) === $cache_table) {
            $wpdb->query("DELETE FROM {$cache_table}");
        }
        $actions[] = 'Disabled Rank Math redirects whose destinations were removed.';
    }
}

$notes[] = 'No replacement URL was supplied for the retired VESL and Medical Assisting pages, articles, categories or tags. Redirects that pointed to those removed destinations were disabled; final redirect or 410 decisions remain pending.';
$notes[] = 'The Net Price Calculator page contains no calculator embed or editable academic-year field; it currently links back to itself. The requested 2026 - 2027 year could not be applied without the calculator provider/embed configuration.';
$notes[] = 'The Weekend Nursing article keeps the generic phrase “medical assistants” because it describes applicants’ prior occupations and does not advertise a SABER Medical Assisting program.';
$notes[] = 'The source asks to verify Board of Directors & Administrators, Facilities, ACT/SAT requirements, COVID-era copy, HEERF archiving and Institutional Plans, but provides no approved URLs or replacement decisions. Those items were not invented.';

flush_rewrite_rules(false);

$report = [
    'applied_at' => current_time('mysql'),
    'backup' => $backup,
    'modified_pages' => array_values(array_unique($modified_pages)),
    'actions' => $actions,
    'disabled_rank_math_redirects' => array_map('intval', $disabled_redirects),
    'notes' => $notes,
];

file_put_contents('/tmp/saber-marketing-corrections-result.json', wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
WP_CLI::line(wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
WP_CLI::success('Verified marketing corrections applied.');
