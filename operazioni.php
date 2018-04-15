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
function differenza_date($data2, $data1){
    $date1= date_create($data1);
    $date2= date_create($data2);
  $diff = strtotime($date2->format('Y-m-d H:i:s.u'))-strtotime($date1->format('Y-m-d H:i:s.u'));
  $micro1 = $date1->format("u");
  $micro2 = $date2->format("u");
  $diffmicro = $micro2 - $micro1;
  list($sec,$micro) = array_pad(explode('.',((($diff) * 1000000) + $diffmicro )/1000000),2,'000');
  $difference = $sec . "." . str_pad($micro,3,'0');
  return $difference;
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
        next($data); // vado a filter
        $relation = $data[key($data)];
        next($data);
        $view=$data['view'];
        next($data);
        $filter = $data[key($data)];
        prev($data);
        echo "<br/>questa dovrebbe essere AND: $relation <br/>";
        var_dump($filter);echo"<br/><br/>";
        $query="SELECT camp_timestamp_inizio FROM questionario WHERE ";
        for($i=0; $i < count($filter);$i++){
           
            if($filter[$i]["key"]=="date_interval" && $i == 0){
               $dateAttaccate = $filter[$i]["value"];
               $datiDate=explode("-",$dateAttaccate);
               $query = $query . "camp_timestamp_inizio BETWEEN '$datiDate[0]' AND '$datiDate[1]'";
            }else if($filter[$i]["key"]=="date_interval" && $i != 0){
               $dateAttaccate = $filter[$i]["value"];
               $datiDate=explode("-",$dateAttaccate);
               $query = $query ." ".$relation." camp_timestamp_inizio BETWEEN '$datiDate[0]' AND '$datiDate[1]'";
            }
           
            if($filter[$i]["key"]=="camp_id_user" && $i == 0){
                if($filter[$i]["operator"] == '='){
                    $query = $query . " camp_id_user LIKE "."'".$filter[$i]["value"]."'";
                }else{
                   $query = $query ." camp_id_user".$filter[$i]["operator"]." '".$filter[$i]["value"]."'";
                }
            }else if($filter[$i]["key"]=="camp_id_user" && $i != 0){
                if($filter[$i]["operator"] == '='){
                   $query = $query ." ".$relation. " camp_id_user LIKE ". "'".$filter[$i]["value"]."'";
                }else{
                   $query = $query ." ".$relation ." camp_id_user".$filter[$i]["operator"]." '". $filter[$i]["value"]."'";
                }
            }
            if($filter[$i]["key"]=="camp_id_questionario" && $i == 0){
                if($filter[$i]["operator"] == "="){
                   $query = $query . " camp_id_questionario LIKE "."'".$filter[$i]["value"]."'";
               }else{
                   $query = $query . " camp_id_questionario".$filter[$i]["operator"]." '". $filter[$i]["value"]."'";
               }
            }else if($filter[$i]["key"]=="camp_id_questionario" && $i != 0){
                if($filter[$i]["operator"] == "="){
                   $query = $query ." ".$relation ." camp_id_questionario LIKE ". "'".$filter[$i]["value"]."'";
                }else{
                   $query = $query ." ".$relation ." camp_id_questionario".$filter[$i]["operator"]." '". $filter[$i]["value"]."'";
                }
            }
        }
        $statement = $db->query($query);
        $result=$statement->fetchAll(PDO::FETCH_NUM);
        $stringarisposta="{'labels': ";                
            
         if($view=='giornaliero'){
            $datainiziale=new DateTime($datiDate[0]);
            $datafinale= new DateTime($datiDate[1]);
            
           for($i = $datainiziale; $i <= $datafinale; $i->modify('3600 seconds')){
                      $stringarisposta=$stringarisposta.$i->format('H').':00, ';
                   }
		 $stringarisposta=$stringarisposta.'}\n';
         }
        else if($view=='settimanale'){
            $datainiziale=new DateTime($datiDate[0]);
            $datafinale= new DateTime($datiDate[1]);
            
           for($i = $datainiziale; $i <= $datafinale; $i->modify('+1 day')){
                      $stringarisposta=$stringarisposta.date_format($i, 'l').', ';
                   
        }
        $stringarisposta=$stringarisposta.'}\n';
}
   else if($view=='mensile'){
            $datainiziale=new DateTime($datiDate[0]);
            $datafinale= new DateTime($datiDate[1]);
            
           for($i = $datainiziale; $i <= $datafinale; $i->modify('+1 month')){
                      $stringarisposta= $stringarisposta.date_format($i, 'F').', ';
                  }
   $stringarisposta=$stringarisposta.'}\n';
}
   $stringarisposta=$stringarisposta.'date [\n';
   
   for($i=0;$i<count($result);$i++){
       $stringarisposta=$stringarisposta.$result[$i].';\n';
   }
   $stringarisposta=$stringarisposta.']\n}';
   echo $stringarisposta;
        break;	
case 'get_midtime':
    /*
           $risposte = 
    array(array('risposta_1_text' => '',
        'numero_di_utenti_che_hanno_risposto_a_questa_risposta' => '',
        'risposta_corretta' => ''),
    array('risposta_2_text' => '',
        'numero_di_utenti_che_hanno_risposto_a_questa_risposta' => '',
        'risposta_corretta' => ''),
    array('risposta_3_text' => '',
        'numero_di_utenti_che_hanno_risposto_a_questa_risposta' => '',
        'risposta_corretta' => ''),
    array('risposta_4_text' => '',
        'numero_di_utenti_che_hanno_risposto_a_questa_risposta' => '',
        'risposta_corretta' => ''),
);
$domande = [
    'id_questionario' => '',
    'domanda_text' => '',
    'domanda_risposta_corretta_testo' => '',
    'tempo_medio_di_risposta_alla_domanda' => '',
    'numero_utenti_che_hanno_risposto_correttamente' => '',
    'numero_totale_di_utenti_che_hanno_effettuato_questa_domanda' => '',
    'risposte' => $risposte
];
$questionari = [
    'domande' => array($domande)
];*/
               $query1= "SELECT camp_id_post_data as primarykeyquestionario, camp_singola_domanda_id,COUNT(*)as numerodomanderipetute,COUNT(camp_singola_domanda_text) as totale, camp_id_questionario,camp_singola_domanda_text FROM questionario,domanda WHERE camp_id_post_data=fk_questionarioid GROUP BY camp_singola_domanda_text";
               $statement = $db->query($query1);
               $resultPrincipQuery=$statement->fetchAll(PDO::FETCH_ASSOC);
               for($i=0; $i<count($resultPrincipQuery); $i++){
                        $domande[$i]['id_questionario']= $resultPrincipQuery[$i]['camp_id_questionario'];
                        $domande[$i]['camp_singola_domanda_text']=$resultPrincipQuery[$i]['camp_singola_domanda_text'];
                        $numtotalediutenti= $resultPrincipQuery[$i]['numerodomanderipetute'];
                        $queryrispostacorretta="SELECT * FROM parco_risposte WHERE fk_domandaid="."'".$resultPrincipQuery[$i]['camp_singola_domanda_id']."'"." AND fk_domandaquestionario="."'".$resultPrincipQuery[$i]['primarykeyquestionario']."'";
                        //echo $queryrispostacorretta."\n";
                        $statement=$db->query($queryrispostacorretta);
                        $result_parco_risposta=$statement->fetchAll(PDO::FETCH_ASSOC);
                        if($result_parco_risposta[0]['camp_singola_domanda_risposta_1_corretta_bool']){
                            $domande[$i]['domanda_risposta_corretta_testo']=$result_parco_risposta[0]['camp_singola_domanda_risposta_1_text'];
                        }
                        else if($result_parco_risposta[0]['camp_singola_domanda_risposta_2_corretta_bool']){
                            
                            $domande[$i]['domanda_risposta_corretta_testo']=$result_parco_risposta[0]['camp_singola_domanda_risposta_2_text'];
                        }
                        else if($result_parco_risposta[0]['camp_singola_domanda_risposta_3_corretta_bool']){
                            $domande[$i]['domanda_risposta_corretta_testo']=$result_parco_risposta[0]['camp_singola_domanda_risposta_3_text'];
                        }
                        else if($result_parco_risposta[0]['camp_singola_domanda_risposta_4_corretta_bool']){
                            $domande[$i]['domanda_risposta_corretta_testo']=$result_parco_risposta[0]['camp_singola_domanda_risposta_4_text'];
                        }
                        else{
                            $domande[$i]['domanda_risposta_corretta_testo']="Error retrieving correct answer!";
                        }
                        $querytempi="SELECT camp_singola_domanda_inizio_timestamp, camp_singola_domanda_risposta_data_timestamp FROM domanda WHERE camp_singola_domanda_text="."'".$resultPrincipQuery[$i]['camp_singola_domanda_text']."'";
                        //echo $querytempi."\n";
                        $statementtimestamp=$db->query($querytempi);
                        $arraytimestamp= $statementtimestamp->fetchAll(PDO::FETCH_ASSOC);
                        $tempitotali=count($arraytimestamp);
                        $seconditotali=0;
                        for($j=0;$j<$tempitotali;$j++){
                            $datainiziod= $arraytimestamp[$j]['camp_singola_domanda_inizio_timestamp'];
                            $datafined= $arraytimestamp[$j]['camp_singola_domanda_risposta_data_timestamp'];
                            $intervallo= differenza_date($datafined, $datainiziod);
                            $seconditotali+=($intervallo*1);
                                    }
                                    $tempomediodomanda=$seconditotali/$tempitotali;
                                    $domande[$i]['tempo_medio_di_risposta_alla_domanda']="".$tempomediodomanda." s";
                                    $querycorrect= "SELECT COUNT(*) as utenti_correct FROM domanda WHERE camp_singola_domanda_text LIKE "."'".$resultPrincipQuery[$i]['camp_singola_domanda_text']."'"." AND camp_singola_domanda_risposta_data_corretta_bool=true";
                                   // echo $querycorrect."\n";
                                    $statementutenticorrect=$db->query($querycorrect);
                                    $resultcorrect= $statementutenticorrect->fetchAll(PDO::FETCH_ASSOC);
                                    $domande[$i]['numero_utenti_che_hanno_risposto_correttamente']=$resultcorrect[0]['utenti_correct'];
                                    $domande[$i]['numero_totale_di_utenti_che_hanno_effettuato_questa_domanda'] = $tempitotali;
                                    $querynumrisposta1= "SELECT COUNT(*) as numutentirisposta1 FROM domanda WHERE camp_singola_domanda_risposta_data_text LIKE "."'".$result_parco_risposta[0]['camp_singola_domanda_risposta_1_text']."'";
                                    //echo $querynumrisposta1."\n";
                                    $statementnumutentirisposta1=$db->query($querynumrisposta1);
                                    $numutentirisposta1= $statementnumutentirisposta1->fetchAll(PDO::FETCH_ASSOC);
                                    $risposte[0]['risposta_1_text']= $result_parco_risposta[0]['camp_singola_domanda_risposta_1_text'];
                                    $risposte[0]['numero_di_utenti_che_hanno_risposto_a_questa_risposta']= $numutentirisposta1[0]['numutentirisposta1'];
                                    $risposte[0]['risposta_corretta']= $result_parco_risposta[0]['camp_singola_domanda_risposta_1_corretta_bool'];
                                    
                                    $querynumrisposta2= "SELECT COUNT(*) as numutentirisposta2 FROM domanda WHERE camp_singola_domanda_risposta_data_text LIKE"."'".$result_parco_risposta[0]['camp_singola_domanda_risposta_2_text']."'";
                                    $statementnumutentirisposta2=$db->query($querynumrisposta2);
                                    $numutentirisposta2= $statementnumutentirisposta2->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    $risposte[1]['risposta_2_text']= $result_parco_risposta[0]['camp_singola_domanda_risposta_2_text'];
                                    $risposte[1]['numero_di_utenti_che_hanno_risposto_a_questa_risposta']= $numutentirisposta2[0]['numutentirisposta2'];
                                    $risposte[1]['risposta_corretta']= $result_parco_risposta[0]['camp_singola_domanda_risposta_2_corretta_bool'];
                                    
                                    $querynumrisposta3= "SELECT COUNT(*) as numutentirisposta3 FROM domanda WHERE camp_singola_domanda_risposta_data_text="."'".$result_parco_risposta[0]['camp_singola_domanda_risposta_3_text']."'";
                                    $statementnumutentirisposta3=$db->query($querynumrisposta3);
                                    $numutentirisposta3= $statementnumutentirisposta3->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    $risposte[2]['risposta_3_text']= $result_parco_risposta[0]['camp_singola_domanda_risposta_3_text'];
                                    $risposte[2]['numero_di_utenti_che_hanno_risposto_a_questa_risposta']= $numutentirisposta3[0]['numutentirisposta3'];
                                    $risposte[2]['risposta_corretta']= $result_parco_risposta[0]['camp_singola_domanda_risposta_3_corretta_bool'];
                                    
                                    $querynumrisposta4= "SELECT COUNT(*) as numutentirisposta4 FROM domanda WHERE camp_singola_domanda_risposta_data_text="."'".$result_parco_risposta[0]['camp_singola_domanda_risposta_4_text']."'";
                                    $statementnumutentirisposta4=$db->query($querynumrisposta4);
                                    $numutentirisposta4= $statementnumutentirisposta4->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    $risposte[3]['risposta_4_text']= $result_parco_risposta[0]['camp_singola_domanda_risposta_4_text'];
                                    $risposte[3]['numero_di_utenti_che_hanno_risposto_a_questa_risposta']= $numutentirisposta4[0]['numutentirisposta4'];
                                    $risposte[3]['risposta_corretta']= $result_parco_risposta[0]['camp_singola_domanda_risposta_4_corretta_bool'];
                                    
                                    $domande[$i]['risposte']=$risposte;
                                    $questionari['domande']=$domande;
                        }//fine for domande;
                       echo json_encode($questionari);
                       break;  
    case 'get_filecsv':
      $output = fopen("sondaggi.csv", "w"); 
      fputcsv($output, array('camp_id_post_data', 'camp_id_questionario', 'camp_id_user', 'camp_username', 'camp_timestamp_inizio', 'camp_timestamp_fine','camp_numero_risposte_corrette','camp_numero_risposte_totali'),";");  
      $query = "SELECT * from questionario ORDER BY camp_timestamp_inizio DESC";  
      $statement = $db->query($query);
      $result = $statement->fetchAll(PDO::FETCH_ASSOC);
      foreach($result as $key => $value)  
      {  
           fputcsv($output, $value,';');  
      }  
      
       fputcsv($output, array('camp_singola_domanda_inizio_timestamp','camp_singola_domanda_id','camp_singola_domanda_text','camp_singola_domanda_risposta_data_id','camp_singola_domanda_risposta_data_text','camp_singola_domanda_risposta_data_corretta_bool','camp_singola_domanda_risposta_data_timestamp','fk_questionarioid'),";");
       
      $query2 = "SELECT * FROM domanda ORDER BY fk_questionarioid,camp_singola_domanda_id ASC";
        $statement2 = $db->query($query2);
        $result2= $statement2->fetchAll(PDO::FETCH_ASSOC);
         foreach($result2 as $key => $value)  
      {  
           fputcsv($output, $value,';');  
      } 
        
      fputcsv($output, array('camp_singola_domanda_risposta_1_id','camp_singola_domanda_risposta_1_text','camp_singola_domanda_risposta_1_corretta_bool','camp_singola_domanda_risposta_2_id','camp_singola_domanda_risposta_2_text','camp_singola_domanda_risposta_2_corretta_bool','camp_singola_domanda_risposta_3_id','camp_singola_domanda_risposta_3_text','camp_singola_domanda_risposta_3_corretta_bool','camp_singola_domanda_risposta_4_id','camp_singola_domanda_risposta_4_text','camp_singola_domanda_risposta_4_corretta_bool','fk_domandaid','fk_domandaquestionario'),";");
      $query3 = "SELECT * FROM parco_risposte ORDER BY fk_domandaquestionario,fk_domandaid ASC";
      $statement3 = $db->query($query3);
        $result3= $statement3->fetchAll(PDO::FETCH_ASSOC);
            foreach($result3 as $key => $value)  
      {  
           fputcsv($output, $value,";");  
      }
      $file = 'Panda.jpg';

if (file_exists($file)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.basename($file).'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    exit;
}
      echo "FILE CSV AGGIORNATO!";
      fclose($output);
      break;
        }