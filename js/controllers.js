'use strict';

/* Controllers */

angular.module('myApp.controllers', [])
.controller('LoginController', ['$scope', '$http', '$location', function($scope, $http, $location ) {
	$http.get("rest/isloggedin").success(function() {
		$location.path("/main");
	});

	$scope.login = function() {
		console.log("Loggin in");
		$http.post('rest/login', $scope.user).success(function(result) {
			$location.path("/main");
		}).error(function(result) {
			$scope.loginfail = true;
		});
	};

	$scope.createuser = function() {
		console.log("Creating user");
		if ($scope.validate()) {
			$http.post('rest/create', $scope.newuser).success(function(result) {
				$location.path("/main");
			}).error(function(result) {
			});
		}
	};

	$scope.validate = function() {
		if ($scope.newuser.password != $scope.newuser.password2) {
			$scope.wrongsecondpassword = true;
			return false;
		} else {
			$scope.wrongsecondpassword = false;
			return true;
		}
	};
}])
.controller('LogoutController', ['$scope', '$http', function($scope, $http) {
	$http.get("rest/logout").success(function() {
		$scope.loggedout = true;
	});
}])

.controller('MainController', ['$scope', '$http', '$location', function($scope, $http, $location){
	$http.get("rest/isloggedin").error(function() {
		$location.path("/login");
	});

	function listDocuments() {
		$http.get("rest/documents").success(function(response) {
			if (response.length == 0) {
				$scope.nodocuments = true;
			} else {
				$scope.nodocuments = false;
			}
			$scope.documents = response;
			$scope.documentsfail = false;
		}).error(function(result) {
			if (result.code == 403) {
				$location.path("/login");
			} else {
				$scope.documentsfail = true;
				$scope.documentsmessage = "Could not list documents.";
			}
		});

	};

	$scope.createdocument = function(edit) {
		if (typeof(edit) === "undefined") edit = false;
		var data = {'documentname': $scope.document.name};
		$http.post('rest/documents', data).success(function() {
			if (edit == true) {
				$location.path("#edit/"+$scope.document.name);
			} else {
				$scope.document.created = true;
				$scope.document.failure = false;
				listDocuments();

			}
		}).error(function(result) {
			$scope.document.failure = true;
			$scope.document.created = false;
		});
	};

	listDocuments();
}])

