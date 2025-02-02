<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome Email</title>
    <style>
        body {
            background-color: white;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        table {
            border-collapse: collapse;
            width: 100%;
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
        .max-h-24 {
            max-height: 6rem;
        }
        .max-h-32 {
            max-height: 8rem;
        }
        h1 {
            font-size: 1.25rem;
            font-weight: bold;
        }
        .font-semibold {
            font-weight: 600;
        }
        .text-gray-600 {
            color: #4B5563;
        }
        .text-gray-500 {
            color: #6B7280;
        }
        .bg-gray-100 {
            background-color: #F3F4F6;
        }
        .p-6 {
            padding: 1.5rem;
        }
        .rounded-lg {
            border-radius: 0.5rem;
        }
        .text-lg {
            font-size: 1.125rem;
        }
        .text-sm {
            font-size: 0.875rem;
        }
        .font-bold {
            font-weight: bold;
        }
        .bg-blue-500 {
            background-color: #3B82F6;
        }
        .text-white {
            color: white;
        }
        .py-1 {
            padding-top: 0.25rem;
            padding-bottom: 0.25rem;
        }
        .px-3 {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
        .rounded {
            border-radius: 0.25rem;
        }
        .mt-2 {
            margin-top: 0.5rem;
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
        a {
            text-decoration: none;
        }

        @media (min-width: 768px) {
            .md\:flex-row {
                flex-direction: row;
            }
            .md\:space-y-0 > * + * {
                margin-top: 0;
            }
            .md\:text-left {
                text-align: left;
            }
        }
    </style>
</head>
<body>
    <table class="container" cellpadding="0" cellspacing="0" border="0" style="max-width: 32rem; margin: 0 auto; padding: 1rem;">
        <tr>
            <td class="text-center" style="text-align: center; margin-bottom: 2rem;">
                <img src="https://azany-uploads.s3.amazonaws.com/assets/logo.png" alt="Azany Logo" style="max-height: 6rem; margin: 0 auto; display: block; margin-bottom: 1rem;">
                <h1 style="font-size: 1.25rem; font-weight: bold;">Hi <span style="font-weight: 600;">{{ $user['first_name'] }}</span>,</h1>
                <p style="color: #4B5563; margin-bottom: 1.5rem;">Welcome to Azany!</p>
                <p style="color: #6B7280;">Your #1 Global E-commerce Platform</p>
            </td>
        </tr>
    </table>

    <table class="max-w-6xl" cellpadding="0" cellspacing="0" border="0" style="max-width: 72rem; margin: 2rem auto; padding: 1.5rem;">
        <tr>
            <td style="background-color: #F3F4F6; padding: 1.5rem; border-radius: 0.5rem; margin-bottom: 2rem;">
                <table cellpadding="0" cellspacing="0" border="0" style="width: 100%; max-width: 36rem; margin: 0 auto;">
                    <tr>
                        <td style="text-align: center; margin-bottom: 1.5rem;">
                            <h2 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem;">Before Starting your adventure</h2>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center; margin-bottom: 1.5rem;">
                            <table cellpadding="0" cellspacing="0" border="0" style="width: 100%; max-width: 36rem; margin: 0 auto;">
                                <tr>
                                    <td style="text-align: center; padding-right: 1.25rem;">
                                        <img src="https://azany-uploads.s3.amazonaws.com/assets/start.png" alt="Start" style="max-height: 8rem;">
                                    </td>
                                    <td style="text-align: left;">
                                        <h3 style="font-weight: bold; font-size: 0.875rem;">Let's get Started</h3>
                                        <p style="font-size: 0.875rem;">Head over to your dashboard and complete your user profile.</p>
                                        <a href="{{ $loginUrl }}" style="background-color: #3B82F6; color: white; padding: 0.25rem 0.75rem; border-radius: 0.25rem; margin-top: 0.5rem; display: inline-block;">Complete Profile</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center; margin-bottom: 1.5rem;">
                            <table cellpadding="0" cellspacing="0" border="0" style="width: 100%; max-width: 36rem; margin: 0 auto;">
                                <tr>
                                    <td style="text-align: center; padding-right: 1.25rem;">
                                        <img src="https://azany-uploads.s3.amazonaws.com/assets/verify.png" alt="Verify" style="max-height: 8rem;">
                                    </td>
                                    <td style="text-align: left;">
                                        <h3 style="font-weight: bold; font-size: 0.875rem;">Verify your information</h3>
                                        <p style="font-size: 0.875rem;">Upload the necessary verification documents to verify your provided information.</p>
                                        <a href="{{ $loginUrl }}" style="background-color: #3B82F6; color: white; padding: 0.25rem 0.75rem; border-radius: 0.25rem; margin-top: 0.5rem; display: inline-block;">Add Verifications</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            <table cellpadding="0" cellspacing="0" border="0" style="width: 100%; max-width: 36rem; margin: 0 auto;">
                                <tr>
                                    <td style="text-align: center; padding-right: 1.25rem;">
                                        <img src="https://azany-uploads.s3.amazonaws.com/assets/explore.png" alt="Explore" style="max-height: 8rem;">
                                    </td>
                                    <td style="text-align: left;">
                                        <h3 style="font-weight: bold; font-size: 0.875rem;">Get Going</h3>
                                        <p style="font-size: 0.875rem;">Explore Azany and all the great benefits we have to offer.</p>
                                        <a href="{{ $baseUrl }}" style="background-color: #3B82F6; color: white; padding: 0.25rem 0.75rem; border-radius: 0.25rem; margin-top: 0.5rem; display: inline-block;">Start Exploring</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td>
                <table cellpadding="0" cellspacing="0" border="0" style="width: 100%; max-width: 72rem; margin: 2rem auto;">
                    <tr>
                        <td style="text-align: center; padding: 0.5rem;">
                            <img src="https://azany-uploads.s3.amazonaws.com/assets/logo.png" alt="Azany Logo" style="max-height: 6rem;">
                        </td>
                        <td style="text-align: center; padding: 0.5rem;">
                            <h2 style="font-weight: bold; color: #E02014;">Support</h2>
                            <address style="color: #4B5563;">
                                333 Freemont Street, California<br>
                                support@shopazany.com<br>
                                +1 (800) 750-7442<br>
                                +1 (470) 255-0365
                            </address>
                        </td>
                        <td style="text-align: center; padding: 0.5rem;">
                            <h2 style="font-weight: bold; color: #E02014;">Quick Links</h2>
                            <ul style="color: #4B5563; padding: 0; list-style-type: none;">
                                <li>Privacy Policy</li>
                                <li>Terms Of Use</li>
                                <li>FAQ</li>
                                <li>Contact</li>
                            </ul>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
