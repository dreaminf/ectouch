<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class MY_Config extends CI_Config
{

    public function __construct()
    {
        parent::__construct();
    }

    public function save($filename, $data = array())
    {
        $out = "<?php defined('BASEPATH') OR exit('No direct script access allowed');" .
            "\n\n\$config = " . $this->array_eval($data) . ";\n";
        write_file(APPPATH . 'config/' . $filename . '.php', $out);
    }

    public function update($filename, $item, $newdata)
    {
        $file = read_file(APPPATH . 'config/' . $filename . '.php');
        $oldstr = "/\'" . $item . "\' => \'(.*?)\'/i";
        $newstr = "'" . $item . "' => '" . $newdata . "'";
        $str = preg_replace($oldstr, $newstr, $file);
        write_file(APPPATH . 'config/' . $filename . '.php', $str);
    }

    private function array_eval($array, $level = 0)
    {
        if (!is_array($array)) {
            return "'" . $array . "'";
        }
        if (is_array($array) && function_exists('var_export')) {
            return var_export($array, true);
        }

        $space = '';
        for ($i = 0; $i <= $level; $i++) {
            $space .= "\t";
        }
        $evaluate = "Array\n$space(\n";
        $comma = $space;
        if (is_array($array)) {
            foreach ($array as $key => $val) {
                $key = is_string($key) ? '\'' . addcslashes($key, '\'\\') . '\'' : $key;
                $val = !is_array($val) && (!preg_match("/^\-?[1-9]\d*$/", $val) || strlen($val) > 12) ? '\'' . addcslashes($val, '\'\\') . '\'' : $val;
                if (is_array($val)) {
                    $evaluate .= "$comma$key => " . $this->array_eval($val, $level + 1);
                } else {
                    $evaluate .= "$comma$key => $val";
                }
                $comma = ",\n$space";
            }
        }
        $evaluate .= "\n$space)";
        return $evaluate;
    }

}