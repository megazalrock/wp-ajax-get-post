# WP Ajax Get Post
Contributors:      10up
Tags: 
Requires at least: 3.7
Tested up to:      3.7
Stable tag:        0.1.0
License:           GPLv2 or later
License URI:       http://www.gnu.org/licenses/gpl-2.0.html


## Description

You can `get_posts` by JSONP.  
It is also available for multisite WordPress.

### Front End (using jquery)

	$.ajax(ajaxUrl,{
		dataType:'jsonp',
		crossDomain:true,
		cache:false,
		data:{
			action:'wpagp_get_posts',
			query:{
				posts_per_page:4,
				category_name:'hobby',
			}
		}
	})
	.error(function(){
		console.log('error');
	})
	.done(function(response){
		console.log(response);
	});

`query` propaty is same format as WP_Query.

### Filter Hooks

You can edit JSON return values.

	add_filter('wpagp_make_json_data','wpagp_make_json_data', null, 1);
	function wpagp_make_json_data($posts){
		//do_something...
		return $posts;
	};

On multisite, posts data are merged to single array.  
If you want do some thing to post data, you can use these hooks.

	add_filter('wpagp_ajax_get_posts_each_site','wpagp_make_json_data', null, 1);
	function wpagp_make_json_data($posts){
		//do_something...
		return $posts;
	};

## Installation

### Manual Installation

1. Upload the entire `/wp_ajax_get_post` directory to the `/wp-content/plugins/` directory.
2. Activate WP Ajax Get Post through the 'Plugins' menu in WordPress.

## Frequently Asked Questions

## Changelog

= 0.1.0 =
* First release

## Upgrade Notice

= 0.1.0 =
First Release