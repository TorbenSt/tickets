# 🎫 Ticket-System PHP iFrame Integration

## ✨ Einfachste Integration - One-Liner

```php
<?php
require_once 'ticket-integration.php';

// User-Daten aus Ihrer Datenbank laden
$userToken = getUserToken($_SESSION['user_id']);
$userEmail = getUserEmail($_SESSION['user_id']);

// One-Liner Integration
echo ticketSystemIframe(
    'your-api-key', 
    'https://tickets.yourdomain.com', 
    $userToken, 
    $userEmail
);
?>
```

**Das war's! 🚀** Das Ticket-System ist integriert.

## 📋 Integration Workflow

1. **Backend-User** meldet sich in seinem System an
2. **Backend** lädt `email` und `iframe_user_token` aus der eigenen Datenbank  
3. **PHP** generiert iFrame-URL mit GET-Parametern
4. **Ticket-System** authentifiziert automatisch über URL-Parameter
5. **User wird eingeloggt** und zur passenden Seite weitergeleitet

## 🗄️ Datenbank-Schema

Fügen Sie diese Spalte zu Ihrer `users` Tabelle hinzu:

```sql
ALTER TABLE users ADD COLUMN iframe_user_token VARCHAR(64) NULL;
```

## 🔑 Token-Generierung

Tokens können über das Admin-Interface des Ticket-Systems generiert werden:

```bash
# Als Developer einloggen und Token für User generieren
curl -X POST http://ticket-system.local/api/admin/users/{user_id}/generate-token \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

## 📁 Integration Files

1. **Kopieren Sie** `ticket-integration.php` in Ihr Projekt
2. **Passen Sie** API-Key und Base-URL an  
3. **Fertig!**

## 🛠️ Integration-Methoden

### Method 1: One-Liner (Empfohlen)

```php
<?php
require_once 'ticket-integration.php';

// User-Daten aus DB laden
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT email, iframe_user_token FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// One-Liner Integration
echo ticketSystemIframe(
    'your-api-key',                    // API Key
    'https://tickets.yourdomain.com',  // Base URL
    $user['iframe_user_token'],        // User Token
    $user['email'],                    // User Email
    ['height' => '900']                // Optional: iFrame Optionen
);
?>
```

### Method 2: Objekt-orientiert (mit Fehlerbehandlung)

```php
<?php
require_once 'ticket-integration.php';

$ticketAuth = new TicketSystemAuth('your-api-key', 'https://tickets.yourdomain.com');

// Server-seitige Authentifizierung (empfohlen für Debugging)
$result = $ticketAuth->authenticateUser($userToken, $userEmail);

if ($result['success']) {
    echo '<iframe src="' . $result['iframe_url'] . '" width="100%" height="800"></iframe>';
    echo '<p>Eingeloggt als: ' . $result['user']['name'] . '</p>';
} else {
    echo '<p>Fehler: ' . $result['error'] . '</p>';
}
?>
```

### Method 3: HTML-Helper

```php
<?php
$ticketAuth = new TicketSystemAuth('your-api-key', 'https://tickets.yourdomain.com');

echo $ticketAuth->renderIframe($userToken, $userEmail, [
    'width' => '100%',
    'height' => '900',
    'style' => 'border: 2px solid #007acc; border-radius: 8px;',
    'redirect' => '/projects'  // Optional: Redirect nach Login
]);
?>
```

## Sicherheitsaspekte

### API-Key Schutz
- Der API-Key wird als gehashter Wert in der `.env` gespeichert
- Nur der Klartext-Key wird an das Backend weitergegeben

### Token-Sicherheit
- Tokens sind 64 Zeichen lange, kryptographisch sichere Zufallsstrings
- Tokens können jederzeit widerrufen werden
- Verwendung wird geloggt für Sicherheitsaudits

### Rate Limiting
- 10 Authentifizierungsversuche pro Minute pro IP
- Verhindert Brute-Force-Angriffe

## User-Management

### Token erstellen
```php
// Im Ticket-System Admin-Bereich
$user = User::find($userId);
$token = $user->generateIframeUserToken();

// Token in Ihr Backend-System übertragen
// UPDATE your_users SET iframe_user_token = ? WHERE id = ?
```

### Token widerrufen
```php
$user->revokeIframeToken();
// Auch in Ihrem Backend auf NULL setzen
```

## Fehlerbehandlung

### Häufige Probleme

1. **"Invalid API key"**
   - API-Key in `.env` prüfen
   - Hash korrekt generiert?

2. **"Invalid credentials"**
   - Token existiert im Ticket-System?
   - Email-Adresse stimmt überein?

3. **"User configuration error"**
   - User hat keine Firma zugewiesen
   - User-Rolle nicht gesetzt

### Debugging

Logs werden in `storage/logs/laravel.log` gespeichert:

```bash
tail -f storage/logs/laravel.log | grep iframe
```

## Anpassungen

### Eigene Styles
Das iFrame kann mit CSS gestylt werden:

```css
#ticket-iframe {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}
```

### Höhen-Anpassung
Das Ticket-System sendet automatisch Resize-Events:

```javascript
window.addEventListener('message', function(event) {
    if (event.origin !== 'https://tickets.yourdomain.com') return;
    
    if (event.data.type === 'resize') {
        document.getElementById('ticket-iframe').style.height = 
            Math.max(600, event.data.height) + 'px';
    }
});
```

## Support

Bei Problemen oder Fragen zur Integration:

1. Logs prüfen (`storage/logs/laravel.log`)
2. API-Aufrufe mit curl testen
3. Browser-Entwicklertools verwenden
4. Token-Status im Admin-Bereich prüfen