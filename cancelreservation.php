<?php
// cancelreservation.php

include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['reservationId'])) {
    $reservationId = $_POST['reservationId'];

    // Debugging statement
    error_log('Reservation ID: ' . $reservationId);

    // Perform the cancellation logic (update the status to 'cancelled' in the database)
    $cancelQuery = "UPDATE ride_reservation SET status = 'Cancelled' WHERE r_id = ?";
    $stmtCancel = mysqli_prepare($conn, $cancelQuery);
    mysqli_stmt_bind_param($stmtCancel, "i", $reservationId);

    if (mysqli_stmt_execute($stmtCancel)) {
      // Send a success response
      $response = ['success' => true];
      header('Content-Type: application/json');
      echo json_encode($response);
    } else {
      // Send an error response
      $response = ['success' => false, 'error' => mysqli_error($conn)];
      echo json_encode($response);
    }

    // Close the statement
    mysqli_stmt_close($stmtCancel);
  } else {
    // Send an error response if reservationId is not provided
    $response = ['success' => false, 'error' => 'Reservation ID not provided'];
    echo json_encode($response);
  }
} else {
  // Send an error response if not a POST request
  $response = ['success' => false, 'error' => 'Invalid request method'];
  echo json_encode($response);
}

// Close the database connection
mysqli_close($conn);
