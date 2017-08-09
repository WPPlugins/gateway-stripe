jQuery(function($){
	$.fn.toggleInputError = function(err){
			if(err){
				$(this).addClass('stripe-field-invalid').removeClass('stripe-field-valid').removeClass('input-field-focus');
			}else{
				$(this).addClass('stripe-field-valid').removeClass('stripe-field-invalid').addClass('input-field-focus');
			}
		};
		$.fn.removeInputError = function(){
			$(this).removeClass('stripe-field-invalid');
		}
		
		$.fieldValidation = {
				fn: {},
				cardFields: {}
		};
		
		$.fieldValidation.fn.setCardField = function(){
			$.fieldValidation.cardFields[$(this).attr('id')] = {
				isValid: true
			}
		}
		
		$.fn.fieldValidation = function(){
			var method = arguments[0];
			$.fieldValidation.fn[method].apply(this);
			
		}
		
		/*Add focus class to fields that are in focus*/
		$.fieldValidation.fn.fieldFocused = function(e){
			$(this).on('focus', function(e){
				var target = $(e.currentTarget);
				var id = target.attr('id');
				if($.fieldValidation.cardFields[id].isValid){
					target.addClass('input-field-focus');
				}else{
					target.toggleInputError(true);
				}
			})
		}
		
		$.fieldValidation.fn.fieldBlur = function(e){
			$(this).on('blur', function(e){
				var target = $(e.currentTarget);
				target.removeClass('input-field-focus');
			})
		}
		
		$.fieldValidation.fn.validateCard = function(e){
			$(this).on('keyup', function(e){
				var $target, card;
				$target = $(e.currentTarget);
				var cardType = $.payment.cardType($target.val());
				if(cardType){
					$.each($.payment.cards, function(i){
						var current_card = $.payment.cards[i];
						if(cardType === current_card['type']){
							var length = $target.val().replace(/\s/g, '').length;
							if(current_card.length.indexOf(length) != -1){
								var isValid = $.payment.validateCardNumber($target.val());
								if(isValid){
									$.fieldValidation.cardFields[$target.attr('id')]['isValid'] = true;
								}else{
									$.fieldValidation.cardFields[$target.attr('id')]['isValid'] = false;
								}
								$target.toggleInputError(!isValid);
							}else{
								$target.toggleInputError(false);
								$.fieldValidation.cardFields[$target.attr('id')]['isValid'] = true;
							}
						}
					})
				}
			});
		}
		
		$.fieldValidation.fn.validateExpMonth = function(e){
			$(this).on('keyup', function(e){
				var isValid = true;
				var target = $(e.currentTarget);
				var month = target.val();
				if(!/^\d+$/.test(month)){
					isValid = false;
				}
				else{
					month = parseInt(month);
					if(month < 1 || month > 12){
						isValid = false;
					}
				}
				$.fieldValidation.cardFields[target.attr('id')]['isValid'] = isValid;
				target.toggleInputError(!isValid);
			})
		}
		
		$.fieldValidation.fn.validateExpYear = function(e){
			$(this).on('keyup', function(e){
				var target, isValid, year, date, currentYear;
				isValid = true;
				target = $(e.currentTarget);
				year = target.val();
				if(year === ''){
					$.fieldValidation.cardFields[target.attr('id')]['isValid'] = true;
					target.toggleInputError(false);
					return;
				}
				if(!/^\d+$/.test(year)){
					isValid = false;
				}else{
					if(year.length < 2 ||year.length == 3){
						$.fieldValidation.cardFields[target.attr('id')]['isValid'] = true;
						target.toggleInputError(false);
						return;
					}
					if(year.length > 4){
						isValid = false;
					}
					date = new Date();
					currentYear = date.getFullYear();
					if(year.length == 2){
						year = parseInt(year);
						currentYear = parseInt(currentYear.toString().substring(2));
						if(year < currentYear){
							isValid = false;
						}
					}else if(year.length == 4){
						year = parseInt(year);
						if(year < currentYear){
							isValid = false;
						}
					}
				}
				
				$.fieldValidation.cardFields[target.attr('id')]['isValid'] = isValid;
				target.toggleInputError(!isValid);
			})
		}
		
		$.fieldValidation.fn.validateExpDate = function(e){
			$(this).on('keyup', function(e){
				var target, isValid, exp;
				target = $(e.currentTarget);
				exp = target.val().replace(/\s/g, '');
				if(/^\d{2}\/(\d{2}){1,2}$/.test(exp)){
					isValid = $.payment.validateCardExpiry($.payment.cardExpiryVal(target.val()));
				}else{
					isValid = true;
				}
				$.fieldValidation.cardFields[target.attr('id')]['isValid'] = isValid;
				target.toggleInputError(!isValid);
			})
		}
});