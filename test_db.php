<?php
require 'config.php';
try {
  // simple query to test
  $row = $pdo->query("SELECT NOW() AS now")->fetch();
  echo "OK — DB responded: " . htmlentities($row['now']);
} catch (Exception $e) {
  echo "Test failed: " . htmlentities($e->getMessage());
}
