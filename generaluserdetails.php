<?php
// Include the database connection
include ("./system/config/dbconnection.php");

session_start();

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    // Redirect to login page if not logged in
    header("Location: signin-signup_users.php");
    exit();
}

// Retrieve the logged-in user's email from the session
$email = $_SESSION['email'];

// Fetch user record based on the provided email
$query = "SELECT * FROM `general_users` WHERE `email`='$email'";
$result = mysqli_query($conn, $query);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $user_id = $row['id']; // Get the ID of the logged-in user

    // Fetch existing details if available
    $existing_details_query = "SELECT * FROM `general_userdetails` WHERE `general_user_id`='$user_id'";
    $existing_details_result = mysqli_query($conn, $existing_details_query);

    if ($existing_details_result && mysqli_num_rows($existing_details_result) > 0) {
        $existing_details = mysqli_fetch_assoc($existing_details_result);
        // Assign existing details to variables for displaying in the form
        $existing_firstname = $existing_details['firstname'];
        $existing_lastname = $existing_details['lastname'];
        $existing_dateofbirth = $existing_details['dateofbirth'];
        $existing_address = $existing_details['address'];
        $existing_city = $existing_details['city'];
        $existing_zipcode = $existing_details['zipcode'];
        $existing_primaryphone = $existing_details['primaryphone'];
        $existing_secondaryphone = $existing_details['secondaryphone'];
        $existing_email = $existing_details['email'];
        $existing_imagelocation = $existing_details['imagelocation'];
    } else {
        // Set default values for existing details if not available
        $existing_firstname = "";
        $existing_lastname = "";
        $existing_dateofbirth = "";
        $existing_address = "";
        $existing_city = "";
        $existing_zipcode = "";
        $existing_primaryphone = "";
        $existing_secondaryphone = "";
        $existing_email = "";
        $existing_imagelocation = "";
    }
} else {
    // Handle error fetching user details
    echo "<script>alert('Error fetching user details.')</script>";
}

