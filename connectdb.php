<?php
function query_sql($sqlstr){
	$mysql_server_name="localhost"; //数据库服务器名称
	$mysql_username="root"; // 连接数据库用户名
    $mysql_password="zpsroot"; // 连接数据库密码
    $mysql_database="usersys"; // 数据库的名字
       
    // 连接到数据库
    $conn=mysql_connect($mysql_server_name, $mysql_username, $mysql_password);                           
    mysql_select_db($mysql_database, $conn);
    mysql_query("set names 'utf8'");
    $tmp = mysql_query($sqlstr);
    $tmpstr = strtolower($sqlstr);
    if(strstr($tmpstr, 'select')){
    	if(mysql_num_rows($tmp) < 1){
    		$tmp = 0;
    	}
    }
    else{
	    if(!mysql_affected_rows()){
	    	$tmp = 0;
	    }
    }          
    // 关闭连接
    mysql_close($conn);
    return $tmp;
} 
?>