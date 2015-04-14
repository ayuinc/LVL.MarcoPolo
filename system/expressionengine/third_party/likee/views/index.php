<div id="likEE">
<h1><?= lang('cp_header') ?></h1>

<?php
	if ($instructions){
?>
	<div class="no-likes">
		<h2><?= lang('cp_no_likes_header') ?></h2>
		<p><?= lang('cp_integrating_description') ?></p>
		<h3><?= lang('cp_step_1') ?></h3>
		<p><?= lang('cp_step_1_desc') ?></p>
		<pre><code><?= $js ?></code></pre>
		<h3><?= lang('cp_step_2') ?></h3>
		<p><?= lang('cp_step_2_desc') ?></p>
		<pre><code><?= $likeeCode ?></code></pre>
		<h3><?= lang('cp_step_3') ?></h3>
		<p><?= lang('cp_step_3_desc') ?> <a href="http://fromtheoutfit.com/likee">fromtheoutfit.com/likee</a></p>
	</div>
<?php
	} else {
?>
	
	<p class="description"><?= lang('cp_description') ?></p>
	<?php if (sizeof($likes)>0){ ?>
		<div class="box">
		<h2><?= lang('cp_top_liked') ?> <?= lang('cp_entries') ?></h2>
		<?php
			// Entry Likes
			$this->table->set_template($cp_table_template);
			$this->table->set_heading(lang('cp_channel'), lang('cp_entry'), lang('cp_likes'));
			
			foreach($likes AS $like){
				$this->table->add_row($like["channel_name"],$like["title"],$like["count"]);
			}	
			
			echo $this->table->generate();
			$this->table->clear();
		?>
		</div>
	<?php } ?>
	
	<?php if (sizeof($dislikes)>0){ ?>
		<div class="box">
		<h2><?= lang('cp_top_disliked') ?> <?= lang('cp_entries') ?></h2>
		<?php	
			// Entry Dislikes
			$this->table->set_template($cp_table_template);
			$this->table->set_heading(lang('cp_channel'),lang('cp_entry'),lang('cp_dislikes'));
			
			
			foreach($dislikes AS $dislike){
				$this->table->add_row($dislike["channel_name"],$dislike["title"],$dislike["count"]);
			}
			
			echo $this->table->generate();
			$this->table->clear();
		?>
		</div>
	<?php } ?>
	<span class="clear"></span>
		
	<?php
		
	}

?>
</div>