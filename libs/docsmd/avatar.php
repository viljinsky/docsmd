<?php


header("Content-type: image/png");

$image = imagecreatefrompng('noavatar92.png');

imagepng($image);