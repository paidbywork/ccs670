<?php
namespace App\Core;
final class Security {
 public static function token(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
 public static function verify(): void {if (!hash_equals(self::token(),(string)($_POST['_csrf']??''))) {http_response_code(419);exit('Invalid CSRF token. Refresh and retry.');}}
 public static function user(): ?array {return $_SESSION['user']??null;}
 public static function requireRole(string ...$roles): array {
   $u=self::user();if (!$u) {header('Location: /login');exit;}
   if (!in_array($u['role'],$roles,true)) {http_response_code(403);exit('Forbidden');}
   $fresh=DB::one('SELECT id,name,email,role,is_active FROM users WHERE id=?',[$u['id']]);
   if (!$fresh || !$fresh['is_active'] || $fresh['role']!==$u['role']) {self::logout();header('Location: /login');exit;}
   $_SESSION['user']=$fresh;return $fresh;
 }
 public static function logout(): void {$_SESSION=[];session_regenerate_id(true);}
 public static function redirect(string $path,string $message=''): never {if($message) $_SESSION['flash']=$message;header('Location: '.$path, true,303);exit;}
 public static function abort(int $status=404): never {http_response_code($status);exit($status===403?'Forbidden':'Not found');}
}
