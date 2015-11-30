#!/usr/bin/php -q
<?php
// load config
$config = parse_ini_file("print.conf", TRUE);
// echo $config["lib"]["mailparser"];

// include Mailparser Library
require_once $config["lib"]["mailparser"];
$Parser = new PhpMimeMailParser\Parser();

// set Variables
$__PATH__ = $config["common"]["root"];
$__LOG__ = $config["common"]["log"];
$__MAIL__ = true;

if(isset($argv[1])) {
$__FILE__ = $__PATH__.$argv[1];
}

// Log
function writeLog($msg) {

    $config = $GLOBALS["config"];

    // get variable out of here when in oop context
    $log = $config["common"]["log"];
    $fdw = fopen($log, "a+");
    fwrite($fdw, $msg . "\n");
    fclose($fdw);
}

writeLog("--- STARTING WORKER PROCESS ---");

$email = "";

if (isset($__FILE__)) {
// if command line argument (local file) exists

    writeLog("--- READING LOCAL FILE ---");
    // writeLog("source file: ".$__FILE__);

    $localfile = fopen($__FILE__,"r");

    // read file content
    while(!feof($localfile))
    {
        $email .= fgets($localfile,1024);
        // writeLog($email);
        }

    $__MAIL__ = false;

    // close file
    fclose($localfile);

    writeLog($email);

} else {
// if no command line argument ist passed

    writeLog("--- READING MAIL ---");
/*
    //listen to incoming e-mails
    $sock = fopen ("php://stdin", 'r');

    //read e-mail into buffer
    while (!feof($sock))
    {
        $email .= fread($sock, 1024);
    }

    //close socket
    fclose($sock);
*/

    $Parser->setStream(fopen("php://stdin", "r"));
}

if($__MAIL__) {

    // $Parser = new PhpMimeMailParser\Parser();
    // $Parser->setText(file_get_contents($email));

    $to = $Parser->getHeader('to');
    $from = $Parser->getHeader('from');
    $subject = $Parser->getHeader('subject');

    $text = $Parser->getMessageBody('text');
    $html = $Parser->getMessageBody('html');
    $htmlEmbedded = $Parser->getMessageBody('htmlEmbedded'); //HTML Body included data

    writeLog("-- Html-Text: " .$html);
    writeLog("-- Html-Text (Data): " .$htmlEmbedded);


} else {
    // $__MAIL__ = false;

    $to = "";
    $from = "";
    $subject = "";
    $message = $email;

}

    $date_rfc = date(DATE_RFC822);
    $date = date("Y-m-d_H-i-s");

    $uid = uniqid();
    $udate = $date."__".$uid;

    $printer = "";

    switch($to) {

        case "kyocera@mail.bib.uni-mannheim.de": $printer = "Kyocera_ECOSYS_M2530dn";
            break;
        case "konica@mail.bib.uni-mannheim.de": $printer = "KONICA_MINOLTA_C360";
            break;
        case "epson@mail.bib.uni-mannheim.de": $printer = "T88V";
            break;
        default: $printer = "Kyocera_ECOSYS_M2530dn";
    }

    $mailstr = "New mail received at " .$printer. " :" .$date_rfc. "\nSubject: " .$subject. "\nTo: " .$to. "\nFrom :" .$from. "\nText: \n" .$htmlEmbedded;

/*
    writeLog("-- EMail Inhalte\n ");
    writeLog("-- Betreff: ". $subject);
    writeLog("-- An: ". $to);
    writeLog("-- Von: ". $from);
    writeLog("-- Inhalt msg: ". $htmlEmbedded);
*/

/*
    // ? test whether necessary
    if(!($__MAIL__)) {
        $mailstring = $message;
    }
*/

    $filename = $config["common"]["tmp"]."incoming__".$udate.".html";
    $pdf = $config["common"]["tmp"]."pdf__".$udate.".pdf";
    writeLog("-- writing html file: ".$filename);

    $fdw = fopen($filename, "w+");
    // Embedded Html Only
    fwrite($fdw, $htmlEmbedded);

    // old self-generated "header"
    // fwrite($fdw, $mailstr);

    // all information from stdin
    // fwrite($fdw, $email);

    fclose($fdw);

    writeLog("-- file: ".$filename." written");

    // html to pdf
    writeLog("-- create pdf: ".$pdf);

    $convert_cmd = "/usr/local/bin/wkhtmltopdf -q ".$filename." ".$pdf;

    writeLog("-- ". $convert_cmd);

    exec($convert_cmd);

    if (file_exists($pdf)) {
        writeLog("-- file: ".$pdf." successfully created");
    } else {
        writeLog("-- file: ".$pdf." not found");
    }

    writeLog("-- start printing: ".$pdf);

    $print_cmd = "lp -d " .$printer. " " .$pdf; // ." >/dev/null 2>&1 &";

    writeLog("-- ". $print_cmd);

    shell_exec($print_cmd);

    // unlink($filename);
    // unlink($pdf);

writeLog("--- END ---");

?>
