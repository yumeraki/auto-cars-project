<?php
// insert_rating.php

// Assuming you have a database connection established
include('config.php'); // Adjust this based on your actual file structure

// Check connection
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}

if (isset($_POST['reservationId']) && isset($_POST['customerId']) && isset($_POST['driverId']) && isset($_POST['rating'])) {
  $reservationId = $_POST['reservationId'];
  $customerId = $_POST['customerId'];
  $driverId = $_POST['driverId'];
  $rating = $_POST['rating'];

  // Insert the rating into the database
  $insertRatingQuery = "INSERT INTO ride_history (c_id, d_id, r_id, driver_rating)
    VALUES (?, ?, ?, ?)";
  $stmtInsertRating = mysqli_prepare($conn, $insertRatingQuery);

  mysqli_stmt_bind_param($stmtInsertRating, "iiid", $customerId, $driverId, $reservationId, $rating);

  if (mysqli_stmt_execute($stmtInsertRating)) {
    echo json_encode(['success' => true]);
  } else {
    echo json_encode(['success' => false, 'error' => 'Error inserting rating into the database.']);
  }

  mysqli_stmt_close($stmtInsertRating);
} else {
  echo json_encode(['success' => false, 'error' => 'Invalid parameters.']);
}

// Close database connection
mysqli_close($conn);
