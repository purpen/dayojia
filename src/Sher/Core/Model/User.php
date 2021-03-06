<?php
/**
 * Model user
 * 
 */
class Sher_Core_Model_User extends Sher_Core_Model_Base {
	const STATE_BLOCKED  = -1;
    const STATE_DISABLED = 0;
    const STATE_PENDING = 1;
    const STATE_OK = 2;

    const ROLE_USER   = 1;
    const ROLE_ADMIN  = 2;
    const ROLE_SYSTEM = 3;
    
    const PERMIT_POST = 'p';
    
    // 保密性别
    const SEX_HIDE = '未知';
	const SEX_MALE = 1;
	const SEX_FEMALE = 2;

    // 婚姻状况
	const MARR_SINGLE = 11;
	const MARR_LOVE = 12;
	const MARR_TWO = 22;
	
    protected $roles = array(
        'user' => self::ROLE_USER,
        'admin' => self::ROLE_ADMIN,
        'system' => self::ROLE_SYSTEM,
    );
	
    public static $TWEET_EVENTS = array('post','comment','like','love');

    protected $collection = 'user';
    protected $mongo_id_style = DoggyX_Model_Mongo_Base::MONGO_ID_SEQ;
	
    protected $schema = array(
		'account'  => null,
		'password' => null,
		'nickname' => null,
		'phone' => null,
		
		'invitation' => null,
        'state' =>  self::STATE_PENDING,
		
        'role_id' => self::ROLE_USER,
		'permission' => array(),
		
        'last_login' => 0,
		'current_login' => 0,
	    'online_alive' => 0,
		
        // counter
		# 关注数
        'follow_count' => 0,
		'fans_count' => 0,
		# 图片数量
        'photo_count' => 0,
		# 喜欢数
		'love_count' => 0,
		
		// 初次登录导向
		'first_login' => 1,
		'avatar' => array(),
		'digged' => 0,
		
		// 个人偏好设置
		'setting' => array(
			'_' => null,
			'layout' => null,
		),
		
        'profile' => array(
            'realname' => null,
			// 民族
			'nation' => null,
			// 籍贯
			'born_place' => null,
            'card' => null,
            // 用户工作类型
            'job'  => null,
            'school' => null,
            'address' => null,
            'zip' => null,
            'im_qq' => null,
        ),
		// 身高
		'height' => null,
		// 体重
		'weight' => null,
		// 所在城市
		'city' => null,
		// 性别
		'sex' => self::SEX_HIDE,
		// 婚姻状况
		'marital' => self::MARR_SINGLE,
		// 出生年月日
		'age'  => array(),
		// 个人关键词
		'tags' => array(),
		// 个人介绍
        'summary' => null,
		
		// 计数器
		'counter' => array(
			'message_count' => 0,
			'notice_count' => 0,
			'alert_count' => 0,
			'fans_count' => 0,
			'comment_count' => 0,
			'people_count' => 0, 
		),
		// 用户行为记录
		'visit' => array(
			'new_user_viewed' => 0,
		),
    );
    protected $required_fields = array('account','password');
    protected $int_fields = array('role_id','state','role_id','marital','sex','height','weight');
    protected $joins = array();
    
    //~ some event handles
    /**
	 * 保存之前,处理标签中的逗号,空格等
	 */
	protected function before_save(&$data) {
	    if (isset($data['tags']) && !is_array($data['tags'])) {
	        $data['tags'] = array_filter(array_values(array_unique(preg_split('/[,，\s]+/u',$data['tags']))));
	    }
	    $data['updated_on'] = time();
	    parent::before_save($data);
	}
	
    protected function after_save() {
    }
	
    protected function validate() {
		if(!$this->is_saved() && !$this->_check_account){
			throw new Sher_Core_Model_Exception('手机号码已被占用，请更换或重试！');
		}
        return true;
    }
	
	/**
	 * 检测账户是否唯一
	 */
	protected function _check_account() {
		$account = $this->data['account'];
		if(empty($account)){
			return false;
		}
		$row = $this->first(array('account' => $account));
		if(!empty($row)){
			return false;
		}
		return true;
	}


