<?php 

    require 'connect.php';  //includes php file for connecting to the database

    header('Content-Type: application/json');
    $incomingContentType = $_SERVER['CONTENT_TYPE'];

    if ($incomingContentType != 'application/json'){
        header($_SERVER['SERVER_PROTOCOL']). ' 500 server error';
        echo 'error';
        exit();
    }

    $content = trim(file_get_contents("php://input"));
    $decoded = json_decode($content, true);
    $data = array();
    
    if ($decoded['form'] == 'login'){
        $sql = ("SELECT * FROM USER_DATA WHERE Username=? AND Password=?");
        $stmt = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt, $sql)){ //if condition fails
            echo "SQL STMT FAILED";
        }else {
            //bind params
            mysqli_stmt_bind_param($stmt, "ss",  $decoded['username'], $decoded['password']);
            mysqli_stmt_execute($stmt); //run para in database
            
            $result = mysqli_stmt_get_result($stmt);

            // // fetch the resulting rows as an array
            $user = mysqli_fetch_all($result, MYSQLI_ASSOC);

            if (empty($user)){
                $data = array(
                    "status" => "false",
                    "reason" => "nothing returned from query"
                );
            }else{
                 //creates array to convert to json file
                $data = array(
                "status" => "true",
                "username" => $user[0]['Username'],
                "password" => $user[0]['Password'],
                "currency" => $user[0]['Currency'],
                "hp" => $user[0]['HP'],
                "str" => $user[0]['SR'],
                "agi" => $user[0]['AGI'],
                "spd" => $user[0]['SPD'],
                "drn" => $user[0]['DRN']
                );
            }
        }
    }else if ($decoded['form'] == 'register'){
        $sql = ("SELECT * FROM USER_DATA WHERE Username=?");
        $stmt = mysqli_stmt_init($conn);
        mysqli_stmt_prepare($stmt, $sql);

        mysqli_stmt_bind_param($stmt, "s",  $decoded['username']);
        mysqli_stmt_execute($stmt); //run para in database
            
        $result = mysqli_stmt_get_result($stmt);

        // // fetch the resulting rows as an array
        $user = mysqli_fetch_all($result, MYSQLI_ASSOC);

        if (empty($user)){ // user name is unique
            $stmt = $conn->prepare("INSERT INTO USER_DATA (Username, Password) VALUE (?, ?)");
            $stmt->bind_param("ss",  $decoded['username'], $decoded['password']);
            $result = $stmt->execute();
            //good to check if insert was successful
            if (!$result) {
                die('Could not insert data: ' . $conn->error); 
            }
            $data = array(
                 "status" => "true",
                 "message" => "account successfully created"
                );

        }else{
            $data = array(
                "status" => "false",
                "reason" => "falied to insert into db",
                "message" => "Username already exists"
                );
        }

    }
    echo json_encode($data);

    // free result from memory
    mysqli_free_results($result);

    // close connection
    mysqli_close($conn);

?>