<?php
session_start();
include "includes/db.php";
$uid = $_SESSION['user'];

if(isset($_POST['update_profile'])) {
    $p = mysqli_real_escape_string($conn, $_POST['phone']);
    $a = mysqli_real_escape_string($conn, $_POST['address']);
    mysqli_query($conn, "UPDATE users SET phone='$p', address='$a' WHERE id=$uid");
    echo "success";
}

if(isset($_FILES['profile_pic'])) {
    $name = time().'_'.$_FILES['profile_pic']['name'];
    move_uploaded_file($_FILES['profile_pic']['tmp_name'], "images/profiles/".$name);
    mysqli_query($conn, "UPDATE users SET profile_pic='$name' WHERE id=$uid");
}
?>