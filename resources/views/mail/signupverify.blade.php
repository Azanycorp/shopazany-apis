<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white font-sans">
    <div class="max-w-lg mx-auto p-4">

        <div class="text-center mb-8">
            <img src="https://azany-uploads.s3.amazonaws.com/assets/logo.png" alt="Azany Logo" class="mx-auto mb-4 max-h-24">
            <h1 class="text-xl font-bold">Activation Code</h1>
            <p class="text-gray-600">Your Activation code from Azany</p>
        </div>

        <div class="text-center mb-8">
            <img src="https://azany-uploads.s3.amazonaws.com/assets/lock.png" alt="Lock Icon" class="mx-auto max-h-36">
        </div>

        <div class="text-center mb-4">
            <span class="inline-block bg-gray-100 border-dashed border-2 border-gray-300 py-2 px-4 rounded text-xl font-mono">
                {{ $user->verification_code }}
            </span>
        </div>

        <p class="text-center text-gray-600 mb-8">
            Please do not share your one-time password with anyone for the sake of securing your account.
        </p>

    </div>

     <div class="max-w-6xl mx-auto p-4 mt-16">
        <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
            <img src="https://azany-uploads.s3.amazonaws.com/assets/logo.png" alt="Azany Logo" class="max-h-18">
            <div class="text-center md:text-left">
                <h2 class="font-bold text-[#E02014]">Support</h2>
                <address class="not-italic text-gray-600">
                    333 Freemont Street, California<br>
                    support@azany.com<br>
                    +88015-88888-9999
                </address>
            </div>
            <div class="text-center md:text-left">
                <h2 class="font-bold text-[#E02014]">Quick Links</h2>
                <ul class="text-gray-600">
                    <li>Privacy Policy</li>
                    <li>Terms Of Use</li>
                    <li>FAQ</li>
                    <li>Contact</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>



