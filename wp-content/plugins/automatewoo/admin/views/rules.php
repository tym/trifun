<?php
/**
 * @var $workflow AW_Model_Workflow
 */
?>


<div id="aw-rules-container"></div>




<script type="text/template" id="tmpl-aw-rules-container">

	<div class="aw-rules-container">
		<div class="aw-rule-groups"></div>
	</div>

	<div class="automatewoo-metabox-footer">
		<button type="button" class="js-add-rule-group button button-primary button-large"><?php _e('+ Add Rule Group', 'automatewoo') ?></button>
	</div>

</script>



<script type="text/template" id="tmpl-aw-rule-groups-empty">
	<p class="aw-rules-empty-message"><?php printf( esc_attr__( 'Rules can be used to add conditional logic to workflows. Click the %s+ Add Rule Group%s button to create a rule.', 'automatewoo'), '<strong>', '</strong>' )  ?></p>
</script>


<script type="text/template" id="tmpl-aw-rule">

	<div class="aw-rule-fields">

		<div class="aw-rule-select-container aw-rule-field-container">
			<select name="{{ data.fieldNameBase }}[name]" class="js-rule-select aw-field" required>

				<option value="">- Choose rule -</option>
				<# _.each( data.groupedRules, function( rules, group_name ) { #>
					<optgroup label="{{ group_name }}">
						<# _.each( rules, function( rule ) { #>
							<option value="{{ rule.name }}">{{ rule.title }}</option>
						<# }) #>
					</optgroup>
				<# }) #>
			</select>
		</div>


		<div class="aw-rule-field-compare aw-rule-field-container">
			<select name="{{ data.fieldNameBase }}[compare]" class="aw-field js-rule-compare-field" <# if ( _.isEmpty( data.rule.object.compare_types ) ) { #>disabled<# } #>>

				<# _.each( data.rule.object.compare_types, function( option, key ) { #>
					<option value="{{ key }}">{{ option }}</option>
				<# }) #>

			</select>
		</div>


		<div class="aw-rule-field-value aw-rule-field-container <# if ( data.rule.isValueLoading ) { #>aw-loading<# } #>">

			<# if ( data.rule.isValueLoading ) { #>

				<div class="aw-loader"></div>

			<# } else { #>


				<# if ( data.rule.object.type === 'number' ) { #>

					<input name="{{ data.fieldNameBase }}[value]" class="aw-field js-rule-value-field" type="number" required>

				<# } else if ( data.rule.object.type === 'object' ) { #>

					<input name="{{ data.fieldNameBase }}[value]"
							 type="hidden"
							 class="{{ data.rule.object.class }} aw-field js-rule-value-field"
							 data-placeholder="{{ data.rule.object.placeholder }}"
							 data-action="{{ data.rule.object.ajax_action }}"
							 data-multiple="false">

				<# } else if ( data.rule.object.type === 'select' ) { #>

					<select name="{{ data.fieldNameBase }}[value][]" multiple="multiple" class="aw-field wc-enhanced-select js-rule-value-field">

						<# _.each( data.rule.object.select_choices, function( option, key ) { #>
							<option value="{{ key }}">{{ option }}</option>
						<# }) #>

					</select>

				<# } else if ( data.rule.object.type === 'string' )  { #>

					<input name="{{ data.fieldNameBase }}[value]" class="aw-field js-rule-value-field" type="text" required>

				<# } else if ( data.rule.object.type === 'bool' )  { #>

					<select name="{{ data.fieldNameBase }}[value]" class="aw-field js-rule-value-field">
						<# _.each( data.rule.object.select_choices, function( option, key ) { #>
							<option value="{{ key }}">{{ option }}</option>
							<# }); #>
					</select>

				<# } else { #>

					<input class="aw-field" type="text" disabled>

				<# } #>


			<# } #>



		</div>


	</div>

	<div class="aw-rule-buttons">
		<button type="button" class="js-add-rule aw-rule-add-btn button"><?php esc_attr_e( 'and', 'automatewoo')  ?></button>
		<button type="button" class="js-remove-rule aw-rule-remove-btn"></button>
	</div>

</script>



<script type="text/template" id="tmpl-aw-rule-group">
	<div class="rules"></div>
	<div class="aw-rule-group-or"><span><?php esc_attr_e( 'or', 'automatewoo')  ?></span></div>
</script>
