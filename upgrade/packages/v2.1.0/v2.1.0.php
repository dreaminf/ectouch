<?php

class up_v2_1_0
{
    /**
     * 本升级包中SQL文件存放的位置（相对于升级包所在的路径）。每个版本类必须有该属性。
     */
    private $sql_files = array(
                            'structure' => 'structure.sql',
                            'data' => 'data_utf-8.sql'
        );
    
    /**
     * 本升级包是否进行智能化的查询操作。每个版本类必须有该属性。
     */
    private $auto_match = true;

    /**
     * 提供给控制器的 接口 函数。每个版本类必须有该函数。
     */
    public function update_database_optionally()
    {
        
    }

    /**
     * 提供给控制器的 接口 函数。每个版本类必须有该函数。
     */
    public update_files()
    {
    
    }
}
