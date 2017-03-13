=== Plugin Name ===
Contributors: frankenstein-uk
Donate link: http://nutmedia.co.uk/nm-contact-forms
Tags: contact form, contact form builder, contact form plugin, contact forms, contact us, feedback form, form, form builder, web form, contacts, contacts shortcode, contact plugin wordpress, easy contact form, simple contact form, form, feedback form  
Requires at least: 3.0.1
Tested up to: 4.4
Stable tag: 1.1.7
License: GPLv2 
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Contact form plugin. NM contact forms allow you simple contact form integration with two built-in anti-spam solutions.
Supports get variable. 

== Description ==

If you want to contact me or find full documentation of NM Contact forms plugin. Please visit  http://nutmedia.co.uk/nm-contact-forms/

I'm freelance developer, who is working from home , therefore support is guaranteed!
Why to choose NM contact forms you may ask.

First off all this contact form plugin is completely free and has no premium plugin bullshit. 

Friendly UI:

	Drag and drop interface that allows to define contact form fields order.
	Plugin allows to turn on/off default styling (off by default). 
	Option to remove donation bar
	Responsive user interface

Control over each field:

	Following field types available:
	
		TEXT
		TEXTAREA
		EMAIL
		SELECT
		CHECKBOXES
		RADIO BUTTONS
		SINGLE FILE UPLOAD
		GET VARIABLE
		SUBMIT
		HONEY POT
		RECAPTCHA
		
	Define extra classes
	Choose place holders
	Choose if field is required
	

Built in anti-spam solutions:

	reCaptcha (requires Google reCaptcha site key and secret)
	Honey Pot 
	
	
GET variable:

	GET variable allows to pass information from URL to the form. For example you create a link/button to contact us page that contains GET variable (http:/example.com/?product=Shampoo), in this case your GET variable name is 'product', so in NM contact form field settings you need to define same GET variable. When user visits Contact Us page, the field will be pre-filled with GET variable value. You can save some time for the users, so they doesn't have to fill information that is already known, and just needs to be sent with form all together.
	
	You can use GET variable with following contact form fields:
	
	Hidden field
	Regular input
	Checkbox
	Select
	Textarea
	
	
If you find any bugs, please report! I will add you to contributors list.

TO DO:

	Custom error messages
	Auto response
	Custom email templates


== Installation ==

1. Upload `/nm-contact-forms/` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Create your contact form
4. Find generated shorcode and paste it where you want your form to show up.

== Frequently Asked Questions ==

= How to create a contact form? =

Install the plugin. After successful install Contact forms button will appear in the sidebar - click on it. This gonna take you to contact forms page. Click on 'Add new' button, enter form name and click 'Add new form'. It will create a contact form below.

Once you have your form created you can start adding wanted fields by clicking 'Add field button'.
Some fields such HoneyPot, reCaptcha, submit are allowed only once, so don't panic if they grey out and become inactive later on.

Don't forget to include Submit field as it's compulsory field, otherwise users won't be able to submit the form.

= What extra classes setting does in NM contact form plugin? =

This option allows to define <li> element class/classes that wraps the field. Defining unique class allows specific field customization in CSS.
You can enter multiple classes separating them with white space.

= How to use GET variable? =

GET variable allows to pass information from URL to the form. For example you create a link/button to contact us page that contains GET variable (http:/example.com/?product=Shampoo), in this case your GET variable name is 'product', so in NM contact form field settings you need to define same GET variable. When user visits Contact Us page, the field will be pre-filled with GET variable value. You can save some time for the users, so they doesn't have to fill information that is already known, and just needs to be sent with form all together.

= What is Honey POT and how to enable it =

HoneyPot is one of anti-spam methods. It creates a hidden field within contact form, that meant to be leaved blank. Bots aren't always so smart, and they fill all fields regardless. That allows code to know, that contact form was filled by bot, and prevent from sending actual message to the receiver.

Pros and Cons:

Doen't impact looks of the contact form, it's hidden, so does not bother users.
This method won't work if spam attack is targeted or bot is super advanced as it's possible to teach bot to leave that field clear.

Works well for low traffic sites, that gets less of spammers interest.

To enable it, just click 'add field' button on the selected contact form, and set field type to HoneyPot.

= What is reCaptcha and how to enable it =

reCaptcha is advanced third party anti-spam solution. It's reliable anti-spam solution provided by Google.

To set up Google reCaptcha on NM contact forms plugin you have to gain 'site key' and 'secret' which you can do by registering your site on  https://www.google.com/recaptcha/ (It's completely free). Once you do that go, to NM Contact forms plugin settings, and enter you 'site-key' and 'secret' into the fields and hit save.

After that go back to the forms page, choose to which form you want to add Google reCaptcha then simply click 'add field' and set field type to reCaptcha.

== Screenshots ==

1. NM Contact forms plugin admin interface
2. NM Contact forms plugin frontend form design with default styling
3. NM Contact forms plugin admin settings
4. NM Contact forms plugin frontend with default styling turned off
5. NM Contact forms plugin received email design

== Changelog ==
= 1.1.7 =
* Admin Layout Change.
* PHP version warning.
* Tag support in subject field.
* Fixed layout errors.
* Donation block amendments.
= 1.1.6 =
* Donation block amendments.
= 1.1.5 =
* Fixed settings page bug.
= 1.1.4 =
* Fixed show labels setting error.
* Added redirect to thank you page url option for each individual form.
= 1.1.3 =
* Fixed php notices if debug mode enabled. Loading CSS and JS only when shortcode is used.
= 1.1.2 =
* Added setting for Google reCaptcha language 
= 1.1.1 =
* Fixed file upload bug
= 1.1.0 =
* Fixed php notices if debug mode enabled
* Implemented form title tags
= 1.0.9 =
* Added admin menu icon
* Menu order number more unique - to avoid possible override
= 1.0.8 =
* Major bug fix - reCaptha secret option wasn't passed correctly.
= 1.0.7 =
* Small update - added setting link to the plugins page
* Bug fix - multiple forms field slug validation conflict
* Bug fix - HTML field was visable on all field types.
= 1.0.6 =
* Extra submit button added to the admin
* New field type 'HTML' added
* Toggle forms setting field button added
= 1.0.5 =
* Major Bug fix - Jquery UI sortable script added, was causing JavaScript error on some websites.
= 1.0.4 =
* Bug fix - Admin layout issue in Firefox
= 1.0.3 =
* Bug fix - Enqueue some scripts to avoid conflicts.
= 1.0.2 =
* Bug fix - Duplicate field ID bug fix. Javascript was preventing from adding new fields to existing forms, if some previous fields were deleted.
= 1.0.1 =
* Bug fix - multiple choices gets converted into string now.
= 1.0 =
* Initial release

