<?php
namespace App\Controllers;
use App\Core\{DB,Security,View};
final class AuthController extends BaseController {
 public function home(): void { $u=Security::user();if(!$u) Security::redirect('/login');Security::redirect('/'.$u['role']); }
 public function loginPage(): void {if(Security::user()) Security::redirect('/');View::render('auth/login',['title'=>'Sign in']);}
 public function login(): void {
  $this->requirePost();
  $email=strtolower(\input('email',191)); $password=(string)($_POST['password']??'');
  $u=DB::one('SELECT id,name,email,password,role,is_active FROM users WHERE email=?',[$email]);
  if(!$u || !$u['is_active'] || !password_verify($password,$u['password'])) Security::redirect('/login','Invalid email or password.');
  if(password_needs_rehash($u['password'],PASSWORD_DEFAULT)) DB::exec('UPDATE users SET password=? WHERE id=?',[password_hash($password,PASSWORD_DEFAULT),$u['id']]);
  session_regenerate_id(true);unset($u['password']);$_SESSION['user']=$u;Security::redirect('/');
 }
 public function logout(): void {$this->requirePost();Security::logout();Security::redirect('/login');}
}
