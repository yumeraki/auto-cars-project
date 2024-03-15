function redirectToLogin(type) {
  let loginPage;

  if (type === "user") {
    loginPage = "customerlogin.php";
  } else if (type === "driver") {
    loginPage = "driverlogin.php"; // Replace with the actual driver login page
  } else if (type === "operator") {
    loginPage = "operatorlogin.php"; // Replace with the actual operator login page
  }

  if (loginPage) {
    window.location.href = loginPage;
  }
}

function showOverview() {
  hideAllSections();
  document.getElementById("overview-section").style.display = "block";
}

function showAssignTaxis() {
  hideAllSections();
  document.getElementById("assign-taxis-section").style.display = "block";
}

function showReserveTaxiForUser() {
  hideAllSections();
  document.getElementById("reserve-taxi-for-user-section").style.display =
    "block";
}

function hideAllSections() {
  var sections = document.getElementsByClassName("content");
  for (var i = 0; i < sections.length; i++) {
    sections[i].style.display = "none";
  }
}

function logout() {
  window.location.href = "index.php";
}

function showProfile() {
  hideAllSections();
  $("#profile-section").show();
}

function showReserveTaxi() {
  hideAllSections();
  $("#reserve-taxi-section").show();
}

function showChooseYourTaxiReservation() {
  hideAllSections();
  $("#choose-taxi-reservation-section").show();
}

function showRideReservations() {
  hideAllSections();
  $("#view-reservations-section").show();
}

function showRideHistory() {
  hideAllSections();
  $("#ride-history-section").show();
}

function showSeeReservationRequests() {
  hideAllSections();
  $("#see-reservation-requests-section").show();
}

function showUserRideReservations() {
  hideAllSections();
  $("#reservation-history-section").show();
}

// Attach an event listener to the form submission
window.addEventListener("load", function () {
  document
    .getElementById("assignTaxiForm")
    .addEventListener("submit", function (event) {
      event.preventDefault(); // Prevent the form from submitting normally

      // Your existing form submission code
      var form = event.target;
      var formData = new FormData(form);

      fetch(form.action, {
        method: form.method,
        body: formData,
      })
        .then((response) => response.text())
        .then((data) => {
          // Check if the response contains a success indicator
          if (data.toLowerCase().includes("assigned successfully")) {
            showSuccessMessage(data);
          } else {
            // Handle other cases, if needed
            showErrorMessage("Error: " + data);
          }
        })
        .catch((error) => {
          showErrorMessage("Error: " + error);
        });
    });

  // Function to display success or error message
  function showMessage(message, isSuccess) {
    var messageDiv = document.createElement("div");
    messageDiv.innerHTML = message;

    messageDiv.style.fontSize = "18px";
    messageDiv.style.marginTop = "20px";
    messageDiv.style.padding = "10px";
    messageDiv.style.borderRadius = "5px";
    messageDiv.style.display = "block";
    messageDiv.style.position = "relative";
    messageDiv.style.backgroundColor = isSuccess ? "green" : "red"; // Adjust colors as needed
    messageDiv.style.color = "white";
    messageDiv.style.textAlign = "center";

    document.getElementById("assign-taxis-section").appendChild(messageDiv);

    // Hide the message after 5000 milliseconds (5 seconds)
    setTimeout(function () {
      messageDiv.style.display = "none";
    }, 5000);
  }

  // Function to display success message
  function showSuccessMessage(message) {
    showMessage(message, true);
  }

  // Function to display error message
  function showErrorMessage(message) {
    showMessage(message, false);
  }
});

// document
//   .querySelectorAll(".btn-view-customer-details")
//   .forEach(function (button) {
//     button.addEventListener("click", function () {
//       var customerId = button.getAttribute("data-customer-id");
//       showCustomerDetailsModal(customerId);
//     });
//   });

// $(document).ready(function () {
//   $(".btn-view-details").on("click", function () {
//     var reservationId = $(this).data("reservation-id");

