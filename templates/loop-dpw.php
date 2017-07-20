<?php $img = wp_get_attachment_image_src(get_post_thumbnail_ID(),'medium'); $img = $img[0];?>

<div <?php post_class('dpw-post');?>>
	<a href="<?php the_permalink();?>">
		<img src="<?php echo $img;?>" alt="<?php the_title();?>">
	</a>
	<h4><a href="<?php the_permalink();?>"><?php the_title();?></a></h4>
</div>