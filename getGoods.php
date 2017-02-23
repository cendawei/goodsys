<?php
include 'connectdb.php';
//处理json中文乱码
function arrayRecursive($array, $function, $apply_to_keys_also = false)
{
    static $recursive_counter = 0;
    if (++$recursive_counter > 1000) {
        die('possible deep recursion attack');
    }
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            arrayRecursive($array[$key], $function, $apply_to_keys_also);
        } else {
            $array[$key] = $function($value);
        }

        if ($apply_to_keys_also && is_string($key)) {
            $new_key = $function($key);
            if ($new_key != $key) {
                $array[$new_key] = $array[$key];
                unset($array[$key]);
            }
        }
    }
    $recursive_counter--;
}

//JSON编码输出
function JSON($array)
{
    arrayRecursive($array, 'urlencode', true);
    $json = json_encode($array);
    return urldecode($json);
}

//获取商品信息API，id为空的话，返回所有商品信息
function getGoodInfo($id){
	$json = array("code"=>0, "msg"=>"success", "data"=>"");
	$sql_str = $id ? "SELECT * FROM goods where id='".$id."'" : "SELECT * FROM goods";	
	$tmp = query_sql($sql_str);
	if(empty($tmp)){
		$json["data"] = "没有商品信息";
		return JSON($json);
	}	
	$i = 0;
	while($row = mysql_fetch_array($tmp))
	{
		$result[$i] = array("id"=>$row[0], "name"=>$row[1], "price"=>$row[2], "desc"=>$row[3], "state"=>$row[4]);
		$i++;
	}
	$json["data"] = $result;	
	return JSON($json);
}

//获取所有商品
function getGoods(){
	$json = array("code"=>0, "msg"=>"success", "data"=>"");
	$sql_str = "SELECT ID, NAME FROM goods";
	$tmp = query_sql($sql_str);
	if(empty($tmp)){
		$json["data"] = "没有商品信息";
		return JSON($json);
	}	
	$i = 0;
	while ($row = mysql_fetch_array($tmp))
	{
		$result[$i] = array("id"=>$row[0], "name"=>$row[1]);
		$i++;
	}
	$json["data"] = $result;
	return JSON($json);
}

//录入商品
function insertGood($goodinfo){
	$json = array("code"=>0, "msg"=>"success", "data"=>"false");
	$goodinfo = json_decode($goodinfo);
	//判断主键和非空字段是否有传
	$not_null_keys = array("id", "name", "price", "state", "category");
	foreach ($not_null_keys as $nnk){
		if(!array_key_exists($nnk, $goodinfo) || empty($goodinfo->$nnk)){
			$json["data"] = "不能设置非空字段为空";
			return JSON($json);
		}
	}
	//判断id是否存在
	$sql_str = "SELECT ID, NAME FROM goods where id='".$goodinfo->id."'";
	$tmp = query_sql($sql_str);
	if(!empty($tmp)){
		$json["data"] = "id已存在";
		return JSON($json);
	} 
	//生成数据返回
	$i = 0;
	foreach ($goodinfo as $k=>$v){
		$karray[$i] = $k;
		$varray[$i] = "'".$v."'";
		$i++;
	}
	$kstr = implode(',', $karray);
	$vstr = implode(',', $varray);
	$sql_str = "INSERT goods (".$kstr.") VALUES (".$vstr.")";
	$tmp = query_sql($sql_str);
	if(!$tmp){
		return JSON($json);
	}
	$json["data"] = "true";
	return JSON($json);
}

//更新商品
function updateGood($id, $goodinfo){
	$json = array("code"=>0, "msg"=>"success", "data"=>"false");
	$goodinfo = json_decode($goodinfo);
	//id为空则返回错误
	if(empty($id)){
		$json["data"] = "id不能为空";
		return JSON($json); 
	}
	//判断非空字段是否传空值
	$not_null_keys = array("id", "name", "price", "state", "category");
	foreach ($not_null_keys as $nnk){
		if(array_key_exists($nnk, $goodinfo) && empty($goodinfo->$nnk)){
			$json["data"] = "不能设置非空字段为空";
			return JSON($json);
		}
	}
	//判断id是否存在
	$sql_str = "SELECT ID, NAME FROM goods where id='".$id."'";
	$tmp = query_sql($sql_str);
	if(empty($tmp)){
		$json["data"] = "id不存在";
		return JSON($json);
	} 
	//生成数据返回
	$i = 0;
	foreach ($goodinfo as $k=>$v){
		$updatearray[$i] = $k."='".$v."'";
		$i++;
	}
	$updatestr = implode(',', $updatearray);
	$sql_str = "UPDATE goods SET ".$updatestr." where id='".$id."'";
	$tmp = query_sql($sql_str);
	if(empty($tmp)){
		$json["data"] = "更新不成功";
		return JSON($json);
	}
	$json["data"] = "更新成功";
	return JSON($json);	
}

//删除商品
function deleteGood($id){
	$json = array("code"=>0, "msg"=>"success", "data"=>"false");
	//id为空则返回错误
	if(empty($id)){
		return JSON($json); 
	}
	$sql_str = "delete from goods where id='".$id."'";
	$tmp = query_sql($sql_str);
	if(empty($tmp)){
		$json["data"] = "删除不成功";
		return JSON($json);
	}
	$json["data"] = "删除成功";
	return JSON($json);
}

//API入口
$action = $_GET["action"];
switch ($action) {
	//获取商品信息GET方法
	case 'get_good_info':
		$id = urldecode($_GET["id"]);
		echo getGoodInfo($id);
	;
	break;
	//获取所有商品GET方法
	case 'get_goods':
		echo getGoods();
	;
	break;
	//录入商品信息post方法
	case 'insert_good':
		$goodinfo = $_POST['goodinfo'];
		echo insertGood($goodinfo);
	;
	break;
	//更新商品信息post方法
	case 'update_good':
		$id = $_POST['id'];
		$goodinfo = $_POST['goodinfo'];
		echo updateGood($id, $goodinfo);
	;
	break;
	//删除商品
	case 'delete_good':
		$id = $_POST['id'];
		echo deleteGood($id);
	default:
		;
	break;
}
?> 