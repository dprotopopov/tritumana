////////////////////////////////////////////
//
// Набор констант для детектирования браузера
//
// Разработчик Дмитрий Протопопов http://protopopov.ru
// RBA DESIGN INTERNATIONAL LLC http://rbadesign.us
//
// Используются нижеследующие компоненты:
// jQuery http://jquery.com
/*

Пример:
	<script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
	<script src="jquery.browserDetect.js"></script>
	
<script>
(function($) {
if($.IsChrome) alert("Welcome Chrome");
})(jQuery);
</script>
*/
////////////////////////////////////////////
(function($) {
    // This method is a JavaScript extension to the ECMA-262 standard; as such it may not be present in other 
    // implementations of the standard. To make it work you need to add following code at the top of your script:
    if (!Array.prototype.forEach) {
        Array.prototype.forEach = function(fun /*, thisp*/) {
            var len = this.length;
            if (typeof fun != "function")
                throw new TypeError();

            var thisp = arguments[1];
            for (var i = 0; i < len; i++) {
                if (i in this)
                    fun.call(thisp, this[i], i, this);
            }
        };
    }
    ['Chrome', 'MSIE', 'Firefox', "Safari", "Presto"].forEach(function(item) {
        $["Is" + item] = navigator.userAgent.indexOf(item) > -1;
    });
})(jQuery);