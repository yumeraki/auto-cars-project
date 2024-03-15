<?php
include 'config.php';
session_start();

// Check if the driver is logged in
if (!isset($_SESSION['d_id'])) {
  header('Location: driverlogin.php');
  exit();
}

// Fetch driver details from the database
$d_id = $_SESSION['d_id'];
$query = mysqli_query($conn, "SELECT * FROM driver WHERE d_id = '$d_id'");
$driverDetails = mysqli_fetch_assoc($query);

// Check if driver details are fetched successfully
if (!$driverDetails) {
  // Handle the error, redirect, or display a message as needed
  die('Error fetching driver details');
}

// Handle update button click
if (isset($_POST['updateCurrentLocation'])) {
  $currentLocation = mysqli_real_escape_string($conn, $_POST['currentLocation']);

  // Update the driver's current location in the 'driver_location' table
  $updateLocationQuery = "INSERT INTO driver_location (d_id, current_driver_location) VALUES ('$d_id', '$currentLocation') ON DUPLICATE KEY UPDATE current_driver_location = '$currentLocation'";

  if (mysqli_query($conn, $updateLocationQuery)) {
    echo '<p class="success-message">Location updated successfully!</p>';
  } else {
    echo '<p class="error-message">Error updating location: ' . mysqli_error($conn) . '</p>';
  }
}

// Handle AJAX request to fetch reservation details
if (isset($_POST['getReservationDetails'])) {
  $reservationID = mysqli_real_escape_string($conn, $_POST['reservationID']);

  // Fetch reservation details from the database based on the reservationID
  $reservationDetailsQuery = mysqli_query($conn, "SELECT * FROM ride_reservation WHERE r_id = '$reservationID'");
  $reservationDetails = mysqli_fetch_assoc($reservationDetailsQuery);

  // Fetch customer details
  $customerID = $reservationDetails['c_id'];
  $customerQuery = mysqli_query($conn, "SELECT * FROM customer WHERE c_id = '$customerID'");
  $customerDetails = mysqli_fetch_assoc($customerQuery);

  // Output reservation and customer details as HTML
  echo '<p><strong>Reservation ID:</strong> ' . $reservationDetails['r_id'] . '</p>';
  echo '<p><strong>Customer ID:</strong> ' . $customerDetails['c_id'] . '</p>';
  echo '<p><strong>Customer Name:</strong> ' . $customerDetails['c_first_name'] . ' ' . $customerDetails['c_last_name'] . '</p>';
  echo '<p><strong>Email:</strong> ' . $customerDetails['c_email'] . '</p>';
  echo '<p><strong>Phone Number:</strong> ' . $customerDetails['c_number'] . '</p>';
  echo '<p><strong>Pickup Location:</strong> ' . $reservationDetails['pickup_location'] . '</p>';
  echo '<p><strong>Drop Location:</strong> ' . $reservationDetails['drop_location'] . '</p>';
  echo '<p><strong>Status:</strong> ' . $reservationDetails['status'] . '</p>';
}

// Add this code at the beginning of your PHP file

if (isset($_POST['getReservationDetails'])) {
  // Handle the AJAX request to fetch reservation details
  $reservationID = mysqli_real_escape_string($conn, $_POST['reservationID']);

  // Fetch reservation details from the database based on the reservationID
  // You need to adjust this query based on your database structure
  $reservationDetailsQuery = mysqli_query($conn, "SELECT * FROM ride_reservation WHERE r_id = '$reservationID'");
  $reservationDetails = mysqli_fetch_assoc($reservationDetailsQuery);

  // Fetch customer details
  $customerID = $reservationDetails['c_id'];
  $customerQuery = mysqli_query($conn, "SELECT * FROM customer WHERE c_id = '$customerID'");
  $customerDetails = mysqli_fetch_assoc($customerQuery);
} elseif (isset($_POST['confirmReservation'])) {
  // Handle the AJAX request to confirm the reservation
  $reservationID = mysqli_real_escape_string($conn, $_POST['reservationID']);

  // Update the reservation status to confirmed in the database
  // You need to adjust this query based on your database structure
  $confirmReservationQuery = mysqli_query($conn, "UPDATE ride_reservation SET status = 'Confirmed' WHERE r_id = '$reservationID'");

  if ($confirmReservationQuery) {
    echo 'Reservation confirmed successfully!';
  } else {
    echo 'Error confirming reservation: ' . mysqli_error($conn);
  }
}

