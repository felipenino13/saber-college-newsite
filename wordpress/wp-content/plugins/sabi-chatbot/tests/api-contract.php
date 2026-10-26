<?php

use SaberCollege\SabiChatbot\Api;

if (!defined('WP_CLI') || !WP_CLI) {
    exit('This contract test must be run with WP-CLI.');
}

$administrators = get_users([
    'role' => 'administrator',
    'number' => 1,
    'fields' => 'ids',
]);

if (!$administrators) {
    WP_CLI::error('No administrator is available for the preview test.');
}

wp_set_current_user((int) $administrators[0]);
$GLOBALS['sabi_contract_calls'] = 0;

add_filter(
    'pre_http_request',
    static function ($preempt, $arguments, $url) {
        if ($url !== 'https://api.openai.com/v1/responses') {
            return $preempt;
        }

        $GLOBALS['sabi_contract_calls']++;
        if ($GLOBALS['sabi_contract_calls'] === 1) {
            return new WP_Error('temporary_contract_failure', 'Synthetic temporary transport failure.');
        }

        $request = json_decode((string) ($arguments['body'] ?? ''), true);
        if (!is_array($request) || empty($request['instructions']) || empty($request['input'])) {
            return new WP_Error('invalid_contract', 'The API request does not contain instructions and input.');
        }

        return [
            'headers' => [],
            'body' => wp_json_encode([
                'id' => 'resp_sabi_contract_test',
                'output' => [[
                    'type' => 'message',
                    'content' => [[
                        'type' => 'output_text',
                        'text' => 'Respuesta verificada de prueba [S1].',
                    ]],
                ]],
                'usage' => [
                    'input_tokens' => 120,
                    'output_tokens' => 18,
                    'total_tokens' => 138,
                ],
            ]),
            'response' => ['code' => 200, 'message' => 'OK'],
            'cookies' => [],
            'filename' => null,
        ];
    },
    10,
    3
);

register_shutdown_function(static function (): void {
    global $wpdb;

    $wpdb->delete(
        $wpdb->prefix . 'sabi_chatbot_usage',
        ['response_id' => 'resp_sabi_contract_test'],
        ['%s']
    );

    if (($GLOBALS['sabi_contract_calls'] ?? 0) !== 2) {
        error_log('SABI API contract test did not perform exactly one controlled retry.');
    }
});

$_REQUEST['nonce'] = wp_create_nonce('sabi_chatbot_message');
$_POST = [
    'nonce' => $_REQUEST['nonce'],
    'message' => '¿Cuáles son los requisitos de graduación?',
    'language' => 'es',
    'session_id' => 'sabi-contract-test-20261026',
    'history' => '[]',
];

(new Api())->respond();
