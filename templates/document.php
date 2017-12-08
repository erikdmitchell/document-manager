<?php 
/**
 * The template for displaying a single document
 *
 * This template can be overridden by copying it to yourtheme/document-manager/document.php.
 *
 * @version 1.0.0
 */
    
get_header(); ?>

<article id="post-<?php dm_document_doc_id(get_the_ID()); ?>" <?php post_class('', dm_get_document_id(get_the_ID())); ?>>
    
	<header class="entry-header">
		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
	</header><!-- .entry-header -->

	<div class="entry-content">
		<?php dm_document_description(); ?>

        <div class="document">
            <a href="<?php dm_document_download_url(get_the_ID()); ?>">
                <?php dm_document_image(get_the_ID()); ?>
            </a>
        </div>
	</div><!-- .entry-content -->

</article><!-- #post-## -->

<?php get_footer(); ?>