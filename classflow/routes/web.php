<?php
use App\Controllers\{AuthController as Auth,AdminController as Admin,TeacherController as Teacher,StudentController as Student};
return [
 ['GET','#^/$#',Auth::class,'home'],
 ['GET','#^/login$#',Auth::class,'loginPage'],['POST','#^/login$#',Auth::class,'login'],['POST','#^/logout$#',Auth::class,'logout'],
 ['GET','#^/admin$#',Admin::class,'index'],['POST','#^/admin/users$#',Admin::class,'user'],['POST','#^/admin/courses$#',Admin::class,'course'],['POST','#^/admin/sections$#',Admin::class,'section'],
 ['GET','#^/teacher$#',Teacher::class,'index'],['GET','#^/teacher/sections/(\d+)$#',Teacher::class,'detail'],
 ['POST','#^/teacher/materials$#',Teacher::class,'material'],['POST','#^/teacher/assignments$#',Teacher::class,'assignmentSave'],
 ['GET','#^/teacher/assignments/(\d+)/submissions$#',Teacher::class,'submissions'],['POST','#^/teacher/grades$#',Teacher::class,'grade'],
 ['GET','#^/teacher/materials/(\d+)/download$#',Teacher::class,'downloadMaterial'],['GET','#^/teacher/submissions/(\d+)/download$#',Teacher::class,'downloadSubmission'],
 ['GET','#^/student$#',Student::class,'index'],['POST','#^/student/enroll$#',Student::class,'enroll'],['GET','#^/student/sections/(\d+)$#',Student::class,'detail'],
 ['POST','#^/student/submissions$#',Student::class,'submit'],['GET','#^/student/materials/(\d+)/download$#',Student::class,'downloadMaterial'],['GET','#^/student/submissions/(\d+)/download$#',Student::class,'downloadSubmission'],
];
