/*
 * Localized default methods for the jQuery validation plugin.
 * Locale: FR
 */
jQuery.extend(jQuery.validator.methods, {
	date: function(value, element) {
		return this.optional(element) || /^(0?[1-9]|[12][0-9]|3[01])[.](0?[1-9]|1[012])[.]\d{4}$/.test(value);
	},
	number: function(value, element) {
		return this.optional(element) || /^-?(?:\d+|\d{1,3}(?:\.\d{3})+)(?:,\d+)?$/.test(value);
	},
	euro: function(value, element) {
		return this.optional(element) || /^-?(?:\d+|\d{1,3}(?:\.\d{3})+)(?:,\d{2})?$/.test(value);
	},
    letterswithbasicpunc: function(value, element) {
        return this.optional(element) || /^[a-zäöüßñçàèìòù`áéíóúý´âêîôûˆ\-.,()'"\s]+$/i.test(value);
    },
    yesorno: function(value, element) {
        return this.optional(element) || /^(ja|Ja|JA|nein|Nein|NEIN|oui|Oui|OUI|non|Non|NON){1}$/.test(value);
    },
    phone: function(value, element) {
        return this.optional(element) || /^[0-9\-\+\ \(\)/]{3,30}$/.test(value);
    },
    nohtml: function(value, element) {
        return this.optional(element) || /^[^<|^>]+$/.test(value);
    },
    alphaandnumeric: function(value, element) {
        return this.optional(element) || /^(?=.*\d)(?=.*[A-Za-z])[A-Za-z0-9]{8,}$/.test(value);
    },
    password: function(value, element) {
        return this.optional(element) || /^(?=.*\d)(?=.*[A-Za-z])[A-Za-z0-9ÄäÖöÜüßÀàÁáÂâÆæÇçÈèÉéÊêËëÎîÏïÔôŒœÙùÛûŸÿ°!"§%&=`´³²~'#-_:,;<>@€\^\$\?\\\(\)\[\]\{\}\+\*\.\|\/]{8,}$/.test(value);
        // Mindestens 8 Zeichen
        // Mindestens eine Zahl
        // Mindestens einen Buchstaben
        // Folgende Sonderzeichen sind erlaubt:
        // 
        // ÄäÖöÜüß
        // ÀàÁáÂâÆæÇçÈèÉéÊêËëÎîÏïÔôŒœÙùÛûŸÿ
        // °"§%&/=`´³²~'#-_:,;<>@€
        // ^$?\()[]{}+*.|
    }
});