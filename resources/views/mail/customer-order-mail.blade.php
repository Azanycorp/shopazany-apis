<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Azany Order Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo img {
            max-width: 150px;
        }
        h1, h2 {
            text-align: center;
            color: #333;
        }
        .subtitle {
            text-align: left;
            color: #666;
            margin-bottom: 20px;
        }
        .banner {
            color: white;
            padding: 20px;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
        }
        .banner-text {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: white;
            color: black;
            padding: 10px;
            font-weight: bold;
        }
        .banner img {
            max-width: 100%;
            height: auto;
        }
        .message {
            text-align: center;
            margin-bottom: 20px;
        }
        .btn {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 10px;
            background-color: #007bff;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
        }
        .purchase-details {
            background-color: #f9f9f9;
            padding: 20px;
            margin-bottom: 20px;
        }
        .product {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
        }
        .product img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            margin-right: 20px;
        }
        .product-info h3 {
            margin-top: 0;
            color: #333;
        }
        .product-info p {
            color: #666;
            margin: 5px 0;
        }
        .price {
            color: #007bff;
            font-weight: bold;
        }
        .total {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            font-weight: bold;
        }
        .footer {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            font-size: 0.9em;
        }
        .footer-logo {
            flex: 1;
        }
        .footer-logo img {
            max-width: 150px;
        }
        .footer-info {
            flex: 1;
            display: flex;
            justify-content: space-between;
        }
        .footer-info div {
            flex: 1;
        }
        .footer h3 {
            color: #333;
            margin-bottom: 10px;
        }
        .footer p {
            color: #666;
            margin: 5px 0;
        }
        @media (max-width: 600px) {
            .container {
                margin: 10px;
                padding: 10px;
            }
            .product {
                flex-direction: column;
            }
            .product img {
                width: 100%;
                height: auto;
                margin-right: 0;
                margin-bottom: 10px;
            }
            .footer, .footer-info {
                flex-direction: column;
            }
            .footer-logo {
                margin-bottom: 20px;
            }

        }
    </style>
</head>
<body>
<div class="container">
    <div class="logo">
        <img src="https://ik.imagekit.io/mdee2wnwm/Azany/logo/azanylogo.png?updatedAt=1753939975701" alt="Azany Logo">
    </div>
    <h1>Hi {{ $user->first_name }},</h1>
    <h2>Order {{ $orderNo }} Has been Placed.</h2>
    <p class="subtitle">Your #1 Global E-commerce Platform</p>

    <div class="banner">
        <div class="banner-text">ORDER CONFIRMED</div>
        <img src="https://ik.imagekit.io/mdee2wnwm/Azany/mail_images/Rectangle%206263.png?updatedAt=1753940698633" alt="Order Confirmed Banner">
    </div>

    <div class="message">
        <h2>We Appreciate Your Purchase!</h2>
        <p>Hello {{ $user->first_name }}, We're Getting Your Order Ready For Dispatch.<br>We'll Notify You As Soon As It's On Its Way.</p>
        <p>— Azany Team</p>
    </div>

    <a href="#" class="btn">Track your Purchase</a>

    <div class="purchase-details">
        <h2>Purchase Details</h2>

        @php
            $subtotal = 0;
        @endphp
        @foreach ($items as $item)
            @php
                $symbol = match($user->default_currency) {
                    'USD' => '$',
                    'NGN' => '₦',
                    default => $user->default_currency,
                };
                $price = currencyConvert($item['currency'], $item['price'], $user->default_currency);
                $subtotal += $price * $item['quantity'];
            @endphp
            <div class="product">
                <img src="{{ $item['image'] }}" alt="{{ $item['product_name'] }}">
                <div class="product-info">
                    <h3>{{ $item['product_name'] }}</h3>
                    <p class="price">
                        {{ $symbol }}{{ number_format($price, 2) }}
                    </p>
                    <p>QTY: {{ $item['quantity'] }}</p>
                </div>
            </div>
        @endforeach

        @php
            $currency = $items[0]['currency'] ?? 'USD';
            $symbol = match($currency) {
                'USD' => '$',
                'NGN' => '₦',
                default => $currency,
            };
        @endphp
        <div class="total">
            <span>Subtotal</span>
            <span>
                {{ $symbol }}{{ number_format($subtotal) }}
            </span>
        </div>
        <div class="total">
            <span>Tax</span>
            <span>
                {{ $symbol }}00.00
            </span>
        </div>
        <div class="total">
            <span>Shipping</span>
            <span>
                {{ $symbol }}00.00
            </span>
        </div>
        <div class="total">
            <span>Total</span>
            <span>
                {{ $symbol }}{{ number_format($totalAmount) }}
            </span>
        </div>
    </div>

    <div class="footer">
        <div class="footer-logo">
            <img src="https://ik.imagekit.io/mdee2wnwm/Azany/logo/azanylogo.png?updatedAt=1753939975701" alt="Azany Logo">
        </div>
        <div class="footer-info">
            <div>
                <h3>Support</h3>
                <p>320 W Lanier Ave Suite 200, Fayetteville, GA 30214</p>
                <p>support@azany.com</p>
                <p>+88015-88888-9999</p>
            </div>
            <div>
                <h3>Quick Links</h3>
                <p><a href="https://shopazany.com/en/privacy-policy"> Privacy Policy </a></p>
                <p><a href="https://shopazany.com/en/terms-and-conditions"> Terms Of Use </a></p>
                <p><a href="https://shopazany.com/en/support-policy"> Support Policy </a></p>
                <p><a href="https://shopazany.com/en/contact"> Contact </a></p>
            </div>
        </div>
    </div>
</div>
</body>
</html>
