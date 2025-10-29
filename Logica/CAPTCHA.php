<?php
require "../vendor/autoload.php";
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$captcha_response = $_POST["g-recaptcha-response"];
$secretKey = $_ENV["RECAPTCHA_SECRET_KEY"];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'secret' => $secretKey,
    'response' => $captcha_response
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// ? curl_setopt($ch, CURLOPT_CAINFO, "D:/xampp/php/extras/ssl/cacert.pem");

// ! DESACTIVAR ANTIVIRUS EN DESARROLLO, Ó PONER "SSL" EN FALSE (NO SEGURO):
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Mantener en true para producción - SSL activado

echo "openssl.cafile = " . ini_get('openssl.cafile') . "<br>";
echo "curl.cainfo = " . ini_get('curl.cainfo') . "<br>";

$response = curl_exec($ch);

if ($response === false) {
    echo "Error cURL: " . curl_error($ch) . "<br>";
    curl_close($ch);
    exit;
} else {
    echo "¡cURL funciona correctamente! <br>";
}

curl_close($ch);

$captcha_result = json_decode($response, true);
?>