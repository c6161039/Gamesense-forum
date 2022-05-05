<?php
$link = mysqli_connect("localhost", "y60313g2_forum","&UIzKIq4");
$database = mysqli_select_db($link, "y60313g2_forum");

$user = $_GET['username'];
$password = $_GET['nopass'];
$hwid = $_GET['hwid'];
$tables = "users";

$sql = "SELECT * FROM ". $tables ." WHERE username = '". mysqli_real_escape_string($link,$user) ."'" ;
$result = $link->query($sql);
if ($result->num_rows > 0) {
    // Outputting the rows
    while($row = $result->fetch_assoc())
    {
        
        $password = $row['nopass'];
        
        function Redirect($url, $permanent = false)
        {
            if (headers_sent() === false)
            {
                header('Location: ' . $url, true, ($permanent === true) ? 301 : 302);
            }
        exit();
        }
        
        if($password != $row['nopass'])
        {
            echo "p0<br>"; // Wrong pass, user exists
        }
        else
        {
            echo "p1<br>"; // Correct pass
        }
        
        echo "g" . $row['group_id'] . "<br>";

        if (strlen($row['hwid']) > 1)
        {
            if ($hwid != $row['hwid'])
            {
                echo "h2"; // Wrong
            }
            else
            {
                echo "h1"; // Correct
            }
        }
        else
        {
            $sql = "UPDATE ". $tables ." SET hwid='$hwid' WHERE username='$user'";
            if(mysqli_query($link, $sql))
            {
                echo $row['hwid'];
                echo "h3"; // HWID Set
            }
            else
            {
                echo "h4"; // Else errors
            }
        }
    }
}  
?>