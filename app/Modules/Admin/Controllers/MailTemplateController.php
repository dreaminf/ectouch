<?php

namespace App\Modules\Admin\Controllers;

/**
 * 模版管理
 * Class MailTemplateController
 * @package App\Modules\Admin\Controllers
 */
class MailTemplateController extends BaseController
{
    public function actionIndex()
    {
        admin_priv('mail_template');

        /**
         * 模版列表
         */
        if ($_REQUEST['act'] == 'list') {
            // 包含插件语言项
            $sql = "SELECT code FROM " . $this->ecs->table('plugins');
            $rs = $this->db->query($sql);
            foreach ($rs as $row) {
                // 取得语言项
                if (file_exists('../plugins/' . $row['code'] . '/languages/common_' . $GLOBALS['_CFG']['lang'] . '.php')) {
                    include_once(ROOT_PATH . 'plugins/' . $row['code'] . '/languages/common_' . $GLOBALS['_CFG']['lang'] . '.php');
                }
            }

            // 获得所有邮件模板
            $sql = "SELECT template_id, template_code FROM " . $this->ecs->table('mail_templates') . " WHERE  type = 'template'";
            $res = $this->db->query($sql);
            $cur = null;

            foreach ($res as $row) {
                if ($cur == null) {
                    $cur = $row['template_id'];
                }

                $len = strlen($GLOBALS['_LANG'][$row['template_code']]);
                $templates[$row['template_id']] = $len < 18 ?
                    $GLOBALS['_LANG'][$row['template_code']] . str_repeat('&nbsp;', (18 - $len) / 2) . " [$row[template_code]]" :
                    $GLOBALS['_LANG'][$row['template_code']] . " [$row[template_code]]";
            }

            $content = $this->load_template($cur);

            // 创建 html editor
            create_html_editor('content', $content['template_content']);

            $this->smarty->assign('tpl', $cur);
            $this->smarty->assign('cur', $cur);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['mail_template_manage']);
            $this->smarty->assign('templates', $templates);
            $this->smarty->assign('template', $content);
            $this->smarty->assign('full_page', 1);
            return $this->smarty->display('mail_template.htm');
        }

        /**
         * 载入指定模版
         */
        if ($_REQUEST['act'] == 'loat_template') {
            $tpl = intval($_GET['tpl']);
            $mail_type = isset($_GET['mail_type']) ? $_GET['mail_type'] : -1;

            // 包含插件语言项
            $sql = "SELECT code FROM " . $this->ecs->table('plugins');
            $rs = $this->db->query($sql);
            foreach ($rs as $row) {
                // 取得语言项
                if (file_exists('../plugins/' . $row['code'] . '/languages/common_' . $GLOBALS['_CFG']['lang'] . '.php')) {
                    include_once(ROOT_PATH . 'plugins/' . $row['code'] . '/languages/common_' . $GLOBALS['_CFG']['lang'] . '.php');
                }
            }

            // 获得所有邮件模板
            $sql = "SELECT template_id, template_code FROM " . $this->ecs->table('mail_templates') . " WHERE  type = 'template'";
            $res = $this->db->query($sql);

            foreach ($res as $row) {
                $len = strlen($GLOBALS['_LANG'][$row['template_code']]);
                $templates[$row['template_id']] = $len < 18 ?
                    $GLOBALS['_LANG'][$row['template_code']] . str_repeat('&nbsp;', (18 - $len) / 2) . " [$row[template_code]]" :
                    $GLOBALS['_LANG'][$row['template_code']] . " [$row[template_code]]";
            }

            $content = $this->load_template($tpl);

            if (($mail_type == -1 && $content['is_html'] == 1) || $mail_type == 1) {
                // 创建 html editor
                create_html_editor('content', $content['template_content']);
                $content['is_html'] = 1;
            } elseif ($mail_type == 0) {
                $content['is_html'] = 0;
            }

            $this->smarty->assign('tpl', $tpl);
            $this->smarty->assign('cur', $tpl);
            $this->smarty->assign('templates', $templates);
            $this->smarty->assign('template', $content);

            return make_json_result($this->smarty->fetch('mail_template.htm'));
        }

        /**
         * 保存模板内容
         */

        if ($_REQUEST['act'] == 'save_template') {
            if (empty($_POST['subject'])) {
                return sys_msg($GLOBALS['_LANG']['subject_empty'], 1, [], false);
            } else {
                $subject = trim($_POST['subject']);
            }

            if (empty($_POST['content'])) {
                return sys_msg($GLOBALS['_LANG']['content_empty'], 1, [], false);
            } else {
                $content = trim($_POST['content']);
            }

            $type = intval($_POST['is_html']);
            $tpl_id = intval($_POST['tpl']);


            $sql = "UPDATE " . $this->ecs->table('mail_templates') . " SET " .
                "template_subject = '" . str_replace('\\\'\\\'', '\\\'', $subject) . "', " .
                "template_content = '" . str_replace('\\\'\\\'', '\\\'', $content) . "', " .
                "is_html = '$type', " .
                "last_modify = '" . gmtime() . "' " .
                "WHERE template_id='$tpl_id'";

            if ($this->db->query($sql, "SILENT")) {
                $link[0] = ['href' => 'mail_template.php?act=list', 'text' => $GLOBALS['_LANG']['update_success']];
                return sys_msg($GLOBALS['_LANG']['update_success'], 0, $link);
            } else {
                return sys_msg($GLOBALS['_LANG']['update_failed'], 1, [], false);
            }
        }
    }

    /**
     * 加载指定的模板内容
     *
     * @access  public
     * @param   string $temp 邮件模板的ID
     * @return  array
     */
    private function load_template($temp_id)
    {
        $sql = "SELECT template_subject, template_content, is_html " .
            "FROM " . $GLOBALS['ecs']->table('mail_templates') . " WHERE template_id='$temp_id'";
        $row = $GLOBALS['db']->getRow($sql);

        return $row;
    }
}