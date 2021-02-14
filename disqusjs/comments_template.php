<?php
if (!defined('ABSPATH')) die;

if (post_password_required()) {
	return;
}

// Don't show comments for Beaver Builder plugin or previews
if (isset($_GET['fl_builder']) || isset($_GET['preview'])) {
	return;
}

// array of post statuses which should NOT display comments
$no_comments = apply_filters('disqusjs_post_statuses', array('draft', 'trash', 'future'));

if (in_array(get_post_status(), $no_comments)) {
	if (is_super_admin()) { // admins see notice only
?>
		<div id="comments" class="comments-area">
			<p>Disqus comments are not displayed until the post is published.</p>
		</div>
<?php
	}
	return;
}

// $disqus_shortname = '';
$options = get_option('disqusjs_settings');
$shortname = sanitize_text_field($options['disqus_shortname']);
$apiendpoint = sanitize_text_field($options['disqus_apiendpoint']);
$apikey = sanitize_text_field($options['disqus_apikey']);
$nesting = sanitize_text_field($options['disqus_nesting']);
$nocomment = sanitize_text_field($options['disqus_nocomment']);
$adminname = sanitize_text_field($options['disqus_admin']);
$adminbadge = sanitize_text_field($options['disqus_badge']);
$plugin_dir = WP_PLUGIN_URL . '/'. str_replace( basename( __FILE__ ), "", plugin_basename(__FILE__) );
$disqus_stylesheet = $plugin_dir . 'disqusjs.css';
$disqus_script = $plugin_dir . 'disqus.js';
$disqus_customcss = sanitize_textarea_field($options['disqus_customcss']);
?>
<div id="comments" class="comments-area">
	<div style="text-align:center;">
  <button class="btn" id="load-disqus" onclick="loadDisqus();">Load comments</button>
</div>
	<div id="disqus_thread"></div>
	<!-- disqusjs -->
	<link rel='stylesheet' href='<?php echo $disqus_stylesheet; ?>' type='text/css' media='all' />
	<?php if (isset($options['disqus_customcss'])) { ?>
		<style type='text/css'>
			<?php echo $disqus_customcss; ?>
		</style>
	<?php } ?>
	<script defer type='text/javascript'>
		(function() {
			var d = document,
				s = d.createElement('script');
			s.src = '<?php echo $disqus_script; ?>';
			s.defer = 'defer';
			s.setAttribute('data-timestamp', +new Date());
			(d.head || d.body).appendChild(s);
		})();
		
		function loadDisqus(){
			var disqusInstance;
			if (typeof DisqusJS !== "undefined") {
				disqusInstance = new DisqusJS({
								shortname: '<?php echo $shortname; ?>',
								identifier: '<?php echo $post->ID . ' ' . $post->guid; ?>',
								url: '<?php the_permalink(); ?>',
								title: '<?php the_title_attribute(); ?>',
								api: '<?php echo $apiendpoint; ?>',
								apikey: '<?php echo $apikey; ?>',
								admin: '<?php echo $adminname; ?>',
								adminLabel: '<?php echo $adminbadge; ?>'
							});
			  if(disqusInstance){
				  var btnObject = document.getElementById('load-disqus');
             if (btnObject != null)
                btnObject.parentNode.removeChild(btnObject);
			  }
			}
			return disqusInstance;
		}

		if (typeof window !== "undefined") {
			if ("IntersectionObserver" in window) {
				var disqus_observer = new IntersectionObserver(function(entries) {
					if (entries[0].intersectionRatio > 0) {
						// entered visibile area
						// initialize disqusjs
						if (typeof loadDisqus() !== "undefined") {
							disqus_observer.disconnect();
							};
						}
					}, {
					threshold: [0]
				});
				// 设置让 Observer 观察 #disqus_thread 元素
				disqus_observer.observe(document.getElementById('disqus_thread'));
			} else {
				function scollMonitor() {
					var currentScroll = document.scrollingElement.scrollTop;
              var disqus_target = document.getElementById('disqus_thread');
					// entered visibile area
					if (disqus_target && (currentScroll > disqus_target.getBoundingClientRect().top - 150)) {
						if (typeof loadDisqus() !== "undefined") {
							window.removeEventListener("scroll", scrollMonitor);
						}
					}
				}

				window.addEventListener("scroll", scollMonitor, {
					passive: true
				});
			}
		}
	</script>
	<noscript>Please enable JavaScript to view comments powered by Disqus.</noscript>
</div>