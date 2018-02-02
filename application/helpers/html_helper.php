<?php defined('BASEPATH') OR exit('No direct script access allowed');
/****************************************************************
 * 伯嘉基金 - html代码组装公共函数 v1.0
 * 使用方法:filter_slashes(接收的参数);
 *---------------------------------------------------------------
 * Copyright (c) 2004-2016 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:linfeng $addtime:2016-11-15
 ****************************************************************/
  
/*
 * 分页
 * @param  integer   $total 总数量
 * @param  integer   $page  页码
 * @param  integer   $pagesize  单页面数量
 * @return string
 */
function pageajax($total,$page,$pagesize)
{
	if($total && $page && $pagesize)
	{
		$pagenumarr = array('1','2','4','6','10');
		$html = '';
		//$html = '<i class="Fl">共有 <a href="#">'.$total.'</a> 条数据，当前第 <a href="#">'.$page.'</a> 页</i>';
		$html .= '<a href="javascript:;" class="NoNm" onclick=\'loadpage("1","'.$pagesize.'")\'>首页</a>';
		if($page=='1')
		$html .= '<a href="javascript:;" class="NoNm" >上一页</a>';//当前页
		else
		$html .= '<a href="javascript:;" class="NoNm" onclick=\'loadpage("'.($page-1).'","'.$pagesize.'")\'>上一页</a>';
		$num = ceil($total/$pagesize);
		for($i=1;$i<$num+1;$i++){
			if($i==$page)
				$html .= '<a href="javascript:;" class="Cur">'.$i.'</a>';
			else
				$html .= '<a href="javascript:;" onclick=\'loadpage("'.$i.'","'.$pagesize.'")\'>'.$i.'</a>';
		}
		if($page==$num)
			$html .= '<a href="javascript:;" class="NoNm" >下一页</a>';//当前页
		else
			$html .= '<a href="javascript:;" class="NoNm" onclick=\'loadpage("'.($page+1).'","'.$pagesize.'")\'>下一页</a>';
		if($page==$num)
			$html .= '<a href="javascript:;" class="NoNm" >尾页</a>';//当前页
		else
			$html .= '<a href="javascript:;" class="NoNm" onclick=\'loadpage("'.$num.'","'.$pagesize.'")\' >尾页</a>';//当前页
		$html .= '<b class="Pl10">每页</b>';
		$html .= '<span class="FormSlt w30">';
		$html .= '<select class="pagesize" name="pagesize" language="javascript" onchange = "pagenum_onchange(this);">';
		foreach($pagenumarr as $val){			
			$panum = $val*5;
			if($pagesize == $panum)
				$html .= '<option selected="selected" value="'.$panum.'">'.$panum.'</option>';
			else
				$html .= '<option value="'.$panum.'">'.$panum.'</option>';
		}
		$html .= '</select>';
		$html .= '</span><b>条</b>';
		return $html;
	}
}

/* End of file security_helper.php */
/* Location: ./application/helper/security_helper.php */