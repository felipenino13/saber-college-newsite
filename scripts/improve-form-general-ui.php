<?php
/** Improve the shared Form General UI without changing fields or integrations. */

if (!defined('ABSPATH') || !class_exists('WP_CLI')) {
    exit("Run this script through WP-CLI.\n");
}

$post_id = 2015;
$form_widget_id = 'f698ff6';
$consent_widget_id = '365f20a';
$integration_widget_ids = ['518a0bf', 'dd51870'];
$style_widget_id = '394d74b';

$raw = get_post_meta($post_id, '_elementor_data', true);
$data = json_decode($raw, true);
if (!is_array($data) || json_last_error() !== JSON_ERROR_NONE) {
    WP_CLI::error('Form General has invalid Elementor data.');
}

$backup = '/tmp/form-general-2015-before-ui-' . gmdate('Ymd-His') . '.json';
if (file_put_contents($backup, $raw) === false) {
    WP_CLI::error('Could not create the pre-update Elementor backup.');
}

function &saber_form_find_element(array &$elements, string $target_id)
{
    foreach ($elements as &$element) {
        if (($element['id'] ?? '') === $target_id) {
            return $element;
        }

        if (!empty($element['elements']) && is_array($element['elements'])) {
            $found = &saber_form_find_element($element['elements'], $target_id);
            if ($found !== null) {
                return $found;
            }
        }
    }

    static $not_found = null;
    return $not_found;
}

function saber_form_protected_configuration(array $settings): array
{
    $keys = [
        'form_name', 'form_fields', 'submit_actions', 'redirect_to',
        'email_to', 'email_subject', 'email_content', 'email_from', 'email_from_name',
        'email_to_2', 'email_subject_2', 'email_content_2', 'email_from_2',
        'email_from_name_2', 'email_reply_to_2',
        'success_message', 'error_message', 'server_message', 'invalid_message',
        'required_field_message', 'button_text', 'button_css_id',
    ];

    return array_intersect_key($settings, array_flip($keys));
}

$form = &saber_form_find_element($data, $form_widget_id);
$consent = &saber_form_find_element($data, $consent_widget_id);
$style_widget = &saber_form_find_element($data, $style_widget_id);
if ($form === null || $consent === null || $style_widget === null) {
    WP_CLI::error('One or more expected Form General elements were not found.');
}

$protected_before = saber_form_protected_configuration($form['settings']);
$consent_before = $consent['settings']['editor'] ?? '';
$integration_before = [];
foreach ($integration_widget_ids as $widget_id) {
    $widget = &saber_form_find_element($data, $widget_id);
    if ($widget === null) {
        WP_CLI::error("Integration widget {$widget_id} was not found.");
    }
    $integration_before[$widget_id] = $widget['settings']['html'] ?? '';
}

$size = static fn($value, string $unit = 'px'): array => [
    'unit' => $unit,
    'size' => $value,
    'sizes' => [],
];
$dimensions = static fn($top, $right, $bottom, $left, bool $linked = false): array => [
    'unit' => 'px',
    'top' => (string) $top,
    'right' => (string) $right,
    'bottom' => (string) $bottom,
    'left' => (string) $left,
    'isLinked' => $linked,
];

$visual_settings = [
    'column_gap' => $size(12),
    'row_gap' => $size(16),
    'label_spacing' => $size(8),
    'label_color' => '#001a39',
    'mark_required_color' => '#b42318',
    'label_typography_typography' => 'custom',
    'label_typography_font_family' => 'Nunito',
    'label_typography_font_size' => $size(14),
    'label_typography_font_weight' => '700',
    'label_typography_line_height' => $size(1.35, 'em'),
    'field_text_color' => '#001a39',
    'field_typography_typography' => 'custom',
    'field_typography_font_family' => 'Nunito',
    'field_typography_font_size' => $size(16),
    'field_typography_font_weight' => '500',
    'field_typography_line_height' => $size(1.4, 'em'),
    'field_background_color' => '#ffffff',
    'field_border_color' => '#b8c8d8',
    'field_border_width' => $dimensions(1, 1, 1, 1, true),
    'field_border_radius' => $dimensions(10, 10, 10, 10, true),
    'button_size' => 'md',
    'button_width' => '100',
    'button_width_mobile' => '100',
    'button_typography_typography' => 'custom',
    'button_typography_font_family' => 'Nunito',
    'button_typography_font_size' => $size(16),
    'button_typography_font_weight' => '800',
    'button_background_color' => '#fdc800',
    'button_text_color' => '#001a39',
    'button_border_border' => 'solid',
    'button_border_width' => $dimensions(1, 1, 1, 1, true),
    'button_border_color' => '#fdc800',
    'button_border_radius' => $dimensions(10, 10, 10, 10, true),
    'button_text_padding' => $dimensions(15, 22, 15, 22, false),
    'button_background_hover_color' => '#082b55',
    'button_hover_color' => '#ffffff',
    'button_hover_border_color' => '#082b55',
    'hover_transition_duration' => $size(180, 'ms'),
];

