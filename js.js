function viewPDF() {
	$("#pdfview").attr("src", "");
	var doc = $("#pad").val();
	var src = "Viewer.js/#../pdf.php?p="+doc+".pdf";

	// for some reason this is neccessary
	setTimeout(function() {
		$("#pdfview").attr("src", src);
	}, 1);
}

function viewEtherpad() {
	var padname = $("#pad").val();
	var src = "https://tallr.se/etherpad/p/"+padname+"?useMonospaceFont=true&noColors=true";
	$("#etherpad").attr("src", src);
}

function handleCompileFailure(response) {
	if (response.errno == 1) {
		// the pad is missing, wait a second for it to be created
		setTimeout(function() {
			compile();
			viewPDF();
		}, 1000);
	} else {
		// write log message
		var str = response.message;
		$("#status").append("Failure!");
		$("#log").html(str.replace("\n", "<br>"));

		// hide the link and the pdf, show the log
		$("#link").hide();
		$("#pdfview").hide();
		$("#log").show();
	}
}

function handleCompileSuccess(response) {
	// write status message and hide log
	$("#status").append("Success!");
	$("#log").hide();

	// update the link
	$("#link").attr("href", "pdf.php?p=" + response.message + ".pdf");

	// show the link and the pdf
	$("#link").show();
	$("#pdfview").show();
}

function compile() {
	$("#status").html("");
	$("#status").append("Compiling latex, this may take a while... ");
	$.ajax({
		url: "etherpad_latex.php",
		data: {
			"document": $("#pad").val()
		},
		success: function (data) {
			var response = JSON.parse(data);
			if (response.result == "failure") {
				handleCompileFailure(response);
			} else {
				handleCompileSuccess(response);
			}
		}
	});
}

function openDocument()
{
	// get the name of the document
	var doc = $("#pad").val();

	// create a regex to check the name against
	var re = /^\w+$/;

	// check the name is valid
	if (!doc.match(re)) {
		alert("The document name must only be letters, digits or underscore (_).");
	} else {
		// make sure the pdf exists
		compile();

		// update the pad 
		viewEtherpad();

		// update the viewer
		viewPDF();
	}
}

$(document).ready(function () {
	// hide link, pdf and log
	$("#link").hide();
	$("#pdfview").hide();
	$("#log").hide();

	$("#compilepdf").click(function() {
		compile();
		viewPDF();
		return false;
	});

	$("#opendoc").click(function() {
		var doc = $("#pad").val();
		window.location.assign("edit.php?d="+doc);
		return false;
	});

	openDocument();
});
