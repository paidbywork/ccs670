<?php
namespace App\Controllers;
use App\Core\{DB,Security,View};
final class AdminController extends BaseController {
 public function index(): void {
  Security::requireRole('admin');
  View::render('admin/index',['title'=>'Administration','users'=>DB::all('SELECT id,name,email,role,is_active FROM users ORDER BY id DESC'),'courses'=>DB::all('SELECT * FROM courses ORDER BY id DESC'),'sections'=>DB::all('SELECT s.*,c.code AS course_code,c.title AS course_title,u.name AS teacher_name FROM sections s JOIN courses c ON c.id=s.course_id JOIN users u ON u.id=s.teacher_id ORDER BY s.id DESC')]);
 }
 public function user(): void { $this->requirePost();Security::requireRole('admin');$this->execute(function(){
   $id=(int)($_POST['id']??0);$name=$this->required('name',150);$email=strtolower($this->required('email'));$role=$this->required('role',20);$active=isset($_POST['is_active'])?1:0;
   if(!filter_var($email,FILTER_VALIDATE_EMAIL)||!in_array($role,['admin','teacher','student'],true)) throw new \RuntimeException('Invalid email or role.');
   if($id){$existing=DB::one('SELECT * FROM users WHERE id=?',[$id]);if(!$existing) throw new \RuntimeException('User not found.');
     if($id==Security::user()['id'] && (!$active||$role!=='admin')) throw new \RuntimeException('Cannot remove your own admin access.');
     $pwd=(string)($_POST['password']??'');if($pwd!=='' && strlen($pwd)<8) throw new \RuntimeException('Password must contain at least 8 characters.');
     if($pwd!=='') DB::exec('UPDATE users SET name=?,email=?,role=?,is_active=?,password=? WHERE id=?',[$name,$email,$role,$active,password_hash($pwd,PASSWORD_DEFAULT),$id]);
     else DB::exec('UPDATE users SET name=?,email=?,role=?,is_active=? WHERE id=?',[$name,$email,$role,$active,$id]);
   }else{$pwd=(string)($_POST['password']??'');if(strlen($pwd)<8) throw new \RuntimeException('Password must contain at least 8 characters.');DB::exec('INSERT INTO users(name,email,password,role,is_active) VALUES(?,?,?,?,?)',[$name,$email,password_hash($pwd,PASSWORD_DEFAULT),$role,$active]);}
   Security::redirect('/admin','Account saved.');
  },'/admin');}
 public function course(): void {$this->requirePost();Security::requireRole('admin');$this->execute(function(){
  $id=(int)($_POST['id']??0);$code=strtoupper($this->required('code',40));$title=$this->required('title');$description=\input('description');$active=isset($_POST['is_active'])?1:0;
  if($id) DB::exec('UPDATE courses SET code=?,title=?,description=?,is_active=? WHERE id=?',[$code,$title,$description,$active,$id]);
  else DB::exec('INSERT INTO courses(code,title,description,is_active) VALUES(?,?,?,?)',[$code,$title,$description,$active]);Security::redirect('/admin','Course saved.');
 },'/admin');}
 public function section(): void {$this->requirePost();Security::requireRole('admin');$this->execute(function(){
  $id=(int)($_POST['id']??0);$course=$this->id('course_id');$teacher=$this->id('teacher_id');$name=$this->required('name',120);$active=isset($_POST['is_active'])?1:0;
  if(!DB::one('SELECT id FROM courses WHERE id=?',[$course]) || !DB::one("SELECT id FROM users WHERE id=? AND role='teacher' AND is_active=1",[$teacher])) throw new \RuntimeException('Select a valid course and active teacher.');
  $code=strtoupper(\input('enrollment_code',32));
  if($id){if($code==='') throw new \RuntimeException('Enrollment code is required when editing.');DB::exec('UPDATE sections SET course_id=?,teacher_id=?,name=?,enrollment_code=?,is_active=? WHERE id=?',[$course,$teacher,$name,$code,$active,$id]);}
  else{$code=$code?:strtoupper(bin2hex(random_bytes(5)));DB::exec('INSERT INTO sections(course_id,teacher_id,name,enrollment_code,is_active) VALUES(?,?,?,?,?)',[$course,$teacher,$name,$code,$active]);}
  Security::redirect('/admin','Section saved.');
 },'/admin');}
}
