<?php
include 'config.php';
session_start();

// Function to fetch total reservations with a specific status
function getTotalReservations($conn, $status)
{
  $queryReservations = mysqli_query($conn, "SELECT COUNT(*) AS total_reservations FROM ride_reservation WHERE status = '$status'");
  $reservationData = mysqli_fetch_assoc($queryReservations);
  return $reservationData['total_reservations'];
}

// Function to fetch total ratings
function getTotalRatings($conn)
{
  $queryRatings = mysqli_query($conn, "SELECT COUNT(*) AS total_ratings FROM ride_history WHERE driver_rating IS NOT NULL");
  $ratingData = mysqli_fetch_assoc($queryRatings);
  return $ratingData['total_ratings'];
}

// Function to fetch total registered customers
function getTotalCustomers($conn)
{
  $queryCustomers = mysqli_query($conn, "SELECT COUNT(*) AS total_customers FROM customer");
  $customerData = mysqli_fetch_assoc($queryCustomers);
  return $customerData['total_customers'];
}

// Function to fetch total registered drivers
function getTotalDrivers($conn)
{
  $queryDrivers = mysqli_query($conn, "SELECT COUNT(*) AS total_drivers FROM driver");
  $driverData = mysqli_fetch_assoc($queryDrivers);
  return $driverData['total_drivers'];
}

// Fetch operator details from the database
$operator_id = $_SESSION['o_id'];
$query = mysqli_query($conn, "SELECT * FROM operator WHERE o_id = '$operator_id'");
$operatorDetails = mysqli_fetch_assoc($query);

// Check if operator details are fetched successfully
if (!$operatorDetails) {
  // Handle the error, redirect, or display a message as needed
  die('Error fetching operator details');
}

// Initialize variables
$totalCustomers = getTotalCustomers($conn);
$totalDrivers = getTotalDrivers($conn);

// Handle update button click
if (isset($_POST['update'])) {
  // If the form is submitted, retrieve values from the form
  $totalPendingReservations = getTotalReservations($conn, 'Pending');
  $totalCompletedReservations = getTotalReservations($conn, 'Dropped Off');
  $totalCancelledReservations = getTotalReservations($conn, 'Cancelled');
  $totalRatings = getTotalRatings($conn);
} else {
  // If the form is not submitted, retrieve values from the database
  $totalPendingReservations = getTotalReservations($conn, 'Pending');
  $totalCompletedReservations = getTotalReservations($conn, 'Dropped Off');
  $totalCancelledReservations = getTotalReservations($conn, 'Cancelled');
  $totalRatings = getTotalRatings($conn);
}
// Handle assign taxi button click
if (isset($_POST['assignTaxi'])) {
  // Get values from the form
  $selectedDriver = mysqli_real_escape_string($conn, $_POST['selectedDriver']);
  $taxiModel = mysqli_real_escape_string($conn, $_POST['taxiModel']);
  $taxiNumber = mysqli_real_escape_string($conn, $_POST['taxiNumber']);

  // Update the selected driver's record with the assigned taxi model and number
  $updateQuery = "UPDATE driver SET d_taxi_model = '$taxiModel', d_taxi_number = '$taxiNumber' WHERE d_id = '$selectedDriver'";

  if (mysqli_query($conn, $updateQuery)) {
    echo '<p class="success-message">Taxi assigned successfully!</p>';
  } else {
    echo '<p class="error-message">Error assigning taxi: ' . mysqli_error($conn) . '</p>';
  }
}

?>



<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Operator Dashboard</title>
  <link rel="stylesheet" href="styles2.css">
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <script src="script.js"></script>
  <style>
    .operator-dashboard {
      background-color: #D9D1CB;
    }

    .header {
      background-color: rgb(74, 4, 4);
      color: #fff;
      text-align: center;
      padding: 10px;
      border-radius: 5px;
      margin-bottom: 20px;
      width: 800px;
    }

    .menu-item {
      cursor: pointer;
      padding: 10px;
      border-radius: 5px;
      margin-bottom: 10px;
      background-color: rgba(255, 255, 255, 0.8);
      color: black;
      text-decoration: none;
      display: block;
      text-align: center;
      font-weight: bolder;
    }

    .menu-item:hover {
      background-color: rgba(255, 255, 255, 0.3);
    }

    #overview-section {
      display: inline;
      flex-wrap: wrap;
      justify-content: center;
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

    .detail-item {
      margin: 10px;
      padding: 10px;
      border: 1px solid rgb(74, 4, 4);
      border-radius: 8px;
      text-align: center;
      align-items: center;
      align-content: center;
      max-width: 400px;
      font-size: 16px;
      display: inline-block;
    }


    .success-message {
      color: black;
      font-size: 18px;
      margin-top: 20px;
      text-align: center;
    }

    .logo {
      text-align: center;
      padding-top: 10px;
    }

    .logo img {
      width: 130px;
      height: 130px;
      border-radius: 50%;
    }
  </style>
