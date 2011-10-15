/**
 * JQuery plugin for dynamically adding and removing user groups
 */
(function( $ ){
	var methods = {
		init: function(select) {
			select.wrap('<div class="user-group-container">');
			select.wrap('<div class="user-group-div">');
			select.after('<img src ="images/add_group.png" style="margin-left: 5px;"></img>');
			select.next().click(function(){methods.add(select)});	
		},
		
		add: function(initial) {
			var newSelect = initial.clone().removeAttr('id');
			$(newSelect).children().removeAttr('selected');
			var container = $(initial).parents('div.user-group-container');
			container.append(newSelect);
			newSelect.wrap('<div class="user-group-div">');
			newSelect.after('<img src="images/delete_group.png" style="margin-left: 5px"></img>');
			newSelect.next().click(function(){methods.remove(newSelect.parent())});
		},
		
		remove: function(div) {
			div.remove();
		},
		
		wrapRemove: function(select) {
			select.wrap('<div class="user-group-div">');
			select.after('<img src ="images/delete_group.png" style="margin-left: 5px;"></img>');
			select.next().click(function(){methods.remove(select.parent())});
		}
	};
	
	$.fn.userGroupChoice = function() {
		var first = true;
		return this.each(function(){
			if (first) {
				methods.init($(this));
				first = false;
			} else {
				methods.wrapRemove($(this));
			}
		});
	};
})(jQuery);



(function( $ ){
	var selectedQuestionsCount = 0;
	var container = '';
	
	var methods = {
		_getSelector: function(checkbox) {
			var id = checkbox.name.replace('[enabled]', '');
			return 'input[name^="' +id+ '"]';
		},
		
		setContainer: function(value) {
			container = value;
		},
		
		init: function(input) {
			if (input.checked) {
				selectedQuestionsCount++;
			} else {
				this.disableInputs(input);
			}
		},
		
		enableInputs: function(checkbox) {
			$(this._getSelector(checkbox)).removeAttr('readonly');
		},
		
		disableInputs: function(checkbox) {
			$(this._getSelector(checkbox)).attr('readonly', 'readonly');
		},
		
		updateCount: function()	{
			$(container).text(selectedQuestionsCount);
		}
	};
	
	$.fn.questionSelection = function(settings) {
		if ( typeof settings === 'object') {
			methods.setContainer(settings.container);
			$(this).each(function(){
				methods.init(this);
			});
			methods.updateCount();
			$(this).each(function(){
				$(this).click(function(){
					if (this.checked) {
						methods.enableInputs(this);
						selectedQuestionsCount++;
					} else {
						methods.disableInputs(this);
						selectedQuestionsCount--;
					}
					methods.updateCount();
					$.fn.examPointsCounter('updateTotal');
					return true;
				});
			});
			return $(this);
		}
		return $.error( 'Unsupported function call.');
	};
})(jQuery);

(function( $ ){
	var input = [];
	var totalContainer = '';
	
	var methods = {
		init: function(options) {
			totalContainer = options.container;
			return $(this).each(function(){	
				input.push(this);
				$(this).blur(methods.updateTotal);
			});
		},
		
		updateTotal: function(){
			var total = 0;
			$.each(input, function(key, value){
				//relies on the onQuestionEnable plugin
				if (!value.readOnly) {
					total += +($(this).val());
				}
			});
			$(totalContainer).text(total);
		}
	};
	
	$.fn.examPointsCounter = function(method) {
		if (methods[method]) {
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			return $.error( 'Method ' +  method + ' does not exist');
		}
	};
})(jQuery);

(function( $ ){
	$.fn.allowOnlyDigits = function() {
		var regex = /^[0-9]+$/;
		return $(this).each(function(){
			var input = $(this);
			var lastValue = input.val();
			$(this).keyup(function(){
				if (!$.trim(input.val()) == '') {
					if(!regex.test(input.val())) {
						input.val(lastValue);
						return;
					}
					lastValue = input.val();
				}
			});
			
		});
	};
})(jQuery);