<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="styles.css">
  <title>Auto Cars Portal</title>
  <style>
    body {
      background-color: #D9D1CB;
      /* background-image: url("black-taxi-car-icon-isolated-on-beige-background-vector-32268734.jpg");
      background-repeat: no-repeat;
      background-size: cover;
      background-position: center center; */
    }
  </style>
</head>

<body>
  <div class="logo-container">
    <img src="autocarslogo.png" alt="Logo" class="logo">
  </div>
  <h1 class="text">Welcome to the login portal of Auto Cars!</h1>
  <h2 class="text">Choose your portal 👇</h2>
  <div class="portal-container">
    <div class="portal-icon" onclick="redirectToLogin('user')">
      <span class="icon" role="img" aria-label="User">👤</span>
      <p class="text">User Login</p>
    </div>

    <div class="portal-icon" onclick="redirectToLogin('driver')">
      <span class="icon" role="img" aria-label="Driver">🚖</span>
      <p class="text">Driver Login</p>
    </div>

    <div class="portal-icon" onclick="redirectToLogin('operator')">
      <span class="icon" role="img" aria-label="Operator">☎️</span>
      <p class="text">Operator Login</p>
    </div>
  </div>

  <script src="script.js"></script>
</body>

</html>