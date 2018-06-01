<?php
/**
 * Génère une image depuis éléments en base de données
 *
 * PHP Version 7.0.9
 *
 * @category Generateur_Image_Meteo
 * @package  Meteo
 * @author   Vincent KRUTTEN <vkrutten@interact.lu>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://interact.lu
 */

require_once './include/Meteo.class.php';


header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('HTTP/1.0 200 Ok');
header("Content-Type: image/png");


if (!isset($airport)) {
    $airport = $_GET['code'];
    $day = $_GET['day'];
}

if (!isset($lang)) {
    $lang = $_GET['lang'];
    switch ($lang) {
    case 'fr':
        setlocale(LC_TIME, "fr");
        break;
    case 'de':
        setlocale(LC_TIME, "de");
        break;
    case 'en':
        setlocale(LC_TIME, "en");
        break;
    default:
        setlocale(LC_TIME, "en");
        break;
    }
}


$meteo=Meteo::getData($airport, $day)["comment"];
$temperatureJ=Meteo::getData($airport, $day)["tempmax"]."°";
$dateString=Meteo::getData($airport, $day)["checktime"];
$date=date_create($dateString);
$font=('./assets/fonts/Exo2-Bold.ttf');
$fontSizeTemp=20;
$fontSizeDate=8;
$xTemp=55;
$yTemp=30;
$xDate=0;
$yDate=55;
$angle=0;
//conversion date locale
$dateString = Meteo::getData($airport, $day)["checktime"];
$dateMeteo = strtoupper(
    strftime(
        "%A %d/%m", date_create($dateString)->getTimestamp()
    )
);


//condition temperature négative -> décalage police
if (substr($temperatureJ, 0, 1)=="-") {
    $xTemp-=5;
}
//création image
$image = imagecreatefrompng("./assets/images/".$meteo);
$couleur = imagecolorallocate($image, 58, 169, 193);

//centrage du texte
$textLength = imagefontwidth($fontSizeDate) * strlen($dateMeteo);
$xDate= (139-$textLength)/2;


//incorporation du texte dans l'image
imagettftext(
    $image, $fontSizeTemp, $angle, $xTemp, $yTemp, $couleur, $font, $temperatureJ
);
imagettftext(
    $image, $fontSizeDate, $angle, $xDate, $yDate, $couleur, $font, $dateMeteo
);
//anti aliasing
imagealphablending($image, false);
imagesavealpha($image, true);


// Rend le background de l'image transparent
$black = imagecolorallocate($image, 0, 0, 0);
imagecolortransparent($image, $black);

// J'affiche l'image et je libère l'espace
imagepng($image);
imagedestroy($image);
