<?php
function log_action($conn, $user_id, $username, $action) {
    $action = mysqli_real_escape_string($conn, $action);
    $username = mysqli_real_escape_string($conn, $username);
    $query = "INSERT INTO logs (user_id, username, action) VALUES ($user_id, '$username', '$action')";
    mysqli_query($conn, $query);
}
?>
