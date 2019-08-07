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
 * 余额服务层
 * @author   Ring
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2019-2-16T16:51:08+0800
 */
class BalanceService
{
    /**
     * [UserBalanceLogAdd 用户余额日志添加]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  1.0.0
     * @datetime 2018-05-18T16:51:12+0800
     * @param    [int]                   $user_id           [用户id]
     * @param    [int]                   $original_Balance [原始余额]
     * @param    [int]                   $new_Balance      [最新余额]
     * @param    [string]                $msg               [操作原因]
     * @param    [int]                   $type              [操作类型（0减少, 1增加）]
     * @param    [int]                   $operation_id      [操作人员id]
     * @return   [boolean]                                  [成功true, 失败false]
     */
    public static function UserBalanceLogAdd($user_id, $original_balance, $new_balance, $msg = '', $type = 0, $operation_id = 0)
    {
        $data = array(
            'user_id'           => intval($user_id),
            'original_balance' => $original_balance,
            'new_balance'      => $new_balance,
            'msg'               => $msg,
            'type'              => intval($type),
            'operation_id'      => intval($operation_id),
            'add_time'          => time(),
        );
        if(Db::name('UserBalanceLog')->insertGetId($data) > 0)
        {
            $type_msg = lang('common_balance_log_type_list')[$type]['name'];
            $balance = ($data['type'] == 0) ? $data['original_balance']-$data['new_balance'] : $data['new_balance']-$data['original_balance'];
            $detail = $msg.'余额'.$type_msg.$balance;
            MessageService::MessageAdd($user_id, '余额变动', $detail,3);
            return true;
        }
        return false;
    }

    /**
     * 前端余额列表条件
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-29
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function UserBalanceLogListWhere($params = [])
    {
        $where = [];

        // 用户id
        if(!empty($params['user']))
        {
            $where[] = ['user_id', '=', $params['user']['id']];
        }

        if(!empty($params['keywords']))
        {
            $where[] = ['msg', 'like', '%'.$params['keywords'] . '%'];
        }

        // 是否更多条件
        if(isset($params['is_more']) && $params['is_more'] == 1)
        {
            if(isset($params['type']) && $params['type'] > -1)
            {
                $where[] = ['type', '=', intval($params['type'])];
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
     * 用户余额日志总数
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-29
     * @desc    description
     * @param   [array]          $where [条件]
     */
    public static function UserBalanceLogTotal($where = [])
    {
        return (int) Db::name('UserBalanceLog')->where($where)->count();
    }

    /**
     * 余额日志列表
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-29
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function UserBalanceLogList($params = [])
    {
        $where = empty($params['where']) ? [] : $params['where'];
        $m = isset($params['m']) ? intval($params['m']) : 0;
        $n = isset($params['n']) ? intval($params['n']) : 10;
        $order_by = empty($params['order_by']) ? 'id desc' : $params['order_by'];

        // 获取数据列表
        $data = Db::name('UserBalanceLog')->where($where)->limit($m, $n)->order($order_by)->select();
        if(!empty($data))
        {
            $common_balance_log_type_list = lang('common_balance_log_type_list');
            foreach($data as &$v)
            {
                // 操作类型
                $v['type_name'] = $common_balance_log_type_list[$v['type']]['name'];

                // 时间
                $v['add_time_time'] = date('Y-m-d H:i:s', $v['add_time']);
                $v['add_time_date'] = date('Y-m-d', $v['add_time']);
            }
        }
        return DataReturn('处理成功', 0, $data);
    }

    /**
     * 订单商品余额赠送
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-11-14
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function OrderGoodsBalanceGiving($params = [])
    {
        // 请求参数
        $p = [
            [
                'checked_type'      => 'empty',
                'key_name'          => 'order_id',
                'error_msg'         => '订单id有误',
            ]
        ];
        $ret = ParamsChecked($params, $p);
        if($ret !== true)
        {
            return DataReturn($ret, -1);
        }

        // 订单
        $order = Db::name('Order')->field('id,user_id,status')->find(intval($params['order_id']));
        if(empty($order))
        {
            return DataReturn('订单不存在或已删除，中止操作', 0);
        }
        if(!in_array($order['status'], [4]))
        {
            return DataReturn('当前订单状态不允许操作['.$params['order_id'].'-'.$order['status'].']', 0);
        }

        // 获取用户信息
        $user = Db::name('User')->field('id')->find(intval($order['user_id']));
        if(empty($user))
        {
            return DataReturn('用户不存在或已删除，中止操作', 0);
        }

        // 获取订单商品
        $goods_all = Db::name('OrderDetail')->where(['order_id'=>$params['order_id']])->column('goods_id');
        if(!empty($goods_all))
        {
            foreach($goods_all as $goods_id)
            {
                $give_Balance = Db::name('Goods')->where(['id'=>$goods_id])->value('give_Balance');
                if(!empty($give_Balance))
                {
                    // 用户余额添加
                    $user_Balance = Db::name('User')->where(['id'=>$user['id']])->value('Balance');
                    if(!Db::name('User')->where(['id'=>$user['id']])->setInc('Balance', $give_Balance))
                    {
                        return DataReturn('用户余额赠送失败['.$params['order_id'].'-'.$goods_id.']', -10);
                    }

                    // 余额日志
                    BalanceService::UserBalanceLogAdd($user['id'], $user_Balance, $user_Balance+$give_Balance, '订单商品完成赠送', 1);
                }
            }
            return DataReturn('操作成功', 0);
        }
        return DataReturn('没有需要操作的数据', 0);
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
    public static function AdminBalanceList($params = [])
    {
        $where = empty($params['where']) ? [] : $params['where'];
        $m = isset($params['m']) ? intval($params['m']) : 0;
        $n = isset($params['n']) ? intval($params['n']) : 10;
        $field = 'ui.*,u.username,u.nickname,u.mobile,u.gender';
        $order_by = empty($params['order_by']) ? 'ui.id desc' : $params['order_by'];

        // 获取数据列表
        $data = Db::name('UserBalanceLog')->alias('ui')->join(['__USER__'=>'u'], 'u.id=ui.user_id')->where($where)->field($field)->limit($m, $n)->order($order_by)->select();
        if(!empty($data))
        {
            $common_balance_log_type_list = lang('common_balance_log_type_list');
            $common_gender_list = lang('common_gender_list');
            foreach($data as &$v)
            {
                // 操作类型
                $v['type_text'] = $common_balance_log_type_list[$v['type']]['name'];

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
     * 后台余额总数
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-29
     * @desc    description
     * @param   [array]          $where [条件]
     */
    public static function AdminBalanceTotal($where = [])
    {
        return (int) Db::name('UserBalanceLog')->alias('ui')->join(['__USER__'=>'u'], 'u.id=ui.user_id')->where($where)->count();
    }

    /**
     * 后台余额列表条件
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-29
     * @desc    description
     * @param   [array]          $params [输入参数]
     */
    public static function AdminBalanceListWhere($params = [])
    {
        $where = [];
        
        // 关键字
        if(!empty($params['keywords']))
        {
            $where[] = ['ui.msg|u.username|u.nickname|u.mobile', 'like', '%'.$params['keywords'].'%'];
        }

        // 是否更多条件
        if(isset($params['is_more']) && $params['is_more'] == 1)
        {
            // 等值
            if(isset($params['type']) && $params['type'] > -1)
            {
                $where[] = ['ui.type', '=', intval($params['type'])];
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


}
?>