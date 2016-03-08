<?php

/* 访问控制 */
defined('IN_ECTOUCH') or die('Deny Access');
define('MIGRATE_VERSION_FILE', '.version');
define('MIGRATE_FILE_PREFIX', 'migrate-');
define('MIGRATE_FILE_POSTFIX', '.php');

/**
 * exp:
 * Migrate::init();
 */
class Migrate
{

    public static $version = 0;
    public static $migrate_path = '';
    public static $migrate_files = array();
    private static $conn = '';
    private static $link = '';


    static public function setPath()
    {
        self::$migrate_path = ROOT_PATH . 'data/migrates/';
    }

    public static function init()
    {
        self::setPath();
        // Find the latest version or star0t at 0.
        $f = @fopen(self::$migrate_path . MIGRATE_VERSION_FILE, 'r');
        if ($f) {
            self::$version = floatval(fgets($f));
            fclose($f);
        }
        self::update_db();
    }
    /*
     * 获取到migrate文件夹中的所有文件
     */
    // Find all the migration files in the directory and return the sorted.
    public static function get_migrations()
    {
        $dir = opendir(self::$migrate_path);
        while ($file = readdir($dir)) {
            if (substr($file, 0, strlen(MIGRATE_FILE_PREFIX)) == MIGRATE_FILE_PREFIX) {
                self::$migrate_files[] = $file;
            }
        }
        asort(self::$migrate_files);
    }

    /*
     * 根据文件名
     */
    public static function get_version_from_file($file)
    {
        return floatval(substr($file, strlen(MIGRATE_FILE_PREFIX)));
    }

    public static function update_db()
    {
        self::get_migrations();

        // Check to make sure there are no conflicts such as 2 files under the same version.
        $errors = array();
        $last_file = false;
        $last_version = false;
        foreach (self::$migrate_files as $file) {
            $file_version = self::get_version_from_file($file);
            if ($last_version !== false && $last_version === $file_version) {
                $errors[] = "$last_file --- $file";
            }
            $last_version = $file_version;
            $last_file = $file;
        }
        if (count($errors) > 0) {
            echo "Error: You have multiple files using the same version. " .
                "To resolve, move some of the files up so each one gets a unique version.\n";
            foreach ($errors as $error) {
                echo "  $error\n";
            }
            exit;
        }

        // Run all the new files.
        foreach (self::$migrate_files as $file) {
            $file_version = self::get_version_from_file($file);
            if ($file_version <= self::$version) {
                continue;
            }

            self::connect();
//            $fp = @fopen(self::$migrate_path . $file, 'r');
            $sqls = file_get_contents(self::$migrate_path . $file);
            $sqls = self::selectsql($sqls);
            $str = null;
            $num = 1;
            self::query('set names utf8');
            self::query('BEGIN');
            foreach ($sqls as $val) {
                if (empty($val)) continue;
                if (is_string($val)) {
                    if(!self::query($val)){
                        $num = 0;
                    }
                }
            }
            if ($num == 0) {
                self::query('ROLLBACK');
            } elseif ($num == 1) {
                self::query('COMMIT');
            }

            $version = $file_version;

            // Output the new version number.
            $f = @fopen(self::$migrate_path . MIGRATE_VERSION_FILE, 'w');
            if ($f) {
                fputs($f, $version);
                fclose($f);
            } else {
                echo "Failed to output new version to " . MIGRATION_VERSION_FILE . "\n";
            }
        }
    }

    public static function query($str)
    {
        if(mysql_query($str, self::$conn)){
            return true;
        }else{
            return false;
        }
        return false;
    }

    /*
     * 连接数据库方法
     */
    public static function connect()
    {
        self::$conn = mysql_connect(C('DB_HOST'), C('DB_USER'), C('DB_PWD')) or die('Error:cannot connect to database!!!' . mysql_error());
        self::$link = mysql_select_db(C('DB_NAME'), self::$conn) or die('Error:fail to select!!!' . mysql_error());
    }
    /**
     * 判断是否是注释
     * @param $sql   获取到的sql文件内容
     */
    public static function selectsql($sqls){
        $statement = null;
        $newStatement=null;
        $commenter = array('#','--');
        $sqls = explode(';',trim($sqls));//按sql语句分开
        foreach($sqls as $sql){
            if (preg_match('/^(\/\*)(.)+/i',$sql)) {
                $sql = preg_replace('/(\/\*){1}([.|\s|\S])*(\*\/){1}/','',$sql);
            }
            $sentence = explode('/n',$sql);
            foreach ($sentence as $subSentence) {
                $subSentence = str_replace('ecs_',C('DB_PREFIX'),$subSentence);
                if('' != trim($subSentence)){
                    //判断是否注释
                    $isComment = false;

                    foreach($commenter as $comer){
                        if(eregi("^(".$comer.")",trim($subSentence)))
                        {
                            $isComment = true;
                            break;
                        }

                    }
                    //不是注释就是sql语句
                    if(!$isComment)
                        $newStatement[] = $subSentence;
                }
            }
            $statement = $newStatement;
        }
        return $statement;
    }
}