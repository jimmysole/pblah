// get the creating profile data
function getProfileData(element, button) {
	var profile_data = $(element).html();

	var data = $(element).blur(function() {
		profile_data = $(element).map(function() {
			return this.innerHTML;
		}).get();

	});

	$(button).on('click', function() {
		$.ajax({
			method : "POST",
			url : "/members/profile/create-profile",
			data : {
				display_name : profile_data[0],
				email_address : profile_data[1],
				age : profile_data[2],
				location : profile_data[3],
				bio : profile_data[4]
			},
		}).done(function() {
			alert("Profile saved!");
			location.href = '/members';
		}).fail(function() {
			alert("Error saving profile, please try again");
		});
	});
}