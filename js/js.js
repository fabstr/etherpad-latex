var ETHERPADHOST = "https://tallr.se/etherpad";
var etherpadsettings = {
	useMonospaceFont: true,
	noColors: true,
	showControls: false,
	showChat: false
};

function viewPDF() {
	$("#pdfview").attr("src", "");
	var doc = $("#pad").val();
	var src = "Viewer.js/#../pdf.php?p="+doc+".pdf";

	// for some reason this is neccessary
	setTimeout(function() {
		$("#pdfview").attr("src", src);
	}, 100);
}

function viewEtherpad() {
	var padname = $("#pad").val();

	// get the url to the pad with the GET parameters
	var src = ETHERPADHOST + "/p/" + padname + "?";
	var getstring = "";
	for (var key in etherpadsettings) {
		getstring += key + "=" + etherpadsettings[key] + "&";
	}
	src += getstring;

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

function removeFile(name) {
	if (confirm("Do you want to remove " + name + "?")) {
		$.ajax({
			url: "removefile.php?d="+$("#pad").val(),
			type: "POST",
			data: {file: name},
			success: refreshFiletable(),
			error: function() {
				alert("Could not remove " + name + ".");
			}
		});

	}
}

function renameFile(name) {
	var newname = prompt("New filename for " + name + ":");
	$.ajax({
		url: "renamefile.php?d="+$("#pad").val(),
		type: "POST",
		data: {file: name, newname: newname},
		success: refreshFiletable(),
		error: function(result) {
			alert("Could not rename " + name + ":" + JSON.stringify(result));
		} });
}

function refreshFiletable() {
	$("#filetable").html("<thead><th>Filename</th><th colspan=\"2\">Actions</th></thead>");
	var doc = $("#pad").val();
	$.ajax({url: "listfiles.php?d="+doc}).done(function(data) {
		var result = JSON.parse(data);
		if (result.length == 0) {
			$("#filetable").append("<tr><td colspan=\"3\">There are no files.</td></tr>");
		}
		result.forEach(function(e) {
			var html = "";
			html += "<tr>";
			html += "<td><a href=\"downloadfile.php?d="+doc+"&file="+e.name+"\">"+e.name+"</a></td>";
			html += "<td><a href=\"#\" onclick=\"renameFile('"+e.name+"');\">Rename</a></td>";
			html += "<td><a href=\"#\" onclick=\"removeFile('"+e.name+"');\">Remove</a></td>";
			html += "</tr>";
			$("#filetable").append(html);
		});
	});;
}

$(document).ready(function () {
	// hide link, pdf and log
	$("#link").hide();
	$("#pdfview").hide();
	$("#log").hide();
	$("#filebox").hide();
	$("#fileupload").hide();

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

	$("#fileuploadbtn").click(function() {
		var formdata = new FormData($("#fileform")[0]);
		$.ajax({
			url: "upload.php?d="+$("#pad").val(),
			type: "POST",
			data: formdata,
			contentType: false,
			processData: false,
			xhr: function() {
				var xhr = $.ajaxSettings.xhr();
				if (xhr.upload) {
					xhr.upload.addEventListener("progress", function(e){
						if (e.lengthComputable) {
							$("#fileupload").attr({value: e.loaded, max: e.total});
						}
					}, false);
				}
				return xhr;
			},
			success: function (result) {
				refreshFiletable();
			},
			error: function(result) {
				alert("Error: " + JSON.stringify(result));
			},
			beforeSend: function() {
				$("#fileupload").show();
			},
			complete: function() {
				$("#fileupload").hide();
			}
		});
	});

	$("#files").click(function() {
		if (!$("#filebox").is(":visible")) {
			refreshFiletable();
		}
		$("#filebox").dialog();
	});

	openDocument();
});
