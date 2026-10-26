<?php

namespace SaberCollege\SabiChatbot;

if (!defined('ABSPATH')) {
    exit;
}

final class Settings
{
    public const OPTION_NAME = 'sabi_chatbot_settings';
    private const CREDENTIAL_OPTION = 'sabi_chatbot_api_credential';

    /**
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'general' => [
                'enabled' => false,
                'preview_mode' => true,
                'assistant_name' => 'SABI',
                'display_mode' => 'shortcode',
            ],
            'content' => [
                'welcome_message' => "Hi! I'm SABI, SABER College's virtual guide. I can help you explore our programs, admissions, tuition resources, and student services. How can I help?",
                'input_placeholder' => 'Ask SABI about programs, admissions, or student services...',
                'compliance_text' => 'General information only. Requirements, costs, dates, and policies may change; confirm them with SABER College and the current School Catalog. Do not share sensitive personal information.',
                'instructions' => self::default_instructions(),
            ],
            'sources' => self::default_sources(),
            'leads' => [
                'enabled' => false,
                'fields' => ['name', 'program', 'email', 'phone', 'consent'],
                'notification_email' => get_option('admin_email', ''),
                'notify_by_email' => false,
                'consent_text_en' => 'I agree that SABER College may contact me about its programs. I have read the Privacy Policy.',
                'consent_text_es' => 'Acepto que SABER College me contacte sobre sus programas. He leído la Política de Privacidad.',
            ],
            'appearance' => [
                'avatar_url' => home_url('/wp-content/uploads/2026/08/chat-sabi-square-notification.png'),
                'primary_color' => '#001A39',
                'accent_color' => '#FDC800',
                'surface_color' => '#F3F7FA',
                'position' => 'bottom-right',
            ],
            'api' => [
                'enabled' => true,
                'provider' => 'openai',
                'model' => 'gpt-5.6-terra',
                'key_source' => 'not_configured',
                'max_output_tokens' => 700,
                'timeout_seconds' => 35,
            ],
            'security' => [
                'max_input_length' => 600,
                'requests_per_minute' => 8,
                'requests_per_session' => 40,
                'requests_per_day' => 500,
                'lead_retention_days' => 180,
                'store_conversations' => false,
                'privacy_url' => home_url('/privacy-policy/'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function get(): array
    {
        $stored = get_option(self::OPTION_NAME, []);
        if (!is_array($stored)) {
            $stored = [];
        }

        $settings = array_replace_recursive(self::defaults(), $stored);
        $settings['api']['key_source'] = self::credential_source();

        return $settings;
    }

    /**
     * @param string               $section Section key.
     * @param array<string, mixed> $input   Untrusted section data.
     * @return array<string, mixed>
     */
    public static function save_section(string $section, array $input): array
    {
        $settings = self::get();

        if ($section === 'api') {
            if (!empty($input['clear_api_key'])) {
                delete_option(self::CREDENTIAL_OPTION);
            } elseif (trim((string) ($input['api_key'] ?? '')) !== '') {
                self::store_api_key((string) $input['api_key']);
            }
        }

        $sanitized = self::sanitize_section($section, $input, $settings[$section] ?? []);

        if ($sanitized === null) {
            return $settings;
        }

        $settings[$section] = $sanitized;
        update_option(self::OPTION_NAME, $settings, false);

        return $settings;
    }

