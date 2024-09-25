<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Azany Admin Portal</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .email-header {
            background-color: #f5f5f5;
            padding: 20px;
            text-align: center;
            color: #fff;
        }
        .email-header img {
            max-width: 150px;
            margin-bottom: 10px;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
        }
        .email-body {
            padding: 20px;
        }
        .email-body p {
            font-size: 16px;
            color: #333;
            line-height: 1.6;
        }
        .email-body .login-details {
            background-color: #f9f9f9;
            padding: 15px;
            border-left: 4px solid #007bff;
            margin: 20px 0;
        }
        .email-footer {
            text-align: center;
            padding: 20px;
            background-color: #f4f4f4;
            color: #999;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <div class="email-container">
        <!-- Header Section -->
        <div class="email-header">
            <img src="https://azany-uploads.s3.amazonaws.com/assets/logo.png" alt="Azany Logo">
            <h1>Welcome to Azany Admin Portal</h1>
        </div>

        <!-- Body Section -->
        <div class="email-body">
            <p>Hi <strong>{{ $user->first_name }}</strong>,</p>

            <p>We’re excited to welcome you to the <strong>Azany</strong> admin team! Below are your login details for accessing the Admin Portal:</p>

            <!-- Login Details Section -->
            <div class="login-details">
                <p><strong>Login URL:</strong> <a href="https://shopazany.com">https://shopazany.com</a></p>
                <p><strong>Username:</strong> {{ $user->email }}</p>
                <p><strong>Password:</strong> {{ $pass }}</p>
            </div>

            <p>For security reasons, we strongly recommend that you log in and change your password as soon as possible.</p>

            <p>To update your password:</p>
            <ol>
                <li>Log in using the details above.</li>
                <li>Navigate to the 'Account Settings' section.</li>
                <li>Follow the prompts to set a new password.</li>
            </ol>

            <p>If you encounter any issues or have questions, feel free to reach out to us at <a href="mailto:support@shopazany-v2.com">support@shopazany-v2.com</a>.</p>

            <p>Welcome aboard,</p>
        </div>

        <!-- Footer Section -->
        <div class="email-footer">
            <p>&copy; 2024 Azany. All rights reserved.</p>
            <p><a href="https://shopazany.com">https://shopazany.com</a></p>
        </div>
    </div>

</body>
</html>