.controller('EditController', ['$scope', '$routeParams', '$http', '$location', function($scope, $routeParams, $http, $location) {
	// if the user is not logged in, redirect to login page
	$http.get("rest/isloggedin").error(function() {
		$location.path("/login");
	});

	// get route parameters
	var name = $routeParams.name;
	var group = $routeParams.group;
	$scope.name = name;
	$scope.group = group;

	// return an url
	// "http://host.example.com/p/padname?settings..."
	function getPadUrl(name) {
		var src = ETHERPADHOST + "/p/" + group + "$" + name + "?";
		var getstring = "";
		for (var key in ETHERPADSETTINGS) {
			getstring += key + "=" + ETHERPADSETTINGS[key] + "&";
		}
		src += getstring;
		return src;

	}

	// return an url "HOSTURL/pdf/name.pdf" if download is not set or is false
	// and "HOSTURL/download/pdf/name.pdf" if download is true.
	function getPdfUrl(name, download) {
		if (typeof(download) === "undefined") download = false;

		var url = HOSTURL + "/rest/pdf/";
		if (download == true) {
			url += "download/";
		}
		
		url += name + ".pdf";
		return url;
	}

	function getPdfView(pdfurl) {
		return "pdfjs/web/viewer.html?file="+pdfurl;
	}

	// compile the current document by calling compile.php with the name
	// and group id as post paramers
	function compile() {
		$http.post("rest/documents/compile", {"name": name, "group": group}).success(function(result) {
			// there was no error, refresh the pdf iframe
			$("#pdfview").attr("src", getPdfView(getPdfUrl(name)));
			//$scope.log.show = false;
		}).error(function(result) {
			// there is an error, show the log-div and set the 
			// message
			$scope.log.show = true;
			$scope.log.msg = result;
		});
	};

	// refresh the file list in manageFiles dialog box by getting a list of
	// files from "rest/isloggedin"
	function refreshFiletable() {
		$http.get("rest/files/?documentid="+name).success(function(result) {
			// set the files list
			$scope.files = result;
		}).error(function(result) {
		});
	}

	// to upload a file
	$("#fileuploadbtn").click(function() {
		var formdata = new FormData($("#fileform")[0]);
		$.ajax({
			url: 'rest/files/?documentid='+name,
			type: 'POST',
			data: formdata,
			contentType: false,
			processData: false,
			xhr: function() {
				var xhr = $.ajaxSettings.xhr();
				if (xhr.upload) {
					xhr.upload.addEventListener('progress', function(e) {
						if (e.lengthComputable) {
							$("#fileupload").attr({
								value: e.loaded,
								max: e.total
							});
						}
					}, false);
				}
				return xhr;
			},
			success: function(result) {
				refreshFiletable();
			},
			error: function(result) {
				alert('Error uploading file: ' + JSON.stringify(result));
			},
			beforeSend: function() {
				$("#fileupload").show();
			},
			complete: function() {
				$("#fileupload").hide();
			}

		});
	});

	// compile the document
	$("#compileLink").click(function() {
		compile();
	});

	// refresh the file list and show the dialog box
	$("#managefilesLink").click(function() {
		refreshFiletable();
		$("#filebox").dialog();
		$("#fileupload").hide();
	});

	// create a hidden iframe with getPdfUrl() as src, download the
	// document
	$("#downloadLink").click(function() {
		var url = getPdfUrl(name, true);
	});

	// create a hidden iframe with getPdfUrl() as src, view the
	// document
	$("#viewLink").click(function() {
		var url = getPdfUrl(name, false);
		alert(url);
	});

	// to upload a file
	$scope.upload = function() {
	};

	// to rename a file
	$scope.rename = function(file) {
		var newname = prompt("New name of '"+file+"':");
		$http.post("rest/files/rename?documentid="+name, {
			"file": file,
			"newfile": newname, 
		}).success(function(result) {
			refreshFiletable();
		}).error(function(result) {
			alert('Could not rename file: ' . JSON.stringify(result));
		});
	};

	// to delete a file
	$scope.delete = function(file) {
		if (confirm("Are you sure you want to delete '"+file+"'?")) {
			$http({
				method: 'DELETE',
				url: 'rest/files/'+file+'?documentid='+name
			}).success(function() {
				refreshFiletable();
			}).error(function(result) {
				alert('Could not delete file: ' + JSON.stringify(result));
			});
		}
	};

	// hide the log by default
	$scope.log = {"show": false};

	// get the urls for the iframes
	var etherurl = getPadUrl(name);
	var pdfurl = getPdfUrl(name);
	var pdfview = getPdfView(pdfurl);

	// authenticate the user and if successfull, update the scope with the
	// urls, else show a message with alert
	$http.post('rest/user/createsessions', {'group': group}).success(function() {
		$scope.url = {"ether": etherurl, "pdf": pdfurl, "pdfview": pdfview};
		console.log("setting scope: " + JSON.stringify($scope.url));
	}).error(function(result) {
		alert("You don't have permission to view or edit this document.");
		$location.path('/main');
	});


	// the edit menu should be hidden when the view is left
	$scope.$on('$locationChangeStart', function(event, next, current) {
		$("#editMenu").hide();
	});

	// when the view is loaded the edit menu should be shown and the filebox
	// be hidden
	$("#filebox").hide();
	$("#editMenu").show();

	// want to compile when the view is loaded
	compile();
}])

.controller('ManageGroupsController', ['$scope', '$http', '$location', function($scope, $http, $location) {
	$http.get("rest/isloggedin").error(function() {
		$location.path("/login");
	});
}]);
