////////////////////////////////////////////
//
// Изменение стиля дочерних элементов на inline-block
//
// Разработчик Дмитрий Протопопов http://protopopov.ru
// RBA DESIGN INTERNATIONAL LLC http://rbadesign.us
//
// Используются нижеследующие компоненты:
// jQuery http://jquery.com
/*
Порядок использования:
1. Установить у нужного элемента класс "row-element-children"

Пример:
	<script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
	<script src="jquery.rowElementChildren.js"></script>
	<link href="jquery.rowElementChildren.css" rel="stylesheet" type="text/css" />
	
<ul class="row-element-children">
<li>Item1</li>
<li>Item2</li>
<li>Item3</li>
</ul>
*/
////////////////////////////////////////////

(function($) {
    $.rowElementChildren = '.row-element-children';
    var update = function() {
        $($.rowElementChildren).each(function(index, element) {
            $(element).children().css("display", "inline-block");
        });
    };
    $(document).ready(update);
})(jQuery);