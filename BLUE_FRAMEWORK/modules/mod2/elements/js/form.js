$(document).ready(function(){
	input = $('input[name="chk[]"]').clone();
	parent = $('input[name="chk[]"]');
	input.attr('value', 2)
	parent.after(input);
	parent = $('input[name="chk[]"]');
	//input.attr('value', 3)
	parent.after(input);
	parent = $('input[name="chk[]"]');
	//input.attr('value', 4)
	parent.after(input);
	//$('p').find('input[name="chk[]"]');
	//
	input = $('input[name="rad[]"]').clone();
	parent = $('input[name="rad[]"]');
	input.attr('value', 2)
	parent.after(input);
	parent = $('input[name="rad[]"]');
	input.attr('value', 3)
	parent.after(input);
	parent = $('input[name="rad[]"]');
	input.attr('value', 4)
	parent.after(input);
	//
	input = $('input[name="input[]"]').clone();
	parent = $('input[name="input[]"]');
	parent.after(input);
	parent = $('input[name="input[]"]');
	parent.after(input);
});