</head>

<body class="operator-dashboard">
  <div class="dashboard-container">
    <div class="sidebar">
      <div class="menu-item" onclick="showOverview()">Overview</div>
      <div class="menu-item" onclick="showAssignTaxis()">Assign Taxis</div>
      <div class="menu-item" onclick="showReserveTaxiForUser()">Reserve Taxi for Customer</div>
      <div class="menu-item" onclick="logout()">Logout</div>
    </div>
    <div class="content-container">
      <div class="header">
        <h2>Welcome to Auto Cars, <?php echo $operatorDetails['o_username']; ?>!</h2>
      </div>
      <div class="logo">
        <img src="autocarslogo.png" alt="Auto Cars Logo">
      </div>
      <!-- <div id="content-wrapper" class="content"> -->
      <div class="content" id="overview-section">
        <h3>Overview</h3>
        <div class="detail-item"><strong>Total Registered Customers - </strong><?php echo getTotalCustomers($conn); ?></div>
        <div class="detail-item"><strong>Total Registered Drivers - </strong><?php echo getTotalDrivers($conn); ?></div>
        <div class="detail-item"><strong>Total Pending Reservations - </strong><?php echo $totalPendingReservations; ?></div>
        <div class="detail-item"><strong>Total Completed Reservations - </strong><?php echo $totalCompletedReservations; ?></div>
        <div class="detail-item"><strong>Total Cancelled Reservations - </strong><?php echo $totalCancelledReservations; ?></div>
        <div class="detail-item"><strong>Total Ratings - </strong><?php echo $totalRatings; ?></div>
        <form method="post">
          <input type="submit" name="update" value="Update" class="update-btn">
        </form>
      </div>
      <div class="content" id="assign-taxis-section">
        <!-- Inside the "Assign Taxis" section -->
        <h3 style="margin-bottom: 15px;">Assign Taxis</h3>
        <form method="post" id="assignTaxiForm" style="text-align: left;">
          <!-- Dropdown menu to select the driver -->
          <div style="margin-bottom: 15px;">
            <label for="selectedDriver" style="font-size: 18px;">Select Driver:</label>
            <select id="selectedDriver" name="selectedDriver" required style="font-size: 16px;">
              <?php
              // Fetch and display all drivers
              $driversQuery = mysqli_query($conn, "SELECT d_id, d_username FROM driver");
              while ($driver = mysqli_fetch_assoc($driversQuery)) {
                echo "<option value='{$driver['d_id']}'>{$driver['d_username']}</option>";
              }
              ?>
            </select>
          </div>

          <!-- Add input fields for taxi model and taxi number -->
          <div style="margin-bottom: 15px;">
            <label for="taxiModel" style="font-size: 18px;">Taxi Model:</label>
            <input type="text" id="taxiModel" name="taxiModel" required style="font-size: 16px;">
          </div>

          <div style="margin-bottom: 15px;">
            <label for="taxiNumber" style="font-size: 18px;">Taxi License Plate:</label>
            <input type="text" id="taxiNumber" name="taxiNumber" required style="font-size: 16px;">
          </div>

          <input type="submit" name="assignTaxi" value="Assign Taxi" class="update-btn" style="font-size: 16px;">
        </form>
      </div>
      <div class="content" id="reserve-taxi-for-user-section">
        <h3>Reserve Taxi for Customer</h3>
        <!-- Add your reserve taxi for user content here -->
        <form method="post" id="reserveTaxiForUserForm" style="text-align: left;">
          <!-- Input fields for user's pickup location and drop location -->
          <div style="margin-bottom: 15px;">
            <label for="pickupLocation" style="font-size: 18px;">Enter Customer's Pickup Location:</label>
            <input type="text" id="pickupLocation" name="pickupLocation" required style="font-size: 16px;">
          </div>

          <div style="margin-bottom: 15px;">
            <label for="dropLocation" style="font-size: 18px;">Enter Customer's Drop Location:</label>
            <input type="text" id="dropLocation" name="dropLocation" required style="font-size: 16px;">
          </div>
          <div style="margin-top: 15px; margin-bottom: 15px;">
            <label for="selectedDriverLocation" style="font-size: 18px;">Enter Driver's Location:</label>
            <input type="text" id="selectedDriverLocation" name="selectedDriverLocation" required style="font-size: 16px;">
          </div>
          <div style="margin-bottom: 15px;">
            <label for="customerPhoneNumber" style="font-size: 18px;">Enter Customer's Phone Number:</label>
            <input type="text" id="customerPhoneNumber" name="customerPhoneNumber" required style="font-size: 16px;">
          </div>
          <input type="hidden" id="customerID" name="customerID" value="<?php echo $customerDetails['c_id']; ?>">
          <input type="hidden" id="driverID" name="driverID" value="">

          <input type="submit" name="checkDrivers" value="Check Available Closest Drivers" class="update-btn" style="font-size: 16px;">

          <!-- Input field for entering driver's location -->
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
                            WHERE dl.current_driver_location LIKE '%$pickupCity%'
                            LIMIT 4";

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

        // Fetch customer details from the database
        $customerID = $_SESSION['c_id'];
        $customerQuery = mysqli_query($conn, "SELECT * FROM customer WHERE c_id = '$customerID'");
        $customerDetails = mysqli_fetch_assoc($customerQuery);
        // Get operator ID from the session or wherever it is stored
        $operatorID = $_SESSION['o_id']; // Replace with your actual session variable name

        // Get values from the form
        $pickupLocation = isset($_POST['pickupLocation']) ? mysqli_real_escape_string($conn, $_POST['pickupLocation']) : '';
        $dropLocation = isset($_POST['dropLocation']) ? mysqli_real_escape_string($conn, $_POST['dropLocation']) : '';

        // Initialize $stmtInsertReservation here
        $stmtInsertReservation = mysqli_stmt_init($conn);

        // Prepare and execute the statement
        mysqli_stmt_prepare($stmtInsertReservation, "INSERT INTO ride_reservation (pickup_location, drop_location, d_id, dl_id, o_id, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
        mysqli_stmt_bind_param($stmtInsertReservation, "ssiii", $pickupLocation, $dropLocation, $selectedDriverId, $driverLocationId, $operatorID);

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
    alert('Taxi reserved successfully for customer! We have sent the customer an SMS with driver details.\\nClosest driver details:\\nDriver: $driverFirstName $driverLastName\\nContact: $driverNumber\\nTaxi Model: $taxiModel\\nTaxi Number: $taxiNumber\\nComing from: $driverCurrentLocation');
    window.location.href = 'operatordashboard.php'; // Redirect to operator dashboard
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
    </div>
  </div>
  </div>
  </div>

</body>
<script>
  <?php

  if (isset($_POST['reserveDriver'])) {
    // Construct the message string including driver details
    $message = "Dear operator, Taxi reserved successfully for customer! We have sent the customer an SMS with driver details.\n";
    $message .= "Driver: $driverFirstName $driverLastName\n";
    $message .= "Contact: $driverNumber\n";
    $message .= "Taxi Model: $taxiModel\n";
    $message .= "Taxi Number: $taxiNumber\n";
    $message .= "Coming from: $driverCurrentLocation";

    // Encode the message as JSON
    $response = json_encode(['success' => true, 'message' => $message]);
  } else {
    // Return an error message or handle the case where the reservation failed
    $response = json_encode(['success' => false, 'error' => "Error: Unable to reserve the taxi. Please try again later."]);
  }

  // Output the JSON response
  echo $response;
  echo "<script>console.log('Message:', " . json_encode($message) . ");</script>";

  // echo "<script>
  //     alert('Taxi reserved successfully for customer! We have sent the customer an SMS with driver details.\\nDriver details:\\nDriver: $driverFirstName $driverLastName\\nContact: $driverNumber\\nTaxi Model: $taxiModel\\nTaxi Number: $taxiNumber\\nComing from: $driverCurrentLocation');";
  //     echo "window.location.href = 'operatordashboard.php; // Redirect to customer dashboard
  //     </script>";
  // } else {
  // echo '<p class="error-message">Error updating reservation with selected driver: ' . mysqli_error($conn) . '</p>';
  // }
  ?>
</script>

</html>