foreach ($visual_settings as $key => $value) {
    $form['settings'][$key] = $value;
}

foreach (['label_color', 'field_text_color', 'button_text_color', 'button_hover_color'] as $global_key) {
    if (isset($form['settings']['__globals__'][$global_key])) {
        $form['settings']['__globals__'][$global_key] = '';
    }
}

$style_widget['settings']['html'] = <<<'HTML'
<style>
.elementor-2015 {
  --saber-form-ink: #001a39;
  --saber-form-navy: #082b55;
  --saber-form-blue: #25377b;
  --saber-form-yellow: #fdc800;
  --saber-form-sky: #f3f9ff;
  --saber-form-border: #b8c8d8;
}

.elementor-2015 .elementor-element.elementor-element-f698ff6 .elementor-field-group > label {
  letter-spacing: .01em;
}

.elementor-2015 .elementor-element.elementor-element-f698ff6 input.elementor-field:not([type="radio"]),
.elementor-2015 .elementor-element.elementor-element-f698ff6 select.elementor-field,
.elementor-2015 .elementor-element.elementor-element-f698ff6 textarea.elementor-field {
  min-height: 52px;
  padding: 13px 16px;
  border: 1px solid var(--saber-form-border);
  border-radius: 10px;
  background: #fff;
  box-shadow: 0 1px 2px rgba(0, 26, 57, .04);
  transition: border-color .18s ease, box-shadow .18s ease, background-color .18s ease;
}

.elementor-2015 .elementor-element.elementor-element-f698ff6 input.elementor-field::placeholder,
.elementor-2015 .elementor-element.elementor-element-f698ff6 textarea.elementor-field::placeholder {
  color: #6d7f90;
  opacity: 1;
}

.elementor-2015 .elementor-element.elementor-element-f698ff6 input.elementor-field:focus,
.elementor-2015 .elementor-element.elementor-element-f698ff6 select.elementor-field:focus,
.elementor-2015 .elementor-element.elementor-element-f698ff6 textarea.elementor-field:focus {
  border-color: var(--saber-form-blue);
  background: #fff;
  box-shadow: 0 0 0 4px rgba(37, 55, 123, .13);
  outline: none;
}

.elementor-2015 .elementor-element.elementor-element-f698ff6 .iti {
  width: 100% !important;
}

.elementor-2015 .elementor-element.elementor-element-f698ff6 .iti input.iti__tel-input {
  padding-left: 3.75rem !important;
}

.elementor-2015 .elementor-element.elementor-element-f698ff6 .elementor-field-type-radio .elementor-field-subgroup {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
  width: 100%;
}

.elementor-2015 .elementor-element.elementor-element-f698ff6 .elementor-field-type-radio .elementor-field-option {
  position: relative;
  width: 100% !important;
  padding: 0 !important;
  border: 0 !important;
}

.elementor-2015 .elementor-element.elementor-element-f698ff6 .elementor-field-type-radio input[type="radio"] {
  position: absolute;
  width: 1px;
  height: 1px;
  margin: 0;
  opacity: 0;
  pointer-events: none;
}

.elementor-2015 .elementor-element.elementor-element-f698ff6 .elementor-field-type-radio input[type="radio"] + label {
  display: flex;
  align-items: center;
  gap: 11px;
  width: 100%;
  min-height: 76px;
  padding: 14px 16px;
  color: var(--saber-form-ink);
  font-weight: 700;
  line-height: 1.35;
  text-align: left;
  cursor: pointer;
  background: #fff;
  border: 1px solid var(--saber-form-border);
  border-radius: 12px;
  box-shadow: 0 2px 7px rgba(0, 26, 57, .04);
  transition: border-color .18s ease, background-color .18s ease, box-shadow .18s ease, transform .18s ease;
}

.elementor-2015 .elementor-element.elementor-element-f698ff6 .elementor-field-type-radio input[type="radio"] + label::before {
  content: "";
  flex: 0 0 20px;
  width: 20px;
  height: 20px;
  border: 2px solid #8294a7;
  border-radius: 50%;
  background: #fff;
  box-shadow: inset 0 0 0 4px #fff;
  transition: border-color .18s ease, background-color .18s ease;
}

.elementor-2015 .elementor-element.elementor-element-f698ff6 .elementor-field-type-radio input[type="radio"] + label:hover {
  border-color: var(--saber-form-blue);
  transform: translateY(-1px);
  box-shadow: 0 8px 18px rgba(0, 26, 57, .08);
}

