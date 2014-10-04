////////////////////////////////////////////
//
// Изменение минимальных размеров елемента до размера окна браузера
//
// Разработчик Дмитрий Протопопов http://protopopov.ru
// RBA DESIGN INTERNATIONAL LLC http://rbadesign.us
//
// Используются нижеследующие компоненты:
// jQuery http://jquery.com
//
/*
Порядок использования:
1. Установить у нужного элемента класс "full-screen-element"

Пример:
	<script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
	<script src="jquery.fullScreenElement.js"></script>
	
<div class="full-screen-element">Page1</div>
<div class="full-screen-element">Page2</div>
<div class="full-screen-element">Page3</div>
*/
////////////////////////////////////////////

(function($) {
    // This method is a JavaScript extension to the ECMA-262 standard; as such it may not be present in other 
    // implementations of the standard. To make it work you need to add following code at the top of your script:
    if (!Array.prototype.forEach) {
        Array.prototype.forEach = function(fun /*,thisp*/) {
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
    $.fullScreenElement = '.full-screen-element';
    $.centerElementContent = '.center-element-content';
    var updateFullScreenElement = function() {
        $($.fullScreenElement).not($.centerElementContent).each(function(index, element) {
            $(element).css("minWidth", $(window).width() + "px");
            $(element).css("minHeight", $(window).height() + "px");
        });
    };
    $(document).ready(updateFullScreenElement);
    $(document).resize(updateFullScreenElement);
    $(window).resize(updateFullScreenElement);
    $(document).ajaxComplete(updateFullScreenElement);
    $(document).ajaxStop(updateFullScreenElement);
})(jQuery);