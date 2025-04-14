<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Spend Wise - Budget Tracker</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
      scroll-behavior: smooth;
    }

    body {
      background: linear-gradient(135deg, #001f3f, #003366);
      color: white;
    }

    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 20px 50px;
      background-color: rgba(0, 0, 50, 0.9);
      position: sticky;
      top: 0;
      z-index: 1000;
    }

    header h1 {
      font-size: 28px;
      color: #00ffcc;
    }

    nav button, nav a {
      margin-left: 15px;
      padding: 10px 20px;
      background: transparent;
      border: 2px solid #00ffcc;
      color: #00ffcc;
      border-radius: 25px;
      cursor: pointer;
      transition: 0.3s;
      text-decoration: none;
    }

    nav button:hover, nav a:hover {
      background-color: #00ffcc;
      color: #003366;
    }

    .hero {
      text-align: center;
      padding: 80px 20px;
    }

    .hero h2 {
      font-size: 48px;
      margin-bottom: 20px;
      color: #00ffcc;
    }

    .hero p {
      font-size: 18px;
      max-width: 700px;
      margin: auto;
    }

    .hero a {
      display: inline-block;
      margin-top: 30px;
      padding: 12px 30px;
      background-color: #00ffcc;
      color: #003366;
      border-radius: 25px;
      text-decoration: none;
      font-weight: bold;
      transition: 0.3s;
    }

    .hero a:hover {
      background-color: #00ccaa;
    }

    #slogan {
      text-align: center;
      margin-top: 40px;
    }

    #slogan h2 {
      font-size: 36px;
      color: #ffcc00;
    }

    #slogan p {
      font-size: 18px;
      margin-top: 10px;
    }

    .section-container {
      display: flex;
      justify-content: space-around;
      flex-wrap: wrap;
      padding: 60px 20px;
    }

    .feature-box, .review-card {
      background: rgba(255, 255, 255, 0.05);
      border-radius: 20px;
      width: 300px;
      padding: 30px;
      margin: 20px;
      text-align: center;
      transition: transform 0.3s;
    }

    .feature-box:hover, .review-card:hover {
      transform: scale(1.05);
    }

    .feature-box i, .review-card i {
      font-size: 40px;
      color: #00ffcc;
      margin-bottom: 15px;
    }

    .feature-box h3 {
      margin-bottom: 10px;
      color: #ffcc00;
    }

    .review-card h4 {
      margin-top: 10px;
      font-weight: 300;
      color: #ffcc00;
    }

    .section-title {
      text-align: center;
      font-size: 32px;
      margin-top: 20px;
      color: #00ffcc;
    }

    section#about, section#contact {
      padding: 60px 20px;
      background-color: rgba(255, 255, 255, 0.05);
      text-align: center;
    }

    section#about h2, section#contact h2 {
      font-size: 32px;
      color: #ffcc00;
    }

    section#about p, section#contact p {
      font-size: 18px;
      max-width: 700px;
      margin: 20px auto 0;
    }

    footer {
      text-align: center;
      padding: 20px;
      font-size: 14px;
      background-color: #001f3f;
    }
  </style>
</head>

<body>
  <header>
    <h1>Spend Wise</h1>
    <nav>
      <a href="#about">About</a>
      <a href="#contact">Contact</a>
      <button onclick="window.location.href='#signup'">Signup</button>
      <button>Login</button>
    </nav>
  </header>

  <section class="hero">
    <h2>Take Control of Your Finances</h2>
    <p>Spend Wise is your smart budget tracker that helps you manage your money, avoid overspending, and grow your savings.</p>
    <a href="/signup.php">Get Started</a>
  </section>

  <section id="slogan">
    <h2>Track. Save. Grow.</h2>
    <p>Your money, your control – with Spend Wise.</p>
  </section>

  <section class="section-container">
    <div class="feature-box">
      <i class="fas fa-wallet"></i>
      <h3>Smart Budgeting</h3>
      <p>Plan and track your spending effortlessly.</p>
    </div>
    <div class="feature-box">
      <i class="fas fa-chart-line"></i>
      <h3>Expense Analysis</h3>
      <p>Visual breakdown of all your expenses and savings.</p>
    </div>
    <div class="feature-box">
      <i class="fas fa-bell"></i>
      <h3>Smart Alerts</h3>
      <p>Get notified when you're close to your limit.</p>
    </div>
  </section>

  <h2 class="section-title">User Reviews</h2>
  <section class="section-container">
    <div class="review-card">
      <i class="fas fa-user-circle"></i>
      <p>"This app helped me control my impulsive spending habits and actually start saving!"</p>
      <h4>— Riya S., Pune</h4>
    </div>
    <div class="review-card">
      <i class="fas fa-user-circle"></i>
      <p>"Best budget tracker I've used so far. Clean UI and powerful features."</p>
      <h4>— Akash M., Mumbai</h4>
    </div>
    <div class="review-card">
      <i class="fas fa-user-circle"></i>
      <p>"Love the reminders! Helped me avoid unnecessary purchases and stick to my goal."</p>
      <h4>— Shruti K., Bangalore</h4>
    </div>
  </section>

  <section id="about">
    <h2>About Us</h2>
    <p>Spend Wise was built by a group of passionate developers who believe in empowering people to take control of their financial life. Our mission is to make budgeting simple, smart, and stress-free for everyone.</p>
  </section>

  <section id="contact">
    <h2>Contact Us</h2>
    <p>Got a question, feedback, or partnership inquiry? Reach out to us at <a href="mailto:support@spendwise.com" style="color: #00ffcc; text-decoration: underline;">support@spendwise.com</a> or follow us on social media.</p>
  </section>

  <footer>
    &copy; 2025 Spend Wise. All Rights Reserved.
  </footer>
</body>

</html>