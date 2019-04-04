<?php

Engine::load_app_class('content');
Engine::load_app_class('page', '', 0);
class article extends content{
	private $db;

	function __construct() {
		$this->db = Engine::load_model('article_model');
		$this->honor_db = Engine::load_model('honor_model');
		$this->feedback_db = Engine::load_model('feedback_model');
		$this->link_db = Engine::load_model('link_model');
		$this->goods_db = Engine::load_model('goods_model');
		$this->news_gallery_db = Engine::load_model('news_gallery_model');
		$this->article_gallery_db = Engine::load_model('article_gallery_model');
		$this->goods_data_db = Engine::load_model('goods_data_model');
		$this->simple_db = Engine::load_model('simple_model');
	    parent::__construct();
	}
	//首页
	public function init() {
		
	}
	
	//列表页
	public function lists($html=false) {
		global $shuwon, $mysql, $website, $webinfo, $CATEGORY;
	    extract($_GET);
	    if ((DEBUG_MODE & 2) != 2){$this->smarty->caching = true;}
		$this->smarty->direct_output = false;
		$act = !empty($_GET['act']) ? $_GET['act'] : '';
		/* 获得当前页码 */
		$curpage   = !empty($_REQUEST['startpage'])  && intval($_REQUEST['startpage'])  > 0 ? intval($_REQUEST['startpage'])  : 1;
		/* 缓存编号 */
		$cache_id = sprintf('%X',crc32($_SESSION['user_rank'] . '-' . $cat_id . '-' . $curpage . '-' .  $_CFG['web_lang']));
		
		$template_list = empty($CATEGORY[$cat_id]['template']) ? 'article_list.html' : $CATEGORY[$cat_id]['template'].'.html';
		/*------------------------------------------------------ */
		//-- 判断是否存在缓存，如果存在则调用缓存，反之读取相应内容
		/*------------------------------------------------------ */
		if (!$this->smarty->is_cached($template_list,$cache_id)||$html||$act=='create_category'||$act=='create_single'){
			if(empty($nav_id)){echo '导航id为传入';exit;}//判断是否传入导航的id
			$cur_root_catname = parent::get_cur_catname($nav_id,1);//获取当前 根栏目 名称
			$this->smarty->assign('cur_root_catname',$cur_root_catname['name']);
			$this->smarty->assign('cur_root_desc',$cur_root_catname['navdiscript']);
			$this->smarty->assign('cur_root_url',$cur_root_catname['navurl']);
			$cur_catname = parent::get_cur_catname($cat_id);//获取当前 栏目 名称
			$this->smarty->assign('cur_catname',$cur_catname['catname']);
			$this->smarty->assign('cur_catdesc',$cur_catname['discription']);
			$this->smarty->assign('cur_key',$cur_catname['keywords']);
			$this->smarty->assign('cur_catface',$cur_catname['newsclass_img']);
			$this->smarty->assign('cur_en_catname',$cur_catname['en_catname']);
			$this->smarty->assign('cur_discription',$cur_catname['discription']);
			$this->smarty->assign('index_name',$this->webinfo['web_name']);
			
			
		    $catid = $cat_id;
		    $this->smarty->assign('nav_id',$nav_id);
		    
		    //最顶级栏目ID
			$arrparentid = explode(',', $cur_catname['arrparentid']);
			$top_parentid = $arrparentid[1] ? $arrparentid[1] : $catid;
			$nav = parent::get_Allcat($top_parentid,'','','',$nav_id);
			$this->smarty->assign('nav',$nav);//获取二级导航
		    //获取TAG标签
		    $tag_arr = array();
			$page = $this->db->get_article_list($cat_id,1,'',999,'',0,0,0,0,'',2,'article_show');
			foreach ($page['data'] as $k=>$v){
				if($v['field2']){
					$tags = explode("|",$v['field2']);
					foreach ($tags as $tag){
						$tag_arr[] = $tag;
					}
				}
			}

			$this->smarty->assign('tag_arr',$tag_arr);
			
			/*------------------------------------------------------ */

            $page = $this->db->get_article_list($cat_id,1,'',$CATEGORY[$catid]['pagemax'],'',0,0,0,0,'',!empty($grade)?$grade:2,'article_show',$filter_attr);
			foreach($page['data'] as $k=>$v){
		    	$page['data'][$k]['img_list'] = $this->news_gallery_db->get_news_img($v['id']);
		    	//$page['data'][$k]['img_list1'] = $this->article_gallery_db->get_article_img($v['id']);
		    }
			
		    $this->smarty->assign('article_list',$page['data']);
		    
		    $this->smarty->assign('page_str',$this->get_Pagestr4($page['page']));
		    
		    
		    
		    $page = $this->db->get_article_list($cat_id,1,'',1,'',1,0,0,0,'',!empty($grade)?$grade:2,'article_show',$filter_attr);
		    $this->smarty->assign('recommend_list',$page['data']);
		    
		    //内页banner
			$honor_list = $this->honor_db->get_honor_list(26);
           	$this->smarty->assign('banner_ny',$honor_list);
		    if ($nav_id==2){
			    $page = $this->db->get_article_list(6,0,'',999,'','','',0,0,'',2,'article_show');//9为显示个数
				foreach($page['data'] as $k=>$v){
			    	$page['data'][$k]['women_list1'] = $this->news_gallery_db->get_news_img($v['id']);
			    }
				$this->smarty->assign('women1',$page['data']);
				$page = $this->db->get_article_list(7,0,'',999,'','','',0,0,'',2,'article_show');//9为显示个数
				foreach($page['data'] as $k=>$v){
			    	$page['data'][$k]['women_list21'] = $this->news_gallery_db->get_news_img($v['id']);
			    }
				$this->smarty->assign('women2',$page['data']);
			}
			if ($nav_id==3){
				$page = $this->db->get_article_list(8,0,'',999,'','','',0,0,'',2,'article_show');//9为显示个数
				foreach($page['data'] as $k=>$v){
			    	$page['data'][$k]['man_list1'] = $this->news_gallery_db->get_news_img($v['id']);
			    }
				$this->smarty->assign('man1',$page['data']);
				$page = $this->db->get_article_list(9,0,'',999,'','','',0,0,'',2,'article_show');//9为显示个数
				foreach($page['data'] as $k=>$v){
			    	$page['data'][$k]['man_list21'] = $this->news_gallery_db->get_news_img($v['id']);
			    }
				$this->smarty->assign('man2',$page['data']);
				
			}
		    
		    $page = $this->db->get_article_list($cat_id,1,'',3,'',0,0,0,1,'',!empty($grade)?$grade:2);
		    $this->smarty->assign('article_list1',$page['data']);
			//服务区
		    $this->smarty->assign('wanling',$this->link_db->get_link_list(30));
		    $this->smarty->assign('xianyudong',$this->link_db->get_link_list(31));
		    $this->smarty->assign('wanli',$this->link_db->get_link_list(32));
		    
		    
			$page = $this->db->get_article_list($cat_id,0,'',9,'','','',0,1,'',2,'article_show');//9为显示个数
			$this->smarty->assign('tuijian',$page['data']);	
			$honor_list = $this->honor_db->get_honor_list(34);
            $this->smarty->assign('honor_list',$honor_list);
            $content = $this->simple_db->get_simple_info($cat_id);//获取单页图文的内容
			$this->smarty->assign('simple',$content);
			$page=$this->goods_db->get_goods_list(36,1,'',99,'',0,0,0,0,2,'product_show');;
			$this->smarty->assign('kecheng',$page['data']);
			$content = $this->simple_db->get_simple_info(37);//获取单页图文的内容
			$this->smarty->assign('xly',$content);
			$honor_list = $this->honor_db->get_honor_list(37);
            $this->smarty->assign('honor_list1',$honor_list);
            
            $honor_list = $this->honor_db->get_honor_list($cat_id);
            $this->smarty->assign('honor_list2',$honor_list);
			
		    if($cat_id==7){
		    	 $page = $this->db->get_article_list($cat_id,0,'',999,'',0,0,0,1,'',2,'article_show');
		         $this->smarty->assign('huodong',$page['data']); 
		    }
			if($cat_id==13){
		    	 $page = $this->db->get_article_list($cat_id,0,'',999,'',0,0,0,1,'',2,'article_show');
		         $this->smarty->assign('tuijian',$page['data']); 
		    }
			if($cat_id==9){
		    	 $page = $this->db->get_article_list($cat_id,0,'',999,'',0,0,0,1,'',2,'article_show');
		         $this->smarty->assign('xinwen',$page['data']); 
		    }
		    /*------------------------------------------------------ */
		    if(!empty($grade) && $grade==3){
		    	$this->smarty->assign('sub_check_id',$cat_id);//三级栏目当前状态
				$cat_id = $cur_catname['parent_id'];
			}
			if(!empty($grade) && $grade==4){
		    	$this->smarty->assign('gs_check_id',$cat_id);//四级栏目当前状态
				$sub_cat_id = $cur_catname['parent_id'];
				$this->smarty->assign('sub_check_id',$sub_cat_id);//三级栏目当前状态
				$cur_catname = parent::get_cur_catname($sub_cat_id);
				$cat_id = $cur_catname['parent_id'];
			}
			$this->smarty->assign('check_id',$cat_id);//二级栏目当前状态
			
			$is_ext = empty($is_ext) ? 0 : $is_ext;
		    $this->smarty->assign('is_ext',$is_ext);
		    
		    $ext_id = empty($ext_id) ? 0 : $ext_id;
		    $this->smarty->assign('ext_id',$ext_id);
			
			//banner[后台广告位id]
			$banner=$this->website->get_Ads(6);
			$this->smarty->assign('banner',$banner[14]);
			//$this->smarty->assign('banner2',$banner[15]);
		
			//重新定义网站的title、描述、关键字
			$this->smarty->assign('web_name',$cur_catname['catname'] . '|' . $cur_root_catname['name'] . '|' . $this->webinfo['web_name']);
			$keywords = empty($cur_catname['keywords']) ? $this->webinfo['web_key'] : $cur_catname['keywords'];
			$description = empty($cur_catname['discription']) ? $this->webinfo['web_discription'] : $cur_catname['discription'];
			$this->smarty->assign('keywords',$keywords);
			$this->smarty->assign('description',$description);
		}
		if($html||$act=='create_category'||$act=='create_single'){
			$this->smarty->display($template_list);
		}else{
			$this->smarty->display($template_list, $cache_id);
		}
	}
	
