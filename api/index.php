<?php

session_start();
require_once '../include/DbHandler.php';
require_once '../include/PassHash.php';
require_once '../include/Params.php';
require '../libs/Slim/Slim.php';
require_once './static_var.php';
\Slim\Slim::registerAutoloader();
require_once('../include/ImageHelper.php');
$app = new \Slim\Slim();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$app->options('/:method','corps', function($method) use ($app) {
    echoRespnse(200, null);
});
$app->options('/:method/:resource_id','corps', function($method,$resource_id) use ($app) {
    echoRespnse(200, null);
});
function corps(\Slim\Route $route){
    if($route){
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: AuthenticationKey,content-type");
        header("Access-Control-Allow-Methods: GET, POST");        
    }
}
/* * Adding Middle Layer to authenticate every request
 *
 * Checking if the request has valid api key in the 'Authorization' header
 */

function authenticate(\Slim\Route $route) {
    // Getting request headers
    $app = \Slim\Slim::getInstance();
    $headers = $app->request->headers("AuthenticationKey");
    if(isset($_SESSION['AuthenticationKey']) &&
        isset($headers) &&
        $headers === $_SESSION['AuthenticationKey'] ){
        global $user_id;
        $user_id = $_SESSION['user_id'];
    }else{
        $response = array();
        // Verifying Authorization Header
        if (isset($headers)) {
            $db = new DbHandler();
            // get the api key
            $api_key = $headers;
            // validating api key
            $user = $db->isValidApiKey($api_key);

            if (null !== $user && intval($user['fk_usr_id']) > 1) {
                global $user_id;
                if(!isset($_SESSION['AuthenticationKey'])){
                    $_SESSION['AuthenticationKey'] = $headers;
                    $_SESSION['user_id'] = $user['fk_usr_id'];
                }
                $user_id = $user['fk_usr_id'];
            } else {
                // api key is not present in users table
                $response[ERROR] = true;
                $response[MESSAGE] = "INVALID_API_KEY";
                echoRespnse(401, $response);
                $app->stop();
            }
        } else {
            // api key is missing in header
            $response[ERROR] = true;
            $response[MESSAGE] = "Api key is misssing";//print_r($headers,true);//
            echoRespnse(400, $response);
            $app->stop();
        }
    }

}

$user_id = NULL;
function getEmailToken($len = 8){
    $characters = 'WERTYUIPASDFGHJKLZXCVBNM123456789';
    $randomString = '';
    for ($i = 0; $i < $len; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}
function getUserAgent(){
    return $_SERVER['HTTP_USER_AGENT'];
}
function getUserIP()
{
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = $_SERVER['REMOTE_ADDR'];

    if(filter_var($client, FILTER_VALIDATE_IP))
    {
        $ip = $client;
    }
    elseif(filter_var($forward, FILTER_VALIDATE_IP))
    {
        $ip = $forward;
    }
    else
    {
        $ip = $remote;
    }

    return $ip;
}
function getVersion(){
    return '0.1';
}
/**
 * Verifying required params posted or not
 */
function isDisposableEmail($email){
    $blocked = array('0-mail.com','0815.ru','0clickemail.com','0wnd.net','0wnd.org','10minutemail.com','20minutemail.com','2prong.com','30minutemail.com','3d-painting.com','4warding.com','4warding.net','4warding.org','60minutemail.com','675hosting.com','675hosting.net','675hosting.org','6url.com','75hosting.com','75hosting.net','75hosting.org','7tags.com','9ox.net','a-bc.net','afrobacon.com','ajaxapp.net','amilegit.com','amiri.net','amiriindustries.com','anonbox.net','anonymbox.com','antichef.com','antichef.net','antispam.de','baxomale.ht.cx','beefmilk.com','binkmail.com','bio-muesli.net','bobmail.info','bodhi.lawlita.com','bofthew.com','brefmail.com','broadbandninja.com','bsnow.net','bugmenot.com','bumpymail.com','casualdx.com','centermail.com','centermail.net','chogmail.com','choicemail1.com','cool.fr.nf','correo.blogos.net','cosmorph.com','courriel.fr.nf','courrieltemporaire.com','cubiclink.com','curryworld.de','cust.in','dacoolest.com','dandikmail.com','dayrep.com','deadaddress.com','deadspam.com','despam.it','despammed.com','devnullmail.com','dfgh.net','digitalsanctuary.com','discardmail.com','discardmail.de','Disposableemailaddresses:emailmiser.com','disposableaddress.com','disposeamail.com','disposemail.com','dispostable.com','dm.w3internet.co.ukexample.com','dodgeit.com','dodgit.com','dodgit.org','donemail.ru','dontreg.com','dontsendmespam.de','dump-email.info','dumpandjunk.com','dumpmail.de','dumpyemail.com','e4ward.com','email60.com','emaildienst.de','emailias.com','emailigo.de','emailinfive.com','emailmiser.com','emailsensei.com','emailtemporario.com.br','emailto.de','emailwarden.com','emailx.at.hm','emailxfer.com','emz.net','enterto.com','ephemail.net','etranquil.com','etranquil.net','etranquil.org','explodemail.com','fakeinbox.com','fakeinformation.com','fastacura.com','fastchevy.com','fastchrysler.com','fastkawasaki.com','fastmazda.com','fastmitsubishi.com','fastnissan.com','fastsubaru.com','fastsuzuki.com','fasttoyota.com','fastyamaha.com','filzmail.com','fizmail.com','fr33mail.info','frapmail.com','front14.org','fux0ringduh.com','garliclife.com','get1mail.com','get2mail.fr','getonemail.com','getonemail.net','ghosttexter.de','girlsundertheinfluence.com','gishpuppy.com','gowikibooks.com','gowikicampus.com','gowikicars.com','gowikifilms.com','gowikigames.com','gowikimusic.com','gowikinetwork.com','gowikitravel.com','gowikitv.com','great-host.in','greensloth.com','gsrv.co.uk','guerillamail.biz','guerillamail.com','guerillamail.net','guerillamail.org','guerrillamail.biz','guerrillamail.com','guerrillamail.de','guerrillamail.net','guerrillamail.org','guerrillamailblock.com','h.mintemail.com','h8s.org','haltospam.com','hatespam.org','hidemail.de','hochsitze.com','hotpop.com','hulapla.de','ieatspam.eu','ieatspam.info','ihateyoualot.info','iheartspam.org','imails.info','inboxclean.com','inboxclean.org','incognitomail.com','incognitomail.net','incognitomail.org','insorg-mail.info','ipoo.org','irish2me.com','iwi.net','jetable.com','jetable.fr.nf','jetable.net','jetable.org','jnxjn.com','junk1e.com','kasmail.com','kaspop.com','keepmymail.com','killmail.com','killmail.net','kir.ch.tc','klassmaster.com','klassmaster.net','klzlk.com','kulturbetrieb.info','kurzepost.de','letthemeatspam.com','lhsdv.com','lifebyfood.com','link2mail.net','litedrop.com','lol.ovpn.to','lookugly.com','lopl.co.cc','lortemail.dk','lr78.com','m4ilweb.info','maboard.com','mail-temporaire.fr','mail.by','mail.mezimages.net','mail2rss.org','mail333.com','mail4trash.com','mailbidon.com','mailblocks.com','mailcatch.com','maileater.com','mailexpire.com','mailfreeonline.com','mailin8r.com','mailinater.com','mailinator.com','mailinator.net','mailinator2.com','mailincubator.com','mailme.ir','mailme.lv','mailmetrash.com','mailmoat.com','mailnator.com','mailnesia.com','mailnull.com','mailshell.com','mailsiphon.com','mailslite.com','mailzilla.com','mailzilla.org','mbx.cc','mega.zik.dj','meinspamschutz.de','meltmail.com','messagebeamer.de','mierdamail.com','mintemail.com','moburl.com','moncourrier.fr.nf','monemail.fr.nf','monmail.fr.nf','msa.minsmail.com','mt2009.com','mx0.wwwnew.eu','mycleaninbox.net','mypartyclip.de','myphantomemail.com','myspaceinc.com','myspaceinc.net','myspaceinc.org','myspacepimpedup.com','myspamless.com','mytrashmail.com','neomailbox.com','nepwk.com','nervmich.net','nervtmich.net','netmails.com','netmails.net','netzidiot.de','neverbox.com','no-spam.ws','nobulk.com','noclickemail.com','nogmailspam.info','nomail.xl.cx','nomail2me.com','nomorespamemails.com','nospam.ze.tc','nospam4.us','nospamfor.us','nospamthanks.info','notmailinator.com','nowmymail.com','nurfuerspam.de','nus.edu.sg','nwldx.com','objectmail.com','obobbo.com','oneoffemail.com','onewaymail.com','online.ms','oopi.org','ordinaryamerican.net','otherinbox.com','ourklips.com','outlawspam.com','ovpn.to','owlpic.com','pancakemail.com','pimpedupmyspace.com','pjjkp.com','politikerclub.de','poofy.org','pookmail.com','privacy.net','proxymail.eu','prtnx.com','punkass.com','PutThisInYourSpamDatabase.com','qq.com','quickinbox.com','rcpt.at','recode.me','recursor.net','regbypass.com','regbypass.comsafe-mail.net','rejectmail.com','rklips.com','rmqkr.net','rppkn.com','rtrtr.com','s0ny.net','safe-mail.net','safersignup.de','safetymail.info','safetypost.de','sandelf.de','saynotospams.com','selfdestructingmail.com','SendSpamHere.com','sharklasers.com','shiftmail.com','shitmail.me','shortmail.net','sibmail.com','skeefmail.com','slaskpost.se','slopsbox.com','smellfear.com','snakemail.com','sneakemail.com','sofimail.com','sofort-mail.de','sogetthis.com','soodonims.com','spam.la','spam.su','spamavert.com','spambob.com','spambob.net','spambob.org','spambog.com','spambog.de','spambog.ru','spambox.info','spambox.irishspringrealty.com','spambox.us','spamcannon.com','spamcannon.net','spamcero.com','spamcon.org','spamcorptastic.com','spamcowboy.com','spamcowboy.net','spamcowboy.org','spamday.com','spamex.com','spamfree24.com','spamfree24.de','spamfree24.eu','spamfree24.info','spamfree24.net','spamfree24.org','spamgourmet.com','spamgourmet.net','spamgourmet.org','SpamHereLots.com','SpamHerePlease.com','spamhole.com','spamify.com','spaminator.de','spamkill.info','spaml.com','spaml.de','spammotel.com','spamobox.com','spamoff.de','spamslicer.com','spamspot.com','spamthis.co.uk','spamthisplease.com','spamtrail.com','speed.1s.fr','supergreatmail.com','supermailer.jp','suremail.info','teewars.org','teleworm.com','tempalias.com','tempe-mail.com','tempemail.biz','tempemail.com','TempEMail.net','tempinbox.co.uk','tempinbox.com','tempmail.it','tempmail2.com','tempomail.fr','temporarily.de','temporarioemail.com.br','temporaryemail.net','temporaryforwarding.com','temporaryinbox.com','thanksnospam.info','thankyou2010.com','thisisnotmyrealemail.com','throwawayemailaddress.com','tilien.com','tmailinator.com','tradermail.info','trash-amil.com','trash-mail.at','trash-mail.com','trash-mail.de','trash2009.com','trashemail.de','trashmail.at','trashmail.com','trashmail.de','trashmail.me','trashmail.net','trashmail.org','trashmail.ws','trashmailer.com','trashymail.com','trashymail.net','trillianpro.com','turual.com','twinmail.de','tyldd.com','uggsrock.com','upliftnow.com','uplipht.com','venompen.com','veryrealemail.com','viditag.com','viewcastmedia.com','viewcastmedia.net','viewcastmedia.org','webm4il.info','wegwerfadresse.de','wegwerfemail.de','wegwerfmail.de','wegwerfmail.net','wegwerfmail.org','wetrainbayarea.com','wetrainbayarea.org','wh4f.org','whyspam.me','willselfdestruct.com','winemaven.info','wronghead.com','wuzup.net','wuzupmail.net','e4ward.com','gishpuppy.com','mailinator.com','wwwnew.eu','xagloo.com','xemaps.com','xents.com','xmaily.com','xoxy.net','yep.it','yogamaven.com','yopmail.com','yopmail.fr','yopmail.net','ypmail.webarnak.fr.eu.org','yuurok.com','zehnminutenmail.de','zippymail.info','zoaxe.com','zoemail.org','33mail.com','maildrop.cc','inboxalias.com','spam4.me','koszmail.pl','tagyourself.com','whatpaas.com','drdrb.com','emeil.in','azmeil.tk','mailfa.tk','inbax.tk','emeil.ir');
    $toarray = explode("@", $email);
    if(is_array($toarray) && count($toarray) == 2){
        $domain = $toarray[1];
        return array_search($domain, $blocked) > -1;
    }
    return true;
}
function verifyRequiredParams($required_fields) {
    $error = false;
    $error_fields = "";
    $request_params = $_REQUEST;
    // Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }

    if ($error) {
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response[ERROR] = true;
        $response[MESSAGE] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echoRespnse(200, $response);
        $app->stop();
    }
}

/**
 * Validating email address
 */
function validateEmail($email) {
    $app = \Slim\Slim::getInstance();
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response[ERROR] = true;
        $response[MESSAGE] = 'Email address is not valid';
        echoRespnse(200, $response);
        $app->stop();
    }
}

/** Echoing json response to client
 *
 * @param String $status_code Http response code
 * @param Int $response Json response
 */
function echoRespnse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);
    $response[VERSION] = getVersion();
    // setting response content type to json
    $app->contentType('application/json');
    header("Access-Control-Allow-Origin: *");
    echo json_encode($response);

}
function echoNonJSonRespnse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);

    // setting response content type to json
    $app->contentType('application/x-www-form-urlencoded');
    echo json_encode($response);
}
/** User Registration
 *
 * url - /wlogin. only used from web browser,
 * This will create a session and a cookie which will host the cookie
 * method - POST
 * params - name, email, password
 */


$app->get('/getversion', function() {
    $response = array();
    $response[ERROR] = false;
    $response[MESSAGE] = "OK";
    echoRespnse(200, $response);
});

$app->get('/getConstitutions', function() {
    $response = array();
    $db_main = new DbHandler();
    $list = array();
    $results =  $db_main->getConstitutions();
    if($results != null){
        while($constitution = $results->fetch_assoc()){
            $tmp = array();
            $tmp['constitution_name'] = utf8_encode($constitution['constitution_name']);
            $tmp['is_appliquable'] = $constitution['is_appliquable'];
            $tmp['titre_number'] = $constitution['vote_date'];
            $tmp['constitution_id'] = $constitution['constitution_pi'];
            $tmp['titre_count'] = $constitution['titre_count'];
            $tmp['article_count'] = $constitution['article_count'];
            $tmp['alinea_count'] = $constitution['alinea_count'];
            $tmp['vote_count'] = $constitution['vote_count'];
            $tmp['short_name'] = utf8_encode($constitution['short_name']);
            $tmp['constitution_url'] = $constitution['constitution_url'];
            $tmp['description'] = utf8_encode($constitution['description']);
            array_push($list,$tmp);
        }
        $response['data'] = $list;
    }else{
        $response[ERROR] = true;
        $response[MESSAGE] = "KO";
    }
    echoRespnse(200, $response);
});
//
function processTitresResults($results){
    $response = array();
    $titre_id = 0;
    $articleArray = array();
    $titres = array();
    $counter = 0;
    $isThereAtLeastOneElement = false;
    if($results){
            while($titre = $results->fetch_assoc()){
            $isThereAtLeastOneElement = true;
            $tmp = array();
            $currentId = $titre['pk_tit_id'];
            if($titre_id  === 0 || $currentId !== $titre_id ){
                if($titre_id > 0){
                    $titres[$counter]['articles'] = $articleArray;
                    $counter++;
                }
                $articleArray = array();
                $titre_id = $currentId;
                $tmp['titre_name'] = utf8_encode($titre['titre_name']);
                $tmp['titre_id'] = $titre['titre_pi'];
                $tmp['titre_number'] = $titre['titre_number'];
                $tmp['titre_url'] = $titre['titre_url'];
                array_push($titres,$tmp);
            }
            $article = array();
            $article['article_name'] = $titre['article_name'];
            $article['article_id'] = $titre['article_pi'];
            $article['article_number'] = $titre['article_number'];
            $article['article_version'] = $titre['article_version'];
            $article['article_description'] = utf8_encode($titre['article_description']);
            $article['article_url'] = utf8_encode($titre['article_url']);
            array_push($articleArray,$article);
            //array_push($list,$tmp);
        }
        if($isThereAtLeastOneElement){
            $titres[$counter]['articles'] = $articleArray;
        }
        $response['data'] = $titres;
        $response[ERROR] = false;
        $response[MESSAGE] = null;
    }else{
        $response['data'] = null;
        $response[ERROR] = true;
        $response[MESSAGE] = 'KO';
    }
    return $response;
}
$app->get('/getTitresFromConstitution/:id', function($constitution_id) {
    $response = array();
    if($constitution_id){
        //getArticlesFromTitre($titre_id)
        $db = new DbHandler();
        $results = $db->getAllTitresFromConstitution($constitution_id,false);
        $response = processTitresResults($results);
        echoRespnse(200,$response);
    }else{
        $response[ERROR] = true;
        $response[MESSAGE] = 'Missing ID';
        echoRespnse(200,$response);
    }
});

$app->get('/getAllVotes', function() {
    $time = time();
    $serve_from_cache = false;
    if(isset($_SESSION['getAllVotes_timout'])){
        if($time > $_SESSION['getAllVotes_timout'] + (5*60)){
            $_SESSION['getAllVotes_timout'] = $time;
        }else{
            $serve_from_cache = true;
        }
    }else{
        $_SESSION['getAllVotes_timout'] = $time;
    }

    $response = array();
    if(!$serve_from_cache){
        $db = new DbHandler();
        $results = $db->getAllVotes();
        $votes = array();
        $i = 0;
        while($vote = $results->fetch_assoc()){
            if($i == 0){
                $votes[YESVOTES_COUNT] = $vote['votes'];
            }else if($i == 1){
                $votes[NOVOTES_COUNT] = $vote['votes'];
            }else{
                $votes[A_VOTE_SANS_OPINION] = $vote['votes'];
            }
            $i ++;
        }
        $_SESSION['getAllVotes'] = $votes;
    }
    $data = array();
    $data[ENTITY] = $_SESSION['getAllVotes'];
    $data[ERROR] = false;
    $data[MESSAGE] = 'OK';
    $data[RESPONSE_STATUS] = 200;
    $response[DATA] = $data;
    $response[MESSAGE] = 'OK';
    $status = 200;
    echoRespnse(200,$response);
});



$app->get('/getTitresFromConstitutionByUrl/:id', function($constitution_url) {
    $response = array();
    if($constitution_url){
        //getArticlesFromTitre($titre_id)
        $db = new DbHandler();
        $results = $db->getAllTitresFromConstitutionByUrl($constitution_url);
        $response = processTitresResults($results);
        echoRespnse(200,$response);
    }else{
        $response[ERROR] = true;
        $response[MESSAGE] = 'Missing ID';
        echoRespnse(200,$response);
    }
});
$app->get('/getGlobalStat', function() {
    $status = array();
    $db = new DbHandler();
    $results = $db->getGlobalStat();
    $userStat = array();
    if($results !== null){
        while($stat = $results->fetch_assoc()){
            $tmp = array();
            $tmp[A_VOTE_OUI] = $stat['yes'];
            $tmp[A_VOTE_NON] = $stat['nop'];
            $tmp[N_A_PAS_VOTE] = $stat['void'];
            $tmp['title'] = 'Tous les votes exprimés';
            array_push($userStat,$tmp);
        }
        $data = array();
        $data[ENTITY] = $userStat;
        $data[ERROR] = false;
        $data[MESSAGE] = 'OK';
        $data[RESPONSE_STATUS] = 200;
        $response[DATA] = $data;
        $response[MESSAGE] = 'OK';
        $status = 200;
    }else{
        $status = 200;
        $response['data'] = $userStat;
        $response[ERROR] = true;
        $response[MESSAGE] = 'KO';
    }
    echoRespnse($status, $response);
});
$app->get('/getTitres', function() {


    $response = array();
    // $response[ERROR] = false;
    // $response[MESSAGE] = "OK";
    $db_main = new DbHandler();
    $list = array();
    $results =  $db_main->getAllTitres();
    if($results != null){
        while($titre = $results->fetch_assoc()){
            $tmp = array();
            $tmp['titre_name'] = utf8_encode($titre['titre_name']);
            $tmp['titre_id'] = $titre['titre_pi'];
            $tmp['titre_number'] = $titre['titre_number'];
            array_push($list,$tmp);
        }
        $response['data'] = $list;
    }else{
        $response[ERROR] = true;
        $response[MESSAGE] = "KO";
    }


    echoRespnse(200, $response);
});

function processArticles($results){
    $articles = array();
    $response = array();
    if($results){
        while($article = $results->fetch_assoc()){
            $tmp = array();
            $tmp['article_name'] = $article['article_name'];
            $tmp['article_id'] = $article['article_pi'];
            $tmp['article_number'] = $article['article_number'];
            $tmp['article_version'] = $article['article_version'];
            $tmp['alinea_count'] = $article['alinea_count'];
            $tmp['vote_count'] = $article['vote_count'];
            $tmp['proposition_count'] = $article['proposition_count'];
            array_push($articles,$tmp);
        }
        $response['data'] = $articles;
        $response[ERROR] = false;
        $response[MESSAGE] = null;
    }
}
//returns Articles By Titre public id
$app->get('/getArticlesByTitreByUrl/:id', function($titre_id) {
    $response = array();
    if($titre_id){
        //getArticlesFromTitre($titre_id)
        $db = new DbHandler();
        $results = $db->getArticlesByTitreByUrl($titre_id);
        $response = processArticles($results);
        echoRespnse(200,$response);
    }else{
        $response[ERROR] = true;
        $response[MESSAGE] = 'Missing ID';
        echoRespnse(200,$response);
    }
});
$app->get('/getArticlesByTitre/:id', function($titre_id) {
    $response = array();
    if($titre_id){
        //getArticlesFromTitre($titre_id)
        $db = new DbHandler();
        $results = $db->getArticlesFromTitre($titre_id,false);
        $response = processArticles($results);
        echoRespnse(200,$response);
    }else{
        $response[ERROR] = true;
        $response[MESSAGE] = 'Missing ID';
        echoRespnse(200,$response);
    }
});
function processAlineasResults ($results){
    $response = array();
    $data = array();
    $metadata = '';
    $alineas = array();
    if($results){
        while($alinea = $results->fetch_assoc()){
            $tmp = array();
            $tmp['alinea_text'] = utf8_encode($alinea['alinea_text']);
            $tmp['alinea_id'] = $alinea['alinea_pi'];
            $tmp['alinea_number'] = $alinea['alinea_number'];
            $tmp['alinea_version'] = $alinea['alinea_version'];
            $tmp[ALINEA_STATUS] = $alinea[ALINEA_STATUS];
            $tmp['yesvotes_count'] = $alinea['yesvotes_count'];
            $tmp[A_VOTE_SANS_OPINION] = $alinea['noopinionvotes_count'];
            $tmp['novotes_count'] = $alinea['novotes_count'];
            $tmp['proposition_count'] = $alinea['proposition_count'];
            if(isset($alinea['vote'])){
                $tmp['vote'] = $alinea['vote'];
            }
            if(isset($alinea['shorturl'])){
                $metadata = $alinea['shorturl'];
            }
            array_push($alineas,$tmp);
        }
        $data[ENTITY] = $alineas;
        $data['metadata'] = $metadata;
        $data[ERROR] = false;
        $data[MESSAGE] = 'OK';
        $response[DATA] = $data;
    }
    return $response;
}
$app->get('/getAllAlineasByArticleId/:id', function($article_id) {
    $response = array();
    if($article_id){
        //getArticlesFromTitre($titre_id)
        $db = new DbHandler();
        $results = $db->getAllAlineasByArticleId($article_id,null,false);
        $response = processAlineasResults($results);
        echoRespnse(200,$response);
    }else{
        $data[ENTITY] = $alineas;
        $data[ERROR] = true;
        $data[MESSAGE] = 'Missing ID';
        $response[DATA] = $data;
        echoRespnse(200,$response);
    }
});
$app->get('/getAllAlineasByArticleUrl/:articleUrl/:constitution', function($articleUrl,$constitution) {
    $user_id = null;
    if(isset($_SESSION['AuthenticationKey'])){
        $user_id = $_SESSION['user_id'];
    }
    $response = array();
    if($articleUrl && $constitution){
        $db = new DbHandler();
        $results = $db->getAllAlineasByArticleId($articleUrl,$user_id,true,$constitution);
        $response = processAlineasResults($results);
        echoRespnse(200,$response);
    }else{
        $data[ENTITY] = $alineas;
        $data[ERROR] = true;
        $data[MESSAGE] = 'Missing ID';
        $response[DATA] = $data;
        echoRespnse(200,$response);
    }
    //

});
$app->get('/getShortUrlForArticle/:articleUrl/:constitution', function($articleUrl,$constitution) {
    $longUrl = "https://www.laconstituante.fr/$articleUrl/$constitution";
    $apiKey = 'AIzaSyB8-krmmmil19dxZ3qpqLGBhn9eijGJV_k';
    // You can get API key here : Login to google and
    // go to http://code.google.com/apis/console/
    // Find API key under credentials under APIs & auth.
    // You will need to do necessary things to get key there. :)
    // Watch video below.
    $result = array();
    // *** No need to modify any of the code line below. ***
    $postData = array('longUrl' => $longUrl, 'key' => $apiKey);
    $jsonData = json_encode($postData);
    $curlObj = curl_init();
    curl_setopt($curlObj, CURLOPT_URL, 'https://www.googleapis.com/urlshortener/v1/url?key='.$apiKey);
    curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curlObj, CURLOPT_HEADER, 0);
    curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-type:application/json'));
    curl_setopt($curlObj, CURLOPT_POST, 1);
    curl_setopt($curlObj, CURLOPT_POSTFIELDS, $jsonData);
    $response = curl_exec($curlObj);
    $json = json_decode($response);
    curl_close($curlObj);
    $shortUrl = $json->id;
    if($articleUrl && $constitution && $shortUrl){
        $db = new DbHandler();
        $db->setShortUrlForArticle($articleUrl,$constitution,$shortUrl);
        $data = array();
        $data[ENTITY] = $shortUrl;
        $data[ERROR] = false;
        $data[MESSAGE] = 'OK';
        $result[DATA] = $data;
        $status = 201;
        echoRespnse($status,$result);
    }else{
        $data[ENTITY] = null;
        $data[ERROR] = true;
        $data[MESSAGE] = 'Something went wrong';
        $result[DATA] = $data;
        echoRespnse(200,$result);
    }

});

