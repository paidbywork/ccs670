<?php
namespace App\Core;
final class View {
 public static function render(string $view,array $data=[]): void {
  extract($data, EXTR_SKIP);
  $file=dirname(__DIR__).'/Views/'.$view.'.php';
  if (!is_file($file)) Security::abort(404);
  require dirname(__DIR__).'/Views/partials/header.php';
  require $file;
  require dirname(__DIR__).'/Views/partials/footer.php';
 }
}
