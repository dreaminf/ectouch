<?php

/**
 * Created by PhpStorm.
 * User: carson
 * Date: 2016/6/4
 * Time: 12:01
 */
define('GULP_SRC_PATH', ROOT_PATH . 'themes/default/src/');
define('GULP_DIST_PATH', ROOT_PATH . 'themes/default/');

class GulpfileController
{
    const ORD_LF = 10;
    const ORD_SPACE = 32;
    const ACTION_KEEP_A = 1;
    const ACTION_DELETE_A = 2;
    const ACTION_DELETE_A_B = 3;
    protected $js_a = '';
    protected $js_b = '';
    protected $js_input = '';
    protected $js_inputIndex = 0;
    protected $js_inputLength = 0;
    protected $js_lookAhead = null;
    protected $js_output = '';
    private $paths = null;

    public function index()
    {
        echo '欢迎使用 ECTouch Kit.';
        echo '<br><a href="' . url('run') . '">压缩发布文件</a>';

    }

    public function run()
    {
        $this->init();
        $this->clean();
        $this->minifycss();
        $this->minifyjs();
        $this->minifyimg();
    }

    /**
     * 初始化目录
     */
    private function init()
    {
        $this->paths = array(
            'frondend' => array(
                'styles' => array(
                    GULP_SRC_PATH . 'css/style.css',
                    GULP_SRC_PATH . 'css/a.css',
                    GULP_SRC_PATH . 'css/b.css',
                ),
                'scripts' => array(
                    GULP_SRC_PATH . 'js/jquery.min.js',
                    GULP_SRC_PATH . 'js/a.js',
                    GULP_SRC_PATH . 'js/b.js',
                    GULP_SRC_PATH . 'js/c.js',
                )
            ),
        );
    }

    /**
     * 压缩css
     */
    private function minifycss()
    {
        $src = $this->paths['frondend']['styles'];
        $dist = GULP_DIST_PATH . 'css/';
        $out = 'test';

        // 合并
        file_put_contents($dist . $out . '.css', '');
        foreach ($src as $vo) {
            if (file_exists($vo)) {
                $content_patch = file_get_contents($vo);
                file_put_contents($dist . $out . '.css', $content_patch, FILE_APPEND);
            }
        }

        // 压缩
        if (file_exists($dist . $out . '.css')) {
            $content = file_get_contents($dist . $out . '.css');
            $content = trim($content);
            $content = str_replace("\r\n", "\n", $content);
            $search = array("/\/\*[\d\D]*?\*\/|\t+/", "/\s+/", "/\}\s+/");
            $replace = array(null, " ", "}\n");
            $content = preg_replace($search, $replace, $content);
            $search = array("/\\;\s/", "/\s+\{\\s+/", "/\\:\s+\\#/", "/,\s+/i", "/\\:\s+\\\'/i", "/\\:\s+([0-9]+|[A-F]+)/i");
            $replace = array(";", "{", ":#", ",", ":\'", ":$1");
            $content = preg_replace($search, $replace, $content);
            $content = str_replace("\n", null, $content);
            $version = sprintf('%08x', crc32($content));
            file_put_contents($dist . $out . '-' . $version . '.min.css', $content);
        }
    }

    /**
     * 压缩js
     */
    private function minifyjs()
    {
        $src = $this->paths['frondend']['scripts'];
        $dist = GULP_DIST_PATH . 'js/';
        $out = 'test';

        // 合并
        file_put_contents($dist . $out . '.js', '');
        foreach ($src as $vo) {
            if (file_exists($vo)) {
                $content_patch = file_get_contents($vo);
                file_put_contents($dist . $out . '.js', $content_patch, FILE_APPEND);
            }
        }

        // 压缩
        if (file_exists($dist . $out . '.js')) {
            $content = file_get_contents($dist . $out . '.js');
            $this->js_input = str_replace("\r\n", "\n", $content);
            $this->js_inputLength = strlen($this->js_input);
            $content = $this->js_min();
            $version = sprintf('%08x', crc32($content));
            file_put_contents($dist . $out . '-' . $version . '.min.js', $content);
        }
    }

    /**
     * 压缩图片
     */
    private function minifyimg()
    {

    }

