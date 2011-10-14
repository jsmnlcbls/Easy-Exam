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