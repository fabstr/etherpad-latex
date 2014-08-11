function validateForm() {
	if ($("#usernameCreate").val() == "") {
		alert("No username");
	} else if ($("#password1").val() == "") {
		alert("No password");
	} else if ($("#password1").val() != $("#password2").val()) {
		alert("The passwords do not match");
	} else {
		return true;
	} 
	return false;
}