$app->get('/getAllAlineasByArticleIdAuth/:id','authenticate', function($article_id) use ($app) {
    $response = array();
    if($article_id){
        global $user_id;
        $db = new DbHandler();
        $results = $db->getAllAlineasByArticleId($article_id,$user_id,false);
        $alineas = array();
        $metadata = '';
        while($alinea = $results->fetch_assoc()){
            $tmp = array();
            $tmp[ALINEA_TEXT] = utf8_encode($alinea['alinea_text']);
            $tmp[ALINEA_ID] = $alinea['alinea_pi'];
            $tmp[ALINEA_NUMBER] = $alinea['alinea_number'];
            $tmp[ALINEA_VERSION] = $alinea['alinea_version'];
            $tmp[ALINEA_STATUS] = $alinea[ALINEA_STATUS];
            $tmp[YESVOTES_COUNT] = $alinea['yesvotes_count'];
            $tmp[NOVOTES_COUNT] = $alinea['novotes_count'];
            $tmp[VOTE] = $alinea['vote'];
            $tmp[PROPOSITION_COUNT] = $alinea['proposition_count'];
            if(isset($alinea['shorturl'])){
                $metadata = $alinea['shorturl'];
            }
            array_push($alineas,$tmp);
        }
        $data = array();
        $data[ENTITY] = $alineas;
        $data['metadata'] = $metadata;
        $data[ERROR] = false;
        $data[MESSAGE] = 'OK';
        $response[DATA] = $data;
        $status = 201;
        echoRespnse(200,$response);
    }else{
        $data = array();
        $data[ENTITY] = null;
        $data[ERROR] = true;
        $data[MESSAGE] = 'Missing ID';
        $response[DATA] = $data;
        echoRespnse(200,$response);
    }
});
//
$app->get('/get6RepublicEligibility','authenticate', function() use ($app) {
    $response = array();
    global $user_id;
    $db = new DbHandler();
    $results = $db->get6RepublicEligibility($user_id);
    if ($results)
    {
    	$resultat = array();
        while($alinea = $results->fetch_assoc()){
            $resultat[TOTALALINEA] = $alinea['totalAlinea'];
            $resultat[TOTALVOTES] = $alinea['totalvotes'];

        }
        $data = array();
        $data[ENTITY] = $resultat;
        $data[ERROR] = false;
        $data[MESSAGE] = 'OK';
        $response[DATA] = $data;
        $status = 201;
    }else{
        $data = array();
        $data[ENTITY] = null;
        $data[ERROR] = true;
        $data[MESSAGE] = 'KO';
        $response[DATA] = $data;
        $status = 200;
    }
    echoRespnse($status,$response);

});
//
$app->get('/getNextAlineas','authenticate', function() use ($app) {
    $response = array();
    global $user_id;
    $db = new DbHandler();
    $results = $db->getNextAlineas($user_id);
    if ($results)
    {
    	$titres = array();
        $currentTitle = 0;
        $currentArticle = 0;
        while($row = $results->fetch_assoc()){
            $alinea = array();
            $temp_article = array();
            $temp_titre = array();
            $alinea[ALINEA_TEXT] = utf8_encode($row['alinea_text']);
            $alinea[ALINEA_ID] = $row['alinea_pi'];
            $alinea[ALINEA_NUMBER] = $row['alinea_number'];
            $alinea[ALINEA_VERSION] = $row['alinea_version'];
            $alinea[ALINEA_STATUS] = $row[ALINEA_STATUS];
            $alinea[YESVOTES_COUNT] = $row['yesvotes_count'];
            $alinea[NOVOTES_COUNT] = $row['novotes_count'];
            $alinea[VOTE] = null;
            $temp_article['article_name'] = $row['article_name'];
            $temp_article['article_id'] = $row['article_pi'];
            $temp_article['article_number'] = $row['article_number'];
            $temp_article['article_version'] = $row['article_version'];
            $temp_titre['titre_name'] = utf8_encode($row['titre_name']);
            $temp_titre['titre_id'] = $row['titre_pi'];
            $temp_titre['titre_number'] = $row['titre_number'];


            if($currentTitle !== intval($row['pk_tit_id'])){
                    $temp_article['alineas'] = array();
                    array_push($temp_article['alineas'],$alinea);
                    $temp_titre['articles'] = array();
                    array_push($temp_titre['articles'],$temp_article);
                    array_push($titres,$temp_titre);
                    $currentTitle = intval($row['pk_tit_id']);
            }else{
                if($currentArticle !== intval($row['pk_art_id'])){
                    $temp_article['alineas'] = array();
                    array_push($temp_article['alineas'],$alinea);
                    array_push($titres[count($titres) -1]['articles'],$temp_article);
                    $currentArticle = intval($row['pk_art_id']);
                }else{
                    array_push($titres[count($titres) -1]['articles'][count($titres[count($titres) -1]['articles']) -1]['alineas'],$alinea);
                }
            }
        }
        $data = array();
        $data[ENTITY] = $titres;
        $data[ERROR] = false;
        $data[MESSAGE] = 'OK';
        $response[DATA] = $data;
        $status = 201;
    }else{
        $data = array();
        $data[ENTITY] = null;
        $data[ERROR] = true;
        $data[MESSAGE] = 'KO';
        $response[DATA] = $data;
        $status = 200;
    }
    echoRespnse($status,$response);

});
$app->get('/getPropositionsFromAlinea/:id', function($alinea_id) {


    $response = array();
    if($alinea_id){
        //getArticlesFromTitre($titre_id)
        $db = new DbHandler();
        $results = $db->getPropositionsFromAlinea($alinea_id);
        $propositions = array();
        $tmp = array();
        while($proposition = $results->fetch_assoc()){
            $tmp[PROPOSITION_TEXT] = utf8_encode($proposition[PROPOSITION_TEXT]);
            $tmp[ALINEA_ID] = $alinea_id;
            $tmp[PROPOSITION_NUMBER] = $proposition[PROPOSITION_NUMBER];
            $tmp[YESVOTES_COUNT] = $proposition[YESVOTES_COUNT];
            $tmp[NOVOTES_COUNT] = $proposition[NOVOTES_COUNT];
            $tmp[REPORTED_COUNT] = $proposition[REPORTED_COUNT];
            $tmp[VOTE] = $proposition[VOTE];
            array_push($propositions,$tmp);
        }
        $data = array();

        $data[ENTITY] = $propositions;
        $data[ERROR] = false;
        $data[MESSAGE] = 'OK';
        $response[DATA] = $data;
        echoRespnse(200,$response);
    }else{
        $response[ERROR] = true;
        $response[MESSAGE] = 'Missing ID';
        echoRespnse(200,$response);
    }
});
//
$app->get('/getPropositionsFromAlineaAuth/:id','authenticate', function($alinea_id)  use ($app) {
    $response = array();
    global $user_id;
    if($alinea_id && $user_id){
        //getArticlesFromTitre($titre_id)
        $db = new DbHandler();
        $results = $db->getPropositionsFromAlineaAuth($alinea_id,$user_id);
        $propositions = array();
        $tmp = array();
        while($proposition = $results->fetch_assoc()){
            $tmp[PROPOSITION_TEXT] = utf8_encode($proposition[PROPOSITION_TEXT]);
            $tmp[ALINEA_ID] = $alinea_id;
            $tmp[PROPOSITION_NUMBER] = $proposition[PROPOSITION_NUMBER];
            $tmp[PROPOSITION_ID] = $proposition['proposition_pi'];
            $tmp[YESVOTES_COUNT] = $proposition[YESVOTES_COUNT];
            $tmp[NOVOTES_COUNT] = $proposition[NOVOTES_COUNT];
            $tmp[REPORTED_COUNT] = $proposition[REPORTED_COUNT];
            $tmp[VOTE] = $proposition[VOTE];
            array_push($propositions,$tmp);
        }
        $data = array();

        $data[ENTITY] = $propositions;
        $data[ERROR] = false;
        $data[MESSAGE] = 'OK';
        $response[DATA] = $data;
        echoRespnse(200,$response);
    }else{
        $response[ERROR] = true;
        $response[MESSAGE] = 'Missing ID';
        echoRespnse(200,$response);
    }
});
$app->post('/createProposition','authenticate', function() use ($app) {
    // check for required params
    $response = array();
    global $user_id;
    verifyRequiredParams(array(ALINEA_ID,PROPOSITION_TEXT));
    $response[ERROR] = false;
    $response[MESSAGE] = "OK";
    $alinea_pi = $app->request->post(ALINEA_ID);
    $proposition_text = utf8_encode($app->request->post(PROPOSITION_TEXT));
    $db = new DbHandler();
    $results = $db->createProposition($user_id,$alinea_pi,$proposition_text);
    $propositions = array();
    if($results){
         $data = array();
         while($proposition = $results->fetch_assoc()){
             if(isset($proposition['alreadyExists'])){
                 $data[MESSAGE]= 'alreadyExists';
                 $propositions = null;
                 $data[ERROR]= true;
             }else{
                $tmp = array();
                $tmp[PROPOSITION_TEXT] = utf8_encode($proposition['proposition_text']);
                $tmp[ALINEA_ID] = $alinea_pi;
                $tmp[PROPOSITION_NUMBER] = $proposition[PROPOSITION_NUMBER];
                array_push($propositions,$tmp);
                $data[ERROR]= false;
             }
         }
         $data[ENTITY]= $propositions;

         $response['data'] = $data;
         $status = 201;
    }else{
        $status = 401;
        $response['data'] = null;
        $response[ERROR] = true;
        $response[MESSAGE] = 'KO';
    }
    echoRespnse($status, $response);
});
//voteProposition
$app->post('/voteProposition','authenticate', function() use ($app) {
    global $user_id;
    verifyRequiredParams(array(PROPOSITION_ID,VOTE));
    $response = array();
    $proposition_ali = $app->request->post(PROPOSITION_ID);
    $alinea_id =  $app->request->post(ALINEA_ID);
    $vote = intval($app->request->post(VOTE));
    $db = new DbHandler();
    $results = $db->voteProposition($user_id,$proposition_ali,$vote);
    if($results !== null){
        $propositionUpdated = array();
        while($proposition = $results->fetch_assoc()){
            $propositionUpdated[PROPOSITION_TEXT] = utf8_encode($proposition[PROPOSITION_TEXT]);
            $propositionUpdated[ALINEA_ID] = $alinea_id;
            $propositionUpdated[PROPOSITION_NUMBER] = $proposition[PROPOSITION_NUMBER];
            $propositionUpdated[PROPOSITION_ID] = $proposition['proposition_pi'];
            $propositionUpdated[YESVOTES_COUNT] = $proposition[YESVOTES_COUNT];
            $propositionUpdated[NOVOTES_COUNT] = $proposition[NOVOTES_COUNT];
            $propositionUpdated[REPORTED_COUNT] = $proposition[REPORTED_COUNT];
            $propositionUpdated[VOTE] = $proposition[VOTE];
        }
        $data = array();
        $data[ENTITY] = $propositionUpdated;
        $data[ERROR] = false;
        $data[MESSAGE] = 'OK';
        $response[DATA] = $data;
        $status = 201;
    }else{
        $data = array();
        $data[ENTITY] = null;
        $data[ERROR] = true;
        $data[MESSAGE] = 'KO';
        $response[DATA] = $data;
        $status = 200;
    }
    echoRespnse($status, $response);
});
$app->post('/voteAlinea','authenticate', function() use ($app) {
    global $user_id;
    verifyRequiredParams(array(ALINEA_ID,VOTE));
    $response = array();
    $alinea_pi = $app->request->post(ALINEA_ID);
    $vote = $app->request->post(VOTE);
    $db = new DbHandler();
    $results = $db->voteAlinea($user_id,$alinea_pi,$vote);
    if($results !== null){
        $alineas = array();
        while($alinea = $results->fetch_assoc()){
            //$tmp = array();
            $alineas[ALINEA_TEXT] = utf8_encode($alinea['alinea_text']);
            $alineas[ALINEA_ID] = $alinea['alinea_pi'];
            $alineas[ALINEA_NUMBER] = $alinea['alinea_number'];
            $alineas[ALINEA_VERSION] = $alinea['alinea_version'];
            $alineas[ALINEA_STATUS] = $alinea[ALINEA_STATUS];
            $alineas[YESVOTES_COUNT] = $alinea['yesvotes_count'];
            $alineas[A_VOTE_SANS_OPINION] = $alinea['noopinionvotes_count'];
            $alineas[NOVOTES_COUNT] = $alinea['novotes_count'];
            $alineas[VOTE] = $alinea['vote'];
            $alineas[PROPOSITION_COUNT] = $alinea['proposition_count'];
            //array_push($alineas,$tmp);
        }
        $data = array();
        $data[ENTITY] = $alineas;
        $data[ERROR] = false;
        $data[MESSAGE] = 'OK';
        $response[DATA] = $data;
        $status = 201;
    }else{
        $data = array();
        $data[ENTITY] = null;
        $data[ERROR] = true;
        $data[MESSAGE] = 'KO';
        $response[DATA] = $data;
        $status = 200;
    }
    echoRespnse($status, $response);
});
$app->get('/getUserStat','authenticate', function() use ($app) {
    // check for required params
    global $user_id;
    $status = array();
    $db = new DbHandler();
    $results = $db->getUserStat($user_id);
    $userStat = array();
    if($results !== null){
        while($stat = $results->fetch_assoc()){
            $tmp = array();
            $tmp[A_VOTE_OUI] = $stat['yes'];
            $tmp[A_VOTE_NON] = $stat['nop'];
            $tmp[A_VOTE_SANS_OPINION] = $stat['nil'];
            $tmp[N_A_PAS_VOTE] = $stat['void'];
            array_push($userStat,$tmp);
        }
        $data = array();
        $data[ENTITY] = $userStat;
        $data[ERROR] = false;
        $data[MESSAGE] = 'OK';
        $data[RESPONSE_STATUS] = 200;
        $response[DATA] = $data;
        $response[MESSAGE] = 'OK';
        $status = 200;
    }else{
        $status = 200;
        $response['data'] = $userStat;
        $response[ERROR] = true;
        $response[MESSAGE] = 'KO';
    }
    echoRespnse($status, $response);
});
$app->get('/getUserStatTitres','authenticate', function() use ($app) {
    // check for required params
    global $user_id;
    $status = array();
    $db = new DbHandler();
    if(!$user_id){
        return;
    }
    $results = $db->getUserStatTitres($user_id);
    $userStat = array();
    if($results !== null){
        while($stat = $results->fetch_assoc()){
            $tmp = array();
            $tmp[TITRE_NAME] = utf8_encode($stat[TITRE_NAME]);
            $tmp[ALLVOTES_COUNT] = $stat[ALLVOTES_COUNT];
            $tmp[TOTALS] = $stat[TOTALS];
            array_push($userStat,$tmp);
        }
        $data = array();
        $data[ENTITY] = $userStat;
        $data[ERROR] = false;
        $data[MESSAGE] = 'OK';
        $data[RESPONSE_STATUS] = 200;
        $response[DATA] = $data;
        $response[MESSAGE] = 'OK';
        $status = 200;
    }else{
        $data = array();
        $data[ENTITY] = null;
        $data[ERROR] = true;
        $data[MESSAGE] = 'KO';
        $status = 200;
        $response['data'] = $data;
        $response[ERROR] = true;
        $response[MESSAGE] = 'KO';
    }
    echoRespnse($status, $response);
});
//
$app->post('/createUser', function() use ($app) {
    verifyRequiredParams(array(FIRSTNAME,LASTNAME,PASSWORD,EMAIL,DOB,ISFRENCH,CIVILITE));
    $response = array();
    $response[ERROR] = false;
    $response[MESSAGE] = "OK";
    $first =utf8_encode( $app->request->post(FIRSTNAME));
    $last = utf8_encode($app->request->post(LASTNAME));
    $password = utf8_encode($app->request->post(PASSWORD));
    $email = utf8_encode($app->request->post(EMAIL));
    $dob = $app->request->post(DOB);
    $isFrench = $app->request->post(ISFRENCH);
    $civilite = $app->request->post(CIVILITE);
    $codepostal = $app->request->post(CODEPOSTAL);
    $coountry = $app->request->post(COUNTRY);
    $user_ip = getUserIP();
    $user_agent_hash = md5(getUserAgent());
    $email_token = getEmailToken();
    if(isDisposableEmail($email)){
        $data = array();
        $data[ERROR] = true;
        $data[MESSAGE] = utf8_encode("Cette address email n'est pas acceptée. Merci d'utiliser une adresse email réellle. Votre adress email est en sécurité avec nous!");
        $status = 200;
        $data[RESPONSE_DATA] = null;
        $data[RESPONSE_STATUS] = 101;
        $response[DATA] = $data;
        echoRespnse($status, $response);
    }else{
        $db = new DbHandler();
        $results = $db->create_user($first,$last,$email,$password,$dob,$civilite,$isFrench,$codepostal,$user_ip,$user_agent_hash,$email_token,$coountry);
        $user = array();
        $status = 500;
        if($results !== null){
             while($usrTmp = $results->fetch_assoc()){
                 if(isset($usrTmp['emailExists'])){
                     $data = array();
                     $data[ERROR] = true;
                     $data[MESSAGE] = utf8_encode("Il existe un compte avec cette adresse email. Merci de s'identifier");
                     $response[RESPONSE_DATA] = null;
                     $status = 200;
                     $response[DATA] = $data;
                 }else{
                     require_once '../include/notifications.php';
                     sendSubscriptionEmail($app->request->post(FIRSTNAME),$email,$email_token,'La constituante');
                     $user['firstname'] = $usrTmp['firstname'];
                     $user['lastname'] = $usrTmp['lastname'];
                     $user['email'] = $usrTmp['email'];
                     $user['dateofbirth'] = $usrTmp['dateofbirth'];
                     $user['isfrench'] = $usrTmp['isfrench'];
                     $user['code_postal'] = $usrTmp['code_postal'];
                     $user['user_id'] = $usrTmp['user_pi'];
                     $user['civilite'] = $usrTmp['civilite'];
                     $status = 201;
                     $data = array();
                     $data[RESPONSE_STATUS] = 201;
                     $_SESSION['has_created_account'] = true;
                     $data[RESPONSE_DATA] = $user;
                     $data[ERROR] = false;
                     $data[MESSAGE] = 'OK';
                     $response[DATA] = $data;
                 }
            }

        }else{
            $status = 200;
            $data[RESPONSE_STATUS] = 102;
            $data = array();
            $data[RESPONSE_DATA] = null;
            $data[ERROR] = true;
            $data[MESSAGE] =utf8_encode("Des opérations de maintenance sur le site sont en cours. Merci de recommencer un peu plus tard.");
            $response[DATA] = $data;
        }
        echoRespnse($status, $response);
    }

});
//
$app->post('/loginUser', function() use ($app) {
    // check for required params
    verifyRequiredParams(array(EMAIL,PASSWORD,IS_PERSISTENT));
    $response = array();
    $response[ERROR] = false;
    $response[MESSAGE] = "OK";
    $password =utf8_encode( $app->request->post(PASSWORD));
    $email = utf8_encode($app->request->post(EMAIL));
    $isPersistent = intval($app->request->post(IS_PERSISTENT));
    $data = array();
    if($password && $email){
        $db = new DbHandler();
        $results = $db->loginUser($email,$password,$isPersistent);
        $user = array();
        $status = 500;
        if($results !== null && !is_string($results)){
            while($usrTmp = $results->fetch_assoc()){
                if(isset($usrTmp['not_found'])){
                    $status = 200;
                    $response[ERROR] = true;
                    $response[MESSAGE] = 'Code de confirmation est incorrecte';
                }else{
                    $user['api_token'] = $usrTmp['api_key'];
                    $user['is_persistent'] = $usrTmp['is_persistent'];
                    $user['LoginTime'] = $usrTmp['aut_created_at'];
                    $status = 201;

                    $data[RESPONSE_DATA] = $user;
                    $data[ERROR] = false;
                    $data[MESSAGE] = 'OK';
                    $response[DATA] = $data;
                }
            }

        }else{
            if(is_string($results)){
                switch($results){
                    case 'FALSE 144':
                        $data[ENTITY] = null;
                        $data[MESSAGE] = 'Le mot de passe ou votre adresse email est incorrete. Veuillez verifier les informations saisies.';
                        $data[ERROR] = true;

                }
            }else{
                $data[ENTITY] = null;
                $data[MESSAGE] = 'Le mot de passe ou votre adresse email est incorrete. Veuillez verifier les informations saisies.';
                $data[ERROR] = true;
            }
            $status = 200;
            $response[DATA] = $data;

        }
        echoRespnse($status, $response);
    }

});

