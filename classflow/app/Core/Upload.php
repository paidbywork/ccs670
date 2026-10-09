<?php
namespace App\Core;
final class Upload {
 private const TYPES=['application/pdf'=>'pdf','image/png'=>'png','image/jpeg'=>'jpg','text/plain'=>'txt','application/zip'=>'zip','application/vnd.openxmlformats-officedocument.wordprocessingml.document'=>'docx'];
 public static function save(string $field): array {
  $f=$_FILES[$field]??null;
  if (!$f || $f['error']!==UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) throw new \RuntimeException('Select a valid file.');
  $sizeLimit=max(1,(int)($_ENV['UPLOAD_MAX_MB']??10))*1024*1024;
  if ($f['size']<=0 || $f['size']>$sizeLimit) throw new \RuntimeException('Invalid file size; maximum '.$_ENV['UPLOAD_MAX_MB'].' MB.');
  $type=(new \finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
  if (!isset(self::TYPES[$type])) throw new \RuntimeException('Unsupported file type (PDF, PNG, JPG, TXT, ZIP, DOCX).');
  $stored=bin2hex(random_bytes(20)).'.'.self::TYPES[$type];
  $dir=dirname(__DIR__,2).'/storage/uploads';
  if (!is_dir($dir) && !mkdir($dir,0700,true)) throw new \RuntimeException('Upload directory unavailable.');
  if (!move_uploaded_file($f['tmp_name'],$dir.'/'.$stored)) throw new \RuntimeException('Unable to save file.');
  return ['path'=>$stored,'name'=>mb_substr(basename(str_replace('\\','/',$f['name'])),0,255)];
 }
 public static function send(string $file,string $name): never {
  if (!preg_match('/^[a-f0-9]{40}\.(pdf|png|jpg|txt|zip|docx)$/',$file)) Security::abort();
  $path=dirname(__DIR__,2).'/storage/uploads/'.$file;
  if(!is_file($path)) Security::abort();
  header('Content-Type: application/octet-stream');header('Content-Disposition: attachment; filename="'.str_replace(['"',"\r","\n"],'',basename($name)).'"');
  header('Content-Length: '.filesize($path));readfile($path);exit;
 }
}
