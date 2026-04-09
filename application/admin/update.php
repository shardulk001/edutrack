<?php
require_once(__DIR__ . "/../../data/db.php");
$stuid = $_GET['updateid'];
$sql="select * from faculty where id = $stuid";
$result= mysqli_query($conn,$sql);
$row=mysqli_fetch_assoc($result);
 $fname=$row['firstname'];  
          $lname=$row['lastname'];  
          $email=$row['email'];  
          $program=$row['pos'];
if(isset($_POST["btn"])){

    $fname=$_POST['fname'];
    $lname=$_POST['lname'];
    $email=$_POST['email'];
    $course=$_POST['pos'];

    $sql="update faculty set firstname='$fname',lastname='$lname',email='$email',pos='$course' where id=$stuid";

    $result=mysqli_query($conn,$sql);

    if($result){
        //echo"Data inserted successfully";
        header("location:../faculty/fdisplay.php");
    }

}



?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>faculty</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  </head>
  <body>
    <div class="container my-5">
<form method="post">
  <div class="mb-3">
    <label class="form-label">First Name</label>
    <input type="text" name="fname" class="form-control" placeholder="Enter the Student First Name." autocomplete="off" 
    value=<?php echo $fname; ?>>
     </div>
  <div class="mb-3">
    <label class="form-label">Last Name</label>
    <input type="text" name="lname"class="form-control" placeholder="Enter the Student Last Name." autocomplete="off" 
    value=<?php echo $lname; ?>>
     </div>
  <div class="mb-3">
    <label class="form-label">Email</label>
    <input type="email" name="email"class="form-control" placeholder="Enter the Student Email Addresss." autocomplete="off" 
    value=<?php echo $email; ?>>
     </div>
  <div class="mb-3">
    <label class="form-label">Department</label>
    <input type="text" name="pos" class="form-control" placeholder="Enter the Student Department ." autocomplete="off" 
    value=<?php echo $program; ?>>
     </div>
  
  <button type="submit" name="btn" class="btn btn-primary">Update</button>
</form>

    </div>
    
  </body>
</html>
