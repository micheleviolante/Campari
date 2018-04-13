<?php
$dati_post['file'] = "@C:\xampp\htdocs\TSW\Campari\operazioni.php";
$headers = array( 
    "file=@operazioni.php", 
);
$ch= curl_init("http://file.io");
curl_setopt($ch, CURLOPT_VERBOSE, true); 
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
curl_setopt($ch, CURLOPT_UPLOAD, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$postResult = curl_exec($ch);

// se ci sono stati degli errori mostro un messaggio esplicativo
if (curl_errno($ch)) {
	print curl_error($ch);
}

// chiudo la sessione CURL
curl_close($ch);

// mostro l'output prodotto da destinatario.php
echo $postResult;