if (isset($_POST['droppedOff'])) {
  // Handle the AJAX request to mark the reservation as dropped off
  $reservationID = mysqli_real_escape_string($conn, $_POST['reservationID']);

  // Update the reservation status to 'Dropped Off' in the database
  $droppedOffQuery = mysqli_query($conn, "UPDATE ride_reservation SET status = 'Dropped Off' WHERE r_id = '$reservationID'");

  if ($droppedOffQuery) {
    echo 'You have successfully dropped off the customer! Thank You.';
  } else {
    echo 'Error marking as dropped off: ' . mysqli_error($conn);
  }

  // Terminate the script after handling the AJAX request
  exit();
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Driver Dashboard</title>
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
    .driver-dashboard {
      background-color: #D9D1CB;
    }

    .dashboard-container {
      display: flex;
      max-width: 1200px;
      height: auto;
      margin: 20px auto;
      background-color: rgba(255, 255, 255, 0.5);
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .sidebar {
      width: 200px;
      background-color: rgb(74, 4, 4);
      padding: 20px;
      color: #fff;
      box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
      border-radius: 10px 0 0 10px;
    }

    .content-container {
      flex-grow: 1;
      padding: 20px;
      align-items: center;
      display: flex;
      flex-direction: column;
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

    .content {
      display: none;
      text-align: center;
    }

    #profile-section {
      display: block;
    }

    .content h3 {
      color: rgb(74, 4, 4);
      font-size: 20px;
      padding-bottom: 10px;
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

    .driver-details {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
    }

    .detail-item {
      margin: 10px;
      padding: 10px;
      border: 1px solid rgb(74, 4, 4);
      border-radius: 8px;
      text-align: center;
      max-width: 400px;
      font-size: 16px;
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
      font-size: 16px;
      cursor: pointer;
      border-radius: 5px;
      margin-top: 20px;
      align-items: center;
    }

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
  </style>
</head>

<body class="driver-dashboard">
  <div class="dashboard-container">
    <div class="sidebar">
      <div class="menu-item" onclick="showProfile()">Your Profile</div>
      <div class="menu-item" onclick="showSeeReservationRequests()">Enter Your Current Location</div>
      <div class="menu-item" onclick="showUserRideReservations()">View Reservations</div>
      <div class="menu-item" onclick="showRideHistory()">View Ride History</div>
      <div class="menu-item" onclick="logout()">Logout</div>
    </div>
    <div class="content-container">
      <div class="header">
        <h2>Welcome to Auto Cars, <?php echo $driverDetails['d_username']; ?>!</h2>
      </div>
      <div class="logo">
        <img src="autocarslogo.png" alt="Auto Cars Logo">
      </div>
      <div class="content" id="profile-section">
        <h3>Your Profile</h3>
        <div class="driver-details">
          <div class="detail-item"><strong>Driver ID</strong><br><?php echo $driverDetails['d_id']; ?></div>
          <div class="detail-item"><strong>Name</strong><br><?php echo $driverDetails['d_first_name'] . ' ' . $driverDetails['d_last_name']; ?></div>
          <div class="detail-item"><strong>Email</strong><br><?php echo $driverDetails['d_email']; ?></div>
          <div class="detail-item"><strong>Phone Number</strong><br><?php echo $driverDetails['d_number']; ?></div>
          <div class="detail-item"><strong>Username</strong><br><?php echo $driverDetails['d_username']; ?></div>
          <div class="detail-item"><strong>Taxi Model</strong><br><?php echo $driverDetails['d_taxi_model']; ?></div>
          <div class="detail-item"><strong>Taxi Number</strong><br><?php echo $driverDetails['d_taxi_number']; ?></div>
        </div>
      </div>
      <div class="content" id="see-reservation-requests-section">
        <h3>Enter Your Current Location</h3>
        <!-- Add your see reservation requests content here -->
        <!-- You can fetch and display reservation requests from the database -->
        <form method="post" id="currentLocationForm" style="text-align: left; margin-bottom: 15px;">
          <label for="currentLocation" style="font-size: 18px;">Your Location:</label>
          <input type="text" id="currentLocation" name="currentLocation" required style="font-size: 16px;">
        </form>

        <!-- Button to initiate viewing reservation requests -->
        <button onclick="updateCurrentLocation()" class="update-btn" style="font-size: 16px;">Update</button>
      </div>
      <!-- The rest of your HTML content -->

      <div class="content" id="reservation-history-section">
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

          // Assuming you have the current driver's ID stored in $currentDriverId
          $currentDriverId = $_SESSION['d_id'];

          $rideReservationsQuery = "SELECT rr.r_id, rr.pickup_location, rr.drop_location, rr.status, rr.d_id,
        c.c_id, c.c_first_name, c.c_last_name, c.c_email, c.c_number, c.c_address, c.c_username,
        dl.current_driver_location
        FROM ride_reservation AS rr
        LEFT JOIN driver AS d ON rr.d_id = d.d_id
        LEFT JOIN driver_location AS dl ON d.d_id = dl.d_id
        LEFT JOIN customer AS c ON rr.c_id = c.c_id
        WHERE rr.d_id = ?";

          $stmtRideReservations = mysqli_prepare($conn, $rideReservationsQuery);
          mysqli_stmt_bind_param($stmtRideReservations, "i", $currentDriverId);
          mysqli_stmt_execute($stmtRideReservations);
          $resultRideReservations = mysqli_stmt_get_result($stmtRideReservations);

          if (mysqli_num_rows($resultRideReservations) > 0) {
            echo '<table class="reservation-table">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Reservation ID</th>';
            echo '<th>Pickup Location</th>';
            echo '<th>Drop Location</th>';
            echo '<th>Status</th>';
            echo '<th>Customer Name</th>';
            echo '<th>Actions</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            while ($rowRideReservations = mysqli_fetch_assoc($resultRideReservations)) {
              $reservationId = $rowRideReservations['r_id'];
              $pickupLocation = $rowRideReservations['pickup_location'];
              $dropLocation = $rowRideReservations['drop_location'];
              $status = $rowRideReservations['status'];
              $customerName = $rowRideReservations['c_first_name'] . ' ' . $rowRideReservations['c_last_name'];

              echo '<tr>';
              echo '<td>' . $reservationId . '</td>';
              echo '<td>' . $pickupLocation . '</td>';
              echo '<td>' . $dropLocation . '</td>';
              echo '<td><span class="status-label';

              // Add a CSS class based on the status
              if ($status == 'Pending') {
                echo ' label-pending';
              } elseif ($status == 'Cancelled') {
                echo ' label-cancelled';
              } elseif ($status == 'Dropped Off') {
                echo ' label-droppedoff';
              }

              echo '">' . $status . '</span></td>';
              echo '<td>' . $customerName . '</td>';
              echo '<td>';
              echo '<button class="btn btn-primary btn-view-details" data-toggle="modal" data-target="#customerDetailsModal' . $reservationId . '">View Details</button>';
              echo '</td>';
              echo '</tr>';

              $modalId = 'customerDetailsModal' . $reservationId;

              // Modal HTML for each reservation
              echo '<div class="modal fade" id="' . $modalId . '" tabindex="-1" role="dialog" aria-labelledby="customerDetailsModalLabel" aria-hidden="true">';
              echo '<div class="modal-dialog" role="document">';
              echo '<div class="modal-content">';
              echo '<div class="modal-header">';
              echo '<h5 class="modal-title" id="customerDetailsModalLabel">Customer Details</h5>';
              echo '<button type="button" class="close" data-dismiss="modal" aria-label="Close">';
              echo '<span aria-hidden="true">&times;</span>';
              echo '</button>';
              echo '</div>';
              echo '<div class="modal-body">';
              // Output customer details placeholder
              echo '<div id="customerDetailsPlaceholder' . $reservationId . '">';
              echo '<p><strong>Reservation ID:</strong> ' . $reservationId . '</p>';
              echo '<p><strong>Customer ID:</strong> ' . $rowRideReservations['c_id'] . '</p>';
              echo '<p><strong>Customer Name:</strong> ' . $customerName . '</p>';
              echo '<p><strong>Email:</strong> ' . $rowRideReservations['c_email'] . '</p>';
              echo '<p><strong>Phone Number:</strong> ' . $rowRideReservations['c_number'] . '</p>';
              echo '<p><strong>Pickup Location:</strong> ' . $pickupLocation . '</p>';
              echo '<p><strong>Drop Location:</strong> ' . $dropLocation . '</p>';
              echo '<p><strong>Status:</strong> ' . $status . '</p>';
              echo '</div>';
              echo '</div>';
              echo '<div class="modal-footer">';
              echo '<button type="button" class="btn btn-primary btn-confirm-reservation" data-reservation-id="' . $reservationId . '">Confirm Reservation</button>';
              echo '<button type="button" class="btn btn-primary btn-dropped-off mr-2" data-reservation-id="' . $reservationId . '">Dropped Off</button>
              ';

              echo '<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>';
              echo '</div>';
              echo '</div>';
              echo '</div>';
              echo '</div>';
            }

            echo '</tbody>';
            echo '</table>';
            echo '<button type="button" class="btn update-btn btn-primary btn-update-table">Update</button>';
          } else {
            echo '<p>No reservations available.</p>';
          }

          // Close the statement after the loop
          mysqli_stmt_close($stmtRideReservations);
          ?>
        </div>
      </div>
      <div class="content" id="ride-history-section">
        <h3 style="padding: 10px;">View Ride History</h3>
        <div class="gallery-container">
          <?php
          $rideHistoryQuery = "SELECT rh.rh_id, rh.c_id,
                    c.c_first_name AS customer_first_name, c.c_last_name AS customer_last_name,
                    rr.r_id, rr.pickup_location, rr.drop_location, rh.driver_rating
                    FROM ride_history AS rh
                    LEFT JOIN ride_reservation AS rr ON rh.r_id = rr.r_id
                    LEFT JOIN customer AS c ON rh.c_id = c.c_id
                    WHERE rh.d_id = ?";

          $stmtRideHistory = mysqli_prepare($conn, $rideHistoryQuery);
          mysqli_stmt_bind_param($stmtRideHistory, "i", $currentDriverId);
          mysqli_stmt_execute($stmtRideHistory);
          $resultRideHistory = mysqli_stmt_get_result($stmtRideHistory);

          if (mysqli_num_rows($resultRideHistory) > 0) {
            while ($rowRideHistory = mysqli_fetch_assoc($resultRideHistory)) {
              $customerId = $rowRideHistory['c_id'];
              $customerFirstName = $rowRideHistory['customer_first_name'];
              $customerLastName = $rowRideHistory['customer_last_name'];
              $pickupLocation = $rowRideHistory['pickup_location'];
              $dropLocation = $rowRideHistory['drop_location'];
              $driverRating = $rowRideHistory['driver_rating'];
              $rideHistoryId = $rowRideHistory['rh_id'];
              $rideReservationId = $rowRideHistory['r_id'];

              echo '<div class="gallery-item">';
              echo '<h4>Ride History ID: ' . $rideHistoryId . '</h4>';
              echo '<p><strong>Ride Reservation ID:</strong> ' . $rideReservationId . '</p>';
              echo '<p><strong>Customer ID:</strong> ' . $customerId . '</p>';
              echo '<p><strong>Customer Name:</strong> ' . $customerFirstName . ' ' . $customerLastName . '</p>';
              echo '<p><strong>Pickup Location:</strong> ' . $pickupLocation . '</p>';
              echo '<p><strong>Drop Location:</strong> ' . $dropLocation . '</p>';
              echo '<p><strong>Rating:</strong> Customer rated you ' . $driverRating . ' stars!</p>';
              echo '</div>';
            }
          } else {
            echo '<p>No ride history available.</p>';
          }

          // Close the statement after the loop
          mysqli_stmt_close($stmtRideHistory);
          ?>

        </div>
      </div>

      <script>
        $(document).ready(function() {
          // Update the event listener to call the showModal function
          document.querySelectorAll('.btn-view-details').forEach(function(button) {
            button.addEventListener('click', function() {
              var reservationId = button.getAttribute('data-reservation-id');
              showModal(reservationId);
            });
          });
        });
      </script>



    </div>
    <script>
      // Function to update the driver's current location
      function updateCurrentLocation() {
        var currentLocation = $('#currentLocation').val();

        $.ajax({
          type: 'POST',
          url: 'driverdashboard.php',
          data: {
            updateCurrentLocation: true,
            currentLocation: currentLocation
          },
          success: function(response) {
            console.log(response);
          },
          error: function(error) {
            console.log('Error updating location:', error);
          }
        });
      }

      // Function to show the modal based on reservation ID
      function showModal(reservationId, customerDetails) {
        var modalId = "#customerDetailsModal" + reservationId; // Corrected modal ID

        // Update modal content with customer details
        $(modalId).find('.modal-body').html(customerDetails);

        // Show the modal
        $(modalId).modal('show');
      }


      // $(document).on('click', '.btn-view-details, .btn-confirm-reservation', function() {
      //   var reservationId = $(this).data('reservation-id');
      //   var action = $(this).hasClass('btn-view-details') ? 'viewDetails' : 'confirmReservation';

      //   if (action === 'viewDetails') {
      //     $.ajax({
      //       type: 'POST',
      //       url: 'driverdashboard.php',
      //       data: {
      //         getReservationDetails: true,
      //         reservationID: reservationId
      //       },
      //       success: function(response) {
      //         showModal(reservationId, response); // Show the modal after fetching details
      //       },
      //       error: function(error) {
      //         console.log('Error fetching reservation details:', error);
      //       }
      //     });
      //   } else if (action === 'confirmReservation') {
      //     $.ajax({
      //       type: 'POST',
      //       url: 'driverdashboard.php',
      //       data: {
      //         confirmReservation: true,
      //         reservationID: reservationId
      //       },
      //       success: function(response) {
      //         alert(response); // Display the response message
      //       },
      //       error: function(error) {
      //         console.log('Error confirming reservation:', error);
      //       }
      //     });
      //   }
      // });

      // Click event handler for the view details, confirm reservation, and dropped off buttons
      $(document).on('click', '.btn-view-details, .btn-confirm-reservation, .btn-dropped-off', function() {
        var reservationId = $(this).data('reservation-id');
        var action = $(this).hasClass('btn-view-details') ? 'viewDetails' : ($(this).hasClass('btn-confirm-reservation') ? 'confirmReservation' : 'droppedOff');

        if (action === 'viewDetails') {
          $.ajax({
            type: 'POST',
            url: 'driverdashboard.php',
            data: {
              getReservationDetails: true,
              reservationID: reservationId
            },
            success: function(response) {
              showModal(reservationId, response);
            },
            error: function(error) {
              console.log('Error fetching reservation details:', error);
            }
          });
        } else if (action === 'confirmReservation') {
          $.ajax({
            type: 'POST',
            url: 'driverdashboard.php',
            data: {
              confirmReservation: true,
              reservationID: reservationId
            },
            success: function(response) {
              // alert(response);
              var confirmationMessage = 'You have confirmed this reservation! Now you must be on your way to pickup the customer! Thank You.';
              alert(confirmationMessage);
              // Update the status in the table
              updateReservationStatus(reservationId, 'Confirmed and Picking Up');
            },
            error: function(error) {
              console.log('Error confirming reservation:', error);
            }
          });
        } else if (action === 'droppedOff') {
          // Handle the dropped off action
          $.ajax({
            type: 'POST',
            url: 'driverdashboard.php',
            data: {
              droppedOff: true,
              reservationID: reservationId
            },
            success: function(response) {
              // alert(response);
              var droppedOffMessage = 'You have successfully dropped off the customer! Thank You.';
              alert(droppedOffMessage);
              // Update the status in the table
              updateReservationStatus(reservationId, 'Dropped Off');
            },
            error: function(error) {
              console.log('Error marking as dropped off:', error);
            }
          });
        }
      });

      // Function to update the reservation status in the table
      function updateReservationStatus(reservationId, newStatus) {
        // Find the row in the table and update the status column
        var statusColumn = $('#reservationId_' + reservationId + ' .status-label');
        statusColumn.text(newStatus);
        // Update the CSS class based on the new status if needed
        statusColumn.removeClass('label-pending label-cancelled').addClass(getStatusClass(newStatus));
      }

      // Function to get the CSS class based on the reservation status
      function getStatusClass(status) {
        if (status === 'pending') {
          return 'label-pending';
        } else if (status === 'cancelled') {
          return 'label-cancelled';
        }
        // Add more conditions if needed
        return '';
      }
    </script>
</body>

</html>