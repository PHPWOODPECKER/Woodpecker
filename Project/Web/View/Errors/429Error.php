<?php
http_response_code(429);
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>خطای ۴۲۹ - محدودیت ارسال درخواست</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #343a40;
            text-align: center;
            padding: 50px;
        }
        h1 {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #dc3545;
        }
        p {
            font-size: 1.5rem;
            margin-bottom: 30px;
        }
        img {
            max-width: 100%;
            height: auto;
            margin-bottom: 30px;
        }
        a {
            display: inline-block;
            padding: 10px 20px;
            font-size: 1.2rem;
            color: #fff;
            background-color: #007bff;
            border-radius: 5px;
            text-decoration: none;
            margin: 10px;
        }
        a:hover {
            background-color: #0056b3;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .icon {
            font-size: 5rem;
            color: #dc3545;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">⚠️</div>
        <h1>۴۲۹</h1>
        <p>شما تعداد زیادی درخواست ارسال کرده‌اید!</p>
        <p>برای جلوگیری از سوء استفاده، تعداد درخواست‌های شما در بازه زمانی مشخص محدود شده است.</p>
        <p>لطفاً چند دقیقه صبر کنید و سپس مجدداً تلاش نمایید.</p>
        
        <div>
            <a href="/">بازگشت به صفحه اصلی</a>
            <a href="javascript:location.reload()">تلاش مجدد</a>
        </div>
    </div>
</body>
</html>