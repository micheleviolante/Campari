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
header('Access-Control-Allow-Origin: *');
$hostname = "localhost";
$dbname = "sondaggi";
$user = "root";
$port = 3306;
$db = new PDO("mysql:host=$hostname;dbname=$dbname; port=$port", $user, '') or die();
echo "connessione avvenuta <br/>";
$string = file_get_contents('php://input');
$data= valida_json($string);
$first_key = key($data);
$id_postdata;
reset($data);
if ($first_key == 'camp_id_post_data') {        //controllo che è il JSON di inserimento
    $stringaqueryquest = "INSERT INTO questionario(";
    $contquest = 0;
    //INIZIO RIEMPIMENTO TABELLA QUESTIONARIO
    while (key($data) != 'camp_dettagli_traccimaneto') {
        if (key($data) == "camp_id_post_data") {
            $id_postdata = $data[key($data)];
        }
            $stringaqueryquest = $stringaqueryquest . key($data) . ',';
            if (key($data) == "camp_timestamp_inizio"){
             $timestamp=round($data[key($data)]/1000);
            $date=date("Y-m-d H:i:s",$timestamp);
            $valorequest[$contquest] = $date;
            }else{
            $valorequest[$contquest] = $data[key($data)];
            }
            next($data);
            $contquest++;
        
    }
    next($data);
    $timestamp=round($data[key($data)]/1000);
    $date=date("Y-m-d H:i:s",$timestamp);
    $valorequest[$contquest] = $date;
    $contquest++;
    $stringaqueryquest=$stringaqueryquest.key($data).',';
    next($data);
    $valorequest[$contquest] = $data[key($data)];
    $contquest++;
    $stringaqueryquest=$stringaqueryquest.key($data).',';
    next($data);
    $valorequest[$contquest] = $data[key($data)];
    $contquest++;
    $stringaqueryquest=$stringaqueryquest.key($data);
    
    prev($data);
    prev($data);
    prev($data);
    $stringaqueryquest = $stringaqueryquest . ')';
    $stringaqueryquest = $stringaqueryquest . " VALUES(";
    for ($contvaluesquest = 0; $contvaluesquest < 8; $contvaluesquest++) {
       if ($contvaluesquest == 7) {
            $stringaqueryquest = $stringaqueryquest . "'$valorequest[$contvaluesquest]'";
        } else {
            $stringaqueryquest = $stringaqueryquest . "'$valorequest[$contvaluesquest]'" . ',';
        }
    }
    $stringaqueryquest = $stringaqueryquest . ')';
    echo "$stringaqueryquest <br/>";
    $statementq = $db->prepare($stringaqueryquest);
    if (!$statementq->execute()) {
        echo "Attenzione: intero questionario non inserito! <br/>";
        print_r($statementq->errorCode().": ".$statementq->errorInfo());
        exit(1);
    } else {
        echo "Query questionario eseguita!<br/>";
    }
    $arraydomande = $data[key($data)];
    $numtotdomande = sizeof($arraydomande);
    $domanda = $arraydomande[0];
    $contdom = 0;
    $valoredom = [];
    $contvaluesdom = 0;
    $i = 0;
    $domandaid = "";
    $stringaqueryrisp = "";
    $valorerisposte = [];
    while ($contdom < $numtotdomande) {
        $stringaquerydom = "INSERT INTO domanda(";
        foreach ($arraydomande[$contdom] as $key => $value) {
            if($key=='camp_singola_domanda_risposta_data_id' || $key=='camp_singola_domanda_risposta_data_text' || $key=='camp_singola_domanda_risposta_data_corretta_bool'|| $key=='camp_singola_domanda_risposta_data_timestamp'){                
            }
           else if ($key == "camp_singola_domanda_parco_risposte") {
                for($z=0;$z<4;$z++){
                    next($arraydomande[$contdom]);
                }
                $stringaquerydom = $stringaquerydom .key($arraydomande[$contdom]). ',';
                $valoredom[$contvaluesdom] = $arraydomande[$contdom][key($arraydomande[$contdom])];
                $contvaluesdom++;
                next($arraydomande[$contdom]);
                $stringaquerydom = $stringaquerydom .key($arraydomande[$contdom]). ',';
                $valoredom[$contvaluesdom] = $arraydomande[$contdom][key($arraydomande[$contdom])];
                $contvaluesdom++;
                next($arraydomande[$contdom]);
                $stringaquerydom = $stringaquerydom .key($arraydomande[$contdom]). ',';
                $valoredom[$contvaluesdom] = $arraydomande[$contdom][key($arraydomande[$contdom])];
                $contvaluesdom++;
                next($arraydomande[$contdom]);
                $stringaquerydom = $stringaquerydom .key($arraydomande[$contdom]). ',';
                 $timestamp=round(($arraydomande[$contdom][key($arraydomande[$contdom])])/1000);
                 $date=date("Y-m-d H:i:s",$timestamp);
                 $valoredom[$contvaluesdom] = $date;
                 $contvaluesdom++;
                for($z=0;$z<8;$z++)
                {
                    prev($arraydomande[$contdom]);
                }
                $stringaquerydom = $stringaquerydom . "fk_questionarioid" . ') VALUES(';
                $dimensionedomanda = count($valoredom);
                for ($i = 0; $i < $dimensionedomanda; $i++) {
                    if ($i == 5) {
                        $stringaquerydom = $stringaquerydom . $valoredom[$i] . ',';
                    }  else{
                        $stringaquerydom = $stringaquerydom . "'$valoredom[$i]'" . ',';
                    }
                }
                $stringaquerydom = $stringaquerydom . "'$id_postdata'" . ')';
                $statementd = $db->prepare($stringaquerydom);
                if (!$statementd->execute()) {
                    echo "Attenzione: La domanda avente id: ".$domandaid." del questionario: ".$id_postdata. " insieme alle relative parco risposte, NON è stata inserita <br/>";
                    print_r($statementd->errorCode().": ".$statementd->errorInfo());
                    exit(1);
                } else {
                    echo "Query domanda eseguita! <br/>";
                }
                $stringaqueryrisp = "INSERT INTO parco_risposte(";
                $arrayrisp = $arraydomande[$contdom][$key];
                $contatore = 0;
                $arrayrisposte = $arrayrisp[0];
                foreach ($arrayrisposte as $chiaverisp => $valorerisp) {
                    $stringaqueryrisp = $stringaqueryrisp . "$chiaverisp" . ',';
                    $valorerisposte[$contatore] = $valorerisp;
                    $contatore++;
                }
                $stringaqueryrisp = $stringaqueryrisp . "fk_domandaid," . "fk_domandaquestionario";
                $stringaqueryrisp = $stringaqueryrisp . ') VALUES(';
                $dimensionearray = sizeof($valorerisposte);
                for ($cont = 0; $cont < $dimensionearray; $cont++) {
                    if ($cont == 2 || $cont == 5 || $cont == 8 || $cont == 11) {
                        $stringaqueryrisp = $stringaqueryrisp . $valorerisposte[$cont] . ',';
                    } else {
                        $stringaqueryrisp = $stringaqueryrisp . "'$valorerisposte[$cont]'" . ',';
                    }
                }
                $stringaqueryrisp = $stringaqueryrisp . "'$domandaid'" . ',' . "'$id_postdata'" . ")";
                $statementr = $db->prepare($stringaqueryrisp);
                if (!$statementr->execute()) {
                    echo "Attenzione: le 4 risposte relative alla domanda avente id: ".$domandaid." del questionario avente id: ".$id_postdata." non sono state inserite <br/>";
                    print_r($statementr->errorCode().": ".$statementr->errorInfo());
                    exit(1);           
                } else {   
                    echo "Query risposta eseguita! <br/>";
                }
                
                $contdom++;
                $contvaluesdom = 0;
            } 
            else {
                if ($key == "camp_singola_domanda_id") {
                    $domandaid = $arraydomande[$contdom][$key];
                    $valoredom[$contvaluesdom] = $arraydomande[$contdom][$key];
                    $stringaquerydom = $stringaquerydom . $key . ',';
                    $contvaluesdom++;
                }
                else if($key=="camp_singola_domanda_inizio_timestamp"){
                    $stringaquerydom = $stringaquerydom . $key . ',';
                    $timestamp=round(($arraydomande[$contdom][$key])/1000);
                    $date=date("Y-m-d H:i:s",$timestamp);
                    $valoredom[$contvaluesdom] = $date;
                    $contvaluesdom++;
                }
                else {
                    
                    $stringaquerydom = $stringaquerydom . $key . ',';
                    $valoredom[$contvaluesdom] = $arraydomande[$contdom][$key];
                    $contvaluesdom++;
                    
                }
            } //FINE ELSE 
        }
    } //FINE WHILE
}