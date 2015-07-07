# Drupal Tincan LRS
Tincan LRS for Drupal 7

requires 
Services, Views, UUID, Entity API, Entity Reference, and tincan_lrs_fieldtypes modules

tincan_lrs_fieldtypes can be found here: https://github.com/jackrabbithanna/tincan_lrs_fieldtypes

Project aims to create a Tincan 1.0 compliant LRS for Drupal
See API Spec
https://github.com/adlnet/xAPI-Spec/blob/master/xAPI.md

Current status is prototypical and should not be considered for production uses. 
Data structures, Services implementation, and configuration options will likely change. Development is ongoing. 

Current status is a prototype Statement API. 
A working Entity based data structure for statements works, and a prototypical Services resources implementation for statements
is in place.  Has had some testing with direct HTTP GET, PUT requests....
More testing has been done with Articulate package iframes embedded in Drupal nodes, which use CORS POST requests. This works. 

Has no validation, or authentication. Those functionalities are forthcoming. 

The initial goal is to just get something working, and head toward pure Tincan 1.0 compliance and passing of the Tincan Test suites. 

https://github.com/adlnet/xAPI_LRS_Test

Developing by Skvare -- https://skvare.com


