<?php

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'task1';

$mysqli = new mysqli($host, $username, $password, $database);

if ($mysqli->connect_errno) {
    die('Failed to connect to MySQL: ' . $mysqli->connect_error);
}

$mysqli->set_charset('utf8mb4');

if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $ip = $_SERVER['REMOTE_ADDR'];
}

$browser_agent =  $_SERVER['HTTP_USER_AGENT'];

$datetime = date('Y-m-d H:i:s');

if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')   
         $url = "https://";
    else  
         $url = "http://";   
    $url.= $_SERVER['HTTP_HOST'];   
    $url.= $_SERVER['REQUEST_URI'];     


$query = "SELECT * FROM ads WHERE ip_address = ? && user_agent = ? && page_url = ?";

if ($stmt = $mysqli->prepare($query)) {
    $stmt->bind_param('sss', $ip_address, $user_agent, $page_url);

    $ip_address = $ip;
    $user_agent = $browser_agent;
    $page_url = $url;

    $stmt->execute();

    $result = $stmt->get_result();
    
    $row_data = [];

    while ($row = $result->fetch_assoc()) {
        $row_data['id'] = $row['id'];
        $row_data['views_count'] = $row['views_count'];
    }
    $num_rows = mysqli_num_rows($result);

    if($num_rows == 0) {
        
        $query = "INSERT INTO ads (ip_address, user_agent, view_date, page_url, views_count) VALUES (?, ?, ?, ?, ?)";

        $stmt = $mysqli->prepare($query);

        $stmt->bind_param("sssss", $ip_address, $user_agent, $view_date, $page_url, $view_count);

        $ip_address = $ip;
        $user_agent = $browser_agent;
        $view_date = $datetime;
        $page_url = $url;
        $view_count = 1;

        $stmt->execute();
        
    }elseif($num_rows == 1){

        $query = "UPDATE ads SET view_date  = ?, views_count = ? WHERE id = ?";

        $stmt = $mysqli->prepare($query);

        $stmt->bind_param("sis", $view_date, $views_count, $id);

        $view_date = $datetime;
        $views_count = $row_data['views_count']+1;
        $id = $row_data['id'];

        $stmt->execute();
        
    }

    $result->close();

    $stmt->close();
}

$mysqli->close();

$rand = rand(1,2);
$name = $rand.'.jpg';
$fp = fopen($name, 'rb');

header("Content-Type: image/jpeg");
fpassthru($fp);

?>


