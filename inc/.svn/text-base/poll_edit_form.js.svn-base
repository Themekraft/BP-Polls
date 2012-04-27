jQuery(document).ready(function() {
	
    
    jQuery('input[name=poll_type]').change(function(){
		update_visibility_of_fields();
	})
	
	jQuery('input[name=poll_start_date], input[name=poll_end_date]').datetimepicker({
		//dateFormat: 'dd M yy',
		dateFormat: JS_DATE_FORMAT,
		timeFormat: JS_TIME_FORMAT,
		separator: ' @ ',
		currentText: __e('Now'),
		closeText: __e('Done'),
		timeText: __e('Time'),
		hourText: __e('Hour'),
		minuteText: __e('Minute')
	}).attr("readOnly", true);
	
	//Add time picker to answers
	update_timepickers();
	
	update_visibility_of_fields();
	
	
	jQuery('.new-poll-form').submit(function() {
        
        if( SUBMIT_TYPE == 'full') {
            if( !confirm( __e('Are you sure want to proceed? This action will clear all votes statistic.') )) {
                return false;
            }
        }
        
		jQuery('.new-poll-form input').removeClass('error-field');
		var formIsValid = 1;
		var error_message = '';
		hide_error_message();
		
		if( jQuery('input[name=poll_name]').val() == '') {
			jQuery('input[name=poll_name]').addClass('error-field');
			formIsValid = 0;
			
			error_message += __e('Please enter the name') + '<br/>';
		}
		
		if( jQuery('input[name=poll_start_date]').val() ) {
			var start_date = jQuery('input[name=poll_start_date]').datetimepicker('getDate');
			// Timestamop in PHP style
			var start_timestamp = ( start_date.getTime() - ( start_date.getTimezoneOffset() * 1000*60 ) ) / 1000 ;
			//Updating hidden field
			jQuery('input[name=poll_start]').val( start_timestamp );
			
		} else {
			error_message += __e('Start date can not be empty') + '<br/>';
			jQuery('input[name=poll_start_date]').addClass('error-field');
			formIsValid = 0;
		}
		
		
		if( jQuery('input[name=poll_end_date]').val() ) {
			
            var end_date = jQuery('input[name=poll_end_date]').datetimepicker('getDate');
            if(end_date){
                // Timestamop in PHP style
                var end_timestamp = ( end_date.getTime() - ( end_date.getTimezoneOffset() * 1000*60 ) ) / 1000 ;
                //Updating hidden field
                jQuery('input[name=poll_end]').val( end_timestamp );
            }
			
			

		} 
        
        if( end_timestamp && start_timestamp && end_timestamp <= start_timestamp) {
			error_message += __e('Start date must be less than the end date') + '<br/>';
			jQuery('input[name=poll_end_date]').addClass('error-field');
			jQuery('input[name=poll_start_date]').addClass('error-field');
			formIsValid = 0;
		}
		
		
	   
		var questions = jQuery('.new-poll-form .poll_questions .poll-question-item');
		if(questions.length == 0) {
			error_message += __e('Poll must have at least one question') + '<br/>';
			formIsValid = 0;
		}
		
		if( SUBMIT_TYPE != 'poll_only' ) {
			questions.each(function(i, el) {
				if( jQuery(this).find('.q_name').val() == ''){
					error_message += __e('Missed question text for Question #')+(i+1)+' <br/>';
					formIsValid = 0;
				}

				var answers = jQuery(this).find('.question-answers .question-answer-item');
				if( answers.length < 2 ) {
					if('dating' == jQuery('input[name=poll_type]:checked').val()){
						error_message += __e('Dating Poll must have at least 2 dates') + '<br/>';
					}else{
						error_message += __e('Question #')+(i+1)+' '+__e('must have at least 2 answers')+'<br/>';
					}
					formIsValid = 0;
				} else {
					answers.each(function(j, el) {
						
						if( 'dating' == jQuery('input[name=poll_type]:checked').val() ) {
							var date_start = jQuery(this).find('.answer-date .start').datetimepicker('getDate');
							var date_end = jQuery(this).find('.answer-date .end').datetimepicker('getDate');
							if ( date_start ) {
								var start_timestamp = ( date_start.getTime() - ( date_start.getTimezoneOffset() * 1000*60 ) ) / 1000 ;
							} else {
								error_message += __e('Missed start for date #')+(j+1)+' <br/>';
								formIsValid = 0;
							}
							
							if ( date_end ) {
								var end_timestamp = ( date_end.getTime() - ( date_end.getTimezoneOffset() * 1000*60 ) ) / 1000 ;
							} else {
								error_message += __e('Missed end for date #')+(j+1)+' <br/>';
								formIsValid = 0;
							}
								
							if( start_timestamp > end_timestamp ){
								error_message += __e('Start must be less than end for date #')+(j+1)+' <br/>';
								formIsValid = 0;
							}
							
							
							if( end_timestamp && start_timestamp && start_timestamp < end_timestamp){
								jQuery(this).find('.answer-text').val( start_timestamp+':'+end_timestamp ); 
							}
							
							
						}
						
						if( jQuery(this).find('.answer-text').val() == ''){
							if('dating' == jQuery('input[name=poll_type]:checked').val()){
								error_message += __e('Missed date #') + (j+1) + ' <br/>';
							}else{
								error_message += __e('Missed answer text for Question #')+(i+1)+', '+__e('Answer #')+(j+1)+' <br/>';
							}
							formIsValid = 0;
						}
					})
					
					
				}

			});
		}
		
		
		if( formIsValid ) {
			return true;
		} else {
			show_error_message( error_message );
			return false;
		}
		
	});
	
    
    
    jQuery('.new_category').click(function(){
		show_new_category();
	})
	
	
	
	
	
	jQuery('.add_answer').live('click',function() {
		
		add_answer(jQuery(this).parent());
		/*
		var answer_item = jQuery('.form_prototypes .question-answer-item').clone();
		var question_id = jQuery(this).parent().parent().attr('rel').substr(14);
		
		//Adding question ID to form inputs names
		answer_item.find('.answer-text').attr('name',"questions["+question_id+"][answers][]");
		jQuery(this).parent().find('.question-answers').append(answer_item);
		update_timepickers();
		update_visibility_of_fields();
		*/
		
	})
	
	jQuery('.answer-text').live('keypress',function(e) {
		
		if(e.keyCode == 13) {
			var answers_wrap = jQuery(this).parent().parent().parent();
			add_answer( answers_wrap );
			e.stopPropagation();
			answers_wrap.find('.answer-text:last').focus();
			return false;
		}		
	});

	
	function add_answer( answers_wrap ) {
		var answer_item = jQuery('.form_prototypes .question-answer-item:first').clone();
		var question_id = answers_wrap.parent().attr('rel').substr(14);
		
		//Adding question ID to form inputs names
		answer_item.find('.answer-text').attr('name',"questions["+question_id+"][answers][]");
		answers_wrap.find('.question-answers').append(answer_item);
		update_timepickers();
		update_visibility_of_fields();
	}
	
	jQuery('.add_question').live('click',function() {
		var question_item = jQuery('.form_prototypes .poll-question-item').clone();
		
		var last_question_id = 0; //Deafult is 0
		if( jQuery('.poll_questions .poll-question-item:last').length > 0 ) {
			last_question_id = jQuery('.poll_questions .poll-question-item:last').attr('rel').substr(14);
		}
		
		var question_id =   parseInt(last_question_id) + 1;
		 
		question_item.attr('rel','question_item_' + question_id);
		question_item.find('.q_name').attr('name', "questions["+question_id+ "][name]")
		question_item.find('.q_limit').attr('name', "questions["+question_id+ "][limit]")
		question_item.find('.q_type').attr('name', "questions["+question_id+ "][type]")
		question_item.find('.answer-text').attr('name', "questions["+question_id+ "][answers][]")
		
		jQuery('.poll_questions').append(question_item);
		
		update_timepickers();
		update_visibility_of_fields();
	})
	
	
	jQuery('.remove-answer.question').live('click',function() {
		var val = jQuery(this).parent().find('.answer-text').val();
		if( val != '') {
			if( window.confirm( __e('Are you sure want delete this answer?') ) ) {
				jQuery(this).parent().remove();			
			}
		} else {
			jQuery(this).parent().remove();			
		}
	})
	
	jQuery('.answer-date .remove-answer').live('click',function(){
		var val = jQuery(this).parent().parent().find('.start').val() + jQuery(this).parent().parent().find('.end').val();
		if( val != '') {
			if( window.confirm( __e('Are you sure want delete this date?') ) ) {
				jQuery(this).parent().parent().parent().remove();
			}
		} else {
			jQuery(this).parent().parent().parent().remove();
		}
		
	})
	
	jQuery('.remove_question').live('click',function(){
		if( window.confirm( __e('Are you sure want delete this question?') ) ) {
			jQuery(this).parent().remove();			
		}
	})
	
	jQuery('.poll_questions .poll-question-item .control_type').live('change',function() {
		if( jQuery(this).val() == 'checkbox') {
			jQuery(this).parent().find('.answers_limit').show();
		} else {
			jQuery(this).parent().find('.answers_limit').hide();
		}
	});
	
	
	
	//Category adding
	jQuery('.category_field').keypress(function(e) {
		if(e.keyCode == 13) {
			add_category();
			e.stopPropagation();
			jQuery('input[name=poll_name]').focus();
			jQuery('.category_field').focus();
            return false;
		}		
	});
	
	jQuery('.add_category').click(function(e) {
		add_category();
	});
	
	//Tags adding
	jQuery('.tag_field').keypress(function(e) {
		if(e.keyCode == 13) {
			add_tag();
			e.stopPropagation();
			jQuery('input[name=poll_name]').focus();
			jQuery('.tag_field').focus();
            return false;
		}		
	});
	
	jQuery('.add_tag').click(function(e) {
		add_tag();
	});
	
	jQuery('.delete_tag').live('click',function() {
		jQuery(this).parent().remove();
		UpdateTagsValues();
	});
	
});

