<?php


namespace app\common\controller;

class Csv
{

    /**
     * 导入Csv文件
     * @param $csv_file
     * @return array
     */
    public function import($csv_file)
    {
        $result_arr = [];
        $i = 0;
        $arr = [];
        while ($data_line = fgetcsv($csv_file, 10000)) {
            if ($i == 0) {
                $arr['csv_key_name_arr'] = $data_line;
                $i++;
                continue;
            }
            foreach ($arr['csv_key_name_arr'] as $csv_key_num => $csv_key_name) {
                unset($arr['csv_key_name_arr'][18]);
                if (isset($data_line[$csv_key_num])){
                    $result_arr[$i][$csv_key_name] = $data_line[$csv_key_num];
                }
            }
            $i++;
        }
        return $result_arr;
    }
}