<?php
/**
 * 接收图片上传Action
 */
class Sher_App_Action_Uploader extends Sher_App_Action_Authorize implements Doggy_Dispatcher_Action_Interface_UploadSupport  {
    public $stash = array();
    
    private $asset = array();
    
    //interface implements
    public function setUploadFiles($files){
        $this->asset = $files;
    }
    
    /**
     * 接收上传文件
     */
    public function execute() {
		return $this->to_raw('ok');
	}
	
    /**
     * 上传更换用户头像
     *
     * @return void
     */
    public function avatar() {
        if (!$this->visitor->id) {
            $result['code'] = 403;
            $result['message'] = 'session expired, please login.';
            return $this->to_raw_json($result);
        }
		
        $file = $this->asset[0]['path'];
        $file_name = $this->asset[0]['name'];
        $size = $this->asset[0]['size'];
        
        $avatar = Doggy_Config::$vars['app.asset.user_avatar'];
        $big_size = $avatar['big'];
        $small_size = $avatar['small'];
		$little_size = $avatar['little'];
		
        $image_info = Sher_Core_Util_Image::image_info($file);

        if (!in_array(strtolower($image_info['format']),array('jpg','png','jpeg'))) {
            $result['code'] = 400;
            $result['message'] = $image_info['format'].'图片格式无法识别，请上传jpg,png,jpeg格式的图片';
            return $this->to_raw_json($result);
        }
		
        $asset = new Sher_Core_Model_Asset();
		// 获取是否存在旧记录
        $old_avatar = $asset->first(
			array(
				'parent_id' => $this->visitor->id,
				'asset_type' => Sher_Core_Model_Asset::TYPE_AVATAR
			)
		);
		
        //create new one
		$asset->setFile($file);
		
        $image_type = Doggy_Util_File::mime_content_type($file_name);
		$image_info['size'] = $size;
        $image_info['mime'] = Doggy_Util_File::mime_content_type($file_name);
        $image_info['filename'] = basename($file_name);
		$image_info['filepath'] = Sher_Core_Util_Image::genPath($file_name,'avatar');
        $image_info['asset_type'] = Sher_Core_Model_Asset::TYPE_AVATAR;
        $image_info['parent_id'] = $this->visitor->id;
		
		$ok = $asset->apply_and_save($image_info);
		
        if ($ok) {
            $avatar_id = $asset->id;
            if (!empty($old_avatar)) {
                $asset->delete_file($old_avatar['_id']);
            }
            
			$result['id'] = $avatar_id;
            $result['code'] = 200;
			$result['success'] = true;
			$result['file_url'] = Sher_Core_Helper_Url::asset_view_url($asset->filepath);
			$result['width'] = $image_info['width'];
			$result['height'] = $image_info['height'];
        } else {
            $result['code'] = 500;
			$result['success'] = false;
            $result['message'] = 'Unknown error';
        }
		
        return $this->to_raw_json($result);
    }
	/**
	 * 裁切头像
	 */
	public function crop_avatar(){
		$avatar_id = $this->stash['avatar_id'];
		
		$x1 = $this->stash['x1'];
		$y1 = $this->stash['y1'];
		$w = $this->stash['w'];
		$h = $this->stash['h'];
		
		$result = array();
		
		$asset = new Sher_Core_Model_Asset();
		$row = $asset->find_by_id($avatar_id);
		if (empty($row)){
			return $this->ajax_note('获取数据错误,请重新提交',true);
		}
		
		$result = Sher_Core_Util_Image::make_crop_avatar($row['filepath'],$w,$h,$x1,$y1);
		if(empty($result)){
			return $this->ajax_note('生成数据错误,请重新提交',true);
		}
		
		// 更新用户头像
		$this->visitor->update_avatar(array(
			'big_avatar' => $result['big_avatar'],
			'mid_avatar' => $result['mid_avatar'],
			'sml_avatar' => $result['sml_avatar']
		));
		
		$this->stash['avatar'] = $result;
		
		return $this->to_taconite_page('ajax/crop_avatar.html');
	}
	
	/**
	 * 上传照片
	 */
	public function photo() {
		if (!$this->visitor->id) {
            $result['code'] = 403;
            $result['message'] = 'session expired, please login.';
            return $this->to_raw_json($result);
        }
		
		$stuffs = array();
		for($i=0; $i<count($this->asset); $i++){
			Doggy_Log_Helper::debug("Upload asset[$i] start.");
			
			$file = $this->asset[$i]['path'];
	        $filename = $this->asset[$i]['name'];
	        $size = $this->asset[$i]['size'];
	
			Doggy_Log_Helper::debug("Create stuff start.");
			# 批量生成
			$stuff = new Sher_Core_Model_Stuff();
			$random = Sher_Core_Helper_Util::gen_random();
			Doggy_Log_Helper::debug("Save stuff ... ok.");
			$stuff->create(array(
			    'user_id'=>$this->visitor->id,
				'random' => $random,
			));
			Doggy_Log_Helper::debug("Save stuff ok.");
			$stuff_id = (string)$stuff->_id;
			
			# 保存附件
			$asset = new Sher_Core_Model_Asset();
			//create new one
			$asset->setFile($file);
			
			$image_info = Sher_Core_Util_Image::image_info($file);
			$image_info['size'] = $size;
	        $image_info['mime'] = Doggy_Util_File::mime_content_type($filename);
	        $image_info['filename'] = basename($filename);
			$image_info['filepath'] = Sher_Core_Util_Image::genPath($filename);
	        $image_info['asset_type'] = Sher_Core_Model_Asset::TYPE_PHOTO;
	        $image_info['parent_id'] = $stuff_id;
			
			$ok = $asset->apply_and_save($image_info);
			Doggy_Log_Helper::debug("Create asset[$i] ok.");
			
	        if ($ok) {
				// 生成缩图
				$req_size = Doggy_Config::$vars['app.asset.photo'];
				$result = Sher_Core_Util_Image::make_photo($image_info['filepath'],$req_size['thumb']);
				$asset->update_set($asset->_id, array('thumb_big'=>$result['photo']));
				$result['stuff_id'] = $stuff_id;
				
				# 更新附件asset_id
				$query['_id'] = new MongoId($stuff_id);
				$updated['asset_id'] = (string)$asset->_id;
				$updated['published'] = 1;
				$stuff->update_set($query,$updated);
				
				// 记录用户图片数量
				$this->visitor->inc_counter('photo_count');
				
				$stuffs[] = $result;
			}
			unset($stuff);
		}
		
		$result['stuffs'] = $stuffs;
		$result['code'] = 200;
		$result['success'] = true;
		
		return $this->to_raw_json($result);
	}
	
	/**
     * 检查指定附件的状态并返回附件列表到上传队列中
     *
     * @return void
     */
    public function check_upload_assets() {
        if (empty($this->stash['stuffs'])) {
            $result['error_message'] = '没有上传的图片';
            $result['code'] = 401;
            return $this->ajax_response('ajax/check_upload_assets.html',$result);
        }
		$stuffs = array();
        $stuffs_ids = $this->stash['stuffs'];
        $model = new Sher_Core_Model_Stuff();
		for($i=0;$i<count($stuffs_ids);$i++){
			$stuffs[] = $model->extend_load($stuffs_ids[$i]);
		}
        $this->stash['stuffs'] = $stuffs;
		
        return $this->to_taconite_page('ajax/check_upload_assets.html');
    }

}
?>