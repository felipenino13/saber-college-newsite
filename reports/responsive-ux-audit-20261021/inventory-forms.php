<?php
$ids = get_posts(array('post_type'=>'page','post_status'=>'publish','numberposts'=>-1,'fields'=>'ids'));
$out=[];
$walk=function($nodes,$id,$title,$has_api) use (&$walk,&$out){foreach($nodes as $n){if(($n['widgetType']??'')==='form'){$s=$n['settings']??[];$out[]=['id'=>$id,'title'=>$title,'widget'=>$n['id']??'','name'=>$s['form_name']??'','actions'=>$s['submit_actions']??[],'fields'=>array_map(fn($f)=>[$f['custom_id']??'', $f['field_type']??'text', $f['required']??''], $s['form_fields']??[]),'has_verity'=>$has_api];}if(!empty($n['elements']))$walk($n['elements'],$id,$title,$has_api);}};
foreach($ids as $id){$j=get_post_meta($id,'_elementor_data',true);$d=json_decode($j,true);if(!is_array($d))continue;$walk($d,$id,get_the_title($id),stripos($j,'api.verityiq.com')!==false);}
echo wp_json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
