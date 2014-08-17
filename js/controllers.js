'use strict';

/* Controllers */

angular.module('myApp.controllers', [])
.controller('LoginController', ['$scope', '$http', '$location', function($scope, $http, $location ) {
	$http.get("back/isloggedin.php").success(function() {
		$location.path("/main");
	});

	$scope.login = function() {
		console.log("Loggin in");
		$http.post('back/login.php', $scope.user).success(function(result) {
			$location.path("/main");
		}).error(function(result) {
			$scope.loginfail = true;
		});
	};

	$scope.createuser = function() {
		console.log("Creating user");
		if ($scope.validate()) {
			$http.post('back/createuser.php', $scope.newuser).success(function(result) {
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
	$http.get("back/logout.php").success(function() {
		$scope.loggedout = true;
	});
}])

.controller('MainController', ['$scope', '$http', '$location', function($scope, $http, $location){
	$http.get("back/isloggedin.php").error(function() {
		$location.path("/login");
	});

	function listDocuments() {
		$http.get("back/listdocuments.php").success(function(response) {
			if (response.message.documentList.length == 0) {
				$scope.nodocuments = true;
			} else {
				$scope.nodocuments = false;
			}
			$scope.documents = response.message;
			$scope.documentsfail = false;
		}).error(function(result) {
			if (result.code == 403) {
				$location.path("/login");
			} else {
				$scope.documentsfail = true;
				$scope.documentsmessage = result.message;
			}
		});

	};

	$scope.createdocument = function(edit) {
		if (typeof(edit) === "undefined") edit = false;
		var url = "back/createdocument.php?name="+$scope.document.name;
		$http.get(url).success(function() {
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
	$http.get("back/isloggedin.php").error(function() {
		$location.path("/login");
	});

	// get route parameters
	var group = $routeParams.group;
	var name = $routeParams.name;

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

		var url = HOSTURL + "/pdf/";
		url += group + "/";
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
		$http.post("back/compile.php", {"name": name, "group": group}).success(function(result) {
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
	// files from "back/listfiles.php?d=padname"
	function refreshFiletable() {
		$http.post("back/listfiles.php", {"name": name, "groupid": group}).success(function(result) {
			// set the files list
			$scope.files = result.message;
		}).error(function(result) {
		});
	}

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
	});

	// to download a file
	$scope.download = function(file) {
		$http.post("back/downloadfile.php", {
			"documentname": name, "file": file, "groupid": group
		}).success(function(result) {
		}).error(function(result) {
		});
	};

	// to rename a file
	$scope.rename = function(file) {
		var newname = prompt("New name of '"+file+"'");
		$http.post("back/renamefile.php", {
			"documentname": name, "file": file,
			"newfilename": newname, "groupid": group
		}).success(function(result) {
		}).error(function(result) {
		});
	};

	// to delete a file
	$scope.delete = function(file) {
		if (confirm("Are you sure you want to delete '"+file+"'?")) {
			$http.post("back/deletefile.php", {
				"documentname": name, "file": file, "groupid": group
			}).success(function(result) {
			}).error(function(result) {
			});
		}
	};

	// hide the log by default
	$scope.log = {"show": false};

	// set urls for the iframes
	var etherurl = getPadUrl(name);
	var pdfurl = getPdfUrl(name);
	var pdfview = getPdfView(pdfurl);
	$scope.url = {"ether": etherurl, "pdf": pdfurl, "pdfview": pdfview};

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
	$http.get("back/isloggedin.php").error(function() {
		$location.path("/login");
	});
}]);
