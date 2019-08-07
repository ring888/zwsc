<?php
// +----------------------------------------------------------------------
// | ShopXO 国内领先企业级B2C免费开源电商系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011~2018 http://shopxo.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Ring
// +----------------------------------------------------------------------
namespace app\service;

use think\Db;
use app\service\MessageService;

/**
 * 提现服务层
 * @author   Ring
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2019-2-16T16:51:08+0800
 */
class WithdrawService
{

    /**
     * 前端提现列表条件
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-29
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function UserWithdrawLogListWhere($params = [])
    {
        $where = [];

        // 用户id
        if(!empty($params['user']))
        {
            $where[] = ['user_id', '=', $params['user']['id']];
        }

        if(!empty($params['keywords']))
        {
            $where[] = ['order_id', 'like', '%'.$params['keywords'] . '%'];
        }

        // 是否更多条件
        if(isset($params['is_more']) && $params['is_more'] == 1)
        {
            if(isset($params['status']) && $params['status'] > -1)
            {
                $where[] = ['status', '=', intval($params['status'])];
            }

            // 时间
            if(!empty($params['time_start']))
            {
                $where[] = ['add_time', '>', strtotime($params['time_start'])];
            }
            if(!empty($params['time_end']))
            {
                $where[] = ['add_time', '<', strtotime($params['time_end'])];
            }
        }

        return $where;
    }

    /**
     * 用户提现日志总数
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-29
     * @desc    description
     * @param   [array]          $where [条件]
     */
    public static function UserWithdrawLogTotal($where = [])
    {
        return (int) Db::name('Withdraw')->where($where)->count();
    }

    /**
     * 提现日志列表
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-29
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function UserWithdrawLogList($params = [])
    {
        $where = empty($params['where']) ? [] : $params['where'];
        $m = isset($params['m']) ? intval($params['m']) : 0;
        $n = isset($params['n']) ? intval($params['n']) : 10;
        $order_by = empty($params['order_by']) ? 'id desc' : $params['order_by'];

        // 获取数据列表
        $data = Db::name('Withdraw')->where($where)->limit($m, $n)->order($order_by)->select();
        if(!empty($data))
        {
            $common_withdraw_log_type_list = lang('common_withdraw_log_type_list');
            foreach($data as &$v)
            {
                // 操作类型
                $v['type_name'] = $common_withdraw_log_type_list[$v['status']]['name'];

                // 时间
                $v['add_time_time'] = date('Y-m-d H:i:s', $v['add_time']);
                $v['add_time_date'] = date('Y-m-d', $v['add_time']);
            }
        }
        return DataReturn('处理成功', 0, $data);
    }

    /**
     * 后台管理员列表
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-29
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function AdminWithdrawList($params = [])
    {
        $where = empty($params['where']) ? [] : $params['where'];
        $m = isset($params['m']) ? intval($params['m']) : 0;
        $n = isset($params['n']) ? intval($params['n']) : 10;
        $field = 'ui.*,u.username,u.nickname,u.mobile,u.gender';
        $order_by = empty($params['order_by']) ? 'ui.id desc' : $params['order_by'];

        // 获取数据列表
        $data = Db::name('Withdraw')->alias('ui')->join(['__USER__'=>'u'], 'u.id=ui.user_id')->where($where)->field($field)->limit($m, $n)->order($order_by)->select();
        if(!empty($data))
        {
            $common_withdraw_log_type_list = lang('common_withdraw_log_type_list');
            $common_gender_list = lang('common_gender_list');
            foreach($data as &$v)
            {
                // 操作类型
                $v['type_text'] = $common_withdraw_log_type_list[$v['status']]['name'];

                // 性别
                $v['gender_text'] = $common_gender_list[$v['gender']]['name'];

                // 时间
                $v['add_time_time'] = date('Y-m-d H:i:s', $v['add_time']);
                $v['add_time_date'] = date('Y-m-d', $v['add_time']);
            }
        }
        return DataReturn('处理成功', 0, $data);
    }

    /**
     * 后台提现总数
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-29
     * @desc    description
     * @param   [array]          $where [条件]
     */
    public static function AdminWithdrawTotal($where = [])
    {
        return (int) Db::name('Withdraw')->alias('ui')->join(['__USER__'=>'u'], 'u.id=ui.user_id')->where($where)->count();
    }

    /**
     * 后台提现列表条件
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-29
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function AdminWithdrawListWhere($params = [])
    {
        $where = [];
        
        // 关键字
        if(!empty($params['keywords']))
        {
            $where[] = ['ui.order_id|u.username|u.nickname|u.mobile|u.realname', 'like', '%'.$params['keywords'].'%'];
        }

        // 是否更多条件
        if(isset($params['is_more']) && $params['is_more'] == 1)
        {
            // 等值
            if(isset($params['status']) && $params['status'] > -1)
            {
                $where[] = ['ui.status', '=', intval($params['status'])];
            }
            if(isset($params['gender']) && $params['gender'] > -1)
            {
                $where[] = ['u.gender', '=', intval($params['gender'])];
            }

            if(!empty($params['time_start']))
            {
                $where[] = ['ui.add_time', '>', strtotime($params['time_start'])];
            }
            if(!empty($params['time_end']))
            {
                $where[] = ['ui.add_time', '<', strtotime($params['time_end'])];
            }
        }

        return $where;
    }


        /**
     * 审核提现
     * @author   RING
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-30
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function Handle($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'id',
                'error_msg'         => '订单id有误',
            ],
            
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        $result = Db::name("Withdraw")->where(['id'=>$params['id']])->update(['status'=>1]);
        if($result !== false)
        {
            return DataReturn('处理成功', 0);
        }
        else
        {
            return DataReturn('处理失败', -10);
        }
    }


}
?>