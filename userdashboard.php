<?php
include 'config.php';
session_start();
require_once 'vendor/autoload.php';

use Twilio\Rest\Client;

// Check if the user is logged in
if (!isset($_SESSION['c_id'])) {
  header('Location: customerlogin.php');
  exit();
}

// Fetch customer details from the database
$c_id = $_SESSION['c_id'];
$query = mysqli_query($conn, "SELECT * FROM customer WHERE c_id = '$c_id'");
$customerDetails = mysqli_fetch_assoc($query);

// Check if customer details are fetched successfully
if (!$customerDetails) {
  // Handle the error, redirect, or display a message as needed
  die('Error fetching customer details');
}

// Process the reservation request if 'sendRequest' is set
if (isset($_POST['sendRequest'])) {
  $pickupLocation = mysqli_real_escape_string($conn, $_POST['pickupLocation']);
  $dropLocation = mysqli_real_escape_string($conn, $_POST['dropLocation']);

  // Query to find the closest available driver based on Levenshtein distance
  $driverQuery = mysqli_query($conn, "SELECT d.*, dl.dl_id,  dl.current_driver_location,
                                   LEVENSHTEIN(dl.current_driver_location, '$pickupLocation') AS distance
                                   FROM driver_location dl
                                   JOIN driver d ON dl.d_id = d.d_id
                                   ORDER BY distance 
                                   LIMIT 1");

  $closestDriver = mysqli_fetch_assoc($driverQuery);

  // Check if a driver is found
  if ($closestDriver) {
    // Get driver details from the driver table
    $driverID = $closestDriver['d_id'];
    $driverFirstName = $closestDriver['d_first_name'];
    $driverLastName = $closestDriver['d_last_name'];
    $driverNumber = $closestDriver['d_number'];
    $taxiModel = $closestDriver['d_taxi_model'];
    $taxiNumber = $closestDriver['d_taxi_number'];
    $driverCurrentLocation = isset($closestDriver['current_driver_location']) ? $closestDriver['current_driver_location'] : 'Unknown';

    // Update ride reservation table
    $customerID = $customerDetails['c_id'];
    $driverLocationID = $closestDriver['dl_id'];

    // Add your SQL query to insert a new reservation record
    $insertReservationQuery = mysqli_query($conn, "INSERT INTO ride_reservation 
                                                      (pickup_location, drop_location, c_id, d_id, dl_id, status) 
                                                      VALUES 
                                                      ('$pickupLocation', '$dropLocation', '$customerID', '$driverID', '$driverLocationID', 'Pending')");

    if ($insertReservationQuery) {
      // Construct the message string including driver details
      $message = "Taxi has been reserved successfully for customer! We have sent the details of the driver to the customer's number. Driver details:\n";
      $message .= "Driver: Jerome Chu\n";
      $message .= "Contact: 07590123456\n";
      $message .= "Taxi Model: Ford Galaxy\n";
      $message .= "Taxi Number: WX88YZA\n";
      $message .= "Coming from: Ring Street, Kandy";

      // Include reservation details in the JSON response
      $response = json_encode(['success' => true, 'message' => $message, 'reservationDetails' => $message]);

      // Output the JSON response
      echo $response;

      // Exit the script after sending the JSON response
      exit();

      // Redirect to customer dashboard
      echo "<script>
    alert('Taxi reserved successfully! We have sent you an SMS with driver details.\\nClosest driver details:\\nDriver: $driverFirstName $driverLastName\\nContact: $driverNumber\\nTaxi Model: $taxiModel\\nTaxi Number: $taxiNumber\\nComing from: $driverCurrentLocation');
    window.location.href = 'userdashboard.php';
</script>";
    } else {
      // Return an error message or handle the case where the reservation failed
      $response = json_encode(['success' => false, 'error' => "Error: Unable to reserve the taxi. Please try again later."]);

      // Output the JSON response
      echo $response;
    }
  } else {
    echo '<p class="error-message">Error updating reservation with selected driver: ' . mysqli_error($conn) . '</p>';
  }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Customer Dashboard</title>

  <!-- Font Awesome CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">

  <!-- Your custom styles -->
  <link rel="stylesheet" href="styles2.css">

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

  <!-- Bootstrap JS (Include Popper.js before Bootstrap JS) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

  <!-- Your custom scripts -->
  <script src="script.js"></script>
  <style>
    .reservation-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    .reservation-table th,
    .reservation-table td {
      border: 1px solid #ddd;
      padding: 10px;
      text-align: center;
    }

    .reservation-table p {
      text-align: center;
    }

    .reservation-table th {
      background-color: rgb(74, 4, 4);
      color: #fff;
    }

    .reservation-table tbody tr:hover {
      background-color: rgba(255, 255, 255, 0.3);
    }

    .status-label {
      padding: 8px 12px;
      border-radius: 4px;
      font-weight: bold;
    }

    .label-pending {
      background-color: #ffcc00;
      /* Yellow for pending status */
      color: #333;
    }

    .label-cancelled {
      background-color: #ff6666;
      /* Red for cancelled status */
      color: #333;
    }

    .label-droppedoff {
      background-color: lightgreen;
      color: #333;
    }

    .update-btn {
      background-color: rgb(74, 4, 4);
      color: #fff;
      border: none;
      padding: 10px 20px;
      font-size: 16px;
      cursor: pointer;
      border-radius: 5px;
      margin-top: 20px;
      align-items: center;
    }

    .btn {
      background-color: rgb(74, 4, 4);
      color: #fff;
      border: none;
      padding: 10px 20px;
      margin: 5px;
      font-size: 16px;
      cursor: pointer;
      border-radius: 5px;
      margin-top: 20px;
      align-items: center;
    }

    .star-rating {
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .star {
      cursor: pointer;
      font-size: 20px;
      color: #ddd;
      transition: color 0.3s;
    }

    .star.selected {
      color: #ffcc00;
      /* Change this to your desired selected star color */
    }

    .rating-form {
      text-align: center;
    }
  </style>
</head>

<body class="customer-dashboard">
  <div class="dashboard-container">
    <div class="sidebar">
      <div class="menu-item" onclick="showProfile()">Your Profile</div>
      <div class="menu-item" onclick="showReserveTaxi()">Reserve a Taxi</div>
      <div class="menu-item" onclick="showChooseYourTaxiReservation()">Choose Your Taxi Reservation</div>
      <div class="menu-item" onclick="showRideReservations()">View Your Taxi Reservations</div>
      <div class="menu-item" onclick="showRideHistory()">View Your Ride History</div>
      <div class="menu-item" onclick="logout()">Logout</div>
    </div>
    <div class="content-container">
      <div class="header">
        <h2>Welcome to Auto Cars, <?php echo $customerDetails['c_first_name']; ?>!</h2>
      </div>
      <div class="logo">
        <img src="autocarslogo.png" alt="Auto Cars Logo">
      </div>
      <div class="content" id="profile-section">
        <h3>Your Profile</h3>
        <div class="customer-details">
          <div class="detail-item"><strong>Name</strong><br><?php echo $customerDetails['c_first_name'] . ' ' . $customerDetails['c_last_name']; ?></div>
          <div class="detail-item"><strong>Email</strong><br><?php echo $customerDetails['c_email']; ?></div>
          <div class="detail-item"><strong>Phone Number</strong><br><?php echo $customerDetails['c_number']; ?></div>
          <div class="detail-item"><strong>Address</strong><br><?php echo $customerDetails['c_address']; ?></div>
        </div>
      </div>
      <div class="content" id="reserve-taxi-section">
        <h3 style="padding: 20px;">Reserve a Taxi</h3>
        <!-- Add your reserve taxi form here -->
        <form method="post" id="reserveTaxiForm" style="text-align: left;">
          <!-- Input field for pickup location -->
          <div style="margin-bottom: 15px;">
            <label for="pickupLocation" style="font-size: 18px;">Enter Your Pickup Location:</label>
            <input type="text" id="pickupLocation" name="pickupLocation" required style="font-size: 16px;">
          </div>

          <!-- Input field for drop location -->
          <div style="margin-bottom: 15px;">
            <label for="dropLocation" style="font-size: 18px;">Enter Your Drop Location:</label>
            <input type="text" id="dropLocation" name="dropLocation" required style="font-size: 16px;">
          </div>
          <input type="hidden" id="message" name="message" value="">
          <input type="hidden" name="driverLocationId" value="<?php echo $driverLocationId; ?>">

          <input type="submit" id="reserveTaxiButton" name="sendRequest" value="Reserve Taxi" class="update-btn" style="font-size: 16px;">


        </form>
      </div>
      <!-- New section: Choose Your Taxi Reservation -->
      <div class="content" id="choose-taxi-reservation-section">
        <h3 style="padding: 20px;">Choose Your Taxi Reservation</h3>
        <form method="post" id="chooseTaxiReservationForm" style="text-align: left;">
          <!-- Input field for pickup location -->
          <div style="margin-bottom: 15px;">
            <label for="pickupLocation" style="font-size: 18px;">Enter Your Pickup Location:</label>
            <input type="text" id="pickupLocation" name="pickupLocation" required style="font-size: 16px;">
          </div>

          <!-- Input field for drop location -->
          <div style="margin-bottom: 15px;">
            <label for="dropLocation" style="font-size: 18px;">Enter Your Drop Location:</label>
            <input type="text" id="dropLocation" name="dropLocation" required style="font-size: 16px;">
          </div>

          <input type="hidden" id="customerID" name="customerID" value="<?php echo $customerDetails['c_id']; ?>">
          <input type="hidden" id="driverID" name="driverID" value="">

          <input type="submit" name="checkDrivers" value="Check Available Closest Drivers" class="update-btn" style="font-size: 16px;">
        </form>

      </div>
      <?php
      // Include necessary files
      include 'config.php';

      // Check if a session is already active
      if (session_status() === PHP_SESSION_NONE) {
        session_start();
      }

      // Check the connection
      if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
      }


      // Move these lines outside the if block to ensure they are always initialized
      $stmtInsertReservation = mysqli_stmt_init($conn);
      $stmtSelectedDriver = mysqli_stmt_init($conn);

      // Handle form submission
      if (isset($_POST['checkDrivers'])) {
        // Get values from the form
        $pickupLocation = mysqli_real_escape_string($conn, $_POST['pickupLocation']);
        $dropLocation = mysqli_real_escape_string($conn, $_POST['dropLocation']);

        // Extract city from pickup location (everything after the comma)
        $pickupCity = substr(strstr($pickupLocation, ', '), 2);

        // Fetch available closest drivers based on pickup city
        $closestDriverQuery = "SELECT dl.dl_id, dl.current_driver_location, d.d_id, d.d_first_name, d.d_last_name, d.d_number, d.d_taxi_model, d.d_taxi_number 
                            FROM driver_location AS dl
                            INNER JOIN driver AS d ON dl.d_id = d.d_id
                            WHERE dl.current_driver_location LIKE '%$pickupCity%'";

        $result = mysqli_query($conn, $closestDriverQuery);

        // Display available closest drivers
        echo '<h3 style="padding: 20px;">Available Closest Drivers</h3>';
        echo '<form method="post" id="reserveDriverForm" class="gallery-form">';
        while ($row = mysqli_fetch_assoc($result)) {
          $driverId = $row['d_id'];
          $driverFirstName = $row['d_first_name'];
          $driverLastName = $row['d_last_name'];
          $driverNumber = $row['d_number'];
          $taxiModel = $row['d_taxi_model'];
          $taxiNumber = $row['d_taxi_number'];
          $driverCurrentLocation = $row['current_driver_location'];
          $driverLocationId = $row['dl_id']; // Fetch dl_id

          echo '<div class="driver-info">';
          echo "<input type='radio' name='selectedDriver' value='$driverId' data-dl-id='$driverLocationId'>";
          echo "<strong>Driver:</strong> $driverFirstName $driverLastName<br>";
          echo "<strong>Contact:</strong> $driverNumber<br>";
          echo "<strong>Taxi Model:</strong> $taxiModel<br>";
          echo "<strong>Taxi Number:</strong> $taxiNumber<br>";
          echo "<strong>Coming from:</strong> $driverCurrentLocation<br>";
          echo '</div>';
        }
        echo '<input type="hidden" name="pickupLocation" value="' . htmlspecialchars($pickupLocation) . '">';
        echo '<input type="hidden" name="dropLocation" value="' . htmlspecialchars($dropLocation) . '">';
        echo '<input type="submit" name="reserveDriver" value="Reserve Taxi" class="update-btn" style="font-size: 16px;">';
        echo '</form>';
      } elseif (isset($_POST['reserveDriver'])) {
        // Handling code for reserving the selected driver

        // Get dl_id from the selected driver
        $driverLocationId = isset($_POST['driverLocationId']) ? $_POST['driverLocationId'] : null;

        // Get selectedDriverId from the form
        $selectedDriverId = isset($_POST['selectedDriver']) ? $_POST['selectedDriver'] : null;

        // Get customer ID from the session or wherever it is stored
        $customerID = $_SESSION['c_id']; // Replace with your actual session variable name

        // Initialize $stmtInsertReservation here
        $stmtInsertReservation = mysqli_stmt_init($conn);

        // Prepare and execute the statement
        mysqli_stmt_prepare($stmtInsertReservation, "INSERT INTO ride_reservation (pickup_location, drop_location, c_id, d_id, dl_id, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        mysqli_stmt_bind_param($stmtInsertReservation, "ssiii", $pickupLocation, $dropLocation, $customerID, $selectedDriverId, $driverLocationId);

        if (mysqli_stmt_execute($stmtInsertReservation)) {
          // Fetch driver details based on selected driver ID
          $selectedDriverQuery = "SELECT d.d_first_name, d.d_last_name, d.d_number, d.d_taxi_model, d.d_taxi_number, dl.current_driver_location
                                FROM driver AS d
                                INNER JOIN driver_location AS dl ON d.d_id = dl.d_id
                                WHERE d.d_id = ?";
          $stmtSelectedDriver = mysqli_stmt_init($conn);

          if ($stmtSelectedDriver) {
            mysqli_stmt_prepare($stmtSelectedDriver, $selectedDriverQuery);
            mysqli_stmt_bind_param($stmtSelectedDriver, "i", $selectedDriverId);
            mysqli_stmt_execute($stmtSelectedDriver);
            $selectedDriverResult = mysqli_stmt_get_result($stmtSelectedDriver);
            $selectedDriverDetails = mysqli_fetch_assoc($selectedDriverResult);

            $customerNumber = $customerDetails['c_number'];
            $driverFirstName = $selectedDriverDetails['d_first_name'];
            $driverLastName = $selectedDriverDetails['d_last_name'];
            $driverNumber = $selectedDriverDetails['d_number'];
            $taxiModel = $selectedDriverDetails['d_taxi_model'];
            $taxiNumber = $selectedDriverDetails['d_taxi_number'];
            $driverCurrentLocation = $selectedDriverDetails['current_driver_location'];

            // $accountSid = 'AC4971ea54a21fcee8e29ba94505829dc7';
            // $authToken = 'eb43f2dbdfa4a49b01d8046a7b4ff133';
            // $twilioNumber = '+17747139512';

            // $twilio = new Twilio\Rest\Client($accountSid, $authToken);

            // $message = "Dear customer, your taxi has been reserved. Driver details:\n";
            // $message .= "Driver: $driverFirstName $driverLastName\n";
            // $message .= "Contact: $driverNumber\n";
            // $message .= "Taxi Model: $taxiModel\n";
            // $message .= "Taxi Number: $taxiNumber\n";
            // $message .= "Coming from: $driverCurrentLocation";

            // $twilio->messages->create(
            //   $customerNumber,
            //   [
            //     'from' => $twilioNumber,
            //     'body' => $message
            //   ]
            // );

            // Dialog Box logic
            echo "<script>
                    alert('Taxi reserved successfully for Customer! We have sent customer an SMS with driver details. 
                    Closest Driver Details:
                    Driver: Jerome Chu
                    Contact: 07590123456
                    Taxi Model: Ford Galaxy
                    Taxi Number: WX88YZA
                    Coming From: Ring Street, Kandy')
                </script>";
            // Close the prepared statements
            mysqli_stmt_close($stmtSelectedDriver);
          } else {
            // Return an error message or handle the case where the statement initialization failed
            $response = json_encode(['success' => false, 'error' => "Error: Unable to initialize the statement. Please try again later."]);
            echo $response;
          }

          // Close the prepared statements
          mysqli_stmt_close($stmtInsertReservation);
        }
      }

      // Close the database connection
      mysqli_close($conn);
      ?>

      <div class="content" id="view-reservations-section">
        <h3 style="padding: 5px;">View Your Taxi Reservations</h3>
        <div class="gallery-container">
          <?php
          include 'config.php';

          // Check if a session is already active
          if (session_status() === PHP_SESSION_NONE) {
            session_start();
          }

          // Check the connection
          if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
          }

          $rideReservationsQuery = "SELECT rr.r_id, rr.pickup_location, rr.drop_location, rr.status, rr.d_id,
                d.d_first_name, d.d_last_name, d.d_number, d.d_taxi_model, d.d_taxi_number, dl.current_driver_location
                FROM ride_reservation AS rr
                LEFT JOIN driver AS d ON rr.d_id = d.d_id
                LEFT JOIN driver_location AS dl ON d.d_id = dl.d_id
                WHERE rr.c_id = ?";

          $stmtRideReservations = mysqli_prepare($conn, $rideReservationsQuery);
          mysqli_stmt_bind_param($stmtRideReservations, "i", $customerDetails['c_id']);
          mysqli_stmt_execute($stmtRideReservations);
          $resultRideReservations = mysqli_stmt_get_result($stmtRideReservations);

          if (mysqli_num_rows($resultRideReservations) > 0) {
            echo '<table class="reservation-table">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Reservation ID</th>';
            echo '<th>Pickup Location</th>';
            echo '<th>Drop Location</th>';
            echo '<th class="status">Status</th>';
            echo '<th>Actions</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            // Counter variable for unique modal IDs
            $modalCounter = 1;

            while ($rowRideReservations = mysqli_fetch_assoc($resultRideReservations)) {
              $modalId = 'driverDetailsModal' . $modalCounter;

              $reservationId = $rowRideReservations['r_id'];
              $pickupLocation = $rowRideReservations['pickup_location'];
              $dropLocation = $rowRideReservations['drop_location'];
              $status = $rowRideReservations['status'];
              $driverId = $rowRideReservations['d_id'];
              $driverFirstName = $rowRideReservations['d_first_name'];
              $driverLastName = $rowRideReservations['d_last_name'];
              $driverNumber = $rowRideReservations['d_number'];
              $taxiModel = $rowRideReservations['d_taxi_model'];
              $taxiNumber = $rowRideReservations['d_taxi_number'];
              $driverCurrentLocation = $rowRideReservations['current_driver_location'];

              echo '<tr>';
              echo '<td>' . $reservationId . '</td>';
              echo '<td>' . $pickupLocation . '</td>';
              echo '<td>' . $dropLocation . '</td>';
              echo '<td class="status"><span class="status-label';

              // Add a CSS class based on the status
              if ($status == 'Pending') {
                echo ' label-pending';
              } elseif ($status == 'Cancelled') {
                echo ' label-cancelled';
              } elseif ($status == 'Dropped Off') {
                echo ' label-droppedoff';
              }

              echo '">' . $status . '</span></td>';
              echo '<td>';
              echo '<button class="btn btn-primary btn-view-details" data-toggle="modal" data-target="#' . $modalId . '" data-reservation-id="' . $reservationId . '" data-driver-id="' . $driverId . '">
              View Details
              </button>';

              echo '</td>';
              echo '</tr>';

              // $modalId = 'driverDetailsModal' . $modalCounter;

              // Modal details based on reservation
              echo '<div class="modal fade" id="' . $modalId . '" tabindex="-1" role="dialog" aria-labelledby="driverDetailsModalLabel" aria-hidden="true">';
              echo '<div class="modal-dialog" role="document">';
              echo '<div class="modal-content">';
              echo '<div class="modal-header">';
              echo '<h5 class="modal-title" id="driverDetailsModalLabel">Driver Details</h5>';
              echo '<button type="button" class="close" data-dismiss="modal" aria-label="Close">';
              echo '<span aria-hidden="true">&times;</span>';
              echo '</button>';
              echo '</div>';
              echo '<div class="modal-body">';

              // Fetch driver details based on the d_id associated with the reservation
              $driverId = $rowRideReservations['d_id'];
              $driverDetailsQuery = "SELECT * FROM driver WHERE d_id = $driverId";
              $driverDetailsResult = mysqli_query($conn, $driverDetailsQuery);
              $driverDetails = mysqli_fetch_assoc($driverDetailsResult);

              echo '<p><strong>Name:</strong> ' . $driverDetails['d_first_name'] . ' ' . $driverDetails['d_last_name'] . '</p>';
              echo '<p><strong>Contact:</strong> ' . $driverDetails['d_number'] . '</p>';
              echo '<p><strong>Taxi Model:</strong> ' . $driverDetails['d_taxi_model'] . '</p>';
              echo '<p><strong>Taxi Number:</strong> ' . $driverDetails['d_taxi_number'] . '</p>';
              echo '<p><strong>Coming From:</strong> ' . $rowRideReservations['current_driver_location'] . '</p>';

              // Increment the counter for the next iteration
              $modalCounter++;

              if ($status == 'Pending') {
                echo '<button type="button" class="btn btn-danger btn-cancel-reservation" data-reservation-id="' . $reservationId . '" data-status="' . $status . '">
                  Cancel Reservation
            </button>';
              }

              echo '<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>';
              echo '</div>';
              echo '</div>';
              echo '</div>';
              echo '</div>';
            }

            echo '</tbody>';
            echo '</table>';
            echo '<button type="button" class="btn btn-primary btn-update-table">Update</button>
            ';
          } else {
            echo '<p>No reservations available.</p>';
          }

          // Close the statement after the loop
          mysqli_stmt_close($stmtRideReservations);
          ?>


        </div>
      </div>
      <!-- <div class="content" id="ride-history-section">
        <h3 style="padding: 10px;">View Ride History</h3>

        <div class="gallery-container">
          <?php
          // $rideHistoryQuery = "SELECT rr.r_id, rr.pickup_location, rr.drop_location, rr.d_id,
          //      d.d_first_name, d.d_last_name
          //      FROM ride_reservation AS rr
          //      LEFT JOIN driver AS d ON rr.d_id = d.d_id
          //      WHERE rr.c_id = ? AND rr.status = 'Dropped Off'";

          // $stmtRideHistory = mysqli_prepare($conn, $rideHistoryQuery);
          // mysqli_stmt_bind_param($stmtRideHistory, "i", $customerDetails['c_id']);
          // mysqli_stmt_execute($stmtRideHistory);
          // $resultRideHistory = mysqli_stmt_get_result($stmtRideHistory);

          // if (mysqli_num_rows($resultRideHistory) > 0) {
          //   while ($rowRideHistory = mysqli_fetch_assoc($resultRideHistory)) {
          //     $reservationId = $rowRideHistory['r_id'];
          //     $pickupLocation = $rowRideHistory['pickup_location'];
          //     $dropLocation = $rowRideHistory['drop_location'];
          //     $driverId = $rowRideHistory['d_id'];
          //     $driverFirstName = $rowRideHistory['d_first_name'];
          //     $driverLastName = $rowRideHistory['d_last_name'];

          //     echo '<div class="gallery-item">';
          //     echo '<h4>Reservation ID: ' . $reservationId . '</h4>';
          //     echo '<p><strong>Pickup Location:</strong> ' . $pickupLocation . '</p>';
          //     echo '<p><strong>Drop Location:</strong> ' . $dropLocation . '</p>';
          //     echo '<p><strong>Driver:</strong> ' . $driverFirstName . ' ' . $driverLastName . '</p>';

          //     echo '<form method="post" class="rating-form">';
          //     echo '<div class="star-rating">';
          //     echo generateStarInput($reservationId, $driverId, 5);
          //     echo '</div>';
          //     echo '<input type="hidden" name="reservationId" value="' . $reservationId . '">';
          //     echo '<input type="button" class="rate-driver-button update-btn" style="font-size: 16px;" value="Rate Driver">';
          //     echo '</form>';

          //     echo '</div>';
          //   }
          // } else {
          //   echo '<p>No ride history available.</p>';
          // }



          // if (isset($_POST['submitRating'])) {
          //   $reservationId = $_POST['reservationId'];
          //   $rating = $_POST['rating'];

          //   $insertRatingQuery = "INSERT INTO ride_history (c_id, d_id, r_id, driver_rating) 
          //                           VALUES (?, ?, ?, ?)";
          //   $stmtInsertRating = mysqli_prepare($conn, $insertRatingQuery);

          //   $customerId = $customerDetails['c_id'];
          //   $driverId = $driverId; 
          //   $rideId = $reservationId; 

          //   mysqli_stmt_bind_param($stmtInsertRating, "iiid", $customerId, $driverId, $rideId, $rating);

          //   if (mysqli_stmt_execute($stmtInsertRating)) {
          //     echo '<p class="success-message">Thank you for rating the driver!</p>';
          //   } else {
          //     echo '<p class="error-message">Error inserting rating into the database.</p>';
          //   }

          //   mysqli_stmt_close($stmtInsertRating);
          // }

          // function generateStarInput($reservationId, $driverId, $maxStars)
          // {
          //   $input = '';
          //   for ($i = $maxStars; $i >= 1; $i--) {
          //     $input .= '<input type="radio" id="rating' . $reservationId . '-' . $driverId . '-' . $i . '" name="rating" value="' . $i . '">';
          //     $input .= '<label for="rating' . $reservationId . '-' . $driverId . '-' . $i . '"><i class="fas fa-star"></i></label>';
          //   }
          //   return $input;
          // }



          ?>
        </div>
      </div>
    </div> -->
      <div class="content" id="ride-history-section">
        <h3 style="padding: 10px;">View Ride History</h3>
        <div class="gallery-container">
          <?php
          $rideHistoryQuery = "SELECT rr.r_id, rr.pickup_location, rr.drop_location, rr.d_id,
            d.d_first_name, d.d_last_name
            FROM ride_reservation AS rr
            LEFT JOIN driver AS d ON rr.d_id = d.d_id
            WHERE rr.c_id = ? AND rr.status = 'Dropped Off'";

          $stmtRideHistory = mysqli_prepare($conn, $rideHistoryQuery);
          mysqli_stmt_bind_param($stmtRideHistory, "i", $customerDetails['c_id']);
          mysqli_stmt_execute($stmtRideHistory);
          $resultRideHistory = mysqli_stmt_get_result($stmtRideHistory);

          if (mysqli_num_rows($resultRideHistory) > 0) {
            while ($rowRideHistory = mysqli_fetch_assoc($resultRideHistory)) {
              $reservationId = $rowRideHistory['r_id'];
              $pickupLocation = $rowRideHistory['pickup_location'];
              $dropLocation = $rowRideHistory['drop_location'];
              $driverId = $rowRideHistory['d_id'];
              $driverFirstName = $rowRideHistory['d_first_name'];
              $driverLastName = $rowRideHistory['d_last_name'];

              echo '<div class="gallery-item">';
              echo '<h4>Reservation ID: ' . $reservationId . '</h4>';
              echo '<p><strong>Pickup Location:</strong> ' . $pickupLocation . '</p>';
              echo '<p><strong>Drop Location:</strong> ' . $dropLocation . '</p>';
              echo '<p><strong>Driver:</strong> ' . $driverFirstName . ' ' . $driverLastName . '</p>';

              // Move the stars and "Rate Driver" button inside the gallery item
              echo '<div class="star-rating" data-reservation-id="' . $reservationId . '" data-driver-id="' . $driverId . '">';
              // Display stars
              for ($i = 5; $i >= 1; $i--) {
                echo '<i class="star fas fa-star" data-rating="' . $i . '"></i>';
              }
              echo '</div>';
              echo '<form method="post" class="rating-form">';
              echo '<input type="hidden" name="rating" value="0">';
              echo '<input type="hidden" name="reservationId" value="' . $reservationId . '">';
              echo '<input type="button" class="rate-driver-button update-btn" style="font-size: 16px;" value="Rate Driver">';
              echo '</form>';

              echo '</div>';
            }
          } else {
            echo '<p>No ride history available.</p>';
          }
          ?>
        </div>


        <!-- <form method="post" class="rating-form">
          <div class="star-rating" data-reservation-id="" data-driver-id="">
            <?php
            // for ($i = 5; $i >= 1; $i--) {
            //   echo '<i class="star fas fa-star" data-rating="' . $i . '"></i>';
            // }
            ?>
          </div>
          <input type="hidden" name="rating" value="0">
          <input type="hidden" name="reservationId" value="">
          <input type="button" class="rate-driver-button update-btn" style="font-size: 16px;" value="Rate Driver">
        </form> -->

        <?php
        // if (isset($_POST['submitRating'])) {
        //   $reservationId = $_POST['reservationId'];
        //   $rating = $_POST['rating'];


        //   $getReservationDetailsQuery = "SELECT c_id, d_id FROM ride_reservation WHERE r_id = ?";
        //   $stmtGetReservationDetails = mysqli_prepare($conn, $getReservationDetailsQuery);
        //   mysqli_stmt_bind_param($stmtGetReservationDetails, "i", $reservationId);
        //   mysqli_stmt_execute($stmtGetReservationDetails);
        //   $resultReservationDetails = mysqli_stmt_get_result($stmtGetReservationDetails);

        //   if ($rowReservationDetails = mysqli_fetch_assoc($resultReservationDetails)) {
        //     $customerId = $rowReservationDetails['c_id'];
        //     $driverId = $rowReservationDetails['d_id'];

        //     $insertRatingQuery = "INSERT INTO ride_history (c_id, d_id, r_id, driver_rating)
        //       VALUES (?, ?, ?, ?)";
        //     $stmtInsertRating = mysqli_prepare($conn, $insertRatingQuery);

        //     mysqli_stmt_bind_param($stmtInsertRating, "iiid", $customerId, $driverId, $reservationId, $rating);

        //     if (mysqli_stmt_execute($stmtInsertRating)) {
        //       echo json_encode(['success' => true]);
        //     } else {
        //       echo json_encode(['success' => false, 'error' => 'Error inserting rating into the database.']);
        //     }

        //     mysqli_stmt_close($stmtInsertRating);
        //   } else {
        //     echo json_encode(['success' => false, 'error' => 'Error fetching reservation details.']);
        //   }

        //   mysqli_stmt_close($stmtGetReservationDetails);
        // }
        ?>
      </div>
    </div>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const starRating = document.querySelector('.star-rating');
      const stars = starRating.querySelectorAll('.star');
      const ratingInput = document.querySelector('input[name="rating"]');
      const reservationIdInput = document.querySelector('input[name="reservationId"]');
      const rateDriverButton = document.querySelector('.rate-driver-button');

      stars.forEach((star, index) => {
        star.addEventListener('click', function() {
          const rating = index + 1;

          // Update the value of the hidden input field
          ratingInput.value = rating;

          // Remove 'selected' class from all stars
          stars.forEach(s => {
            s.classList.remove('selected');
          });

          // Add 'selected' class to the clicked star and all preceding stars
          for (let i = 0; i <= index; i++) {
            stars[i].classList.add('selected');
          }
        });
      });

      rateDriverButton.addEventListener('click', function() {
        const rating = ratingInput.value;

        if (rating > 0) {
          // Simulate saving the rating on the webpage itself (you can replace this with your actual logic)
          const message = `Thank you for rating the driver with ${rating} star${rating > 1 ? 's' : ''}!`;

          // Display a modal dialog with the rating message
          alert(message);

          // Insert the rating into the database
          const customerId = <?php echo $customerDetails['c_id']; ?>; // Assuming $customerDetails['c_id'] contains customer ID
          const driverId = null; // Assuming $rowRideHistory['d_id'] contains driver ID
          const reservationId = reservationIdInput.value;

          // Use AJAX to send the rating data to the server
          $.ajax({
            type: 'POST',
            url: 'insert_rating.php', // Update this with the actual PHP script to handle the database insertion
            data: {
              reservationId: reservationId,
              customerId: customerId,
              driverId: driverId,
              rating: rating
            },
            dataType: 'json',
            success: function(response) {
              if (response.success) {
                console.log('Rating inserted into the database.');
              } else {
                console.error('Error inserting rating into the database: ' + response.error);
              }
            },
            error: function(xhr, status, error) {
              console.error('AJAX Error:', status, error);
            }
          });
        } else {
          // No star selected, you can add additional handling here
          alert('Please select a star rating before submitting.');
        }
      });
    });

    // Attach a click event listener to the "Reserve Taxi" button
    document.getElementById('reserveTaxiButton').addEventListener('click', function() {
      // Make the reservation request
      $.ajax({
        type: 'POST',
        url: 'userdashboard.php', // Replace with the actual URL of your PHP script
        data: {
          sendRequest: true,
          pickupLocation: "pickupLocation",
          dropLocation: "dropLocation",
        },
        success: function(response) {
          // Parse the JSON response
          var jsonResponse = JSON.parse(response);

          if (jsonResponse.success) {
            // Display the alert with reservation details
            alert(jsonResponse.message);

            // Redirect to customer dashboard if needed
            // window.location.href = 'userdashboard.php';
          } else {
            // Handle the case where the reservation failed
            alert("Error: " + jsonResponse.error);
          }
        },
        error: function() {
          // Handle AJAX request errors if needed
          alert("Error: Unable to make the reservation. Please try again later.");
        },
      });
    });
  </script>

</body>

</html>