.elementor-2015 .elementor-element.elementor-element-f698ff6 .elementor-field-type-radio input[type="radio"]:checked + label {
  color: var(--saber-form-ink);
  border-color: var(--saber-form-yellow);
  background: #fff8d8;
  box-shadow: 0 0 0 2px rgba(253, 200, 0, .2), 0 8px 18px rgba(0, 26, 57, .08);
}

.elementor-2015 .elementor-element.elementor-element-f698ff6 .elementor-field-type-radio input[type="radio"]:checked + label::before {
  border-color: var(--saber-form-blue);
  background: var(--saber-form-blue);
}

.elementor-2015 .elementor-element.elementor-element-f698ff6 .elementor-field-type-radio input[type="radio"]:focus-visible + label {
  outline: 3px solid rgba(37, 55, 123, .25);
  outline-offset: 3px;
}

.elementor-2015 .elementor-element.elementor-element-f698ff6 .elementor-button[type="submit"] {
  min-height: 52px;
  justify-content: center;
  box-shadow: 0 8px 18px rgba(253, 200, 0, .2);
  transition: transform .18s ease, box-shadow .18s ease, background-color .18s ease;
}

.elementor-2015 .elementor-element.elementor-element-f698ff6 .elementor-button[type="submit"]:hover {
  transform: translateY(-1px);
  box-shadow: 0 10px 24px rgba(8, 43, 85, .18);
}

.elementor-2015 .elementor-element.elementor-element-f698ff6 .elementor-button[type="submit"]:focus-visible {
  outline: 3px solid rgba(37, 55, 123, .28);
  outline-offset: 3px;
}

.elementor-2015 .elementor-element.elementor-element-f698ff6 .elementor-message {
  margin-top: 7px;
  font-size: 13px;
  font-weight: 700;
  line-height: 1.4;
}

.elementor-2015 .elementor-element.elementor-element-365f20a p {
  max-width: 72ch;
  margin: 0;
  color: rgba(0, 26, 57, .68);
  line-height: 1.55;
}

@media (max-width: 767px) {
  .elementor-2015 .elementor-element.elementor-element-f698ff6 .elementor-field-group-fname,
  .elementor-2015 .elementor-element.elementor-element-f698ff6 .elementor-field-group-lname {
    width: 100% !important;
  }

  .elementor-2015 .elementor-element.elementor-element-f698ff6 .elementor-field-type-radio .elementor-field-subgroup {
    grid-template-columns: 1fr;
  }

  .elementor-2015 .elementor-element.elementor-element-f698ff6 .elementor-field-type-radio input[type="radio"] + label {
    min-height: 60px;
  }
}
</style>
HTML;

$protected_after = saber_form_protected_configuration($form['settings']);
if ($protected_before !== $protected_after) {
    WP_CLI::error('Protected form configuration changed unexpectedly.');
}
if (($consent['settings']['editor'] ?? '') !== $consent_before) {
    WP_CLI::error('Consent text changed unexpectedly.');
}
foreach ($integration_widget_ids as $widget_id) {
    $widget = &saber_form_find_element($data, $widget_id);
    if (($widget['settings']['html'] ?? '') !== $integration_before[$widget_id]) {
        WP_CLI::error("Integration widget {$widget_id} changed unexpectedly.");
    }
}

$encoded = wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if (!$encoded || update_post_meta($post_id, '_elementor_data', wp_slash($encoded)) === false) {
    WP_CLI::error('Could not update Form General.');
}

update_post_meta($post_id, '_elementor_edit_mode', 'builder');
clean_post_cache($post_id);
if (class_exists('Elementor\\Plugin')) {
    Elementor\Plugin::$instance->files_manager->clear_cache();
}

$saved = json_decode(get_post_meta($post_id, '_elementor_data', true), true);
$saved_form = &saber_form_find_element($saved, $form_widget_id);
$saved_style = &saber_form_find_element($saved, $style_widget_id);
if (saber_form_protected_configuration($saved_form['settings'] ?? []) !== $protected_before) {
    WP_CLI::error('Saved form configuration verification failed.');
}
if (strpos($saved_style['settings']['html'] ?? '', 'input[type="radio"]:checked + label') === false) {
    WP_CLI::error('Saved radio-card CSS verification failed.');
}

WP_CLI::log(wp_json_encode([
    'post_id' => $post_id,
    'template' => 'Form General',
    'backup' => $backup,
    'visible_fields_preserved' => count(array_filter(
        $protected_before['form_fields'] ?? [],
        static fn(array $field): bool => ($field['field_type'] ?? '') !== 'hidden'
    )),
    'integration_widgets_preserved' => $integration_widget_ids,
    'radio_options_preserved' => explode("\n", $protected_before['form_fields'][4]['field_options'] ?? ''),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
WP_CLI::success('Form General UI improved without changing fields or integrations.');