// Check if the form is submitted
if (isset($_POST["submitDetails"])) {
    // Retrieve input values from the form and sanitize them
    $firstname = mysqli_real_escape_string($conn, $_POST["firstname"]);
    $lastname = mysqli_real_escape_string($conn, $_POST["lastname"]);
    $dateofbirth = mysqli_real_escape_string($conn, $_POST["dateofbirth"]);
    $address = mysqli_real_escape_string($conn, $_POST["address"]);
    $city = mysqli_real_escape_string($conn, $_POST["city"]);
    $zipcode = mysqli_real_escape_string($conn, $_POST["zipcode"]);
    $primaryphone = mysqli_real_escape_string($conn, $_POST["primaryphone"]);
    $secondaryphone = mysqli_real_escape_string($conn, $_POST["secondaryphone"]);
    $email = mysqli_real_escape_string($conn, $_POST["email"]);

    // Initialize image location variable
    $imagelocation = "";

    // Check if image file is uploaded
    if ($_FILES["imageUpload"]["error"] === 0 && $_FILES["imageUpload"]["size"] > 0) {
        $target_dir = "./images/uploads/generalusers/"; // Directory where images will be saved
        $target_file = $target_dir . basename($_FILES["imageUpload"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if the uploaded file is an image
        $check = getimagesize($_FILES["imageUpload"]["tmp_name"]);
        if ($check !== false) {
            // Check file size
            if ($_FILES["imageUpload"]["size"] > 5000000) { // Adjust size limit as needed
                echo "<script>alert('Sorry, your file is too large.')</script>";
            } else {
                // Allow certain file formats
                if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
                    echo "<script>alert('Sorry, only JPG, JPEG, PNG files are allowed.')</script>";
                } else {
                    // Move uploaded file to designated directory
                    if (move_uploaded_file($_FILES["imageUpload"]["tmp_name"], $target_file)) {
                        // File uploaded successfully
                        $imagelocation = $target_file;
                    } else {
                        echo "<script>alert('Sorry, there was an error uploading your file.')</script>";
                    }
                }
            }
        } else {
            echo "<script>alert('File is not an image.')</script>";
        }
    }

    if (empty($existing_details)) {
        // Insert data into database for new user
        $sql = "INSERT INTO `general_userdetails` (`firstname`, `lastname`, `dateofbirth`, `address`, `city`, `zipcode`, `primaryphone`, `secondaryphone`, `email`, `imagelocation` , `general_user_id`) 
            VALUES ('$firstname', '$lastname', '$dateofbirth', '$address', '$city',  '$zipcode', '$primaryphone', '$secondaryphone', '$email' , '$imagelocation', '$user_id')";
    } else {
        // Update data in database for existing user
        $sql = "UPDATE `general_userdetails` SET `firstname`='$firstname', `lastname`='$lastname', `dateofbirth`='$dateofbirth', `address`='$address', `city`='$city', 
        `zipcode`='$zipcode', `primaryphone`='$primaryphone', `secondaryphone`='$secondaryphone', `email` = '$email'";
        // Update image location if new image is uploaded
        if (!empty($imagelocation)) {
            $sql .= ", `imagelocation`='$imagelocation'";
        }
        $sql .= " WHERE `general_user_id`='$user_id'";
    }

    if (mysqli_query($conn, $sql)) {
        // On successful submission or update, display the popup
        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    document.getElementById('popup').classList.add('active');
                });

                function closePopup() {
                    window.location.href = 'generaluser_dashboard.php'; // Redirect to home.php
                }
              </script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "')</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <title>LFBL | User Details</title>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700;800;900&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(270deg, #06142e, #bbb);
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            overflow-y: auto;
        }

        .wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            overflow: hidden;
        }

        .nav {
            position: fixed;
            top: 10px;
            display: flex;
            align-items: start;
            width: 100%;
            height: 100px;
            line-height: 100px;
            z-index: 100;
        }

        .nav-logo img {
            display: none;
            height: 45px;
            padding-left: 40px;
        }

        .form-box {
            position: relative;
            width: 516px;
            height: auto;
            overflow: auto;
            z-index: 2;
        }

        .header {
            font-size: 24px;
            align-self: center;
            margin-top: 15px;
            color: #fff;
            font-weight: 500;
        }

        .login-container {
            width: 100%;
            display: flex;
            flex-direction: column;
            padding: 10px;
            border-radius: 10px;
        }

        .login-container header {
            color: #fff;
            font-size: 30px;
            text-align: center;
            padding: 10px 0 30px 0;
        }

        .input-box {
            position: relative;
            margin-bottom: 20px;
        }

        .image-upload {
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            margin-top: 15px;
        }

        .upload-label {
            font-size: 30px;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            height: 60px;
            width: 60px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: background 0.3s ease;
        }

        .upload-label:hover {
            background: rgba(255, 255, 255, 0.25);
        }

        .upload-label i {
            color: #fff;
        }

        #imageUpload {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 1;
        }

        .input-field {
            font-size: 15px;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            height: 50px;
            width: 100%;
            padding: 0 45px 0 45px;
            border: none;
            border-radius: 30px;
            outline: none;
        }

        .input-field:hover,
        .input-field:focus {
            background: rgba(255, 255, 255, 0.25);
        }

        .input-box i {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            left: 15px;
            color: #fff;
        }

        .submit,
        .delete {
            font-size: 15px;
            font-weight: 500;
            color: black;
            height: 45px;
            width: 100%;
            border: none;
            border-radius: 30px;
            outline: none;
            background: rgba(255, 255, 255, 0.7);
            cursor: pointer;
            transition: .3s ease-in-out;
        }

        .submit:hover {
            background: green;
            font-size: 16px;
            font-weight: 600;
            box-shadow: 1px 5px 7px 1px rgba(0, 0, 0, 0.2);
        }

        .delete:hover {
            background: red;
            font-size: 16px;
            font-weight: 600;
            color: #fff;
            box-shadow: 1px 5px 7px 1px rgba(0, 0, 0, 0.2);
        }

        .popup {
            width: 400px;
            background: #fff;
            border-radius: 6px;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.1);
            text-align: center;
            padding: 0 30px 30px;
            color: #333;
            visibility: hidden;
            transition: transform 0.4s, top 0.4s;
            z-index: 9999;
            overflow: hidden;
        }

        .popup.active {
            visibility: visible;
            transform: translate(-50%, -50%) scale(1);
        }

        .popup i {
            font-size: 100px;
            color: #333;
            width: 100px;
            margin-top: 30px;
            border-radius: 50%;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .popup h2 {
            font-size: 38px;
            font-weight: 500;
            margin: 30px 0 10px;
        }

        .popup button {
            width: 100%;
            margin-top: 50px;
            padding: 10px 0;
            background: #6fd649;
            color: #fff;
            border: 0;
            outline: none;
            font-size: 18px;
            border-radius: 4px;
            cursor: pointer;
            box-shadow: 0 5px 5px rgba(0, 0, 0, 0.2);
        }

        .popup.active~.blur-background {
            display: block;
        }

        .notification-popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #fff;
            border: 1px solid #ccc;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            z-index: 9999;
        }

        .notification-content {
            text-align: center;
        }

        .notification-buttons button {
            margin: 10px 10px;
            padding: 8px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .notification-buttons button:hover {
            background-color: #0056b3;
        }

        .blur-background {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            z-index: 9998;
        }

        @media only screen and (max-width: 540px) {
            .wrapper {
                min-height: auto;
            }

            .form-box {
                width: 100%;
            }

            .login-container,
            .register-container {
                padding: 0 20px;
            }
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <nav class="nav">
            <div class="nav-logo">
                <img src="./images/Logo Files/For Web/png/White logo - no background.png" alt="Logo">
            </div>
        </nav>

        <div class="form-box">
            <form class="login-container" id="login" action="./generaluserdetails.php" method="POST"
                enctype="multipart/form-data">
                <p class="header">Edit Profile</p>

                <div class="input-box image-upload">
                    <?php if (!empty($existing_imagelocation)): ?>
                        <!-- Display existing image with the ability to change -->
                        <label for="imageUpload" class="upload-label" id="imageLabel">
                            <img class="uploaded-image" src="<?php echo $existing_imagelocation; ?>" alt="Existing Image"
                                style="max-width: 100%; max-height: 100%; cursor: pointer;">
                        </label>
                    <?php else: ?>
                        <!-- Display upload icon if no existing image -->
                        <label for="imageUpload" class="upload-label">
                            <i class='bx bx-image'></i>
                        </label>
                    <?php endif; ?>
                    <input type="file" id="imageUpload" name="imageUpload" accept=".jpg, .jpeg, .png"
                        style="display: none;" onchange="displayImage(this)">
                </div>

                <div class="input-box">
                    <input type="text" class="input-field" name="firstname" placeholder="Firstname"
                        value="<?php echo $existing_firstname; ?>">
                    <i class='bx bxs-bank'></i>
                </div>

                <div class="input-box">
                    <input type="text" class="input-field" name="lastname" placeholder="Lastname"
                        value="<?php echo $existing_lastname; ?>">
                    <i class='bx bx-user'></i>
                </div>

                <div class="input-box">
                    <input type="date" class="input-field" name="dateofbirth" placeholder="Date of Birth"
                        value="<?php echo $existing_dateofbirth; ?>">
                    <i class='bx bxs-phone'></i>
                </div>

                <div class="input-box">
                    <input type="text" class="input-field" name="address" placeholder="Address"
                        value="<?php echo $existing_address; ?>">
                    <i class='bx bx-map-pin'></i>
                </div>

                <div class="input-box">
                    <input type="text" class="input-field" name="city" placeholder="City"
                        value="<?php echo $existing_city; ?>">
                    <i class='bx bx-map-pin'></i>
                </div>

                <div class="input-box">
                    <input type="text" class="input-field" name="zipcode" placeholder="Zipcode"
                        value="<?php echo $existing_zipcode; ?>">
                    <i class='bx bx-cross'></i>
                </div>

                <div class="input-box">
                    <input type="text" class="input-field" name="primaryphone" placeholder="Primary Phone Number"
                        value="<?php echo $existing_primaryphone; ?>">
                    <i class='bx bx-map-pin'></i>
                </div>

                <div class="input-box">
                    <input type="text" class="input-field" name="secondaryphone" placeholder="Secondary Phone Number"
                        value="<?php echo $existing_secondaryphone; ?>">
                    <i class='bx bx-map-pin'></i>
                </div>

                <div class="input-box">
                    <input type="text" class="input-field" name="email" placeholder="Email"
                        value="<?php echo $existing_email; ?>">
                    <i class='bx bx-map-pin'></i>
                </div>

                <div class="input-box">
                    <input type="submit" class="submit" name="submitDetails" value="Submit">
                </div>

                <div class="input-box">
                    <input type="submit" class="delete" name="deleteaccount" value="Delete Account"
                        onclick="openNotification()">
                </div>
            </form>

            <div class="notification-popup" id="notificationPopup">
                <div class="notification-content">
                    <p>Are you sure you want to delete your account?</p>
                    <div class="notification-buttons">
                        <button onclick="confirmDelete()">Yes</button>
                        <button onclick="closeNotification()">No</button>
                    </div>
                </div>
            </div>

            <div class="popup" id="popup">
                <i class='bx bx-check-circle'></i>
                <h2>Success</h2>
                <p>Your details have been successfully submitted.</p>
                <button type="button" onclick="closePopup();">OK</button>
            </div>

            <div class="blur-background" id="blur-background"></div>
        </div>
    </div>
</body>
<script>
    document.getElementById('imageUpload').addEventListener('change', function (event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function () {
                const imgData = reader.result;
                const uploadLabel = document.querySelector('.upload-label');
                uploadLabel.style.backgroundImage = `url('${imgData}')`;
                uploadLabel.style.backgroundSize = 'cover';
                uploadLabel.style.backgroundPosition = 'center';
                // Hide the <i> tag
                uploadLabel.querySelector('i').style.display = 'none';
            }
            reader.readAsDataURL(file);
        }
    });

    //Display new image on selecting upon the existing
    function displayImage(input) {
        var label = input.previousElementSibling;
        var file = input.files[0];
        var reader = new FileReader();

        reader.onload = function (e) {
            // If label contains an image, replace it with the new image
            if (label.querySelector('img')) {
                label.querySelector('img').src = e.target.result;
            } else { // Otherwise, create a new image element and append it to the label
                var img = document.createElement('img');
                img.src = e.target.result;
                img.alt = "Selected Image";
                img.style.maxWidth = "100%";
                img.style.maxHeight = "100%";
                img.style.cursor = "pointer";
                label.appendChild(img);
            }
        };

        reader.readAsDataURL(file);
    }

    // JavaScript function to open the notification popup
    function openNotification() {
        document.getElementById("notificationPopup").style.display = "block";
        document.getElementById("blur-background").style.display = "block"; // Display background overlay
    }

    // JavaScript function to close the notification popup
    function closeNotification() {
        document.getElementById("notificationPopup").style.display = "none";
        document.getElementById("blur-background").style.display = "none"; // Hide background overlay
    }

    // JavaScript function to handle the Delete Account button click
    function confirmDelete() {
        // Redirect to a PHP script to handle account deletion
        window.location.href = 'deletegeneraluser.php';
    }

    // Add event listener to ensure the notification popup stays open until user interaction
    document.addEventListener('DOMContentLoaded', function () {
        var deleteButton = document.querySelector('.delete');
        var notificationPopup = document.getElementById('notificationPopup');

        deleteButton.addEventListener('click', function (event) {
            event.preventDefault(); // Prevents the default form submission behavior
            openNotification();
        });

        notificationPopup.addEventListener('click', function (event) {
            // Close the notification popup if user clicks outside of it or on "No"
            if (event.target === notificationPopup || event.target.classList.contains('no-button')) {
                closeNotification();
            }
        });
    });

</script>

</html>