$app->post('/confirmEmail', function() use ($app) {
    // check for required params
    verifyRequiredParams(array(EMAIL_TOKEN,USRID));
    $response = array();
    $response[ERROR] = false;
    $response[MESSAGE] = "OK";
    $token =utf8_encode( $app->request->post(EMAIL_TOKEN));
    $user_id = utf8_encode($app->request->post(USRID));

    if($user_id){
        $db = new DbHandler();
        $results = $db->confirmEmail($token,$user_id);
        $user = array();
        $status = 500;
        if($results !== null){
            while($usrTmp = $results->fetch_assoc()){
                if(isset($usrTmp['not_found'])){
                    $status = 200;
                    $response[ERROR] = true;
                    $response[MESSAGE] = 'Code de confirmation est incorrecte';
                }else{
                    $user['api_token'] = $usrTmp['api_key'];
                    $user['is_persistent'] = $usrTmp['is_persistent'];
                    $status = 201;
                    $data = array();
                    $data[RESPONSE_DATA] = $user;
                    $data[ERROR] = false;
                    $data[MESSAGE] = 'OK';
                    $response[DATA] = $data;
                }
            }

        }else{
            $status = 200;
            $response['data'] = null;
            $response[ERROR] = true;
            $response[MESSAGE] = 'KO';
        }
        echoRespnse($status, $response);
    }

});

//
$app->post('/contactUsMobile', function() use ($app) {
    // check for required params
    verifyRequiredParams(array(EMAIL,SUBJECT,MESSAGE));
    $response = array();
    $data = array();
    $data[ERROR] = false;
    $data[MESSAGE] = "OK";
    $data[ENTITY]= null;
    $email = utf8_encode( $app->request->post(EMAIL));
    $subject =utf8_encode( $app->request->post(SUBJECT));
    $message = utf8_encode( $app->request->post(MESSAGE));

    sendContactEmail($email,$subject,$message);

    $response[DATA] = $data;
    echoRespnse(200,$response);
});

$app->post('/contactUs', function() use ($app) {
    // check for required params
    verifyRequiredParams(array(EMAIL,SUBJECT,MESSAGE,CAPTCHA));
    $response = array();
    $data = array();
    $data[ERROR] = false;
    $data[MESSAGE] = "OK";
    $data[ENTITY]= null;
    $email = utf8_encode( $app->request->post(EMAIL));
    $subject =utf8_encode( $app->request->post(SUBJECT));
    $message = utf8_encode( $app->request->post(MESSAGE));
    $captcha = utf8_encode($app->request->post(CAPTCHA));
    if(isset($_SESSION['contact-attemps'])){
        if($_SESSION['contact-attemps'] > 5){
            return;
        }else{
            $_SESSION['contact-attemps'] ++;
        }
    }else{
        $_SESSION['contact-attemps'] = 1;
    }
    if(isset($_SESSION['captcha']) && $_SESSION['captcha'] === $captcha){
        sendContactEmail($email,$subject,$message);
    }else{
        $data[ERROR] = true;
        $data[MESSAGE] = WRONG_CAPTCHA;
    }
    $response[DATA] = $data;
    echoRespnse(200,$response);
});


function getFacebookUserIDFromAccessToken($access_token){
    $appSecretProof = hash_hmac('sha256', $access_token, 'b2ab7dda503ca458b1f396987ad0962a');
    $url = 'https://graph.facebook.com/v2.6/me?access_token='.$access_token.'&appsecret_proof='.$appSecretProof;
    try{
        $curl = curl_init();
        if(FALSE === $curl)
            throw new Exception('Failed to initialise curl');
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($curl);
        if(FALSE === $content)
            throw new Exception(curl_error($curl), curl_errno($curl));

        curl_close($curl);
        return json_decode($content);
    } catch (Exception $ex) {
        sendErrorEmail(sprintf('Failed to validate Access token with error #%d: %s',$ex->getCode(), $ex->getMessage()));
        return NULL;
    }
}

$app->post('/fbquicklogin', function() use ($app) {
    // check for required params
    verifyRequiredParams(array(USER_FACEBOOK_ID, FACEBOOK_ACCESS_TOKEN));
    $response = array();
    $response[ERROR] = false;
    $response[MESSAGE] = "OK";
    $access_token = $app->request->post(FACEBOOK_ACCESS_TOKEN);
    $fid  = $app->request->post(USER_FACEBOOK_ID);
    $verifiedUser = getFacebookUserIDFromAccessToken($access_token);
    if(NULL !== $verifiedUser && property_exists($verifiedUser,'id') && isset($verifiedUser->id) && $fid == $verifiedUser->id){
        $db_main = new MainDbHandler();
        $api_key = $db_main->getApiKeyByFacebookID($fid);
        if(NULL != $api_key){
           $data = array();
            $data[TOKEN] = $api_key['api_key'];
            $data[COUNTRY] = $api_key['country_name'];
            $data[CURRENCY] = $api_key['currency_sign'];
            $data[CURRENCYNAME] = $api_key['currency_name'];
            $data[COUNTRYCODE] = $api_key['country_code'];
           $response[DATA] = $data;
        }else{
           $response[DATA] = null;
           $response[MESSAGE] = "UNSUBSCRIBE";
        }
    }else{
        $response[ERROR] = true;
        $response[MESSAGE] = "INVALID_FACEBOOK_ID";
    }
    echoRespnse(200, $response);
});


$app->post('/flogin', function() use ($app) {
    // check for required params
    verifyRequiredParams(array(EMAIL, USER_FACEBOOK_ID,LASTNAME,FIRSTNAME,GENDER,BIRTHDAY,HOMETOWN,COUNTRY,WORLDREGION,FACEBOOK_ACCESS_TOKEN));
    $status = array();
    $status[ERROR] = false;
    $status[MESSAGE] = null;
    $data = array();
    $response = array();
    $email = $app->request->post(EMAIL);
    $fid = $app->request->post(USER_FACEBOOK_ID);
    $lastName = $app->request->post(LASTNAME);
    $firstName = $app->request->post(FIRSTNAME);
    $birthday =  $app->request->post(BIRTHDAY);
    $gender =  $app->request->post(GENDER);
    $facebookFriendsNumber = $app->request->post(FACEBOOKFRIENDSNUMBER);
    $town = $app->request->post(HOMETOWN);
    $country = $app->request->post(COUNTRY);
    $region = $app->request->post(WORLDREGION);
    $phone = $app->request->post(PHONE);
    if(!$facebookFriendsNumber){
        $facebookFriendsNumber = 0;
    }
    $facebook_Mutual_friends = $app->request->post(FACEBOOK_MUTUAL_FRIENDS);
    if(!$facebook_Mutual_friends){
        $facebook_Mutual_friends = '';
    }
    $access_token = $app->request->post(FACEBOOK_ACCESS_TOKEN);
    $verifiedUser = getFacebookUserIDFromAccessToken($access_token);
    if(NULL !== $verifiedUser && property_exists($verifiedUser,'id') && isset($verifiedUser->id) ){
        if($fid != $verifiedUser->id){
            $response[ERROR] = true;
            $idFromFacebook = $verifiedUser->id;
            $response[MESSAGE] = "INVALID_FACEBOOK_ID";
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time | flogin | Unable to validate User | Browser sent fid : $fid\nFacebook sent the Id : $idFromFacebook \n",3,ERROR_LOG_PATH);
            echoRespnse(200, $response);
            $app->stop();
        }
    }
    if(!isValidCountry($region, $country)){
        $response[ERROR] = true;
        $response[MESSAGE] = "COUNTRY_REGION_INCONSISTENT";
        $time = date("Y-m-d H:i:s",time() + 61200 );
        error_log("$time | flogin | Unable to validate region | Region:$region, Country:$country \n",3,ERROR_LOG_PATH);
        echoRespnse(200, $response);
        $app->stop();
    }else if($region === 'tp'){
        if($country == 'New Zealand')
            $region = 'nz';
        else if($country == 'Australia')
            $region = 'au';
        else if($country == 'New Caledonia')
            $region = 'nc';
        else{
            $response[ERROR] = true;
            $response[MESSAGE] = "COUNTRY_REGION_INCONSISTENT";
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time | flogin | Unable to validate region | Region:$region, Country:$country \n",3,ERROR_LOG_PATH);
            echoRespnse(200, $response);
            $app->stop();
        }
    }
    $db = new MainDbHandler();
    $user_details = null;
    if($fid !== "" ){
        $user_details = $db->getApiKeyByFacebookID($fid);
        if($user_details['pk_usr_id'] > 0 && $user_details['target_usr_id'] > 0 && $user_details['region_code'] !== ''){
            $db2 = new DbHandler($user_details['region_code']);
            $db2->updateUserFromFb($user_details['target_usr_id'],$lastName,  $firstName, $email,$facebookFriendsNumber,$facebook_Mutual_friends);
            $data[TOKEN] = $user_details['api_key'];
            $data[COUNTRY] = $user_details['country_name'];
            $data[CURRENCY] = $user_details['currency_sign'];
            $data[CURRENCYNAME] = $user_details['currency_name'];
            $data[COUNTRYCODE] = $user_details['country_code'];
            $response[RESPONSE_DATA] = $data;
            $response[RESPONSE_STATUS] = $status;
            $response[RESPONSE_NOTIFICATION] = null;
            echoRespnse(201, $response);
        }else{
            validateEmail($email);
            $picture = 'https://graph.facebook.com/'.$fid.'/picture?type=square&width=300';
            $db2 = new DbHandler($region);
            $user_id = $db2->createTargetUser($lastName, $firstName,$email, $gender,$phone, $birthday,$town,$country,$facebookFriendsNumber,$facebook_Mutual_friends,$picture);
            if($user_id > 0){
                $terms_version = intval(ROADMATE_TERMS_VERSION);
                $password = null;
                $api_key = $db->createUser($fid,$user_id, $email,$access_token, $password, $terms_version,$region,$country);
                if($api_key != ''){
                    $user_details = $db->getApiKeyByFacebookID($fid);

                    $data[TOKEN] = $user_details['api_key'];
                    $data[COUNTRY] = $user_details['country_name'];
                    $data[CURRENCY] = $user_details['currency_sign'];
                    $data[CURRENCYNAME] = $user_details['currency_name'];
                    $data[COUNTRYCODE] = $user_details['country_code'];
                    $response[RESPONSE_DATA] = $data;
                    $response[RESPONSE_STATUS] = $status;
                    $response[RESPONSE_NOTIFICATION] = null;
                    echoRespnse(201, $response);
                }else{
                    //sendErrorEmail("createUserFromFb($fid,$user_id,$email,$access_token,$region);");
                    $response[RESPONSE_DATA] = null;
                    $status[ERROR] = true;
                    $status['DB'] = 'Main';
                    //$status[MESSAGE] = "CALL rmdbp_createUser($user_id,$fid,$email,$api_key,hash_password,$access_token, $terms_version,$country)";
                    $response[RESPONSE_STATUS] = $status;
                    $response[RESPONSE_NOTIFICATION] = null;
                    echoRespnse(200, $response);
                }

            }else{
                //mail('mehdi.tanouti@gmail.com','Facebook creation error', "createUserFromFb($fid, $lastName, $firstName,$email, , 0,$gender, $birthday,$picture)",'');
                $response[RESPONSE_DATA] = null;
                $status[ERROR] = true;
                $status['DB'] = 'Target';
                //$status[MESSAGE] = "CALL rmdbp_createUser($lastName, $firstName,$email, $gender,$phone, $birthday,$town,$country,$facebookFriendsNumber,$facebook_Mutual_friends,$picture)";
                $response[RESPONSE_STATUS] = $status;
                $response[RESPONSE_NOTIFICATION] = null;
                echoRespnse(200, $response);
            }

        }
    }else{
       $response[RESPONSE_DATA] = null;
        $status[MESSAGE] = "FACEBOOK_ID_MISSING";
        $response[RESPONSE_STATUS] = $status;
        $response[RESPONSE_NOTIFICATION] = null;
        echoRespnse(200, $response);
    }
});

$app->post('/mlogin', function() use ($app) {
    // check for required params
    verifyRequiredParams(array(EMAIL, PASSWORD));
    $status = array();
    $status[ERROR] = false;
    $status[MESSAGE] = null;
    $data = array();
    $response = array();
    $email = $app->request->post(EMAIL);
    $password = $app->request->post(PASSWORD);
    validateEmail($email);
    $db = new MainDbHandler();
    $result = $db->checkLogin($email,$password);
    if('FALSE' !== $result && $result !== '' && $result != null){
        $api_key = $result['api_key'];
        $data[TOKEN] = $api_key;
        $data[COUNTRY] = $result['country_name'];
        $data[CURRENCY] = $result['currency_sign'];
        $data[CURRENCYNAME] = $result['currency_name'];
        $data[COUNTRYCODE] = $result['country_code'];
        $data[COUNTRY] = $result['country_name'];
    }else{
        $status[ERROR] = TRUE;
        $status[MESSAGE] = 'BAD_EMAIL_OR_PASSWORD';
    }
    $response[RESPONSE_STATUS] = $status;
    $response[RESPONSE_NOTIFICATION] = null;
    $response[RESPONSE_DATA] = $data;
    echoRespnse(200, $response);
});
//

$app->get('/mlogout', function() use ($app){
    $status = array();
    $status[ERROR] = false;
    $status[MESSAGE] = null;
    $notification = null;
    $data = array();
    $response = array();
    //$response['user'] = $user_id;
    $_SESSION = array();
    session_unset();
    session_destroy();
    // Finally, destroy the session.
    session_start();
    $response[RESPONSE_STATUS] = $status;
    $response[RESPONSE_NOTIFICATION] = $notification;
    $response[RESPONSE_DATA] = $data;

    echoRespnse(200, $response);

});

$app->post('/login', function() use ($app) {
    // check for required params
    verifyRequiredParams(array(EMAIL, PASSWORD));
    // validating email address

    $status = array();
    $status[ERROR] = false;
    $status[MESSAGE] = null;
    $notification = null;
    $data = array();
    $response = array();
    $email = $app->request->post(EMAIL);
    $password = $app->request->post(PASSWORD);
    validateEmail($email);
    $response = array();
    global $user_region;
    $db = new DbHandler($user_region);
    $result = $db->checkLogin($email,$password);
    if($result){
        $api_key = $db->updateApiKeyForNewLogin($email);
        if($api_key != null && $api_key != ''){
            $response[RESPONSE_DATA] = $api_key;
        }else{
            $status[ERROR] = TRUE;
            $status[MESSAGE] = null;
        }
    }else{
        $status[ERROR] = TRUE;
        $status[MESSAGE] = 'Bad email or password';
    }
    $response[RESPONSE_STATUS] = $status;
    $response[RESPONSE_NOTIFICATION] = null;
    //$response[RESPONSE_DATA] = null;
    echoRespnse(200, $response);
    //Do Login stuff here !!

});
/**Get User Profile
 **url /user
 * Methode GET
 * No data, Only Authorization
 */
$app->get('/user','authenticate', function() use ($app) {
    global $user_id;
    $status = array();
    $status[ERROR] = false;
    $status[MESSAGE] = null;
    $notification = null;
    $data = array();
    $response = array();
    //$response['user'] = $user_id;
    global $user_region;
    $db = new DbHandler($user_region);
    $result = $db->getUserFromId($user_id);
    if($result != null){
        while ($user = $result->fetch_assoc()) {
            $tmp = getUserArray($user);
            $tmp[WORLDREGION] = $user_region;
            $tmp[PRIVACY_POLICAY_VERSION_PARAM] = intval(ROADMATE_TERMS_VERSION);
            $tmp[GROUP_LIST] = getUserGroups($user_id,$user_region);
            $data[USER] = $tmp;
        }
    }else{
        $status[ERROR] = true;
        $status[MESSAGE] = 'Unknown User, Please Login again';
    }

    $response[RESPONSE_STATUS] = $status;
    $response[RESPONSE_NOTIFICATION] = $notification;
    $response[RESPONSE_DATA] = $data;

    echoRespnse(200, $response);

});

function getUserArray($user){
    $tmp = array();
    $tmp[USER_ID] = $user['public_id'];
    $tmp[LASTNAME] = $user['lname'];
    $tmp[FIRSTNAME] = $user['fname'];
    $tmp[EMAIL] = $user['email'];
    $tmp[FACEBOOKFRIENDSNUMBER] = $user['fb_friends_nb'];
    $tmp[PHONE] = $user['phone'];
    $tmp[GENDER] = $user['gender'];
    $tmp[BIRTHDAY] = $user['birthday'];
    $tmp[CREATED_AT] = $user['created_at'];
    $tmp[USERPROFILE_URL] = $user['fb_pic'];
    $tmp[CARMODEL] = $user['car_model'];
    $tmp[CARSEATS] = $user['car_seats'];
    $tmp[CARTYPE] = $user['car_type'];
    $tmp[CARQUALITY] = $user["car_comfort"];
    $tmp[CARCOLOR] = $user['car_color'];
    $tmp[RATE] = $user['rate'];
    $tmp[RATE_COUNT] = $user['rate_count'];
    $tmp[TRIP_COUNT] = $user["trips_count"];
    $tmp[REVIEW_COUNT] = $user["review_count"];
    $tmp[USER_STATUS] = $user["usr_status"];
    $tmp[USER_TERMS_ACCEPTED_VERSION] = intval($user["accepted_terms_version"]);
    $tmp[USER_TERMS_ACCEPTION_DATE] = $user["terms_accept_date"];
    $tmp[USER_TERMS_HAS_ACCPETED] = $user["has_accepted_terms"];
    $tmp[USER_OPTIN_EMAIL] = $user["isOptinEmail"];
    $tmp[USER_OPTIN_SMS] = $user["isOptinSMS"];
    $tmp[USER_OPTIN_NOTIFICATION] = $user["isOptinNotification"];
    $tmp[USER_MONEY_SAVED] = $user["moneysaved"];
    $tmp[USER_CARBON_FOOT_PRINT] = $user["co2saved"];
    $tmp[USER_FEMALE_VERIFIED] = $user["isFemaleVerified"];
    $tmp[USER_EMAIL_VERIFIED] = $user["isEmailVerified"];

    $tmp[DRIVER_LICENCE_CONDITIONS] = $user["dl_conditions"];
    $tmp[DRIVER_LICENCE_COUNTRY] = $user["dl_country"];
    $tmp[DRIVER_LICENCE_ISSUED_DATE] = $user["dl_issued_date"];
    $tmp[DRIVER_LICENCE_EXPIRY_DATE] = $user["dl_expiry_date"];
    $tmp[DRIVER_LICENCE_STATUS] = $user["dl_status"];


    if(isset($user["town"])){
        $tmp[HOMETOWN] = $user["town"];
    }
    if(isset($user["country"])){
        $tmp[COUNTRY] = $user["country"];
    }
    return $tmp;
}

