<?php
if ($_SESSION['ACType'] !== 'admin') {
    header("Location: person-details.php?id=" . urlencode($_SESSION['user_id']));
    exit;
}