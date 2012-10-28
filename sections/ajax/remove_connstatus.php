<?
 

if(isset($_REQUEST['ip']) && isset($_REQUEST['userid']) ){
     
    if (!is_number($_REQUEST['userid'])) {
        echo json_encode(array(false, 'UserID is not a number'));
        die();
    }
    if (!check_perms('users_mod') && $_REQUEST['userid']!==$LoggedUser['ID'] ) {
        echo json_encode(array(false, 'You do not have permission to access this page!'));
        die();
    }
 
     
    //$DB->query("DELETE FROM users_connectable_status
    //             WHERE UserID='" . db_string($_REQUEST['userid']) . "' AND IP='" . db_string($_REQUEST['ip']) . "' ");
    
    $now = time();
    $DB->query("INSERT INTO users_connectable_status (UserID, IP, Status, Time) 
                    VALUES ( '" . db_string($_REQUEST['userid']) . "','" . db_string($_REQUEST['ip']) . "', 'unset','$now' )
                    ON DUPLICATE KEY UPDATE Status='unset'");
    $result = $DB->affected_rows();
    
    $Cache->delete_value('connectable_'.$_REQUEST['userid']);
    
    if ($result > 0) {
        echo json_encode(array(true, "removed $result record for UserID: $_REQUEST[userid] &nbsp; IP: $_REQUEST[ip] "));
    }  elseif ($result == 0) {
        echo json_encode(array(false, "no record to remove for UserID: $_REQUEST[userid] &nbsp; IP: $_REQUEST[ip] "));
    } else {
        echo json_encode(array(false, "error: failed to remove record for UserID: $_REQUEST[userid] &nbsp; IP: $_REQUEST[ip] "));
    } 
    
    
} else {
    // didnt get ip and port info
    echo json_encode(array(false, 'Parameters not specified'));
}
 
