<?php
/*
Plugin Name: Popular on the web
Plugin URI: http://wprdpress.com/
Description: Showing latest trending searches on the web based on google search results.
Version: 1.0
Author: Darko Atanasovski
Author URI: http://atanasovski.tumblr.com
*/
if ( !defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( is_admin() === true ) {
    new popularSearches();
}

class popularSearches {

	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action('admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_ajax_getpow', array( $this, 'getpow_callback' ) );	
	}
	
	public function enqueue_assets() {
		wp_enqueue_style( "pow-style",  plugins_url( 'popular_on_the_web.css', __FILE__ ) );
		wp_enqueue_script( "pow-script",  plugins_url( 'popular_on_the_web.js', __FILE__ ), array('jquery') );
	}
	
	public function add_meta_box( $post_type ) {
            $post_types = array('post'); 
            if ( in_array( $post_type, $post_types )) {
				add_meta_box(
					'popular_searches_meta_box'
					,__( 'Popular for ' . date('d M Y'), 'popular_searches_textdomain' )
					,array( $this, 'render_meta_box_content' )
					,$post_type
					,'side'
					,'high'
				);
            }
	}

	public function getpow_callback() {
		header('Contant-Type: text/json');
		
		check_ajax_referer( 'pow-nonce', 'nonce' );
		
		$output = array();
		$xmlitems = $this->get_popular_searches( $_POST[ 'region_id' ] );
		$i=0;
		foreach( $xmlitems->channel->item as $item )
		{
			$output[$i]['title'] 	= $item->title->__toString();
			$output[$i]['url']		= $item->news_item->news_item_url->__toString();
			$output[$i]['snippet']	= implode(" ",array_splice(explode(" ",$item->news_item->news_item_snippet->__toString()),0,13)).'...';
			$output[$i]['picture']	= $item->picture->__toString();
			$i++;
		}
		echo json_encode( $output );
		wp_die();
	}
	
	public function get_popular_searches( $id = 'p1' ) {
		$items = apc_fetch( 'pow-items-' . $id );
		if( false === $items )
		{
			$url = 'https://www.google.com/trends/hottrends/atom/feed?pn=' . $id;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36');
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_SSLVERSION , 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_URL , $url );
			$response = curl_exec($ch);
			if($response === false)
			{
			    exit( 'Curl error: ' . curl_error($ch) );
			}
			curl_close($ch);
			$items = str_replace(array('<ht:','</ht:'),array('<','</'), $response);
			apc_store( 'pow-items-' . $id, $items, 600 );
		}

		return simplexml_load_string($items);
	}
	
	public function render_meta_box_content( $post ) {
		?>
		<div id="pow-wrapper">
			<div id="pow-region-wrapper">
				<a href="javascript:;" id="pow-region" class="text-center">United States</a>
				<select id="pow-choose-region" data-nonce="<?php echo wp_create_nonce('pow-nonce'); ?>">
					<option value="p30">Argentina</option><option value="p8">Australia</option><option value="p44">Austria</option><option value="p41">Belgium</option><option value="p18">Brazil</option><option value="p13">Canada</option><option value="p38">Chile</option><option value="p32">Colombia</option><option value="p43">Czech Republic</option><option value="p49">Denmark</option><option value="p29">Egypt</option><option value="p50">Finland</option><option value="p16">France</option><option value="p15">Germany</option><option value="p48">Greece</option><option value="p10">Hong Kong</option><option value="p45">Hungary</option><option value="p3">India</option><option value="p19">Indonesia</option><option value="p6">Israel</option><option value="p27">Italy</option><option value="p4">Japan</option><option value="p37">Kenya</option><option value="p34">Malaysia</option><option value="p21">Mexico</option><option value="p17">Netherlands</option><option value="p52">Nigeria</option><option value="p51">Norway</option><option value="p25">Philippines</option><option value="p31">Poland</option><option value="p47">Portugal</option><option value="p39">Romania</option><option value="p14">Russia</option><option value="p36">Saudi Arabia</option><option value="p5">Singapore</option><option value="p40">South Africa</option><option value="p23">South Korea</option><option value="p26">Spain</option><option value="p42">Sweden</option><option value="p46">Switzerland</option><option value="p12">Taiwan</option><option value="p33">Thailand</option><option value="p24">Turkey</option><option value="p35">Ukraine</option><option value="p9">United Kingdom</option><option value="p1">United States</option><option value="p28">Vietnam</option>
				</select>
			</div>
			<div id="pow-content">
		<?php
		$xmlitems = $this->get_popular_searches( 'p1' );
		foreach( $xmlitems->channel->item as $item ):
		?>
		<div class="row">
			<div class="col-xs-4 text-center">
				<a href="<?php echo $item->news_item->news_item_url; ?>" target="_blank">
					<img src="<?php echo $item->picture; ?>" alt="<?php echo $item->title; ?>" class="pow-thumb">
				</a>
			</div>
			<div class="col-xs-8">
				<a href="<?php echo $item->news_item->news_item_url; ?>" target="_blank"><?php echo $item->title; ?></a>
				<div class="snippet">
					<?php echo implode(" ",array_splice(explode(" ",$item->news_item->news_item_snippet),0,13)).'...'; ?>
				</div>
			</div>
		</div>
		<?php
		endforeach;
		echo '</div></div>';
	}
}