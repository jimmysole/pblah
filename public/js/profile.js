
function getProfile(element, user_id) {
	var id = $('#' + element);

	$.ajax({
		type: "POST",
		url: "/members/get-profile",
		data: { id: user_id }
	}).done(function(data) {
		id.html(data);
	}).fail(function(data) {
		id.html(data);
	});
}