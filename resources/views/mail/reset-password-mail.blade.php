<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
</head>

<body style="background-color: white; font-family: Arial, sans-serif; margin: 0; padding: 0;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0"
        style="max-width: 32rem; margin: 0 auto; padding: 1rem;">
        <tr>
            <td style="text-align: center;">
                <img src="https://ik.imagekit.io/mdee2wnwm/Azany/logo/azanylogo.png?updatedAt=1753939975701"
                    alt="Azany Logo" style="max-height: 6rem; margin: 0 auto 1rem; display: block;">
                <p style="color: #4B5563;">Dear {{ $user['first_name'] }},</p>
            </td>
        </tr>

        <tr>
            <td style="color: #4B5563; line-height: 1.6;">
                <p>
                    We received a request to reset your password. Use the code below to proceed.
                </p>

                <!-- Reset Code Box -->
                <div
                    style="background-color: #EFF6FF; border: 2px solid #3B82F6; border-radius: 0.5rem; padding: 1.5rem; margin: 2rem 0; text-align: center;">
                    <p style="color: #1D4ED8; font-weight: bold; margin-bottom: 0.5rem; font-size: 1.1rem;">
                        Password Reset Code
                    </p>

                    <div
                        style="display: inline-block; background-color: white; border: 2px dashed #D1D5DB; padding: 0.5rem 1.5rem; border-radius: 0.25rem; font-size: 1.5rem; font-family: monospace; font-weight: bold; letter-spacing: 0.25rem;">
                        {{ $user['code'] }}
                    </div>

                    <p style="color: #4B5563; margin-top: 0.75rem; font-size: 0.9rem;">
                        This code will expire in
                        <strong style="color: #E02014;">10 minutes</strong>.
                    </p>
                </div>

                <p>
                    Enter this code on the password reset page to create a new password for your account.
                </p>

                <!-- Security Note -->
                <p
                    style="color: #6B7280; font-size: 0.9rem; margin-top: 1.5rem; padding: 0.75rem; background-color: #F9FAFB; border-radius: 0.25rem;">
                    <strong>Didn’t request this?</strong><br>
                    If you did not request a password reset, please ignore this email. Your account remains secure.
                </p>

                <p style="margin-top: 2rem;">
                    If you need help, feel free to contact our support team.
                </p>

                <p style="margin-top: 2rem;">
                    Regards,<br>
                    <strong>The Azany Team</strong>
                </p>
            </td>
        </tr>
    </table>

    <!-- Footer -->
    <table width="100%" cellpadding="0" cellspacing="0" border="0"
        style="max-width: 72rem; margin: 2rem auto; padding: 1rem;">
        <tr>
            <td>
                <div style="text-align: center;">
                    <img src="https://ik.imagekit.io/mdee2wnwm/Azany/logo/azanylogo.png?updatedAt=1753939975701"
                        alt="Azany Logo" style="max-height: 4.5rem;">
                </div>

                <div style="text-align: center; margin-top: 1.5rem;">
                    <h3 style="color: #E02014;">Support</h3>
                    <p style="color: #4B5563; font-size: 0.875rem;">
                        320 W Lanier Ave Suite 200, Fayetteville, GA 30214<br>
                        support@shopazany.com<br>
                        +1 (800) 750-7442
                    </p>
                </div>

                <div style="text-align: center; margin-top: 1rem;">
                    <a href="https://shopazany.com/en/privacy-policy" style="color: #3B82F6;">Privacy Policy</a> |
                    <a href="https://shopazany.com/en/terms-and-conditions" style="color: #3B82F6;">Terms</a> |
                    <a href="https://shopazany.com/en/contact" style="color: #3B82F6;">Contact</a>
                </div>
            </td>
        </tr>
    </table>
</body>

</html>