    protected function extra_extend_model_row(&$row) {
        $id = $row['id'] = $row['_id'];
		# 用户头像
		if(!empty($row['avatar'])){
			$row['big_avatar_url'] = Sher_Core_Helper_Url::asset_view_url($row['avatar']['big_avatar']);
			$row['mid_avatar_url'] = Sher_Core_Helper_Url::asset_view_url($row['avatar']['mid_avatar']);
			$row['sml_avatar_url'] = Sher_Core_Helper_Url::asset_view_url($row['avatar']['sml_avatar']);
		}else{
			// 用户默认头像
			$row['big_avatar_url'] = Sher_Core_Helper_Url::avatar_default_url('big', $row['sex']);
			$row['mid_avatar_url'] = Sher_Core_Helper_Url::avatar_default_url('mid', $row['sex']);
			$row['sml_avatar_url'] = Sher_Core_Helper_Url::avatar_default_url('sml', $row['sex']);
		}
		
		if($row['sex'] == self::SEX_FEMALE){
			$row['sex_text'] = 'female';
			$row['sex_title'] = '她';
		}elseif($row['sex'] == self::SEX_MALE){
			$row['sex_text'] = 'male';
			$row['sex_title'] = '他';
		}else{
			$row['sex_text'] = '';
			$row['sex_title'] = '它';
		}
		
		if($row['marital'] == self::MARR_SINGLE){
			$row['marital_text'] = '单身';
			$row['marital_icon'] = 'icon-single.jpg';
		}elseif($row['marital'] == self::MARR_LOVE){
			$row['marital_text'] = '热恋';
			$row['marital_icon'] = 'icon-love.jpg';
		}else{
			$row['marital_text'] = '已婚';
			$row['marital_icon'] = 'icon-married.jpg';
		}
		
		// calculate age
		if(isset($row['age']['year']) && isset($row['age']['mouth']) && isset($row['age']['day'])){
			$age_number = Sher_Core_Helper_Util::calc_age($row['age']['year'],$row['age']['mouth'],$row['age']['day']);
			$row['age_text'] = Sher_Core_Helper_Util::belong_age_interval($age_number);
		}else{
			$row['age_text'] = '未设置';
		}
		
        $row['home_url'] = Sher_Core_Helper_Url::user_home_url($id);
        $row['view_follow_url'] = Sher_Core_Helper_Url::user_follow_list_url($id);
        $row['view_fans_url'] = Sher_Core_Helper_Url::user_fans_list_url($id);
        $row['is_ok'] = $row['state'] == self::STATE_OK;
        $row['can_post'] = false;
        if ($row['role_id'] == self::ROLE_SYSTEM){
        	$row['is_system'] = $row['is_admin'] = true;
        	$row['can_system'] = $row['can_admin'] = true;
        	$row['can_post'] = true;
        }
        if ($row['role_id'] == self::ROLE_ADMIN){
            $row['is_admin'] = true;
            $row['can_admin'] = true;
            $row['can_post'] = true;
        }
        if (!empty($row['permission']) && in_array(self::PERMIT_POST,$row['permission'])) {
            $row['can_post'] = true;
        }
    }
	
    /**
     * 判断是否是管理员
     */
    public function is_admin() {
        return empty($this->data)?false:($this->data['role_id'] == self::ROLE_SYSTEM || $this->data['role_id'] == self::ROLE_ADMIN);
    }
    /**
     * 判断是否是管理员
     */
    public function can_admin() {
        return empty($this->data)?false:($this->data['role_id'] == self::ROLE_SYSTEM || $this->data['role_id'] == self::ROLE_ADMIN);
    }
    /**
     * 判断是否是系统管理员
     */
    public function can_system() {
        return empty($this->data)?false:($this->data['role_id'] == self::ROLE_SYSTEM);
    }
    
    public function can_post() {
        return empty($this->data)?false:($this->data['role_id'] == self::ROLE_SYSTEM
            || $this->data['role_id'] == self::ROLE_ADMIN
            || (!empty($this->data['permission']) && in_array(self::PERMIT_POST,$this->data['permission'])));
    }
	/**
	 * 是否是第一次登录
	 */
	public function first_login() {
		if(empty($this->data)){
			return false;
		}
		if(isset($this->data['first_login']) && $this->data['first_login'] == 1){
			return true;
		}
		return false;
	}
	/**
	 * 设置完成向导
	 */
	public function completed_guide($id) {
		return $this->update_set((int)$id, array('first_login' => 0));
	}
	