$app->post('/usrphone','authenticate', function() use ($app) {
    verifyRequiredParams(array(PHONE));
    $phone_number = $app->request->post(PHONE);
    //$phone_number = preg_replace('/[^\d\(\)]/', '', $phone_number);
    $pattern = '/^(\(\+\d{1,4}\))(\d{6,12})$/';//'/^(02)(\d{6,9})$/'
    global $global_user_id;
    if (preg_match($pattern, $phone_number)){
        $response = array();
        $_SESSION['user_phone_number'] = $phone_number;
        $phone_token = createSMSToken();
        $_SESSION[CONFIRMATION_PHONE_TOKEN] = $phone_token;
        $message = 'RoadMate activation code: '.$phone_token;

        $smsId = sendSMS(cleanPhoneNumber($phone_number),$message,$global_user_id);
        if($smsId){
            $response[ERROR] = false;
            $response[MESSAGE] = 'OK';
        }else{
            //Send Email to confirm user

        }
    }else{
        $response[ERROR] = true;
        $response[MESSAGE] = 'PHONE_UPDATE_FAIL';
        $time = date("Y-m-d H:i:s",time() + 61200 );
        error_log("$time | usrphone | Phone Validation failed: $phone_number.",3,ERROR_LOG_PATH);
    }
    echoRespnse(200, $response);

});
function createSMSToken($len = 4){
        $characters = '0123456789';
        $randomString = '';
        for ($i = 0; $i < $len; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
 }
function sendSMS($phone,$message,$global_user_id,$email=null){
	//Using Nexmo service
    $encodedMesage = urlencode($message);
	//Update the api keys on the following line
    $url = "https://rest.nexmo.com/sms/json?api_key=XXXXXXXXXXX&api_secret=YYYYYYYYYYYYY&from=ZZZZZZZ&to=$phone&text=$encodedMesage";
    try{
        $curl = curl_init();
        if(FALSE === $curl)
            throw new Exception('Failed to initialise curl');
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($curl);
        if(FALSE === $content)
            throw new Exception(curl_error($curl), curl_errno($curl));

        curl_close($curl);
        $sms_return = json_decode($content);
        if($sms_return){
            $arr = array();
            if(property_exists($sms_return,'messages')){
                $arr = 	(array)$sms_return->messages[0];
            }
            if(is_array($arr) && isset($arr['message-price']) && isset($arr['message-id']) && isset($arr['remaining-balance'])){
                $sms_cost = $arr['message-price'];
                $sms_traffic_id = $arr['message-id'];
                $sms_remaining_balance = $arr['remaining-balance'];
                $sms_status = $arr['status'];
                $db = new MainDbHandler();
                $msgId = $db->saveSms($global_user_id,$email, $phone, $sms_cost, $sms_traffic_id, $message, $sms_status, $sms_remaining_balance);
                return $msgId;
            }
        }
    } catch (Exception $ex) {
        sendErrorEmail(sprintf('Failed to validate Access token with error #%d: %s',$ex->getCode(), $ex->getMessage()));
        return NULL;
    }
    return NULL;
}
$app->post('/updateuser','authenticate', function() use ($app) {
    verifyRequiredParams(array(FIRSTNAME,PHONE, BIRTHDAY));
    $status = array();
    $status[ERROR] = false;
    $status[MESSAGE] = null;
    $notification = null;
    $data = array();
    $response = array();
    $phone = $phone_number = $app->request->post(PHONE);
    $firstName = $phone_number = $app->request->post(FIRSTNAME);
    $birthday = $phone_number = $app->request->post(BIRTHDAY);
    global $user_id;
    global $user_region;
    $db = new DbHandler($user_region);
    $result = $db->updateUserDetails($firstName,$phone,$birthday,$user_id);
    if($result != null){
        while ($user = $result->fetch_assoc()) {
            $tmp = getUserArray($user);
            $tmp[PRIVACY_POLICAY_VERSION_PARAM] = intval(ROADMATE_TERMS_VERSION);
            $data[USER] = $tmp;
        }
    }else{
        $status[ERROR] = true;
        $status[MESSAGE] = 'ERROR';
    }
    $response[RESPONSE_STATUS] = $status;
    $response[RESPONSE_NOTIFICATION] = $notification;
    $response[RESPONSE_DATA] = $data;

    echoRespnse(200, $response);
});

/** User Registration
 *
 * url - /register
 * method - POST
 * params - name, email, password
 */
$app->post('/user', function() use ($app) {
    // check for required params
    verifyRequiredParams(array(FIRSTNAME, LASTNAME, GENDER, PHONE, EMAIL, PASSWORD,BIRTHDAY));

    $response = array();

    // reading post params
    $lname = $app->request->post(LASTNAME);
    $fname = $app->request->post(FIRSTNAME);
    $email = $app->request->post(EMAIL);
    $phone = $app->request->post(PHONE);
    $gender = $app->request->post(GENDER);
    $password = $app->request->post(PASSWORD);
    $b_day = $app->request->post(BIRTHDAY);

    // validating email address
    validateEmail($email);
    $db = new MainDbHandler();
    $res = $db->createUser($fname, $lname, $email, $password, $phone, $gender,$b_day);

    if ($res['public_id'] == USER_CREATE_FAILED) {
        $response[ERROR] = true;
        $response[MESSAGE] = USER_CREATE_FAILED; //"Oops! An error occurred while registereing";
        echoRespnse(200, $response);
    } else if ($res['public_id'] == USER_ALREADY_EXISTED) {
        $response[ERROR] = true;
        $response["error_code"] = USER_ALREADY_EXISTED; //"Sorry, this email already existed";
        echoRespnse(200, $response);
    } else {
        $response[ERROR] = false;
        $response[USER_ID] = $res; //"You are successfully registered";
        sendWelcomeEmailTo($email);
        //TODO add a message with the id created
        echoRespnse(201, $response);
    }
});
function validateRegion($country,$region){
    if($region == 'tp'){
        if($country == 'New Zealand')
            return 'nz';
        else if($country == 'Australia')
            return 'au';
        else if($country == 'New Caledonia')
            return 'nc';
        else
            return null;
    }else if(isValidRegion($region)){
        return $region;
    }
    return null;
}
function createUser($lname,$fname,$email,$phone,$gender,$b_day,$password,$home,$country,$region,$fb_friends_nb,$mutual_friends,$fid,$facebook_pic,$access_token){
    validateEmail($email);
    $response = array();
    $terms_version = intval(ROADMATE_TERMS_VERSION);

    if($region == 'tp'){
        if($country == 'New Zealand')
           $region = 'nz';
        else if($country == 'Australia')
            $region = 'au';
        else if($country == 'New Caledonia')
            $region = 'nc';
        else
            return null;
    }

    $db = new DbHandler($region);

    $target_id = $db->createUser($fname, $lname, $email, $phone, $gender,$b_day,$home,$country,$fb_friends_nb,$mutual_friends,$facebook_pic);

    if ($target_id == 0) {
        $response[ERROR] = true;
        $response[MESSAGE] = 'USER_EXISTS_ALREADY';
    } else if ($target_id == -1) {
        $response[ERROR] = true;
        $response[MESSAGE] = 'FAILED_TO_CREATE_USER';
    } else {
        $db_main = new MainDbHandler();
        $globalUser = $db_main->createUser($fid, $target_id, $email, $access_token, $password, $terms_version, $region, $country);
        if($globalUser && is_array($globalUser) && $globalUser['global_user_id'] > 0){
            $response[ERROR] = false;
            $_SESSION['api_temp'] = $globalUser['api_key'];
            $_SESSION['token'] = $db->createSMSToken();
            if($phone){
                //SEND CONFIRMATION SMS
                $_SESSION[GLOBAL_USER_ID] = $globalUser['global_user_id'];
                $_SESSION[USER_ID] = $target_id;
                $_SESSION['region'] = $region;
                $db->acceptTermsAndConditions($target_id, ROADMATE_TERMS_VERSION);
                $smsId = sendSMS(cleanPhoneNumber($phone), 'RoadMate activation code : '.$_SESSION['token'], $globalUser['global_user_id'],$email);
                if($smsId){
                    $response[ERROR] = false;
                    $response[MESSAGE] = 'OK';
                }else{
                    //IF SMS FAILS Send confirmation Email :
                    $response[ERROR] = false;
                    $response[MESSAGE] = 'VERIFY_EMAIL';
                }
            }else{
                $response[ERROR] = false;
                $response[MESSAGE] = 'VERIFY_EMAIL';

            }
        }else {
            $response[ERROR] = true;
            $response[MESSAGE] = 'FAILED_TO_CREATE_USER_MAIN';
        }
    }
    return $response;
}
function cleanPhoneNumber($phone){
    $arr = explode(")", $phone);
    if(is_array($arr) && strpos($phone, '(+') !==false && strpos($arr[0],'(+') !==false ){
        if(substr( $arr[1], 0, 1 ) === "0")
               $arr[1] = substr($arr[1], 1, strlen($arr[1]) -1 );
        return substr($arr[0], 2, strlen($arr[0])) . $arr[1];
    }else{
        return $phone;
    }
}
$app->post('/wusr', function() use ($app) {
    // check for required params
    verifyRequiredParams(array(FIRSTNAME, LASTNAME, GENDER, PHONE, EMAIL, PASSWORD,BIRTHDAY,CAPTCHA,COUNTRY,HOMETOWN,WORLDREGION));
    $response = array();
    if(!isset($_SESSION['attempt_create']))
        $_SESSION['attempt_create'] = 1;
    $status = 200;
    if(isset($_SESSION['captcha'])){
        if ($_SESSION['attempt_create'] < 5 && $app->request->post(CAPTCHA) == $_SESSION['captcha']) {
            $_SESSION['captcha'] = '';
            // reading post params
            $lname = $app->request->post(LASTNAME);
            $fname = $app->request->post(FIRSTNAME);
            $email = $app->request->post(EMAIL);
            $phone = $app->request->post(PHONE);
            $gender = intval($app->request->post(GENDER));
            $password = $app->request->post(PASSWORD);
            $b_day = $app->request->post(BIRTHDAY);
            $home = $app->request->post(HOMETOWN);
            $country = $app->request->post(COUNTRY);
            $region = $app->request->post(WORLDREGION);
            $fid = $app->request->post(USER_FACEBOOK_ID);
            $fb_friends_nb = $app->request->post(FACEBOOKFRIENDSNUMBER);
            $mutual_friends = $app->request->post(FACEBOOK_MUTUAL_FRIENDS);
            if($fid)
                $facebook_pic = 'https://graph.facebook.com/'.$fid.'/picture?type=square&width=300';
            else
                $facebook_pic = null;
            $access_token = $app->request->post(FACEBOOK_ACCESS_TOKEN);
            $response = createUser($lname,$fname,$email,$phone,$gender,$b_day,$password,$home,$country,$region,$fb_friends_nb,$mutual_friends,$fid,$facebook_pic,$access_token);
        }
        else{
            $_SESSION['attempt_create'] = $_SESSION['attempt_create'] + 1;
            $response[ERROR] = true;
            $response[MESSAGE] = ERROR_CAPTCHA;
        }
    }else{
        $_SESSION['attempt_create'] = $_SESSION['attempt_create'] + 1;
        $response[ERROR] = true;
        $response[MESSAGE] = ERROR_CAPTCHA;
    }
    echoRespnse($status, $response);
});
$app->post('/cusr', function() use ($app) {
    // check for required params
    verifyRequiredParams(array(FIRSTNAME, LASTNAME, GENDER, PHONE, EMAIL, PASSWORD,BIRTHDAY,COUNTRY,HOMETOWN,WORLDREGION));
    $response = array();
    if(!isset($_SESSION['attempt_create']))
        $_SESSION['attempt_create'] = 1;
    $status = 200;
    if ($_SESSION['attempt_create'] < 3) {
        // reading post params
        $lname = $app->request->post(LASTNAME);
        $fname = $app->request->post(FIRSTNAME);
        $email = $app->request->post(EMAIL);
        $phone = $app->request->post(PHONE);
        $gender = intval($app->request->post(GENDER));
        $password = $app->request->post(PASSWORD);
        $b_day = $app->request->post(BIRTHDAY);
        $home = $app->request->post(HOMETOWN);
        $country = $app->request->post(COUNTRY);
        $region = $app->request->post(WORLDREGION);
        $fid = $app->request->post(USER_FACEBOOK_ID);
        $fb_friends_nb = $app->request->post(FACEBOOKFRIENDSNUMBER);
        $mutual_friends = $app->request->post(FACEBOOK_MUTUAL_FRIENDS);
        if($fid)
            $facebook_pic = 'https://graph.facebook.com/'.$fid.'/picture?type=square&width=300';
        else
            $facebook_pic = null;
        $access_token = $app->request->post(FACEBOOK_ACCESS_TOKEN);
        $response = createUser($lname,$fname,$email,$phone,$gender,$b_day,$password,$home,$country,$region,$fb_friends_nb,$mutual_friends,$fid,$facebook_pic,$access_token);
        $_SESSION['attempt_create'] = $_SESSION['attempt_create'] +1;
    }
    else{
        $response[ERROR] = true;
        $response[MESSAGE] = 'USER_OVER_CREATING';
    }
    echoRespnse($status, $response);
});

/** User Validation
 *
 * url - /token
 * method - POST
 * params - tk : token
 */
$app->post('/token', function() use ($app) {
    // check for required params
    verifyRequiredParams(array(TOKEN));
    $response = array();
    $data = array();
    // reading post params
    $token = $app->request()->post(TOKEN);

    if($token != null && $token != ''){
        if(isset($_SESSION['token']) && $_SESSION['token'] != ''){
            if($token == $_SESSION['token'] && isset($_SESSION['region']) && isset($_SESSION[USER_ID])){
                $db = new DbHandler($_SESSION['region']);
                $db->subscribeUser($_SESSION[USER_ID]);
                $mainDb = new MainDbHandler();
                $user = $mainDb->isValidApiKey($_SESSION['api_temp']);
                if($user){
                    $data[TOKEN] = $_SESSION['api_temp'];
                    $data[CURRENCY] = $user['currency_sign'];
                    $data[CURRENCYNAME] = $user['currency_name'];
                    $data[COUNTRYCODE] = $user['country_code'];
                    $data[COUNTRY] = $user['country_name'];
                }else{
                    $data = null;
                    $response[ERROR] = true;
                    $response[MESSAGE] = 'BAD_USER_INSERTION';
                }
                $response[RESPONSE_DATA] = $data;
                //$response[]
            }
            else{
                $response[ERROR] = true;
                $response[MESSAGE] = 'BAD';
            }
        }
        else{
            $response[ERROR] = true;
            $response[MESSAGE] = 'ERROR';
        }

    }else{
        $response[ERROR] = true;
        $response[MESSAGE] = 'TOKEN_EMPTY';
    }

    echoRespnse(200, $response);
});

$app->post('/updatePassword','authenticate', function() use ($app) {
    // check for required params
    if(!isset($_SESSION['update_password_nb_attempts'])){
        $_SESSION['update_password_nb_attempts'] = 1;
    }else{
        $_SESSION['update_password_nb_attempts'] += 1;
    }
    if($_SESSION['update_password_nb_attempts'] < 4){
        $status = 201;
        $response = array();
        $response[MESSAGE] = "OK";
        $response[ERROR] = false;
        $oldPassword = $app->request()->post(OLD_PASSWORD);
        $newPassword = $app->request()->post(NEW_PASSWORD);
        $db = new MainDbHandler();
        global $global_user_id;
        $result = $db->CheckUserOldPasswordAndReplace($global_user_id,$oldPassword, $newPassword);
        $response[MESSAGE] = $result;
        $response[ERROR] = $result !== 'OK';

    }else{
        $response[MESSAGE] = "ATTEMPTS_ACCEEDED";
        $status = 401;
        $response[ERROR] = true;
    }
    echoRespnse($status, $response);
});

$app->post('/resetpwd', function() use ($app) {
    // check for required params
    verifyRequiredParams(array(EMAIL,TOKEN,WORLDREGION));
    $response[ERROR] = true;
    $response[MESSAGE] = 'ERROR';
    $response = array();
    // reading post params
    $email = $app->request()->post(EMAIL);
    $token = $app->request()->post(TOKEN);
    $region = $app->request()->post(WORLDREGION);
    if($email != null && $email != '' && $token != ''){
        if(isset($_SESSION['fg-pwd']) && $_SESSION['fg-pwd'] != ''){
            if($token == $_SESSION['fg-pwd'] && isValidRegion($region)){
                $db = new MainDbHandler();
                $globalUserId = $db->isUserExists($email);
                if($globalUserId){
                   $response[ERROR] = false;
                   $db2 = new DbHandler($region);
                   $user = $db2->getUserPhoneFromEmail($email);
                   if($user){
                       $sms_token = createSMSToken(6);
                       $userId = $user['id'];
                       $userPhone = $user['phone'];
                       if($userId > 0){
                           if($userPhone !== null && $userPhone !== ''){
                               $_SESSION['fg-pwd-token'] = $sms_token;
                                $_SESSION['fg-pwd-email'] = $email;
                                $_SESSION['fg-pwd-region'] = $region;
                                $_SESSION['fg-pwd-globalId'] = $globalUserId;
                                $msgId = sendSMS(cleanPhoneNumber($userPhone),'RoadMate activation code : '.$sms_token,$globalUserId,$email);
                                if($msgId){
                                    $response[MESSAGE] = 'OK';
                                    $response[ERROR] = false;
                                }else{
                                    //Send Email to the user.

                                }
                           }else{
                               //Send Email to Admin. User found in Main but not in Target
                                $response[MESSAGE] = 'MISSING_PHONE_NUMBER';
                                $response[ERROR] = true;
                                $time = date("Y-m-d H:i:s",time() + 61200 );
                                error_log("$time | resetpwd | ERROR : user $email from [$region] tried forgot password but does not exist in db \n",3,ERROR_LOG_PATH);
                           }
                       }else{
                           $response[MESSAGE] = 'USER_NOT_FOUND_IN_SPECIFIED_REGION';
                           $response[ERROR] = true;
                       }
                   }else{
                        $response[MESSAGE] = 'USER_NOT_FOUND';
                        $response[ERROR] = true;
                       $time = date("Y-m-d H:i:s",time() + 61200 );
                       error_log("$time | resetpwd | URGENT ERROR : could find phone for user $email \n",3,ERROR_LOG_PATH);
                   }
                }else{
                    $response[MESSAGE] = 'USER_NOT_FOUND';
                    $response[ERROR] = true;
                    $time = date("Y-m-d H:i:s",time() + 61200 );
                    error_log("$time | resetpwd | ERROR : user $email tried forgot password but does not exist in db \n",3,ERROR_LOG_PATH);
                }
            }
            else{
                $time = date("Y-m-d H:i:s",time() + 61200 );
                error_log("$time | resetpwd | ERROR : user $email tried forgot wrong Captha. User sent: $token instead of ".$_SESSION['fg-pwd']."\n",3,ERROR_LOG_PATH);
                $response[MESSAGE] = 'BAD';
            }
        }
        else{
            $response[MESSAGE] = 'BAD_TOKEN';
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time | resetpwd | URGENT ERROR : could not find the captcha for forgotten password",3,ERROR_LOG_PATH);
        }
    }else{
        $response[ERROR] = true;
        $response[MESSAGE] = 'TOKEN_EMPTY';
    }

    echoRespnse(200, $response);
});
//
$app->get('/getcomments/:id','authenticate', function($user_public_id) {
    $response = array();
    global $user_region;
    $db = new DbHandler($user_region);
    $response[COMMENT_LIST] = array();
    $response[ERROR] = false;
    $response[MESSAGE] = null;
    // fetching all user tasks
    $conversations = $db->getListOfCommets($user_public_id);
    if ($conversations !== null) {
        $response[ERROR] = false;
        while ($conversation = $conversations->fetch_assoc()) {
            $temp = array();
            $temp[FIRSTNAME] = $conversation["fname"];
            $temp[USERPROFILE_URL] = $conversation["fb_pic"];
            $temp[MESSAGE_TXT] = $conversation["comment"];
            $temp[CREATED_AT] = $conversation["creationDate"];
            $temp[RATE] = $conversation["rate"];
            array_push($response[COMMENT_LIST], $temp);
        }
        echoRespnse(200, $response);
    }
    else {
        $response[ERROR] = true;
        $response[MESSAGE] = "EMPTY";
        echoRespnse(200, $response);
    }
});


$app->get('/getIndicatif/:id', function($country) {
    $response = array();
    $db = new MainDbHandler();
    $response[ERROR] = false;
    $response[MESSAGE] = "OK";
    //This preventing from repetitive attacks;
    if(isset($_SERVER['indicatif']) && is_array($_SERVER['indicatif']) && $_SERVER['indicatif']['country'] === $country){
        $response[RESPONSE_DATA] = $indicatif;
        echoRespnse(200, $response);
    }else{
        $indicatif = $db->getIndicatifFromCountry($country);
        if ($indicatif ) {
            $tmp = array();
            $tmp['country'] = $indicatif;
            $_SERVER['indicatif'] = $tmp;
            $response[RESPONSE_DATA] = $indicatif;
            echoRespnse(200, $response);
        }
        else {
            $tmp = array();
            $tmp['country'] = null;
            $_SERVER['indicatif'] = $tmp;
            $response[ERROR] = true;
            $response[MESSAGE] = "DOES_NOT_EXIST";
            echoRespnse(200, $response);
        }
    }
});

$app->get('/resetpwd/:id', function($token) {
    // check for required params
    $response = array();
    $response[ERROR] = true;
    $response[MESSAGE] = 'ERROR';
    $region = '';
    if(isset($_SESSION['fg-pwd-region'])){
        $region = $_SESSION['fg-pwd-region'];
    }
    if($token != ''){
        if(isset($_SESSION['fg-pwd-token']) && isset($_SESSION['fg-pwd-email']) && $_SESSION['fg-pwd-token'] != ''){
            if($token == intval($_SESSION['fg-pwd-token'])){
                $db = new MainDbHandler();
                $api_key = $db->updateApiKeyForNewLogin($_SESSION['fg-pwd-email']);
                if($api_key != null && $api_key != ''){
                    $response[ROADMATE_UPDATE_PASSWORD_TOKEN] = $db->createSMSToken(36);
                    $_SESSION['roadmate_update_password_token'] = $response[ROADMATE_UPDATE_PASSWORD_TOKEN];
                    $response[ERROR] = false;
                    $response[MESSAGE] = 'OK';
                }else{
                    $response[MESSAGE] = 'FAILED_TO_UPDATE_API_KEY';
                }
            }
            else{
                $time = date("Y-m-d H:i:s",time() + 61200 );
                error_log("$time | resetpwd | ERROR : user" .$_SESSION['fg-pwd-email']. "tried forgot wrong Captha. User sent: $token instead of ".$_SESSION['fg-pwd']."\n",3,ERROR_LOG_PATH);
                $response[MESSAGE] = 'BAD';
            }
        }
        else{
            $response[MESSAGE] = 'ERROR';
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time | resetpwd | URGENT ERROR : could not find the captcha for forgotten password \n",3,ERROR_LOG_PATH);
        }

    }else{
        $response[ERROR] = true;
        $response[MESSAGE] = 'TOKEN_EMPTY';
    }
    echoRespnse(200, $response);
});


$app->post('/resetPassword',function() use ($app) {
    // check for required params
    verifyRequiredParams(array(PASSWORD,ROADMATE_UPDATE_PASSWORD_TOKEN));
    $response = array();
    // reading post params
    $status = 201;
    $password = $app->request()->post(PASSWORD);
    $token = $app->request()->post(ROADMATE_UPDATE_PASSWORD_TOKEN);
    $region = '';
    if(isset($_SESSION['fg-pwd-region'])){
        $region = $_SESSION['fg-pwd-region'];
    }

    if(strlen($password) < 6 ||strlen($password) > 30){
        $response[ERROR] = true;
        $response[MESSAGE] = 'PASSWORD_LENGTH_ERROR';
        $status = 200;
    }
    else if (!isValidRegion($region)){
        $response[ERROR] = true;
        $response[MESSAGE] = 'BROKEN_PROCESS';
        $status = 200;
    }
    else if (!isset($_SESSION['fg-pwd-email']) || $_SESSION['fg-pwd-email'] === ''){
        $response[ERROR] = true;
        $response[MESSAGE] = 'PASSWORD_PROCESS_UNCOMPLETE';
        $status = 200;
    }
    else if(isset($_SESSION['roadmate_update_password_token']) && $_SESSION['roadmate_update_password_token'] === $token ){
         $db = new MainDbHandler();
         $api_key = $db->resetPassword($_SESSION['fg-pwd-email'],$password);
         if($api_key !== null){
             $result = $db->isValidApiKey($api_key);
             $data = array();
             $data[TOKEN] = $api_key;
             $data[CURRENCY] = $result['currency_sign'];
             $data[CURRENCYNAME] = $result['currency_name'];
             $data[COUNTRYCODE] = $result['country_code'];
             $data[COUNTRY] = $result['country_name'];
             $response[RESPONSE_DATA] = $data;
             $response[ERROR] = false;
             $response[MESSAGE] = 'OK';
             $response[TOKEN] = $api_key;
         }else{
             $response[ERROR] = true;
             $response[MESSAGE] = 'ERROR_UNABLE_TO_CHANGE_PASSWORD';
             $response[TOKEN] = $api_key;
             $status = 400;
         }
    }else{
        $response[ERROR] = true;
        $response[MESSAGE] = 'TOKEN_EXPIRED';
        $status = 200;
    }
    echoRespnse($status, $response);
});

$app->post('/confirmephone','authenticate', function() use ($app) {
    // check for required params
    verifyRequiredParams(array(TOKEN));
    $response = array();
    // reading post params
    $token = $app->request()->post(TOKEN);
    if($token != null && $token != ''){
        if(isset($_SESSION[CONFIRMATION_PHONE_TOKEN]) && $_SESSION[CONFIRMATION_PHONE_TOKEN] != ''){
            if($token == $_SESSION[CONFIRMATION_PHONE_TOKEN]){
                global $user_id;
                global $user_region;
                $db = new DbHandler($user_region);
                $db->writeUserPhoneFromapiKey($user_id,$_SESSION['user_phone_number']);
                $db->subscribeUser($user_id);
                $response[ERROR] = false;
                 $response[MESSAGE] = 'OK';
            }
            else{
                $response[ERROR] = true;
                $response[MESSAGE] = 'BAD';
            }
        }
        else{
            $response[ERROR] = true;
            $response[MESSAGE] = 'ERROR';
        }

    }else{
        $response[ERROR] = true;
        $response[MESSAGE] = 'TOKEN_EMPTY';
    }

    echoRespnse(200, $response);
});

function isValidRegion($region){
    $available_regions = array(NORTHMERICA,SOUTHAMERICA,EUROPE,AFRICA,ASIA,TASMANPACIFIC,NEWZEALAND,NEWCALEDONIA,AUSTRALIA);
    return in_array($region,$available_regions);
}

function isValidCountry($region,$country){
    $africa = array('Algeria','Angola','Benin','Botswana','Burkina','Burundi','Cameroon','Cape Verde','Central African Republic','Chad','Comoros','Congo','Djibouti','Egypt','Equatorial Guinea','Eritrea','Ethiopia','Gabon','Gambia','Ghana','Guinea','Guinea-Bissau','Ivory Coast','Kenya','Lesotho','Liberia','Libya','Madagascar','Malawi','Mali','Mauritania','Mauritius','Morocco','Mozambique','Namibia','Niger','Nigeria','Rwanda','Sao Tome and Principe','Senegal','Seychelles','Sierra Leone','Somalia','South Africa','South Sudan','Sudan','Swaziland','Tanzania','Togo','Tunisia','Uganda','Zambia','Zimbabwe');
    $asia = array('Afghanistan','Bahrain','Bangladesh','Bhutan','Brunei','Burma (Myanmar)','Cambodia','China','East Timor','India','Indonesia','Iran','Iraq','Israel','Japan','Jordan','Kazakhstan','Korea, North','Korea, South','Kuwait','Kyrgyzstan','Laos','Lebanon','Malaysia','Maldives','Mongolia','Nepal','Oman','Pakistan','Philippines','Qatar','Russian Federation','Saudi Arabia','Singapore','Sri Lanka','Syria','Tajikistan','Thailand','Turkey','Turkmenistan','United Arab Emirates','UAE','Uzbekistan','Vietnam','Yemen');
    $europe = array('Albania','Andorra','Armenia','Austria','Azerbaijan','Belarus','Belgium','Bosnia and Herzegovina','Bulgaria','Croatia','Cyprus','Czech Republic','Denmark','Estonia','Finland','France','Georgia','Germany','Greece','Hungary','Iceland','Ireland','Italy','Latvia','Liechtenstein','Lithuania','Luxembourg','Macedonia','Malta','Moldova','Monaco','Montenegro','Netherlands','Norway','Poland','Portugal','Romania','San Marino','Serbia','Slovakia','Slovenia','Spain','Sweden','Switzerland','Ukraine','United Kingdom','UK');
    $southAmerica = array('Antigua and Barbuda','Bahamas','Barbados','Belize','Costa Rica','Cuba','Dominica','Dominican Republic','El Salvador','Grenada','Guatemala','Haiti','Honduras','Jamaica','Mexico','Nicaragua','Panama','Saint Kitts and Nevis','Saint Lucia','Saint Vincent and the Grenadines','Trinidad and Tobago');
    $northAmerica = array('Canada','United States','United States of America','USA');
    $tasmanPacific = array('Australia','New Zealand','New Caledonia','Fiji');
    $result = false;
    if(isValidRegion($region)){
        switch($region){
            case 'na' :
                $result = in_array($country,$northAmerica);
            break;
            case 'sa' :
                $result = in_array($country,$southAmerica);
            break;
            case 'eu' :
                $result = in_array($country,$europe);
            break;
            case 'af' :
                $result = in_array($country,$africa);
            break;
            case 'tp' :
                $result = in_array($country,$tasmanPacific);
            break;
            case 'as' :
                $result = in_array($country,$asia);
            break;
            default :
            break;
        }
    }
    return $result;
}


//**********Tests***********************

//***********************************

function writeSearchTrips($result){
    $trip_id = 0;
    $trip_list = array();
    $tmp = array();
    while ($trip = $result->fetch_assoc()) {
        $subtrips = array();

        if($trip["trip_id"] >0){
            if($trip_id != $trip["trip_id"]){
                if($trip_id > 0)
                    array_push($trip_list, $tmp);
                $tmp = array();
                $trip_id = $trip["trip_id"];
                $tmp[SUBTRIP_LIST] = array();
                $tmp[DEPARTURE_TIME] = $trip["departure_time"];
                $tmp[TRIP_ID] = $trip["trip_public_id"];
                $tmp[DEPARTURE_NAME] = $trip["departure_name"];
                $tmp[DEPARTURE_LATITUDE] = $trip["departure_lt"];
                $tmp[DEPARTURE_LONGITUDE] = $trip["departure_lg"];
                $tmp[ARRIVAL_NAME] = $trip["arrival_name"];
                $tmp[ARRIVAL_LATITUDE] = $trip["arrival_lt"];
                $tmp[ARRIVAL_LONGITUDE] = $trip["arrival_lg"];
                $tmp[PRICE] = $trip["price"];
                $tmp[AVAILABLE_SEATS] = $trip["available_seats"];
                $tmp[DESCRIPTION] = $trip["description"];
                $tmp[SEAT_TO_FIRST_STEP] = $trip["seats_to_first_Step"];
                $tmp[PRICE_FROM_LAST_STEP] = $trip["price_from_last_step"];
                $tmp[TRIP_PARAM] = $trip["trip_param"];
                $tmp[USER_ID] = $trip["usrId"];
                $tmp[USERPROFILE_URL] = $trip["fb_pic"];
                $tmp[FACEBOOKFRIENDSNUMBER] = $trip["fb_friends_nb"];
                $tmp[FIRSTNAME] = $trip["fname"];
                $tmp[BIRTHDAY] = $trip["birthday"];
                $tmp[GENDER] = $trip["gender"];
                //	TRIP_DURATION
                $tmp[ARRIVAL_TIME] = $trip["arrival_time"];
                $tmp[TRIP_DISTANCE] = $trip["trip_distance"];
                $tmp[TRIP_DURATION] = $trip["trip_duration"];
                $tmp[TRIP_DURATION_FROM_LAST] = $trip["trip_duration_from_previous"];
                $tmp[TRIP_DISTANCE_FROM_LAST] = $trip["trip_distance_from_previous"];
                //TRIP COMMUTE DETAILS
                $tmp[TRIP_COMMUTE] = $trip["trip_is_commute"];
                $tmp[TRIP_COMMUTE_END_DATE] = $trip["trip_commute_enddate"];
                $tmp[TRIP_IS_WOMEN_ONLY] = $trip["isWomenOnly"];
                $tmp[TRIP_COMMUTE_PATTERN] = $trip["trip_commute_values"];
                // CAR DETAILS
                $tmp[CARMODEL] = $trip["car_model"];
                $tmp[CARCOLOR] = $trip["car_color"];
                $tmp[CARTYPE] = $trip["car_type"];
                $tmp[CARSEATS] = $trip['car_seats'];
                $tmp[CARQUALITY] = $trip["car_comfort"];
                //.review_count,.trips_count,
                $tmp[RATE] = $trip["rate"];
                $tmp[TRIP_COUNT] = $trip["trips_count"];
                $tmp[REVIEW_COUNT] = $trip["review_count"];
                $tmp[RATE_COUNT] = $trip["rate_count"];
                $tmp[USER_CARBON_FOOT_PRINT] = $trip["co2saved"];
                ///
                $tmp[DRIVER_LICENCE_CONDITIONS] = $trip["dl_conditions"];
                $tmp[DRIVER_LICENCE_ISSUED_DATE] = $trip["dl_issued_date"];
                $tmp[DRIVER_LICENCE_EXPIRY_DATE] = $trip["dl_expiry_date"];
                $tmp[DRIVER_LICENCE_STATUS] = $trip["dl_status"];
                /////
                $tmp[GROUP_NAME] = $trip["group_name"];
                $tmp[GROUP_ID] = $trip["group_public_id"];
                if($trip["subtrip_id"] != null && $trip["subtrip_id"] != '' ){
                    $subtrips[SUBTRIP_ID] = $trip["subtrip_id"];
                    $subtrips[STEP_TOWN_NAME] = $trip["step_name"];
                    $subtrips[STEP_TOWN_LT] = $trip["step_lt"];
                    $subtrips[STEP_TOWN_LG] = $trip["step_lg"];
                    $subtrips[STEP_ORDER] = $trip["step_order"];
                    $subtrips[PRICE_FROM_PREVIOUS] = $trip["price_from_previous"];
                    $subtrips[AVAILABLE_SEATS_TO_NEXT] = $trip["available_seats_to_next"];
                    $subtrips[DURATION_FROM_PREVIOUS] = $trip["duration_fromPrevious"];
                    $subtrips[DISTANCE_FROM_PREVIOUS] = $trip["distance_fromPrevious"];
                    $subtrips[SUBTRIP_ARRIVAL_TIME] = $trip["subtrip_arrival_time"];
                    array_push($tmp[SUBTRIP_LIST], $subtrips);
                }
            }
            else{
                if($trip["subtrip_id"] != null && $trip["subtrip_id"] != '' ){
                    $subtrips[SUBTRIP_ID] = $trip["subtrip_id"];
                    $subtrips[STEP_TOWN_NAME] = $trip["step_name"];
                    $subtrips[STEP_TOWN_LT] = $trip["step_lt"];
                    $subtrips[STEP_TOWN_LG] = $trip["step_lg"];
                    $subtrips[STEP_ORDER] = $trip["step_order"];
                    $subtrips[PRICE_FROM_PREVIOUS] = $trip["price_from_previous"];
                    $subtrips[AVAILABLE_SEATS_TO_NEXT] = $trip["available_seats_to_next"];
                    $subtrips[DURATION_FROM_PREVIOUS] = $trip["duration_fromPrevious"];
                    $subtrips[DISTANCE_FROM_PREVIOUS] = $trip["distance_fromPrevious"];
                    $subtrips[SUBTRIP_ARRIVAL_TIME] = $trip["subtrip_arrival_time"];
                    $subtrips[IS_LAST] = $trip["is_last"];
                    array_push($tmp[SUBTRIP_LIST], $subtrips);
                }
            }
        }
    }
    if(count($tmp) === 0){
        $trip_list = null;
    }
    else{
        array_push($trip_list, $tmp);
    }

    return $trip_list;
}

//**********End Tests*******************
/** Listing all trips corresponding to specific departure and destination
 *
 * method Post to sent more than one criteria
 * url /tasks
 */
$app->post('/search', 'authenticate', function() use ($app) {
    // check for required params
    //verifyRequiredParams(array(DEPARTURE_TIME,DEPARTURE_NAME,ARRIVAL_NAME,DEPARTURE_LATITUDE,DEPARTURE_LONGITUDE));

    $response = array();
    $departure_time = $app->request->post(DEPARTURE_TIME) == '' ? null : $app->request->post(DEPARTURE_TIME);
    $departure_name = $app->request->post(DEPARTURE_NAME) == '' ? null : $app->request->post(DEPARTURE_NAME);
    $arrival_name = $app->request->post(ARRIVAL_NAME) == '' ? null : $app->request->post(ARRIVAL_NAME);
    $departure_lt = $app->request->post(DEPARTURE_LATITUDE) == '' ? null : $app->request->post(DEPARTURE_LATITUDE);
    $departure_lg = $app->request->post(DEPARTURE_LONGITUDE) == '' ? null : $app->request->post(DEPARTURE_LONGITUDE);

    $arrival_lt = $app->request->post(ARRIVAL_LATITUDE) == '' ? null : $app->request->post(ARRIVAL_LATITUDE);
    $arrival_lg = $app->request->post(ARRIVAL_LONGITUDE) == '' ? null : $app->request->post(ARRIVAL_LONGITUDE);

    $onlywomen = intval($app->request->post(SEARCH_ONLYWOMAN_TRIPS)) === 0 ? 0 : 1;
    //Optionnal fields

    global $user_region;
    $db = new DbHandler($user_region);
    //$from = $app->request->get('from');
    //$to = $app->request->get('to');
    // fetching all user tasks
    global $user_id;
    $result = $db->getLuckyTrips($departure_time,$departure_name,$arrival_name,$departure_lt,$departure_lg,$arrival_lt,$arrival_lg,$onlywomen,$user_id);
    if($result){
        $tripList = writeSearchTrips($result);
        $response[ERROR] = false;
        $response[TRIPLIST] = array();
        if(is_array($tripList) && count($tripList) === 0)
            $response[TRIPLIST] = null;
        else
            $response[TRIPLIST] = $tripList;
    }else{
        $response[ERROR] = true;
    }
    echoRespnse(200, $response);
});

$app->post('/searchoffline', function() use ($app) {
    $response = array();
    $departure_time = $app->request->post(DEPARTURE_TIME) == '' ? null : $app->request->post(DEPARTURE_TIME);
    $departure_name = $app->request->post(DEPARTURE_NAME) == '' ? null : $app->request->post(DEPARTURE_NAME);
    $arrival_name = $app->request->post(ARRIVAL_NAME) == '' ? null : $app->request->post(ARRIVAL_NAME);
    $departure_lt = $app->request->post(DEPARTURE_LATITUDE) == '' ? null : $app->request->post(DEPARTURE_LATITUDE);
    $departure_lg = $app->request->post(DEPARTURE_LONGITUDE) == '' ? null : $app->request->post(DEPARTURE_LONGITUDE);

    $arrival_lt = $app->request->post(ARRIVAL_LATITUDE) == '' ? null : $app->request->post(ARRIVAL_LATITUDE);
    $arrival_lg = $app->request->post(ARRIVAL_LONGITUDE) == '' ? null : $app->request->post(ARRIVAL_LONGITUDE);
    //Optionnal fields
    $db = new DbHandler('nz');
    $result = $db->getLuckyTrips($departure_time,$departure_name,$arrival_name,$departure_lt,$departure_lg,$arrival_lt,$arrival_lg,0,0);
    if($result){
        $tripList = writeSearchTrips($result);
        $response[ERROR] = false;
        $response[TRIPLIST] = array();
        if(is_array($tripList) && count($tripList) === 0)
            $response[TRIPLIST] = null;
        else
            $response[TRIPLIST] = $tripList;
    }else{
        $response[ERROR] = true;
    }
    echoRespnse(200, $response);
});
$app->get('/search/:id', 'authenticate', function($trip_id) {
    $response = array();
    $tripId = $trip_id;
    $response[TRIPLIST] = array();
    $response[ERROR] = false;
    $response[MESSAGE] = "OK";
    global $user_region;
    global $user_id;
    $db = new DbHandler($user_region);
    $result = $db->getSpecificTrip($user_id,$tripId);
    $tmp = writeSearchTrips($result);
    if(count($tmp) === 0)
        $response[TRIPLIST] = null;
    else
        $response[TRIPLIST] = $tmp;
    echoRespnse(200, $response);
});

/* * ********************Booking methods***************************************
 * Get
 * Post
 * Put
 * Delete
 * *************************************************************************** */

/** Create a booking with post
 *
 * Method post
 *
 */
$app->post('/booking', 'authenticate', function() use ($app) {
    // check for required params
    verifyRequiredParams(array(TRIP_ID, SEATSRESERVED,PRICE));
    $trip_id = $app->request->post(TRIP_ID);
    $seats = intval($app->request->post(SEATSRESERVED));
    $price = intval($app->request->post(PRICE));
    $from_sub_trip_order = intval($app->request->post(SUBTRIP_ID_DEPARTURE));
    $to_sub_trip_order = intval($app->request->post(SUBTRIP_ID_ARRIVAL));


    global $user_id;
    global $user_region;
    $db = new DbHandler($user_region);
    $bookings = $db->booktrip($user_id,$trip_id, $seats,$price,$from_sub_trip_order,$to_sub_trip_order);
    if($bookings != null){
       //sendBookingList($bookings);
       writeTripsFromRequest($bookings);
    }else{
        $time = date("Y-m-d H:i:s",time() + 61200 );
        //error_log("$time | booking | Failed to CALL rmdbp_bookNewTrip($trip_id, $user_id, $seats,$price, $from_sub_trip_order,$to_sub_trip_order) \n",3,ERROR_LOG_PATH);
        $response = array();
        $response[MESSAGE] = 'Failed to complete booking task';
        $response[ERROR] = true;
        echoRespnse(200, $response);
    }
});

/**
 * commutebooking
 * Specific method for booking commute trips
 */
$app->post('/commutebooking', 'authenticate', function() use ($app) {
    // check for required params
    verifyRequiredParams(array(TRIP_ID, SEATSRESERVED,PRICE,DEPARTURE_TIME));
    $trip_id = $app->request->post(TRIP_ID);
    $seats = intval($app->request->post(SEATSRESERVED));
    $price = intval($app->request->post(PRICE));
    $departureTime = $app->request->post(DEPARTURE_TIME);
    $from_sub_trip_order = intval($app->request->post(SUBTRIP_ID_DEPARTURE));
    $to_sub_trip_order = intval($app->request->post(SUBTRIP_ID_ARRIVAL));


    global $user_id;
    global $user_region;
    $db = new DbHandler($user_region);
    $bookings = $db->bookCommuteTrip($user_id,$trip_id, $seats,$price,$departureTime,$from_sub_trip_order,$to_sub_trip_order);
    if($bookings != null){
       //sendBookingList($bookings);
       writeTripsFromRequest($bookings);
    }else{
        $time = date("Y-m-d H:i:s",time() + 61200 );
        //error_log("$time | booking | Failed to CALL rmdbp_bookCommuteTrip($trip_id, $user_id, $seats,$price,$departureTime, $from_sub_trip_order,$to_sub_trip_order) \n",3,ERROR_LOG_PATH);
        echoRespnse(200, $response);
    }
});


/**
 *
 * managebooking
*/

$app->post('/managebooking', 'authenticate', function() use ($app) {
    // check for required params
    verifyRequiredParams(array(BOOKING_ID, FLAG));
    $book_id = $app->request->post(BOOKING_ID);
    $flag = intval($app->request->post(FLAG));

    global $user_id;
    global $user_region;
    $db = new DbHandler($user_region);
    $trips = $db->updateBookingStatus($user_id, $book_id,$flag);
    if($trips != null){
       //sendBookingList($bookings);
       writeTripsFromRequest($trips);
    }else{
        $time = date("Y-m-d H:i:s",time() + 61200 );
        //error_log("$time | managebooking | Failed to CALL rmdbp_updatebookingstatus($user_id, $book_id,$flag) \n",3,ERROR_LOG_PATH);
        $response = array();
        $response[ERROR] = true;
        $response[MESSAGE] = 'ERROR';
        echoRespnse(200, $response);
    }
});


/** Get all bookings for a particular user
 * Method Get
 *
 *
 * */
$app->get('/booking', 'authenticate', function() {
    global $user_id;
    global $user_region;
    $db = new DbHandler($user_region);
    $bookings = $db->getAllUserBookings($user_id);
    writeTripsFromRequest($bookings);
});

/** Get all previous bookings where no comments has been made
 *  Method Get
 * */
$app->get('/previousbooking', 'authenticate', function() {
    global $user_id;
    global $user_region;
    $db = new DbHandler($user_region);
    $bookings = $db->getPreviousUserBookings($user_id);
    writePreviousTripsFromRequest($bookings,$user_id);
});

/** Put method on /booking
 * Updates a booking by a given user
 * This method allows to update the number of seats purchased
 *
 * updateBooking
 * */
$app->post('/updatebooking', 'authenticate', function() use ($app) {
    // check for required params
    verifyRequiredParams(array(BOOKING_ID,TRIP_ID,SEATSRESERVED));
    $booking_id = intval($app->request->post(BOOKING_ID));
    $seats = intval($app->request->post(SEATSRESERVED));
    $trip_id = intval($app->request->post(TRIP_ID));
    global $user_id;
    global $user_region;
    $db = new DbHandler($user_region);
    $result = $db->updateBooking($user_id,$trip_id, $booking_id, $seats);
    $response = array();
    $response[ERROR] = false;
    $response[MESSAGE] = 'OK';
    if($result === null){
        $response[ERROR] = true;
        $response[MESSAGE] = 'ERROR';
    }
    echoRespnse(201, $response);
});
$app->post('/updateregion', 'authenticate', function() use ($app) {
    // check for required params
    verifyRequiredParams(array(WORLDREGION,COUNTRY,HOMETOWN));
    $town = $app->request->post(HOMETOWN);
    $country = $app->request->post(SEATSRESERVED);
    $region = intval($app->request->post(WORLDREGION));
    $region = validateRegion($country,$region);
    global $user_id;
    global $user_region;
    if($region !== null && $user_region !== $region){
        $db = new DbHandler($region);
        $result = $db->updateRegionAndCreateNewTarget($user_id,$user_region);
        $response = array();
        $response[ERROR] = false;
        $response[MESSAGE] = 'OK';
        if($result === null){
            $response[ERROR] = true;
            $response[MESSAGE] = 'ERROR';
        }
    }

    echoRespnse(201, $response);
});

function sendWelcomeEmailTo($email){
    $subject = 'Bienvenu(e) à la constituante';

    $headers = "From: confirmation@laconstituante.fr \r\n";
    $headers .= "Reply-To: contact@laconstituante.fr \r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
    return mail($email,$subject,LACONSTITUANTE_SUBSCRIBE_EMAIL,$headers);
}
function sendValidationEmailForGroupMembership($email,$firstname,$group_name,$token){
    $subject = 'Welcome to RoadMate';

    $headers = "From: no-reply@roadmate.co.nz \r\n";
    $headers .= "Reply-To: contact@roadmate.co.nz \r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
    $search = array('[GROUP_REQUEST_VALIDATE_URL]','[GROUP_NAME]','[USER_NAME]');
    $replace = array($token,$group_name,$firstname);
    $message = str_replace($search, $replace, ROADMATE_GROUP_REQUEST_TEMPLATE_EMAIL);
    return mail($email,$subject,$message,$headers);
    //return $message;
}
function sendErrorEmail($message){
    $subject = 'Error create Main user';

    $headers = "From: no-reply@roadmate.co.nz \r\n";
    $headers .= "Reply-To: contact@roadmate.co.nz \r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

    return mail('mehdi.tanouti@gmail.com',$subject,$message,$headers);
}
function sendContactEmail($from,$subject,$message){
    $subject = 'Laconstituante Contact : '.$subject;

    $headers = "From: $from \r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

    return mail('mehdi.tanouti@gmail.com',$subject,$message,$headers);
}
$app->post('/contact', function() use ($app) {
    $response = array();
    $response[MESSAGE] = 'BAD';
    $response[ERROR] = true;
    verifyRequiredParams(array(EMAIL,MESSAGE,CAPTCHA));
    $email = $app->request->post(EMAIL);
    $msg = $app->request->post(MESSAGE);
    $captcha = $app->request->post(CAPTCHA);
    if(isset($_SESSION['contact']) && $_SESSION['contact'] == $captcha){
        $headers = "From: $email". "\r\n" .
        "CC: contact@roadmate.co.nz";
        if(mail("mehdi.tanouti@gmail.com,thierry.cosme@gmail.com","User Contact : $email",$msg,$headers)){
            $response[MESSAGE] = 'OK';
            $response[ERROR] = false;
        }else{
            $response[MESSAGE] = 'ERROR';
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time | contact | Failed to send User Contact [$email:$msg] \n",3,ERROR_LOG_PATH);
        }
    }else{
        $response[ERROR] = true;
    }
    echoRespnse(201, $response);
});

/** Delete method on /booking
 * Deletes a booking by a given user
 * This method allows to delete booking
 *
 * deleteBooking
 * */
$app->post('/deletebooking', 'authenticate', function() use ($app) {
    verifyRequiredParams(array(BOOKING_ID));
    global $user_id;
    $booking_id = $app->request->post(BOOKING_ID);
    global $user_region;
    $db = new DbHandler($user_region);
    $response = array();
    $response[ERROR] = false;
    $response[MESSAGE] = 'OK';
    $bookings = $db->cancelBooking($user_id, $booking_id);
    if(!$bookings){
        $response[ERROR] = true;
        $response[MESSAGE] = "ERROR";
        echoRespnse(200, $response);
    }else{
        echoRespnse(201, $response);
    }
});
//This method returns Terms and Conditions when $element_id = 1. Privacy Policy when $element_id = 2
$app->get('/rmtacpp/:id', function($element_id) {
    $response = array();
    $response[ERROR] = false;
    $response[MESSAGE] = 'OK';
    if(intval($element_id) == 1){
        $response[TERMS_AND_CONDITION_PARAM] = ROADMATE_TERMS;
        $response[TERMS_AND_CONDITION_VERSION_PARAM] = ROADMATE_TERMS_VERSION;
        $response[TERMS_AND_CONDITION_DATE_PARAM] = ROADMATE_TERMS_DATE;

    }else{
        $response[PRIVACY_POLICAY_PARAM]= ROADMATE_PRIVACY_POLICY;
        $response[PRIVACY_POLICAY_VERSION_PARAM] = ROADMATE_PRIVACY_POLICY_VERSION;
        $response[PRIVACY_POLICAY_DATE_PARAM] = ROADMATE_PRIVACY_POLICY_DATE;
    }
    $response[MESSAGE] = 'OK';
    echoRespnse(201, $response);
});

/* * ************************ Trip methods ***************************************
 * Get
 * Pot
 * Put
 * Delete
 * *************************************************************************** */

/** 	Listing all trips and associated booking of particual user
 * When no trip is published, the method returns a list of bookings
 * method GET
 * url /trips
 */
$app->get('/trip', 'authenticate', function() {
    global $user_id;
    global $user_region;
    $db = new DbHandler($user_region);
    writeTripsFromRequest($db->getAllUserTrips($user_id));

});
//
$app->get('/previoustrip', 'authenticate', function() {
    global $user_id;
    global $user_region;
    $db = new DbHandler($user_region);
    writePreviousTripsFromRequest($db->getPreviousUserTrips($user_id),$user_id);

});
function writeTripsFromRequest($result){
    $response = array();
    $response[ERROR] = false;
    $response[TRIPLIST] = array();
    while ($trip = $result->fetch_assoc()) {
        $booking = array();
        $bookings = array();
        if(isset($trip['no_seats_available_for'])){
            $response[ERROR] = false;
            $response[TRIPLIST] = null;
            $response[MESSAGE] = 'TRIP_FULL';
        }else{
            //if its the first time or the current id is not equal to the previous
            if (empty($response[TRIPLIST]) || $response[TRIPLIST][count($response[TRIPLIST]) - 1][TRIP_ID] != $trip["trip_public_id"]) {
                //$global_trip[TRIP_ID] = $trip["trip_public_id"];
                global $user_region;
                $db = new DbHandler($user_region);
                $sql_result = $db->getAllSubTripsFromTrip($trip["id"]);
                $global_trip[TRIP_ID] = $trip["trip_public_id"];
                $global_trip[DEPARTURE_NAME] = $trip["departure_name"];
                $global_trip[DEPARTURE_LATITUDE] = $trip["departure_lt"];
                $global_trip[DEPARTURE_LONGITUDE] = $trip["departure_lg"];
                $global_trip[ARRIVAL_NAME] = $trip["arrival_name"];
                $global_trip[ARRIVAL_LATITUDE] = $trip["arrival_lt"];
                $global_trip[ARRIVAL_LONGITUDE] = $trip["arrival_lg"];
                $global_trip[PRICE] = $trip["price"];
                $global_trip[AVAILABLE_SEATS] = $trip["available_seats"];
                $global_trip[DEPARTURE_TIME] = $trip["departure_time"];
                $global_trip[DESCRIPTION] = $trip["description"];
                $global_trip[MODIFIED_AT] = $trip["modified_at"];
                $global_trip[TRIP_PARAM] = $trip["trip_param"];
                $global_trip[PRICE_FROM_LAST_STEP] = $trip["price_from_last_step"];
                $global_trip[SEAT_TO_FIRST_STEP] = $trip["seats_to_first_Step"];
                $global_trip[ARRIVAL_TIME] = $trip["arrival_time"];
                $global_trip[TRIP_DISTANCE] = $trip["trip_distance"];
                $global_trip[TRIP_DURATION] = $trip["trip_duration"];
                $global_trip[TRIP_DISTANCE_FROM_LAST] = $trip["trip_distance_from_previous"];
                $global_trip[TRIP_DURATION_FROM_LAST] = $trip["trip_duration_from_previous"];
                $global_trip[TRIP_VIEW_COUNT] = isset($trip["views"]) ? $trip["views"] : '';
                $global_trip[TRIP_MESSAGE_COUNT] = isset($trip["msg_count"])  ? $trip["msg_count"] : '';
                $global_trip[TRIP_STATUS] = isset($trip["trip_status"])  ? $trip["trip_status"] : '';
                $global_trip[TRIP_COMMUTE] = $trip["trip_is_commute"];
                $global_trip[TRIP_COMMUTE_PATTERN] = $trip["trip_commute_values"];
                $global_trip[TRIP_COMMUTE_END_DATE] = $trip["trip_commute_enddate"];
                $global_trip[TRIP_IS_WOMEN_ONLY] = $trip["isWomenOnly"];
                $global_trip[DEPARTURE_STREET_NUMBER] = $trip["arrival_nb"];
                $global_trip[DEPARTURE_STREET_NAME] = $trip["arrival_st"];
                $global_trip[DEPARTURE_COUNTRY] = $trip["arrival_ct"];
                $global_trip[ARRIVAL_STREET_NUMBER] = $trip["destination_nb"];
                $global_trip[ARRIVAL_STREET_NAME] = $trip["destination_st"];
                $global_trip[ARRIVAL_COUNTRY] = $trip["destination_ct"];

                if($trip['trip_usr_id'] != 'NULL'){
                    $global_trip[USER_ID] = $trip["trip_usr_id"];
                    $global_trip[FIRSTNAME] = $trip["fname"];
                    $global_trip[PHONE] = $trip["phone"];
                    $global_trip[FACEBOOKFRIENDSNUMBER] = $trip["fb_friends_nb"];
                    $global_trip[GENDER] = $trip["gender"];
                    $global_trip[USERPROFILE_URL] = $trip["fb_pic"];
                    $global_trip[RATE_COUNT] = $trip["rate_count"];
                    $global_trip[BIRTHDAY] = $trip["birthday"];
                    $global_trip[RATE] = $trip["rate"];
                    $global_trip[TRIP_COUNT] = $trip["trips_count"];
                    $global_trip[REVIEW_COUNT] = $trip["review_count"];
                    $global_trip[CARMODEL] = $trip["car_model"];
                    $global_trip[CARCOLOR] = $trip["car_color"];
                    $global_trip[CARTYPE] = $trip["car_type"];
                    $global_trip[CARSEATS] = $trip['car_seats'];
                    $global_trip[CARQUALITY] = $trip["car_comfort"];
                }
                if($sql_result != null){
                    $global_trip[SUBTRIP_LIST] = array();
                    $is_there_subtrips = false;
                    while ($subtrip = $sql_result->fetch_assoc()){
                        $temp [TRIP_ID] = $subtrip['parent_trip_id'];
                        $temp [SUBTRIP_ID] = $subtrip['subtrip_id'];
                        $temp [STEP_TOWN_NAME] = $subtrip['step_name'];
                        $temp [STEP_TOWN_LT] = $subtrip['step_lt'];
                        $temp [STEP_TOWN_LG] = $subtrip['step_lg'];
                        $temp [STEP_ORDER] = $subtrip['step_order'];
                        $temp [PRICE_FROM_PREVIOUS] = $subtrip['price_from_previous'];
                        $temp [AVAILABLE_SEATS_TO_NEXT] = $subtrip['available_seats_to_next'];
                        $temp [IS_LAST] = $subtrip['is_last'];
                        $temp [STEP_ORDER] = $subtrip['duration_fromPrevious'];
                        $temp [DURATION_FROM_PREVIOUS] = $subtrip['distance_fromPrevious'];
                        $temp [SUBTRIP_ARRIVAL_TIME] = $subtrip['arrival_time'];
                        array_push($global_trip[SUBTRIP_LIST],$temp);
                        $is_there_subtrips = true;
                    }
                    if(!$is_there_subtrips)
                        $global_trip[SUBTRIP_LIST] = null;
                }
                if ($trip["res_id"] != null) {
                    $booking[BOOKING_ID] = $trip["res_id"];
                    $booking[SEATSRESERVED] = $trip["seats_reserved"];
                    $booking[PRICEPAID] = $trip["price_paid"];
                    $booking[MODIFIED_AT] = $trip["res_modified_at"];
                    $booking[STATUS] = $trip["booking_status"];
                    $booking[FROM_STEP] = $trip["from_step"];
                    $booking[TO_STEP] = $trip["to_step"];
                    if($trip["res_usr_id"] != 'NULL'){
                        $booking[USER_ID] = $trip["res_usr_id"];
                        $booking[FIRSTNAME] = $trip["fname"];
                        $booking[PHONE] = $trip["phone"];
                        $booking[FACEBOOKFRIENDSNUMBER] = $trip["fb_friends_nb"];
                        $booking[GENDER] = $trip["gender"];
                        $booking[USERPROFILE_URL] = $trip["fb_pic"];
                        $booking[RATE_COUNT] = $trip["rate_count"];
                        $booking[BIRTHDAY] = $trip["birthday"];
                        $booking[RATE] = $trip["rate"];
                        $booking[TRIP_COUNT] = $trip["trips_count"];
                        $booking[REVIEW_COUNT] = $trip["review_count"];
                        $booking[CARMODEL] = $trip["car_model"];
                        $booking[CARCOLOR] = $trip["car_color"];
                        $booking[CARTYPE] = $trip["car_type"];
                        $booking[CARSEATS] = $trip['car_seats'];
                        $booking[CARQUALITY] = $trip["car_comfort"];
                    }
                    array_push($bookings, $booking);
                    $global_trip[BOOKINGLIST] = array();
                    $global_trip[BOOKINGLIST] = $bookings;
                } else
                    $global_trip[BOOKINGLIST] = null;

                array_push($response[TRIPLIST], $global_trip);
            }
            else {
                $booking[BOOKING_ID] = $trip["res_id"];
                $booking[SEATSRESERVED] = $trip["seats_reserved"];
                $booking[PRICEPAID] = $trip["price_paid"];
                $booking[MODIFIED_AT] = $trip["res_modified_at"];
                $booking[STATUS] = $trip["booking_status"];
                $booking[FROM_STEP] = $trip["from_step"];
                $booking[TO_STEP] = $trip["to_step"];
                if($trip["res_usr_id"] != 'NULL'){
                    $booking[USER_ID] = $trip["trip_usr_id"];
                    $booking[FIRSTNAME] = $trip["fname"];
                    $booking[PHONE] = $trip["phone"];
                    $booking[FACEBOOKFRIENDSNUMBER] = $trip["fb_friends_nb"];
                    $booking[GENDER] = $trip["gender"];
                    $booking[USERPROFILE_URL] = $trip["fb_pic"];
                    $booking[RATE_COUNT] = $trip["rate_count"];
                    $booking[BIRTHDAY] = $trip["birthday"];
                    $booking[RATE] = $trip["rate"];
                    $booking[TRIP_COUNT] = $trip["trips_count"];
                    $booking[REVIEW_COUNT] = $trip["review_count"];
                    $booking[CARMODEL] = $trip["car_model"];
                    $booking[CARCOLOR] = $trip["car_color"];
                    $booking[CARTYPE] = $trip["car_type"];
                    $booking[CARSEATS] = $trip['car_seats'];
                    $booking[CARQUALITY] = $trip["car_comfort"];
                }
                array_push($response[TRIPLIST][count($response[TRIPLIST]) - 1][BOOKINGLIST], $booking);
            }
        }

    }

    echoRespnse(200, $response);

}

function writePreviousTripsFromRequest($result,$userId){
    $response = array();
    $response[ERROR] = false;
    $response[TRIPLIST] = array();
    while ($trip = $result->fetch_assoc()) {
        $booking = array();
        if(isset($trip['no_seats_available_for'])){
            $response[ERROR] = false;
            $response[TRIPLIST] = null;
            $response[MESSAGE] = 'TRIP_FULL';
        }else{
            //if its the first time or the current id is not equal to the previous
            if (empty($response[TRIPLIST]) || $response[TRIPLIST][count($response[TRIPLIST]) - 1][TRIP_ID] != $trip["trip_public_id"]) {
                //$global_trip[TRIP_ID] = $trip["trip_public_id"];
                $global_trip[COMMENT_LIST] = array();
                global $user_region;
                $db = new DbHandler($user_region);
                $sql_result = $db->getAllSubTripsFromTrip($trip["id"]);
                $comment_results = $db->getAllCommentsForThisTrip($userId,$trip["id"]);
                $global_trip[TRIP_ID] = $trip["trip_public_id"];
                $global_trip[DEPARTURE_NAME] = $trip["departure_name"];
                $global_trip[DEPARTURE_LATITUDE] = $trip["departure_lt"];
                $global_trip[DEPARTURE_LONGITUDE] = $trip["departure_lg"];
                $global_trip[ARRIVAL_NAME] = $trip["arrival_name"];
                $global_trip[ARRIVAL_LATITUDE] = $trip["arrival_lt"];
                $global_trip[ARRIVAL_LONGITUDE] = $trip["arrival_lg"];
                $global_trip[PRICE] = $trip["price"];
                $global_trip[AVAILABLE_SEATS] = $trip["available_seats"];
                $global_trip[DEPARTURE_TIME] = $trip["departure_time"];
                $global_trip[DESCRIPTION] = $trip["description"];
                $global_trip[MODIFIED_AT] = $trip["modified_at"];
                $global_trip[TRIP_PARAM] = $trip["trip_param"];
                $global_trip[PRICE_FROM_LAST_STEP] = $trip["price_from_last_step"];
                $global_trip[SEAT_TO_FIRST_STEP] = $trip["seats_to_first_Step"];
                $global_trip[ARRIVAL_TIME] = $trip["arrival_time"];
                $global_trip[TRIP_DISTANCE] = $trip["trip_distance"];
                $global_trip[TRIP_DURATION] = $trip["trip_duration"];
                $global_trip[TRIP_DISTANCE_FROM_LAST] = $trip["trip_distance_from_previous"];
                $global_trip[TRIP_DURATION_FROM_LAST] = $trip["trip_duration_from_previous"];
                $global_trip[TRIP_VIEW_COUNT] = isset($trip["views"]) ? $trip["views"] : '';
                $global_trip[TRIP_MESSAGE_COUNT] = isset($trip["msg_count"])  ? $trip["msg_count"] : '';
                $global_trip[TRIP_STATUS] = isset($trip["trip_status"])  ? $trip["trip_status"] : '';
                $global_trip[TRIP_COMMUTE] = $trip["trip_is_commute"];
                $global_trip[TRIP_COMMUTE_PATTERN] = $trip["trip_commute_values"];
                $global_trip[TRIP_COMMUTE_END_DATE] = $trip["trip_commute_enddate"];
                $global_trip[TRIP_IS_WOMEN_ONLY] = $trip["isWomenOnly"];
                $global_trip[USER_LIST] = array();
                $user = array();
                $user[USER_ID] = $trip["trip_usr_id"];
                $user[FIRSTNAME] = $trip["fname"];
                $user[PHONE] = $trip["phone"];
                $user[FACEBOOKFRIENDSNUMBER] = $trip["fb_friends_nb"];
                $user[GENDER] = $trip["gender"];
                $user[USERPROFILE_URL] = $trip["fb_pic"];
                $user[RATE_COUNT] = $trip["rate_count"];
                $user[BIRTHDAY] = $trip["birthday"];
                $user[RATE] = $trip["rate"];
                $user[TRIP_COUNT] = $trip["trips_count"];
                $user[REVIEW_COUNT] = $trip["review_count"];
                $user[CARMODEL] = $trip["car_model"];
                $user[CARCOLOR] = $trip["car_color"];
                $user[CARTYPE] = $trip["car_type"];
                $user[CARSEATS] = $trip['car_seats'];
                $user[CARQUALITY] = $trip["car_comfort"];
                $user[USER_IS_DRIVER] = $trip["is_driver"];
                array_push($global_trip[USER_LIST], $user);

                if($sql_result != null){
                    $global_trip[SUBTRIP_LIST] = array();
                    $is_there_subtrips = false;
                    while ($subtrip = $sql_result->fetch_assoc()){
                        $temp [TRIP_ID] = $subtrip['parent_trip_id'];
                        $temp [SUBTRIP_ID] = $subtrip['subtrip_id'];
                        $temp [STEP_TOWN_NAME] = $subtrip['step_name'];
                        $temp [STEP_TOWN_LT] = $subtrip['step_lt'];
                        $temp [STEP_TOWN_LG] = $subtrip['step_lg'];
                        $temp [STEP_ORDER] = $subtrip['step_order'];
                        $temp [PRICE_FROM_PREVIOUS] = $subtrip['price_from_previous'];
                        $temp [AVAILABLE_SEATS_TO_NEXT] = $subtrip['available_seats_to_next'];
                        $temp [IS_LAST] = $subtrip['is_last'];
                        $temp [STEP_ORDER] = $subtrip['duration_fromPrevious'];
                        $temp [DURATION_FROM_PREVIOUS] = $subtrip['distance_fromPrevious'];
                        $temp [SUBTRIP_ARRIVAL_TIME] = $subtrip['arrival_time'];
                        array_push($global_trip[SUBTRIP_LIST],$temp);
                        $is_there_subtrips = true;
                    }
                    if(!$is_there_subtrips)
                        $global_trip[SUBTRIP_LIST] = null;
                }
                if($comment_results != null){
                    $is_there_comments = false;
                    $tmp = array();
                    while ($comment = $comment_results->fetch_assoc()){
                        $tmp [USER_ID] = $comment['public_id'];
                        $tmp [TRIP_ID] = $comment['trip_public_id'];
                        $tmp [CREATED_AT] = $comment['creationDate'];
                        $tmp [RATE] = $comment['rate'];
                        $tmp [COMMENT] = $comment['comment'];
                        array_push($global_trip[COMMENT_LIST],$tmp);
                        $is_there_comments = true;
                    }
                }
                array_push($response[TRIPLIST], $global_trip);
            }
            else {
                $user = array();
                $user[USER_ID] = $trip["trip_usr_id"];
                $user[FIRSTNAME] = $trip["fname"];
                $user[PHONE] = $trip["phone"];
                $user[FACEBOOKFRIENDSNUMBER] = $trip["fb_friends_nb"];
                $user[GENDER] = $trip["gender"];
                $user[USERPROFILE_URL] = $trip["fb_pic"];
                $user[RATE_COUNT] = $trip["rate_count"];
                $user[BIRTHDAY] = $trip["birthday"];
                $user[RATE] = $trip["rate"];
                $user[TRIP_COUNT] = $trip["trips_count"];
                $user[REVIEW_COUNT] = $trip["review_count"];
                $user[CARMODEL] = $trip["car_model"];
                $user[CARCOLOR] = $trip["car_color"];
                $user[CARTYPE] = $trip["car_type"];
                $user[CARSEATS] = $trip['car_seats'];
                $user[CARQUALITY] = $trip["car_comfort"];
                $user[USER_IS_DRIVER] = $trip["is_driver"];
                array_push($response[TRIPLIST][count($response[TRIPLIST]) - 1][USER_LIST], $user);
            }
        }
    }
    echoRespnse(200, $response);

}

/** Update a trip with a given ID
 *
 * request should provide a departure time and price
 * returns response 200 if request is treated correctly
 */
$app->post('/updatetrip', 'authenticate', function() use($app) {
    // check for required params
    verifyRequiredParams(array('new_time', 'new_price'));

    global $user_id;
    $new_time = $app->request->post('new_time');
    $new_price = $app->request->post('new_price');

    global $user_region;
    $db = new DbHandler($user_region);
    $response = array();

    // updating task
    $result = $db->updateTrip($user_id, $trip_id, $new_time, $new_price);
    if ($result) {
        // task updated successfully
        $response[ERROR] = false;
        $response[MESSAGE] = "Task updated successfully";
    } else {
        // task failed to update
        $response[ERROR] = true;
        $response[MESSAGE] = "Task failed to update. Please try again!";
    }
    echoRespnse(200, $response);
});


/** Deleting trip. Users can delete only their trips when nobody has reserved yet
 *
 * method DELETE
 * url /tasks
 */
$app->post('/deletetrip', 'authenticate', function() use($app) {
    verifyRequiredParams(array(TRIP_ID));
    global $user_id;
    $trip_id = $app->request->post(TRIP_ID);
    global $user_region;
    $db = new DbHandler($user_region);
    $response = array();
    $response[ERROR] = false;
    $response[MESSAGE] = "OK";
    $status = 201;
    $result = $db->deleteTrip($user_id, $trip_id);
    if (!$result) {
        $response[ERROR] = true;
        $response[MESSAGE] = "TRIP_NOT_FOUND";
        $status = 200;
    }
    echoRespnse($status, $response);
});


//
$app->post('/rateauser', 'authenticate', function() use($app) {
    verifyRequiredParams(array(TRIP_ID,USER_ID));
    global $user_id;
    $trip_id = $app->request->post(TRIP_ID);
    $ratedUser_id = $app->request->post(USER_ID);
    $givenRate = intval($app->request->post(RATE_VALUE));
    $givenComment = $app->request->post(COMMENT_TXT);
    global $user_region;
    $db = new DbHandler($user_region);
    $response = array();
    $response[ERROR] = false;
    $response[MESSAGE] = "OK";
    $status = 201;
    $result = $db->rateUser($user_id, $trip_id,$ratedUser_id,$givenRate,$givenComment);
    if (!$result) {
        $response[ERROR] = true;
        $response[MESSAGE] = "TRIP_NOT_FOUND";
        $status = 200;
    }
    echoRespnse($status, $response);
});

/** Creating new trip in db
 *
 * method POST
 * params - name
 * url - /trips/
 */
function createSubTrips($user_id,$trip_id,$subtrips){
    global $user_region;
    $db = new DbHandler($user_region);
    if(is_array($subtrips) && count($subtrips) > 0){
        $i = 0;
        foreach($subtrips as $row => $innerArray){
           $step_name = 'Uknown step';
            if(property_exists($innerArray,STEP_TOWN_NAME))
                $step_name = $innerArray->stn;

           $step_lt = 0;
           if(property_exists($innerArray,STEP_TOWN_LT))
                $step_lt = $innerArray->stt;

           $step_lg = 0;
           if(property_exists($innerArray,STEP_TOWN_LG))
                $step_lg = $innerArray->stg;

           $seats_to_next = 0;
           if(property_exists($innerArray,AVAILABLE_SEATS_TO_NEXT))
                $seats_to_next = $innerArray->sts;

           $step_order = $i +1;
           if(property_exists($innerArray,STEP_ORDER))
                $step_order = $innerArray->sto;

           $is_last = 0;
           if(property_exists($innerArray,IS_LAST))
                $is_last = $innerArray->stl;

           $price_from_previous = 0;
           if(property_exists($innerArray,PRICE_FROM_PREVIOUS))
                $price_from_previous = $innerArray->sp;

           $duration_fromPrevious = 0;
           if(property_exists($innerArray,DURATION_FROM_PREVIOUS))
                $duration_fromPrevious = $innerArray->sdu;

           $distance_fromPrevious = $innerArray->sdi;
           if(property_exists($innerArray,DISTANCE_FROM_PREVIOUS))
                $distance_fromPrevious = $innerArray->sdi;

           $arrival_time= 0;
           if(property_exists($innerArray,SUBTRIP_ARRIVAL_TIME))
                $arrival_time= $innerArray->sat;

           if($i < count($subtrips) -1){
               $db->createSubTrip($user_id,$trip_id,$step_name,$step_lt,$step_lg,$seats_to_next,$step_order,$price_from_previous,0,$duration_fromPrevious,$distance_fromPrevious,$arrival_time);
           }
           else{
               $trip_list = $db->createSubTrip($user_id,$trip_id,$step_name,$step_lt,$step_lg,$seats_to_next,$step_order,$price_from_previous,1,$duration_fromPrevious,$distance_fromPrevious,$arrival_time);
               //$r['last_trip'] = $user_id.$trip_id.$step_name.$step_lt.$step_lg.$seats_to_next.$step_order.$price_from_previous.'1';
               return $trip_list;
           }
           $i++;
        }
    }else{
        return null;
    }

}
function createTrip($data,$user_id){
    $response = array();
    $departure_lt = null;
    $departure_lg = null;
    $departure_street_nb =null;
    $departure_street_name = null;
    $departure_country = null;
    $arrival_street_nb =null;
    $arrival_street_name = null;
    $arrival_country = null;
    $passengers = null;
    $arrival_name =null;
    $arrival_lt = null;
    $price = null;
    $departure_time = null;
    $trip_params = null;
    $description = null;
    $isAutomatic = 0;
    //
    $commute_end_date = null;
    $commutePattern = '';
    $arrival_time = '';
    $trip_distance=0;
    $trip_duration=0;
    $trip_duration_from_previous =0;
    $trip_distance_from_previous=0;
    $seats_to_first_Step =0;
    $price_from_last_step =0;
    //Return Trip
    $isReturnTrip = false;
    $returnDate = '';
    $returnDescription = '';
    $returnSeats = 0;
    $return_trip_duration_from_previous = 0;
    $return_arrival_time = '';
    $return_trip_distance_from_previous = 0;
    $return_Steps = array();
    $return_price_from_last_step = 0;
    $trip_women_only = 0;
    $group_id = 'EMPTY';
    //error_log("Creating new trip ... \n",3,ERROR_LOG_PATH);
    if(property_exists($data,RETURN_DEPARTURE_TIME) && isset($data->rdt) && $data->rdt != ''){
        $returnDate = $data->rdt;
        $returnDescription  = $data->rd;
        $returnSeats = $data->rsa;
        $return_trip_duration_from_previous = $data->rtdul;
        $return_trip_distance_from_previous = $data->rtdil;
        $return_arrival_time = $data->rat;
        $return_price_from_last_step = $data->rpls;
        $return_Steps = $data->rsl;
        $isReturnTrip = true;
    }
    if(property_exists($data,DEPARTURE_NAME) && isset($data->dn) && $data->dn != ''){
        $departure_name = $data->dn;
    }else{
        $response[ERROR] = true;
        //TODO Send Email to admin
        $response[MESSAGE] = 'DATA_MISSING' ;
        echoRespnse(500, $response);
        return;
    }
    if(property_exists($data,DEPARTURE_STREET_NUMBER) && isset($data->dnb) && $data->dnb != ''){
        $departure_street_nb =$data->dnb;
    }
    if(property_exists($data,DEPARTURE_STREET_NAME) && isset($data->dst) && $data->dst != ''){
        $departure_street_name = $data->dst;
    }
    if(property_exists($data,DEPARTURE_COUNTRY) && isset($data->dct) && $data->dct != ''){
        $departure_country = $data->dct;
    }
    if(property_exists($data,DEPARTURE_LATITUDE) && isset($data->dl) && $data->dl != ''){
        $departure_lt = $data->dl;
    }
    if(property_exists($data,DEPARTURE_LONGITUDE) && isset($data->dg) && $data->dg != '')
        $departure_lg = $data->dg;
    if(property_exists($data,AVAILABLE_SEATS) && isset($data->sa) && $data->sa != '')
        $passengers = $data->sa;
    if(property_exists($data,ARRIVAL_NAME) && isset($data->an) && $data->an != '')
        $arrival_name = $data->an;
    if(property_exists($data,ARRIVAL_STREET_NUMBER) && isset($data->anb) && $data->anb != '')
        $arrival_street_nb = $data->anb;
    if(property_exists($data,ARRIVAL_STREET_NAME) && isset($data->ast) && $data->ast != '')
        $arrival_street_name = $data->ast;
    if(property_exists($data,ARRIVAL_COUNTRY) && isset($data->act) && $data->act != '')
        $arrival_country = $data->act;
    if(property_exists($data,ARRIVAL_LATITUDE) && isset($data->al) && $data->al != '')
        $arrival_lt = $data->al;
    if(property_exists($data,ARRIVAL_LONGITUDE) && isset($data->ag) && $data->ag != '')
        $arrival_lg = $data->ag;
    if(property_exists($data,PRICE) && isset($data->pr) && $data->pr != '')
        $price = $data->pr;
    if(property_exists($data,DEPARTURE_TIME) && isset($data->dt) && $data->dt != '')
        $departure_time = $data->dt;
    if(property_exists($data,TRIP_PARAM) && isset($data->tp) && $data->tp != '')
        $trip_params = $data->tp;
    if(property_exists($data,DESCRIPTION) && isset($data->d) )
        $description = $data->d;
    if(property_exists($data,ISAUTOMATICBOOKING) && isset($data->ta) && $data->ta != '')
        $isAutomatic = $data->ta ? 1 : 0;
    //////////
    if(property_exists($data,ARRIVAL_TIME) && isset($data->at) && $data->at != '')
        $arrival_time = $data->at;
    if(property_exists($data,TRIP_DISTANCE) && isset($data->tdi) && $data->tdi != '')
        $trip_distance = $data->tdi;
    if(property_exists($data,TRIP_DURATION) && isset($data->tdu) && $data->tdu != '')
        $trip_duration = $data->tdu;
    if(property_exists($data,TRIP_DISTANCE_FROM_LAST) && isset($data->tdil) && $data->tdil != '')
        $trip_duration_from_previous = $data->tdil;
    if(property_exists($data,TRIP_DURATION_FROM_LAST) && isset($data->tdul) && $data->tdul != '')
        $trip_distance_from_previous = $data->tdul;
    if(property_exists($data,PRICE_FROM_LAST_STEP) && isset($data->pls) && $data->pls != '')
        $price_from_last_step = $data->pls;
    if(property_exists($data,SEAT_TO_FIRST_STEP) && isset($data->sfs) && $data->sfs != '')
        $seats_to_first_Step = $data->sfs;
    if(property_exists($data,TRIP_COMMUTE_PATTERN) && isset($data->tcp) && $data->tcp != '')
        $commutePattern = $data->tcp;
    if(property_exists($data,TRIP_COMMUTE_END_DATE) && isset($data->tced) && $data->tced != '')
        $commute_end_date = $data->tced;
    if(property_exists($data,TRIP_IS_WOMEN_ONLY) && isset($data->two) && $data->two != '')
        $trip_women_only = $data->two;
    if(property_exists($data,GROUP_ID) && isset($data->gid) && $data->gid)
        $group_id = $data->gid;
    /////////
    if(is_array($commutePattern)){
        $commutePattern = '';
    }
    //error_log("Finished processing data form ... \n",3,ERROR_LOG_PATH);
    global $user_region;
    $db = new DbHandler($user_region);
    $trip_list = array();
    $r = array();
    //Check whether the trip is woman only
    $isWomenOnlyAndEligiblleForIt = 0;
    if($trip_women_only && intval($trip_women_only) === 1){
        //Check if the user is eligible to Women only trips
        $isWomenOnlyAndEligiblleForIt = $db->isWomenOnlyAndEligiblleForIt($user_id);
    }
    if(property_exists($data,SUBTRIP_LIST) && isset($data->sl) && $data->sl != null){
        //addTripAnd get trip_id
        $trip_id = $db->createTrip($user_id, $departure_name, $departure_lt, $departure_lg, $arrival_name, $arrival_lt, $arrival_lg, $price, $departure_time, $description,$passengers,$trip_params,$isAutomatic,$arrival_time,$trip_distance,$trip_duration,$trip_duration_from_previous,$trip_distance_from_previous,1,$price_from_last_step,$seats_to_first_Step,$commute_end_date,$commutePattern,$isWomenOnlyAndEligiblleForIt,$group_id,$departure_street_nb ,$departure_street_name,$departure_country,$arrival_street_nb,$arrival_street_name,$arrival_country);
        if($trip_id != null && $trip_id >0){
            $subtrips = $data->sl;
            $trip_list =  createSubTrips($user_id,$trip_id,$subtrips);
            if($isReturnTrip){
                if(count($return_Steps) > 0){
                    $trip_id = $db->createTrip($user_id, $arrival_name, $arrival_lt, $arrival_lg,$departure_name, $departure_lt, $departure_lg, $price, $returnDate, $returnDescription,$returnSeats,$trip_params,$isAutomatic,$return_arrival_time,$trip_distance,$trip_duration,$return_trip_duration_from_previous,$return_trip_distance_from_previous,1,$return_price_from_last_step,$returnSeats,$commute_end_date,$commutePattern,$isWomenOnlyAndEligiblleForIt,$group_id,$departure_street_nb ,$departure_street_name,$departure_country,$arrival_street_nb,$arrival_street_name,$arrival_country);
                    if($trip_id != null && $trip_id >0){
                        $trip_list = createSubTrips($user_id, $trip_id, $return_Steps);
                    }else{
                        $response[MESSAGE] = "ERROR";
                    }
                }else{
                    $trip_id = $db->createTrip($user_id, $arrival_name, $arrival_lt, $arrival_lg,$departure_name, $departure_lt, $departure_lg, $price, $returnDate, $returnDescription,$returnSeats,$trip_params,$isAutomatic,$return_arrival_time,$trip_distance,$trip_duration,$return_trip_duration_from_previous,$return_trip_distance_from_previous,0,$return_price_from_last_step,$seats_to_first_Step,$commute_end_date,$commutePattern,$isWomenOnlyAndEligiblleForIt,$group_id,$departure_street_nb ,$departure_street_name,$departure_country,$arrival_street_nb,$arrival_street_name,$arrival_country);
                }
            }
        }
        else{
            $response = array();
            $response[ERROR] = true;
            $response[MESSAGE] = "ERROR";
            echoRespnse (200, $response);
        }
    }
    else{
        //error_log("Finished processing data form ... \n",3,ERROR_LOG_PATH);
        //error_log("New Version | CALL rmdbp_createTrip( '$departure_name', $departure_lt, $departure_lg, '$arrival_name', $arrival_lt, $arrival_lg, $price, $user_id, '$departure_time','$description',$passengers,'$trip_params',$isAutomatic,'$arrival_time',$trip_distance,$trip_duration,$trip_duration_from_previous,$trip_distance_from_previous,0,$price_from_last_step,$seats_to_first_Step,'$commute_end_date','$commutePattern',0,$isWomenOnlyAndEligiblleForIt,'$group_id') \n",3,ERROR_LOG_PATH);
        $trip_list = $db->createTrip($user_id, $departure_name, $departure_lt, $departure_lg, $arrival_name, $arrival_lt, $arrival_lg, $price, $departure_time, $description,$passengers,$trip_params,$isAutomatic,$arrival_time,$trip_distance,$trip_duration,$trip_duration_from_previous,$trip_distance_from_previous,0,$price_from_last_step,$seats_to_first_Step,$commute_end_date,$commutePattern,$isWomenOnlyAndEligiblleForIt,$group_id,$departure_street_nb ,$departure_street_name,$departure_country,$arrival_street_nb,$arrival_street_name,$arrival_country);
        if($isReturnTrip){
            $trip_list = $db->createTrip($user_id, $arrival_name, $arrival_lt, $arrival_lg,$departure_name, $departure_lt, $departure_lg, $price, $returnDate, $returnDescription,$returnSeats,$trip_params,$isAutomatic,$return_arrival_time,$trip_distance,$trip_duration,$return_trip_duration_from_previous,$return_trip_distance_from_previous,0,$return_price_from_last_step,$seats_to_first_Step,$commute_end_date,$commutePattern,$isWomenOnlyAndEligiblleForIt,$group_id,$departure_street_nb ,$departure_street_name,$departure_country,$arrival_street_nb,$arrival_street_name,$arrival_country);
         }
    }
    return $trip_list;
}
$app->post('/trip', 'authenticate', function() use ($app) {
    // check for required params
    //verifyRequiredParams(array(DEPARTURE_NAME,AVAILABLE_SEATS, DEPARTURE_LATITUDE,DEPARTURE_LONGITUDE, ARRIVAL_NAME, ARRIVAL_LATITUDE, ARRIVAL_LONGITUDE, PRICE, DEPARTURE_TIME,TRIP_PARAM,ISAUTOMATICBOOKING));
    verifyRequiredParams(array('json'));
    $data = json_decode($app->request->post("json"));
    global $user_id;
    $trip_list = createTrip($data,$user_id);
    if($trip_list != null && count($trip_list) > 0)
        writeTripsFromRequest($trip_list);
    else{
        //echoRespnse (500, $r);
        // TODO DO something here.. catch the parameters and send them to log

    }

});

/* * *************************** Message Methods *******************************************
 * Get
 * Put
 * Post
 * delete
 * *************************************************************************************** */

$app->get('/message/:id', 'authenticate', function($trip_id) {
    $response = array();
    global $user_region;
    $db = new DbHandler($user_region);
    $tripId = $trip_id;
    $response[MESSAGE_LIST] = array();
    $response[ERROR] = false;
    $response[MESSAGE] = null;
    global $user_id;
    // fetching all user tasks
    $conversations = $db->getUserConversations($tripId);
    if ($conversations != null) {
        $response[ERROR] = false;
        while ($conversation = $conversations->fetch_assoc()) {
            $temp = array();
            $temp[ISMESSAGE_FROM_CURRENT] = $conversation["usr_id"] == $user_id;
            $temp[FIRSTNAME] = $conversation["fname"];
            $temp[USERPROFILE_URL] = $conversation["fb_pic"];
            $temp[MESSAGE_TXT] = $conversation["message"];
            $temp[MESSAGE_READ] = $conversation["read_at"];
            $temp[CREATED_AT] = $conversation["create_at"];
            $temp[MESSAGE_STATUS] = $conversation["msg_status"];
            array_push($response[MESSAGE_LIST], $temp);
        }
        echoRespnse(200, $response);
    }
    else {
        $response[ERROR] = true;
        $response[MESSAGE] = "No elements found";
        echoRespnse(200, $response);
    }
});


$app->post('/message', 'authenticate', function() use ($app) {
    // check for required params
    verifyRequiredParams(array(TRIP_ID, MESSAGE_TXT));

    $response = array();
    $on_trip_id = $app->request->post(TRIP_ID);
    $msg = $app->request->post(MESSAGE_TXT);
    $response[MESSAGE_LIST] = array();

    global $user_id;
    global $user_region;
    $db = new DbHandler($user_region);
    //$response[''] = "call rmdbp_sendNewMessage($on_trip_id,$user_id,$msg)";
    // creating new task
    $group_conversation = $db->createMessage($user_id, $on_trip_id,$msg);
    if ($group_conversation != null) {
        $response[ERROR] = false;
        $response[MESSAGE] = "OK";
        while ($conversation = $group_conversation->fetch_assoc()) {
            $temp = array();
            $temp[ISMESSAGE_FROM_CURRENT] = intval($conversation["usr_id"]) == intval($user_id);
//            //TO BE DELETED
//            $temp['sender'] = intval($conversation["usr_id"]);
//            $temp['currentUser'] = intval($user_id);
//            //TO BE DELETED
            $temp[FIRSTNAME] = $conversation["fname"];
            $temp[USERPROFILE_URL] = $conversation["fb_pic"];
            $temp[MESSAGE_TXT] = $conversation["message"];
            $temp[MESSAGE_READ] = $conversation["read_at"];
            $temp[CREATED_AT] = $conversation["create_at"];
            $temp[MESSAGE_STATUS] = $conversation["msg_status"];
            array_push($response[MESSAGE_LIST], $temp);
        }
        echoRespnse(201, $response);
    } else {
        $response[ERROR] = true;
        $response[MESSAGE] = "Failed to send message. Please try again";
        echoRespnse(200, $response);
    }
});

$app->post('/car', 'authenticate', function() use ($app) {
    // check for required params
    verifyRequiredParams(array(CARMODEL, CARSEATS,CARCOLOR,CARTYPE,CARQUALITY));

    $response = array();
    $carmodel = $app->request->post(CARMODEL);
    $carseats = intval($app->request->post(CARSEATS));
    $carcolor = $app->request->post(CARCOLOR);
    $cartype = $app->request->post(CARTYPE);
    $carquality = $app->request->post(CARQUALITY);
    $response[MESSAGE] = 'OK';
    $response[ERROR] = false;
    global $user_id;
    global $user_region;
    $db = new DbHandler($user_region);

    // creating new task
    $ret = $db->updateUserCar($user_id,$carmodel,$carseats,$carcolor,$cartype,$carquality);
    if ($ret == 1) {
        $response[ERROR] = false;
        $response[MESSAGE] = "OK";
        echoRespnse(201, $response);
    } else {
        $response[ERROR] = true;
        $response[MESSAGE] = "UPDATE_CAR_FAILED";
        echoRespnse(200, $response);
    }
});

$app->post('/acceptterms', 'authenticate', function() use ($app) {
    // check for required params
    verifyRequiredParams(array(USER_TERMS_ACCEPTED_VERSION));
    global $user_id;
    $response = array();
    $version = $app->request->post(USER_TERMS_ACCEPTED_VERSION);
    $response[MESSAGE] = 'OK';
    $response[ERROR] = false;
    global $user_region;
    $db = new DbHandler($user_region);
    // creating new task
    $ret = $db->acceptTermsAndConditions($user_id,$version);
    if($ret) {
        echoRespnse(201, $response);
    }else {
        $response[ERROR] = true;
        $response[MESSAGE] = "ERROR";
        echoRespnse(200, $response);
    }
});

$app->post('/uploadusrimg',  'authenticate', function() use ($app)  {
    $response = array();
    $response[ERROR] = false;
    $status = 201;
    global $user_id;
    $response[MESSAGE] = 'OK';
    if (!isset($_FILES['upload']) && (!isset($_FILES['upload']['error']) || is_array($_FILES['upload']['error']))) {
        $response[ERROR] = true;
        $response[MESSAGE] = 'EMPTY';
        echoRespnse(200, $response);
    }else{
        switch ($_FILES['upload']['error']) {
            case UPLOAD_ERR_OK:
                //$response[ERROR] = true;
                $response[MESSAGE] = 'UPLOAD_ERR_OK';
                $status = 201;
                break;
            case UPLOAD_ERR_NO_FILE:
                $response[ERROR] = true;
                $response[MESSAGE] = 'UPLOAD_ERR_NO_FILE';
                $status = 400;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $response[ERROR] = true;
                $response[MESSAGE] = 'UPLOAD_ERR_FORM_SIZE';
                $status = 400;
            default:
                $response[ERROR] = true;
                $response[MESSAGE] = 'UNKOWN_ERROR';
                $status = 400;
        }
        //$response['size'] = $_FILES['upload']['size'];

        if ($_FILES['upload']['size'] > 5000000) {
            $response[ERROR] = true;
            $response[MESSAGE] = 'SIZE_EXCEEDED';
            $status = 400;
        }
        if(!$response[ERROR]){
            $imgs = array();
            $file = $_FILES['upload'];
            $path = $_FILES['upload']['name'];
            $user_pi = $_POST['uid'];
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            //$response['ext'] = $ext;
            if ($file['error'] === 0 && ($ext === 'png' || $ext === 'jpg' || $ext === 'jpeg' || $ext === 'JPG' || $ext === 'JPEG' )) {
                if(file_exists(IMG_FOLDER . $user_pi.$ext)) {
                    chmod(IMG_FOLDER . $user_pi.$ext,0755); //Change the file permissions if allowed
                    unlink(IMG_FOLDER . $user_pi.$ext); //remove the file
                }
                $manipulator = new ImageManipulator($file['tmp_name']);
                $manipulator->resample(300, 300);
                if($manipulator->save(IMG_FOLDER . $user_pi.'.'.$ext,strcasecmp($ext, 'jpg') == 0 ? IMAGETYPE_JPEG : IMAGETYPE_PNG))
                {
                    global $user_region;
                    $db = new DbHandler($user_region);
                    $db->updateUserPicture($user_id,'./usrimg/' . $user_pi.'.'.$ext);
                    $response[USERPROFILE_URL] =  './usrimg/' .$user_pi.'.'.$ext;
                }else{
                    $response[ERROR] = true;
                    $response[MESSAGE] = 'UNABLE TO WRITE FILE';
                    $status = 400;
                }
            }else{
                $response[ERROR] = true;
                $response[MESSAGE] = 'EXTENSION_FAULT';
                $status = 400;
            }
        }else{
            $code = $response[MESSAGE];
            error_log("Upload pic faild with code $code \n",3,ERROR_LOG_PATH);
        }
    }
    echoRespnse($status , $response);
});
function uploadPictureFor($user_id,$isUser){
    $response = array();
    $response[ERROR] = false;
    if (!isset($_FILES['upload']) && (!isset($_FILES['upload']['error']) || is_array($_FILES['upload']['error']))) {
        $response[ERROR] = true;
        $response[MESSAGE] = 'EMPTY';
        echoRespnse(200, $response);
    }else{
        switch ($_FILES['upload']['error']) {
            case UPLOAD_ERR_OK:
                //$response[ERROR] = true;
                $response[MESSAGE] = 'UPLOAD_ERR_OK';
                $status = 201;
                break;
            case UPLOAD_ERR_NO_FILE:
                $response[ERROR] = true;
                $response[MESSAGE] = 'UPLOAD_ERR_NO_FILE';
                $status = 400;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $response[ERROR] = true;
                $response[MESSAGE] = 'UPLOAD_ERR_FORM_SIZE';
                $status = 400;
            default:
                $response[ERROR] = true;
                $response[MESSAGE] = 'UNKOWN_ERROR';
                $status = 400;
        }
        if ($_FILES['upload']['size'] > 5000000) {
            $response[ERROR] = true;
            $response[MESSAGE] = 'SIZE_EXCEEDED';
            $status = 400;
        }
        if(!$response[ERROR]){
            $file = $_FILES['upload'];
            $path = $_FILES['upload']['name'];
            if($isUser){
                $folder = IMG_FOLDER;
                $pic_name = $_POST['uid'];
            }else{
                $folder = GROUP_IMG_FOLDER;
                $pic_name = md5($_POST['gn']);
            }
            //$user_pi =
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            if ($file['error'] === 0 && ($ext === 'png' || $ext === 'jpg' || $ext === 'jpeg' || $ext === 'JPG' || $ext === 'JPEG' )) {
                if(file_exists($folder . $pic_name.$ext)) {
                    chmod($folder . $pic_name.$ext,0755); //Change the file permissions if allowed
                    unlink($folder . $pic_name.$ext); //remove the file
                }
                $manipulator = new ImageManipulator($file['tmp_name']);
                $manipulator->resample(300, 300);

                if($manipulator->save($folder . $pic_name.'.'.$ext,strcasecmp($ext, 'jpg') == 0 ? IMAGETYPE_JPEG : IMAGETYPE_PNG))
                {
                    if($isUser){
                        global $user_region;
                        $db = new DbHandler($user_region);
                        $db->updateUserPicture($user_id,'./usrimg/' . $pic_name.'.'.$ext);
                        $response[USERPROFILE_URL] =  './usrimg/' .$pic_name.'.'.$ext;
                    }else{
                        $response[GROUP_PICTURE] = './grpimg/'.$pic_name.'.'.$ext;
                    }

                }else{
                    $response[ERROR] = true;
                    $response[MESSAGE] = 'UNABLE TO WRITE FILE';
                    $status = 400;
                }


            }else{
                $response[ERROR] = true;
                $response[MESSAGE] = 'EXTENSION_FAULT';
                $status = 400;
            }
        }
    }
    return $response;
}
$app->post('/uploadusrimgcordova',  'authenticate', function() use ($app)  {
    $response = array();
    $response[ERROR] = false;
    global $user_id;
    $response[MESSAGE] = 'OK';
    if ($_FILES['upload']['size'] > 5000000) {
        $response[ERROR] = true;
        $response[MESSAGE] = 'SIZE_EXCEEDED';
        echoRespnse(401 , $response);
    } else if ($_FILES['upload']['size'] < 1) {
        $response[ERROR] = true;
        $response[MESSAGE] = 'FILE_EMPTY';
        echoRespnse(401 , $response);
    }
    if(!$response[ERROR]){
        $file = $_FILES['upload'];
        $path = $_FILES['upload']['name'];
        $user_pi = $_POST['uid'];
        $ext = '.jpg';
        if ($file['error'] === 0 ) {
            if(file_exists(IMG_FOLDER . $user_pi.$ext)) {
                 chmod(IMG_FOLDER . $user_pi.$ext,0755); //Change the file permissions if allowed
                 unlink(IMG_FOLDER . $user_pi.$ext); //remove the file
            }
            $manipulator = new ImageManipulator($file['tmp_name']);
            $manipulator->resample(300, 300);
            //if(move_uploaded_file($file['tmp_name'],IMG_FOLDER . $user_pi.'.'.$ext) === true)
            if($manipulator->save(IMG_FOLDER . $user_pi.$ext,IMAGETYPE_JPEG))
            {
                global $user_region;
                $db = new DbHandler($user_region);
                $db->updateUserPicture($user_id,'./usrimg/' . $user_pi.$ext);
                $response[USERPROFILE_URL] =  './usrimg/' .$user_pi.$ext;
                echoNonJSonRespnse(201 , $response);
            }else{
                error_log('\n File could not be saved : ' .$manipulator->getWidth(),3,ERROR_LOG_PATH);
                $response[ERROR] = true;
                $response[MESSAGE] = 'UNABLE TO WRITE FILE';
                echoRespnse(20 , $response);
            }
        }else{
            $response[ERROR] = true;
            $response[MESSAGE] = 'EXTENSION_FAULT';
            echoRespnse(401 , $response);
        }
    }


});
$app->post('/approveRequest', function() use ($app){
    verifyRequiredParams(array(USER_FEMALE_REQUEST_ID));
    $requestId = $app->request->post(USER_FEMALE_REQUEST_ID);
    $status = 200;
    $response = array();
    $response[MESSAGE] = "OK";
    $response[ERROR] = false;
    global $user_region;
    $db = new DbHandler($user_region);
    $response = $db->approveFemaleRequests($requestId);
    if($response == NULL){
        $response[MESSAGE] = "NOK";
        $response[ERROR] = true;
    }
    echoRespnse($status, $response);

});

$app->get('/pullComments','authenticate', function() {
    $response = array();
    $response[ERROR] = false;
    $response[MESSAGE] = "OK";
    $response[COMMENT_LIST] = array();
    global $user_region;
    global $user_id;
    $db = new DbHandler($user_region);

    $results = $db->pullNotificationOfType(COMMENT,$user_id);
    if($results){
        while($result = $results->fetch_assoc()){
            $temp = array();
            $temp[FIRSTNAME] = $result["fname"];
            $temp[USERPROFILE_URL] = $result["fb_pic"];
            $temp[MESSAGE_TXT] = $result["comment"];
            $temp[CREATED_AT] = $result["creationDate"];
            $temp[RATE] = $result["rate"];
            array_push($response[COMMENT_LIST], $temp);
        }
    }else{

    }
    echoRespnse(200, $response);
});
$app->get('/pullTrip','authenticate', function() {
    global $user_region;
    global $user_id;
    $db = new DbHandler($user_region);
    writeTripsFromRequest($db->pullNotificationOfType(TRIP_UPDATED,$user_id));
});
$app->get('/pullBooking','authenticate', function() {
    global $user_id;
    global $user_region;
    $db = new DbHandler($user_region);
    writeTripsFromRequest($db->pullNotificationOfType(BOOKING,$user_id));
});
//
$app->get('/pullMessages','authenticate', function() {

    $response = array();
    global $user_region;
    $db = new DbHandler($user_region);
    $response[MESSAGE_LIST] = array();
    $response[ERROR] = false;
    $response[MESSAGE] = null;
    global $user_id;
    // fetching all user tasks
    $conversations = $db->pullNotificationOfType(QUESTION,$user_id);
    if ($conversations != null) {
        $response[ERROR] = false;
        while ($conversation = $conversations->fetch_assoc()) {
            $temp = array();
            $temp[ISMESSAGE_FROM_CURRENT] = $conversation["usr_id"] == $user_id;
            $temp[FIRSTNAME] = $conversation["fname"];
            $temp[USERPROFILE_URL] = $conversation["fb_pic"];
            $temp[MESSAGE_TXT] = $conversation["message"];
            $temp[MESSAGE_READ] = $conversation["read_at"];
            $temp[CREATED_AT] = $conversation["create_at"];
            $temp[MESSAGE_STATUS] = $conversation["msg_status"];
            array_push($response[MESSAGE_LIST], $temp);
        }
        echoRespnse(200, $response);
    }
    else {
        $response[ERROR] = true;
        $response[MESSAGE] = "EMPTY";
        echoRespnse(200, $response);
    }

});
$app->get('/getNotifications','authenticate', function() {
    $response = array();
    global $user_region;
    $db = new DbHandler($user_region);
    $response[NOTIFICATION_LIST] = array();
    $response[ERROR] = false;
    $response[MESSAGE] = "OK";
    global $user_id;
    $notificationList = $db->get_NotificationList($user_id);
    if ($notificationList) {
        $response[ERROR] = false;
        while ($notification = $notificationList->fetch_assoc()) {
            $temp = array();
            $temp[NOTIFICATION_ID] = $notification["pk_not_id"];
            $temp[OBJECT_TYPE] = $notification["object_type"];
            $temp[OBJECT_ID] = $notification["object_id"];
            $temp[NOTIFICATION_CREATIONDATE] = $notification["created_at"];
            array_push($response[NOTIFICATION_LIST], $temp);
        }
        echoRespnse(200, $response);
    }
    else {
        $response[ERROR] = true;
        $response[MESSAGE] = "EMPTY";
        echoRespnse(200, $response);
    }
});

$app->post('/updateNotifications','authenticate', function()  use ($app) {
    verifyRequiredParams(array(NOTIFICATION_ID));
    $notificationIds = $app->request->post(NOTIFICATION_ID);
    $response = array();

    global $user_region;
    $db = new DbHandler($user_region);
    global $user_id;
    $result = $db->update_NotificationList($user_id,$notificationIds);
    if ($result) {
        $response[ERROR] = false;
        echoRespnse(200, $response);
    }
    else {
        $response[ERROR] = true;
        $response[MESSAGE] = "ERROR";
        echoRespnse(200, $response);
    }
});

$app->post('/validateGroupMembership','authenticate', function()  use ($app) {
    verifyRequiredParams(array(GROUP_ID,GROUP_USER_ID));
    $response = array();
    $user_to_validate = $app->request->post(GROUP_USER_ID);
    $group_to_join = $app->request->post(GROUP_ID);
    global $user_region;
    $db = new DbHandler($user_region);
    global $user_id;
    $result = $db->validateGroupMembership($user_id,$user_to_validate,$group_to_join);
    if ($result) {
        $response = getPendingGroupRequests($result);
        $response[ERROR] = false;
        $response[MESSAGE] = "OK";
        echoRespnse(200, $response);
    }
    else {
        $response[ERROR] = true;
        $response[MESSAGE] = "ERROR";
        echoRespnse(200, $response);
    }
});
$app->post('/validateGroupMembershiptoken', function()  use ($app) {
    verifyRequiredParams(array(GROUP_VALIDATION_TOKEN));
    $response = array();
    $encoded = urldecode($app->request->post(GROUP_VALIDATION_TOKEN));
    $decoded = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5(TOKEN_ENCRIPTION_KEY), base64_decode($encoded), MCRYPT_MODE_CBC, md5(md5(TOKEN_ENCRIPTION_KEY))), "\0");
    $parts = explode('-', $decoded);
    if(is_array($parts) && count($parts) === 4){
        $group_id = $parts[0];
        $user_public_id = $parts[2];
        $token = $parts[1];
        $region = $parts[3];
        $db = new DbHandler($region);
        if($db){
            $result = $db->validateGroupMembershipByToken($group_id,$user_public_id,$token);
            if ($result === 1) {
                $response[ERROR] = false;
                $response[MESSAGE] = "OK";
            }else{
                $response[ERROR] = true;
                $response[MESSAGE] = "INCORRECT_TOKEN1";
            }
        }else{
            $response[ERROR] = true;
            $response[MESSAGE] = "INCORRECT_TOKEN2";
        }
    }else{
        $response[MESSAGE] = "INCORRECT_TOKEN3";
    }

    echoRespnse(200, $response);
});
$app->post('/searchTripsInGroup', function()  use ($app) {
    verifyRequiredParams(array(GROUP_ID,REGION_ID));
    $response = array();
    $groupId = $app->request->post(GROUP_ID);
    $regionId = intval($app->request->post(REGION_ID));
    $db = new DbHandler($regionId === 7 ? 'au' : 'nz');
    $tripResults = $db->searchTripsInGroup($groupId);
    $response[TRIPLIST] = $tripResults;
    $response[ERROR] = false;
    $response[MESSAGE] = "OK";
    echoRespnse(200, $response);
});

