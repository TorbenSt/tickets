TICKET SYSTEM PHP IFRAME INTEGRATION


SIMPLEST INTEGRATION – ONE-LINER

<?php
require_once 'ticket-integration.php';

// Load user data from your own database
$userToken = getUserToken($_SESSION['user_id']);
$userEmail = getUserEmail($_SESSION['user_id']);

// One-liner integration
echo ticketSystemIframe(
    'your-api-key',
    'https://tickets.yourdomain.com',
    $userToken,
    $userEmail
);
?>
That's it!
The ticket system is now integrated.


INTEGRATION WORKFLOW

1) Backend user logs into your system
2) Backend loads email and iframe_user_token from its own database
3) PHP generates the iframe URL with GET parameters
4) Ticket system authenticates automatically via URL parameters
5) User is logged in and redirected to the appropriate page


DATABASE SCHEMA

Add the following column to your users table:

ALTER TABLE users ADD COLUMN iframe_user_token VARCHAR(64) NULL;


TOKEN GENERATION

Tokens can be generated via the ticket system admin interface:

curl -X POST http://ticket-system.local/api/admin/users/{user_id}/generate-token \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"


INTEGRATION FILES

1) Copy ticket-integration.php into your project
2) Adjust API key and base URL
3) Done


INTEGRATION METHODS


METHOD 1: ONE-LINER (RECOMMENDED)

<?php
require_once 'ticket-integration.php';

// Load user data from database
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT email, iframe_user_token FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// One-liner integration
echo ticketSystemIframe(
    'your-api-key',
    'https://tickets.yourdomain.com',
    $user['iframe_user_token'],
    $user['email'],
    ['height' => '900']
);
?>


METHOD 2: OBJECT-ORIENTED (WITH ERROR HANDLING)

<?php
require_once 'ticket-integration.php';

$ticketAuth = new TicketSystemAuth(
    'your-api-key',
    'https://tickets.yourdomain.com'
);

// Server-side authentication (recommended for debugging)
$result = $ticketAuth->authenticateUser($userToken, $userEmail);

if ($result['success']) {
    echo '<iframe src="' . $result['iframe_url'] . '" width="100%" height="800"></iframe>';
    echo '<p>Logged in as: ' . $result['user']['name'] . '</p>';
} else {
    echo '<p>Error: ' . $result['error'] . '</p>';
}
?>


METHOD 3: HTML HELPER

<?php
$ticketAuth = new TicketSystemAuth(
    'your-api-key',
    'https://tickets.yourdomain.com'
);

echo $ticketAuth->renderIframe($userToken, $userEmail, [
    'width' => '100%',
    'height' => '900',
    'style' => 'border: 2px solid #007acc; border-radius: 8px;',
    'redirect' => '/projects'
]);
?>


SECURITY CONSIDERATIONS


API KEY PROTECTION

- The API key is stored as a hashed value in the .env file
- Only the plain-text key is sent to the backend


TOKEN SECURITY

- Tokens are 64-character cryptographically secure random strings
- Tokens can be revoked at any time
- Token usage is logged for security audits


RATE LIMITING

- 10 authentication attempts per minute per IP
- Prevents brute-force attacks


USER MANAGEMENT


CREATE TOKEN

<?php
// In the ticket system admin area
$user = User::find($userId);
$token = $user->generateIframeUserToken();

// Transfer token to your backend system
// UPDATE your_users SET iframe_user_token = ? WHERE id = ?
?>


REVOKE TOKEN

<?php
$user->revokeIframeToken();
// Also set the token to NULL in your backend system
?>


ERROR HANDLING


COMMON ISSUES

1) "Invalid API key"
   - Check the API key in .env
   - Verify that the hash was generated correctly

2) "Invalid credentials"
   - Verify the token exists in the ticket system
   - Check that the email address matches

3) "User configuration error"
   - User has no company assigned
   - User role is not set


DEBUGGING

Logs are written to storage/logs/laravel.log:

tail -f storage/logs/laravel.log | grep iframe


CUSTOMIZATION


CUSTOM STYLES

#ticket-iframe {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}


DYNAMIC HEIGHT ADJUSTMENT

window.addEventListener('message', function(event) {
    if (event.origin !== 'https://tickets.yourdomain.com') return;

    if (event.data.type === 'resize') {
        document.getElementById('ticket-iframe').style.height =
            Math.max(600, event.data.height) + 'px';
    }
});


SUPPORT

If you encounter issues with the integration:

1) Check the logs (storage/logs/laravel.log)
2) Test API calls using curl
3) Use browser developer tools
4) Verify token status in the admin interface
