<?php
declare(strict_types=1);
require dirname(__DIR__) . '/bootstrap/app.php';
use App\Core\Security;
use App\Controllers\{AuthController,AdminController,TeacherController,StudentController};
try {
 $path=parse_url($_SERVER['REQUEST_URI']??'/',PHP_URL_PATH) ?: '/';
 $method=$_SERVER['REQUEST_METHOD']??'GET';
 $routes=require dirname(__DIR__).'/routes/web.php';
 foreach($routes as [$verb,$regex,$controller,$action]) {
  if($verb!==$method||!preg_match($regex,$path,$match)) continue;
  $id=isset($match[1])?(int)$match[1]:null;
  $instance=new $controller(); $id===null?$instance->$action():$instance->$action($id);exit;
 }
 Security::abort($method==='GET'?404:405);
} catch (Throwable $e) {
 error_log((string)$e);
 http_response_code(500);
 if(filter_var($_ENV['APP_DEBUG']??'false',FILTER_VALIDATE_BOOLEAN)) echo '<pre>'.e($e->getMessage()).'</pre>';
 else echo 'An unexpected error occurred. See server log.';
}
