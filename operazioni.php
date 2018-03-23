<?php

$user = 'root';
$password = '';
$dbname = 'sondaggi';
$host = 'localhost';
$port = 3306;
$db = new PDO("mysql:host=$host; dbname=$dbname; port=$port", $user, $password) or die("Connessione non riuscita");
    echo "connessione stabilita<br/><br/>";
$data = json_decode(file_get_contents('php://input'), true);
$firstKey = key($data); // la prima chiave del json : get_data
//echo "$firstKey";
switch ($data[$firstKey]) { // mi trovo nel caso di get_data
    case 'get_data':
        next($data); // vado al secondo campo: page
        $page = $data[key($data)]; 
        next($data); // vado a filter
        $filter = $data[key($data)][0]; 
        foreach($filter as $k => $v){
            if($k == 'value'){
                $tempi = explode("-", $v);
                if($page == ""){
                    $querysenzaPage = "SELECT camp_id_questionario,camp_id_user,camp_timestamp_inizio,camp_timestamp_fine,camp_numero_risposte_corrette,camp_numero_risposte_totali FROM questionario WHERE camp_timestamp_inizio BETWEEN '$tempi[0]]' AND '$tempi[1]]' "; //valori '2018-03-07 03:26:39' and '2018-03-20 03:26:39'
                    $statement = $db->query($querysenzaPage);
                    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                   $percentuale= ($result['camp_numero_risposte_corrette']/$result['camp_numero_risposte_totali'])*100;
                   for($i=0;$i<count($result);$i++){
                       $percentuale= ($result[$i]['camp_numero_risposte_corrette']/$result[$i]['camp_numero_risposte_totali'])*100;
                       $result[$i]['risposte_corrette_percentuale']=$percentuale;
                       unset($result[$i]['camp_numero_risposte_totali']);
                   }
                    $json_string = json_encode($result, JSON_PRETTY_PRINT);
                    echo $json_string;
                }
                else{
                    $offset = ($page-1)*50;
                    $queryconPage = "SELECT camp_id_questionario,camp_id_user,camp_timestamp_inizio,camp_timestamp_fine,camp_numero_risposte_corrette,camp_numero_risposte_totali FROM questionario WHERE camp_timestamp_inizio BETWEEN '$tempi[0]]' AND '$tempi[1]' LIMIT 50 OFFSET $offset"; //valori '2018-03-07 03:26:39' and '2018-03-20 03:26:39'
                    $statement = $db->query($queryconPage);
                    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                    for($i=0;$i<count($result);$i++){
                       $percentuale= ($result[$i]['camp_numero_risposte_corrette']/$result[$i]['camp_numero_risposte_totali'])*100;
                       $result[$i]['risposte_corrette_percentuale']=$percentuale;
                       unset($result[$i]['camp_numero_risposte_totali']);
                    }
                    $json_string = json_encode($result, JSON_PRETTY_PRINT);
                    echo $json_string;
                }
            }
        }
        break;
    case 'get_statistics':
//        echo "i equals 1";
//        break;
//    case 2:
//        echo "i equals 2";
//        break;
}
