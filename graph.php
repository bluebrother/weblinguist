<?php
// Simple web interface to adjust Qt4 translation files.
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This software is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY
// KIND, either express or implied.
//
// (c) 2010 Dominik Riebeling
//

$width = 200;
$height = 15;

$image = imagecreate($width, $height);

$green = imagecolorallocate($image, 0x00, 0xff, 0x00);
$red = imagecolorallocate($image, 0xff, 0x00, 0x00);

$percent = ceil($_GET['p']);
if($percent > 100)
    $percent = 100;
$end = $width * $percent / 100;

imagefilledrectangle($image, 0, 0, $end, $height, $green);
imagefilledrectangle($image, $end, 0, $width, $height, $red);
header("Content-type: image/png");
imagepng($image);
imagedestroy($image);

?>
