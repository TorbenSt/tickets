<?php
/**
 * Ticket System PHP Integration
 * 
 * Einfache PHP-Klasse für die Integration des Ticket-Systems in bestehende Backends
 * 
 * @author Ticket System
 * @version 1.0
 */

class TicketSystemAuth 
{
    private string $apiKey;
    private string $baseUrl;
    
    public function __construct(string $apiKey, string $baseUrl)
    {
        $this->apiKey = $apiKey;
        $this->baseUrl = rtrim($baseUrl, '/');
    }
    
    /**
     * Authentifiziert einen User über die API und gibt iFrame-URL zurück
     * 
     * @param string $token User Token (64 Zeichen)
     * @param string $email User Email
     * @return array ['success' => bool, 'iframe_url' => string, 'error' => string]
     */
    public function authenticateUser(string $token, string $email): array
    {
        // Input-Validierung
        if (strlen($token) !== 64) {
            return ['success' => false, 'error' => 'Invalid token length (must be 64 characters)'];
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Invalid email format'];
        }
        
        // API-Call durchführen
        $result = $this->callAuthApi($token, $email);
        
        if ($result['success']) {
            return [
                'success' => true,
                'iframe_url' => $this->baseUrl . ($result['data']['redirect_url'] ?? '/projects'),
                'user' => $result['data']['user'] ?? null
            ];
        }
        
        return [
            'success' => false,
            'error' => $result['data']['error'] ?? 'Authentication failed (HTTP ' . $result['http_code'] . ')'
        ];
    }
    
    /**
     * Generiert eine direkte iFrame-URL (GET-basiert, einfachste Methode)
     * 
     * @param string $token User Token (64 Zeichen)
     * @param string $email User Email
     * @param string $redirect Redirect-Pfad nach Login (optional)
     * @return string Vollständige iFrame URL
     */
    public function generateIframeUrl(string $token, string $email, string $redirect = null): string
    {
        $params = [
            'token' => $token,
            'email' => $email,
            'api_key' => $this->apiKey
        ];
        
        if ($redirect) {
            $params['redirect'] = $redirect;
        }
        
        return $this->baseUrl . '/iframe/login?' . http_build_query($params);
    }
    
    /**
     * Ruft die Ticket-System Authentication API auf
     * 
     * @param string $token
     * @param string $email
     * @return array
     */
    private function callAuthApi(string $token, string $email): array
    {
        $postData = json_encode(['token' => $token, 'email' => $email]);
        
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => [
                    'Content-Type: application/json',
                    'X-API-Key: ' . $this->apiKey,
                    'Accept: application/json'
                ],
                'content' => $postData,
                'ignore_errors' => true,
                'timeout' => 10
            ]
        ]);
        
        $response = @file_get_contents($this->baseUrl . '/api/iframe/authenticate', false, $context);
        
        if ($response === false) {
            return ['success' => false, 'data' => ['error' => 'Network error'], 'http_code' => 0];
        }
        
        $data = json_decode($response, true) ?? [];
        
        // HTTP Status Code extrahieren
        $httpCode = 0;
        if (isset($http_response_header[0])) {
            preg_match('/HTTP\/\d\.\d\s+(\d+)/', $http_response_header[0], $matches);
            $httpCode = intval($matches[1] ?? 0);
        }
        
        return [
            'success' => $httpCode === 200 && isset($data['success']) && $data['success'],
            'data' => $data,
            'http_code' => $httpCode
        ];
    }
    
    /**
     * Einfacher HTML-Helper für die iFrame-Erstellung
     * 
     * @param string $token
     * @param string $email
     * @param array $options HTML-Attribute für das iFrame
     * @return string HTML iFrame Tag
     */
    public function renderIframe(string $token, string $email, array $options = []): string
    {
        $url = $this->generateIframeUrl($token, $email, $options['redirect'] ?? null);
        
        $attributes = array_merge([
            'src' => $url,
            'width' => '100%',
            'height' => '800',
            'frameborder' => '0',
            'style' => 'border: 1px solid #ddd; border-radius: 4px;'
        ], $options);
        
        unset($attributes['redirect']); // Redirect ist kein HTML-Attribut
        
        $attrString = '';
        foreach ($attributes as $key => $value) {
            $attrString .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
        }
        
        return '<iframe' . $attrString . '></iframe>';
    }
}

/**
 * Helper-Funktion für maximale Einfachheit
 * 
 * @param string $apiKey
 * @param string $baseUrl
 * @param string $token
 * @param string $email
 * @param array $options
 * @return string HTML iFrame
 */
function ticketSystemIframe(string $apiKey, string $baseUrl, string $token, string $email, array $options = []): string
{
    $auth = new TicketSystemAuth($apiKey, $baseUrl);
    return $auth->renderIframe($token, $email, $options);
}

/*
=== USAGE EXAMPLES ===

// Beispiel 1: Einfachste Verwendung (One-Liner)
echo ticketSystemIframe(
    'mein-super-sicherer-api-key-2024',
    'http://localhost:8000',
    $userToken,
    $userEmail
);

// Beispiel 2: Mit Options
echo ticketSystemIframe(
    'mein-super-sicherer-api-key-2024',
    'http://localhost:8000',
    $userToken,
    $userEmail,
    ['height' => '900', 'redirect' => '/tickets']
);

// Beispiel 3: Objekt-orientiert mit Fehlerbehandlung
$ticketAuth = new TicketSystemAuth('api-key', 'http://localhost:8000');

// Server-seitige Authentifizierung (empfohlen für Debugging)
$result = $ticketAuth->authenticateUser($userToken, $userEmail);
if ($result['success']) {
    echo '<iframe src="' . $result['iframe_url'] . '" width="100%" height="800"></iframe>';
} else {
    echo '<p>Fehler: ' . $result['error'] . '</p>';
}

// Beispiel 4: Direkte URL-Generierung
$iframeUrl = $ticketAuth->generateIframeUrl($userToken, $userEmail, '/projects');
echo '<iframe src="' . htmlspecialchars($iframeUrl) . '" width="100%" height="800"></iframe>';

// Beispiel 5: HTML-Helper
echo $ticketAuth->renderIframe($userToken, $userEmail, [
    'width' => '100%',
    'height' => '900',
    'style' => 'border: 2px solid #007acc; border-radius: 8px;',
    'redirect' => '/projects'
]);
*/