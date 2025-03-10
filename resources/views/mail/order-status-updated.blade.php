<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order Status Update</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f8f8;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .header {
            background-color: #8b0000;
            color: white;
            text-align: center;
            padding: 15px;
            font-size: 20px;
            font-weight: bold;
            border-radius: 8px 8px 0 0;
        }
        .content {
            padding: 20px;
            text-align: left;
        }
        .content p {
            font-size: 16px;
            color: #333;
            line-height: 1.5;
        }
        .order-details {
            background-color: #f1f1f1;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
        }
        .order-details strong {
            color: #333;
        }
        .cta-button {
            display: block;
            width: 200px;
            text-align: center;
            background-color: #8b0000;
            color: white;
            padding: 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
            margin: 20px auto;
        }
        .footer {
            text-align: center;
            padding: 15px;
            font-size: 14px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            Order Status Update
        </div>

        <div class="content">
            <p>Hello {{ $user['first_name'] }},</p>

            <p>We wanted to inform you that your order <strong>#{{ $order['order_no'] }}</strong> has been updated.</p>

            <div class="order-details">
                <p><strong>Order No:</strong> {{ $order['order_no'] }}</p>
                <p><strong>New Status:</strong> <span style="color: #db4444; font-weight: bold;">{{ strtoupper($status) }}</span></p>
                <p><strong>Order Date:</strong> {{ \Carbon\Carbon::parse($order['created_at'])->format('F j, Y') }}</p>
            </div>

            <p>Thank you for shopping with us. If you have any questions, feel free to contact us.</p>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} ShopAzany. All rights reserved.
        </div>
    </div>
</body>
</html>
