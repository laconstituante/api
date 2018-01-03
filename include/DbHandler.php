<?php

/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables
 *
 * @author Ravi Tamada
 */
require_once(__DIR__.'/../api/static_var.php');
require_once(__DIR__.'/config.php');
class DbHandler {

    private $conn;
    private $_region;

    function __construct() {
        require_once dirname(__FILE__) . '/DbConnect.php';
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    public function getAllTitres(){
        $stmt = $this->conn->prepare("CALL getAllTitres()");
        $stmt->execute();
        $articles = $stmt->get_result();
        $stmt->close();
        return $articles;
    }
    public function getAllTitresFromConstitution($constitutionId, $isUrl){
        if($isUrl){
            $stmt = $this->conn->prepare("CALL getAllTitresFromConstitutionByUrl(?)");
        }else{
            $stmt = $this->conn->prepare("CALL getAllTitresFromConstitution(?)");
        }

        if(FALSE !== $stmt){
            if($isUrl){
                $urlHash = md5($constitutionId);
                $stmt->bind_param("s", $urlHash);
            }else{
                $stmt->bind_param("s", $constitutionId);
            }

            if($stmt->execute()){
                $results = $stmt->get_result();
                $stmt->close();
                return $results;
            }else{
                return null;
            }
        }
        return null;
    }
    public function getAllTitresFromConstitutionByUrl($constitution_url){
        return $this->getAllTitresFromConstitution($constitution_url, true);
    }
    public function getConstitutions(){
        $stmt = $this->conn->prepare("CALL getConstitutions()");
        if(FALSE !== $stmt){
            if($stmt->execute()){
                $results = $stmt->get_result();
                $stmt->close();
                return $results;
            }else{
                return null;
            }
        }
        return null;
    }
    public function getPropositionsFromAlineaAuth($alinea_id,$user_id){
        $stmt = $this->conn->prepare("CALL getPropositionsFromAlineaAuth(?,?)");
        if(FALSE !== $stmt){
            $stmt->bind_param("si", $alinea_id,$user_id);
            if($stmt->execute()){
                $results = $stmt->get_result();
                $stmt->close();
                return $results;
            }else{
                return null;
            }
        }
        return null;

    }
    public function getPropositionsFromAlinea($alinea,$user_id = null){
        return $this->getPropositionsFromAlineaAuth($alinea,0);
    }
    public function getArticlesFromTitre($titre_id,$isUrl){
        if($isUrl){
            $stmt = $this->conn->prepare("CALL getArticlesFromTitreByUrl(?)");
        }else{
            $stmt = $this->conn->prepare("CALL getArticlesFromTitre(?)");
        }
        if(FALSE !== $stmt){
            if($isUrl){
                $url_hash = md5($titre_id);
                $stmt->bind_param("s", $url_hash);
            }else{
                $stmt->bind_param("s", $titre_id);
            }

            if($stmt->execute()){
                $articles = $stmt->get_result();
                $stmt->close();
                return $articles;
            }else{
                return null;
            }
        }
        return null;
    }
    public function getArticlesByTitreByUrl($titre_url){
        return $this->getArticlesFromTitre($titre_url,true);
    }

    public function getAllVotes(){
        $stmt = $this->conn->prepare("CALL getAllVotes()");
        if(FALSE !== $stmt){
            if($stmt->execute()){
                $result = $stmt->get_result();
                $stmt->close();
                return $result;
            }else{
                $time = date("Y-m-d H:i:s",time() + 61200 );
                error_log("$time | CALL getAllVotes()\n",3,ERROR_LOG_PATH);
                return null;
            }
        }
        return null;
    }
    public function getAllAlineasByArticleId($article_id,$user_id= null,$isUrl,$constitutionUrl=null){
        if($user_id === null){
            if($isUrl){
                $stmt = $this->conn->prepare("CALL getAllAlineasByArticleUrl(?,?)");
                $article_hash = md5($article_id);
                $constitution_hash = md5($constitutionUrl);
                $stmt->bind_param("ss", $article_hash,$constitution_hash);
                $log = "CALL getAllAlineasByArticleUrl('$article_hash','$constitution_hash')\n";
            }else{
               $stmt = $this->conn->prepare("CALL getAllAlineasByArticleId(?)");
               $stmt->bind_param("s", $article_id);
               $log = "CALL getAllAlineasByArticleId('$article_id')\n";
            }
        }else if($user_id > 0 && $isUrl){
            $stmt = $this->conn->prepare("CALL getAllAlineasByArticleUrlAuth(?,?,?)");
            $article_hash = md5($article_id);
            $constitution_hash = md5($constitutionUrl);
            $stmt->bind_param("iss",$user_id, $article_hash,$constitution_hash);
            $log = "CALL getAllAlineasByArticleUrlAuth($user_id,'$article_hash','$constitution_hash')\n";
        }else{
            $stmt = $this->conn->prepare("CALL getAllAlineasByArticleIdAuth(?,?)");
            $stmt->bind_param("si", $article_id,$user_id);
            $log = "CALL getAllAlineasByArticleIdAuth('$article_id',$user_id)\n";
        }
        error_log("$log",3,ERROR_LOG_PATH);
        if(FALSE !== $stmt){
            if($stmt->execute()){
                $alineas = $stmt->get_result();
                $stmt->close();
                return $alineas;
            }else{
                $time = date("Y-m-d H:i:s",time() + 61200 );
                error_log("$time | $log",3,ERROR_LOG_PATH);
                return null;
            }
        }
        return null;

    }
    public function setShortUrlForArticle($article_id,$constitutionUrl,$shortUrl){
        $stmt = $this->conn->prepare("CALL setShortUrlForArticle(?,?,?)");
        $article_hash = md5($article_id);
        $constitution_hash = md5($constitutionUrl);
        $log = "CALL setShortUrlForArticle('$article_hash','$constitution_hash','$shortUrl')\n";
        if(FALSE !== $stmt){
            $stmt->bind_param("sss", $article_hash,$constitution_hash,$shortUrl);
            if($stmt->execute()){
                $result = $stmt->get_result();
                $stmt->close();
                return $result;
            }else{
                $time = date("Y-m-d H:i:s",time() + 61200 );
                error_log("$time | $log\n",3,ERROR_LOG_PATH);
                return null;
            }
        }
        return null;
    }
    public function get6RepublicEligibility($user_Id){
        $stmt = $this->conn->prepare("CALL get6RepublicEligibility(?)");
        if(FALSE !== $stmt){
            $stmt->bind_param("s", $user_Id);
            if($stmt->execute()){
                $result = $stmt->get_result();
                $stmt->close();
                return $result;
            }else{
                $time = date("Y-m-d H:i:s",time() + 61200 );
                error_log("$time | CALL get6RepublicEligibility('$user_Id')\n",3,ERROR_LOG_PATH);
                return null;
            }
        }
        return null;
    }
    public function getNextAlineas($user_Id){
        $stmt = $this->conn->prepare("CALL getNextAlineas(?)");
        if(FALSE !== $stmt){
            $stmt->bind_param("s", $user_Id);
            if($stmt->execute()){
                $result = $stmt->get_result();
                $stmt->close();
                return $result;
            }else{
                $time = date("Y-m-d H:i:s",time() + 61200 );
                error_log("$time | CALL getNextAlineas('$user_Id')\n",3,ERROR_LOG_PATH);
                return null;
            }
        }
        return null;
    }
    public function voteProposition($user_id,$proposition_id,$vote){
        $stmt = $this->conn->prepare("CALL voteProposition(?,?,?)");
        if(FALSE !== $stmt){
            $stmt->bind_param("isi",$user_id, $proposition_id,$vote);
            if($stmt->execute()){
                $alineas = $stmt->get_result();
                $stmt->close();
                return $alineas;
            }else{
                $time = date("Y-m-d H:i:s",time() + 61200 );
                error_log("$time | CALL voteProposition($user_id,'$proposition_id',$vote)\n",3,ERROR_LOG_PATH);
                return null;
            }
        }
        return null;
    }
    public function voteAlinea($user_id,$alinea_pi,$vote){
        $stmt = $this->conn->prepare("CALL voteAlinea(?,?,?)");
        if(FALSE !== $stmt){
            $stmt->bind_param("isi",$user_id, $alinea_pi,$vote);
            if($stmt->execute()){
                $alineas = $stmt->get_result();
                $stmt->close();
                return $alineas;
            }else{
                $time = date("Y-m-d H:i:s",time() + 61200 );
                error_log("$time | CALL voteAlinea($user_id,'$alinea_pi',$vote)\n",3,ERROR_LOG_PATH);
                return null;
            }
        }
        return null;
    }
    public function createProposition($user_id,$alinea_pi,$proposition_text){
        $stmt = $this->conn->prepare("CALL createProposition(?,?,?)");
        if(FALSE !== $stmt){
            $stmt->bind_param("iss", $user_id,$alinea_pi,$proposition_text);
            $toto = "CALL createProposition($user_id,'$alinea_pi','$proposition_text')";
            if($stmt->execute()){
                $Propositions = $stmt->get_result();
                $stmt->close();
                return $Propositions;
            }else{
                $time = date("Y-m-d H:i:s",time() + 61200 );
                error_log("$time | CALL createProposition($user_id,'$alinea_pi','$proposition_text')\n",3,ERROR_LOG_PATH);
                return null;
            }
        }
        return null;
    }
    public function isValidApiKey($api_key) {
        $stmt = $this->conn->prepare("call getUserByAuthenticatedToken(?)");
        if(FALSE !== $stmt){
            $stmt->bind_param("s", $api_key);
            if($stmt->execute()){
                $user = $stmt->get_result()->fetch_assoc();
                // Check for successful insertion
                if (intval($user['fk_usr_id']) < 1 ) {
                    $time = date("Y-m-d H:i:s",time() + 61200 );
                    error_log("$time | call getUserByAuthenticatedToken('$api_key'); \n",3,ERROR_LOG_PATH);
                    $stmt->close();
                    return null;
                }else{
                    $stmt->close();
                    return $user;
                }
            }
            else{
                $stmt->close();
                $time = date("Y-m-d H:i:s",time() + 61200 );
                error_log("$time |Error call getUserByAuthenticatedToken('$api_key'); \n",3,ERROR_LOG_PATH);
                return null;
            }
        }
        return null;

    }
    public function getUserStatTitres($user_id){
        $stmt = $this->conn->prepare("CALL getStatByTitreForUser(?)");
        if(FALSE !== $stmt){
            $stmt->bind_param("i", $user_id);
            if($stmt->execute()){
                $stat = $stmt->get_result();
                $stmt->close();
                return $stat;
            }else{
                $time = date("Y-m-d H:i:s",time() + 61200 );
                error_log("$time | CALL getStatByTitreForUser($user_id);\n",3,ERROR_LOG_PATH);
                return null;
            }
        }
        return null;
    }
    public function getUserStat($user_id){
        $stmt = $this->conn->prepare("CALL getUserStat(?)");
        if(FALSE !== $stmt){
            $stmt->bind_param("i", $user_id);
            if($stmt->execute()){
                $stat = $stmt->get_result();
                $stmt->close();
                return $stat;
            }else{
                $time = date("Y-m-d H:i:s",time() + 61200 );
                error_log("$time | CALL getUserStat($user_id);\n",3,ERROR_LOG_PATH);
                return null;
            }
        }
        return null;
    }
    public function getGlobalStat(){
        $stmt = $this->conn->prepare("CALL getGlobalStat()");
        if(FALSE !== $stmt){
            if($stmt->execute()){
                $stat = $stmt->get_result();
                $stmt->close();
                return $stat;
            }else{
                $time = date("Y-m-d H:i:s",time() + 61200 );
                error_log("$time | CALL getGlobalStat();\n",3,ERROR_LOG_PATH);
                return null;
            }
        }
        return null;
    }
    public function create_user($first,$last,$email,$password,$dob,$civilite,$isFrench,$codepostal,$user_ip,$user_agent_hash,$email_token,$coountry){
        require_once 'PassHash.php';
        $password_hash = PassHash::hash($password);
        if($isFrench){
            $isFrench = 1;
        }else{
            $isFrench = 0;
        }
        $stmt = $this->conn->prepare("CALL create_user(?,?,?,?,?,?,?,?,?,?,?,?)");
        if(FALSE !== $stmt){
            $stmt->bind_param("sssssiisssss", $first,$last,$email,$password_hash,$dob,$civilite,$isFrench,$codepostal,$user_ip,$user_agent_hash,$email_token,$coountry);
            if($stmt->execute()){
                $user = $stmt->get_result();
                $stmt->close();
                return $user;
            }else{
                $time = date("Y-m-d H:i:s",time() + 61200 );
                error_log("$time | CALL create_user('$first','$last','$email','$password_hash','$dob',$civilite,$isFrench,'$codepostal','$user_ip','$user_agent_hash','$email_token','$coountry')\n",3,ERROR_LOG_PATH);
                return null;
            }
        }
        return null;
    }
    public function confirmEmail($token,$user_id){
        $stmt = $this->conn->prepare("CALL checkTokenAndLogin(?,?)");
        if(FALSE !== $stmt){
            $stmt->bind_param("ss", $token,$user_id);
            if($stmt->execute()){
                $user = $stmt->get_result();
                $stmt->close();
                return $user;
            }else{
                $time = date("Y-m-d H:i:s",time() + 61200 );
                error_log("$time | CALL checkTokenAndLogin('$token','$user_id')\n",3,ERROR_LOG_PATH);
                return null;
            }
        }
        return null;
    }
    public function loginUser($email,$password,$isPersistent){
        $stmt = $this->conn->prepare("CALL checkUserLogin(?)");
        if(FALSE !== $stmt){
            $stmt->bind_param("s", $email);
            if($stmt->execute()){
                $ret = $stmt->get_result()->fetch_assoc();
                if (PassHash::check_password($ret['password_hash'], $password)) {
                    $user_id = intval($ret['pk_usr_id']);
                    $api_key = $this->generateApiKey();
                    $stmt->close();
                    $stmt2 = $this->conn->prepare("CALL loginUser(?,?,?)");
                    $stmt2->bind_param("isi", $user_id,$api_key,$isPersistent);
                    if($stmt2->execute()){
                        $ret2 = $stmt2->get_result();
                        $stmt2->close();
                        return $ret2;
                    }else{
                         $time = date("Y-m-d H:i:s",time() + 61200 );
                        error_log("$time | CALL loginUser($user_id,'$api_key',$isPersistent)\n",3,ERROR_LOG_PATH);
                        $stmt2->close();
                        return null;
                    }
                } else {
                    $stmt->close();
                    return 'FALSE 144';
                }
            }else{
                $time = date("Y-m-d H:i:s",time() + 61200 );
                error_log("$time | CALL checkUserLogin('$email')\n",3,ERROR_LOG_PATH);
                return null;
            }
        }
        return null;
    }
    public function checkLogin($email, $password,$rememberUser) {
        // fetching user by email
        $stmt = $this->conn->prepare("CALL rmdbp_checkLogin(?)");
        $stmt->bind_param("s", $email);

        if ($stmt->execute()) {
            $ret = $stmt->get_result()->fetch_assoc();
            if (PassHash::check_password($ret['password_hash'], $password)) {
                $user_id = $ret['pk_usr_id'];
                $api_key = $this->generateApiKey();
                $stmt = $this->conn->prepare("CALL loginUSer(?,?,?)");
                $stmt->bind_param("isi", $user_id,$api_key,$rememberUser);
                if($stmt->execute()){
                    $stmt->close();
                    return $ret;
                }else{
                    $stmt->close();
                    return 'FALSE 139';
                }
            } else {
                $stmt->close();
                return 'FALSE 144';
            }
        } else {
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time | MainDbHandler | checkLogin | Login fail for user $email \n\n",3,ERROR_LOG_PATH);
            $stmt->close();
            // user not existed with the email
            return 'FALSE 151';
        }
    }




    //p_first varchar(100) CHAR SET utf8,
    //p_last varchar(100) CHAR SET utf8,
    //p_email varchar(100) CHAR SET utf8,
    //p_password varchar(100) CHAR SET utf8,
    //p_dob  varchar(100) CHAR SET utf8,
    //p_civil varchar(5) CHAR SET utf8,
    //p_isfrench  INT,
    //p_code_postal  varchar(5) CHAR SET utf8









    public function createTargetUser($lastName, $firstName,$email, $gender,$phone, $birthday,$town,$country,$facebookFriendsNumber,$facebook_Mutual_friends,$picture){
        $stmt = $this->conn->prepare("CALL rmdbp_createUser(?,?,?,?,?,?,?,?,?,?,?)");
        if(FALSE !== $stmt){
             $stmt->bind_param("sssissssiss", $lastName, $firstName,$email, $gender,$phone, $birthday,$town,$country,$facebookFriendsNumber,$facebook_Mutual_friends,$picture);
            if($stmt->execute()){
                $user = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                return $user['id'];
            }else{

            }
        }
        else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            $region = $this->_region;
            error_log("$time | DbHandler [$region]| Unable to create Target User From fb | CALL rmdbp_createUser($lastName, $firstName,$email, $gender,$phone, $birthday,$town,$country,$facebookFriendsNumber,$facebook_Mutual_friends,$picture)\n",3,ERROR_LOG_PATH);
        }
            $stmt->close();
        return -1;
    }
    public function updateUserFromFb($id,$lname,$fname, $email,$fb_friends_nb,$mutual_friends){
        $stmt = $this->conn->prepare("CALL rmdbp_updateUserFromFacebook(?,?,?,?,?,?)");
        $stmt->bind_param("ssisis", $lname,$fname,$fb_friends_nb, $email,$id,$mutual_friends);
        if($stmt->execute()){
            $stmt->close();
            return true;
        }
        else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            $region = $this->_region;
            error_log("$time | DbHandler [$region]| updateUserFromFb | CALL rmdbp_updateUserFromFacebook($lname,$fname,$fb_friends_nb, $email,$id,$mutual_friends)\n",3,ERROR_LOG_PATH);
        }
        $stmt->close();
        return false;
    }


    public function insertFemaleValidation($id){
        $stmt = $this->conn->prepare("CALL rmdbp_insertFemaleValidation(?)");
        $stmt->bind_param("i", $id);
        if($stmt->execute()){
            $stmt->close();
            return true;
        }
        else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            $region = $this->_region;
            error_log("$time | DbHandler [$region]| insertFemaleValidation | INSERT INTO RMDB_REQ(fk_usr_id) VALUES($id)\n",3,ERROR_LOG_PATH);
        }
        $stmt->close();
        return false;

    }

    public function getFemaleRequests(){
        $stmt = $this->conn->prepare("SELECT req.pk_req_id, req.request_status,usr.* FROM RMDB_REQ req JOIN RMDB_USR usr ON req.fk_usr_id = usr.id WHERE req.request_status = 'pending'");
        $stmt->execute();
        $trips = $stmt->get_result();
        $stmt->close();
        return $trips;
    }

    //
    public function getStatistics(){
        $stmt = $this->conn->prepare("CALL getcreationstat()");
        if($stmt->execute()){
            $stat = $stmt->get_result();
            $stmt->close();
            return $stat;
        }else{
            $stmt->close();
            return null;
        }
    }
    public function approveFemaleRequests($requestId){
        $stmt = $this->conn->prepare("CALL rmdbp_approveFemaleRequests(?) ");
        $stmt->bind_param("i", $requestId);
        if($stmt->execute()){
            $trips = $stmt->get_result();
            $stmt->close();
            return true;
        }else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            $region = $this->_region;
            error_log("$time | DbHandler [$region]| approveFemaleRequests | CALL rmdbp_approveFemaleRequests($requestId)\n",3,ERROR_LOG_PATH);
        }
        return NULL;
    }
    public function log($userId,$userAgent,$currentSource,$errorLine,$currentMethod,$appVersion,$message){
//        $stmt = $this->conn->prepare("CALL rmdbp_log(?,?,?,?,?,?,?)");
//        $stmt->bind_param("sssssss", $userId,$userAgent,$currentSource,$errorLine,$currentMethod,$appVersion,$message);
//        if($stmt->execute()){
//            $stmt->close();
//            return true;
//        }
//        else{
//            $time = date("Y-m-d H:i:s",time() + 61200 );
//            error_log("$time | log | Unable to log error | CALL rmdbp_log($userId,$userAgent,$currentSource,$errorLine,$currentMethod,$appVersion,$message)\n",3,ERROR_LOG_PATH);
//        }
//        $stmt->close();
//        return false;
        $time = date("Y-m-d H:i:s",time() + 61200 );
        error_log("$time | CALL rmdbp_log($userId,$userAgent,$currentSource,$errorLine,$currentMethod,$appVersion,$message)\n",3,ERROR_LOG_PATH);
        return true;
    }

    public function updateUserCar($user_id,$carmodel,$carseats,$carcolor,$cartype,$carquality){
        $stmt = $this->conn->prepare("CALL rmdbp_UpdateUserCar(?,?,?,?,?,?)");
        $stmt->bind_param("isisss", $user_id,$carmodel,$carseats,$carcolor,$cartype,$carquality);
        if($stmt->execute()){
            $stmt->close();
            return 1;
        }
        else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            $region = $this->_region;
            error_log("$time | DbHandler [$region]| updateUcerCar | Unable to updateUser Car | CALL rmdbp_UpdateUserCar($user_id,$carmodel,$carseats,$carcolor,$cartype,$carquality) \n\n",3,ERROR_LOG_PATH);
        }
        return -1;
    }

    public function updateUserPicture($user_id,$imgurl){
        if($user_id > 0){
            $stmt = $this->conn->prepare("UPDATE RMDB_USR SET fb_pic = ? WHERE id = ?");
            $stmt->bind_param("si", $imgurl,$user_id);
            if($stmt->execute()){
                $stmt->store_result();
                $num_rows = $stmt->num_rows;
                $stmt->close();
                return $num_rows !== 1;
            }else{
                $stmt->close();
                $time = date("Y-m-d H:i:s",time() + 61200 );
                $region = $this->_region;
                error_log("$time | DbHandler [$region]| updateUserPicture | UPDATE RMDB_USR SET fb_pic = $imgurl WHERE id = $user_id\n",3,ERROR_LOG_PATH);
                return false;
            }

        }
    }

    public function updateUserDetails($firstname,$phone,$birthday,$userId){
        $stmt = $this->conn->prepare("CALL rmdbp_updateUserDetails(?,?,?,?)");
        $stmt->bind_param("sssi", $firstname,$phone,$birthday,$userId);
        $result = null;
        if($stmt->execute()){
            $result = $stmt->get_result();
        }else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            $region = $this->_region;
            error_log("$time | DbHandler [$region]| updateUserDetails | CALL rmdbp_updateUserDetails($firstname,$phone,$birthday,$userId) \n\n",3,ERROR_LOG_PATH);
        }
        $stmt->close();
        return $result;
    }
    /**
     * Creating new user in the target DB
     *
     */
    public function createUser($fname, $lname, $email, $phone, $gender,$b_day,$home,$country,$fb_friends_nb,$mutual_friends,$facebook_pic) {
        require_once 'PassHash.php';
        $response = array();
        $response[USER_ID] = -1;
        $response[APIKEY] = null;
        if (!$this->isUserExists($email)) {
            $user_id = array();
            $stmt = $this->conn->prepare("CALL rmdbp_createUser(?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("sssissssiss", $lname,$fname, $email, $gender , $phone,$b_day,$home,$country,$fb_friends_nb,$mutual_friends,$facebook_pic);
            if($stmt->execute()){
                $user_id = $stmt->get_result()->fetch_assoc();
                // Check for successful insertion
                if ($user_id['id'] < 0 ) {
                    $time = date("Y-m-d H:i:s",time() + 61200 );
                    $region = $this->_region;
                    error_log("$time | DbHandler [$region]| createUser | Unable to createUser | CREATE USER RETURNED -1 | CALL rmdbp_createUser($lname,$fname, $email, $gender , $phone,$bd,$home,$country,$fb_friends_nb,$mutual_friends) \n\n",3,ERROR_LOG_PATH);
                } else {
                    $stmt->close();
                    return $user_id['id'];
                }
                $stmt->close();
            }
            else{
                $stmt->close();
                $time = date("Y-m-d H:i:s",time() + 61200 );
                $region = $this->_region;
            error_log("$time |2 DbHandler [$region]| createUser | Unable to createUser | CREATE USER FAILED;CALL rmdbp_createUserFromform($lname,$fname, $email,$api_key,$password_hash, $gender , $phone,$bd)\n",3,ERROR_LOG_PATH);
            }
        } else {
            $response[USER_ID] = -1;
        }
        return 0;
    }

    /*
     * updateOptinForUser
     *
     *
     */
    public function updateOptinForUser($userpublicId,$isOptinSms,$isOptinEmail){
        $stmt = $this->conn->prepare("CALL rmdbp_updateOptinForUser(?,?,?)");
        $stmt->bind_param("sii", $userpublicId,$isOptinSms,$isOptinEmail);
        //return "updateOptinForUser | Unable to updateOptinForUser | CALL rmdbp_updateOptinForUser($userpublicId,$isOptinSms,$isOptinEmail)";
        if($stmt->execute()){
            $stmt->close();
            return true;
        }
        else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            $region = $this->_region;
            error_log("$time | DbHandler [$region]| updateOptinForUser | Unable to updateOptinForUser | CALL rmdbp_updateOptinForUser($userpublicId,$isOptinSms,$isOptinEmail) \n\n",3,ERROR_LOG_PATH);
        }
        $stmt->close();
        return false;
    }
    /**
     * Checking for duplicate user by email address
     * @param String $email email to check in db
     * @return boolean
     */
    public function isUserExists($email) {
        $stmt = $this->conn->prepare("CALL rmdbp_isExistUser(?)");
        $stmt->bind_param("s", $email);
        $num_rows = 0;
        if($stmt->execute()){
            $result = $stmt->get_result()->fetch_assoc();
                $num_rows = $result['rows_count'];
        }else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            $region = $this->_region;
            error_log("$time | DbHandler [$region]| isUserExists | CALL rmdbp_isExistUser($email)\n",3,ERROR_LOG_PATH);
        }
        $stmt->close();
        return $num_rows > 0;
    }

    public function getUserPhoneFromEmail($email){
        $stmt = $this->conn->prepare("CALL rmdbp_getUserPhoneFromEmail(?)");
        $stmt->bind_param("s", $email);
        if($stmt->execute()){
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $user;
        }else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            $region = $this->_region;
            error_log("$time | DbHandler [$region]| getUserPhoneFromEmail | CALL rmdbp_getUserPhoneFromEmail($email) \n\n",3,ERROR_LOG_PATH);
        }
        $stmt->close();
        return null;
    }


    public function getUserFromId($usr_id) {
        $stmt = $this->conn->prepare("CALL rmdbp_getUserFromID(?)");
        $stmt->bind_param("i", $usr_id);
        if ($stmt->execute()) {
            $user = $stmt->get_result();
            $stmt->close();
            return $user;
        } else {
            $time = date("Y-m-d H:i:s",time() + 61200 );
            $region = $this->_region;
            error_log("$time | DbHandler [$region]| getUserFromId | CALL rmdbp_getUserFromID($usr_id) \n\n",3,ERROR_LOG_PATH);
            $stmt->close();
            return NULL;
        }
    }
    public function writeUserPhoneFromapiKey($usr_id,$phone) {
        $stmt = $this->conn->prepare("CALL rmdbp_writeuserphone(?,?)");
        $stmt->bind_param("is", $usr_id,$phone);
        if ($stmt->execute()) {
            $user = $stmt->get_result();
            $stmt->close();
            return true;
        } else {
            $time = date("Y-m-d H:i:s",time() + 61200 );
            $region = $this->_region;
            error_log("$time | DbHandler [$region]| writeUserPhoneFromapiKey | CALL rmdbp_writeuserphone($usr_id,$phone) \n\n",3,ERROR_LOG_PATH);
            $stmt->close();
            return NULL;
        }
    }

    /*
     *
     *
     */
    public function createSMSToken($len = 4){
        $characters = '0123456789';
        $randomString = '';
        for ($i = 0; $i < $len; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    /**
     * Generating random Unique MD5 String for user Api key
     */
    private function generateApiKey() {
        return md5(uniqid(rand(), true));
    }

    /* ------------- `RMDB_TRI` Trip table method ------------------ */

    public function createTrip($usr_id, $departure_name, $departure_lt, $departure_lg, $arrival_name, $arrival_lt, $arrival_lg, $price, $departure_time, $description,$passengers,$trip_params,$isAutomatic,$arrival_time,$trip_distance,$trip_duration,$trip_duration_from_previous,$trip_distance_from_previous,$isSubtripToBeCreated,$price_from_last_step,$seats_to_first_Step,$endDate,$pattern,$isWomenOnlyAndEligiblleForIt,$group_id,$departure_street_nb ,$departure_street_name,$departure_country,$arrival_street_nb,$arrival_street_name,$arrival_country) {
        $isCommute = $pattern ? 1 : 0;
        if($departure_street_nb || $departure_street_name||$departure_country||$arrival_street_nb||$arrival_street_name||$arrival_country){
            $stmt = $this->conn->prepare("CALL rmdbp_createTripV2(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("sddsddiissisisiiiiiiissiisssssss", $departure_name, $departure_lt, $departure_lg, $arrival_name, $arrival_lt, $arrival_lg, $price, $usr_id, $departure_time,$description,$passengers,$trip_params,$isAutomatic,$arrival_time,$trip_distance,$trip_duration,$trip_duration_from_previous,$trip_distance_from_previous,$isSubtripToBeCreated,$price_from_last_step,$seats_to_first_Step,$endDate,$pattern,$isCommute,$isWomenOnlyAndEligiblleForIt,$group_id,$departure_street_nb ,$departure_street_name,$departure_country,$arrival_street_nb,$arrival_street_name,$arrival_country);
            $error_msg = "New Version | CALL rmdbp_createTrip( '$departure_name', $departure_lt, $departure_lg, '$arrival_name', $arrival_lt, $arrival_lg, $price, $usr_id, '$departure_time','$description',$passengers,'$trip_params',$isAutomatic,'$arrival_time',$trip_distance,$trip_duration,$trip_duration_from_previous,$trip_distance_from_previous,$isSubtripToBeCreated,$price_from_last_step,$seats_to_first_Step,'$endDate','$pattern',$isCommute,$isWomenOnlyAndEligiblleForIt,'$group_id','$departure_street_nb' ,'$departure_street_name','$departure_country','$arrival_street_nb','$arrival_street_name','$arrival_country') \n";
        }else{
            $stmt = $this->conn->prepare("CALL rmdbp_createTrip(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("sddsddiissisisiiiiiiissiis", $departure_name, $departure_lt, $departure_lg, $arrival_name, $arrival_lt, $arrival_lg, $price, $usr_id, $departure_time,$description,$passengers,$trip_params,$isAutomatic,$arrival_time,$trip_distance,$trip_duration,$trip_duration_from_previous,$trip_distance_from_previous,$isSubtripToBeCreated,$price_from_last_step,$seats_to_first_Step,$endDate,$pattern,$isCommute,$isWomenOnlyAndEligiblleForIt,$group_id);
            $error_msg = "New Version | CALL rmdbp_createTrip( '$departure_name', $departure_lt, $departure_lg, '$arrival_name', $arrival_lt, $arrival_lg, $price, $usr_id, '$departure_time','$description',$passengers,'$trip_params',$isAutomatic,'$arrival_time',$trip_distance,$trip_duration,$trip_duration_from_previous,$trip_distance_from_previous,$isSubtripToBeCreated,$price_from_last_step,$seats_to_first_Step,'$endDate','$pattern',$isCommute,$isWomenOnlyAndEligiblleForIt,'$group_id') \n";
        }
        //error_log("$error_msg\n",3,ERROR_LOG_PATH);
        if ($isSubtripToBeCreated == 1) {
            if ($stmt->execute()) {
                $trip_id = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                return $trip_id['trip_id'];
            }else{
                $time = date("Y-m-d H:i:s",time() + 61200 );
                $region = $this->_region;
                error_log("$time | DbHandler [$region]| $error_msg\n",3,ERROR_LOG_PATH);
                return 0;
            }

        }
        else {
            if($stmt->execute()){
                $trips = $stmt->get_result();
               $stmt->close();
               return $trips;
            }else{
                $time = date("Y-m-d H:i:s",time() + 61200 );
                error_log("$time |CALL rmdbp_createTrip( $departure_name, $departure_lt, $departure_lg, $arrival_name, $arrival_lt, $arrival_lg, $price, $usr_id, $departure_time,$description,$passengers,$trip_params,$isAutomatic,$arrival_time,$trip_distance,$trip_duration,$trip_duration_from_previous,$trip_distance_from_previous,$isSubtripToBeCreated,$price_from_last_step,$seats_to_first_Step,$endDate,$pattern,$isCommute,$isWomenOnlyAndEligiblleForIt,$group_id) \n\n",3,ERROR_LOG_PATH);
                return null;
            }
        }
    }
    //Check if a user is Eligible for Women only trips
    public function isWomenOnlyAndEligiblleForIt($userId){
        $stmt = $this->conn->prepare("SELECT count(1) as count FROM RMDB_USR WHERE id = ? AND isFemaleVerified = 1");
        $stmt->bind_param("i", $userId);
        if($stmt->execute()){
           $trips = $stmt->get_result()->fetch_assoc();
           $stmt->close();
           return intval($trips['count']);
        }else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time |isWomenOnlyAndEligiblleForIt: SELECT count(1) as count FROM RMDB_USR WHERE id = $userId AND isFemaleVerified = 1 \n\n",3,ERROR_LOG_PATH);
            return 0;
        }
    }
    //Flag a trip as Women Only
    public function flagTripAsWomenOnly($tripId){
        $stmt = $this->conn->prepare("UPDATE RMDB_TRI SET isWomenOnly = 1 WHERE id = ?");
        $stmt->bind_param("i", $tripId);
        if($stmt->execute()){
           $trips = $stmt->get_result();
           $stmt->close();
           return true;
        }else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time |flagTripAsWomenOnly: UPDATE RMDB_TRI SET isWomenOnly = 1 WHERE id = $tripId \n\n",3,ERROR_LOG_PATH);
            return false;
        }
    }

    public function createSubTrip($user_id,$trip_id,$step_name,$step_lt,$step_lg,$available_seats_to_next,$step_order,$price_from_previous,$is_last,$duration_fromPrevious,$distance_fromPrevious,$arrival_time) {
        $stmt = $this->conn->prepare("CALL rmdbp_createSubTrip(?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("iisddiiiiiis", $user_id,$trip_id,$step_name,$step_lt,$step_lg,$available_seats_to_next,$step_order,$price_from_previous,$is_last,$duration_fromPrevious,$distance_fromPrevious,$arrival_time);
        if($stmt->execute()){
           $trips = $stmt->get_result();
            $stmt->close();
            return $trips;
        }else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time |createSubTrip 0 : CALL rmdbp_createSubTrip($user_id,$trip_id,$step_name,$step_lt,$step_lg,$available_seats_to_next,$step_order,$price_from_previous,$is_last,$duration_fromPrevious,$distance_fromPrevious,$arrival_time) \n\n",3,ERROR_LOG_PATH);
            $stmt->close();
            return false;
        }
    }

    /**
     * Fetching all user trips and their associate bookings
     * @param String $user_id id of the user
     */
    public function getAllUserTrips($user_id) {
        $stmt = $this->conn->prepare("CALL rmdbp_GetLastTripsForUser(?)");
        $stmt->bind_param("i", $user_id);
        if($stmt->execute()){
            $trips = $stmt->get_result();
            $stmt->close();
            return $trips;
        }else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time |getAllUserTrips : CALL rmdbp_GetLastTripsForUser($user_id) \n\n",3,ERROR_LOG_PATH);
            $stmt->close();
            return false;
        }
    }

    public function getPreviousUserTrips($user_id){
        $stmt = $this->conn->prepare("CALL rmdbp_GetOldTripsForUser(?)");
        $stmt->bind_param("i", $user_id);
        if($stmt->execute()){
            $trips = $stmt->get_result();
            $stmt->close();
            return $trips;
        }else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time | getPreviousUserTrips | Failed to CALL rmdbp_GetOldTripsForUser($user_id) \n\n",3,ERROR_LOG_PATH);
            $stmt->close();
            return null;
        }
    }
    /**
     * Fetching all user trips and their associate bookings
     * @param String $user_id id of the user
     */
    public function getAllSubTripsFromTrip($trip_id) {
        $stmt = $this->conn->prepare("CALL rmdbp_GetSubTripsForTrip (?)");
        $stmt->bind_param("i", $trip_id);
        if($stmt->execute()){
            $subtrips = $stmt->get_result();
            $stmt->close();
            return $subtrips;
        }else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time | getAllSubTripsFromTrip | CALL rmdbp_GetSubTripsForTrip($trip_id) \n\n",3,ERROR_LOG_PATH);
            $stmt->close();
            return null;
        }

    }

    public function getAllCommentsForThisTrip($userId,$tripId) {
        $stmt = $this->conn->prepare("CALL rmdbp_getCommentsByUserOnATrip(?,?)");
        $stmt->bind_param("ii",$userId, $tripId);
        if($stmt->execute()){
            $subtrips = $stmt->get_result();
            $stmt->close();
            return $subtrips;
        }else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time | getAllCommentsForThisTrip | CALL rmdbp_getCommentsByUserOnATrip($userId,$tripId) \n\n",3,ERROR_LOG_PATH);
            $stmt->close();
            return null;
        }
    }

    /**
     *  Get Lucky Method
     *
     * /
     */
    public function getLuckyTrips($departure_time,$departure_name,$arrival_name,$departure_lt,$departure_lg,$arrival_lt,$arrival_lg,$isWomanOnly,$user_id){
        $stmt = $this->conn->prepare("call rmdbp_getLucky(?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("sssddddii", $departure_time,$departure_name,$arrival_name,$departure_lt,$departure_lg,$arrival_lt,$arrival_lg,$isWomanOnly,$user_id);
        if($stmt->execute()){
            $trips = $stmt->get_result();
            $stmt->close();
            return $trips;
        }else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time | getLuckyTrips | Failed to CALL rmdbp_getLucky($departure_time,$departure_name,$arrival_name,$departure_lt,$departure_lg,$isWomanOnly,$user_id) \n\n",3,ERROR_LOG_PATH);
            $stmt->close();
            return null;
        }
    }
    /**
     *  Get Lucky Method
     *
     * /
     */
    public function getSpecificTrip($user_id,$trip_public_id){
        $stmt = $this->conn->prepare("call rmdbp_getSpecificTrip(?,?)");
        $stmt->bind_param("is",$user_id, $trip_public_id);
        if($stmt->execute()){
            $trips = $stmt->get_result();
            $stmt->close();
            return $trips;
        }else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time | getSpecificTrip | Failed to CALL rmdbp_getSpecificTrip($user_id, $trip_public_id) \n\n",3,ERROR_LOG_PATH);
            $stmt->close();
            return null;
        }
    }
    /**
     * Updating trip
     * @param String $usr_id user id
     * @param String $trip_id trip id
     * @param String $departure_time departure time
     */
    public function updateTrip($usr_id, $trip_id, $departure_time, $price) {
        if ($price == null || $price == 0 || $departure_time == 0 || $departure_time == null)
            return null;
        $stmt = $this->conn->prepare("UPDATE RMDB_TRI set t.departure_time = ?, price = ? WHERE t.usr_id = ? AND t.id = ?");
        $stmt->bind_param("sss", $departure_time, $price, $usr_id, $trip_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        //TODO create methode which warn subscribers about that the time has changed
        return $num_affected_rows > 0;
    }

    /**
     * Deleting a trip
     * @param String $task_id id of the task to delete
     */
    public function deleteTrip($user_id, $trip_id) {
        //A user can't delete a trip which already has a reservation
        $stmt = $this->conn->prepare("CALL rmdbp_canceltrip(?,?)");
        $stmt->bind_param("si", $trip_id, $user_id);
        $num_affected_rows = false;
        if($stmt->execute()){
            $num_affected_rows = true;
        }else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time | deleteTrip | CALL rmdbp_canceltrip($trip_id, $user_id) \n\n",3,ERROR_LOG_PATH);
        }
        $stmt->close();
        return $num_affected_rows;
    }

    /**
     * function rateUser
     * @param type $user_id
     * @param type $trip_id
     * @param type $ratedUser_id
     * @param type $givenRate
     * @param type $givenComment
     * @return boolean
     */

    public function rateUser($user_id, $trip_id,$ratedUser_id,$givenRate,$givenComment) {
        //A user can't delete a trip which already has a reservation
        $stmt = $this->conn->prepare("CALL rmdbp_rateuser(?,?,?,?,?)");
        $stmt->bind_param("issis", $user_id, $trip_id,$ratedUser_id,$givenRate,$givenComment);
        $result = false;
        if($stmt->execute()){
            $result = true;
        }else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time | rateUser | CALL rmdbp_rateuser($user_id, $trip_id,$ratedUser_id,$givenRate,$givenComment) \n\n",3,ERROR_LOG_PATH);
        }
        $stmt->close();
        return $result;
    }

    /* ------------- RMDB_RES table method ------------------ */

    /**
     * Function to Confirm a trip purchass. Can only be fired by the seller.
     * As soon as the the line is entered, the buyer should be notified.
     * @param String $user_id id of the user
     * @param String $task_id id of the task
     */
    public function booktrip($usr_id, $trip_id, $seats_reserved,$price, $from,$to) {
        $stmt = $this->conn->prepare("CALL rmdbp_bookNewTrip(?,?,?,?,?,?)");
        $stmt->bind_param("siiiii", $trip_id, $usr_id, $seats_reserved,$price, $from,$to);
        //TODO inserer un message dans le fil du trip_id et envoyer une notif a proprietaire du trip
        if($stmt->execute()){
            $ret = $stmt->get_result();
        }else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time | booking | Failed to CALL rmdbp_bookNewTrip($trip_id, $usr_id, $seats_reserved,$price, $from,$to) \n\n",3,ERROR_LOG_PATH);
            $stmt->close();
            $ret = null;
        }
        $stmt->close();
        return $ret;
    }
    /**
     * bookCommuteTrip
     * $user_id
     * $trip_id
     * $seats
     * $price
     * $departureTime
     * $from_sub_trip_order
     * $to_sub_trip_order
     */
    public function bookCommuteTrip($user_id,$trip_id, $seats,$price,$departureTime,$from_sub_trip_order,$to_sub_trip_order){
        $stmt = $this->conn->prepare("CALL rmdbp_bookCommuteTrip(?,?,?,?,?,?,?)");
        $stmt->bind_param("siiisii", $trip_id, $user_id, $seats,$price,$departureTime,$from_sub_trip_order,$to_sub_trip_order);
        //TODO inserer un message dans le fil du trip_id et envoyer une notif a proprietaire du trip
        $ret = null;
        if($stmt->execute()){
            $ret = $stmt->get_result();
        }else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time | bookCommuteTrip | CALL rmdbp_bookCommuteTrip($trip_id, $user_id, $seats,$price,$departureTime,$from_sub_trip_order,$to_sub_trip_order) \n\n",3,ERROR_LOG_PATH);
        }
        $stmt->close();
        return $ret;
    }

    /**
     *
     * @param type $user_id
     * @param type $book_id
     * @param type $flag
     *
     */
    public function updateBookingStatus($user_id, $book_id,$flag){
        $stmt = $this->conn->prepare("CALL rmdbp_updatebookingstatus(?,?,?)");
        $stmt->bind_param("isi", $user_id, $book_id,$flag);
        //TODO inserer un message dans le fil du trip_id et envoyer une notif a proprietaire du trip
        if($stmt->execute()){
            $ret = $stmt->get_result();
        }else{
            $ret = null;
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time | updateBookingStatus | CALL rmdbp_updatebookingstatus($user_id, $book_id,$flag) \n\n",3,ERROR_LOG_PATH);
        }
        $stmt->close();
        return $ret;
    }
    /**
     * function to get all bookings for a given user
     *
     *
     */
    public function getAllUserBookings($user_id) {
        $stmt = $this->conn->prepare("CALL rmdbp_getAllBookingForUser(?)");
        $stmt->bind_param("i", $user_id);
        if($stmt->execute()){
            $bookings = $stmt->get_result();
        }else{
            $bookings = null;
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time | getAllUserBookings | CALL rmdbp_getAllBookingForUser($user_id) \n\n",3,ERROR_LOG_PATH);
        }
        $stmt->close();
        return $bookings;
    }

    /**
     * function to get all bookings for a given user
     *
     *
     */
    public function getPreviousUserBookings($user_id) {
        $stmt = $this->conn->prepare("CALL rmdbp_getPreviousBookingForUser(?)");
        $stmt->bind_param("i", $user_id);
        if($stmt->execute()){
            $bookings = $stmt->get_result();
            $stmt->close();
            return $bookings;
        }else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time | getPreviousUserBookings | CALL rmdbp_getPreviousBookingForUser($user_id) \n\n",3,ERROR_LOG_PATH);
            $stmt->close();
            return null;
        }
    }

    public function updateBooking($user_id,$trip_id, $booking_id, $seats) {
        $stmt = $this->conn->prepare("CALL rmdbp_updateBooking(?,?,?,?)");
        $stmt->bind_param("iiii", $user_id, $trip_id, $booking_id, $seats);
        if($stmt->execute()){
            $bookings = $stmt->get_result();
            $stmt->close();
            return $bookings;
        }else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time | updateBooking | CALL rmdbp_updateBooking($user_id, $trip_id, $booking_id, $seats) \n\n",3,ERROR_LOG_PATH);
            $stmt->close();
            return null;
        }
    }

    public function updateRegionAndCreateNewTarget($userId,$from_region){
        $stmt = $this->conn->prepare("CALL updateRegionAndCreateNewTarget(?,?)");
        $stmt->bind_param("is", $userId,$from_region);
        if($stmt->execute()){
            $bookings = $stmt->get_result();
            $stmt->close();
            return $bookings;
        }else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time | updateRegionAndCreateNewTarget | CALL updateRegionAndCreateNewTarget($userId,$from_region) \n\n",3,ERROR_LOG_PATH);
            $stmt->close();
            return null;
        }
    }

    /** deleteBooking : Deleting a booking for a given user
     * The booking will not be deleted physically from DB.
     * status 'cancelled' will be assigned to this booking.
     * The client has a credit = price_paid to be used for another booking.
     * Can be used when a user deletes their booking
     * Or when a user deletes their trip which is related to a booking
     * Important : Deleting a booking should increment the available trips
     * */
    public function cancelBooking($user_id, $booking_id) {
        $stmt = $this->conn->prepare("CALL rmdbp_cancelBooking(?,?)");
        $stmt->bind_param("is", $user_id, $booking_id);
        if($stmt->execute()){
            $stmt->close();
            return $ret;
        }else{
           $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time | cancelBooking | CALL rmdbp_cancelBooking($user_id, $booking_id) \n\n",3,ERROR_LOG_PATH);
            $stmt->close();
            return null;
        }
    }

    /*     * ******************************** Message Methods ***************
     * getConversations : returns a list on conversation objects or a specific conversation
     *
     *
     */

    public function getUserConversations($tripId) {
        $stmt = $this->conn->prepare("call rmdbp_getMessageFromTripID(?)");
        $stmt->bind_param("s", $tripId);
        if($stmt->execute()){
            $ret = $stmt->get_result();
            $stmt->close();
            return $ret;
        }
        else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time | getUserConversations | CALL rmdbp_getMessageFromTripID($tripId) \n\n",3,ERROR_LOG_PATH);
            $stmt->close();
            return null;
        }
    }

    public function getListOfCommets($user_public_id){
        $stmt = $this->conn->prepare("call rmdbp_getListOfCommets(?)");
        $stmt->bind_param("s", $user_public_id);
        if($stmt->execute()){
            $ret = $stmt->get_result();
            $stmt->close();
            return $ret;
        }
        else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time | getListOfCommets | CALL rmdbp_getListOfCommets($user_public_id) \n\n",3,ERROR_LOG_PATH);
            $stmt->close();
            return null;
        }
    }

    public function createMessage($user_id,$on_trip_id,$msg){
        $stmt = $this->conn->prepare("call rmdbp_sendNewMessage(?,?,?)");
        $stmt->bind_param("sis", $on_trip_id, $user_id, $msg);
        if($stmt->execute()){
            $ret = $stmt->get_result();
            $stmt->close();
            return $ret;
        }else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time | createMessage | call rmdbp_sendNewMessage($on_trip_id, $user_id, $msg) \n\n",3,ERROR_LOG_PATH);
            $stmt->close();
        }
        return false;
    }
    public function createNewSMS($user_id,$phone,$msg,$action){
        $stmt = $this->conn->prepare("call rmdb_queue_new_sms(?,?,?,?)");
        $stmt->bind_param("isss", $user_id,$phone,$msg,$action);
        $message_id = -1;
        if($stmt->execute()){
            $message_id = $stmt->get_result()->fetch_assoc();
            $message = array();//new EnvayaSMS_OutgoingMessage();
            $message['id'] = $message_id['lid'];
            $message['to'] = $phone;
            $message['message'] = $msg;
            //file_put_contents(OUTGOING_DIR_NAME."".$message_id['lid'].".json", json_encode($message));
        } else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time | createNewSMS | call rmdb_queue_new_sms($user_id,$phone,$msg,$action) \n\n",3,ERROR_LOG_PATH);
        }
        $stmt->close();
        return $message_id;
    }
    public function update_SMS_status($sms_id,$status){
        $stmt = $this->conn->prepare("call rmdb_update_sms_in_queue(?,?)");
        $stmt->bind_param("is", $sms_id,$status);
        $r = false;
        if($stmt->execute()){
            $r = true;
        }else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time | update_SMS_status | call rmdb_update_sms_in_queue($sms_id,$status) \n\n",3,ERROR_LOG_PATH);
        }
        $stmt->close();
        return $r;
    }
    public function get_SMS_inqueue(){
        $stmt = $this->conn->prepare("CALL rmdb_get_sms_in_queue()");
        //$stmt->bind_param("is", $sms_id,$status);
        $r = false;
        if($stmt->execute()){
            $r = $stmt->get_result();
        }else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time | get_SMS_inqueue | CALL rmdb_get_sms_in_queue() \n\n",3,ERROR_LOG_PATH);
        }
        $stmt->close();
        return $r;
    }
    public function update_Notification($sms_id,$chanel){
        $stmt = $this->conn->prepare("call rmdbp_updateNotification(?,?)");
        $stmt->bind_param("is", $sms_id,$chanel);
        $r = false;
        if($stmt->execute()){
            $r = true;
        }else{
             $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time | update_Notification | call rmdbp_updateNotification($sms_id,$chanel) \n\n",3,ERROR_LOG_PATH);
        }
        $stmt->close();
        return $r;
    }
    public function get_Notifications(){
        $stmt = $this->conn->prepare("CALL rmdbp_getNotifications()");
        //$stmt->bind_param("is", $sms_id,$status);
        $r = false;
        if($stmt->execute()){
            $r = $stmt->get_result();
        }
        $stmt->close();
        return $r;
    }
    public function get_NotificationList($userId){
        $stmt = $this->conn->prepare("CALL rmdbp_NotificationList(?)");
        $stmt->bind_param("i", $userId);
        $r = false;
        if($stmt->execute()){
            $r = $stmt->get_result();
        } else{
             $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time | get_NotificationList | call rmdbp_NotificationList($userId) \n\n",3,ERROR_LOG_PATH);
        }
        $stmt->close();
        return $r;
    }
    public function validateGroupMembership($user_id,$group_to_join,$user_to_validate){
        $stmt = $this->conn->prepare("CALL rmdbp_validateGroupMembership(?,?,?,?,?)");
        $valdidation_token = 'NONE';
        $id = 0;
        $stmt->bind_param("isssi", $user_id,$group_to_join,$user_to_validate,$valdidation_token,$id);
        $ret = null;
        if($stmt->execute()){
            $ret = $stmt->get_result();

        }else{
            error_log("CALL rmdbp_validateGroupMembership($user_id,$group_to_join,$user_to_validate,$valdidation_token,0) \n\n",3,ERROR_LOG_PATH);
        }
        $stmt->close();
        return $ret;
    }
    public function getUserGroups($user_id){
        $stmt = $this->conn->prepare("CALL rmdbp_getUserGroups(?)");
        $stmt->bind_param("i", $user_id);
        if($stmt->execute()){
            $r = $stmt->get_result();
            $stmt->close();
            return $r;
        } else{
            error_log("CALL rmdbp_getUserGroups($user_id) \n\n",3,ERROR_LOG_PATH);
        }
        $stmt->close();
        return NULL;
    }
    public function getTripsForGroup($group_id){
        $stmt = $this->conn->prepare("CALL rmdbp_getTripsForGroup(?)");
        $stmt->bind_param("s", $group_id);
        if($stmt->execute()){
            $r = $stmt->get_result();

            $stmt->close();
            return $r;
        } else{
            error_log("CALL rmdbp_getTripsForGroup($group_id) \n\n",3,ERROR_LOG_PATH);
        }
        $stmt->close();
        return NULL;
    }
    public function searchTripsInGroup($groupId){
        $stmt = $this->conn->prepare("CALL rmdbp_searchTripsInGroup(?)");
        $stmt->bind_param("s", $groupId);
        if($stmt->execute()){
            $r = $stmt->get_result();
            $stmt->close();
            return $this->writeSearchTrips($r);
        } else{
            error_log("CALL rmdbp_searchTripsInGroup($groupId) \n\n",3,ERROR_LOG_PATH);
        }
        $stmt->close();
        return NULL;
    }
    public function getSingleTrip($tripId){
        $stmt = $this->conn->prepare("CALL rmdbp_getSingleTrip(?)");
        $stmt->bind_param("s", $tripId);
        if($stmt->execute()){
            $r = $stmt->get_result();
            $stmt->close();
            return $this->writeSearchTrips($r);
        } else{
            error_log("CALL rmdbp_getSingleTrip($tripId) \n\n",3,ERROR_LOG_PATH);
        }
        $stmt->close();
        return NULL;
    }
    public function getPendingGroupRequests($userId){
        $stmt = $this->conn->prepare("CALL rmdbp_getPendingGroupRequests(?)");
        $stmt->bind_param("i", $userId);
        if($stmt->execute()){
            $r = $stmt->get_result();
            $stmt->close();
            return $r;
        } else{
            error_log("CALL rmdbp_getPendingGroupRequests($userId) \n\n",3,ERROR_LOG_PATH);
        }
        $stmt->close();
        return NULL;
    }
    public function searchGroups($searchString){
        $stmt = $this->conn->prepare("CALL rmdbp_searchGroups(?)");
        $stmt->bind_param("s", $searchString);
        if($stmt->execute()){
            $r = $stmt->get_result();
            $stmt->close();
            return $r;
        }else{
            error_log("CALL rmdbp_searchGroups($searchString) \n\n",3,ERROR_LOG_PATH);
        }
        $stmt->close();
        return NULL;
    }
    public function validateGroupMembershipByToken($group_to_join,$user_to_validate,$token){
        $stmt = $this->conn->prepare("CALL rmdbp_validateGroupMembership(?,?,?,?,?)");
        $empty = "";
        $zero = 0;
        $stmt->bind_param("isssi", $zero,$group_to_join,$empty,$token,$user_to_validate);
        $ret = 0;
        if($stmt->execute()){
            $r = $stmt->get_result()->fetch_assoc();
            $ret = $r['update_result'];
        }else{
            error_log("CALL rmdbp_validateGroupMembership($zero,$group_to_join,$empty,$token,$user_to_validate) \n\n",3,ERROR_LOG_PATH);
        }
        $stmt->close();
        return $ret;
    }
    public function update_NotificationList($user_id,$notificationIds){
        $stmt = $this->conn->prepare("CALL rmdbp_updateNotificationList(?,?)");
        $stmt->bind_param("is", $user_id,$notificationIds);
        $r = false;
        if($stmt->execute()){
            $r = true;
        } else{
           error_log("CALL rmdbp_updateNotificationList($user_id,$notificationIds) \n\n",3,ERROR_LOG_PATH);
        }
        $stmt->close();
        return $r;
    }
    public function subscribeUser($user_id){
        $stmt = $this->conn->prepare("call rmdb_subscribe_user(?)");
        $stmt->bind_param("i", $user_id);
        $r = null;
        if($stmt->execute()){
            $r = $stmt->get_result();
        }else{
           error_log("CALL rmdb_subscribe_user($user_id) \n\n",3,ERROR_LOG_PATH);
        }
        $stmt->close();
        return $r;
    }
    public function acceptTermsAndConditions($user_id,$version){
        $stmt = $this->conn->prepare("CALL rmdbp_accept_terms(?,?)");
        $stmt->bind_param("ii", $user_id,$version);
        $r = false;
        if($stmt->execute()){
            $r = true;
        }else{
           error_log("CALL rmdbp_accept_terms($user_id,$version) \n\n",3,ERROR_LOG_PATH);
        }
        $stmt->close();
        return $r;
    }

    /**
     * Group methods start here
     *
     */
    public function createGroup($user_id,$p_group_name,$p_group_type,$p_group_email_suffix,$p_group_is_public,$p_group_description,$p_group_pic){
        $stmt = $this->conn->prepare("CALL rmdbp_createGroup(?,?,?,?,?,?,?)");
        $gic = NULL;
        $pattern = '/^(@)(.+)\.([a-zA-Z]{2,3})$/';
        if($p_group_email_suffix && preg_match($pattern, $p_group_email_suffix)){
            $gic = $p_group_email_suffix;
        }
        $stmt->bind_param("isisiss", $user_id,$p_group_name,$p_group_type,$gic,$p_group_is_public,$p_group_description,$p_group_pic);
        $r = false;
        if($stmt->execute()){
            $r = $stmt->get_result();
        }else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time | booking | Failed to CALL rmdbp_createGroup($user_id,$p_group_name,$p_group_type,$p_group_email_suffix,$p_group_is_public,$p_group_description,$p_group_pic) \n\n",3,ERROR_LOG_PATH);
        }
        $stmt->close();
        return $r;
    }
    public function getGroupEmailValidator($p_group_id){
        $stmt = $this->conn->prepare("CALL rmdbp_getGroupEmailValidator(?)");
        $stmt->bind_param("s", $p_group_id);
        $r = false;
        if($stmt->execute()){
            $r = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $r['group_email_suffix'];
        }else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time | booking | Failed to CALL rmdbp_createGroup($user_id,$p_group_name,$p_group_type,$p_group_email_suffix,$p_group_is_public,$p_group_description,$p_group_pic) \n\n",3,ERROR_LOG_PATH);
        }
        $stmt->close();
        return $r;
    }
    public function addUserToGroup($user_id,$group_id){
        $stmt = $this->conn->prepare("CALL rmdbp_addUserToGroup(?,?)");
        $stmt->bind_param("is", $user_id,$group_id);
        $r = false;
        if($stmt->execute()){
            $r = $stmt->get_result();
            $stmt->close();
            return $r;
        }else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time | booking | Failed to CALL rmdbp_addUserToGroup($user_id,$group_id) \n\n",3,ERROR_LOG_PATH);
        }
        $stmt->close();
        return $r;

    }
    public function getUserListInGroup($user_id,$group_id){
        $stmt = $this->conn->prepare("CALL rmdbp_getUserListInGroup(?,?)");
        $stmt->bind_param("is", $user_id,$group_id);
        $r = false;
        if($stmt->execute()){
            $r = $stmt->get_result();
            $stmt->close();
            return $r;
        }else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time | booking | Failed to CALL rmdbp_getUserListInGroup($user_id,$group_id) \n\n",3,ERROR_LOG_PATH);
        }
        $stmt->close();
        return $r;

    }
    public function makeUserAdminInGroup($user_id,$group_id,$new_admin_user){
        $stmt = $this->conn->prepare("CALL rmdbp_updateUserRoleInGroup(?,?,?)");
        error_log("CALL rmdbp_updateUserRoleInGroup($user_id,$group_id,$new_admin_user) \n\n",3,ERROR_LOG_PATH);
        $stmt->bind_param("iss", $user_id,$group_id,$new_admin_user);
        $r = false;
        if($stmt->execute()){
            $r = $stmt->get_result();
            $stmt->close();
            return $r;
        }else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time | booking | Failed to CALL rmdbp_updateUserRoleInGroup($user_id,$group_id,$new_admin_user) \n\n",3,ERROR_LOG_PATH);
        }
        $stmt->close();
        return $r;

    }
    public function updateNotificationFlag($user_id,$group_id){
        $stmt = $this->conn->prepare("CALL rmdbp_reverseNotificationFlagForGroup(?,?)");
        $stmt->bind_param("is", $user_id,$group_id);
        $r = false;
        if($stmt->execute()){
            $stmt->close();
            return true;
        }else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time | booking | Failed to CALL rmdbp_reverseNotificationFlagForGroup($user_id,$group_id) \n\n",3,ERROR_LOG_PATH);
        }
        $stmt->close();
        return $r;
    }


    /**
     * Alerts part starts here
     *
     */
    //
    public function createTripAlert($user_id,$departure_name ,$departure_lt ,$departure_lg ,$arrival_name ,$arrival_lt ,$arrival_lg ,$departure_time ,$isRepeated ,$endDate ,$pattern ){
        $stmt = $this->conn->prepare("CALL rmdbp_createNewTripAlert(?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("isddsddsisi", $user_id,$departure_name ,$departure_lt ,$departure_lg ,$arrival_name ,$arrival_lt ,$arrival_lg ,$departure_time ,$isRepeated ,$endDate ,$pattern);
        $r = false;
        if($stmt->execute()){
            $results = $stmt->get_result();
            $stmt->close();
            return $results;
        }else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time | createTripAlert| Failed to CALL rmdbp_createNewTripAlert($user_id,$departure_name ,$departure_lt ,$departure_lg ,$arrival_name ,$arrival_lt ,$arrival_lg ,$departure_time ,$isRepeated ,$endDate ,$pattern) \n\n",3,ERROR_LOG_PATH);
        }
        $stmt->close();
        return $r;
    }
    public function getActiveTripAlert($user_id){
        $stmt = $this->conn->prepare("CALL rmdbp_getActiveTripAlert(?)");
        $stmt->bind_param("i", $user_id);
        $r = false;
        if($stmt->execute()){
            $results = $stmt->get_result();
            $stmt->close();
            return $results;
        }else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time | booking | Failed to CALL rmdbp_getActiveTripAlert($user_id) \n\n",3,ERROR_LOG_PATH);
        }
        $stmt->close();
        return $r;
    }
    public function disableTripAlert($user_id,$alert_id){
        $stmt = $this->conn->prepare("CALL rmdbp_disableTripAlert(?,?)");
        $stmt->bind_param("is", $user_id,$alert_id);
        $r = false;
        if($stmt->execute()){
            $results = $stmt->get_result();
            $stmt->close();
            return $results;
        }else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time | booking | Failed to CALL rmdbp_disableTripAlert($user_id,$alert_id) \n\n",3,ERROR_LOG_PATH);
        }
        $stmt->close();
        return $r;
    }
    public function saveDriverLicenceDetails($user_id,$dl_number,$dl_date,$dl_end,$dl_country,$dl_condition){
        $stmt = $this->conn->prepare("CALL rmdbp_saveDriverLicenceDetails(?,?,?,?,?,?)");
        $stmt->bind_param("isssss", $user_id,$dl_number,$dl_date,$dl_end,$dl_country,$dl_condition);
        $r = false;
        if($stmt->execute()){
            $results = $stmt->get_result();
            $stmt->close();
            return $results;
        }else{
            $time = date("Y-m-d H:i:s",time() + 61200 );
            error_log("$time | driver licence | Failed to CALL rmdbp_saveDriverLicenceDetails($user_id,$dl_number,$dl_date,$dl_end,$dl_country,$dl_condition) \n\n",3,ERROR_LOG_PATH);
        }
        $stmt->close();
        return $r;
    }

    private function writeSearchTrips($result){
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
                    //$tmp[GROUP_NAME] = $trip["group_name"];
                    //$tmp[GROUP_ID] = $trip["group_public_id"];
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

}

?>