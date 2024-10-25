<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invalid Reset Token</title>
    <style>
        /* Base styles for body */
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f4f4f9; /* Light background for professional look */
        }

        /* Container styling */
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
            color: #d9534f; /* Red for error emphasis */
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        /* Paragraph text styling */
        .message-container p {
            color: #333;
            font-size: 1rem;
            margin-bottom: 1.5rem;
        }

        /* Link styling */
        .message-container a {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background-color: #004085; /* Dark blue for MSWD theme */
            color: #ffffff;
            text-decoration: none;
            font-weight: bold;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        /* Hover effect for the link */
        .message-container a:hover {
            background-color: #003366;
        }
    </style>
</head>
<body>
    <div class="message-container">
        <h1>Invalid Reset Token</h1>
        <p>The password reset token is invalid or has already been used.</p>
        <a href="<?= site_url('forgot-password') ?>">Request New Password Reset</a>
    </div>
</body>
</html>
