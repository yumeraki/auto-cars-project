<?php
include 'config.php';
session_start();
$message = array();

if (isset($_POST['submit'])) {
  $username = mysqli_real_escape_string($conn, $_POST['username']);
  $password = $_POST['password'];

  // Checking for a valid username format if not showing a message to enter a valid one
  if (!preg_match("/^[a-zA-Z0-9_]+$/", $username)) {
    $message[] = 'Please enter a valid username (alphanumeric characters and underscores only).';
  } else {
    $select = mysqli_prepare($conn, "SELECT o_id, o_password FROM operator WHERE o_username = ?");
    mysqli_stmt_bind_param($select, "s", $username);
    mysqli_stmt_execute($select);
    mysqli_stmt_bind_result($select, $id, $storedPassword);
    mysqli_stmt_fetch($select);

    if ($password === $storedPassword) {
      $_SESSION['o_id'] = $id;
      $message[] = 'Login successful! Redirecting you to your profile...';
      mysqli_stmt_close($select);

      echo "<script>
                    setTimeout(function() {
                        window.location.href = 'operatordashboard.php';
                    }, 10000);
                </script>";
    } else {
      $message[] = 'Incorrect password or username!';
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <!-- Custom CSS file link  -->
  <link rel="stylesheet" href="styles.css">
</head>

<body class="login">
  <?php
  if (isset($message)) {
    foreach ($message as $message) {
      echo '<div class="message" onclick="this.remove();">' . $message . '</div>';
    }
  }
  ?>
  <div class="form-container">
    <form action="" method="post">
      <h3>Operator Login</h3>
      <!-- Username input -->
      <input type="text" name="username" required placeholder="Enter username" class="box">
      <!-- Password input -->
      <input type="password" name="password" required placeholder="Enter password" class="box">
      <!-- Submit button to login -->
      <input type="submit" name="submit" class="btn" value="Login now">
      <!-- Registration link to direct to register page, register.php for new customers -->
      <!-- <p>Forgot login details? <a href="customerregister.php">Contact Manager</a></p> -->
    </form>
  </div>
</body>

</html>