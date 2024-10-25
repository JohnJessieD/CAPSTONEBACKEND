<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        /* Base styles for the body */
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f4f4f9; /* Light background */
        }
        
        /* Styling the form container */
        .form-container {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
            border-radius: 8px;
            background-color: #ffffff;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* MSWD branding header */
        .form-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .form-header h1 {
            color: #004085; /* Dark blue for a formal touch */
            font-size: 1.5rem;
            margin: 0;
        }
        
        /* Input styling */
        label {
            font-weight: bold;
            color: #333;
            display: block;
            margin-top: 1rem;
        }

        input[type="password"] {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-top: 0.3rem;
            font-size: 1rem;
        }

        /* Submit button */
        button[type="submit"] {
            width: 100%;
            padding: 0.75rem;
            margin-top: 1.5rem;
            background-color: #004085; /* MSWD-inspired color */
            color: #ffffff;
            font-size: 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button[type="submit"]:hover {
            background-color: #003366;
        }

        /* Footer note */
        .footer-note {
            text-align: center;
            margin-top: 1rem;
            font-size: 0.85rem;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <h1>Reset Your Password</h1>
        </div>
        <form action="<?= site_url('reset-password/' . $token) ?>" method="post">
            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" required>
            
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
            
            <button type="submit">Reset Password</button>
        </form>
        <div class="footer-note">
            Please enter a strong password to protect your account.
        </div>
    </div>
</body>
</html>
