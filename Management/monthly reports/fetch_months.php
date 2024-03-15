<?php
  session_start();
  include '../configuration/interns_config.php';

  $config = getDatabaseConfig();
  $dbhost = $config['dbhost'];
  $dbuser = $config['dbuser'];
  $dbpass = $config['dbpass'];
  $dbname = $config['dbname'];

  $connection = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
  if ($connection->connect_error) {
      die("Connection failed: " . $connection->connect_error);
  }

  $selectedMonth = isset($_GET["month"]) ? $_GET["month"] : date('m');
  $stmt = $connection->prepare("SELECT * FROM acomplisment_report WHERE user_id = ? AND MONTH(date) = ?");
  $stmt->bind_param("ss", $username, $selectedMonth);
  $stmt->execute();
  $result = $stmt->get_result();

  $data = array();
  while ($row = $result->fetch_assoc()) {
      $data[] = $row;
  }

  echo json_encode($data);

  $stmt->close();
  $connection->close();
?>
