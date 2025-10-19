<?php

function connectDb() {
  $hostname = "localhost";
  $user = "moamen";
  $password = "moamen";
  $dbName = "today.blog";

  $result = mysqli_connect($hostname, $user, $password, $dbName);

  if (!$result) {
    die ("Error While Connecting " . mysqli_connect_error());
  }

  return $result;
}