	//内容页
	public function show($html=false) {
		global $shuwon, $mysql, $website, $webinfo, $CATEGORY;
	    extract($_GET);
	    if ((DEBUG_MODE & 2) != 2){$this->smarty->caching = true;}
		$this->smarty->direct_output = false;
		$act = !empty($_GET['act']) ? $_GET['act'] : '';
		/* 获得当前页码 */
		$curpage   = !empty($_REQUEST['startpage'])  && intval($_REQUEST['startpage'])  > 0 ? intval($_REQUEST['startpage'])  : 1;
		/* 缓存编号 */
		$cache_id = sprintf('%X',crc32($_SESSION['user_rank'] . '-' . $cat_id . '-' . $curpage . '-' .  $_CFG['web_lang']));
		
		$readtemplate = empty($CATEGORY[$cat_id]['readtemplate']) ? 'article_show.html' : $CATEGORY[$cat_id]['readtemplate'].'.html';
		
		/*------------------------------------------------------ */
		//-- 判断是否存在缓存，如果存在则调用缓存，反之读取相应内容
		/*------------------------------------------------------ */
		if (!$this->smarty->is_cached($readtemplate,$cache_id)||$html||$act=='create_show'||$act=='create_single'||$act=='create_pimg'){
			//获取内容
			$content = $this->db->get_article_info($id,'?act=article_show&id='.$id);
	        $this->smarty->assign('article',$content);//内容
			$cur_catname = parent::get_cur_catname($content['cat_id']);//获取当前 栏目 名称
			$this->smarty->assign('cur_catname',$cur_catname['catname']);
			$this->smarty->assign('cur_catdesc',$cur_catname['discription']);
			$this->smarty->assign('cur_catface',$cur_catname['newsclass_img']);
			$this->smarty->assign('cur_en_catname',$cur_catname['en_catname']);
			$this->smarty->assign('check_id',$content['cat_id']);//二级栏目当前状态

			if(!empty($CATEGORY[$content['cat_id']]['ishtml'])){
				$this->smarty->assign('cur_caturl',$cur_catname['url']);
			}else{
				$cur_caturl = 'article_list-'.$content['cat_id'].'-'.$CATEGORY[$content['cat_id']]['nav_id'].'-'.$CATEGORY[$content['cat_id']]['grade'].'.html';
				//$this->smarty->assign('cur_caturl',$cur_caturl);
				$this->smarty->assign('cur_caturl',$cur_catname['link']);
			}
			
			
			
			$this->smarty->assign('title',$title);
			$this->smarty->assign('page',$page);
			
			$cat_id=$content['cat_id'];
			
			//根据cat_id获取自定义导航的id
			if(empty($content['cat_id'])){echo '该条数据无分类信息！';exit;}
			$nav_id=$this->get_Nav_Id($content['cat_id']);
			if(empty($nav_id)){echo '该数据未绑定自定义导航';exit;}//判断是否传入导航的id
			$cur_root_catname = parent::get_cur_catname($nav_id,1);//获取当前 根栏目 名称
			$this->smarty->assign('cur_root_catname',$cur_root_catname['name']);
			$this->smarty->assign('cur_root_desc',$cur_root_catname['navdiscript']);
			$this->smarty->assign('cur_root_url',$cur_root_catname['navurl']);
		   
			$this->smarty->assign('nav_id',$nav_id);
		 
			$nav = parent::get_Allcat($cur_root_catname['cat_id'],'','','',$nav_id);
			$this->smarty->assign('nav',$nav);//获取二级导航
			
			//内页banner
			$honor_list = $this->honor_db->get_honor_list(26);
           	$this->smarty->assign('banner_ny',$honor_list);
			
           	$page = $this->db->get_article_list(1,1,'',3,'',0,0,0,1,'',!empty($grade)?$grade:2);
		    $this->smarty->assign('article_tuijian',$page['data']);
           	
           	
			$data_list1 = $this->goods_data_db->get_goods_data_list($id,1);
			$this->smarty->assign('data_list1',$data_list1);
			//获取图片组
			$img_list = $this->news_gallery_db->get_news_img($id);
			$this->smarty->assign('img_list',$img_list);
			$img_list = $this->article_gallery_db->get_article_img($id);
			$this->smarty->assign('img_list1',$img_list);
		    $op = !empty($_GET['op']) ? $_GET['op'] : 'in_progress';
			$this->smarty->assign('op',$op);
			$ext_id = $cur_catname['sort'];
			
			if(!empty($grade) && $grade==3){
			    $this->smarty->assign('sub_check_id',$content['cat_id']);
				$cat_id = $cur_catname['parent_id'];
				$ext_id = $CATEGORY[$cur_catname['parent_id']]['sort'];
				$is_ext = $cur_catname['sort'];
			}
			if(!empty($grade) && $grade==4){
		    	$this->smarty->assign('gs_check_id',$content['cat_id']);//四级栏目当前状态
				$sub_cat_id = $cur_catname['parent_id'];
				$this->smarty->assign('sub_check_id',$sub_cat_id);//三级栏目当前状态
				$cur_catname=$this->website->get_Cur_Catname($sub_cat_id);
				$cat_id = $cur_catname['parent_id'];
				$ext_id = $CATEGORY[$cat_id]['sort'];
				$is_ext = $cur_catname['sort'];
			}  
		  
			$this->smarty->assign('check_id',$cat_id);//二级栏目当前状态
		
			//上一条
			$this->smarty->assign('up',$this->db->get_article_up($content['add_time'],'',$content['cat_id']));
			$this->smarty->assign('up1',$this->db->get_article_up1($content['add_time'],'',$content['cat_id']));
			//下一条
			$this->smarty->assign('down',$this->db->get_article_down($content['add_time'],'',$content['cat_id']));
			$this->smarty->assign('down1',$this->db->get_article_down1($content['add_time'],'',$content['cat_id']));
            
			//banner[后台广告位id]
			//$banner=$this->website->get_Ads($nav_id);
			//$this->smarty->assign('banner',$banner[$nav_id]);
			
			$is_ext = empty($is_ext) ? 0 : $is_ext;
		    $this->smarty->assign('is_ext',$is_ext);
		    
			$ext_id = empty($ext_id) ? 0 : $ext_id;
		    $this->smarty->assign('ext_id',$ext_id);
			
			//重新定义网站的title、描述、关键字
			$this->smarty->assign('web_name',$content['title'] . '|' . $cur_catname['catname'] . '|' . $cur_root_catname['name'] . '|' . $this->webinfo['web_name']);
			$keywords = empty($cur_catname['keywords']) ? $this->webinfo['web_key'] : $cur_catname['keywords'];
			$description = empty($cur_catname['discription']) ? $this->webinfo['web_discription'] : $cur_catname['discription'];
			$this->smarty->assign('keywords',$keywords);
			$this->smarty->assign('description',$description);
		}
		
		if($html||$act=='create_show'||$act=='create_single'||$act=='create_pimg'){
		    $this->smarty->display($readtemplate);
		}else{
			$this->smarty->display($readtemplate, $cache_id);
		}
	}
	
public function get_dy() {
		extract($_GET);
		global $shuwon, $mysql,$CATEGORY;
	    if(!empty($tags)){$where.=" and tags like '%".$tags."%'";}
		
		$sql = "select * from " . $shuwon->table('news') . " where is_del=-1 and audit=1 and cat_id=9 $where order by sort desc,add_time desc"; 
		$page['data'] = $mysql->getAll($sql);
		foreach ($page['data'] as $k=>$v){
			$page['data'][$k]['url'] = $CATEGORY[$v['cat_id']]['link'].build_uri('article_show',array('id'=>$v['id'],'cat_id'=>$v['cat_id'],'grade'=>$CATEGORY[$v['cat_id']]['grade']));
		}
		$this->smarty->assign('article_list',$page['data']);
		$this->smarty->display('news_ajax.html');
		
	}
	//Ajax调用
	public function ajax() {
		$page = $this->db->get_article_list($_GET['cat_id'],1,'',6,'',0,0,0,0,'',2);//6指每页显示个数
		$this->smarty->assign('article_list',$page['data']);
		$this->smarty->display('news_ajax.html');
	}
	public function dx_ajax() {
		$page = $this->db->get_article_list($_GET['cat_id'],1,'',6,'',0,0,0,0,'',2);//6指每页显示个数
		$this->smarty->assign('article_list',$page['data']);
		$this->smarty->display('Typical_ajax.html');
	}
	public function ajax1() {
		$page = $this->db->get_article_list($_GET['cat_id'],1,'',6,'',0,0,0,0,'',2);//6指每页显示个数
		$this->smarty->assign('article_list',$page['data']);
		$this->smarty->display('sense_ajax.html');
	}
	
