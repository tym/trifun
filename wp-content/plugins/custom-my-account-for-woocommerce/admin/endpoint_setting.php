<?php
if(isset($_POST['phoen_reset']))
		{
			$arg ='view-orders,edit-account,edit-address';

				$view_orders =array(
					'active'=>'1',
					'slug'=>'view-orders',
					'label'=>'My Orders',
					'icon'=>'file-text-o',
					'content'=>'[view_order_content]'
				);

				$edit_account =array(
					'active'=>'1',
					'slug'=>'edit-account',
					'label'=>'Edit Account',
					'icon'=>'pencil-square-o',
					'content'=>''
				);

				$edit_address =array(
					'active'=>'1',
					'slug'=>'edit-address',
					'label'=>'Edit Address',
					'icon'=>'pencil-square-o',
					'content'=>''
				);

				update_option('phoen-endpoint', $arg);
				update_option('phoen-endpoint-view-orders', $view_orders);
				update_option('phoen-endpoint-edit-account', $edit_account);
				update_option('phoen-endpoint-edit-address', $edit_address);
		}


if(isset($_POST['add']))
		{
			$title = $_POST['phoen-new-endpoint'];

			$slug = strtolower( $title );
			$slug = trim( $slug );
			// clear from space and add -
			$slug = preg_replace( '/[^a-z]/', '-', $slug );


			$endpoint_name = trim($title);
			//$endpoint_name = ucfirst( $endpoint_name );

			$endpoint_order = $_POST['endpoints-order'].','.$slug;

			$arg =array(
						'active'=>'1',
						'slug'=>$slug,
						'label'=>$endpoint_name,
						'icon'=>'',
						'content'=>''
					);

			update_option('phoen-endpoint-'.$slug.'', $arg);
			$check = update_option('phoen-endpoint', $endpoint_order);

		if($check ==1)
			{
			?>

				<div class="updated" id="message">

					<p><strong>Add menu successfully</strong></p>

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

		if(isset($_POST['submit']))
		{

		//print_r($_POST);

		$data = get_option('phoen-endpoint');
			 //print_r($data);

		$endpoint = explode(',',$data);
		foreach($endpoint as $point)
		{
			/*echo '<pre>';
			print_r($_POST["phoen-endpoint-".$point.""]);
			echo '</pre>';*/

			 $value = $_POST["phoen-endpoint-".$point.""];

			$check = update_option('phoen-endpoint-'.$point.'', $value);
		}
			$endpoints = $_POST['endpoints-order'];
			 update_option('phoen-endpoint',$endpoints);


			?>

				<div class="updated" id="message">

					<p><strong>Menus updated.</strong></p>

				</div>

			<?php


		}





			?>
			<form method="post" action="">

				<table class="form-table" style="background:#fff;">
					<tbody>
						<h3> Menu Options</h3>
						<tr valign="top">
						<th>
						<h3>Manage Menus</h3>
						</th>
							<td class="phoen-endpoint-container">
								<ul class="endpoints ui-sortable" id="sortable">





			<?php

			$data = get_option('phoen-endpoint');
			 //print_r($data);

			 $endpoint = explode(',',$data);


			foreach($endpoint as $ep)
			 {


				$row = get_option('phoen-endpoint-'.$ep.'');



			?>



								<li style="border: 1px solid #dfdfdf;" class="endpoint ui-sortable-handle ui-state-default">
									<span style="float:left;"><input type="checkbox" <?php if($row['active']==1){echo 'checked';}?> value="1" name="phoen-endpoint-<?php echo $ep.'[active]'; ?>"></span>
									<div class="header" >
									<label for="phoen-endpoint-<?php echo $ep.'-active';?>"><?php echo ucfirst($row['label']);?></label>
									</div>
									<div class="options" style="display:none;">

									<?php if($ep!='view-orders' && $ep!='edit-account' && $ep!='edit-address'){?>
										<div class="options-row">
											<a class="remove-link" data-endpoint="<?php echo $ep?>">Remove</a>
										</div>
									<?php } ?>
									<table class="form-table">
										<tbody>
										<tr>
										<?php if($ep!="dashboard"){?>
											<tr>
												<th>Slug</th>
												<td><input type="text" name="phoen-endpoint-<?php echo $ep.'[slug]'; ?>" value="<?php echo strtolower($row['slug']); ?>"></td>

											</tr>
										<?php } ?>
											<tr>
												<th>Label</th>
												<td><input type="text" name="phoen-endpoint-<?php echo $ep.'[label]'; ?>" value="<?php echo ucfirst($row['label']); ?>"></td>
											</tr>
											<tr>
											<th>icon</th>


											<td><select class="phoen-selectbox" style="font-family:'FontAwesome'" name="phoen-endpoint-<?php echo $ep.'[icon]'; ?>">
												<optgroup label="Web Application Icons">

															<option value="">Choose icon</option>
															<option value="adjust" <?php if($row['icon']=='adjust'){ echo"selected";}?>> adjust</option>
                                                            <option value="adn" <?php if($row['icon']=='adn'){ echo"selected";}?>> adn</option>
                                                            <option value="align-center" <?php if($row['icon']=='align-center'){ echo"selected";}?>> align-center</option>
                                                            <option value="align-justify" <?php if($row['icon']=='align-justify'){ echo"selected";}?>> align-justify</option>
                                                            <option value="align-left" <?php if($row['icon']=='align-left'){ echo"selected";}?>> align-left</option>
                                                            <option value="align-right" <?php if($row['icon']=='align-right'){ echo"selected";}?>> align-right</option>
                                                            <option value="ambulance" <?php if($row['icon']=='ambulance'){ echo"selected";}?>> ambulance</option>
                                                            <option value="anchor" <?php if($row['icon']=='anchor'){ echo"selected";}?>> anchor</option>
                                                            <option value="android" <?php if($row['icon']=='android'){ echo"selected";}?>> android</option>
                                                            <option value="angellist" <?php if($row['icon']=='angellist'){ echo"selected";}?>> angellist</option>
                                                            <option value="angle-double-down" <?php if($row['icon']=='angle-double-down'){ echo"selected";}?>> angle-double-down</option>
                                                            <option value="angle-double-left" <?php if($row['icon']=='angle-double-left'){ echo"selected";}?>> angle-double-left</option>
                                                            <option value="angle-double-right" <?php if($row['icon']=='angle-double-right'){ echo"selected";}?>> angle-double-right</option>
                                                            <option value="angle-double-up"<?php if($row['icon']=='angle-double-up'){ echo"selected";}?>> angle-double-up</option>
                                                            <option value="angle-down"<?php if($row['icon']=='angle-down'){ echo"selected";}?>> angle-down</option>
                                                            <option value="angle-left"<?php if($row['icon']=='angle-left'){ echo"selected";}?>> angle-left</option>
                                                            <option value="angle-right"<?php if($row['icon']=='angle-right'){ echo"selected";}?>> angle-right</option>
                                                            <option value="angle-up"<?php if($row['icon']=='angle-up'){ echo"selected";}?>> angle-up</option>
                                                            <option value="apple"<?php if($row['icon']=='apple'){ echo"selected";}?>> apple</option>
                                                            <option value="archive"<?php if($row['icon']=='archive'){ echo"selected";}?>> archive</option>
                                                            <option value="area-chart"<?php if($row['icon']=='area-chart'){ echo"selected";}?>> area-chart</option>
                                                            <option value="arrow-circle-down"<?php if($row['icon']=='arrow-circle-down'){ echo"selected";}?>> arrow-circle-down</option>
                                                            <option value="arrow-circle-left"<?php if($row['icon']=='arrow-circle-left'){ echo"selected";}?>> arrow-circle-left</option>
                                                            <option value="arrow-circle-o-down"<?php if($row['icon']=='arrow-circle-o-down'){ echo"selected";}?>> arrow-circle-o-down</option>
                                                            <option value="arrow-circle-o-left"<?php if($row['icon']=='arrow-circle-o-left'){ echo"selected";}?>> arrow-circle-o-left</option>
                                                            <option value="arrow-circle-o-right"<?php if($row['icon']=='arrow-circle-o-right'){ echo"selected";}?>> arrow-circle-o-right</option>
                                                            <option value="arrow-circle-o-up"<?php if($row['icon']=='arrow-circle-o-up'){ echo"selected";}?>> arrow-circle-o-up</option>
                                                            <option value="arrow-circle-right"<?php if($row['icon']=='arrow-circle-right'){ echo"selected";}?>> arrow-circle-right</option>
                                                            <option value="arrow-circle-up"<?php if($row['icon']=='arrow-circle-up'){ echo"selected";}?>> arrow-circle-up</option>
                                                            <option value="arrow-down"<?php if($row['icon']=='arrow-down'){ echo"selected";}?>> arrow-down</option>
                                                            <option value="arrow-left"<?php if($row['icon']=='arrow-left'){ echo"selected";}?>> arrow-left</option>
                                                            <option value="arrow-right"<?php if($row['icon']=='arrow-right'){ echo"selected";}?>> arrow-right</option>
                                                            <option value="arrow-up"<?php if($row['icon']=='arrow-up'){ echo"selected";}?>> arrow-up</option>
                                                            <option value="arrows"<?php if($row['icon']=='arrows'){ echo"selected";}?>> arrows</option>
                                                            <option value="arrows-alt"<?php if($row['icon']=='arrows-alt'){ echo"selected";}?>> arrows-alt</option>
                                                            <option value="arrows-h"<?php if($row['icon']=='arrows-h'){ echo"selected";}?>> arrows-h</option>
                                                            <option value="arrows-v"<?php if($row['icon']=='arrows-v'){ echo"selected";}?>> arrows-v</option>
                                                            <option value="asterisk"<?php if($row['icon']=='asterisk'){ echo"selected";}?>> asterisk</option>
                                                            <option value="at"<?php if($row['icon']=='at'){ echo"selected";}?>> at</option>
                                                            <option value="car"<?php if($row['icon']=='car'){ echo"selected";}?>> car</option>
                                                            <option value="backward"<?php if($row['icon']=='backward'){ echo"selected";}?>> backward</option>
                                                            <option value="ban"<?php if($row['icon']=='ban'){ echo"selected";}?>> ban</option>
                                                            <option value="university"<?php if($row['icon']=='university'){ echo"selected";}?>> university</option>
                                                            <option value="bar-chart-o"<?php if($row['icon']=='bar-chart-o'){ echo"selected";}?>> bar-chart-o</option>
                                                            <option value="barcode"<?php if($row['icon']=='barcode'){ echo"selected";}?>> barcode</option>
                                                            <option value="reorder"<?php if($row['icon']=='reorder'){ echo"selected";}?>> reorder</option>
                                                            <option value="hotel"<?php if($row['icon']=='hotel'){ echo"selected";}?>> hotel</option>
                                                            <option value="beer"<?php if($row['icon']=='beer'){ echo"selected";}?>> beer</option>
                                                            <option value="behance"<?php if($row['icon']=='behance'){ echo"selected";}?>> behance</option>
                                                            <option value="behance-square"<?php if($row['icon']=='behance-square'){ echo"selected";}?>> behance-square</option>
                                                            <option value="bell"<?php if($row['icon']=='bell'){ echo"selected";}?>> bell</option>
                                                            <option value="bell-o"<?php if($row['icon']=='bell-o'){ echo"selected";}?>> bell-o</option>
                                                            <option value="bell-slash"<?php if($row['icon']=='bell-slash'){ echo"selected";}?>> bell-slash</option>
                                                            <option value="bell-slash-o"<?php if($row['icon']=='bell-slash-o'){ echo"selected";}?>> bell-slash-o</option>
                                                            <option value="bicycle"<?php if($row['icon']=='bicycle'){ echo"selected";}?>> bicycle</option>
                                                            <option value="binoculars"<?php if($row['icon']=='binoculars'){ echo"selected";}?>> binoculars</option>
                                                            <option value="birthday-cake"<?php if($row['icon']=='birthday-cake'){ echo"selected";}?>> birthday-cake</option>
                                                            <option value="bitbucket"<?php if($row['icon']=='bitbucket'){ echo"selected";}?>> bitbucket</option>
                                                            <option value="bitbucket-square"<?php if($row['icon']=='bitbucket-square'){ echo"selected";}?>> bitbucket-square</option>
                                                            <option value="btc"<?php if($row['icon']=='btc'){ echo"selected";}?>> btc</option>
                                                            <option value="bold"<?php if($row['icon']=='bold'){ echo"selected";}?>> bold</option>
                                                            <option value="flash"<?php if($row['icon']=='flash'){ echo"selected";}?>> flash</option>
                                                            <option value="bomb"<?php if($row['icon']=='bomb'){ echo"selected";}?>> bomb</option>
                                                            <option value="book"<?php if($row['icon']=='book'){ echo"selected";}?>> book</option>
                                                            <option value="bookmark"<?php if($row['icon']=='bookmark'){ echo"selected";}?>> bookmark</option>
                                                            <option value="bookmark-o"<?php if($row['icon']=='bookmark-o'){ echo"selected";}?>> bookmark-o</option>
                                                            <option value="briefcase"<?php if($row['icon']=='briefcase'){ echo"selected";}?>> briefcase</option>
                                                            <option value="bug"<?php if($row['icon']=='bug'){ echo"selected";}?>> bug</option>
                                                            <option value="building"<?php if($row['icon']=='building'){ echo"selected";}?>> building</option>
                                                            <option value="building-o"<?php if($row['icon']=='building-o'){ echo"selected";}?>> building-o</option>
                                                            <option value="bullhorn"<?php if($row['icon']=='bullhorn'){ echo"selected";}?>> bullhorn</option>
                                                            <option value="bullseye"<?php if($row['icon']=='bullseye'){ echo"selected";}?>> bullseye</option>
                                                            <option value="bus"<?php if($row['icon']=='bus'){ echo"selected";}?>> bus</option>
                                                            <option value="buysellads"<?php if($row['icon']=='buysellads'){ echo"selected";}?>> buysellads</option>
                                                            <option value="taxi"<?php if($row['icon']=='taxi'){ echo"selected";}?>> taxi</option>
                                                            <option value="calculator"<?php if($row['icon']=='calculator'){ echo"selected";}?>> calculator</option>
                                                            <option value="calendar"<?php if($row['icon']=='calendar'){ echo"selected";}?>> calendar</option>
                                                            <option value="calendar-o"<?php if($row['icon']=='calendar-o'){ echo"selected";}?>> calendar-o</option>
                                                            <option value="camera"<?php if($row['icon']=='camera'){ echo"selected";}?>> camera</option>
                                                            <option value="camera-retro"<?php if($row['icon']=='camera-retro'){ echo"selected";}?>> camera-retro</option>
                                                            <option value="caret-down"<?php if($row['icon']=='caret-down'){ echo"selected";}?>> caret-down</option>
                                                            <option value="caret-left"<?php if($row['icon']=='caret-left'){ echo"selected";}?>> caret-left</option>
                                                            <option value="caret-right"<?php if($row['icon']=='caret-right'){ echo"selected";}?>> caret-right</option>
                                                            <option value="toggle-down"<?php if($row['icon']=='toggle-down'){ echo"selected";}?>> toggle-down</option>
                                                            <option value="toggle-left"<?php if($row['icon']=='toggle-left'){ echo"selected";}?>> toggle-left</option>
                                                            <option value="toggle-right"<?php if($row['icon']=='toggle-right'){ echo"selected";}?>> toggle-right</option>
                                                            <option value="toggle-up"<?php if($row['icon']=='toggle-up'){ echo"selected";}?>> toggle-up</option>
                                                            <option value="caret-up"<?php if($row['icon']=='caret-up'){ echo"selected";}?>> caret-up</option>
                                                            <option value="cart-arrow-down"<?php if($row['icon']=='cart-arrow-down'){ echo"selected";}?>> cart-arrow-down</option>
                                                            <option value="cart-plus"<?php if($row['icon']=='cart-plus'){ echo"selected";}?>> cart-plus</option>
                                                            <option value="cc"<?php if($row['icon']=='cc'){ echo"selected";}?>> cc</option>
                                                            <option value="cc-amex"<?php if($row['icon']=='cc-amex'){ echo"selected";}?>> cc-amex</option>
                                                            <option value="cc-discover"<?php if($row['icon']=='cc-discover'){ echo"selected";}?>> cc-discover</option>
                                                            <option value="cc-mastercard"<?php if($row['icon']=='cc-mastercard'){ echo"selected";}?>> cc-mastercard</option>
                                                            <option value="cc-paypal"<?php if($row['icon']=='cc-paypal'){ echo"selected";}?>> cc-paypal</option>
                                                            <option value="cc-stripe"<?php if($row['icon']=='cc-stripe'){ echo"selected";}?>> cc-stripe</option>
                                                            <option value="cc-visa"<?php if($row['icon']=='cc-visa'){ echo"selected";}?>> cc-visa</option>
                                                            <option value="certificate"<?php if($row['icon']=='certificate'){ echo"selected";}?>> certificate</option>
                                                            <option value="link"<?php if($row['icon']=='link'){ echo"selected";}?>> link</option>
                                                            <option value="unlink"<?php if($row['icon']=='unlink'){ echo"selected";}?>> unlink</option>
                                                            <option value="check"<?php if($row['icon']=='check'){ echo"selected";}?>> check</option>
                                                            <option value="check-circle"<?php if($row['icon']=='check-circle'){ echo"selected";}?>> check-circle</option>
                                                            <option value="check-circle-o"<?php if($row['icon']=='check-circle-o'){ echo"selected";}?>> check-circle-o</option>
                                                            <option value="check-square"<?php if($row['icon']=='check-square'){ echo"selected";}?>> check-square</option>
                                                            <option value="check-square-o"<?php if($row['icon']=='check-square-o'){ echo"selected";}?>> check-square-o</option>
                                                            <option value="chevron-circle-down"<?php if($row['icon']=='chevron-circle-down'){ echo"selected";}?>> chevron-circle-down</option>
                                                            <option value="chevron-circle-left"<?php if($row['icon']=='chevron-circle-left'){ echo"selected";}?>> chevron-circle-left</option>
                                                            <option value="chevron-circle-right"<?php if($row['icon']=='chevron-circle-right'){ echo"selected";}?>> chevron-circle-right</option>
                                                            <option value="chevron-circle-up"<?php if($row['icon']=='chevron-circle-up'){ echo"selected";}?>> chevron-circle-up</option>
                                                            <option value="chevron-down"<?php if($row['icon']=='chevron-down'){ echo"selected";}?>> chevron-down</option>
                                                            <option value="chevron-left"<?php if($row['icon']=='chevron-left'){ echo"selected";}?>> chevron-left</option>
                                                            <option value="chevron-right"<?php if($row['icon']=='chevron-right'){ echo"selected";}?>> chevron-right</option>
                                                            <option value="chevron-up"<?php if($row['icon']=='chevron-up'){ echo"selected";}?>> chevron-up</option>
                                                            <option value="child"<?php if($row['icon']=='child'){ echo"selected";}?>> child</option>
                                                            <option value="circle"<?php if($row['icon']=='circle'){ echo"selected";}?>> circle</option>
                                                            <option value="circle-o"<?php if($row['icon']=='circle-o'){ echo"selected";}?>> circle-o</option>
                                                            <option value="circle-o-notch"<?php if($row['icon']=='circle-o-notch'){ echo"selected";}?>> circle-o-notch</option>
                                                            <option value="genderless"<?php if($row['icon']=='genderless'){ echo"selected";}?>> genderless</option>
                                                            <option value="paste"<?php if($row['icon']=='paste'){ echo"selected";}?>> paste</option>
                                                            <option value="clock-o"<?php if($row['icon']=='clock-o'){ echo"selected";}?>> clock-o</option>
                                                            <option value="times"<?php if($row['icon']=='times'){ echo"selected";}?>> times</option>
                                                            <option value="cloud"<?php if($row['icon']=='cloud'){ echo"selected";}?>> cloud</option>
                                                            <option value="cloud-download"<?php if($row['icon']=='cloud-download'){ echo"selected";}?>> cloud-download</option>
                                                            <option value="cloud-upload"<?php if($row['icon']=='cloud-upload'){ echo"selected";}?>> cloud-upload</option>
                                                            <option value="yen"<?php if($row['icon']=='yen'){ echo"selected";}?>> yen</option>
                                                            <option value="code"<?php if($row['icon']=='code'){ echo"selected";}?>> code</option>
                                                            <option value="code-fork"<?php if($row['icon']=='code-fork'){ echo"selected";}?>> code-fork</option>
                                                            <option value="codepen"<?php if($row['icon']=='codepen'){ echo"selected";}?>> codepen</option>
                                                            <option value="coffee"<?php if($row['icon']=='coffee'){ echo"selected";}?>> coffee</option>
                                                            <option value="gear"<?php if($row['icon']=='gear'){ echo"selected";}?>> gear</option>
                                                            <option value="gears"<?php if($row['icon']=='gears'){ echo"selected";}?>> gears</option>
                                                            <option value="columns"<?php if($row['icon']=='columns'){ echo"selected";}?>> columns</option>
                                                            <option value="comment"<?php if($row['icon']=='comment'){ echo"selected";}?>> comment</option>
                                                            <option value="comment-o"<?php if($row['icon']=='comment-o'){ echo"selected";}?>> comment-o</option>
                                                            <option value="comments"<?php if($row['icon']=='comments'){ echo"selected";}?>> comments</option>
                                                            <option value="comments-o"<?php if($row['icon']=='comments-o'){ echo"selected";}?>> comments-o</option>
                                                            <option value="compass"<?php if($row['icon']=='compass'){ echo"selected";}?>> compass</option>
                                                            <option value="compress"<?php if($row['icon']=='compress'){ echo"selected";}?>> compress</option>
                                                            <option value="connectdevelop"<?php if($row['icon']=='connectdevelop'){ echo"selected";}?>> connectdevelop</option>
                                                            <option value="files-o"<?php if($row['icon']=='files-o'){ echo"selected";}?>> files-o</option>
                                                            <option value="copyright"<?php if($row['icon']=='copyright'){ echo"selected";}?>> copyright</option>
                                                            <option value="credit-card"<?php if($row['icon']=='credit-card'){ echo"selected";}?>> credit-card</option>
                                                            <option value="crop"<?php if($row['icon']=='crop'){ echo"selected";}?>> crop</option>
                                                            <option value="crosshairs"<?php if($row['icon']=='crosshairs'){ echo"selected";}?>> crosshairs</option>
                                                            <option value="css3"<?php if($row['icon']=='css3'){ echo"selected";}?>> css3</option>
                                                            <option value="cube"<?php if($row['icon']=='cube'){ echo"selected";}?>> cube</option>
                                                            <option value="cubes"<?php if($row['icon']=='cubes'){ echo"selected";}?>> cubes</option>
                                                            <option value="scissors"<?php if($row['icon']=='scissors'){ echo"selected";}?>> scissors</option>
                                                            <option value="cutlery"<?php if($row['icon']=='cutlery'){ echo"selected";}?>> cutlery</option>
                                                            <option value="tachometer"<?php if($row['icon']=='tachometer'){ echo"selected";}?>> tachometer</option>
                                                            <option value="dashcube"<?php if($row['icon']=='dashcube'){ echo"selected";}?>> dashcube</option>
                                                            <option value="database"<?php if($row['icon']=='database'){ echo"selected";}?>> database</option>
                                                            <option value="outdent"<?php if($row['icon']=='outdent'){ echo"selected";}?>> outdent</option>
                                                            <option value="delicious"<?php if($row['icon']=='delicious'){ echo"selected";}?>> delicious</option>
                                                            <option value="desktop"<?php if($row['icon']=='desktop'){ echo"selected";}?>> desktop</option>
                                                            <option value="deviantart"<?php if($row['icon']=='deviantart'){ echo"selected";}?>> deviantart</option>
                                                            <option value="diamond"<?php if($row['icon']=='diamond'){ echo"selected";}?>> diamond</option>
                                                            <option value="digg"<?php if($row['icon']=='digg'){ echo"selected";}?>> digg</option>
                                                            <option value="usd"<?php if($row['icon']=='usd'){ echo"selected";}?>> usd</option>
                                                            <option value="dot-circle-o"<?php if($row['icon']=='dot-circle-o'){ echo"selected";}?>> dot-circle-o</option>
                                                            <option value="download"<?php if($row['icon']=='download'){ echo"selected";}?>> download</option>
                                                            <option value="dribbble"<?php if($row['icon']=='dribbble'){ echo"selected";}?>> dribbble</option>
                                                            <option value="dropbox"<?php if($row['icon']=='dropbox'){ echo"selected";}?>> dropbox</option>
                                                            <option value="drupal"<?php if($row['icon']=='drupal'){ echo"selected";}?>> drupal</option>
                                                             <option value="file-text-o"<?php if($row['icon']=='file-text-o'){ echo"selected";}?>> file-text-o</option>
															<option value="heart"<?php if($row['icon']=='heart'){ echo"selected";}?>> heart</option>
															<option value="pencil-square-o"<?php if($row['icon']=='pencil-square-o'){ echo"selected";}?>> pencil-square-o</option>
															<option value="user-md"<?php if($row['icon']=='user-md'){ echo"selected";}?>> user-md</option>
                                                            <option value="user-plus"<?php if($row['icon']=='user-plus'){ echo"selected";}?>> user-plus</option>
                                                            <option value="user-secret"<?php if($row['icon']=='user-secret'){ echo"selected";}?>> user-secret</option>
                                                            <option value="user-times"<?php if($row['icon']=='user-times'){ echo"selected";}?>> user-times</option>
                                                            <option value="venus"<?php if($row['icon']=='venus'){ echo"selected";}?>> venus</option>
                                                            <option value="venus-double"<?php if($row['icon']=='venus-double'){ echo"selected";}?>> venus-double</option>
                                                            <option value="venus-mars"<?php if($row['icon']=='venus-mars'){ echo"selected";}?>> venus-mars</option>
                                                            <option value="viacoin"<?php if($row['icon']=='viacoin'){ echo"selected";}?>> viacoin</option>
                                                            <option value="video-camera"<?php if($row['icon']=='video-camera'){ echo"selected";}?>> video-camera</option>
                                                            <option value="vimeo-square"<?php if($row['icon']=='vimeo-square'){ echo"selected";}?>> vimeo-square</option>
                                                            <option value="vine"<?php if($row['icon']=='vine'){ echo"selected";}?>> vine</option>
                                                            <option value="vk"<?php if($row['icon']=='vk'){ echo"selected";}?>> vk</option>
                                                            <option value="volume-down"<?php if($row['icon']=='volume-down'){ echo"selected";}?>> volume-down</option>
                                                            <option value="volume-off"<?php if($row['icon']=='volume-off'){ echo"selected";}?>> volume-off</option>
                                                            <option value="volume-up"<?php if($row['icon']=='volume-up'){ echo"selected";}?>> volume-up</option>
                                                            <option value="weixin"<?php if($row['icon']=='weixin'){ echo"selected";}?>> weixin</option>
                                                            <option value="weibo"<?php if($row['icon']=='weibo'){ echo"selected";}?>> weibo</option>
                                                            <option value="whatsapp"<?php if($row['icon']=='whatsapp'){ echo"selected";}?>> whatsapp</option>
                                                            <option value="wheelchair"<?php if($row['icon']=='wheelchair'){ echo"selected";}?>> wheelchair</option>
                                                            <option value="wifi"<?php if($row['icon']=='wifi'){ echo"selected";}?>> wifi</option>
                                                            <option value="windows"<?php if($row['icon']=='windows'){ echo"selected";}?>> windows</option>
                                                            <option value="wordpress"<?php if($row['icon']=='wordpress'){ echo"selected";}?>> wordpress</option>
                                                            <option value="wrench"<?php if($row['icon']=='wrench'){ echo"selected";}?>> wrench</option>
                                                            <option value="xing"<?php if($row['icon']=='xing'){ echo"selected";}?>> xing</option>
                                                            <option value="xing-square"<?php if($row['icon']=='xing-square'){ echo"selected";}?>> xing-square</option>
                                                            <option value="yahoo"<?php if($row['icon']=='yahoo'){ echo"selected";}?>> yahoo</option>
                                                            <option value="yelp"<?php if($row['icon']=='yelp'){ echo"selected";}?>> yelp</option>
                                                            <option value="youtube"<?php if($row['icon']=='youtube'){ echo"selected";}?>> youtube</option>
                                                            <option value="youtube-play"<?php if($row['icon']=='youtube-play'){ echo"selected";}?>> youtube-play</option>
                                                            <option value="youtube-square"<?php if($row['icon']=='youtube-square'){ echo"selected";}?>> youtube-square</option>
											</select>

											</td>
											</tr>
											<th>Custom Content</th>
											<td><?php  $content = $row['content']; $editor_id="phoen-endpoint-".$ep;  $text_area ="phoen-endpoint-".$ep.'[content]'; echo wp_editor( stripcslashes($content), $editor_id, array( 'textarea_name' => $text_area ));?></td>
										</tr>
										</tbody>
									</table>
									</div>
									<input type="hidden" class="phoen-endpoint-order" name="phoen-endpoint-order" value="<?php echo $ep;?>">
								</li>


			<?php } ?>


								<li class="add_endpoint" style="border-style: dashed; height:30px; cursor:pointer; ">

								</li>


								</ul>
								<input type="hidden" name="endpoints-order" class="endpoints-order" name="endpoints-order" value="<?php echo get_option('phoen-endpoint');?>">
							</td>
						</tr>

					</tbody>
				</table>
				<p class="submit"><input type="submit" value="Save changes" class="button button-primary" id="submit" name="submit">&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" onclick="return confirm('If you continue with this action, you will reset all options in this page.\nAre you sure?');" value="Reset Defaults" class="button-secondary" name="phoen_reset">
</p>
			</form>
