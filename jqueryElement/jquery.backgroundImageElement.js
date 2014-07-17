////////////////////////////////////////////
//
// Назначение изображения в качестве бэкграундного изображения
//
// Разработчик Дмитрий Протопопов http://protopopov.ru
// RBA DESIGN INTERNATIONAL LLC http://rbadesign.us
//
// Используются нижеследующие компоненты:
// jQuery http://jquery.com
// cssParentSelector https://github.com/Idered/cssParentSelector
/*
Порядок использования:
1. Установить у нужного элемента класс "background-image-element"

Пример:
	<script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
    <script src="jQuery.cssParentSelector.js"></script>
	<script src="jquery.backgroundImageElement.js"></script>
	<link href="jquery.backgroundImageElement.css" rel="stylesheet" type="text/css" />
	
<div>
<img src="1.jpg" class="background-image-element" role="fixed"/>
<img src="2.jpg" class="background-image-element" role="scroll"/>
</div>
*/
////////////////////////////////////////////

(function($) {
    $.backgroundImageElement = '.background-image-element';
    var initBackgroundImageElement = function() {
        $($.backgroundImageElement).each(function(index, element) {
            var parent = $(element).parent();
            var role = $(element).attr("role") || "fixed";
            $(parent).css('background-image', 'url("' + $(element).attr('src') + '")');
            $(element).attr('src', "");
            $(parent).css("background-attachment", role);
            $(parent).css("background-position", "center center");
            $(parent).css("background-repeat", "no-repeat");
            $(parent).css("background-size", "cover");
            $(parent).css("-webkit-background-size", "cover");
            $(parent).css("-o-background-size", "cover");
            $(parent).css("-moz-background-size", "cover");
            $(element).remove();
        });
    };
    $(document).ready(initBackgroundImageElement);
})(jQuery);