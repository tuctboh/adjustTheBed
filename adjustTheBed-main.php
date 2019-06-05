<?php

require_once "random_compat-2.0.18/lib/random.php";

function doMain($post)
{
    $debug=1;
    $stayopen="false";
    $outtext="Something didn't go right, please contact the author via the skill information in the Alexa App";
    $card_outtext="Something didn't go right, please contact the author via the skill information in the Alexa App";
    $amzn_user=$post->session->user->userId;
    $t_prt=$post->request->type;
    if (isset($post->request->intent->name)) {
        $intent=$post->request->intent->name;
    } else {
        $intent=null;
    }
    if (isset($post->request->intent->slots->side->value)) {
        $side=$post->request->intent->slots->side->value;
    } else {
        $side = null;
    }
    if (isset($post->request->intent->slots->sleepnum->value)) {
        $sleepNum=$post->request->intent->slots->sleepnum->value;
    } else {
        $sleepNum = null;
    }
    $appid=$post->session->application->applicationId;
    $d_indb=accessdb("password", "get", $amzn_user);

    //  sanitize_data();

    if ($debug) {
        writeDebug("adjustTheBed", "Amazon User:");
        writeDebug("adjustTheBed", $amzn_user);
        writeDebug("adjustTheBed", "Request Type:");
        writeDebug("adjustTheBed", $t_prt);
        writeDebug("adjustTheBed", "Intent/Request/Name:");
        writeDebug("adjustTheBed", $intent);
        writeDebug("adjustTheBed", "Side:");
        writeDebug("adjustTheBed", $side);
        writeDebug("adjustTheBed", "Sleepnum:");
        writeDebug("adjustTheBed", $sleepNum);
    }

    if (!function_exists('curl_setopt')) {
        $outtext = "This requires cURL as part of PHP";
        $card_outtext = "This requires cURL as part of PHP";
        $t_prt="";
        $intent="";
    }

    if ($t_prt == "SessionEndedRequest") {
        $outtext = "An error occurred with this skill. Please try again or contact the author via the skill information in the Alexa App";
        $card_outtext = "An error occurred with this skill. Please try again or contact the author via the skill information in the Alexa App";
        $t_prt="";
        $intent="";
    }

    if ($intent == "signUp") {
        generateSignup($amzn_user);
        exit;
    }

    if ($intent == "AMAZON.CancelIntent" || $intent == "AMAZON.StopIntent") {
        $outtext = "There is nothing to stop or cancel currently";
        $card_outtext = $outtext;
        outputJSON($outtext, $card_outtext, $stayopen);
    }


    if ($t_prt == "LaunchRequest" || $intent == "AMAZON.FallbackIntent" || $intent == "AMAZON.HelpIntent" || $intent == "AMAZON.NavigateHomeIntent") {
        if (isset($d_indb)) {
            $outtext = "Welcome back to adjust the bed. You are already signed up so feel free to say something like Alexa, ask adjust the bed to set the left side to 40 or Alexa, ask adjust the bed what is the left side sleep number";
            $card_outtext = "Welcome back to 'adjust the bed' for Sleep Number(r). You are already signed up so feel free to say something like Alexa, ask adjust the bed to set the left side to 40 or Alexa, ask adjust the bed what is the left side sleep number";
            $stayopen="true";
        } else {
            $outtext = "Welcome to adjust the bed. You are already signed up so feel free to say something like Alexa, ask adjust the bed to set the left side to 40 or Alexa, ask adjust the bed what is the left side sleep number";
            $card_outtext = "Welcome back to 'adjust the bed' for Sleep Number(r). You are already signed up so feel free to say something like Alexa, ask adjust the bed to set the left side to 40 or Alexa, ask adjust the bed what is the left side sleep number";
        }
    }

    if ($t_prt != "LaunchRequest") {
        if (!isset($d_indb)) {
            $outtext = "Thank you for using 'adjust the bed'. To be able to use this skill you must first sign up. Just say \"Alexa, ask adjust the bed to sign up\" for the signup link";
            $card_outtext = "Welcome to 'adjust the bed' for Sleep Number(r). To be able to use this skill you must first sign up. Just say \"Alexa, ask adjust the bed to sign up\" for the signup link";
            $intent="";
        }
    }

    if ($intent == "resetUser") {
        if ($debug) {
            writeDebug("adjustTheBed", "In resetUser");
        }
        accessdb("password", "delete", $amzn_user);
        generateSignup($amzn_user);
        exit;
    }

    if ($intent == "showUserID") {
        if ($debug) {
            writeDebug("adjustTheBed", "In showUserID");
        }
        $outtext="Your userid is ".$amzn_user." and the skillid is ".$appid;
        $card_outtext=$outtext;
    }

    if ($intent == "getSleepNumber") {
        if ($debug) {
            writeDebug("adjustTheBed", "In getSleepNumber");
        }

        $response = getResults($amzn_user, "getSleepNumber");

        if ($debug) {
            writeDebug("adjustTheBed", "Left is ".$response->beds[0]->leftSide->sleepNumber);
            writeDebug("adjustTheBed", "Right is ".$response->beds[0]->rightSide->sleepNumber);
        }

        if ($side == "left") {
            $sleepNumber = $response->beds[0]->leftSide->sleepNumber;
        } else {
            $sleepNumber = $response->beds[0]->rightSide->sleepNumber;
        }

        if ($debug) {
            writeDebug("adjustTheBed", "Setting for ".$side." is ".$sleepNumber);
        }

        $outtext="The sleep number for the ".$side." side is ".$sleepNumber;
        $card_outtext=$outtext;
    }

    if ($intent == "isInBed") {
        if ($debug) {
            writeDebug("adjustTheBed", "In isInBed");
        }

        $response = getResults($amzn_user, "isInBed");

        if ($debug) {
            writeDebug("adjustTheBed", "Left is ".$response->beds[0]->leftSide->isInBed);
            writeDebug("adjustTheBed", "Right is ".$response->beds[0]->rightSide->isInBed);
        }

        if ($side == "left") {
            $isInBed = $response->beds[0]->leftSide->isInBed;
        } else {
            $isInBed = $response->beds[0]->rightSide->isInBed;
        }

        if ($debug) {
            writeDebug("adjustTheBed", "Setting ".$side." and ".$isInBed);
        }

        $outtext="Someone is currently";
        $outtext.=$isInBed ? ' ' : ' not ';
        $outtext.="in the ".$side." side of the bed";
        $card_outtext=$outtext;
    }

    if ($intent == "setSleepNumber") {
        if ($debug) {
            writeDebug("adjustTheBed", "In setSleepNumber");
        }

        if ($side == "left") {
            $shortside="L";
        } else {
            $shortside="R";
        }

        if ($sleepNum < 0 || $sleepNum > 100) {
            $outtext = "The sleep number specified is less than zero or higher than 100. Please try your request again with a number between 0 and 100";
            $card_outtext=$outtext;
        } else {
            $setSleepNumber_data=[
              "side"    => $shortside,
              "sleepNumber" => $sleepNum,
            ];

            $response = getResults($amzn_user, "setSleepNum", $setSleepNumber_data);

            $outtext="The ".$side." side of the bed is being changed to ".$sleepNum;
            $card_outtext=$outtext;
        }
    }


    if ($debug) {
        writeDebug("adjustTheBed", "Out text");
        writeDebug("adjustTheBed", $outtext);
    }

    outputJSON($outtext, $card_outtext, $stayopen);
}

