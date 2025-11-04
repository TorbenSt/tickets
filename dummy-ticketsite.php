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
    $IFRAME_API_KEY = 'mein-super-sicherer-api-key-2024';
    $TICKET_SYSTEM_URL = 'http://localhost:8000';
    
    // Integration-Klasse laden
    require_once 'public/ticket-integration.php';
    
    // User-Daten aus Backend-Datenbank
    $userId = $_SESSION['user_id'] ?? 1;
    
    // TODO: Diese Daten aus Ihrer Backend-Datenbank laden
    // Beispiel-Query: SELECT email, iframe_user_token FROM users WHERE id = ?
    $userEmail = 'customer@example.com'; // Aus Ihrer DB laden
    $userToken = 'O3bAf7I79A3aplTCKW2v33kNrrlxdK3up8ZYIBjgiINbootbUjyZxbrPg5cCEPp2'; // Aus Ihrer DB laden
    
    /* 
    // Real-World Beispiel:
    $stmt = $pdo->prepare("SELECT email, iframe_user_token FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    $userEmail = $user['email'] ?? null;
    $userToken = $user['iframe_user_token'] ?? null;
    */
    
    // Ticket-System Integration
    $errorMessage = '';
    $iframeHtml = '';
    
    if (!$userEmail || !$userToken) {
        $errorMessage = 'Benutzer hat keinen gültigen Ticket-System-Token. Bitte kontaktieren Sie den Administrator.';
    } else {
        try {
            // PHP-basierte Integration (super einfach!)
            $iframeHtml = ticketSystemIframe(
                $IFRAME_API_KEY,
                $TICKET_SYSTEM_URL,
                $userToken,
                $userEmail,
                [
                    'width' => '100%',
                    'height' => '800',
                    'style' => 'border: 1px solid #e5e7eb; border-radius: 8px; background: white;'
                ]
            );
        } catch (Exception $e) {
            $errorMessage = 'Fehler bei der Ticket-System Integration: ' . $e->getMessage();
        }
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
                                    <strong>Email:</strong> <?php echo htmlspecialchars($userEmail ?? 'Nicht gefunden'); ?><br>
                                    <strong>Token verfügbar:</strong> <?php echo $userToken ? 'Ja' : 'Nein'; ?><br>
                                    <strong>System:</strong> PHP-basierte Integration
                                </div>
                            </details>
                        <?php elseif ($iframeHtml): ?>
                            <!-- PHP-basierte Ticket-System Integration -->
                            <div class="alert alert-success mb-3" role="alert">
                                <i class="fa fa-check-circle mr-2"></i>
                                Authentifiziert als: <strong><?php echo htmlspecialchars($userEmail); ?></strong>
                            </div>
                            
                            <!-- Das generierte iFrame -->
                            <?php echo $iframeHtml; ?>
                            
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="fa fa-info-circle mr-1"></i>
                                    PHP-Integration aktiv • Automatische Authentifizierung
                                </small>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info" role="alert">
                                <i class="fa fa-spinner fa-spin mr-2"></i>
                                Ticket-System wird initialisiert...
                            </div>
                        <?php endif; ?>
                        
                    
            </div>
        </div>
    </div>

    <script>
        // Optional: Message-Handler für iFrame-Kommunikation
        window.addEventListener('message', function(event) {
            // Sicherheitscheck
            if (event.origin !== '<?php echo $TICKET_SYSTEM_URL; ?>') return;
            
            // Höhen-Anpassung
            if (event.data && event.data.type === 'resize') {
                const iframe = document.querySelector('iframe[src*="iframe/login"]');
                if (iframe && event.data.height) {
                    iframe.style.height = Math.max(600, event.data.height) + 'px';
                }
            }
        });
        
        // Loading-Feedback
        document.addEventListener('DOMContentLoaded', function() {
            const iframe = document.querySelector('iframe[src*="iframe/login"]');
            if (iframe) {
                iframe.addEventListener('load', function() {
                    console.log('✅ Ticket-System erfolgreich geladen (PHP-Integration)');
                });
            }
        });
    </script>
</body>
</html>