function getUserGroups($user_id,$region,$r=null){
    $response = array();
    if(!$r){
        $db = new DbHandler($region);
        $results = $db->getUserGroups($user_id);
    }else{
        $results = $r;
    }
    if ($results) {
        while($result = $results->fetch_assoc()){
            $tmp = array();
            $tmp[GROUP_ID] = $result['group_public_id'];
            $tmp[GROUP_NAME] = $result['group_name'];
            $tmp[GROUP_DESCRIPTION] = $result['group_description'];
            $tmp[GROUP_MEMBERS_COUNT] = $result['member_count'];
            $tmp[GROUP_IS_CORPORATE] = $result['group_email_suffix'];
            $tmp[GROUP_IS_PUBLIC] = $result['group_is_public'];
            $tmp[GROUP_MEMBERSHIP_STATUS] = $result['request_status'];
            $tmp[GROUP_TYPE] = $result['group_type'];
            $tmp[GROUP_PICTURE] = $result['group_pic'];
            $tmp[GROUP_NOTIFICATION_ENABLED] = $result['notify'];
            $tmp[GROUP_USER_ROLE] = $result['user_role'];
            array_push($response,$tmp);
        }
    }
    return $response;
}

$app->get('/getUserGroups','authenticate', function()  use ($app) {
    $response = array();
    $response[GROUP_LIST] = array();
    global $user_region;
    global $user_id;
    $response[GROUP_LIST] = getUserGroups($user_id,$user_region);
    $response[ERROR] = false;
    $response[MESSAGE] = "OK";
    echoRespnse(201, $response);

});
$app->post('/searchGroups', function()  use ($app) {
    $response = array();
    $response[GROUP_LIST] = array();
    verifyRequiredParams(array(GROUP_SEARCH_STRING));
    $search = $app->request->post(GROUP_SEARCH_STRING);
    if(isset($_SESSION['user_region'])){
        $db = new DbHandler($_SESSION['user_region']);
        $results = $db->searchGroups($search);
        if ($results) {
            while($result = $results->fetch_assoc()){
                $tmp = array();
                $tmp[GROUP_ID] = $result['group_public_id'];
                $tmp[GROUP_NAME] = $result['group_name'];
                $tmp[GROUP_DESCRIPTION] = $result['group_description'];
                $tmp[GROUP_MEMBERS_COUNT] = $result['member_count'];
                $tmp[GROUP_IS_PUBLIC] = $result['group_is_public'];
                $tmp[GROUP_IS_CORPORATE] = $result['group_email_suffix'];
                $tmp[GROUP_TYPE] = $result['group_type'];
                $tmp[GROUP_PICTURE] = $result['group_pic'];
                array_push($response[GROUP_LIST],$tmp);
            }
        }
    }
    $response[ERROR] = false;
    $response[MESSAGE] = "OK";
    echoRespnse(201, $response);

});
$app->get('/getPendingGroupRequests','authenticate', function()  use ($app) {
    global $user_region;
    global $user_id;
    $db = new DbHandler($user_region);
    $results = $db->getPendingGroupRequests($user_id);
    $response = getPendingGroupRequests($results);
    $response[ERROR] = false;
    $response[MESSAGE] = "OK";
    echoRespnse(200, $response);
});

