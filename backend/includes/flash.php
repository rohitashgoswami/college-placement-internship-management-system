<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function set_flash_message($type, $message)
{
    $_SESSION['flash_message'] = array(
        'type' => $type,
        'message' => $message
    );
}

function get_flash_message()
{
    if (!isset($_SESSION['flash_message'])) {
        return null;
    }

    $flash = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);

    return $flash;
}
?>
