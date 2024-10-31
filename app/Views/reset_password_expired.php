<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Token Expired</title>
    <style>
        /* Base body styles */
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f4f4f9; /* Light background */
        }

        /* Message container styling */
        .message-container {
            max-width: 400px;
            padding: 2rem;
            background-color: #ffffff;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Header styling */
        .message-container h1 {
            color: #f0ad4e; /* Orange for a warning tone */
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        /* Paragraph text styling */
        .message-container p {
            color: #333;
            font-size: 1rem;
            margin-bottom: 1.5rem;
        }

        /* Link/button styling */
        .message-container a {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background-color: #004085; /* MSWD-themed dark blue */
            color: #ffffff;
            text-decoration: none;
            font-weight: bold;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        /* Hover effect for the button */
        .message-container a:hover {
            background-color: #003366;
        }
    </style>
</head>
<body>
    <div class="message-container">
        <h1>Reset Token Expired</h1>
    <p>We're sorry, but your password reset link has expired.</p>
    <p>For security reasons, please request a new password reset link from the login page.</p>
        <a href="<?= site_url('forgot-password') ?>">Request New Password Reset</a>
        
    <p><a href="<?= site_url('login') ?>">Return to Login Page</a></p>
    </div>
</body>
</html>
