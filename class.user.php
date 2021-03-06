<?php
  date_default_timezone_set('Etc/UTC');
  require_once 'dbconfig.php';

class USER
{
 private $conn;

 public function __construct()
 {
  $database = new Database();
  $db = $database->dbConnection();
  $this->conn = $db;
    }

 public function runQuery($sql)
 {
  $stmt = $this->conn->prepare($sql);
  return $stmt;
 }

 public function lasdID()
 {
  $stmt = $this->conn->lastInsertId();
  return $stmt;
 }

 public function register($uname,$email,$upass,$code)
 {
  try
  {
   $password = md5($upass);
   $stmt = $this->conn->prepare("INSERT INTO users(userName,userEmail,userPass,tokenCode)
                                                VALUES(:user_name, :user_mail, :user_pass, :active_code)");
   $stmt->bindparam(":user_name",$uname);
   $stmt->bindparam(":user_mail",$email);
   $stmt->bindparam(":user_pass",$password);
   $stmt->bindparam(":active_code",$code);
   $stmt->execute();
   return $stmt;
  }
  catch(PDOException $ex)
  {
   echo $ex->getMessage();
  }
 }

 public function makeUni($uni_name,$userID,$uni_loc, $ad_id,$uni_des, $uni_im)
 {
    try{
    $stmt = $this->conn->prepare("INSERT INTO affiliateduniversityprofile(name,userID,location,description,studentNo,image)
          VALUES(:uni_name, :userID, :uni_loc, :ad_id,:uni_des,:uni_im)");
    $stmt->bindparam(":uni_name",$uni_name);
    $stmt->bindparam(":userID",$userID);
    $stmt->bindparam(":uni_loc",$uni_loc);
    $stmt->bindparam(":ad_id",$ad_id);
    $stmt->bindparam(":uni_des",$uni_des);
    $stmt->bindparam(":uni_im", $uni_im);
    $stmt->execute();
    return $stmt;
  }
  catch(PDOException $ex)
  {
    die("wasn't able to insert university profile into the database.");
   echo $ex->getMessage();
  }

 }

 public function login($email,$upass)
 {
  try
  {
   $stmt = $this->conn->prepare("SELECT * FROM users WHERE userEmail=:email_id");
   $stmt->execute(array(":email_id"=>$email));
   $userRow=$stmt->fetch(PDO::FETCH_ASSOC);

   if($stmt->rowCount() == 1)
   {
    if($userRow['userStatus']=="Y")
    {
     if($userRow['userPass']==md5($upass))
     {
      $_SESSION['userSession'] = $userRow['userID'];
      return true;
     }
     else
     {
      header("Location: index.php?error");
      exit;
     }
    }
    else
    {
     header("Location: index.php?inactive");
     exit;
    }
   }
   else
   {
    header("Location: index.php?error");
    exit;
   }
  }
  catch(PDOException $ex)
  {
   echo $ex->getMessage();
  }
 }


 public function is_logged_in()
 {
  if(isset($_SESSION['userSession']))
  {
   return true;
  }
 }

public function is_superadmin()
{
    $adminCheck = $this->conn->prepare("SELECT * FROM usersuperadmin WHERE userID = :user_id");
    $adminCheck->execute(array(":user_id"=>$_SESSION['userSession']));
    $row2 = $adminCheck->fetch(PDO::FETCH_ASSOC);
    
    if($adminCheck->rowCount() > 0)
    {
      return true;
    }

}

 public function redirect($url)
 {
  header("Location: $url");
 }

 public function logout()
 {
  session_destroy();
  $_SESSION['userSession'] = false;
 }


 function send_mail($email,$message,$subject)
 {
   require 'PHPMailerAutoload.php';
  $mail = new PHPMailer; // fill in your email information here
  $mail->IsSMTP();
  $mail->SMTPDebug  = 0;
  $mail->Host       = 'mail.google.com';
  $mail->Port       = 587;
  $mail->SMTPSecure = 'tls';
  $mail->SMTPAuth   = true;
  $mail->Username="emailAccount_here@gmail.com";
  $mail->Password="password_here";

  $mail->AddAddress($email);
  $mail->SetFrom('NoReply@Group6.com','Group 6');
  $mail->AddReplyTo('NoReply@Group6.com','Group 6');
  $mail->Subject    = $subject;
  $mail->MsgHTML($message);
  if (!$mail->send()){
    echo "Mailer Error: " .$mail->ErrorInfo;
  } else {
    echo "Message sent!";
  }
 }
}
