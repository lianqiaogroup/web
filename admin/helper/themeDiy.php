<?php

namespace admin\helper;

class themeDiy extends common
{

    public function getModule($product_id, $mid)
    {
        $data = $confData =[];
        $map['product_id'] = $product_id;
        $theme = $this->db->get('theme_diy', '*', $map);
        if($theme){
            $confData = json_decode($theme['data'], true);
        }
        if($mid){
            $confData = array_column($confData,NULL,"mid");
            $data['modules'] = $confData[$mid];
        } else {
//            //没有指定模板，查询所有模块
//           $modules = $this->db->select('theme_modules','*');
//           foreach ($modules as  $val){
//               $val['options'] = $confData[$val['mid']];
//               $val['sort'] = $confData[$val['mid']]['sort']?:0;
//               unset($val['options']['sort'],$val['options']['mid']);
//               $row[] =  $val;
//           }
//            array_multisort(array_column($row,'sort'),SORT_DESC,$row);
//            $data['modules'] =$row ;
            array_multisort(array_column($confData,'sort'),SORT_ASC,$confData);
            foreach ($confData as $val){
                $module['module_id'] = $val['module_id'];
                $module['module_name'] = $val['module_name'];
                $module['sort'] = $val['sort']?:0;
                $module['options'] = $val;
                unset($module['options']['module_id'], $module['options']['module_name'], $module['options']['sort']);
                $row[] = $module;
            }
            $data['modules'] = $row;
        }
        return $data;
    }


    public function save($data)
    {
        $product_id = $data['product_id'];
        $module_id = $data['module_id'];
        if( $module_id == 0 ){
            $module_id = (string)time();
            $data['module_id'] = $module_id;
        }
        unset($data['product_id']);
        $theme = $this->db->get('theme_diy', '*', ['product_id' => $product_id]);
        if(!isset($data['sort']))
        {
            $data['sort'] =0;
        }
        if($data['module_name'] =='countdown'){
            if(!isset($data['time_step']) || !$data['time_step']){
                $data['time_step'] =8;
            }
        }

        $map['product_id'] = $product_id;
        if ($theme) {
            $info = json_decode($theme['data'], true);
            $info[$module_id] = $data;
            $json = json_encode($info);
            $save['data'] = $json;
            $ret = $this->db->update('theme_diy', $save, $map);
            if ($ret === false) {
                return ['ret' => 0, 'msg' => "更新失败"];
            }else{
                return ['ret' => 1, 'module_id' => $module_id ];
            }
        } else {
            $save['product_id'] = $product_id;
            $data['module_id'] = $module_id;
            $info[$module_id] = $data;
            $save['data'] = json_encode($info);
            $ret = $this->db->insert('theme_diy', $save);
            if (!$ret) {
                return ['ret' => 0, 'msg' => "插入失败"];
            }else{
                return ['ret' => 1, 'module_id' => $module_id ];
            }
        }
        return ['ret' => 1];
    }

    /**
     * @param $data
     * @return array
     *  保存排序
     */
    public function saveSort($data){
        $product_id = $data['product_id'];
        if(!$product_id){
            return ['ret'=>0,'msg'=>'product_id 不能为空'] ;
        }

        $sort = $data['sort'];
        if(!$sort)
        {
            return ['ret'=>0,'msg'=>'排序数据不能为空'] ;
        }
        $theme = $this->db->get('theme_diy', '*', ['product_id' => $product_id]);
        $row = json_decode($theme['data'],true);

        $sort = explode(',',$sort);

        for($i=0;$i<count($sort);$i++){
            $id = $sort[$i];
            $row[$id]['sort'] = $i;
        }

        $save = json_encode($row);
        $ret = $this->db->update('theme_diy',['data'=>$save], ['product_id' => $product_id]);
        if($ret ===false){
            return ['ret'=>0,'msg'=>'保存失败，请联系技术'] ;
        }

        return ['ret'=>1];
    }

    // 重置数据
    public function reset($product_id){
        $map['product_id'] = $product_id;
        $this->db->delete('theme_diy', $map);
        $ret['ret'] = 1;
        return $ret;
    }

}