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

/* Silence is golden. */
if ( !function_exists( 'add_action' ) )
	die();

/* This is for members eyes only */
if ( !is_user_logged_in() )
	auth_redirect();

/* Apply page template filter */
add_filter( 'body_class', 'evecorp_singular_body' );

get_header();
?>
<!-- end get_header() -->
<div id="primary">
	<div id="content" role="main">
		<div id="article" class="page type-page status-publish">
			<header class="page-header">
				<h1 class="page-title">Corporation Members</h1>
			</header>
			<?php evecorp_the_members(); ?>
		</article>
	</div><!-- id="content" role="main" -->
</div><!-- #primary -->
<!-- start get_footer() -->
<?php get_footer(); ?>