<?php
/**
 * Integration Test für das PHP-basierte iFrame System
 * Verwendet die GET-basierte Authentifizierung (kein API-Call mehr nötig)
 */
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket-System Integration Test</title>
    <style>
        body {
            font-family: system-ui, -apple-system, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f8fafc;
        }
        .container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0 0 10px 0;
            font-size: 2em;
        }
        .content {
            padding: 30px;
        }
        .test-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #f9fafb;
        }
        .test-section h3 {
            margin: 0 0 15px 0;
            color: #374151;
        }
        .success {
            background: #d1fae5;
            border: 1px solid #10b981;
            color: #065f46;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
        .error {
            background: #fee2e2;
            border: 1px solid #ef4444;
            color: #991b1b;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
        .info {
            background: #dbeafe;
            border: 1px solid #3b82f6;
            color: #1e40af;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
        .code {
            background: #1f2937;
            color: #f9fafb;
            padding: 15px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            overflow-x: auto;
            margin: 10px 0;
        }
        iframe {
            width: 100%;
            height: 600px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            margin-top: 15px;
        }
        .test-urls {
            display: grid;
            gap: 10px;
            margin: 15px 0;
        }
        .test-url {
            padding: 10px;
            background: #f3f4f6;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
            word-break: break-all;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin: 5px;
        }
        .btn:hover {
            background: #2563eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎫 Ticket-System Integration Test</h1>
            <p>PHP-basierte GET-Integration (Keine API-Calls erforderlich)</p>
        </div>

        <div class="content">
            <!-- Test-Konfiguration -->
            <div class="test-section">
                <h3>📋 Test-Konfiguration</h3>
                <?php
                $apiKey = 'mein-super-sicherer-api-key-2024';
                $baseUrl = 'http://localhost:8000';
                $userToken = 'O3bAf7I79A3aplTCKW2v33kNrrlxdK3up8ZYIBjgiINbootbUjyZxbrPg5cCEPp2';
                $userEmail = 'customer@example.com';
                ?>
                
                <div class="info">
                    <strong>✅ Konfiguration geladen:</strong><br>
                    • API Key: <?php echo htmlspecialchars(substr($apiKey, 0, 10) . '...'); ?><br>
                    • Base URL: <?php echo htmlspecialchars($baseUrl); ?><br>
                    • User Token: <?php echo htmlspecialchars(substr($userToken, 0, 8) . '...' . substr($userToken, -8)); ?><br>
                    • User Email: <?php echo htmlspecialchars($userEmail); ?>
                </div>
            </div>

            <!-- Integration-Test mit PHP -->
            <div class="test-section">
                <h3>🔧 PHP Integration Test</h3>
                
                <?php
                // Test 1: Integration-Klasse prüfen
                if (!file_exists('./ticket-integration.php')) {
                    echo '<div class="error"><strong>❌ Fehler:</strong> ticket-integration.php nicht gefunden!</div>';
                } else {
                    require_once './ticket-integration.php';
                    echo '<div class="success"><strong>✅ Integration-Klasse gefunden</strong></div>';
                    
                    // Test 2: iFrame-URL generieren
                    try {
                        $ticketAuth = new TicketSystemAuth($apiKey, $baseUrl);
                        $iframeUrl = $ticketAuth->generateIframeUrl($userToken, $userEmail, '/projects');
                        
                        echo '<div class="success">';
                        echo '<strong>✅ iFrame-URL erfolgreich generiert:</strong><br>';
                        echo '<div class="code">' . htmlspecialchars($iframeUrl) . '</div>';
                        echo '</div>';
                        
                    } catch (Exception $e) {
                        echo '<div class="error"><strong>❌ Fehler bei URL-Generierung:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
                        $iframeUrl = null;
                    }
                }
                ?>
            </div>

            <!-- Direkte URL-Tests -->
            <div class="test-section">
                <h3>🌐 Direkte URL-Tests</h3>
                
                <p>Diese URLs können direkt getestet werden:</p>
                
                <div class="test-urls">
                    <?php
                    $testUrls = [
                        'Customer Login' => '/iframe/login?' . http_build_query([
                            'token' => $userToken,
                            'email' => $userEmail,
                            'api_key' => $apiKey,
                            'redirect' => '/projects'
                        ]),
                        'Developer Dashboard' => '/iframe/login?' . http_build_query([
                            'token' => $userToken,
                            'email' => $userEmail,
                            'api_key' => $apiKey,
                            'redirect' => '/tickets'
                        ]),
                        'Direkter Ticket-Zugriff' => '/iframe/login?' . http_build_query([
                            'token' => $userToken,
                            'email' => $userEmail,
                            'api_key' => $apiKey,
                            'redirect' => '/tickets/1'
                        ])
                    ];
                    
                    foreach ($testUrls as $name => $url) {
                        $fullUrl = $baseUrl . $url;
                        echo '<div class="test-url">';
                        echo '<strong>' . htmlspecialchars($name) . ':</strong><br>';
                        echo '<a href="' . htmlspecialchars($fullUrl) . '" target="_blank" class="btn">🔗 Test ' . htmlspecialchars($name) . '</a>';
                        echo '<br><small>' . htmlspecialchars($fullUrl) . '</small>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>

            <!-- Live iFrame Demo -->
            <?php if (isset($iframeUrl) && $iframeUrl): ?>
            <div class="test-section">
                <h3>📺 Live iFrame Demo</h3>
                
                <div class="info">
                    <strong>ℹ️ iFrame wird geladen...</strong><br>
                    Falls das iFrame leer bleibt, prüfen Sie die Browser-Konsole auf Fehler.
                </div>
                
                <iframe src="<?php echo htmlspecialchars($iframeUrl); ?>" frameborder="0">
                    <p>Ihr Browser unterstützt keine iFrames.</p>
                </iframe>
            </div>
            <?php endif; ?>

            <!-- Integration-Code Beispiele -->
            <div class="test-section">
                <h3>💻 Integration-Code Beispiele</h3>
                
                <h4>One-Liner Integration:</h4>
                <div class="code">
&lt;?php
// Super-einfache Integration
echo ticketSystemIframe(
    '<?php echo $apiKey; ?>', 
    '<?php echo $baseUrl; ?>', 
    $user['iframe_token'], 
    $user['email']
);
?&gt;
                </div>

                <h4>Objekt-orientierte Integration:</h4>
                <div class="code">
&lt;?php
require_once 'ticket-integration.php';

$ticketAuth = new TicketSystemAuth('<?php echo $apiKey; ?>', '<?php echo $baseUrl; ?>');
$iframeUrl = $ticketAuth->generateIframeUrl($userToken, $userEmail, '/projects');

echo '&lt;iframe src="' . $iframeUrl . '" width="100%" height="600">&lt;/iframe>';
?&gt;
                </div>
            </div>

            <!-- System-Status -->
            <div class="test-section">
                <h3>🔍 System-Status</h3>
                
                <div class="info">
                    <strong>📊 PHP-basierte Integration aktiv:</strong><br>
                    • ✅ Keine JavaScript-Abhängigkeiten<br>
                    • ✅ Server-seitige Authentifizierung<br>
                    • ✅ GET-basierte iFrame-URLs<br>
                    • ✅ Sichere API-Key-Validierung<br>
                    • ✅ Automatische Session-Erstellung<br>
                    • ✅ Role-basierte Weiterleitung
                </div>
            </div>
        </div>
    </div>
</body>
</html>