var SUBMIT_TYPE = '';
function show_error_message( error_message ) 
{
	if( SUBMIT_TYPE == 'poll_only' ){
		jQuery('.form-errors.poll_only').html(error_message).show()
	}else{
		jQuery('.form-errors.full').html(error_message).show()
	}
}

function hide_error_message() 
{
	jQuery('.form-errors').html('').hide()
}

function setSubmitType(type)
{
    SUBMIT_TYPE = type;
}


function add_category(){
	var cat = jQuery('.category_field').val().trim().toLowerCase();
	if( cat != '' ) {
		var cat_found = 0;
		jQuery('.categories .cat .name').each(function(){
			if( cat == jQuery(this).text().toLowerCase() ) {
				jQuery(this).parent().find('input[type=checkbox]').attr('checked',1);
				cat_found = 1;
			}
		});

		if( cat_found == 1) {
			jQuery('.category_field').val('');	
			return;
		}
		
		jQuery('.categories').prepend( jQuery('.cat_prototype').html().replace('CATNAME','cats[0#'+cat+']') );
		jQuery('.category_field').val('');	
	}
}


function add_tag(){
	var tag = jQuery('.tag_field').val().trim().toLowerCase();
	var tag_found = 0;
	jQuery('.tags .tag .tag_name').each(function(){
		if( tag == jQuery(this).text().toLowerCase() ) {
			tag_found = 1;
		}
	});
	
	if( tag_found == 1) {
		jQuery('.tag_field').val('');
		return;
	}
   
	var tag_reg = /^[^\s].*$/;
	if(tag != '' && tag_reg.test(tag) != false) {
		jQuery('.tags').append( jQuery('.tag_prototype').html().replace('TAGNAME',tag) );
		jQuery('.tag_field').val('');
		UpdateTagsValues();
	}
}

