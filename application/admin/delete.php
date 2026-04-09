<?php
require_once(__DIR__ . "/../../data/db.php");
if(isset($_GET['deleteid'])){
    $stuid=$_GET['deleteid'];
    $sql="Delete from faculty where id=$stuid";
    $result=mysqli_query($conn,$sql);

    if($result){
        echo"Deleted successfully";
    }
}

?>