	//Ajax调用
	public function case_ajax() {
		$page = $this->db->get_article_list(18,1,'',6,'',0,0,0,0,'',2);
		$this->smarty->assign('article_list',$page['data']);//内容
		$this->smarty->display('case_ajax.html');
	}
	public function news_ajax() {
		$page = $this->db->get_article_list(23,1,'',6,'',0,0,0,0,'',2);
		$this->smarty->assign('article_list',$page['data']);//内容
		$this->smarty->display('news_ajax.html');
	}
	
    //Ajax调用
	public function lists_ajax1() {
		$page = $this->db->get_article_list($_GET['ID'],1,'',999,'',0,0,0,0,'',2);
		$article_list = array();
        $article = array();
        foreach ($page['data'] as $k=>$v){
        	$article['details'] = $v['content'];
        	$article_list['jsonCat'][] = $article;
        }
        echo json_encode($article_list);
	}
	public function show_ajax() {
		global $shuwon, $mysql;
		//获取内容
		$id = $_GET['ID'];
		$content = $this->db->get_article_info($id,'?act=article_show&id='.$id);
		$this->smarty->assign('article',$content);//内容
        //上一条
		$this->smarty->assign('up',$this->db->get_article_up($content['sort'],'',$content['cat_id']));
		//下一条
		$this->smarty->assign('down',$this->db->get_article_down($content['sort'],'',$content['cat_id']));
		$this->smarty->assign('up1',$this->db->get_article_up1($content['add_time'],'',$content['cat_id']));
		$this->smarty->assign('down1',$this->db->get_article_down1($content['add_time'],'',$content['cat_id']));
		$is_team = empty($_GET['is_team']) ? 0 : $_GET['is_team']; 
	    $this->smarty->assign('is_team',$is_team);
		$this->smarty->display('faq_ajax.html');
	}
    //Ajax调用
	public function showq_ajax() {
		global $shuwon, $mysql;
		//获取内容
		$id = $_GET['id'];
		$content = $this->db->get_article_info($id,'?act=article_show&id='.$id);
        $this->smarty->assign('article',$content);//内容
       
		$this->smarty->assign('up',$this->db->get_article_up($content['add_time'],'',$content['cat_id']));
		$this->smarty->assign('down',$this->db->get_article_down($content['add_time'],'',$content['cat_id']));
		
		
		$is_team = empty($_GET['is_team']) ? 0 : $_GET['is_team']; 
	    $this->smarty->assign('is_team',$is_team);
		$this->smarty->display('article_show_ajax.html');
	}
//Ajax调用
	public function show_ajax1() {
		global $shuwon, $mysql;
		//获取内容
		$id = $_GET['id'];
		$content = $this->db->get_article_info($id,'?act=article_show&id='.$id);
        $this->smarty->assign('article',$content);//内容
        //上一条
		//$this->smarty->assign('up',$this->db->get_article_up($content['add_time'],'',$content['cat_id']));
		//下一条
		//->smarty->assign('down',$this->db->get_article_down($content['add_time'],'',$content['cat_id']));
		$is_team = empty($_GET['is_team']) ? 0 : $_GET['is_team']; 
	    $this->smarty->assign('is_team',$is_team);
		$this->smarty->display('Products_ajax.html');
	}
	
