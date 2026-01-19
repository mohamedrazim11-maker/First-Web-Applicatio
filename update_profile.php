<?php
session_start();
include "includes/db.php";

if(!isset($_SESSION['user'])) {
    exit("unauthorized");
}

$uid = $_SESSION['user'];

// Update Text Data
if(isset($_POST['update_profile'])) {
    $p = mysqli_real_escape_string($conn, $_POST['phone']);
    $a = mysqli_real_escape_string($conn, $_POST['address']);
    mysqli_query($conn, "UPDATE users SET phone='$p', address='$a' WHERE id=$uid");
    echo "Profile Updated";
}

// Upload New Photo
if(isset($_FILES['profile_pic'])) {
    $name = time().'_'.$_FILES['profile_pic']['name'];
    if(move_uploaded_file($_FILES['profile_pic']['tmp_name'], "images/profiles/".$name)) {
        mysqli_query($conn, "UPDATE users SET profile_pic='$name' WHERE id=$uid");
        echo "Photo Uploaded";
    }
}

// Remove Photo Logic
if(isset($_POST['remove_photo'])) {
    $res = mysqli_query($conn, "SELECT profile_pic FROM users WHERE id=$uid");
    $row = mysqli_fetch_assoc($res);
    if(!empty($row['profile_pic'])) {
        @unlink("images/profiles/".$row['profile_pic']);
    }
    mysqli_query($conn, "UPDATE users SET profile_pic='' WHERE id=$uid");
    echo "Photo Removed";
}
?>