    /**
     * 清除文件
     */
    private function clean()
    {
        $styles = $this->paths['frondend']['styles'];
        $scripts = $this->paths['frondend']['scripts'];

        $dist_paths = array();
        foreach ($styles as $vo) {
            $path = str_replace(array('/src'), '', dirname($vo));
            if (!in_array($path, $dist_paths)) {
                $dist_paths[] = $path;
            }
        }

        foreach ($scripts as $vo) {
            $path = str_replace(array('/src'), '', dirname($vo));
            if (!in_array($path, $dist_paths)) {
                $dist_paths[] = $path;
            }
        }

        foreach ($dist_paths as $vo) {
            if (!is_dir($vo)) {
                mkdir($vo, 777);
            }
        }
    }

    /**
     * Action -- do something! What to do is determined by the $command argument.
     *
     * action treats a string as a single character. Wow!
     * action recognizes a regular expression if it is preceded by ( or , or =.
     *
     * @uses next()
     * @uses get()
     * @throws JsminException If parser errors are found:
     *         - Unterminated string literal
     *         - Unterminated regular expression set in regex literal
     *         - Unterminated regular expression literal
     * @param int $command One of class constants:
     *      ACTION_KEEP_A      Output A. Copy B to A. Get the next B.
     *      ACTION_DELETE_A    Copy B to A. Get the next B. (Delete A).
     *      ACTION_DELETE_A_B  Get the next B. (Delete B).
     */
    protected function js_action($command)
    {
        switch ($command) {
            case self::ACTION_KEEP_A:
                $this->js_output .= $this->js_a;
            case self::ACTION_DELETE_A:
                $this->js_a = $this->js_b;
                if ($this->js_a === "'" || $this->js_a === '"') {
                    for (; ;) {
                        $this->js_output .= $this->js_a;
                        $this->js_a = $this->js_get();
                        if ($this->js_a === $this->js_b) {
                            break;
                        }
                        if (ord($this->js_a) <= self::ORD_LF) {
                            throw new JsminException('Unterminated string literal.');
                        }
                        if ($this->js_a === '\\') {
                            $this->js_output .= $this->js_a;
                            $this->js_a = $this->js_get();
                        }
                    }
                }
            case self::ACTION_DELETE_A_B:
                $this->js_b = $this->next();
                if ($this->js_b === '/' && (
                        $this->js_a === '(' || $this->js_a === ',' || $this->js_a === '=' ||
                        $this->js_a === ':' || $this->js_a === '[' || $this->js_a === '!' ||
                        $this->js_a === '&' || $this->js_a === '|' || $this->js_a === '?' ||
                        $this->js_a === '{' || $this->js_a === '}' || $this->js_a === ';' ||
                        $this->js_a === "\n")
                ) {
                    $this->js_output .= $this->js_a . $this->js_b;
                    for (; ;) {
                        $this->js_a = $this->js_get();
                        if ($this->js_a === '[') {
                            /*
                             inside a regex [...] set, which MAY contain a '/' itself. Example: mootools Form.Validator near line 460:
                            return Form.Validator.getValidator('IsEmpty').test(element) || (/^(?:[a-z0-9!#$%&'*+/=?^_`{|}~-]\.?){0,63}[a-z0-9!#$%&'*+/=?^_`{|}~-]@(?:(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)*[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\])$/i).test(element.get('value'));
                            */
                            for (; ;) {
                                $this->js_output .= $this->js_a;
                                $this->js_a = $this->js_get();
                                if ($this->js_a === ']') {
                                    break;
                                } elseif ($this->js_a === '\\') {
                                    $this->js_output .= $this->js_a;
                                    $this->js_a = $this->js_get();
                                } elseif (ord($this->js_a) <= self::ORD_LF) {
                                    throw new JsminException('Unterminated regular expression set in regex literal.');
                                }
                            }
                        } elseif ($this->js_a === '/') {
                            break;
                        } elseif ($this->js_a === '\\') {
                            $this->js_output .= $this->js_a;
                            $this->js_a = $this->js_get();
                        } elseif (ord($this->js_a) <= self::ORD_LF) {
                            throw new JsminException('Unterminated regular expression literal.');
                        }
                        $this->js_output .= $this->js_a;
                    }
                    $this->js_b = $this->next();
                }
        }
    }

