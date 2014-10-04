////////////////////////////////////////////
//
// Отображение элемента в верхней части окна браузера,во всю ширину окна браузера
//
// Разработчик Дмитрий Протопопов http://protopopov.ru
// RBA DESIGN INTERNATIONAL LLC http://rbadesign.us
//
// Используются нижеследующие компоненты:
// jQuery http://jquery.com
/*
Порядок использования:
1. Установить у нужного элемента класс "top-menu-element"

Пример:
	<script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
	<script src="jquery.topMenuElement.js"></script>
	
<ul class="top-menu-element">
<li>Item1</li>
<li>Item2</li>
<li>Item3</li>
</ul>
*/
////////////////////////////////////////////

(function($) {
    $.topMenuElement = '.top-menu-element';
    var isFixedSupported = function() {
        var isSupported = null;
        if (document.createElement) {
            var el = document.createElement("div");
            if (el && el.style) {
                el.style.position = "fixed";
                el.style.top = "10px";
                var root = document.body;
                if (root && root.appendChild && root.removeChild) {
                    root.appendChild(el);
                    isSupported = el.offsetTop === 10;
                    root.removeChild(el);
                }
            }
        }
        return isSupported;
    };
    var updateFixed = function() {
        $($.topMenuElement).css("minWidth", $(window).width() + "px");
        $($.topMenuElement).css("top", "0px");
        $($.topMenuElement).css("zIndex", 1000);
        $($.topMenuElement).css("position", "fixed");
    };
    var update = function() {
        $($.topMenuElement).css("minWidth", $(window).width() + "px");
        $($.topMenuElement).css("top", window.pageYOffset + "px");
        $($.topMenuElement).css("zIndex", 1000);
        $($.topMenuElement).css("position", "absolute");
    };
    if (isFixedSupported()) {
        $(document).ready(updateFixed);
        $(document).resize(updateFixed);
        $(window).resize(updateFixed);
        $(document).ajaxComplete(updateFixed);
        $(document).ajaxStop(updateFixed);
    } else {
        $(document).ready(update);
        $(document).resize(update);
        $(window).resize(update);
        $(window).scroll(update);
        $(document).ajaxComplete(update);
        $(document).ajaxStop(update);
    }
})(jQuery);