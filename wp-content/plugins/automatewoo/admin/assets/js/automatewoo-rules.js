(function( $, data ) {

    // MODELS

    AW.Rules = Backbone.Model.extend({


        initialize: function() {

            var app = this;
            var ruleOptions = [];

            if ( this.get( 'rawRuleOptions' ) ) {

                // convert rule options from json to models

                _.each( this.get( 'rawRuleOptions' ), function( rawRuleGroup ) {

                    var group = new AW.RuleGroup( app );
                    var rules = [];

                    _.each( rawRuleGroup, function( rawRule ) {

                        var rule = new AW.Rule( group );

                        rule.set('name', rawRule.name );
                        rule.resetOptions();
                        rule.set('compare', rawRule.compare );
                        rule.set('value', rawRule.value );

                        // for objects
                        if ( rawRule.selected ) {
                            rule.set( 'selected', rawRule.selected );
                        }

                        rules.push( rule );
                    });

                    group.set( 'rules', rules );
                    ruleOptions.push( group );

                });

            }

            this.set('ruleOptions', ruleOptions );

            this.resetAvailableRules();
        },


        defaults: function() {
            return {

                allRules: {},
                availableRules: {},

                // array of condition group models
                ruleOptions: []

            };
        },


        resetAvailableRules: function(){
            // calculate available conditions based on the selected trigger

            var trigger = AW.workflow.get('trigger');

            this.set('availableRules', _.filter( this.get('allRules'), function(rule) {
                return trigger && trigger.supplied_data_items.indexOf(rule.data_item) !== -1;
            }));


            // put rules into groups for select
            var groupedRules = {};

            _.each( this.get('availableRules'), function( rule ){

                if ( ! groupedRules[rule.group] ) groupedRules[rule.group] = [];
                groupedRules[rule.group].push(rule);

            });

            this.set( 'groupedRules', groupedRules );
        },


        isRuleAvailable: function( rule_name ) {
            var availableRules = AW.rules.get('availableRules');
            var names = _.pluck( availableRules, 'name' );
            return _.indexOf( names, rule_name ) !== -1;
        },


        clearIncompatibleRules: function() {

            var rulesToRemove = [];

            _.each( AW.rules.get( 'ruleOptions' ), function( ruleGroup ) {
                _.each( ruleGroup.get( 'rules' ), function( rule ) {
                    if ( rule && ! AW.rules.isRuleAvailable( rule.get('name') ) ) {
                        rulesToRemove.push( rule );
                    }
                });
            });

            // clear out of initial loop to avoid index changing issues, when rules are cleared
            _.each( rulesToRemove, function( rule ) {
                rule.clear();
            });
        },



        createGroup: function() {

            var groups = this.get('ruleOptions');

            var group = new AW.RuleGroup( this );
            group.createRule();
            groups.push( group );

            this.set( 'ruleOptions', groups );
            this.trigger('ruleGroupChange');

            return group;
        },


        removeGroup: function( id ) {

            var groups = this.get('ruleOptions');

            // find index - note we cant use _.findIndex due to backwards compatibility
            var index = groups.map( function( group ) {
                return group.id;
            }).indexOf( id );

            groups[index].destroy();
            groups.splice( index, 1 );
            this.set( 'ruleOptions', groups );
            this.trigger('ruleGroupChange');
        }

    });



    AW.Rule = Backbone.Model.extend({

        initialize: function( group ) {
            this.set( 'id', _.uniqueId( 'rule_' ) );
            this.set( 'group', group );

            this.resetOptions();
        },


        getRuleObject: function() {
            return data.allRules[ this.get('name') ];
        },


        resetOptions: function() {

            var name = this.get('name');
            var ruleObject = this.getRuleObject();

            if ( name ) {
                this.set( 'object', ruleObject );
            }
            else {
                this.set( 'object', {} );
            }

            this.set( 'compare', false );
            this.set( 'value', false );

            this.loadSelectOptions();

            return this;
        },



        /**
         * async gather rule select choices, if not already loaded
         */
        loadSelectOptions: function() {

            var self = this;
            var ruleObject = this.getRuleObject();

            if ( ! ruleObject || ruleObject.type !== 'select' || ruleObject.select_choices )
                return this;

            self.set( 'isValueLoading', true );

            $.getJSON( ajaxurl, {
                action: 'aw_get_rule_select_choices',
                rule_name: ruleObject.name
            }, function( response ) {

                if ( ! response.success )
                    return;

                ruleObject.select_choices = response.data.select_choices;

                self.set( 'isValueLoading', false );
                self.set( 'object', ruleObject );
                self.trigger('optionsLoaded');
            });

            return this;
        },


        clear: function() {
            var group = this.get('group');
            group.removeRule( this.id );
        },


        destroy: function() {
            this.trigger('destroy');
        }

    });



    AW.RuleGroup = Backbone.Model.extend({

        initialize: function( app ) {
            this.set( 'id', _.uniqueId('rule_group_') );
            this.set( 'app', app );
            this.set( 'rules', [] );
        },


        createRule: function() {
            var rules = this.get('rules');
            var rule = new AW.Rule( this );
            rules.push( rule );
            this.set( 'rules', rules );
            return rule;
        },


        removeRule: function( id ) {

            var rules = this.get('rules');

            // find rule index - note we cant use _.findIndex due to backwards compatibility
            var index = rules.map( function( rule ) {
                return rule.id;
            }).indexOf( id );

            // if only 1 rule left delete the whole group object
            if ( rules.length > 1 ) {
                rules[index].destroy();
                rules.splice( index, 1 );
                this.set( 'rules', rules );
            }
            else {
                rules[index].destroy(); // destroy the last rule
                this.clear();
            }
        },


        clear: function() {
            var app = this.get('app');
            app.removeGroup( this.id );
        },


        destroy: function() {
            this.trigger('destroy');
        }

    });


    // VIEWS


    AW.RuleView = Backbone.View.extend({

        className: 'aw-rule-row',

        template: wp.template( 'aw-rule' ),

        events: {
            'change .js-rule-select': 'updateName',
            'change .js-rule-compare-field': 'updateCompare',
            'change .js-rule-value-field': 'updateValue',
            'click .js-remove-rule': 'clear'
        },


        initialize: function() {
            this.listenTo( this.model, 'change:id', this.render );
            this.listenTo( this.model, 'change:group', this.render );
            this.listenTo( this.model, 'optionsLoaded', this.render );
            this.listenTo( this.model, 'destroy', this.remove );
        },


        render: function() {

            var self = this;

            self.$el.html( self.template({
                rule: self.model.toJSON(),
                groupedRules: AW.rules.get('groupedRules'),
                fieldNameBase: self.getFieldNameBase()
            }));

            self.$el.find('.js-rule-select').val( self.model.get('name') );

            if ( self.model.get('compare') ) {
                self.$el.find('.js-rule-compare-field').val( self.model.get('compare') );
            }

            if ( self.model.get('value') ) {
                self.$el.find('.js-rule-value-field').val( self.model.get('value') );
            }

            if ( self.model.get('selected') ) {
                self.$el.find('.js-rule-value-field').attr( 'data-selected', self.model.get('selected') );
            }

            AW.initEnhancedSelects();

            return this;
        },


        updateName: function(e) {
            this.model.set( 'name', e.target.value ).resetOptions();
            this.render();
        },


        updateCompare: function(e) {
            this.model.set( 'compare', e.target.value );
        },

        updateValue: function(e) {
            this.model.set( 'value', $(e.target).val() );
        },


        getFieldNameBase: function() {
            var id = this.model.get( 'id' );
            var group = this.model.get( 'group' );
            return 'aw_workflow_data[rule_options]['+group.id+']['+id+']';
        },


        clear: function() {
            this.model.clear();
        }

    });




    AW.RuleGroupView = Backbone.View.extend({

        className: 'aw-rule-group',

        template: wp.template( 'aw-rule-group' ),

        events: {
            'click .js-add-rule': 'addRule'
        },


        initialize: function() {
            this.listenTo( this.model, 'refreshRules', this.refreshRules );
            this.listenTo( this.model, 'change:id', this.refreshRules );
            this.listenTo( this.model, 'destroy', this.remove );
        },


        render: function() {

            var self = this;

            if ( self.model.get('rules').length )
            {
                self.$el.html( self.template( self.model.toJSON() ) );

                self.$el.find('.rules').empty();

                _.each( self.model.get('rules'), function( rule ) {
                    var view = new AW.RuleView({ model: rule } );
                    self.$el.find( '.rules' ).append( view.render().el );
                });
            }

            AW.initEnhancedSelects();

            return this;
        },


        addRule: function() {
            var model = this.model.createRule();
            var view = new AW.RuleView({ model: model } );

            this.$el.find( '.rules').append( view.render().el );

            AW.initEnhancedSelects();

            return this;
        },


        refreshRules: function() {
            _.each(this.model.get('rules'), function( rule ) {
                rule.trigger('change:group');
            });
        },


        clear: function() {
            this.undelegateEvents();
            this.model.clear();
        }

    });



    AW.RulesView = Backbone.View.extend({

        /**
         * Element
         */
        el: $( '#aw-rules-container' ),

        $meta_box: $( '#aw_rules_box' ),

        template: wp.template( 'aw-rules-container' ),

        events: {
            'click .js-add-rule-group': 'addGroup'
        },


        initialize: function(){
            this.listenTo( this.model, 'ruleGroupChange', this.maybeShowEmptyMessage );
            this.listenTo( this.model, 'change:groupedRules', this.refreshRules );

            this.render();
        },


        render: function() {

            var self = this,
                trigger = AW.workflow.get('trigger');

            self.$el.html( self.template({
                app: self,
                trigger: trigger
            }));

            var $groups = self.$el.find( '.aw-rule-groups' );
            var groups = self.model.get('ruleOptions');

            if ( groups.length ) {
                _.each( groups, function( group ){
                    var view = new AW.RuleGroupView({ model: group } );
                    $groups.append( view.render().el );
                });
            }
            else {
                this.addEmptyMessage();
            }


            AW.initEnhancedSelects();

            return this;
        },


        addGroup: function() {
            var model = this.model.createGroup();
            var view = new AW.RuleGroupView({ model: model } );

            this.$el.find( '.aw-rule-groups').append( view.render().el );

            AW.initEnhancedSelects();

            return this;
        },


        maybeShowEmptyMessage: function() {
            if ( this.model.get('ruleOptions').length ) {
                this.removeEmptyMessage();
            }
            else {
                this.addEmptyMessage();
            }
        },

        addEmptyMessage: function() {
            this.$el.find( '.aw-rule-groups' ).html( wp.template( 'aw-rule-groups-empty' ) );
        },


        removeEmptyMessage: function() {
            this.$el.find('.aw-rules-empty-message').remove();
        },


        refreshRules: function() {
            _.each( this.model.get('ruleOptions'), function( group ) {
                group.trigger('refreshRules');
            });
        },


    });


    $(document).ready(function(){

        AW.rules = new AW.Rules({
            allRules: data.allRules,
            rawRuleOptions: data.ruleOptions
        });


        AW.rulesView = new AW.RulesView({
            model: AW.rules
        });

    });



})( jQuery, automatewooWorkflowLocalizeScript );