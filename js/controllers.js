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
		$http.post('rest/documents', data).success(function(reply) {
			if (edit == true) {
				var group = reply.group;
				var id = reply.id;
				var path = "/edit/"+group+"/"+id;
				$location.path(path);
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

	$scope.changeDocumentName = function(documentid, newname) {
		$http.post('rest/documents/'+documentid+'/name', {
			newname: newname
		}).success(function() {
			listDocuments();
		}).error(function(result) {
			alert('Could not change name.');
		});
	};

	$scope.changeDocumentGroup = function(documentid, newgroupid) {
		$http.post('rest/documents/'+documentid+'/group', {
			groupid: newgroupid
		}).success(function() {
			listDocuments();
		}).error(function(result) {
			alert('Could not change group.');
		});
	};

	$scope.removeDocument = function(documentid, name) {
		var str = 'Are you sure you want to delete "' + name + '"?';
		str += ' This cannot be undone.';
		if (confirm(str)) {
			$http({
				method: 'DELETE',
				url: 'rest/documents/'+documentid
			}).success(function() {
				listDocuments();
			}).error(function(result) {
				alert('Could not remove document.');
			});
		}
	}

	// get the user's groups
	$http.get('rest/groups').success(function(result) {
		$scope.groups = result;
	});

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

	function getTexUrl(name) {
		var url = HOSTURL + "/rest/tex/download/" + name + ".tex";
		return url;
	}

	function getPdfView(pdfurl) {
		return "pdfjs/web/viewer.html?file="+pdfurl;
	}

	// compile the current document by calling compile.php with the name
	// and group id as post paramers
	function compile() {
		$("html, body").css("cursor", "wait");
		$("#loading").show();
		$http.post("rest/documents/"+name+"/compile", {"documentid": name, "group": group}).success(function(result) {
			$("html, body").css("cursor", "auto");
			// there was no error, refresh the pdf iframe
			$("#pdfview").attr("src", getPdfView(getPdfUrl(name)));
			$scope.log.show = false;
			$("#loading").hide();
		}).error(function(result, statuscode) {
			if (statuscode == 409) {
				// the document is locked
				alert('The document is already compiling, please try again in a while.');
				return;
			}

			$("html, body").css("cursor", "auto");
			// there is an error, show the log-div and set the 
			// message
			$scope.log.show = true;
			$scope.log.msg = result.message.replace(/\\n/g, '\n');
			$("#loading").hide();
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

	// get the user's snippets
	function getSnippets() {
		$http.get('rest/snippets').success(function(snippets) {
			$scope.snippets = snippets;
		}).error(function(result) {
			console.log(result);
		});
	};

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
		$("#filebox").dialog({
			width: 350,
			height: 500
		});
		$("#fileupload").hide();
	});


	// refresh the snippet list and show the snippet dialog box
	$("#snippetslink").click(function() {
		getSnippets();
		$("#snippetbox").dialog({
			width: 350,
			height: 500
		});
	});

	// set a link to download the pdf and tex
	$("#downloadLink").attr('href', getPdfUrl(name, true));
	$("#downloadTexLink").attr('href', getTexUrl(name));

	// set ta link to view the pdf 
	$("#viewLink").attr('href', getPdfUrl(name, false));
	$("#viewLink").attr('target', '_blank');


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

	// to refresh the snippets from angular click
	$scope.refreshSnippets = function() {
		getSnippets();
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

	// when the view is loaded the edit menu should be shown and the 
	// filebox and the snippetbox be hidden
	$("#editMenu").show();
	$("#filebox").hide();
	$("#snippetbox").hide();

	// want to compile when the view is loaded
	compile();
}])

.controller('ManageGroupsController', ['$scope', '$http', '$location', function($scope, $http, $location) {
	$http.get("rest/isloggedin").error(function() {
		$location.path("/login");
	});

	// list the user's groups
	function listGroups() {
		$http.get('rest/groups').success(function(groups) {
			$scope.groups = groups;
		}).error(function(result) {
			alert('Could not get groups: ' + JSON.stringify(result));
		});
	}

	// remove a group
	$scope.removeGroup = function (groupid) {
		$http({
			method: 'DELETE',
			url: 'rest/groups/'+groupid
		}).success(function() {
			listGroups();
		}).error(function(result) {
			alert('Could not remove group.');
		});
	}


	$scope.addgroup = function(groupname) {
		$http.post('rest/groups', {
			groupname: groupname
		}).success(function() {
			// update the group list
			listGroups();
		}).error(function(result) {
			alert('Could not add group');
		});
	};

	// list the groups now
	listGroups();
}])

.controller('GroupDetailsController', ['$scope', '$http', '$location', '$routeParams', function($scope, $http, $location, $routeParams) {
	$http.get("rest/isloggedin").error(function() {
		$location.path("/login");
	});

	// get the groupid 
	var groupid = $routeParams.groupid;

	// get the details for the group
	function getDetails() {
		$http.get('rest/groups/'+groupid).success(function(result) {
			$scope.groupname = result.groupname;
			$scope.owner = result.owner;
			$scope.members = result.members;
			$scope.documents = result.documents;
		}).error(function(result) {
			alert('Could not get group details: ' + JSON.stringify(result));
		});
	}

	// get the details now
	getDetails();

	$scope.adduser = function(username) {
		$http.post('rest/groups/'+groupid, {username: username}).success(function(){
			getDetails();
		}).error(function(result) {
			alert('Could not add user to group: ' + JSON.stringify(result));
		});
	};

	$scope.removeUser = function(userid) {
		$http({
			method: 'DELETE',
			url: 'rest/groups/'+groupid+'/'+userid
		}).success(function() {
			getDetails();
		}).error(function(result) {
		});
	};
}])


.controller('SnippetsController', ['$scope', '$http', '$location', function($scope, $http, $location, $routeParams){
	$http.get('rest/isloggedin').error(function() {
		$location.path('/login');
	});

	// to get the snippets
	function listSnippets() {
		$http.get('rest/snippets').success(function(snippets) {
			$scope.snippets = snippets;
		}).error(function(result) {
			console.log(result);
			alert('Could not get snippets');
		});
	}

	// to add a snippet
	$scope.createSnippet = function(snippet) {
		$http.post('rest/snippets', snippet).success(function() {
			listSnippets();
		}).error(function(result) {
			console.log(result);
			alert('Could not save snippet');
		});
	};


	// to save/change a snippet
	$scope.saveSnippet = function(snippet) {
		$http.post('rest/snippets/' + snippet.id, snippet).success(function() {
			listSnippets();
		}).error(function(result) {
			console.log(result);
			alert('Could not save the changes to the snippet');
		});
	};

	// revert changes to a snippet by getting the old one from the server
	$scope.revertSnippet = function(snippet) {
		$http.get('rest/snippets/'+snippet.id).success(function(realsnippet) {
			$scope.snippets.forEach(function(s) {
				if (s.id == realsnippet.id) {
					s.snippetname = realsnippet.snippetname;
					s.content = realsnippet.content;
				}
			});
			//snippet = realsnippet;
			//snippet.snippetnamename = realsnippet.snippetnamename;
			//snippet.content = realsnippet.content;
		}).error(function(result) {
			console.log(result);
			alert('Could not get the old snippet');
		});
	};

	// remove a snippet
	$scope.removeSnippet = function(snippet) {
		var str = 'Are you sure you want to remove "' + snippet.snippetname;
		str += '"? This cannot be undone.';
		if (confirm(str)) {
			$http({
				method: 'DELETE',
				url: 'rest/snippets/'+snippet.id
			}).success(function() {
				listSnippets();
			}).error(function(result) {
				console.log(result);
				alert('Could not remove the snippet');
			});
		}
	};

	// list the snippets now
	listSnippets();
}]);

