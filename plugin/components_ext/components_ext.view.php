<?php global $components_ext, $USR;

# create components form html
$data = $components_ext->return_ext_components();
$count = 0;
?>
	<h3 class="floated"><?php echo i18n('EDIT_COMPONENTS');?></h3>
	<div class="edit-nav" >
		<a href="javascript:void(0)" id="btn-new" accesskey="<?php echo find_accesskey(i18n_r('ADD_COMPONENT'));?>" ><?php i18n('ADD_COMPONENT');?></a>
		<a href="javascript:void(0)" id="component-sort">
			<span>sort</span>
			<select>
				<option value="title">A-z</option>
				<option value="title" data-invert>Z-a</option>
				<option value="created_dt"><?php i18n('components_ext/MOST_RECENT'); ?></option>
				<option value="created_dt" data-invert><?php i18n('components_ext/OLDEST'); ?></option>
				<option value="modified_dt"><?php i18n('components_ext/LAST_MODIFIED'); ?></option>
			</select>
		</a>
		<div class="clear"></div>
	</div>
		<div id="divTxt">
		<div id="new-component" class="compdiv expanded" style="display: none;">
	    <form action="<?php myself(); ?>?id=components_ext&action=edit" class="manyinputs" method="post" accept-charset="utf-8" spellcheck="false">
	    <table class="comptable">
	      <tr><td class="editcomp">
	        <label for="title-new"><?php i18n('components_ext/TITLE'); ?>:</label><input type="text" class="comptitle text" id="title-new" name="title">
		      <label for="slug-new"><?php i18n('components_ext/SLUG'); ?>:</label><input type="text" class="compslug text"  id="slug-new" name="slug">
		    </td></tr>
		  </table>
		  <textarea name="val" class="compval text"></textarea>
			<?php exec_action('component-extras'); ?>
				<p class="submit_line">
					<span><input type="button" name="submitted" class="btn-save button" value="<?php i18n('BTN_SAVECHANGES');?>" /></span> 
					&nbsp;&nbsp;<?php i18n('OR'); ?>&nbsp;&nbsp; <button type="button" class="btn-cancel button" id="cancel-new"><?php i18n('CANCEL'); ?></button>
				</p>
			</form>
		</div>
		<?php foreach($data as $comp) { ?>
	  <div class="compdiv" id="section-<?php echo $count + 1; ?>">
	    <form action="<?php myself(); ?>?id=components_ext&action=edit" class="manyinputs" method="post" accept-charset="utf-8" spellcheck="false">
	    <table class="comptable">
	      <tr>
	        <td class="comp-title"><strong><?php echo stripslashes($comp->title); ?></strong></td>
	        <td class="editcomp">
	          <label for="title<?php echo $count; ?>"><?php i18n('components_ext/TITLE'); ?>:</label><input type="text" class="comptitle text" id="title<?php echo $count; ?>" name="title" value="<?php echo $comp->title; ?>">
	          <label for="slug<?php echo $count; ?>"><?php i18n('components_ext/SLUG'); ?>:</label><input type="text" class="compslug text"  id="slug<?php echo $count; ?>" name="slug" value="<?php echo $comp->slug; ?>">
	        </td>
		      <td class="comp-snippet" title="<?php i18n('components_ext/DBLCLICK_COPY'); ?>"><code>&lt;?php get_ext_component('<span class="compslugcode"><?php echo $comp->slug; ?></span>'); ?&gt;</code></td>
		      <td class="comp-edit"><a href="javascript:void(0)" title="Edit component" class="btn-edit" rel="<?php echo $count; ?>"><?php i18n('EDIT');?></a></td>
		      <td class="comp-active" style="display: none !important;"><a href="javascript:void(0)" title="Activate component" class="btn-active">🔒</a></td>
		      <td class="delete"><a href="javascript:void(0)" title="<?php i18n('DELETE_COMPONENT'); echo ': \'' . stripslashes($comp->title); ?>'?" class="btn-delete" rel="<?php echo $count; ?>">&times;</a></td>
		    </tr>
		  </table>
			<textarea name="val" class="compval text"><?php echo $comp->value; ?></textarea>
			<input type="hidden" name="modified_dt" class="compmodified_dt" value="<?php echo @$comp->modified_dt; ?>">
			<input type="hidden" name="created_dt"  class="compcreated_dt" value="<?php echo @$comp->created_dt; ?>">
			<input type="hidden" name="modified_by" value="<?php echo @$comp->modified_by; ?>">
			<input type="hidden" name="oldslug" value="<?php echo $comp->slug; ?>">
			<?php $table = ''; exec_action('component-extras'); echo $table; ?>
			<?php exec_action('component-extras'); ?>
			<p class="submit_line">
				<span style="float: left;"><?php 
					if (strlen($comp->modified_dt . $comp->modified_by) > 0) {
						$dt_time     = explode(' ', $comp->modified_dt);
						$last_editor = strlen(@$comp->modified_by) > 0 ? ' ' . i18n_r('components_ext/BY') . ' <span class="modified_by">' . $comp->modified_by . '</span>' : '';
						$modified_dt = count($dt_time) == 2 ? ' ' . i18n_r('components_ext/ON') . ' <span class="modified_dt">' . $dt_time[0] . '</span> ' . i18n_r('components_ext/AT') . ' <span class="modified_dt">' . $dt_time[1] . '</span>' : '';
						echo i18n_r('components_ext/LAST_MODIFIED') . $last_editor . ' ' . $modified_dt;
					}					
			  ?></span>
				<span><input type="button" name="submitted" class="btn-save button" value="<?php i18n('BTN_SAVECHANGES');?>" /></span> 
				&nbsp;&nbsp;<?php i18n('OR'); ?>&nbsp;&nbsp; <button type="button" class="btn-cancel button"><?php i18n('CANCEL'); ?></button>
			</p>
			</form>
		</div>
		<?php $count++; } ?>
		<input type="hidden" name="user" value="<?php echo $USR; ?>">
		<input type="hidden" name="nonce" value="<?php echo get_nonce('components_ext_action', 'components_ext.php'); ?>">
		 </div>
	<script type="text/template" id="tpl-search">
	<form id="component-search">
		<i>&#9906;</i>
		<input type="text" class="text">
		<div id="component-search-list" style="display: none;"></div>
	</form>
	</script> 
	<script type="text/template" id="tpl-compdiv">
	  <div class="compdiv" id="section-%n%">
	    <form action="<?php myself(); ?>?id=components_ext&action=edit" class="manyinputs" method="post" accept-charset="utf-8" spellcheck="false">
		    <table class="comptable">
		      <tr>
		        <td class="comp-title"><strong>%title%</strong></td>
		        <td class="editcomp">
		          <label for="title%n%"><?php i18n('components_ext/TITLE'); ?>:</label><input type="text" class="comptitle text" id="title%n%" name="title" value="%title%">
			        <label for="slug%n%"><?php i18n('components_ext/SLUG'); ?>:</label><input type="text" class="compslug text"  id="slug%n%" name="slug" value="%slug%">
			      </td>
			      <td class="comp-snippet" title="<?php i18n('components_ext/DBLCLICK_COPY'); ?>"><code>&lt;?php get_ext_component(<span class="compslugcode">'%slug%'</span>); ?&gt;</code></td>
			      <td class="comp-edit"><a href="javascript:void(0)" title="Edit component" class="btn-edit" rel="%n%"><?php echo lowercase(i18n_r(('EDIT')));?></a></td>
			      <td class="delete"><a href="javascript:void(0)" title="<?php i18n('DELETE_COMPONENT'); ?> '%title%'?" class="btn-delete" rel="%n%">&times;</a></td>
			    </tr>
			  </table>
				<textarea name="val" class="compval text">%val%</textarea>
			  <?php exec_action('component-extras'); ?>
				<p class="submit_line">
					<span><input type="submit" class="btn-save button" name="submitted" value="<?php i18n('BTN_SAVECHANGES');?>" /></span> 
					&nbsp;&nbsp;<?php i18n('OR'); ?>&nbsp;&nbsp; <button type="button" class="btn-cancel button"><?php i18n('CANCEL'); ?></button>
				</p>
			</form>
		</div>
	</script>
	<script>(function(i18n) {
		i18n = i18n || {};

		var labels = {
	    cancelUpdates: '<?php i18n('components_ext/MSG_CANCEL_UPDATES'); ?>',
	    noSlugOrTitle: '<?php i18n('components_ext/MSG_NO_SLUG_TITLE'); ?>',
	    comp_created : '<?php i18n('components_ext/MSG_COMP_CREATED');  ?>',
	    comp_updated : '<?php i18n('components_ext/MSG_COMP_UPDATED' ); ?>',
	    comp_deleted : '<?php i18n('components_ext/MSG_COMP_DELETED' ); ?>',
	    existing_slug: '<?php i18n('components_ext/MSG_EXISTING_SLUG'); ?>',
	    delete_component: '<?php i18n('DELETE_COMPONENT'); ?>',
	    error        : '<?php i18n('ERROR'); ?>',
		};

		for (var l in labels) {
			i18n[l] = labels[l];
		}
	}(GS.i18n));
	</script>
	<?php if (version_compare(GSVERSION, '3.4', '>=')) { ?>
    <style>
			.error { background-color: rgb(245, 225, 225); }
			.updated { background-color: #FFFBCC; }
		</style>
	<?php } ?>