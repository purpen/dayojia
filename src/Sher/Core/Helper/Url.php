<?php
/**
 * 构造访问地址
 */
class Sher_Core_Helper_Url {

	/**
	 * 附件的URL
	 */
	public static function asset_view_url($path,$domain='sher'){
		$asset_url = Sher_Core_Util_Asset::getAssetUrl($domain,$path);
		return $asset_url;
	}
	
	/**
	 * 用户默认头像
	 */
	public static function avatar_default_url($type='big',$sex=2){
		$avatar_default = '';
		$prefix = ($sex == 2) ? 'female' : 'male';
		switch ($type) {
		    case 'big':
		        $avatar_default = "/images/avatar_${prefix}_big.jpg";
		        break;
		    case 'mid':
		        $avatar_default = "/images/avatar_${prefix}_mid.jpg";
		        break;
		    case 'sml':
		        $avatar_default = "/images/avatar_${prefix}_sml.jpg";
		        break;
		}
		return $avatar_default;
	}
	
	
	/**
	 * 管理申请列表
	 */
    public static function admin_reply_list_url($state,$page=null) {
        if (!empty($page)) {
            $page = "?page=${page}";
        }
        return self::build_url_path('app.url.admin.reply','state',$state).$page;
    }

	/**
	 * 管理举报列表
	 */
    public static function admin_report_list_url($state,$page=null) {
        if (!empty($page)) {
            $page = "?page=${page}";
        }
        return self::build_url_path('app.url.admin.report','state',$state).$page;
    }
    
    /**
     * 他人关注的列表地址
     */
    public static function user_follow_list_url($user_id, $page=null) {
        if (!empty($page)) {
            $page = "p${page}.html";
        }
		return self::build_url_path('app.url.user',$user_id,'follow').$page;
    }
    
    /**
     * 他人粉丝的列表地址
     */
    public static function user_fans_list_url($user_id, $page=null) {
        if (!empty($page)) {
            $page = "p${page}.html";
        }
        return self::build_url_path('app.url.user',$user_id,'fans').$page;
    }

	/**
	 * 我分享的图片地址
	 */
    public static function my_share_list_url($page=null) {
        if (!empty($page)) {
            $page = "p${page}.html";
        }
        return self::build_url_path('app.url.my','share').$page;
    }

	/**
	 * 我收藏的图片地址
	 */
    public static function my_like_list_url($page=null) {
        if (!empty($page)) {
            $page = "p${page}.html";
        }
        return self::build_url_path('app.url.my','like').$page;
    }
    
    /**
     * 我专辑的图片地址
     */
    public static function my_album_list_url($page=null) {
        if (!empty($page)) {
            $page = "p${page}.html";
        }
        return self::build_url_path('app.url.my','album').$page;
    }
    
    /**
     * 我喜欢的图片地址
     */
    public static function my_love_list_url($page=null) {
        if (!empty($page)) {
            $page = "p${page}.html";
        }
        return self::build_url_path('app.url.my','love').$page;
    }
    
    /**
     * 我关注的用户列表地址
     */
    public static function my_follow_list_url($page=null){
        if (!empty($page)) {
            $page = "p${page}.html";
        }
        return self::build_url_path('app.url.my','follow').$page;
    }
    
    /**
     * 我粉丝的用户列表地址
     */
    public static function my_fans_list_url($page=null){
    	if (!empty($page)) {
            $page = "p${page}.html";
        }
        return self::build_url_path('app.url.my','fans').$page;
    }

    /**
     * 关注用户分享图片列表
     */
    public static function follow_stuff_list_url($page=null){
        if (!empty($page)) {
            $page = "p${page}.html";
        }
		return self::build_url_path('app.url.stuff','follow').$page;
    }
    
	/**
	 * 分类访问地址
	 */
    public static function stuff_list_url($category_id=null,$page=null) {
        if (!is_null($category_id)) {
            $category_id = 'c'.$category_id;
        }
        if (!empty($page)) {
            $page = "p${page}.html";
        }
        return self::build_url_path('app.url.stuff',$category_id).$page;
    }

    /**
     * 分享浏览地址
     */
    public static function stuff_view_url($stuff_id){
    	return  sprintf(Doggy_Config::$vars['app.url.stuff.view'],$stuff_id);
    }

    /**
     * 举报分享地址
     */
    public  static function stuff_report_url($stuff_id){
    	return  sprintf(Doggy_Config::$vars['app.url.stuff.report'],$stuff_id);
    }

    /**
     * 根据 $id 显示缩略图
     */
    public static function thumb_view_url($id){
        return sprintf(Doggy_Config::$vars['app.url.thumb'],$id);
    }

  	/**
     * 将参数作为url的path添加到指定的config key定义的url,生成一个友好的伪静态地址.
     *
     * @param string $url_config_key 指定的config key用于url前缀
     * @param string $force_trailing_slash
     * @return void
     */
    public static function build_url_path() {
        $args = func_get_args();
        if (empty($args)) {
            return '';
        }
        $key = array_shift($args);
        $url = Doggy_Config::$vars[$key];
        $url = rtrim($url,'/');
        while ($path = array_shift($args)) {
            $url .= '/'.$path;
        }
		
        return substr($url,-1,1) != '/' ? $url.'/':$url;
    }
	
    public static function user_home_url($id){
       return self::build_url_path('app.url.user',$id);
    }
	
    public static function asset_url($id) {
        return sprintf(Doggy_Config::$vars['app.url.asset_view'],$id);
    }
	
	public static function asset_ori_url($file_id) {
        return sprintf(Doggy_Config::$vars['app.url.asset_ori'],$file_id);
    }

	/**
	 * 设置向导
	 */
	public static function get_guide_url(){
		return self::build_url_path('app.url.guide');
	}
}
?>