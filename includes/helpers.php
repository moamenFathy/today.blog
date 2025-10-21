<?php

function checkIsImg($name) {

$extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

if (in_array($extension, ["jpeg", "jpg", "png", "gif", "webp", "avif"])) {
  return true;
}

return false;
}