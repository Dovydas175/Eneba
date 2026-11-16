<?php
declare(strict_types=1);
$allowed=getenv('APP_ORIGIN')?:'';
$sessionPath=getenv('SESSION_SAVE_PATH')?:'';if($sessionPath!==''&&is_dir($sessionPath))ini_set('session.save_path',$sessionPath);
session_set_cookie_params(['httponly'=>true,'secure'=>str_starts_with($allowed,'https://')||(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off'),'samesite'=>'Lax','path'=>'/']);
session_start();
header('Content-Type: application/json; charset=utf-8'); header('X-Content-Type-Options: nosniff'); header('Cache-Control: no-store');
$origin=$_SERVER['HTTP_ORIGIN']??'';
if($origin!==''&&$allowed!==''&&hash_equals($allowed,$origin)){header('Access-Control-Allow-Origin: '.$origin);header('Access-Control-Allow-Credentials: true');header('Vary: Origin');}
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');
if($_SERVER['REQUEST_METHOD']==='OPTIONS'){http_response_code(204);exit;}
$host=getenv('DB_HOST')?:'mysql';$port=(int)(getenv('DB_PORT')?:3306);$db=getenv('DB_NAME')?:'playvault';$user=getenv('DB_USER')?:'playvault';$pass=getenv('DB_PASSWORD')?:'playvault_dev';
$options=[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC];$ca=getenv('DB_SSL_CA')?:'';if($ca!==''){$options[PDO::MYSQL_ATTR_SSL_CA]=$ca;$options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT]=true;}
$pdo=new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",$user,$pass,$options);
function respond(mixed $data,int $status=200):never{http_response_code($status);echo json_encode($data,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);exit;}
function body():array{$data=json_decode(file_get_contents('php://input'),true);return is_array($data)?$data:[];}
require_once __DIR__.'/search.php';
function userId():int{if(empty($_SESSION['user_id']))respond(['error'=>'Sign in to continue'],401);return(int)$_SESSION['user_id'];}
function csrf():void{$sent=$_SERVER['HTTP_X_CSRF_TOKEN']??'';if(empty($_SESSION['csrf'])||!hash_equals($_SESSION['csrf'],$sent))respond(['error'=>'Session expired. Refresh and try again.'],419);}