function getPendingGroupRequests($results){
    $response = array();
    $response[GROUP_USER_LIST] = array();
    if($results){
        while($result = $results->fetch_assoc()){
            $tmp = array();
            $tmp[USER] = array();
            $tmp[GROUP] = array();

            $tmp[USER][USER_ID] = $result['public_id'];
            $tmp[USER][FIRSTNAME] = $result['fname'];
            $tmp[USER][FACEBOOKFRIENDSNUMBER] = $result['fb_friends_nb'];
            $tmp[USER][GENDER] = $result['gender'];
            $tmp[USER][BIRTHDAY] = $result['birthday'];
            $tmp[USER][USERPROFILE_URL] = $result['fb_pic'];
            $tmp[USER][RATE_COUNT] = $result['rate_count'];
            $tmp[USER][REVIEW_COUNT] = $result['review_count'];
            $tmp[USER][TRIP_COUNT] = $result['trips_count'];
            $tmp[USER][RATE] = $result['rate'];
            $tmp[USER][RATE_COUNT] = $result['rate_count'];
            $tmp[GROUP][GROUP_ID] = $result['group_public_id'];
            $tmp[GROUP][GROUP_NAME] = $result['group_name'];
            $tmp[GROUP][GROUP_DESCRIPTION] = $result['group_description'];
            $tmp[GROUP][GROUP_MEMBERSHIP_STATUS] = $result['request_status'];
            array_push($response[GROUP_USER_LIST],$tmp);
        }
    }
    return $response;
}
$app->post('/acceptGroupRequest','authenticate', function()  use ($app) {
    verifyRequiredParams(array(GROUP_ID,USER_ID));
    global $user_region;
    global $user_id;
    $db = new DbHandler($user_region);
    $group = $app->request->post(GROUP_ID);
    $user_to_validate = $app->request->post(USER_ID);
    $result = $db->validateGroupMembership($user_id,$group,$user_to_validate);
    $response = getPendingGroupRequests($result);
    $response[ERROR] = false;
    $response[MESSAGE] = "OK";
    echoRespnse(201, $response);
});
$app->post('/createGroup','authenticate', function()  use ($app) {
    verifyRequiredParams(array(GROUP_NAME,GROUP_DESCRIPTION,GROUP_IS_PUBLIC));
    $response = array();
    $response[GROUP_LIST] = array();
    global $user_region;
    global $user_id;
    $group_name = $app->request->post(GROUP_NAME);
    $group_validation_email = $app->request->post(GROUP_IS_CORPORATE);
    $group_description = $app->request->post(GROUP_DESCRIPTION);
    $group_is_public = $app->request->post(GROUP_IS_PUBLIC);
    $group_pic = $app->request->post(GROUP_PICTURE);
    $db = new DbHandler($user_region);
    $result = $db->createGroup($user_id,$group_name,$group_is_public,$group_validation_email,$group_is_public,$group_description,$group_pic);
    if ($result) {
        $response[GROUP_LIST] = getUserGroups(null,null,$result);
        $response[MESSAGE] = "OK";
    }
    else {
        $response[ERROR] = true;
        $response[MESSAGE] = "ERROR";
    }
    echoRespnse(201, $response);

});

