<?php
// +----------------------------------------------------------------------
// | ShopXO 国内领先企业级B2C免费开源电商系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011~2018 http://shopxo.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Devil
// +----------------------------------------------------------------------
namespace app\index\controller;

use app\service\BalanceService;
use app\service\UserService;
use app\service\WithdrawService;
/**
 * 用户积分管理
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class UserBalance extends Common
{
    /**
     * [_initialize 前置操作-继承公共前置方法]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-03T12:39:08+0800
     */
    public function _initialize()
    {
        // 调用父类前置方法
        parent::_initialize();

        // 是否登录
        $this->Is_Login();
    }

    /**
     * 用户积分列表
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-28
     * @desc    description
     */
    public function Index()
    {
        // 参数
        $params = input();
        $params['user'] = $this->user;

        // 分页
        $number = 10;

        // 条件
        $where = BalanceService::UserBalanceLogListWhere($params);

        // 获取总数
        $total = BalanceService::UserBalanceLogTotal($where);

        // 分页
        $page_params = array(
                'number'    =>  $number,
                'total'     =>  $total,
                'where'     =>  $params,
                'page'      =>  isset($params['page']) ? intval($params['page']) : 1,
                'url'       =>  MyUrl('index/userbalance/index'),
            );
        $page = new \base\Page($page_params);
        $this->assign('page_html', $page->GetPageHtml());

        // 获取列表
        $data_params = array(
            'm'         => $page->GetPageStarNumber(),
            'n'         => $number,
            'where'     => $where,
        );
        $data = BalanceService::UserBalanceLogList($data_params);
        $this->assign('data_list', $data['data']);

        // 操作类型
        $this->assign('common_balance_log_type_list', lang('common_balance_log_type_list'));

        // 参数
        $this->assign('params', $params);
        return $this->fetch();
    }

    	/**
	 * [withdraw 提交页面页面]
	 * @author   RING
	 * @blog     http://gong.gg/
	 * @version  0.0.1
	 * @datetime 2019-02-17T14:26:01+0800
	 */
	public function withdraw()
	{
		// 数据
		$this->assign('data', $this->user);
        $user = $this->user;
        if(empty($user['realname']) || empty($user['identity']))
        {
            //$this->assign('msg', '请先填写身份信息');
            //return $this->fetch('public/tips_error');
            return $this->error('请先填写身份信息', MyUrl('index/personal/saveinfo'));
        }

        // 参数
        $params = input();
        $params['user'] = $this->user;

        // 分页
        $number = 10;

        // 条件
        $where = WithdrawService::UserWithdrawLogListWhere($params);
        // 获取总数
        $total = WithdrawService::UserWithdrawLogTotal($where);

        // 分页
        $page_params = array(
                'number'    =>  $number,
                'total'     =>  $total,
                'where'     =>  $params,
                'page'      =>  isset($params['page']) ? intval($params['page']) : 1,
                'url'       =>  MyUrl('index/userbalance/drawlist'),
            );
        $page = new \base\Page($page_params);
        $this->assign('page_html', $page->GetPageHtml());

        // 获取列表
        $data_params = array(
            'm'         => $page->GetPageStarNumber(),
            'n'         => $number,
            'where'     => $where,
        );
        $data = WithdrawService::UserWithdrawLogList($data_params);
        $this->assign('data_list', $data['data']);

        // 操作类型
        $this->assign('common_withdraw_log_type_list', lang('common_withdraw_log_type_list'));

        // 参数
        $this->assign('params', $params);
        return $this->fetch();
		
    }
    
    public function WithdrawSave()
    {
        // 是否ajax请求
		if(!IS_AJAX)
		{
			$this->error('非法访问');
		}

		// 开始操作
		$params = input('post.');
        $params['user'] = $this->user;
        return UserService::WithdrawSave($params);
    }

    /**
     * 用户提现列表
     * @author   Devil
     * @blog    http://gong.gg/
     * @version 1.0.0
     * @date    2018-09-28
     * @desc    description
     */
    public function drawlist()
    {
        // 参数
        $params = input();
        $params['user'] = $this->user;

        // 分页
        $number = 10;

        // 条件
        $where = WithdrawService::UserWithdrawLogListWhere($params);
        // 获取总数
        $total = WithdrawService::UserWithdrawLogTotal($where);

        // 分页
        $page_params = array(
                'number'    =>  $number,
                'total'     =>  $total,
                'where'     =>  $params,
                'page'      =>  isset($params['page']) ? intval($params['page']) : 1,
                'url'       =>  MyUrl('index/userbalance/drawlist'),
            );
        $page = new \base\Page($page_params);
        $this->assign('page_html', $page->GetPageHtml());

        // 获取列表
        $data_params = array(
            'm'         => $page->GetPageStarNumber(),
            'n'         => $number,
            'where'     => $where,
        );
        $data = WithdrawService::UserWithdrawLogList($data_params);
        $this->assign('data_list', $data['data']);

        // 操作类型
        $this->assign('common_withdraw_log_type_list', lang('common_withdraw_log_type_list'));

        // 参数
        $this->assign('params', $params);
        return $this->fetch();
    }
    


}
?>