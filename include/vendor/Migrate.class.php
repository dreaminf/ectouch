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
            self::$version = intval(fgets($f));
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
            $fp = @fopen(self::$migrate_path . $file, 'r');
            $str = null;
            $num = 1;
            self::query('BEGIN');
            if ($fp) {
                while (!feof($fp)) {
                    if ($str = fgets($fp)) {
                        self::query($str);
                    } else {
                        $num = 0;
                    }
                }
                fclose($fp);
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
        mysql_query($str, self::$conn) or die('Error:fail to query this sql!!!' . mysql_error());
    }

    /*
     * 连接数据库方法
     */
    public static function connect()
    {
        self::$conn = mysql_connect(C('DB_HOST'), C('DB_USER'), C('DB_PWD')) or die('Error:cannot connect to database!!!' . mysql_error());
        self::$link = mysql_select_db(C('DB_NAME'), self::$conn) or die('Error:fail to select!!!' . mysql_error());
    }
}