    /**
     * @param string               $section Section key.
     * @param array<string, mixed> $input   Untrusted section data.
     * @param array<string, mixed> $current Current section values.
     * @return array<string, mixed>|null
     */
    private static function sanitize_section(string $section, array $input, array $current): ?array
    {
        if ($section === 'general') {
            return [
                'enabled' => !empty($input['enabled']),
                'preview_mode' => !empty($input['preview_mode']),
                'assistant_name' => sanitize_text_field($input['assistant_name'] ?? 'SABI'),
                'display_mode' => 'shortcode',
            ];
        }

        if ($section === 'content') {
            return [
                'welcome_message' => sanitize_textarea_field($input['welcome_message'] ?? ''),
                'input_placeholder' => sanitize_text_field($input['input_placeholder'] ?? ''),
                'compliance_text' => sanitize_textarea_field($input['compliance_text'] ?? ''),
                'instructions' => sanitize_textarea_field($input['instructions'] ?? ''),
            ];
        }

        if ($section === 'leads') {
            return [
                'enabled' => !empty($input['enabled']),
                'fields' => ['name', 'program', 'email', 'phone', 'consent'],
                'notification_email' => sanitize_email($input['notification_email'] ?? ''),
                'notify_by_email' => !empty($input['notify_by_email']),
                'consent_text_en' => sanitize_textarea_field($input['consent_text_en'] ?? ''),
                'consent_text_es' => sanitize_textarea_field($input['consent_text_es'] ?? ''),
            ];
        }

        if ($section === 'appearance') {
            $positions = ['bottom-right', 'bottom-left'];
            $position = sanitize_key($input['position'] ?? 'bottom-right');

            return [
                'avatar_url' => esc_url_raw($input['avatar_url'] ?? ''),
                'primary_color' => sanitize_hex_color($input['primary_color'] ?? '') ?: '#001A39',
                'accent_color' => sanitize_hex_color($input['accent_color'] ?? '') ?: '#FDC800',
                'surface_color' => sanitize_hex_color($input['surface_color'] ?? '') ?: '#F3F7FA',
                'position' => in_array($position, $positions, true) ? $position : 'bottom-right',
            ];
        }

        if ($section === 'api') {
            return [
                'enabled' => !empty($input['enabled']),
                'provider' => 'openai',
                'model' => sanitize_text_field($input['model'] ?? ''),
                'key_source' => self::credential_source(),
                'max_output_tokens' => self::bounded_integer($input['max_output_tokens'] ?? 700, 100, 2000),
                'timeout_seconds' => self::bounded_integer($input['timeout_seconds'] ?? 35, 10, 90),
            ];
        }

        if ($section === 'security') {
            return [
                'max_input_length' => self::bounded_integer($input['max_input_length'] ?? 600, 100, 4000),
                'requests_per_minute' => self::bounded_integer($input['requests_per_minute'] ?? 8, 1, 60),
                'requests_per_session' => self::bounded_integer($input['requests_per_session'] ?? 40, 1, 500),
                'requests_per_day' => self::bounded_integer($input['requests_per_day'] ?? 500, 10, 10000),
                'lead_retention_days' => self::bounded_integer($input['lead_retention_days'] ?? 180, 1, 730),
                'store_conversations' => !empty($input['store_conversations']),
                'privacy_url' => esc_url_raw($input['privacy_url'] ?? ''),
            ];
        }

        return null;
    }

    public static function api_key(): string
    {
        $stored = self::decrypt_api_key((string) get_option(self::CREDENTIAL_OPTION, ''));
        if ($stored !== '') {
            return $stored;
        }

        $key = defined('SABI_OPENAI_API_KEY') ? (string) constant('SABI_OPENAI_API_KEY') : '';
        if ($key === '') {
            $key = (string) getenv('OPENAI_API_KEY');
        }

        return trim($key);
    }

    public static function credential_source(): string
    {
        if (self::decrypt_api_key((string) get_option(self::CREDENTIAL_OPTION, '')) !== '') {
            return 'wordpress_encrypted';
        }
        if (defined('SABI_OPENAI_API_KEY') && trim((string) constant('SABI_OPENAI_API_KEY')) !== '') {
            return 'constant';
        }
        if (trim((string) getenv('OPENAI_API_KEY')) !== '') {
            return 'environment';
        }

        return 'not_configured';
    }

    public static function has_stored_api_key(): bool
    {
        return self::credential_source() === 'wordpress_encrypted';
    }