$app->post('/uploadGroupPicture','authenticate', function()  use ($app) {
    global $user_id;
    $response[GROUP_LIST] = array();
    $response = uploadPictureFor($user_id, false);
    echoRespnse(201, $response);
});

$app->post('/requestGroupMembership','authenticate', function()  use ($app) {
    global $user_id;
    $response = array();
    $response[ERROR] = true;
    verifyRequiredParams(array(GROUP_ID));
    global $user_region;
    $group_id = $app->request->post(GROUP_ID);
    $emailToValidate = $app->request->post(EMAIL);
    $gic = $app->request->post(GROUP_IS_CORPORATE);
    $db = new DbHandler($user_region);
    $corporateEmail = $db->getGroupEmailValidator($group_id);
    if($emailToValidate && $corporateEmail && $gic == $corporateEmail){
        $r = $db->addUserToGroup($user_id, $group_id);
        $firstname = $app->request->post(FIRSTNAME);
        $group_name = $app->request->post(GROUP_NAME);
        if($r){
           $token = $r->fetch_assoc();
           $tmp = $group_id.'-'.$token['validation_token'].'-'.$user_id.'-'.$user_region;
           $encoded = urlencode(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5(TOKEN_ENCRIPTION_KEY), $tmp, MCRYPT_MODE_CBC, md5(md5(TOKEN_ENCRIPTION_KEY)))));
           $response[ERROR] = !sendValidationEmailForGroupMembership($emailToValidate, $firstname, $group_name, $encoded);
           $response[MESSAGE] = 'OK';
           $response[GROUP_LIST] = getUserGroups($user_id, $user_region, NULL);
           //SEND EMAIL HERE : GET HTML SEND IT BACK TO BROWSER AND TEST IT FROM THERE.
        }else{

        }
    }else{
        if(!$corporateEmail){
            $r = $db->addUserToGroup($user_id, $group_id);
            if($r){
                $response[GROUP_LIST] = getUserGroups(null, null, $r);
                $response[ERROR] = false;
                $response[MESSAGE] = 'OK';
            }else{
                $response[ERROR] = true;
                $response[MESSAGE] = 'NOK';
            }

        }else{
            $response[ERROR] = true;
            $response[MESSAGE] = 'VALIDATION_EMAIL_ADDRESS_MISSING';
            $response[GROUP_LIST] = null;
        }
    }
    echoRespnse(201, $response);
});

