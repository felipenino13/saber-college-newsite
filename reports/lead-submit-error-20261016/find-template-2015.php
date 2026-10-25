<?php
global $wpdb;
$rows=$wpdb->get_col("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_elementor_data' AND meta_value LIKE '%elementor-template id=%2015%'");
foreach($rows as $id){$p=get_post($id); echo $id.'|'.($p?$p->post_title:'').'|'.($p?get_permalink($id):'').PHP_EOL;}
