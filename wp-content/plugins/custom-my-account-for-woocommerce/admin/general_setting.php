<?php 

 if(isset($_POST['update_settings']))
 {
	$check = update_option('myaccount_general_setting', $_POST);
	 
	 if($check ==1)
		{
		?>

			<div class="updated" id="message">

				<p><strong>Setting updated.</strong></p>

			</div>

		<?php
		}
		else
		{
			?>
				<div class="error below-h2" id="message"><p> Something Went Wrong Please Try Again With Valid Data.</p></div>
			<?php
		}
 }
 if(isset($_POST['phoen_reset']))
 {
	 $reset = array(
					'custom_profile'=>'enable',
					'menu_style'=>'sidebar',					
					'menu_item_color'=>'#777777',
					'menu_item_hover_color'=>'#000000',
					'logout_color'=>'#ffffff',
					'logout_hover_color'=>'#ffffff',
					'logout_bg_color'=>'#c0c0c0',
					'logout_hover_bg_color'=>'#333333'
					
	);
	$check = update_option('myaccount_general_setting', $reset); 
	
	if($check ==1)
		{
		?>

			<div class="updated" id="message">

				<p><strong>Setting has been Reset.</strong></p>

			</div>

		<?php
		}
		else
		{
			?>
				<div class="error below-h2" id="message"><p> Something Went Wrong Please Try Again With Valid Data.</p></div>
			<?php
		}
 }
  $row = get_option('myaccount_general_setting');

?>

<form method="post" action="">
	<table class="form-table" style="background:#fff;">
		<tbody>
		<h3>General Options</h3>
			<tr>
				<th>Custom Profile</th>
				<td><input type="checkbox" value="enable" name="custom_profile" <?php if($row['custom_profile']=='enable'){echo"checked";}?>><td>
			</tr>
			<tr>
				<th>Menu Style</th>
				<td><input type="radio" <?php if($row['menu_style']=='sidebar'){ echo "checked";}?> name="menu_style" value="sidebar">Sidebar &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" <?php if($row['menu_style']=='tab'){echo "checked";}?> name="menu_style" value="tab">Tab<td>
			</tr>
		</tbody>
	</table>
	<table class="form-table" style="background:#fff;">
		<tbody>
		<h3>Style Options</h3>
			<tr>
				<th>Menu item color</th>
				<td><input type="text" value="<?php echo $row['menu_item_color'];?>" name="menu_item_color" id="menu_item_color"><td>
			</tr>
			<tr>
				<th>Menu item color on hover</th>
				<td><input type="text" value="<?php echo $row['menu_item_hover_color'];?>" name="menu_item_hover_color" id="menu_item_hover_color">
				
			</tr>
			<tr>
				<th>Logout color</th>
				<td><input type="text" value="<?php echo $row['logout_color'];?>" name="logout_color" id="logout_color"><td>
			</tr>
			<tr>
				<th>Logout color on hover</th>
				<td><input type="text" value="<?php echo $row['logout_hover_color'];?>" name="logout_hover_color" id="logout_hover_color"><td>
			</tr>
			<tr>
				<th>Logout background color </th>
				<td><input type="text" value="<?php echo $row['logout_bg_color'];?>" name="logout_bg_color" id="logout_bg_color"><td>
			</tr>
			<tr>
				<th>Logout background on hover color</th>
				<td><input type="text" value="<?php echo $row['logout_hover_bgcolor'];?>" name="logout_hover_bgcolor" id="logout_hover_bgcolor"><td>
			</tr>
			
		</tbody>
	</table>
	<p class="submit"><input type="submit" value="Save changes" class="button button-primary" id="submit" name="update_settings">&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" onclick="return confirm('If you continue with this action, you will reset all options in this page.\nAre you sure?');" value="Reset Defaults" class="button-secondary" name="phoen_reset">
</p>
</form>