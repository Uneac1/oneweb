<?php
/**
 * name: OneNav安装初始化文件
 * author: xiaoz<xiaoz93@outlook.com>
 */

 /**
  * 安装前先检查环境
  */
function check_env() {
    //获取组件信息
    $ext = get_loaded_extensions();
    //检查PHP版本，需要大于7.0小于8.0
    $php_version = floatval(PHP_VERSION);
    $uri = $_SERVER["REQUEST_URI"];

    // var_dump($uri);
    
    if( ( $php_version < 7 ) || ( $php_version > 8 ) ) {
        exit("当前PHP版本{$php_version}不满足要求，需要7.0 <= PHP <= 7.4");
    }
    
    //检查是否支持pdo_sqlite
    if ( !array_search('pdo_sqlite',$ext) ) {
        exit("不支持PDO_SQLITE组件，请先开启!");
    }

    if ( !array_search('openssl', $ext) ) {
        exit("不支持OPENSSL组件，请先开启!");
    }
    
    //检查是否支持zlib
    if ( !array_search('zlib', $ext) ) {
        exit("不支持ZLIB组件，请先开启!");
    }
    
    //检查是否支持curl
    if ( !array_search('curl', $ext) ) {
        exit("不支持CURL组件，请先开启!");
    }

    //如果配置文件存在
    if( file_exists("data/config.php") ) {
        exit("配置文件已存在，无需再次初始化!");
    }
    // 路径必须以/或者/index.php开头
    if( !preg_match('/^\/|\/index.php/',$uri) ) {
        exit("暂不支持二级目录安装!");
    }
    //检查是否是二级目录
    // $pattern = '/\/[a-z0-9\/]+$/';
    // if( preg_match_all($pattern,$uri) ) {
    //     exit("暂不支持二级目录安装!");
    // }
    return TRUE;
}

/**
 * 安装OneNav
 */
function install() {
    if( !file_exists('./data/config.php') ) {
        //复制配置文件
        //加载初始化模板
        require("templates/admin/init.php");
        exit();
    }
    else {

    }
}

function err_msg($code,$err_msg){
    $data = [
        'code'      =>  $code,
        'err_msg'   =>  $err_msg
    ];
    //返回json类型
    header('Content-Type:application/json; charset=utf-8');
    exit(json_encode($data));
}
/**
 * 初始化设置OneNav
 */
function init($data){
    //判断参数是否为空
    if( empty($data['username']) || empty($data['password']) ) {
        err_msg(-2000,'用户名或密码不能为空！');
    }
    // 正则验证用户名
    $u_patt = '/^[0-9a-z]{3,32}$/';
    if( !preg_match($u_patt,$data['username']) ) {
        err_msg(-2000,'用户名格式不正确！');
    }
    // 正则验证密码
    $p_patt = '/^[0-9a-zA-Z!@#%^*.()]{6,16}$/';
    if( !preg_match($p_patt,$data['password']) ) {
        err_msg(-2000,'密码格式不正确！');
    }
    // 验证邮箱是否合法
    if( !filter_var($data['email'],FILTER_VALIDATE_EMAIL) ) {
        err_msg(-2000,'邮箱格式不正确！');
    }
    $config_file = "data/config.php";
    //检查配置文件是否存在，存在则不允许设置
    if( file_exists($config_file) ) {
        err_msg(-2000,'配置文件已存在，无需再次初始化！');
    }
    //复制配置文件
    
    //读取配置文件内容
    $content = file_get_contents("config.simple.php");
    //替换内容
    $content = str_replace('{email}',$data['email'],$content);
    $content = str_replace('{username}',$data['username'],$content);
    // $content = str_replace('{password}',$data['password'],$content);
    // 存入加密后的密码，用户名 + 密码，再进行MD5加密
    $content = str_replace('{encrypted_password}',md5($data['username'].$data['password']),$content);

    //写入配置文件
    if( !file_put_contents($config_file,$content) ) {
        err_msg(-2000,'写入配置文件失败，请检查目录权限！');
    }
    else{
        //成功并返回json格式
        $data = [
            'code'      =>  200,
            'msg'        =>  "初始化完成！"
        ];
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($data));
    }
}

$c = @$_GET['c'];

check_env();

if ( $c == 'init' ) {
    //接收POST参数
    $email = htmlspecialchars(trim($_POST['email']));
    $username = htmlspecialchars(trim($_POST['username']));
    $password = htmlspecialchars(trim($_POST['password']));
    $data = [
        "email"     =>  $email,
        "username"  =>  $username,
        "password"  =>  $password
    ];
    init($data);
}
else{
    install();
}
