<?php
namespace st;

/**
 *
 * Google Analytics
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2017-09-27
 *
 */


function google_analytics_code( $id = '' ) {
	if ( empty( $id ) ) {
		if ( is_user_logged_in() ) {
		?>
<script>
document.addEventListener("DOMContentLoaded",function(){var e=document.createElement("div");
e.innerText="The ID of Google Analytics Code is not assigned!",e.style.position="fixed",
e.style.right="0",e.style.bottom="0",e.style.background="red",e.style.color="white",e.style.padding="4px",
e.style.zIndex=9999,document.body.appendChild(e),console.log("The ID of Google Analytics Code is not assigned!")});
</script>
		<?php
		}
	} else {
	?>
<script>
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
ga('create', '<?php echo $id ?>', 'auto');
ga('send', 'pageview');
</script>
	<?php
	}
}