    /**
     * Get next char. Convert ctrl char to space.
     *
     * @return string|null
     */
    protected function js_get()
    {
        $c = $this->js_lookAhead;
        $this->js_lookAhead = null;
        if ($c === null) {
            if ($this->js_inputIndex < $this->js_inputLength) {
                $c = substr($this->js_input, $this->js_inputIndex, 1);
                $this->js_inputIndex += 1;
            } else {
                $c = null;
            }
        }
        if ($c === "\r") {
            return "\n";
        }
        if ($c === null || $c === "\n" || ord($c) >= self::ORD_SPACE) {
            return $c;
        }
        return ' ';
    }

    /**
     * Is $c a letter, digit, underscore, dollar sign, or non-ASCII character.
     *
     * @return bool
     */
    protected function js_isAlphaNum($c)
    {
        return ord($c) > 126 || $c === '\\' || preg_match('/^[\w\$]$/', $c) === 1;
    }

    /**
     * Perform minification, return result
     *
     * @uses action()
     * @uses isAlphaNum()
     * @uses get()
     * @uses peek()
     * @return string
     */
    protected function js_min()
    {
        if (0 == strncmp($this->peek(), "\xef", 1)) {
            $this->js_get();
            $this->js_get();
            $this->js_get();
        }
        $this->js_a = "\n";
        $this->js_action(self::ACTION_DELETE_A_B);
        while ($this->js_a !== null) {
            switch ($this->js_a) {
                case ' ':
                    if ($this->js_isAlphaNum($this->js_b)) {
                        $this->js_action(self::ACTION_KEEP_A);
                    } else {
                        $this->js_action(self::ACTION_DELETE_A);
                    }
                    break;
                case "\n":
                    switch ($this->js_b) {
                        case '{':
                        case '[':
                        case '(':
                        case '+':
                        case '-':
                        case '!':
                        case '~':
                            $this->js_action(self::ACTION_KEEP_A);
                            break;
                        case ' ':
                            $this->js_action(self::ACTION_DELETE_A_B);
                            break;
                        default:
                            if ($this->js_isAlphaNum($this->js_b)) {
                                $this->js_action(self::ACTION_KEEP_A);
                            } else {
                                $this->js_action(self::ACTION_DELETE_A);
                            }
                    }
                    break;
                default:
                    switch ($this->js_b) {
                        case ' ':
                            if ($this->js_isAlphaNum($this->js_a)) {
                                $this->js_action(self::ACTION_KEEP_A);
                                break;
                            }
                            $this->js_action(self::ACTION_DELETE_A_B);
                            break;
                        case "\n":
                            switch ($this->js_a) {
                                case '}':
                                case ']':
                                case ')':
                                case '+':
                                case '-':
                                case '"':
                                case "'":
                                    $this->js_action(self::ACTION_KEEP_A);
                                    break;
                                default:
                                    if ($this->js_isAlphaNum($this->js_a)) {
                                        $this->js_action(self::ACTION_KEEP_A);
                                    } else {
                                        $this->js_action(self::ACTION_DELETE_A_B);
                                    }
                            }
                            break;
                        default:
                            $this->js_action(self::ACTION_KEEP_A);
                            break;
                    }
            }
        }
        return $this->js_output;
    }

    /**
     * Get the next character, skipping over comments. peek() is used to see
     *  if a '/' is followed by a '/' or '*'.
     *
     * @uses get()
     * @uses peek()
     * @throws JsminException On unterminated comment.
     * @return string
     */
    protected function next()
    {
        $c = $this->js_get();
        if ($c === '/') {
            switch ($this->peek()) {
                case '/':
                    for (; ;) {
                        $c = $this->js_get();
                        if (ord($c) <= self::ORD_LF) {
                            return $c;
                        }
                    }
                case '*':
                    $this->js_get();
                    for (; ;) {
                        switch ($this->js_get()) {
                            case '*':
                                if ($this->peek() === '/') {
                                    $this->js_get();
                                    return ' ';
                                }
                                break;
                            case null:
                                throw new JsminException('Unterminated comment.');
                        }
                    }
                default:
                    return $c;
            }
        }
        return $c;
    }

    /**
     * Get next char. If is ctrl character, translate to a space or newline.
     *
     * @uses get()
     * @return string|null
     */
    protected function peek()
    {
        $this->js_lookAhead = $this->js_get();
        return $this->js_lookAhead;
    }
}