	//内容页
	public function build_article($app,$params,$append) {
		$article = array();
		$article['url'] = '';
	    switch ($app){
	        case '0':
	        	$article['url'] = $params.'!'.$append[1];
	        	if($append[0]==$params) $article['checked']=1;
	        	break;
	        case '1':
	        	$article['url'] = $append[0].'!'.$params;
	        	if($append[1]==$params) $article['checked']=1;
	        	break;
	        	
		}
		return $article;
	}
	/*public function search(){
		global $shuwon, $mysql, $CATEGORY;
		extract($_GET);
		extract($_POST);
		$where = '';
		$arr = explode(' ',$title);
	    foreach ($arr as $v){
			if (empty($where)){
				$where = " and title like '%".$v."%'";
				
			}else{
				$where .= " or title like '%".$v."%'";
			}
		}
		//$param = '&keywords='.$keywords;
		//$where.=" and cat_id in (14,8,9,10,11)";
		
	   if(!empty($field1)){$where.=" and field1='".$field1."'";}
	   $this->smarty->assign('field1',$field1);
		$page = new page(array('table'=>$shuwon->table('news'),'numofpage'=>999,'iscurrent' => 1));
		$sql_totle = "select count(*) from " . $shuwon->table('news') . " where is_del=-1 and audit=1 ".$where." order by sort desc,id desc";
		$sql_content = "select * from " . $shuwon->table('news') . " where is_del=-1 and audit='1' $where order by sort desc,id desc";
		$page = $page -> nextPage($sql_totle,$sql_content,$param,$CATEGORY[$cat_id]['ishtml']);
	    foreach ($page['data'] as $k=>$v) {
			if(empty($CATEGORY[$v['cat_id']]['ishtml'])){
		    	$page['data'][$k]['url'] = $CATEGORY[$v['cat_id']]['link'].build_uri('article_show',array('id'=>$v['id'],'cat_id'=>$v['cat_id'],'grade'=>$CATEGORY[$v['cat_id']]['grade']));
			}
			$page['data'][$k]['abstract'] = !empty($page['data'][$k]['abstract']) ? $page['data'][$k]['abstract'] : trim(strip_tags($page['data'][$k]['content']));
		}
		$this->smarty->assign('search_list',$page['data']);
		$this->smarty->assign('page_str',$this->get_Pagestr1($page['page']));
		
		$this->smarty->assign('title',$title);
		$this->smarty->assign('page',$page);
		$this->smarty->display('jytd-1.html');
	}*/
	
}
?>