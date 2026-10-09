<?php
namespace App\Controllers;
use App\Core\Security;
abstract class BaseController {
 protected function requirePost(): void {if($_SERVER['REQUEST_METHOD']!=='POST') Security::abort(405);Security::verify();}
 protected function required(string $key,int $limit=191): string {$v=\input($key,$limit);if($v==='') throw new \RuntimeException(ucfirst($key).' is required.');return $v;}
 protected function id(string $key): int {$id=filter_var($_POST[$key]??null,FILTER_VALIDATE_INT);if(!$id || $id<1) throw new \RuntimeException('Invalid '. $key);return (int)$id;}
 protected function execute(callable $fn,string $fallback): void {try{$fn();}catch(\PDOException $e){error_log($e->getMessage());Security::redirect($fallback,'Database operation failed. Check for duplicates or referenced records.');}catch(\RuntimeException $e){Security::redirect($fallback,$e->getMessage());}}
}