function outputJSON($text, $card, $stayopen)
{
    header('Content-Type: application/json');
    $PHP_Output                                     = array(
  'version' => '1.0',
  'response' => array(
    'outputSpeech' => array(
      'type' => 'PlainText'
    ),
    'card' => array(
      'type' => 'Simple',
      'title' => 'Adjust The Bed for Sleep Number(r)',
      'text' => 'Text content for a standard card',
      'content' => 'This is missing'
    ),
    'shouldEndSession' => 'true'
  )
);
    $PHP_Output['response']['outputSpeech']['text'] = $text;
    $PHP_Output['response']['card']['content'] = $card;
    $PHP_Output['response']['shouldEndSession'] = $stayopen;

    echo json_encode($PHP_Output, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function writeDebug($f_debugfile, $f_msg)
{
    $fh_debugout = fopen("/tmp/".$f_debugfile.".txt", "a");
    fwrite($fh_debugout, "[".microtime(true)."] - ".$f_msg."\n");
    fclose($fh_debugout);
}

function requestJSONHasLoginErrors($response)
{
    writeDebug("requestJSONHasLoginErrors", "----------------------");
    if (property_exists($response, 'Error')) {
        $error_code    = property_exists($response->Error, 'Code')    ? $response->Error->Code    : null;
        $error_message = property_exists($response->Error, 'Message') ? $response->Error->Message : null;
        writeDebug("requestJSONHasLoginErrors", "Error code $error_code");
        writeDebug("requestJSONHasLoginErrors", "Error message $error_message");

        $login_error_codes = [
      50002,
      401,
    ];
        $login_error_messages = [
      "Session is invalid",
      "HTTP 401 Unauthorized",
    ];

        if (in_array($error_code, $login_error_codes)) {
            writeDebug("requestJSONHasLoginErrors", "------code------------");
            return true;
        }
        if (in_array($error_message, $login_error_messages)) {
            writeDebug("requestJSONHasLoginErrors", "-------message-------");
            return true;
        }
    }

    writeDebug("requestJSONHasLoginErrors", "-------false-------");
    return false;
}

function accessdb($db, $function, $key, $data = null)
{
    $db_store=$db.".db";
    $dbh = dba_open($db_store, 'c', 'db4') or die("Can't open db $db_store");

    if ($function == "get") {
        if ($exists = dba_exists($key, $dbh)) {
            $record=dba_fetch($key, $dbh) or die($php_errormsg);
            dba_close($dbh);
            return $record;
        } else {
            dba_close($dbh);
            return null;
        }
    }

    if ($function == "put") {
        dba_insert($key, $data, $dbh);
        dba_close($dbh);
        return;
    }

    if ($function == "delete") {
        dba_delete($key, $dbh);
        dba_close($dbh);
        return;
    }
}

function random_str($length = 48, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
{
    $keyspace = str_shuffle($keyspace);
    $pieces = [];
    $max = mb_strlen($keyspace, '8bit') - 1;
    for ($i = 0; $i < $length; ++$i) {
        $pieces []= $keyspace[random_int(0, $max)];
    }
    return implode('', $pieces);
}

function getResults($f_amzn_user, $function, $data = null)
{
    if ($function == "getBeds") {
        $getbeds =  requestJSON($f_amzn_user, '/rest/bed');
        return $getbeds;
    }

    if ($function == "getSleepNumber" ||
    $function == "isInBed" ||
    $function == "getBedNum") {
        $getBedFamilyStatus =  requestJSON($f_amzn_user, '/rest/bed/familyStatus');
        return $getBedFamilyStatus;
    }

    if ($function == "setSleepNum") {
        if ($debug) {
            writeDebug("setSleepNum", "Setting setSleepNum_data");
            writeDebug("setSleepNum", "Sleep side");
            writeDebug("setSleepNum", $data['side']);
            writeDebug("setSleepNum", "Sleep num");
            writeDebug("setSleepNum", $data['sleepNumber']);
            writeDebug("setSleepNum", "getting BedNum");
        }
        $response = getResults($f_amzn_user, "getBedNum");
        $bedId=$response->beds[0]->bedId;
        $data['bedId']=$bedId;
        $request_url="/rest/bed/".$bedId."/sleepNumber";
        if ($debug) {
            writeDebug("setSleepNum", "bedId");
            writeDebug("setSleepNum", $bedId);
            writeDebug("setSleepNum", "bedId");
            writeDebug("setSleepNum", $data['bedId']);
            writeDebug("setSleepNum", "request_url");
            writeDebug("setSleepNum", $request_url);
        }
        $putSleepNum =  requestJSON($f_amzn_user, $request_url, $data, "PUT");
        if ($debug) {
            writeDebug("setSleepNum", "----------------------");
        }
        return $putSleepNum;
    }
}


function requestJSON($f_amzn_user, $path, $data = null, $method = "GET")
{
    $debug       = 1;
    $site_url    = "https://prod-api.sleepiq.sleepnumber.com";
    $user_agent  = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)";
    $token=null;

    if ($data != "*PASSPROXY*") {
        if ($path != "/rest/login") {
            $token=accessdb("token", "get", $f_amzn_user);

            if (is_null($token)) {
                $login_url="/rest/login";
                $userpassorurl=accessdb("password", "get", $f_amzn_user);
                if (strpos($userpassorurl, '|') == true) {
                    list($t_login_user, $t_login_pass)=explode("|", $userpassorurl);
                    $login_data=[
                         "login"    => $t_login_user,
                         "password" => $t_login_pass,
                     ];
                    $login_return = requestJSON($f_amzn_user, $login_url, $login_data, "PUT");
                    $token=$login_return->key;
                    accessdb("token", "put", $f_amzn_user, $token);
                } else {
                    $login_return = requestJSON($f_amzn_user, $userpassorurl, "*PASSPROXY*");
                    $token=$login_return->key;
                    writeDebug("urlget", "Got token of $token");
                    accessdb("token", "put", $f_amzn_user, $token);
                    writeDebug("urlget", "Got put user $f_amzn_user and token of $token");
                }
            }
        }

        if (is_null($token)) {
            $url=$site_url.$path;
        } else {
            $url = $site_url.$path."?_k=".$token;
        }
    } else {
        $url = $path;
    }

    if ($debug) {
        writeDebug("rJ", "Identified URL $url");
    }

    ob_start();
    $out=fopen('php://output', 'w');

    $request = curl_init($url);

    $cookie_file = "cookie/".$f_amzn_user.".txt";
    if (!is_dir(dirname($cookie_file))) {
        mkdir(dirname($cookie_file), 0777, true);
    }
    if (!is_file($cookie_file)) {
        chmod(dirname($cookie_file), 0777);
        touch($cookie_file);
        chmod($cookie_file, 0777);
    }

    if ($debug) {
        writeDebug("rJ", "Cookie file is $cookie_file");
    }

    if ($debug) {
        curl_setopt($request, CURLOPT_VERBOSE, true);
        curl_setopt($request, CURLOPT_STDERR, $out);
    }
    curl_setopt($request, CURLOPT_COOKIEFILE, $cookie_file);
    curl_setopt($request, CURLOPT_COOKIEJAR, $cookie_file);
    curl_setopt($request, CURLOPT_ENCODING, "gzip");
    curl_setopt($request, CURLOPT_USERAGENT, $user_agent);
    curl_setopt($request, CURLOPT_HEADER, 0);
    curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);

    if (is_array($data)) {
        curl_setopt($request, CURLOPT_POSTFIELDS, json_encode($data));
    }
    curl_setopt($request, CURLOPT_HTTPHEADER, array( 'Content-Type:application/json' ));
    curl_setopt($request, CURLOPT_CUSTOMREQUEST, $method);

    $response = curl_exec($request);

    if ($data == "*PASSPROXY*") {
        $cookies = curl_getinfo($request, CURLINFO_COOKIELIST);
        $loop2 = 0;
        for ($loop = 0; $loop < count($cookies); $loop++) {
            if (preg_match('/prod-api\.sleepiq\.sleepnumber\.com/', $cookies[$loop])) {
                // Tossing
            } else {
                // Keeping
                $subcookies[$loop2]= $cookies[$loop];
                $loop2++;
            }
        }
        curl_setopt($request, CURLOPT_COOKIELIST, "ALL");
        for ($loop3 = 0; $loop3 < $loop2; $loop3++) {
            list($t_hostname, $t_include_sub, $t_path, $t_secure, $t_expire, $t_name, $t_value)=explode("\t", $subcookies[$loop3]);
            $cookieline='Set-Cookie: '.$t_name."=".$t_value."; path=".$t_path."; domain=prod-api.sleepiq.sleepnumber.com; Secure; HttpOnly";
            curl_setopt($request, CURLOPT_COOKIELIST, $cookieline);
        }
    }

    //if ($debug) {
    writeDebug("CURL_EXEC", $response);
    //}

    if (curl_errno($request)) {
        throw new Exception("Response Error: ".curl_error($request));
    }

    fclose($out);
    $outdebug=ob_get_clean();
    if ($debug) {
        writeDebug("CURL", $outdebug);
    }

    curl_close($request);

    $json_response = json_decode($response);

    if (!$json_response) {
        throw new Exception("requestJSON(): Missing/Invalid Response");
    }

    if (property_exists($json_response, 'Error') && $path == "/rest/login") {
        outputJSON("Your userid/password or adjustTheBedPassProxy is invalid. Please say Alexa, ask adjust the bed to reset my information to get new signup information", "Your userid/password or adjustTheBedPassProxy is invalid. Please say Alexa, ask adjust the bed to reset my information to get new signup information", "false");
        exit;
    }

    if (requestJSONHasLoginErrors($json_response)) {
        writeDebug("requestJSONHasLoginErrors", "in requestJSONHasLoginErrors");
        accessdb("token", "delete", $f_amzn_user);
        writeDebug("requestJSONHasLoginErrors", "token deleted for $f_amzn_user");
        writeDebug("requestJSONHasLoginErrors", "Would have re-run using $f_amzn_user  $path , $data, $method\n");
        $json_response=requestJSON($f_amzn_user, $path, $data, $method);
        writeDebug("requestJSONHasLoginErrors", "re-executing command");
    }

    if (property_exists($json_response, 'Error')) {
        throw new Exception("requestJSON(): [".$json_response->Error->Code."] ".$json_response->Error->Message."");
    }

    if ($debug) {
        writeDebug("rJ", "Dumping response");
        writeDebug("rJ", $response);
        //writeDebug("rJ", "Dumping json_response");
        //ob_start();
        //var_dump($json_response);
        //writeDebug("rJ", ob_get_flush());
    }
    return $json_response;
}

function generateSignup($theuser)
{
    $outtext = "Welcome to adjust the bed. Please see the Alexa app for how to sign up";
    $signup=accessdb("signup", "get", $theuser);
    if (! isset($signup)) {
        $signup=random_str();
        $insignup=accessdb("signup", "get", $signup);
        if (isset($insignup)) {
            $card_outtext = "Fix me later, there was a duplicate signup.";
        } else {
            accessdb("signup", "put", $signup, $theuser);
            accessdb("signup", "put", $theuser, $signup);
        }
    }
    $card_outtext = "To be able to use the 'adjust the bed' app, you'll need to link it to your userid and password for sleepnumber or your AdjustTheBedPassProxy. Please use the following signup link - https://adjustTheBed.com/adjustTheBed/signup.php?access=".$signup;
    outputJSON($outtext, $card_outtext, "false");
}
