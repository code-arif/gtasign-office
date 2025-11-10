<!-- resources/views/emails/contact.blade.php -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Help & support Message</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background-color: #282929;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
        }
        .content p {
            line-height: 1.6;
            margin: 0 0 10px;
        }
        .footer {
            background-color: #f4f4f4;
            text-align: center;
            padding: 10px;
            font-size: 12px;
            color: #777777;
        }

        .h2{
            font-size: 24px;
            margin-bottom: 10px;
            color: #fd8b00;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>New Message from {{ $helpandSupportData['email'] }}</h2>
        </div>
        <div class="content">
            <p><strong>Problem Details:</strong></p>
            <p>{{ $helpandSupportData['message'] }}</p>
        </div>
        <div class="footer">
            <p>Thank you for your submission.</p>
        </div>
    </div>
</body>
</html>
