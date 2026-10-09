<?php
require dirname(__DIR__).'/bootstrap/app.php';
use App\Core\{Security,DB};
assert_options(ASSERT_ACTIVE,1);
$token=Security::token();if(strlen($token)!==64) throw new RuntimeException('Bad CSRF token');
if(!password_verify('test-password',password_hash('test-password',PASSWORD_DEFAULT))) throw new RuntimeException('Password hashing failed');
foreach(['users','courses','sections','enrollments','materials','assignments','submissions','grades'] as $table) {if(!preg_match('/^[a-z_]+$/',$table)) throw new RuntimeException('Invalid table');}
echo "PASS: bootstrap, CSRF generation, password hashing, table configuration.\n";
try {$tables=DB::all('SHOW TABLES');echo 'Database connection OK ('.count($tables)." tables).\n";}catch(Throwable $e){echo 'Database not connected: '.$e->getMessage()."\n";}