//     $.ajax({
//       type: "POST",
//       url: "driverdashboard.php",
//       data: {
//         getReservationDetails: true,
//         reservationID: reservationId,
//       },
//       dataType: "json",
//       success: function (reservationDetails) {
//         var modalContent =
//           "<p><strong>Reservation ID:</strong> " +
//           reservationDetails["r_id"] +
//           "</p>" +
//           "<p><strong>Customer ID:</strong> " +
//           reservationDetails["c_id"] +
//           "</p>" +
//           "<p><strong>Customer Name:</strong> " +
//           reservationDetails["c_first_name"] +
//           " " +
//           reservationDetails["c_last_name"] +
//           "</p>" +
//           "<p><strong>Email:</strong> " +
//           reservationDetails["c_email"] +
//           "</p>" +
//           "<p><strong>Phone Number:</strong> " +
//           reservationDetails["c_number"] +
//           "</p>" +
//           "<p><strong>Pickup Location:</strong> " +
//           reservationDetails["pickup_location"] +
//           "</p>" +
//           "<p><strong>Drop Location:</strong> " +
//           reservationDetails["drop_location"] +
//           "</p>" +
//           "<p><strong>Status:</strong> " +
//           reservationDetails["status"] +
//           "</p>";

//         $("#reservationDetailsContent").html(modalContent);
//         $("#customerDetailsModal").modal("show");
//       },
//       error: function (error) {
//         console.log("Error fetching reservation details:", error);
//       },
//     });
//   });
// });

// Function to show the modal based on reservation ID
function showModal(reservationId) {
  var modalId = "#customerDetailsModal" + reservationId;
  $(modalId).modal("show");
}

// Function to handle the view details and confirm reservation buttons
function handleButtonClick(reservationId, action) {
  if (action === "viewDetails") {
    $.ajax({
      type: "POST",
      url: "driverdashboard.php",
      data: {
        getReservationDetails: true,
        reservationID: reservationId,
      },
      success: function (response) {
        $("#customerDetailsPlaceholder" + reservationId).html(response);
        showModal(reservationId); // Show the modal after fetching details
      },
      error: function (error) {
        console.log("Error fetching reservation details:", error);
      },
    });
  } else if (action === "confirmReservation") {
    $.ajax({
      type: "POST",
      url: "driverdashboard.php",
      data: {
        confirmReservation: true,
        reservationID: reservationId,
      },
      success: function (response) {
        alert(response); // Display the response message
      },
      error: function (error) {
        console.log("Error confirming reservation:", error);
      },
    });
  }
}

// Click event handler for the view details and confirm reservation buttons
$(document).on(
  "click",
  ".btn-view-details, .btn-confirm-reservation",
  function () {
    var reservationId = $(this).data("reservation-id");
    var action = $(this).hasClass("btn-view-details")
      ? "viewDetails"
      : "confirmReservation";
    handleButtonClick(reservationId, action);
  }
);

document.addEventListener("click", function (event) {
  if (
    event.target.tagName === "LABEL" &&
    event.target.parentNode.classList.contains("star-rating")
  ) {
    const labels = event.target.parentNode.querySelectorAll("label");
    const clickedIndex = Array.from(labels).indexOf(event.target);

    for (let i = 0; i <= clickedIndex; i++) {
      labels[i].classList.add("selected");
    }

    for (let i = clickedIndex + 1; i < labels.length; i++) {
      labels[i].classList.remove("selected");
    }

    const rating = clickedIndex + 1;
    document.querySelector('input[name="rating"]').value = rating;
  }
});

$.ajax({
  type: "POST",
  url: "userdashboard.php", // Replace with the actual URL of your PHP script
  data: {
    sendRequest: true,
    pickupLocation: "pickup_location",
    dropLocation: "drop_location",
  },
  success: function (response) {
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
  error: function () {
    // Handle AJAX request errors if needed
    alert("Error: Unable to make the reservation. Please try again later.");
  },
});
