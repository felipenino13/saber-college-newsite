<?php

namespace SaberCollege\SabiChatbot;

if (!defined('ABSPATH')) {
    exit;
}

final class Leads
{
    public function register(): void
    {
        add_action('wp_ajax_sabi_chatbot_submit_lead', [$this, 'submit']);
        add_action('wp_ajax_nopriv_sabi_chatbot_submit_lead', [$this, 'submit']);
    }

    public function submit(): void
    {
        check_ajax_referer('sabi_chatbot_submit_lead', 'nonce');

        $settings = Settings::get();
        $language = sanitize_key(wp_unslash($_POST['language'] ?? 'en')) === 'es' ? 'es' : 'en';
        if (empty($settings['leads']['enabled'])) {
            $this->error(
                __('Lead capture is not available yet.', 'sabi-chatbot'),
                __('La captura de datos todavía no está disponible.', 'sabi-chatbot'),
                $language,
                403
            );
        }

        if (sanitize_text_field(wp_unslash($_POST['website'] ?? '')) !== '') {
            wp_send_json_success(['message' => $this->message(
                __('Thank you. SABER College received your request.', 'sabi-chatbot'),
                __('Gracias. SABER College recibió tu solicitud.', 'sabi-chatbot'),
                $language
            )]);
        }

        if (!$this->within_rate_limit()) {
            $this->error(
                __('Please wait a few minutes before sending another request.', 'sabi-chatbot'),
                __('Espera unos minutos antes de enviar otra solicitud.', 'sabi-chatbot'),
                $language,
                429
            );
        }

        $name = sanitize_text_field(wp_unslash($_POST['name'] ?? ''));
        $program = sanitize_key(wp_unslash($_POST['program'] ?? ''));
        $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
        $phone = sanitize_text_field(wp_unslash($_POST['phone'] ?? ''));
        $consent = sanitize_text_field(wp_unslash($_POST['consent'] ?? '')) === '1';
        $allowed_programs = ['professional-nursing', 'physical-therapist-assistant', 'undecided'];

        if (mb_strlen($name) < 2 || mb_strlen($name) > 120) {
            $this->error(__('Enter a valid name.', 'sabi-chatbot'), __('Ingresa un nombre válido.', 'sabi-chatbot'), $language);
        }
        if (!in_array($program, $allowed_programs, true)) {
            $this->error(__('Select a program of interest.', 'sabi-chatbot'), __('Selecciona un programa de interés.', 'sabi-chatbot'), $language);
        }
        if (!is_email($email)) {
            $this->error(__('Enter a valid email address.', 'sabi-chatbot'), __('Ingresa un correo electrónico válido.', 'sabi-chatbot'), $language);
        }

        $phone_digits = preg_replace('/\D+/', '', $phone);
        if (!is_string($phone_digits) || strlen($phone_digits) < 7 || strlen($phone_digits) > 15) {
            $this->error(__('Enter a valid phone number.', 'sabi-chatbot'), __('Ingresa un número de teléfono válido.', 'sabi-chatbot'), $language);
        }
        if (!$consent) {
            $this->error(__('Consent is required to send the request.', 'sabi-chatbot'), __('El consentimiento es obligatorio para enviar la solicitud.', 'sabi-chatbot'), $language);
        }

        $lead = [
            'name' => $name,
            'program' => $program,
            'email' => $email,
            'phone' => $phone,
            'language' => $language,
            'source_url' => $this->source_path(wp_unslash($_POST['source_url'] ?? '')),
        ];
        $lead_id = Database::insert($lead);
        if (is_wp_error($lead_id)) {
            $this->error(
                __('The request could not be saved. Please contact SABER College.', 'sabi-chatbot'),
                __('No se pudo guardar la solicitud. Comunícate con SABER College.', 'sabi-chatbot'),
                $language,
                500
            );
        }

        if (!empty($settings['leads']['notify_by_email']) && is_email($settings['leads']['notification_email'])) {
            $this->notify($settings['leads']['notification_email'], $lead);
        }

        wp_send_json_success(['message' => $this->message(
            __('Thank you. SABER College received your request and can follow up using the information provided.', 'sabi-chatbot'),
            __('Gracias. SABER College recibió tu solicitud y podrá contactarte con la información proporcionada.', 'sabi-chatbot'),
            $language
        )]);
    }

    private function within_rate_limit(): bool
    {
        $address = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        $key = 'sabi_lead_rate_' . substr(hash_hmac('sha256', $address, wp_salt('nonce')), 0, 32);
        $count = (int) get_transient($key);
        if ($count >= 5) {
            return false;
        }

        set_transient($key, $count + 1, 10 * MINUTE_IN_SECONDS);

        return true;
    }

    private function source_path(mixed $source): string
    {
        $source = esc_url_raw((string) $source);
        $parts = wp_parse_url($source);
        $site = wp_parse_url(home_url('/'));
        if (!is_array($parts) || !is_array($site) || ($parts['host'] ?? '') !== ($site['host'] ?? '')) {
            return '';
        }

        return substr((string) ($parts['path'] ?? '/'), 0, 500);
    }

    /**
     * @param array<string, mixed> $lead Sanitized lead data.
     */
    private function notify(string $recipient, array $lead): void
    {
        $programs = [
            'professional-nursing' => 'Professional Nursing',
            'physical-therapist-assistant' => 'Physical Therapist Assistant',
            'undecided' => 'Undecided',
        ];
        $body = implode("\n", [
            'A new lead was submitted through SABI Chatbot.',
            '',
            'Name: ' . $lead['name'],
            'Program: ' . ($programs[$lead['program']] ?? $lead['program']),
            'Email: ' . $lead['email'],
            'Phone: ' . $lead['phone'],
            '',
            'Review the record in WordPress under SABI Chatbot > Leads.',
        ]);

        wp_mail($recipient, '[SABI] New information request', $body);
    }

    private function message(string $english, string $spanish, string $language): string
    {
        return $language === 'es' ? $spanish : $english;
    }

    private function error(string $english, string $spanish, string $language, int $status = 400): never
    {
        wp_send_json_error(['message' => $this->message($english, $spanish, $language)], $status);
    }
}
