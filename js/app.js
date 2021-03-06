'use strict';


// Declare app level module which depends on filters, and services
angular.module('myApp', [
  'ngRoute',
  'myApp.filters',
  'myApp.services',
  'myApp.directives',
  'myApp.controllers'
]).
config(['$routeProvider', function($routeProvider) {
  $routeProvider.when('/main', {templateUrl: 'partials/main.html', controller: 'MainController'});
  $routeProvider.when('/login', {templateUrl: 'partials/login.html', controller: 'LoginController'});
  $routeProvider.when('/logout', {templateUrl: 'partials/logout.html', controller: 'LogoutController'});
  $routeProvider.when('/edit/:group/:name', {templateUrl: 'partials/edit.html', controller: 'EditController'});
  $routeProvider.when('/groups', {templateUrl: 'partials/groups.html', controller: 'ManageGroupsController'});
  $routeProvider.when('/groupdetails/:groupid/', {templateUrl: 'partials/groupdetails.html', controller: 'GroupDetailsController'});
  $routeProvider.when('/snippets', {templateUrl: 'partials/snippets.html', controller: 'SnippetsController'});
  $routeProvider.otherwise({redirectTo: '/login'});
}]);
