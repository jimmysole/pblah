
// handle quick edit of name
function quickEditName(element, button) {
	var edited_data;

	var data = $(element).blur(function() {
		edited_data = $(element).html();
	});

	$(button).on('click', function() {
		$.ajax({
			method : "POST",
			url : "/members/profile/change-name",
			data : {
				display_name : edited_data
			}
		}).done(function() {
			// name saved
			// reload the data into the field
			alert("Display name updated")
			$(element).load('/members/profile/get-user-display-name');
		}).fail(function() {
			alert("Error updating display name, please try again.");
		});
	});
}


// handle quick edit of location
function quickEditLocation(element, button) {
	var edited_data;

	var data = $(element).blur(function() {
		edited_data = $(element).html();
	});

	$(button).on('click', function() {
		$.ajax({
			method : "POST",
			url : "/members/profile/change-location",
			data : {
				location : edited_data
			}
		}).done(function() {
			// location saved
			// reload the data into the field
			alert("Location updated");
			$(element).load('/members/profile/get-user-location');
		}).fail(function() {
			alert("Error updating location, please try again.");
		});
	});
}


// handle quick edit of age
function quickEditAge(element, button) {
	var edited_data;

	var data = $(element).blur(function() {
		edited_data = $(element).html();
	});

	$(button).on('click', function() {
		$.ajax({
			method : "POST",
			url : "/members/profile/change-age",
			data : {
				user_age : edited_data
			}
		}).done(function() {
			// location saved
			// reload the data into the field
			alert("Age updated successfully");
			$(element).load('/members/profile/get-user-age');
		}).fail(function() {
			alert("Error updating age, please try again.");
		});
	});
}


// handle quick edit of bio
function quickEditBio(element, button) {
	var edited_data;

	var data = $(element).blur(function() {
		edited_data = $(element).html();
	});

	$(button).on('click', function() {
		$.ajax({
			method : "POST",
			url : "/members/profile/change-bio",
			data : {
				user_bio : edited_data
			}
		}).done(function() {
			// bio saved
			// reload the data into the field
			alert("Bio updated Successfully");
			$(element).load('/members/profile/get-user-bio');
		}).fail(function() {
			alert("Error updating bio, please try again.");
		});
	});
}

