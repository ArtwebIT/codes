/*
 * Translated default messages for the jQuery validation plugin.
 * Locale: DE (German, Deutsch)
 */
(function ($) {
	$.extend($.validator.messages, {
		required: "Dieses Feld ist ein Pflichtfeld.",
		maxlength: $.validator.format("Bitte gib maximal {0} Zeichen ein."),
		minlength: $.validator.format("Bitte gib mindestens {0} Zeichen ein."),
		rangelength: $.validator.format("Geben Sie bitte mindestens {0} und maximal {1} Zeichen ein."),
		email: "Bitte gib eine gültige E-Mail Adresse ein.",
		url: "Geben Sie bitte eine gültige URL ein.",
		date: "Bitte gib ein gültiges Datum ein.",
		number: "Bitte gib eine Zahl ein.",
		euro: "Bitte gib einen Betrag mit zwei Nachkommastellen ein.",
		digits: "Bitte gib nur Ziffern ein.",
		equalTo: "Bitte denselben Wert wiederholen.",
		range: $.validator.format("Geben Sie bitte einen Wert zwischen {0} und {1} ein."),
		max: $.validator.format("Geben Sie bitte einen Wert kleiner oder gleich {0} ein."),
		min: $.validator.format("Geben Sie bitte einen Wert größer oder gleich {0} ein."),
		creditcard: "Geben Sie bitte eine gültige Kreditkarten-Nummer ein.",
		lettersonly: "Bitte gib nur Buchstaben ein.",
		letterswithbasicpunc: "Bitte gib nur Buchstaben und Interpunktionszeichen ein.",
        yesorno: "Bitte gib entweder ja oder nein ein.",
        phone: "Bitte gib hier eine korrekte Nummer ein.",
        nohtml: "Bitte gib weder < noch > ein.",
        alphanumeric: "Bitte verwende nur Buchstaben, Zahlen und Unterstriche.",
        alphaandnumeric: "Bitte verwende mindestens einen Buchstaben und eine Zahl.",
        password: "Bitte gib zwischen 8 und 20 Zeichen ein und verwende mindestens einen Buchstaben und eine Zahl.",
        unique: "Wert ist nicht eindeutig."
	});
}(jQuery));