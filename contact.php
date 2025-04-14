<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <link rel="stylesheet" href="style.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #1e90ff, #00c9ff, #b1d4e0);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            animation: backgroundAnimation 10s infinite alternate;
        }

        @keyframes backgroundAnimation {
            0% { background: linear-gradient(135deg, #1e90ff, #00c9ff, #b1d4e0); }
            100% { background: linear-gradient(135deg, #0057b7, #0096c7, #caf0f8); }
        }

        .contact-container {
            width: 80%;
            max-width: 900px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(15px);
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.2);
            transform: scale(0.9);
            animation: fadeIn 1s ease-in-out forwards;
        }

        @keyframes fadeIn {
            100% { transform: scale(1); }
        }

        h2 {
            font-size: 2em;
            font-weight: 700;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .contact-info {
            margin-top: 20px;
            color: white;
        }

        .info-item {
            background: rgba(255, 255, 255, 0.3);
            padding: 15px;
            border-radius: 10px;
            font-size: 18px;
            font-weight: bold;
            color: white;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease-in-out;
            margin: 10px 0;
            position: relative;
            overflow: hidden;
        }

        .info-item:hover {
            transform: translateY(-5px) scale(1.05);
        }

        .info-item::before {
            content: "";
            position: absolute;
            top: -50px;
            left: -50px;
            width: 200%;
            height: 200%;
            background: radial-gradient(rgba(255, 255, 255, 0.3), transparent);
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }

        .info-item:hover::before {
            opacity: 1;
        }

        .glow-text {
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.8);
            animation: glowEffect 1.5s infinite alternate;
        }

        @keyframes glowEffect {
            from { text-shadow: 0 0 10px rgba(255, 255, 255, 0.8); }
            to { text-shadow: 0 0 20px rgba(255, 255, 255, 1); }
        }

        .floating-btn {
            margin-top: 20px;
            padding: 15px 30px;
            border: none;
            border-radius: 50px;
            background: linear-gradient(135deg, #00b4d8, #48cae4);
            color: white;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0px 10px 20px rgba(72, 202, 228, 0.3);
            transition: transform 0.3s ease-in-out, background 0.3s ease-in-out;
        }

        .floating-btn:hover {
            transform: translateY(-5px);
            background: linear-gradient(135deg, #0096c7, #00b4d8);
        }

        .circle-animation {
            position: absolute;
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: 10%;
            left: 5%;
            animation: floatingAnimation 6s infinite alternate ease-in-out;
        }

        @keyframes floatingAnimation {
            from { transform: translateY(0px) scale(1); }
            to { transform: translateY(-20px) scale(1.2); }
        }

        .circle-animation:nth-child(2) {
            top: 60%;
            left: 80%;
            animation-duration: 8s;
        }

        .circle-animation:nth-child(3) {
            top: 30%;
            left: 40%;
            animation-duration: 10s;
        }
    </style>
</head>
<body>

    <div class="circle-animation"></div>
    <div class="circle-animation"></div>
    <div class="circle-animation"></div>

    <div class="contact-container">
        <h2 class="glow-text">📞 Contact Us 📞</h2>
        <p class="glow-text">Get in touch with us</p>

        <div class="contact-info">
            <div class="info-item">📍 Address: 123 Finance Street, Smart City</div>
            <div class="info-item">📧 Email: support@budgettracker.com</div>
            <div class="info-item">📞 Phone: +1 (123) 456-7890</div>
            <div class="info-item">⏰ Working Hours: Mon-Fri, 9 AM - 6 PM</div>
        </div>

        <button class="floating-btn" onclick="reloadPage()">🔄 Refresh</button>
    </div>

    <script>
        function reloadPage() {
            location.reload();
        }
    </script>

</body>
</html>