    public function approve_post($user_id) {
        return $this->update($user_id,array(
            '$addToSet' => array(
                'permission' => self::PERMIT_POST,
            )));
    }
    public function revoke_post($user_id) {
        return self::$_db->pull($this->collection,array('_id' => (int)$user_id),'permission',self::PERMIT_POST);
    }
    /**
     * 记录用户最后一次动作的心跳时间,用于判断用户是否属于在线状态
     *
     * @param int $user_id
     * @param int $time
     * @return void
     */
    public function heartbeat($user_id=null,$time=null) {
        if (is_null($user_id)) {
            $user_id = $this->id;
        }
        $user_id = (int) $user_id;
        $update['online_alive'] = is_null($time)?time():(int)$time;
        return $this->update_set($user_id,$update);
    }
    /**
     * 修改用户的角色
     *
     * @param string $id
     * @param string $new_role user|system|admin
     * @return bool
     */
    public function change_user_role($id,$new_role) {
        if (empty($new_role) || !isset($this->roles[$new_role])) {
            throw new Doggy_Model_ValidateException("未知的role:$new_role");
        }
        $role_id = $this->roles[$new_role];
        return $this->update_set((int)$id, array('role_id' => $role_id));
    }
	
	/**
	 * 推荐用户,orderby越大，排序越靠前
	 */
	public function digged_user($id){
		return $this->inc(array('_id' => (int)$id),'digged');
	}
	
    public function touch_last_login($user_id=null,$time=null) {
        if (is_null($user_id)) {
            $user_id = $this->id;
        }
        if (is_null($time)) {
            $time = time();
        }
		// 同时更新上次登录时间
		$row = $this->find_by_id($user_id);
		if(!empty($row)){
			$last_login = $row['current_login'];
			if(!isset($row['visit'])){
				$row['visit'] = array();
			}
			$visit = $row['visit'];
			// 设置距离上次用户登录新增用户的标识
			$visit['new_user_viewed'] = 1;
			$this->update_set((int)$user_id, array('current_login'=>(int)$time, 'last_login'=> $last_login,'visit' => $visit));
		}
    }
	
	/**
	 * 更新用户行为记录标识
	 */
	public function update_visit_field($user_id, $field, $value=0) {
		if(!in_array($field,array('new_user_viewed'))){
			return;
		}
		$this->update_set((int)$user_id, array('visit.'.$field => $value));
	}

    /**
     * 禁用账户
     */
    public function block_account($id) {
        return $this->update_set((int)$id,array('state' => self::STATE_DISABLED));
    }

    /**
     * 解封/激活帐号
     */
    public function active_account($id){
    	return $this->update_set((int)$id,array('state' => self::STATE_OK));
    }

    public function inc_counter($counter_name,$user_id=null) {
        if (is_null($user_id)) {
            $user_id = $this->id;
        }
        if (empty($user_id)) {
            return;
        }
        return $this->inc(array('_id' => (int)$user_id),$counter_name);
    }
	
    public function dec_counter($counter_name,$user_id = null) {
        if (is_null($user_id)) {
            $user_id = $this->id;
        }
        if (empty($user_id)) {
            return;
        }
        return $this->dec(array('_id' => (int)$user_id),$counter_name);
    }
	
	/**
	 * 更新用户头像
	 */
	public function update_avatar($avatar=array(),$user_id=null){
		if (is_null($user_id)) {
            $user_id = $this->id;
        }
        if (empty($user_id)) {
            throw new Sher_Core_Model_Exception('user_id is NULL');
        }
		
        $this->update_set((int) $user_id,array('avatar'=>$avatar));
	}
	
    /**
     * 更新基本资料
     */
    public function update_profile($profile,$user_id=null) {
        if ($user_id==null) {
            $user_id = $this->id;
        }
        if (empty($user_id)) {
            throw new Sher_Core_Model_Exception('user_id is NULL');
        }
        $user_id = (int) $user_id;
        $this->update_set($user_id,array('profile' => $profile));
    }
    
    public function update_contact($contact,$user_id=null) {
        if ($user_id==null) {
            $user_id = $this->id;
        }
        if (empty($user_id)) {
            throw new Sher_Core_Model_Exception('user_id is NULL');
        }
        $user_id = (int) $user_id;
        $this->update_set($user_id,array('contact' => $contact));
    }
	
	/**
	 * 更新计数器
	 */
	public function update_counter($user_id,$field,$value=0){
		if(!in_array($field,array('message_count','notice_count','alert_count','fans_count','comment_count'))){
			return;
		}
		$this->update_set((int)$user_id, array('counter.'.$field => $value));
	}
	/**
	 * 更新计数器，累加
	 */
	public function update_counter_byinc($user_id,$field,$value=1){
		if(!in_array($field,array('message_count','notice_count','alert_count','fans_count','comment_count','people_count'))){
			return;
		}
		$counter_name = 'counter.'.$field;
		return $this->inc(array('_id' => (int)$user_id), $counter_name, $value,true);
	}
	
	
	
}
?>