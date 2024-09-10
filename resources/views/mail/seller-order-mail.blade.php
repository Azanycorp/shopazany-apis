<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
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
        a {
            text-decoration: none;
        }
        .footer {
            max-width: 72rem;
            margin: 2rem auto; /* Added top and bottom margins */
            padding: 1rem;
        }
        .footer-content {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: 2rem; /* Added margin-top */
        }
        .footer-item {
            flex: 1;
            margin: 0 1rem; /* Added horizontal spacing between columns */
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
    </style>
</head>
<body style="background-color: white; font-family: Arial, sans-serif; margin: 0; padding: 0;">
    <div>
        <h5>Dear {{ $seller->first_name }} {{ $seller->last_name }},</h5>
        <p>
            A customer placed an order
        </p>
        <p>Order No: {{ $order->order_no }}</p>
    </div>
</body>
</html>
