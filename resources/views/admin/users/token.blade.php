<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Token Information</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .token-info {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .user-details {
            margin-bottom: 20px;
        }
        .token-display {
            font-family: monospace;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="token-info">
        <div class="user-details">
            <h2>User Token Information</h2>
            <p><strong>Name:</strong> {{ $user->name }}</p>
            <p><strong>Email:</strong> {{ $user->email }}</p>
            <p><strong>Role:</strong> {{ $user->role->value }}</p>
            @if($user->firma)
                <p><strong>Firma:</strong> {{ $user->firma->name }}</p>
            @endif
        </div>
        
        <div class="token-section">
            <h3>API Token</h3>
            <div class="token-display">
                {{ $user->remember_token ?? 'No token available' }}
            </div>
        </div>
    </div>
</body>
</html>