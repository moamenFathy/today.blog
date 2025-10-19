<?php

function selectFromTable($conn, $tableName, $where = "", $condition = "") {
  if ($where) {
    $query = "SELECT * FROM $tableName WHERE $where = $condition";
  } else {
    $query = "SELECT * FROM $tableName";
  }
  
  $result = mysqli_query($conn, $query);

  if (!$result) {
    die("Something went wrong while fetching: " . mysqli_error($conn));
  }

  $data = [];
  while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
  }

  return $data;
}

function insertIntoTable($conn, $tableName, $data) {
  
  if (!is_array($data) || empty($data)) {
    die("The Values Must Be Assoc Array");
  }

  $columns = array_keys($data);
  $values = array_values($data);

  $escapedValues = array_map(function($values) use ($conn) {
    return "'" . mysqli_real_escape_string($conn, $values) . "'";
  }, $values);

  $cols = implode(", ", array_map(fn($col) => "`$col`", $columns));
  $vals = implode(", ", $escapedValues);

  $query = "INSERT INTO $tableName ($cols) VALUES ($vals)";
  $result = mysqli_query($conn, $query);

  if (!$result) {
    die("Insertion Failed " . mysqli_error($conn));
  }
  
  return mysqli_insert_id($conn);
}