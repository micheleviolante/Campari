<?php
function valida_json($string)
{   // decode the JSON data
    $data = json_decode($string, true);
    // switch and check possible JSON errors
    switch (json_last_error()) {
        case JSON_ERROR_NONE:
            $error = ''; // JSON is valid // No error has occurred
            break;
        case JSON_ERROR_DEPTH:
            $error = 'JSON ERROR: The maximum stack depth has been exceeded.';
            break;
        case JSON_ERROR_STATE_MISMATCH:
            $error = 'JSON ERROR: Invalid or malformed JSON.';
            break;
        case JSON_ERROR_CTRL_CHAR:
            $error = 'JSON ERROR: Control character error, possibly incorrectly encoded.';
            break;
        case JSON_ERROR_SYNTAX:
            $error = 'JSON ERROR: Syntax error, malformed JSON.';
            break;
        // PHP >= 5.3.3
        case JSON_ERROR_UTF8:
            $error = 'JSON ERROR: Malformed UTF-8 characters, possibly incorrectly encoded.';
            break;
        // PHP >= 5.5.0
        case JSON_ERROR_RECURSION:
            $error = 'JSON ERROR: One or more recursive references in the value to be encoded.';
            break;
        // PHP >= 5.5.0
        case JSON_ERROR_INF_OR_NAN:
            $error = 'JSON ERROR: One or more NAN or INF values in the value to be encoded.';
            break;
        case JSON_ERROR_UNSUPPORTED_TYPE:
            $error = 'JSON ERROR: A value of a type that cannot be encoded was given.';
            break;
        default:
            $error = 'JSON ERROR: Unknown JSON error occured.';
            break;
    }

    if ($error !== '') {
        // throw the Exception or exit // or whatever :)
        exit($error);
    }

    // everything is OK
    return $data;
}
$user = 'root';
$password = '';
$dbname = 'sondaggi';
$result;
$host = 'localhost';
$port = 3306;
$db = new PDO("mysql:host=$host; dbname=$dbname; port=$port", $user, $password) or die("Connessione non riuscita");
$string = file_get_contents('php://input');
$data = valida_json($string);
$numerototalequestionari=0;
$firstKey = key($data); // la prima chiave del json : get_data
//echo "$firstKey";
switch ($data[$firstKey]) { // mi trovo nel caso di get_data
    case 'get_data':
        next($data); // vado al secondo campo: page
        $page = $data[key($data)]; 
        next($data); // vado a filter
        $filter = $data[key($data)][0];
        $date= $filter["value"];
        $dateinizia=explode("-",$date);
        $queryinizio = "SELECT COUNT(*) as sondaggitotali FROM questionario WHERE camp_timestamp_inizio BETWEEN '$dateinizia[0]' AND '$dateinizia[1]' ";
        $statement = $db->query($queryinizio);
        $valoretot=$statement->fetchAll(PDO::FETCH_ASSOC);
        $numerototalequestionari = $valoretot[0]['sondaggitotali'];
        foreach($filter as $k => $v){
            if($k == 'value'){
                $tempi = explode("-", $v);
                if($page == ""){
                    $querysenzaPage = "SELECT camp_id_questionario,camp_id_user,camp_timestamp_inizio,camp_timestamp_fine,camp_numero_risposte_corrette,camp_numero_risposte_totali FROM questionario WHERE camp_timestamp_inizio BETWEEN '$tempi[0]' AND '$tempi[1]' "; //valori '2018-03-07 03:26:39' and '2018-03-20 03:26:39'
                    $statement = $db->query($querysenzaPage);
                    $result=$statement->fetchAll(PDO::FETCH_ASSOC);
                   for($i=0;$i<count($result);$i++){
                       $percentuale= ($result[$i]['camp_numero_risposte_corrette']/$result[$i]['camp_numero_risposte_totali'])*100;
                       $result[$i]['risposte_corrette_percentuale']=$percentuale;
                       unset($result[$i]['camp_numero_risposte_totali']);
                   }
                    $result['sondaggitotali']= $numerototalequestionari;
                    $json_string = json_encode($result, JSON_PRETTY_PRINT);
                    print_r($json_string);
                }
                else{
                    $offset = ($page-1)*50;
                    $queryconPage = "SELECT camp_id_questionario,camp_id_user,camp_timestamp_inizio,camp_timestamp_fine,camp_numero_risposte_corrette,camp_numero_risposte_totali FROM questionario WHERE camp_timestamp_inizio BETWEEN '$tempi[0]]' AND '$tempi[1]' LIMIT 50 OFFSET $offset"; //valori '2018-03-07 03:26:39' and '2018-03-20 03:26:39'
                    $statement = $db->query($queryconPage);
                    $result=$statement->fetchAll(PDO::FETCH_ASSOC);
                    for($i=0;$i<count($result);$i++){
                       $percentuale= ($result[$i]['camp_numero_risposte_corrette']/$result[$i]['camp_numero_risposte_totali'])*100;
                       $result[$i]['risposte_corrette_percentuale']=$percentuale;
                       unset($result[$i]['camp_numero_risposte_totali']);
                    }
                    $result['sondaggitotali']= $numerototalequestionari;
                    $json_string = json_encode($result,JSON_PRETTY_PRINT);
                    print_r($json_string);
                }
            }
        }
        break;
    case 'get_statistics':
               echo "";
//        break;
//    case 2:
//        echo "i equals 2";
//        break;
}
