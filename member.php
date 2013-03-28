<?php
/**
 * Template Name: CorpMembers
 *
 * The template for displaying the list of corporation members.
 *
 * You may copy this file to your theme's directory and modify it according to
 * you needs, as long a you keep the file-name 'members-list.php' and the
 * function call evecorp_the_members().
 *
 * @package evecorp_theme
 */
// Apply filter
add_filter('body_class', 'evecorp_singular_body');

get_header();
?>
<!-- end get_header() -->
<div id="primary">
	<div id="content">
		<div id="article" class="page type-page status-publish">
			<header class="page-header">
				<h1 class="page-title">Member Profile</h1>
			</header>
			<?php evecorp_the_member(); ?>
		</div><!-- #article -->
	</div><!-- #content -->
</div><!-- #primary -->
<!-- start get_footer() -->
<?php get_footer(); ?>