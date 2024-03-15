<?php
include 'config.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = array();

if (isset($_POST['submit'])) {
  $firstName = mysqli_real_escape_string($conn, $_POST['first_name']);
  $lastName = mysqli_real_escape_string($conn, $_POST['last_name']);
  $username = mysqli_real_escape_string($conn, $_POST['username']);
  $email = mysqli_real_escape_string($conn, $_POST['email']);
  $phoneNumber = mysqli_real_escape_string($conn, $_POST['phone_number']);
  $address = mysqli_real_escape_string($conn, $_POST['address']);
  $password = $_POST['password'];

  $insert = mysqli_prepare($conn, "INSERT INTO customer (c_first_name, c_last_name, c_username, c_email, c_number, c_address, c_password) VALUES (?, ?, ?, ?, ?, ?, ?)");
  mysqli_stmt_bind_param($insert, "sssssss", $firstName, $lastName, $username, $email, $phoneNumber, $address, $password);

  if (mysqli_stmt_execute($insert)) {
    $message[] = 'Registration successful! Check your email for login details.';

    // Send email with login details
    sendEmail($username, $email, $password);
  } else {
    $message[] = 'Registration failed. Please try again later.';
  }

  mysqli_stmt_close($insert);
}

function sendEmail($username, $email, $password)
{
  $mail = new PHPMailer(true);

  try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com'; // Replace with your SMTP server
    $mail->SMTPAuth   = true;
    $mail->Username   = 'naflanajim18@gmail.com';   // Replace with your SMTP username
    $mail->Password   = 'htdcmcjglaoofmdb';   // Replace with your SMTP password
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    // Recipients
    $mail->setFrom('autocars@gmail.com', 'Auto Cars Taxi Service');
    $mail->addAddress($email, $username);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Registration Successful';
    $mail->Body    = "Hello $username, <br>Your registration was successful!<br>Your login details:<br>Username: $username<br>Password: $password<br>";

    $mail->send();
  } catch (Exception $e) {
    // Log the error or handle it accordingly
    echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Registration</title>
  <!-- Custom CSS file link  -->
  <link rel="stylesheet" href="styles.css">
</head>

<body class="register">
  <?php
  if (isset($message)) {
    foreach ($message as $message) {
      echo '<div class="message" onclick="this.remove();">' . $message . '</div>';
    }
  }
  ?>

  <div class="form-container">
    <form action="" method="post">
      <h3>User Registration</h3>
      <!-- First Name input -->
      <input type="text" name="first_name" required placeholder="Enter first name" class="box" value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>">
      <!-- Last Name input -->
      <input type="text" name="last_name" required placeholder="Enter last name" class="box" value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>">
      <!-- Username input -->
      <input type="text" name="username" required placeholder="Enter username" class="box" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
      <!-- Email input -->
      <input type="email" name="email" required placeholder="Enter email" class="box" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
      <!-- Phone Number input -->
      <input type="text" name="phone_number" required placeholder="Enter phone number" class="box" value="<?php echo isset($_POST['phone_number']) ? htmlspecialchars($_POST['phone_number']) : ''; ?>">
      <!-- Address input -->
      <input type="text" name="address" required placeholder="Enter address" class="box" value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>">
      <!-- Password input -->
      <input type="password" name="password" required placeholder="Enter password" class="box">
      <!-- Submit button to register -->
      <input type="submit" name="submit" class="btn" value="Register" onclick="disableSubmitButton()">
      <!-- Login link to direct to login page -->
      <p>Already have an account? <a href="customerlogin.php">Login now</a></p>
    </form>
  </div>

  <script>
    function disableSubmitButton() {
      document.querySelector('.btn').disabled = true;
      setTimeout(function() {
        document.querySelector('.btn').disabled = false;
      }, 10000); // 10000 milliseconds = 10 seconds
    }
  </script>

</body>

</html>