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
namespace app\admin\controller;

use app\service\WithdrawService;

/**
 * 用户积分日志管理
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Withdraw extends Common
{
	/**
	 * 构造方法
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  0.0.1
	 * @datetime 2016-12-03T12:39:08+0800
	 */
	public function __construct()
	{
		// 调用父类前置方法
		parent::__construct();

		// 登录校验
		$this->Is_Login();

		// 权限校验
		$this->Is_Power();
	}

	/**
     * [Index 用户积分日志列表]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-06T21:31:53+0800
     */
	public function Index()
	{
		// 参数
        $params = input();
        $params['user'] = $this->admin;
        $params['user_type'] = 'admin';

        // 分页
        $number = MyC('admin_page_number', 10, true);

        // 条件
        $where = WithdrawService::AdminWithdrawListWhere($params);

        // 获取总数
        $total = WithdrawService::AdminWithdrawTotal($where);

        // 分页
        $page_params = array(
                'number'    =>  $number,
                'total'     =>  $total,
                'where'     =>  $params,
                'page'      =>  isset($params['page']) ? intval($params['page']) : 1,
                'url'       =>  MyUrl('admin/withdraw/index'),
            );
        $page = new \base\Page($page_params);
        $this->assign('page_html', $page->GetPageHtml());

        // 获取列表
        $data_params = array(
            'm'         => $page->GetPageStarNumber(),
            'n'         => $number,
            'where'     => $where,
        );
        $data = WithdrawService::AdminWithdrawList($data_params);
        $this->assign('data_list', $data['data']);

		// 性别
		$this->assign('common_gender_list', lang('common_gender_list'));

		// 操作类型
		$this->assign('common_withdraw_log_type_list', lang('common_withdraw_log_type_list'));

		// 参数
        $this->assign('params', $params);
        return $this->fetch();
    }
    
       /**
     * [审核提现]
     * @author   RING
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-15T11:03:30+0800
     */
    public function Handle()
    {
        // 是否ajax请求
        if(!IS_AJAX)
        {
            return $this->error('非法访问');
        }

        // 发货操作
        $params = input();
        $params['creator'] = $this->admin['id'];
        $params['creator_name'] = $this->admin['username'];
        return WithdrawService::Handle($params);
    }
}
?>