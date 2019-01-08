<?php

/*
Plugin Name: Contenteamizer
Description: Adds picture to all draft posts or draft pages by post or page titles.
Version: 1.0
Author: Alex Slavko aka "Tupo Master"
*/

require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

register_activation_hook(__FILE__, 'getLoadImgTurn');
// add_action('init', 'getLoadImgTurn', 10, 1);

function getLoadImgTurn(){
//access to WP db
    $my_posts = new WP_Query;
//URL for finding image
// $url = "https://www.bing.com/images/search?sp=-1&pq=".str_replace(" ","+",$handle_item)."&sc=8-6&sk=&cvid=C1E660A7D9B644928ED6A3DF14B77EBA&q=".str_replace(" ", "+",$handle_item)."&qft=+filterui:imagesize-medium+filterui:photo-photo&FORM=IRFLTR";
    $url = "https://www.bing.com/images/search?sp=-1&pq=".str_replace(" ","+",$handle_item)."&sc=8-6&sk=&cvid=C1E660A7D9B644928ED6A3DF14B77EBA&q=".str_replace(" ", "+",$handle_item)."&qft=+filterui:imagesize-medium+filterui:photo-photo&FORM=IRFLTR";
    $draftPosts = array(); // variable - post/pages draft data (title, ID, content, images) 
    $output = []; // variable - pictures url array
    $editPost = array();
    $rightLeftImgAlign = ['alignleft', 'alignright'];

//get WP Object from WP DB
    $myposts = $my_posts->query(array(
        'post_status' => 'draft',
        'post_type'=>['post','page']
    ));

//current theme data from WP db without image url  
    foreach( $myposts as $pst ){
        $draftPosts[$pst->ID]['p_title']=$pst->post_title;
        $draftPosts[$pst->ID]['p_сontent']=$pst->post_content;
        preg_match_all('!<a class="thumb" target="_blank" href="(.*?)"!',get(urlConstract($pst->post_title)),$url_matches);
        $draftPosts[$pst->ID]['p_newImg']= $url_matches[1][0];
    }
//fill array $draftPosts by imgLoad function     
    foreach ($draftPosts as $key => $value) {
        imgLoad($value['p_newImg'], $key, $value['p_title']);
    }
//adds to array with posts/pages data are data about images ID in WP DB
    foreach ($draftPosts as $key => $value) {
        $attachId = new WP_Query(array(
            'post_status' => 'any',
            'post_type' => 'attachment',
            // 'post_mime_type' => 'image/jpeg',
            'post_parent' => $key
        ));
        $draftPosts[$key]['p_img_id'] = $attachId->posts[0]->ID;
    }
//get picture URL from WP DB and insert them to draft post or pages
    foreach ($draftPosts as $key => $value) {
        $side;
        $img = wp_get_attachment_image_url($value['p_img_id'], 'medium');
        $editPost['ID'] = $key;
        if (array_rand($rightLeftImgAlign) == 0) {
            $side = $rightLeftImgAlign[0];
        } else {
            $side = $rightLeftImgAlign[1];
        }
        // $editPost['post_content'] = '<img class="alignnone size-medium wp-image-19 alignleft" src="'.$img.'" alt="'.$value['p_title'].'"/>'.$value['p_сontent'];
        $editPost['post_content'] = '<img class="alignnone size-medium wp-image-19 '.$side.'" src="'.$img.'" alt="'.$value['p_title'].'"/>'.$value['p_сontent'];
        wp_update_post( wp_slash($editPost));
    }
}

// get image url from browser (Bing now)
    function get($url){ 
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
//function for constraction URL 
    function urlConstract($keyword){
        return $url = "https://www.bing.com/images/search?sp=-1&pq=".str_replace(" ","+",$keyword)."&sc=8-6&sk=&cvid=C1E660A7D9B644928ED6A3DF14B77EBA&q=".str_replace(" ", "+",$keyword)."&qft=+filterui:imagesize-medium+filterui:photo-photo&FORM=IRFLTR";
    }
//function uploadign image to WP database by image URL 
    function imgLoad($url_address, $id, $img_desc){ 
            $url = $url_address; 
            $post_id = $id;
            $desc = $img_desc;

        $img_tag = media_sideload_image( $url, $post_id, $desc );

        if( is_wp_error($img_tag) ){
            echo $img_tag->get_error_message();
        }    
    }

    // $rightLeftImgAlign = ['alignleft', 'alignright'];
    // if (array_rand($rightLeftImgAlign) == 0) {
    //     $side = $rightLeftImgAlign[0];
    // } else {
    //     $side = $rightLeftImgAlign[1];
    // }

?>