function UpdateTagsValues()
{
	jQuery('.tags_values').val('');
	var tags_list = '';
	
	jQuery('.tags .tag').each(function(i, tag){
		tags_list += jQuery(this).find('.tag_name').text() + '=' + jQuery(this).find('.tag_id').val() + '&';
	})
	
	jQuery('.tags_values').val(tags_list);
	
}


function update_timepickers() 
{	
	jQuery('.poll_questions .poll-question-item .answer-date .start, .poll_questions .poll-question-item .answer-date .end').datetimepicker({
		dateFormat: JS_DATE_FORMAT,
		timeFormat: JS_TIME_FORMAT,
		separator: ' @ ',
		currentText: __e('Now'),
		closeText: __e('Done'),
		timeText: __e('Time'),
		hourText: __e('Hour'),
		minuteText: __e('Minute')
	}).attr("readOnly", true);
	
}

function show_dating_fields() 
{
	//Hide standart inputs and show datepickres
	jQuery('.new-poll-form .poll_questions .poll-question-item .question-answer-item .answer-text').hide();
	jQuery('.new-poll-form .poll_questions .poll-question-item .question-answer-item .answer-date').show();
	
	
	//Show Dates Aswers title
	jQuery('.answers-title .question').hide();
	jQuery('.answers-title .date').show();
	//Show Add Answer button with proper value
	jQuery('.add_answer.question').hide();
	jQuery('.add_answer.date').show();
	
	//Cnahge poll name title
	jQuery('.poll_name_title').text( __e('Date Title'));
	
	
	
	//Remove buttom
	
	jQuery('.remove-answer.question').hide();
	
	
	
	
	//Hide all fileds except dating inputs and title
	jQuery('.poll_questions .poll-question-item > *').hide();
	jQuery('.poll_questions .poll-question-item .question-answers-wrap, .poll-question-item .answers-title').show();
	
	//Set control type - checkbox
	jQuery('.poll_questions .poll-question-item .control_type').val('checkbox')
	
	//Add Class to change padding
	jQuery('.poll_questions .poll-question-item .question-answers-wrap').addClass('dating');
	// Set not null in question fileds
	jQuery('.poll_questions .poll-question-item .q_name').val(' ');
	//Hide button 'Add question'
	jQuery('.add_question').hide();
	
	
}

