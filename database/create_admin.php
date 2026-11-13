<?php
// CLI only: php database/create_admin.php email@example.com "Full Name"
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
require dirname(__DIR__).'/bootstrap/app.php';
use App\Core\DB;
$email=strtolower(trim($argv[1]??''));$name=trim($argv[2]??'');
if(!filter_var($email,FILTER_VALIDATE_EMAIL)||$name===''){fwrite(STDERR,"Usage: php database/create_admin.php email@example.com \"Full Name\"\n");exit(1);}
if(DB::one('SELECT id FROM users WHERE email=?',[$email])){fwrite(STDERR,"Email already exists.\n");exit(1);}
$password=bin2hex(random_bytes(12));DB::exec("INSERT INTO users(name,email,password,role,is_active) VALUES(?,?,?,'admin',1)",[$name,$email,password_hash($password,PASSWORD_DEFAULT)]);
echo "Admin account created. Email: {$email}\nTemporary password: {$password}\nStore securely; the application does not retain plaintext passwords.\n";
