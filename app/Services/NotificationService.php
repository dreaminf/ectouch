<?php

namespace App\Services;


class NotificationService
{

    /**
     * 获取邮件模板
     *
     * @access  public
     * @param:  $tpl_name[string]       模板代码
     *
     * @return array
     */
    function get_mail_template($tpl_name)
    {
        $sql = 'SELECT template_subject, is_html, template_content FROM ' . $GLOBALS['ecs']->table('mail_templates') . " WHERE template_code = '$tpl_name'";

        return $GLOBALS['db']->getRow($sql);
    }

}