    private static function store_api_key(string $value): bool
    {
        $value = trim($value);
        if (strlen($value) < 20 || strlen($value) > 500 || preg_match('/\s/', $value)) {
            return false;
        }

        $encrypted = self::encrypt_api_key($value);
        if ($encrypted === '') {
            return false;
        }

        return update_option(self::CREDENTIAL_OPTION, $encrypted, false);
    }

    private static function encrypt_api_key(string $value): string
    {
        $key = self::encryption_key();
        if (function_exists('sodium_crypto_secretbox')) {
            $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
            $ciphertext = sodium_crypto_secretbox($value, $nonce, $key);

            return 'sodium:' . base64_encode($nonce . $ciphertext);
        }
        if (function_exists('openssl_encrypt')) {
            $iv = random_bytes(12);
            $tag = '';
            $ciphertext = openssl_encrypt($value, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
            if (!is_string($ciphertext)) {
                return '';
            }

            return 'openssl:' . base64_encode($iv . $tag . $ciphertext);
        }

        return '';
    }

    private static function decrypt_api_key(string $payload): string
    {
        if ($payload === '' || !str_contains($payload, ':')) {
            return '';
        }

        [$method, $encoded] = explode(':', $payload, 2);
        $decoded = base64_decode($encoded, true);
        if (!is_string($decoded)) {
            return '';
        }

        $key = self::encryption_key();
        if ($method === 'sodium' && function_exists('sodium_crypto_secretbox_open')) {
            $nonce_length = SODIUM_CRYPTO_SECRETBOX_NONCEBYTES;
            if (strlen($decoded) <= $nonce_length) {
                return '';
            }
            $plain = sodium_crypto_secretbox_open(substr($decoded, $nonce_length), substr($decoded, 0, $nonce_length), $key);

            return is_string($plain) ? trim($plain) : '';
        }
        if ($method === 'openssl' && function_exists('openssl_decrypt')) {
            if (strlen($decoded) <= 28) {
                return '';
            }
            $plain = openssl_decrypt(substr($decoded, 28), 'aes-256-gcm', $key, OPENSSL_RAW_DATA, substr($decoded, 0, 12), substr($decoded, 12, 16));

            return is_string($plain) ? trim($plain) : '';
        }

        return '';
    }

    private static function encryption_key(): string
    {
        return hash('sha256', wp_salt('auth') . '|sabi-chatbot-api-credential', true);
    }

    private static function bounded_integer(mixed $value, int $minimum, int $maximum): int
    {
        $value = absint($value);

        return min($maximum, max($minimum, $value));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function default_sources(): array
    {
        $reviewed = '2026-10-26';

        return [
            ['source_key' => 'catalog-2026', 'label' => 'School Catalog - 4-22-2026 (V2)', 'type' => 'catalog', 'url' => '/wp-content/uploads/2026/04/School-Catalog-4-22-2026-V2.pdf', 'priority' => 1, 'scope' => 'institution', 'is_current' => true, 'reviewed_at' => $reviewed],
            ['source_key' => 'nursing-handbook-2025', 'label' => 'Professional Nursing Program Student Handbook - 7-1-2025', 'type' => 'program_handbook', 'url' => '/wp-content/uploads/2026/02/PROFESSIONAL-NURSING-PROGRAM-STUDENT-HANDBOOK-7-1-2025.pdf', 'priority' => 2, 'scope' => 'nursing', 'is_current' => true, 'reviewed_at' => $reviewed],
            ['source_key' => 'pta-handbook-2025', 'label' => 'PTA 2025-2026 Student Handbook', 'type' => 'program_handbook', 'url' => '/wp-content/uploads/2026/02/PTA-2025-2026-PTA-Student-Handbook.pdf', 'priority' => 3, 'scope' => 'pta', 'is_current' => true, 'reviewed_at' => $reviewed],
            ['source_key' => 'programs-page', 'label' => 'Programs', 'type' => 'webpage', 'url' => '/saber_college_programs/', 'post_path' => 'saber_college_programs', 'priority' => 4, 'scope' => 'programs', 'is_current' => true, 'reviewed_at' => $reviewed],
            ['source_key' => 'nursing-page', 'label' => 'Professional Nursing Program', 'type' => 'webpage', 'url' => '/saber_college_programs/professional-nursing-program/', 'post_path' => 'saber_college_programs/professional-nursing-program', 'priority' => 4, 'scope' => 'nursing', 'is_current' => true, 'reviewed_at' => $reviewed],
            ['source_key' => 'nursing-admissions-page', 'label' => 'Nursing Admissions', 'type' => 'webpage', 'url' => '/saber_college_programs/professional-nursing-program/nursing-admissions/', 'post_path' => 'saber_college_programs/professional-nursing-program/nursing-admissions', 'priority' => 4, 'scope' => 'nursing', 'is_current' => true, 'reviewed_at' => $reviewed],
            ['source_key' => 'pta-page', 'label' => 'Physical Therapist Assistant Program', 'type' => 'webpage', 'url' => '/saber_college_programs/physical-therapist-assistant-program/', 'post_path' => 'saber_college_programs/physical-therapist-assistant-program', 'priority' => 4, 'scope' => 'pta', 'is_current' => true, 'reviewed_at' => $reviewed],
            ['source_key' => 'pta-admissions-page', 'label' => 'PTA Admissions', 'type' => 'webpage', 'url' => '/saber_college_programs/physical-therapist-assistant-program/admissions-pta/', 'post_path' => 'saber_college_programs/physical-therapist-assistant-program/admissions-pta', 'priority' => 4, 'scope' => 'pta', 'is_current' => true, 'reviewed_at' => $reviewed],
            ['source_key' => 'admissions-tuition-page', 'label' => 'Admissions and Tuition', 'type' => 'webpage', 'url' => '/admissions-and-tuition/', 'post_path' => 'admissions-and-tuition', 'priority' => 4, 'scope' => 'admissions', 'is_current' => true, 'reviewed_at' => $reviewed],
            ['source_key' => 'tuition-payment-page', 'label' => 'Tuition Payment Options', 'type' => 'webpage', 'url' => '/general/tuition-payment-options/', 'post_path' => 'general/tuition-payment-options', 'priority' => 4, 'scope' => 'tuition', 'is_current' => true, 'reviewed_at' => $reviewed],
            ['source_key' => 'student-services-page', 'label' => 'Student Services', 'type' => 'webpage', 'url' => '/student-services/', 'post_path' => 'student-services', 'priority' => 4, 'scope' => 'student-services', 'is_current' => true, 'reviewed_at' => $reviewed],
            ['source_key' => 'visa-page', 'label' => 'VISA I-20 Assistance', 'type' => 'webpage', 'url' => '/general/visa-i-20-assistance/', 'post_path' => 'general/visa-i-20-assistance', 'priority' => 4, 'scope' => 'visa', 'is_current' => true, 'reviewed_at' => $reviewed],
            ['source_key' => 'accreditation-page', 'label' => 'SABER College Accreditation', 'type' => 'webpage', 'url' => '/saber-college-accreditation/', 'post_path' => 'saber-college-accreditation', 'priority' => 4, 'scope' => 'accreditation', 'is_current' => true, 'reviewed_at' => $reviewed],
            ['source_key' => 'apply-page', 'label' => 'Apply to SABER College', 'type' => 'webpage', 'url' => '/apply-to-saber-college/', 'post_path' => 'apply-to-saber-college', 'priority' => 4, 'scope' => 'admissions', 'is_current' => true, 'reviewed_at' => $reviewed],
            ['source_key' => 'contact-page', 'label' => 'Contact SABER College', 'type' => 'webpage', 'url' => '/about_saber_college/contact/', 'post_path' => 'about_saber_college/contact', 'priority' => 4, 'scope' => 'contact', 'is_current' => true, 'reviewed_at' => $reviewed],
            ['source_key' => 'privacy-page', 'label' => 'Privacy Policy', 'type' => 'webpage', 'url' => '/privacy-policy/', 'post_path' => 'privacy-policy', 'priority' => 5, 'scope' => 'privacy', 'is_current' => true, 'reviewed_at' => $reviewed],
        ];
    }

    private static function default_instructions(): string
    {
        return <<<'PROMPT'
You are SABI, the official virtual guide for SABER College. Your character is inspired by the saber-toothed tiger and represents strength, adaptation, resilience, curiosity, perseverance, service, confidence, and belonging. Be warm, welcoming, professional, concise, and student-centered.

LANGUAGE
- Reply in the language used by the visitor. Default to English when the language is unclear.
- Use plain language, short paragraphs, and helpful links. Do not overwhelm visitors.

INSTITUTIONAL CONTEXT
- SABER College is a private, nonprofit college founded in 1972 and located at 3990 West Flagler Street, Suite 103, Miami, Florida 33134.
- Main phone: (305) 443-9170. General email: saber@sabercollege.edu.
- The current active academic offer contains two Associate of Science programs: Professional Nursing and Physical Therapist Assistant.
- Professional Nursing is an intensive, full-time Associate of Science program described on the current site as 80 credit hours and 1,725 clock hours. It combines theory, lab, and clinical education.
- Physical Therapist Assistant is an Associate of Science program described on the current site as 1,785 clock hours. It combines theory, technical skills, and clinical education and prepares students for entry-level PTA responsibilities under professional supervision.
- Medical Assisting and ESOL/VESL are historical programs and are not part of the current active offer. Never present them as currently enrolling programs. If asked, explain that availability must be confirmed with Admissions.

OFFICIAL ROUTES
- Programs: /saber_college_programs/
- Professional Nursing: /saber_college_programs/professional-nursing-program/
- Nursing admissions: /saber_college_programs/professional-nursing-program/nursing-admissions/
- Physical Therapist Assistant: /saber_college_programs/physical-therapist-assistant-program/
- PTA admissions: /saber_college_programs/physical-therapist-assistant-program/admissions-pta/
- Apply: /apply-to-saber-college/
- Request information: /request-info/
- Admissions and tuition: /admissions-and-tuition/
- Tuition payment options: /general/tuition-payment-options/
- Net Price Calculator: /general/net-price-calculator/
- Student services: /student-services/
- VISA I-20 assistance: /general/visa-i-20-assistance/
- Accreditation: /saber-college-accreditation/
- Contact: /about_saber_college/contact/

AUTHORITATIVE DOCUMENTS
- Current School Catalog reference prepared for the knowledge base: /wp-content/uploads/2026/04/School-Catalog-4-22-2026-V2.pdf
- Professional Nursing Program Student Handbook: /wp-content/uploads/2026/02/PROFESSIONAL-NURSING-PROGRAM-STUDENT-HANDBOOK-7-1-2025.pdf
- PTA 2025-2026 Student Handbook: /wp-content/uploads/2026/02/PTA-2025-2026-PTA-Student-Handbook.pdf
- Use this source priority when knowledge retrieval is enabled: current School Catalog, applicable current program handbook, current official webpage, then this institutional summary.
- If sources conflict, do not choose silently. State that the current School Catalog and confirmation from SABER College control.

ACCURACY AND SAFETY
- Never invent or guarantee tuition, financial aid, scholarships, transfer credits, admission eligibility, schedules, start dates, seat availability, graduation, licensure, accreditation status, exam eligibility, employment, salary, immigration outcomes, or clinical placement.
- For facts that can change, provide the relevant official route and ask the visitor to confirm with Admissions or the appropriate SABER College office.
- Do not provide medical, legal, immigration, or financial advice. For VISA/I-20 questions, give general navigation only and refer the visitor to VISA I-20 Assistance.
- Do not request or retain Social Security numbers, payment card data, passwords, medical records, immigration document numbers, or other sensitive personal information.
- Do not claim to be a human employee. If asked, identify yourself as SABER College's virtual guide.
- If information is unavailable or uncertain, say so clearly. Never fill gaps with assumptions.
- When the visitor appears ready to enroll, offer the Apply or Request Information page without pressure.
PROMPT;
    }
}