$app->post('/updateNotificationFlag','authenticate', function()  use ($app) {
    global $user_id;
    global $user_region;
    $response = array();
    $response[ERROR] = true;
    $response[MESSAGE] = 'KO';
    $db = new DbHandler($user_region);
    $group_id = $app->request->post(GROUP_ID);
    if($db->updateNotificationFlag($user_id, $group_id)){
        $response[ERROR] = false;
        $response[MESSAGE] = 'OK';
    }
    echoRespnse(201, $response);
});
$app->post('/makeUserAdminInGroup','authenticate', function()  use ($app) {
    global $user_id;
    global $user_region;
    $response = array();
    $response[ERROR] = false;
    $response[MESSAGE] = 'OK';
    $db = new DbHandler($user_region);
    $group_id = $app->request->post(GROUP_ID);
    $new_admin_user = $app->request->post(USER_ID);
    $t = $db->makeUserAdminInGroup($user_id,$group_id,$new_admin_user);
    $response[GROUP_USER_LIST] = getUserListInGroup(null,null,null,$t);
    if(!$response[GROUP_USER_LIST]){
        $response[ERROR] = true;
        $response[MESSAGE] = 'OK';
    }
    echoRespnse(201, $response);
});

$app->post('/getUserListInGroup','authenticate', function()  use ($app) {
    global $user_id;
    global $user_region;
    $response = array();
    $response[ERROR] = false;
    $response[MESSAGE] = 'OK';
    $group_id = $app->request->post(GROUP_ID);
    $response[GROUP_USER_LIST] = getUserListInGroup($user_id,$user_region,$group_id,NULL);
    if(!$response[GROUP_USER_LIST]){
        $response[ERROR] = true;
        $response[MESSAGE] = 'OK';
    }
    echoRespnse(201, $response);
});

function getUserListInGroup($user_id,$region,$group_id,$results=NULL){
    if($results === NULL){
        $db = new DbHandler($region);
        $tmp = $db->getUserListInGroup($user_id, $group_id);
    }else{
       $tmp = $results;
    }

    if($tmp){
        $list = array();
        while($result = $tmp->fetch_assoc()){
            $temp = array();
            $temp[USER_ID] = $result['public_id'];
            $temp[FIRSTNAME] = $result['fname'];
            $temp[FACEBOOKFRIENDSNUMBER] = $result['fb_friends_nb'];
            $temp[FACEBOOKFRIENDSNUMBER] = $result['fb_friends_nb'];
            $temp[GENDER] = $result['gender'];
            $temp[BIRTHDAY] = $result['birthday'];
            $temp[USERPROFILE_URL] = $result['fb_pic'];
            $temp[RATE_COUNT] = $result['rate_count'];
            $temp[REVIEW_COUNT] = $result['review_count'];
            $temp[TRIP_COUNT] = $result['trips_count'];
            $temp[RATE] = $result['rate'];
            $temp[GROUP_MEMBERSHIP_STATUS] = $result['request_status'];
            $temp[GROUP_USER_ROLE] = $result['user_role'];
            array_push($list, $temp);
        }
        return $list;
    }
    return null;
}

$app->post('/alert','authenticate', function()  use ($app) {
    global $user_id;
    global $user_region;
    verifyRequiredParams(array(DEPARTURE_NAME,ARRIVAL_NAME,DEPARTURE_TIME));

    $departure_name = $app->request->post(DEPARTURE_NAME);
    $departure_lt = $app->request->post(DEPARTURE_LATITUDE);
    $departure_lg = $app->request->post(DEPARTURE_LONGITUDE);
    $arrival_name = $app->request->post(ARRIVAL_NAME);
    $arrival_lt = $app->request->post(ARRIVAL_LATITUDE);
    $arrival_lg = $app->request->post(ARRIVAL_LONGITUDE);
    $departure_time = $app->request->post(DEPARTURE_TIME);
    $isRepeated = $app->request->post(TRIP_COMMUTE);
    $endDate  = $app->request->post(TRIP_COMMUTE_END_DATE);
    $pattern = $app->request->post(TRIP_COMMUTE_PATTERN);
    $response = array();
    $response[ERROR] = false;
    $response[MESSAGE] = 'OK';
    $db = new DbHandler($user_region);
    $results = $db->createTripAlert($user_id,$departure_name ,$departure_lt ,$departure_lg ,$arrival_name ,$arrival_lt ,$arrival_lg ,$departure_time ,$isRepeated ,$endDate ,$pattern );
    $response[ALERT_LIST] = getUserAlerts(null,null,$results);
    if(!$response[ALERT_LIST]){
        $response[ERROR] = true;
        $response[MESSAGE] = 'OK';
    }
    echoRespnse(201, $response);
});

$app->get('/alert','authenticate', function()  use ($app) {
    global $user_id;
    global $user_region;
    $response = array();
    $response[ERROR] = false;
    $response[MESSAGE] = 'OK';
    $response[ALERT_LIST] = getUserAlerts($user_id,$user_region,NULL);
    if(!$response[ALERT_LIST]){
        $response[ERROR] = true;
        $response[MESSAGE] = 'OK';
    }
    echoRespnse(201, $response);
});

$app->post('/disablealert','authenticate', function()  use ($app) {
    global $user_id;
    global $user_region;
    $alert_id = $pattern = $app->request->post(ALERT_ID);
    $response = array();
    $response[ERROR] = false;
    $response[MESSAGE] = 'OK';
    $db = new DbHandler($user_id);
    $results = $db->disableTripAlert($user_id, $alert_id);
    $response[ALERT_LIST] = getUserAlerts(null,null,$results);
    if(!$response[ALERT_LIST]){
        $response[ERROR] = true;
        $response[MESSAGE] = 'OK';
    }
    echoRespnse(201, $response);
});


function getUserAlerts($user_id,$user_region,$results = NULL){
    $list = array();
    if($results !== NULL){
        $tmp = $results;
    }else if($user_id && $user_region) {
        $db = new DbHandler($user_region);
        $tmp = $db->getActiveTripAlert($user_id);
    }
    if($tmp){
        while($result = $tmp->fetch_assoc()){
            $temp = array();
            $temp[ALERT_ID] = $result['alt_public_id'];
            $temp[DEPARTURE_NAME] = $result['departure_name'];
            $temp[DEPARTURE_LATITUDE] = $result['departure_lt'];
            $temp[DEPARTURE_LONGITUDE] = $result['departure_lg'];
            $temp[ARRIVAL_NAME] = $result['arrival_name'];
            $temp[ARRIVAL_LATITUDE] = $result['arrival_lt'];
            $temp[ARRIVAL_LONGITUDE] = $result['arrival_lg'];
            $temp[DEPARTURE_TIME] = $result['departure_time'];
            $temp[TRIP_COMMUTE] = $result['isRepeated'];
            $temp[TRIP_COMMUTE_END_DATE] = $result['endDate'];
            $temp[TRIP_COMMUTE_PATTERN] = $result['patter_base_ten'];
            array_push($list,$temp);
        }
    }
    return $list;
}

$app->post('/savedriverlicence','authenticate', function() use ($app) {
    // check for required params
    verifyRequiredParams(array(DRIVER_LICENCE_NUMBER,DRIVER_LICENCE_ISSUED_DATE,DRIVER_LICENCE_COUNTRY,DRIVER_LICENCE_CONDITIONS));
    $dl_number = $app->request->post(DRIVER_LICENCE_NUMBER);
    $dl_date = $app->request->post(DRIVER_LICENCE_ISSUED_DATE);
    $dl_end = $app->request->post(DRIVER_LICENCE_EXPIRY_DATE);
    $dl_country = $app->request->post(DRIVER_LICENCE_COUNTRY);
    $dl_condition = $app->request->post(DRIVER_LICENCE_CONDITIONS);
    global $user_region;
    $db = new DbHandler($user_region);
    global $user_id;
    $status = 200;
    $response = array();
    $response[MESSAGE] = "OK";
    $response[RESPONSE_STATUS] = array();
    $response[RESPONSE_STATUS][ERROR] = false;
    $response[ERROR] = false;
    $data = array();
    $result = $db->saveDriverLicenceDetails($user_id,$dl_number,$dl_date,$dl_end,$dl_country,$dl_condition);
    if($result){
        while ($user = $result->fetch_assoc()) {
            $tmp = getUserArray($user);
            $tmp[WORLDREGION] = $user_region;
            $tmp[PRIVACY_POLICAY_VERSION_PARAM] = intval(ROADMATE_TERMS_VERSION);
            $tmp[GROUP_LIST] = getUserGroups($user_id,$user_region);
            $data[USER] = $tmp;
            $response[RESPONSE_DATA] = $data;
        }
    }else{
        $response[STATUS][ERROR] = true;
        $response[ERROR] = true;
        $status[MESSAGE] = 'FAILED TO UPDATE DRIVER LICENCE';
    }

    echoRespnse($status, $response);
});

$app->run();