function show_question_fields() 
{
	
	//Hide  datepickres and show standart inputs
	jQuery('.new-poll-form .poll_questions .poll-question-item .question-answer-item .answer-text').show();
	jQuery('.new-poll-form .poll_questions .poll-question-item .question-answer-item .answer-date').hide();
	
	//Show Aswers title
	jQuery('.answers-title .question').show();
	jQuery('.answers-title .date').hide();
	//Show Add Answer button with proper value
	jQuery('.add_answer.question').show();
	jQuery('.add_answer.date').hide();
	
	//Cnahge poll name title
	jQuery('.poll_name_title').text( __e('Poll Name') );
	
	//Remove buttom
	jQuery('.remove-answer.question').show();
	
	
	
	//Show full questuion form
	jQuery('.poll_questions .poll-question-item > * ').show();
	
	
	jQuery('.poll_questions .poll-question-item .control_type').each(function(){
		if( jQuery(this).val() == 'radio') {
			jQuery(this).parent().find('.answers_limit').hide();
		}
	})
	
	
	
	//Remove class with padding
	jQuery('.poll_questions .poll-question-item .question-answers-wrap ').removeClass('dating');
	
	//Show button Add Question
	jQuery('.add_question').show();
	
}

function remove_questions(){
	jQuery('.poll_questions .poll-question-item:not(:first-child)').remove();
}

function update_visibility_of_fields( type ) 
{
	
	if( typeof(type) == 'undefined' ) {
		type = jQuery('input[name=poll_type]:checked').val();
	}
	
	if (type == 'dating') {
		remove_questions();
		show_dating_fields();
	} else {
		show_question_fields();	
	}
}

function is_dating(){
	var type = jQuery('input[name=poll_type]:checked').val();
	if( type == 'dating') {
		return true;
	} else {
		return false;
	}
}

function update_end_time_field_visibility( is_shown ){
	if( is_shown == 'yes') {
		jQuery('.end_date_wrap').show();
		jQuery('input[name=poll_end_date]').removeClass('empty');
	} else {
		jQuery('.end_date_wrap').hide();
		jQuery('input[name=poll_end_date]').addClass('empty').val('0');
	}
}
		
		
function show_new_category(){
	jQuery('select[name=cat_id]').hide();
	jQuery('.new_category').hide();
	jQuery('input[name=new_category]').show();
	jQuery('.cancel_category').show();
}

function hide_new_category(){
	jQuery('select[name=cat_id]').show();
	jQuery('.new_category').show();
	jQuery('input[name=new_category]').val('').hide();
	jQuery('.cancel_category').hide();
}