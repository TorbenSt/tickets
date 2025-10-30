<?php 
    ini_set('log_errors', 1);
    ini_set('error_log', 'error.log');

    //Fehler anzeigen
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    session_start();
    if ($_SESSION['loggiboggi'] != 'eingelogt' || empty($_SESSION['user_id'])) {
        header("Location: login.php");
        die();
    }

    // Konfiguration für Ticket-System
    $IFRAME_API_KEY = 'mein-super-sicherer-api-key-2024'; // Ungehashter Key!
    $TICKET_SYSTEM_URL = 'http://localhost:8000';
    $TICKET_SYSTEM_API_URL = $TICKET_SYSTEM_URL . '/api';

    // Funktion zum Abrufen der iframe-URL mit besserer Fehlerbehandlung
    function getIframeUrl($userId, $apiKey, $apiUrl, $baseUrl) {
        $postData = json_encode([
            'user_id' => (int)$userId,
            'api_key' => $apiKey,
            'parent_domain' => 'http://' . $_SERVER['HTTP_HOST'],
            'expires_in' => 120
        ]);
        
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n" .
                           "Accept: application/json\r\n",
                'content' => $postData,
                'timeout' => 10
            ]
        ]);
        
        $response = @file_get_contents($apiUrl . '/iframe/token', false, $context);
        
        // Debug-Informationen
        if ($response === false) {
            error_log('API Request failed. URL: ' . $apiUrl . '/iframe/token');
            error_log('POST Data: ' . $postData);
            
            $error = error_get_last();
            if ($error) {
                error_log('Last error: ' . print_r($error, true));
            }
            
            return false;
        }
        
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('JSON Decode Error: ' . json_last_error_msg());
            error_log('Response: ' . $response);
            return false;
        }
        
        if (isset($data['error'])) {
            error_log('API Error: ' . $data['error']);
            return false;
        }
        
        return $data['iframe_url'] ?? false;
    }

    // iframe-URL abrufen
    $userId = $_SESSION['user_id'] ?? 1;
    $iframeUrl = getIframeUrl($userId, $IFRAME_API_KEY, $TICKET_SYSTEM_API_URL, $TICKET_SYSTEM_URL);
    $errorMessage = '';
    
    if (!$iframeUrl) {
        $errorMessage = 'Ticket-System konnte nicht geladen werden. Prüfen Sie die error.log für Details.';
    }

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <?php require_once 'templates/head.php' ?>
    <title>MFB - Ticket System</title>
</head>
<body>
    <div class="container-fluid" id="backend">
        <div class="row">
            <!-- SiteMenu -->
            <?php require_once "templates/sitemenu.php" ?>

            <div class="col-md-10">
                <div class="kasten mainset px-3 py-5">
                    <div class="p-5 shad">
                        <h2 class="my-3 pb-3 font-weight-bold">Ticket System</h2>
                        
                        <?php if ($errorMessage): ?>
                            <div class="alert alert-warning" role="alert">
                                <i class="fa fa-exclamation-triangle mr-2"></i>
                                <?php echo htmlspecialchars($errorMessage); ?>
                            </div>
                            <div class="text-center py-5">
                                <button onclick="window.location.reload()" class="btn btn-primary">
                                    <i class="fa fa-refresh mr-2"></i>Erneut versuchen
                                </button>
                            </div>
                            
                            <!-- Debug-Info (nur in Development) -->
                            <details class="mt-3">
                                <summary>Debug-Informationen</summary>
                                <div class="mt-2 p-3 bg-light">
                                    <strong>User ID:</strong> <?php echo htmlspecialchars($userId); ?><br>
                                    <strong>API URL:</strong> <?php echo htmlspecialchars($TICKET_SYSTEM_API_URL . '/iframe/token'); ?><br>
                                    <strong>Domain:</strong> <?php echo htmlspecialchars($_SERVER['HTTP_HOST']); ?><br>
                                    <strong>Log-Datei:</strong> Prüfen Sie error.log für weitere Details
                                </div>
                            </details>
                        <?php else: ?>
                            <div style="width: 100%; border: 1px solid #e5e7eb; border-radius: 8px; background: white;">
                                <iframe 
                                    src="<?php echo htmlspecialchars($iframeUrl); ?>"
                                    width="100%" 
                                    height="700"
                                    frameborder="0"
                                    id="ticket-iframe"
                                    style="border-radius: 8px;"
                                    sandbox="allow-same-origin allow-scripts allow-forms allow-top-navigation">
                                </iframe>
                            </div>
                        <?php endif; ?>
                        
                    </div>  
                </div>
            </div>
        </div>
    </div>

    <script>
        // Automatische Höhen-Anpassung
        window.addEventListener('message', function(event) {
            // Sicherheitscheck für Origin
            if (event.origin !== '<?php echo $TICKET_SYSTEM_URL; ?>') return;
            
            if (event.data.type === 'resize') {
                const iframe = document.getElementById('ticket-iframe');
                if (iframe) {
                    iframe.style.height = Math.max(600, event.data.height) + 'px';
                }
            }
        });

        // iframe-Loading Events
        const iframe = document.getElementById('ticket-iframe');
        if (iframe) {
            iframe.addEventListener('load', function() {
                console.log('Ticket-System iframe erfolgreich geladen');
            });

            iframe.addEventListener('error', function() {
                console.error('Fehler beim Laden des Ticket-Systems');
            });
        }
    </script>
</body>
</html>