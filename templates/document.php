<?php get_header(); ?>

<article id="post-<?php dm_document_doc_id(get_the_ID()); ?>" <?php post_class('', dm_get_document_id(get_the_ID())); ?>>
    
	<header class="entry-header">
		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
	</header><!-- .entry-header -->

	<div class="entry-content">
		<?php dm_document_description(); ?>

        <div class="document">
            <a href="<?php echo wp_get_attachment_url(dm_get_document_id(get_the_ID())); ?>">
                <?php echo wp_get_attachment_image(dm_get_document_id(get_the_ID()), 'medium'); ?>
            </a>
        </div>
	</div><!-- .entry-content -->

</article><!-- #post-## -->

<?php get_footer(); ?>