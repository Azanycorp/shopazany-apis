<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unlock Your Sign-up Reward</title>
    <style>
        body {
            background-color: white;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 32rem;
            margin: 0 auto;
            padding: 1rem;
        }
        .text-center {
            text-align: center;
        }
        .mb-8 {
            margin-bottom: 2rem;
        }
        .mb-6 {
            margin-bottom: 1.5rem;
        }
        .mb-4 {
            margin-bottom: 1rem;
        }
        .mb-12 {
            margin-bottom: 3rem;
        }
        .mx-auto {
            margin-left: auto;
            margin-right: auto;
        }
        .max-h-24 {
            max-height: 6rem;
        }
        .max-h-36 {
            max-height: 9rem;
        }
        h1 {
            font-size: 1.25rem;
            font-weight: bold;
        }
        .text-gray-600 {
            color: #4B5563;
        }
        .inline-block {
            display: inline-block;
        }
        .bg-gray-100 {
            background-color: #F3F4F6;
        }
        .border-dashed {
            border-style: dashed;
        }
        .border-2 {
            border-width: 2px;
        }
        .border-gray-300 {
            border-color: #D1D5DB;
        }
        .py-2 {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }
        .px-4 {
            padding-left: 1rem;
            padding-right: 1rem;
        }
        .rounded {
            border-radius: 0.25rem;
        }
        .text-xl {
            font-size: 1.25rem;
        }
        .font-mono {
            font-family: monospace;
        }
        .bg-blue-500 {
            background-color: #3B82F6;
        }
        .text-white {
            color: white;
        }
        .text-yellow {
            color: #D97706;
        }
        .text-purple {
            color: #8B5CF6;
        }
        a {
            text-decoration: none;
        }
        .footer {
            max-width: 72rem;
            margin: 2rem auto;
            padding: 1rem;
        }
        .footer-content {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: 2rem;
        }
        .footer-item {
            flex: 1;
            margin: 0 1rem;
            text-align: center;
            box-sizing: border-box;
        }
        .max-h-18 {
            max-height: 4.5rem;
        }
        .text-red {
            color: #E02014;
        }
        address {
            font-style: normal;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        .code-container {
            background-color: #FFFBEB;
            border: 2px solid #F59E0B;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin: 2rem 0;
        }
        .benefits-list {
            text-align: left;
            margin: 1.5rem 0;
        }
        .benefits-list li {
            margin-bottom: 0.75rem;
            padding-left: 1.5rem;
            position: relative;
        }
        .benefits-list li:before {
            content: "✨";
            position: absolute;
            left: 0;
        }
    </style>
</head>
<body style="background-color: white; font-family: Arial, sans-serif; margin: 0; padding: 0;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 32rem; margin: 0 auto; padding: 1rem;">
        <tr>
            <td style="text-align: center; margin-bottom: 2rem;">
                <img src="https://ik.imagekit.io/mdee2wnwm/Azany/logo/azanylogo.png?updatedAt=1753939975701" alt="Azany Logo" style="max-height: 6rem; margin: 0 auto; display: block; margin-bottom: 1rem;">
                <p style="color: #4B5563; margin-bottom: 2rem;">Dear {{ $user['first_name'] }},</p>
            </td>
        </tr>

        <tr>
            <td style="color: #4B5563; line-height: 1.6; margin-bottom: 1.5rem;">
                <p style="margin-bottom: 1.5rem;">
                    You're one code away from unlocking your sign-up reward.
                </p>

                <div class="code-container" style="background-color: #FFFBEB; border: 2px solid #F59E0B; border-radius: 0.5rem; padding: 1.5rem; margin: 2rem 0; text-align: center;">
                    <p style="color: #D97706; font-weight: bold; margin-bottom: 0.5rem; font-size: 1.1rem;">Your Verification Code</p>
                    <div style="display: inline-block; background-color: white; border: 2px dashed #D1D5DB; padding: 0.5rem 1.5rem; border-radius: 0.25rem; font-size: 1.5rem; font-family: monospace; font-weight: bold; letter-spacing: 0.25rem; margin: 0.5rem 0;">
                        {{ $user['verification_code'] }}
                    </div>
                    <p style="color: #4B5563; margin-top: 0.75rem; font-size: 0.9rem;">
                        Simply enter this code on our website to verify your identity. It's valid for
                        <strong style="color: #E02014;">10 minutes</strong>, so act quickly!
                    </p>
                </div>

                <p style="color: #6B7280; font-size: 0.9rem; margin-bottom: 2rem; padding: 0.75rem; background-color: #F9FAFB; border-radius: 0.25rem;">
                    <strong>P.S.</strong> Didn't register? No worries, just ignore this email.
                </p>

                <div style="margin: 2rem 0;">
                    <h2 style="color: #8B5CF6; text-align: center; margin-bottom: 1rem;">Once verified, get ready to:</h2>
                    <ul class="benefits-list" style="text-align: left; margin: 1.5rem 0; padding: 0; list-style-type: none;">
                        <li style="margin-bottom: 0.75rem; padding-left: 1.5rem; position: relative; color: #4B5563;">
                            <span style="position: absolute; left: 0;">✨</span>
                            Explore our vibrant store
                        </li>
                        <li style="margin-bottom: 0.75rem; padding-left: 1.5rem; position: relative; color: #4B5563;">
                            <span style="position: absolute; left: 0;">✨</span>
                            Unlock sweet deals and discounts
                        </li>
                        <li style="margin-bottom: 0.75rem; padding-left: 1.5rem; position: relative; color: #4B5563;">
                            <span style="position: absolute; left: 0;">✨</span>
                            Track your orders like a pro
                        </li>
                        <li style="margin-bottom: 0.75rem; padding-left: 1.5rem; position: relative; color: #4B5563;">
                            <span style="position: absolute; left: 0;">✨</span>
                            Dive into even more awesome stuff
                        </li>
                    </ul>
                </div>

                <p style="color: #4B5563; font-weight: bold; text-align: center; margin: 2rem 0; font-size: 1.1rem;">
                    We can't wait to have you!
                </p>

                <p style="color: #4B5563; margin-bottom: 1rem;">
                    Happy exploring,<br>
                    The Azany Team
                </p>
            </td>
        </tr>
    </table>

    <table width="100%" cellpadding="0" cellspacing="0" border="0" class="footer" style="max-width: 72rem; margin: 2rem auto; padding: 1rem;">
        <tr>
            <td style="padding: 0;">
                <div class="footer-content" style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-start; margin-top: 2rem;">
                    <div class="footer-item" style="flex: 1; margin: 0 1rem; text-align: center; box-sizing: border-box;">
                        <img src="https://ik.imagekit.io/mdee2wnwm/Azany/logo/azanylogo.png?updatedAt=1753939975701" alt="Azany Logo" style="max-height: 4.5rem;">
                    </div>
                    <div class="footer-item" style="flex: 1; margin: 0 1rem; text-align: center; box-sizing: border-box;">
                        <h2 style="color: #E02014; font-size: 1rem;">Support</h2>
                        <address style="color: #4B5563; font-size: 0.875rem;">
                            320 W Lanier Ave Suite 200, Fayetteville, GA 30214<br>
                            support@shopazany.com<br>
                            +1 (800) 750-7442 <br>
                            +1 (470) 255-0365
                        </address>
                    </div>
                    <div class="footer-item" style="flex: 1; margin: 0 1rem; text-align: center; box-sizing: border-box;">
                        <h2 style="color: #E02014; font-size: 1rem;">Quick Links</h2>
                        <ul style="color: #4B5563; padding: 0; list-style-type: none; font-size: 0.875rem;">
                            <li><a href="https://shopazany.com/en/privacy-policy" style="color: #3B82F6;">Privacy Policy</a></li>
                            <li><a href="https://shopazany.com/en/terms-and-conditions" style="color: #3B82F6;">Terms Of Use</a></li>
                            <li><a href="https://shopazany.com/en/support-policy" style="color: #3B82F6;">Support Policy</a></li>
                            <li><a href="https://shopazany.com/en/contact" style="color: #3B82F6;">Contact</a></li>
                        </ul>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
